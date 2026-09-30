<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'roll',
        'blood_group',
        'religion',
        'email',
        'parent_email',
        'parent_user_id',
        'parent_name',
        'parent_phone',
        'parent_relationship',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'previous_school',
        'enrollment_status',
        'enrollment_application_id',
        'class',
        'year_level',
        'section',
        'admission_id',
        'phone_number',
        'upload',
    ];

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function linkedParentUser(): ?User
    {
        if ($this->parent_user_id) {
            $parent = User::query()
                ->where('id', $this->parent_user_id)
                ->where('role_name', User::ROLE_PARENT)
                ->first();
            if ($parent) {
                return $parent;
            }
        }

        if ($this->parent_email) {
            return User::query()
                ->where('email', $this->parent_email)
                ->where('role_name', User::ROLE_PARENT)
                ->first();
        }

        return null;
    }

    public function scopeForParent($query, User $parent)
    {
        return $query->where(function ($q) use ($parent) {
            $q->where('parent_user_id', $parent->id);
            if ($parent->email) {
                $q->orWhere('parent_email', $parent->email);
            }
        });
    }

    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function consultationRequests() { return $this->hasMany(ConsultationRequest::class); }
    public function subjects() { return $this->belongsToMany(Subject::class, 'enrollments'); }
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'student_section_assignments', 'student_id', 'section_id')
            ->withPivot('academic_year_id', 'semester_id', 'assigned_date')
            ->withTimestamps();
    }

    public function legacySections()
    {
        return $this->belongsToMany(Section::class, 'section_student', 'student_id', 'section_id');
    }

    /**
     * Section IDs from modern assignments, legacy pivot, or the students.section name.
     *
     * @return list<int>
     */
    public function resolvedSectionIds(): array
    {
        $ids = $this->sections()->pluck('sections.id');

        try {
            $ids = $ids->merge($this->legacySections()->pluck('sections.id'));
        } catch (\Throwable $e) {
            // Legacy pivot may be missing on some databases.
        }

        if ($ids->isEmpty() && trim((string) $this->section) !== '') {
            $name = trim((string) $this->section);
            $labels = \App\Services\GradeSubjectCatalogService::gradeAliases($this->year_level ?: $this->class);
            $matched = Section::query()->where('name', $name);
            if (! empty($labels)) {
                $matched = $matched->whereIn('grade_level', $labels);
            }
            $found = $matched->pluck('id');
            if ($found->isEmpty()) {
                $found = Section::query()->where('name', $name)->pluck('id');
            }
            $ids = $ids->merge($found);
        }

        return $ids->unique()->filter()->map(fn ($id) => (int) $id)->values()->all();
    }

    public function resolvedSections()
    {
        $ids = $this->resolvedSectionIds();
        if ($ids === []) {
            return collect();
        }

        return Section::query()->whereIn('id', $ids)->with('adviser')->orderBy('grade_level')->orderBy('name')->get();
    }

    public function studentNumber(): string
    {
        return $this->admission_id ?: ('STD'.$this->id);
    }

    public function accountId(): ?string
    {
        return $this->user_id;
    }

    public function sectionLabel(): string
    {
        $section = $this->resolvedSections()->first();
        if ($section) {
            return $section->name;
        }

        return trim((string) $this->section);
    }

    public static function gradeMatchLabels(?string $grade): array
    {
        $labels = collect(\App\Services\GradeSubjectCatalogService::gradeAliases($grade));
        if (preg_match('/(?:grade|g)?\s*(\d+)/i', (string) $grade, $m)) {
            $n = $m[1];
            $labels->push('Grade '.$n, 'GRADE '.$n, 'G'.$n, $n);
        }

        return $labels->filter()->unique()->values()->all();
    }

    public function scopeInGradeLevel($query, string $grade)
    {
        $labels = self::gradeMatchLabels($grade);
        $sectionIds = Section::query()->whereIn('grade_level', $labels)->pluck('id');
        $sectionNames = Section::query()->whereIn('grade_level', $labels)->pluck('name');

        return $query->where(function ($q) use ($labels, $sectionIds, $sectionNames) {
            $q->whereIn('year_level', $labels)->orWhereIn('class', $labels);
            if ($sectionNames->isNotEmpty()) {
                $q->orWhereIn('section', $sectionNames);
            }
            if ($sectionIds->isNotEmpty()) {
                $q->orWhereIn('id', function ($sub) use ($sectionIds) {
                    $sub->from('student_section_assignments')->select('student_id')->whereIn('section_id', $sectionIds);
                });
                if (\Illuminate\Support\Facades\Schema::hasTable('section_student')) {
                    $q->orWhereIn('id', function ($sub) use ($sectionIds) {
                        $sub->from('section_student')->select('student_id')->whereIn('section_id', $sectionIds);
                    });
                }
            }
        });
    }

    public function scopeNotSeparated($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('enrollment_status')
                ->orWhereRaw('LOWER(TRIM(enrollment_status)) NOT IN (?,?,?,?,?,?,?)', [
                    'withdrawn', 'dropped', 'inactive', 'archived', 'graduated', 'transferred', 'rejected',
                ]);
        });
    }

    public function photoUrl(): string
    {
        return \App\Support\AvatarUploader::urlForStudent($this);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function enrollmentApplication()
    {
        return $this->belongsTo(EnrollmentApplication::class);
    }

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function gpaRecords()
    {
        return $this->hasMany(StudentGpa::class);
    }

    public function gradeAlerts()
    {
        return $this->hasMany(GradeAlert::class);
    }

    public function promotions()
    {
        return $this->hasMany(StudentPromotion::class);
    }

    public function quarterlyGrades()
    {
        return $this->hasMany(QuarterlyGrade::class);
    }

    // Get current GPA for a specific academic period
    public function getCurrentGpa($academicYearId = null, $semesterId = null)
    {
        $query = $this->gpaRecords();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        
        return $query->latest()->first();
    }

    // Get active alerts
    public function getActiveAlerts()
    {
        return $this->gradeAlerts()->where('is_resolved', false)->get();
    }

    /**
     * Get the student's full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
