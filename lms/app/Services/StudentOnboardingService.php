<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\EnrollmentApplication;
use App\Models\EnrollmentDocument;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\AvatarUploader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StudentOnboardingService
{
    /**
     * Fill the fields Profile, Analytics, Attendance, and Chat already read
     * so newly created students match existing student records.
     */
    public function finalize(Student $student, array $options = []): Student
    {
        $student->refresh();

        $user = $options['user'] ?? $student->user;
        $application = $options['application'] ?? $student->enrollmentApplication;
        $section = $options['section'] ?? null;
        $activate = array_key_exists('activate', $options) ? (bool) $options['activate'] : null;
        $admissionId = $options['admission_id'] ?? null;

        $this->ensureUserAccount($student, $user);
        $this->ensureParentLink($student, $application, $options['parent'] ?? null);
        $this->ensureGradeAndClass($student, $application);
        $this->ensureAdmissionId($student, $application, $admissionId);
        $this->copyApplicationDemographics($student, $application);
        $this->syncSectionRecord($student, $section);
        $this->syncIdPhoto($student, $application);

        if ($activate === true) {
            $student->enrollment_status = 'active';
            $linked = $student->user;
            if ($linked && ! User::isActiveStatus($linked->status)) {
                $linked->status = 'active';
                $linked->save();
            }
        }

        $student->save();

        return $student->fresh();
    }

    public function ensureRole(User $user, string $role): void
    {
        if ($user->role_name !== $role) {
            $user->role_name = $role;
        }

        $defaults = [
            User::ROLE_STUDENT => ['position' => 'Student', 'department' => 'Student Affairs'],
            User::ROLE_PARENT => ['position' => 'Parent/Guardian', 'department' => 'Parent Relations'],
        ];
        if (isset($defaults[$role])) {
            if (empty($user->position)) {
                $user->position = $defaults[$role]['position'];
            }
            if (empty($user->department)) {
                $user->department = $defaults[$role]['department'];
            }
        }
        if (empty($user->join_date)) {
            $user->join_date = now()->format('Y-m-d');
        }
        if ($user->isDirty()) {
            $user->save();
        }

        try {
            if (method_exists($user, 'hasRole') && $user->roles()->where('name', $role)->exists()) {
                return;
            }
            $user->assignRole($role);
        } catch (\Throwable $e) {
            Log::warning('Could not assign Spatie role '.$role.' to user '.$user->user_id.': '.$e->getMessage());
        }
    }

    public function syncSectionLinks(Student $student, Section $section): void
    {
        $updates = ['section' => $section->name];
        if (empty($student->class) && ! empty($section->grade_level)) {
            $updates['class'] = $section->grade_level;
        }
        if (empty($student->year_level) && ! empty($section->grade_level)) {
            $updates['year_level'] = $section->grade_level;
        }
        $student->fill($updates);
        $student->save();

        if (Schema::hasTable('section_student')) {
            DB::table('section_student')->insertOrIgnore([
                'section_id' => $section->id,
                'student_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Enroll the student in every subject for their grade (same catalog the dashboards use).
     *
     * @return list<string>
     */
    public function enrollInGradeSubjects(Student $student, ?string $gradeLevel = null): array
    {
        $gradeLevel = $gradeLevel ?: ($student->year_level ?: $student->class);
        if (! $gradeLevel) {
            return [];
        }

        $academicYear = \App\Models\AcademicYear::current();
        $semester = \App\Models\Semester::current();
        if (! $academicYear || ! $semester) {
            throw new \Exception('No academic year or semester found. Please set up academic periods first.');
        }

        $subjects = app(GradeSubjectCatalogService::class)->subjectsForGrade($gradeLevel);
        if ($subjects->isEmpty()) {
            throw new \Exception(
                "No subjects found for grade level: {$gradeLevel}. ".
                'Please add subjects under Academic Management → Classes & Subjects first.'
            );
        }

        $enrolledSubjects = [];
        foreach ($subjects as $subject) {
            $existing = Enrollment::query()->where([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ])->first();

            if (! $existing) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => $semester->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
            }

            $enrolledSubjects[] = $subject->subject_name;
        }

        return $enrolledSubjects;
    }

    private function ensureUserAccount(Student $student, ?User $user): void
    {
        if (! $user && $student->user_id) {
            $user = User::where('user_id', $student->user_id)->first();
        }
        if (! $user) {
            return;
        }

        $this->ensureRole($user, User::ROLE_STUDENT);

        if (empty($student->user_id)) {
            $student->user_id = $user->user_id;
        }
    }

    private function ensureParentLink(Student $student, ?EnrollmentApplication $application, ?User $parent = null): void
    {
        if (! $parent && $student->parent_user_id) {
            $parent = User::query()
                ->where('id', $student->parent_user_id)
                ->where('role_name', User::ROLE_PARENT)
                ->first();
        }

        $parentEmail = $student->parent_email ?: $application?->parent_email;
        if (! $parent && $parentEmail) {
            $parent = User::query()
                ->where('email', $parentEmail)
                ->where('role_name', User::ROLE_PARENT)
                ->first();
        }

        if ($parent) {
            $this->ensureRole($parent, User::ROLE_PARENT);
            $student->parent_user_id = $parent->id;
            if (empty($student->parent_email)) {
                $student->parent_email = $parent->email;
            }
        }
    }

    private function ensureGradeAndClass(Student $student, ?EnrollmentApplication $application): void
    {
        $grade = $student->year_level ?: $student->class ?: $application?->grade_level_applying_for;
        if (! $grade) {
            return;
        }
        if (empty($student->year_level)) {
            $student->year_level = $grade;
        }
        if (empty($student->class)) {
            $student->class = $grade;
        }
    }

    private function ensureAdmissionId(Student $student, ?EnrollmentApplication $application, ?string $custom): void
    {
        if (is_string($custom) && trim($custom) !== '') {
            $student->admission_id = trim($custom);

            return;
        }

        if (trim((string) $student->admission_id) !== '') {
            return;
        }

        $fromApp = trim((string) ($application?->application_number ?? ''));
        $student->admission_id = $fromApp !== '' ? $fromApp : ('STD'.$student->id);
    }

    private function copyApplicationDemographics(Student $student, ?EnrollmentApplication $application): void
    {
        if (! $application) {
            return;
        }

        $map = [
            'religion' => 'religion',
            'address' => 'address',
            'phone_number' => 'phone_number',
            'parent_name' => 'parent_name',
            'parent_phone' => 'parent_phone',
            'parent_relationship' => 'parent_relationship',
            'emergency_contact_name' => 'emergency_contact_name',
            'emergency_contact_phone' => 'emergency_contact_phone',
            'previous_school' => 'previous_school',
        ];
        foreach ($map as $studentField => $appField) {
            if (empty($student->{$studentField}) && ! empty($application->{$appField})) {
                $student->{$studentField} = $application->{$appField};
            }
        }
    }

    private function syncSectionRecord(Student $student, ?Section $section): void
    {
        if (! $section) {
            $ids = $student->resolvedSectionIds();
            if ($ids !== []) {
                $section = Section::query()->find($ids[0]);
            }
        }

        if ($section) {
            $this->syncSectionLinks($student, $section);
        }
    }

    private function syncIdPhoto(Student $student, ?EnrollmentApplication $application): void
    {
        $user = $student->user;
        if ($user && ! $this->isPlaceholderAvatar($user->avatar) && ! empty($user->avatar)) {
            if (empty($student->upload) || $this->isPlaceholderAvatar($student->upload)) {
                $student->upload = $user->avatar;
            }

            return;
        }

        if (! empty($student->upload) && ! $this->isPlaceholderAvatar($student->upload)) {
            if ($user && ($this->isPlaceholderAvatar($user->avatar) || empty($user->avatar))) {
                $copied = $this->copyStoredFileToAvatars($student->upload);
                if ($copied) {
                    $user->avatar = $copied;
                    $user->save();
                    $student->upload = $copied;
                }
            }

            return;
        }

        if (! $application) {
            return;
        }

        $document = EnrollmentDocument::query()
            ->where('enrollment_application_id', $application->id)
            ->where('document_type', 'id_photo')
            ->orderByDesc('id')
            ->first();

        if (! $document || empty($document->file_path)) {
            return;
        }

        $copied = $this->copyStoredFileToAvatars($document->file_path);
        if (! $copied) {
            return;
        }

        if ($user) {
            $user->avatar = $copied;
            $user->save();
        }
        $student->upload = $copied;
    }

    private function isPlaceholderAvatar(?string $avatar): bool
    {
        $avatar = strtolower(trim((string) $avatar));

        return $avatar === ''
            || $avatar === 'photo_defaults.jpg'
            || $avatar === 'default-avatar.png'
            || $avatar === 'avatar-01.jpg';
    }

    private function copyStoredFileToAvatars(string $source): ?string
    {
        $source = ltrim(str_replace('\\', '/', $source), '/');
        if (str_starts_with($source, 'storage/')) {
            $source = substr($source, strlen('storage/'));
        }

        $candidates = [$source];
        if (! str_contains($source, '/')) {
            $candidates[] = 'avatars/'.$source;
            $candidates[] = 'student-photos/'.$source;
        }

        $found = null;
        foreach ($candidates as $path) {
            if (Storage::disk('public')->exists($path)) {
                $found = $path;
                break;
            }
        }

        if (! $found) {
            return null;
        }

        if (str_starts_with($found, 'avatars/')) {
            return $found;
        }

        $ext = strtolower(pathinfo($found, PATHINFO_EXTENSION) ?: 'jpg');
        $dest = 'avatars/'.time().'_'.uniqid().'.'.$ext;
        Storage::disk('public')->copy($found, $dest);

        return $dest;
    }
}
