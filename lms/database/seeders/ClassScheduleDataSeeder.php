<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Room;
use Carbon\Carbon;

class ClassScheduleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createClassSchedules();
    }

    /**
     * Create sample class schedules
     */
    private function createClassSchedules()
    {
        $sections = Section::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::all();

        if ($sections->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty() || $rooms->isEmpty()) {
            echo "Skipping class schedule seeding - missing required data\n";
            return;
        }

        $scheduleData = [
            // Monday
            [
                'day_of_week' => 'monday',
                'schedules' => [
                    ['start_time' => '08:00', 'end_time' => '09:00', 'subject_index' => 0, 'teacher_index' => 0, 'room_index' => 0],
                    ['start_time' => '09:00', 'end_time' => '10:00', 'subject_index' => 1, 'teacher_index' => 1, 'room_index' => 1],
                    ['start_time' => '10:00', 'end_time' => '11:00', 'subject_index' => 2, 'teacher_index' => 2, 'room_index' => 2],
                    ['start_time' => '11:00', 'end_time' => '12:00', 'subject_index' => 3, 'teacher_index' => 0, 'room_index' => 3],
                    ['start_time' => '13:00', 'end_time' => '14:00', 'subject_index' => 4, 'teacher_index' => 1, 'room_index' => 0],
                    ['start_time' => '14:00', 'end_time' => '15:00', 'subject_index' => 0, 'teacher_index' => 2, 'room_index' => 1],
                ]
            ],
            // Tuesday
            [
                'day_of_week' => 'tuesday',
                'schedules' => [
                    ['start_time' => '08:00', 'end_time' => '09:00', 'subject_index' => 1, 'teacher_index' => 1, 'room_index' => 1],
                    ['start_time' => '09:00', 'end_time' => '10:00', 'subject_index' => 2, 'teacher_index' => 2, 'room_index' => 2],
                    ['start_time' => '10:00', 'end_time' => '11:00', 'subject_index' => 3, 'teacher_index' => 0, 'room_index' => 3],
                    ['start_time' => '11:00', 'end_time' => '12:00', 'subject_index' => 4, 'teacher_index' => 1, 'room_index' => 0],
                    ['start_time' => '13:00', 'end_time' => '14:00', 'subject_index' => 0, 'teacher_index' => 2, 'room_index' => 1],
                    ['start_time' => '14:00', 'end_time' => '15:00', 'subject_index' => 1, 'teacher_index' => 0, 'room_index' => 2],
                ]
            ],
            // Wednesday
            [
                'day_of_week' => 'wednesday',
                'schedules' => [
                    ['start_time' => '08:00', 'end_time' => '09:00', 'subject_index' => 2, 'teacher_index' => 2, 'room_index' => 2],
                    ['start_time' => '09:00', 'end_time' => '10:00', 'subject_index' => 3, 'teacher_index' => 0, 'room_index' => 3],
                    ['start_time' => '10:00', 'end_time' => '11:00', 'subject_index' => 4, 'teacher_index' => 1, 'room_index' => 0],
                    ['start_time' => '11:00', 'end_time' => '12:00', 'subject_index' => 0, 'teacher_index' => 2, 'room_index' => 1],
                    ['start_time' => '13:00', 'end_time' => '14:00', 'subject_index' => 1, 'teacher_index' => 0, 'room_index' => 2],
                    ['start_time' => '14:00', 'end_time' => '15:00', 'subject_index' => 2, 'teacher_index' => 1, 'room_index' => 3],
                ]
            ],
            // Thursday
            [
                'day_of_week' => 'thursday',
                'schedules' => [
                    ['start_time' => '08:00', 'end_time' => '09:00', 'subject_index' => 3, 'teacher_index' => 0, 'room_index' => 3],
                    ['start_time' => '09:00', 'end_time' => '10:00', 'subject_index' => 4, 'teacher_index' => 1, 'room_index' => 0],
                    ['start_time' => '10:00', 'end_time' => '11:00', 'subject_index' => 0, 'teacher_index' => 2, 'room_index' => 1],
                    ['start_time' => '11:00', 'end_time' => '12:00', 'subject_index' => 1, 'teacher_index' => 0, 'room_index' => 2],
                    ['start_time' => '13:00', 'end_time' => '14:00', 'subject_index' => 2, 'teacher_index' => 1, 'room_index' => 3],
                    ['start_time' => '14:00', 'end_time' => '15:00', 'subject_index' => 3, 'teacher_index' => 2, 'room_index' => 0],
                ]
            ],
            // Friday
            [
                'day_of_week' => 'friday',
                'schedules' => [
                    ['start_time' => '08:00', 'end_time' => '09:00', 'subject_index' => 4, 'teacher_index' => 1, 'room_index' => 0],
                    ['start_time' => '09:00', 'end_time' => '10:00', 'subject_index' => 0, 'teacher_index' => 2, 'room_index' => 1],
                    ['start_time' => '10:00', 'end_time' => '11:00', 'subject_index' => 1, 'teacher_index' => 0, 'room_index' => 2],
                    ['start_time' => '11:00', 'end_time' => '12:00', 'subject_index' => 2, 'teacher_index' => 1, 'room_index' => 3],
                    ['start_time' => '13:00', 'end_time' => '14:00', 'subject_index' => 3, 'teacher_index' => 2, 'room_index' => 0],
                    ['start_time' => '14:00', 'end_time' => '15:00', 'subject_index' => 4, 'teacher_index' => 0, 'room_index' => 1],
                ]
            ]
        ];

        $subjectColors = [
            '#dc3545', // Red
            '#28a745', // Green
            '#17a2b8', // Blue
            '#ffc107', // Yellow
            '#6f42c1', // Purple
            '#fd7e14', // Orange
            '#20c997', // Teal
            '#198754', // Dark Green
            '#0d6efd', // Primary Blue
            '#e83e8c'  // Pink
        ];

        foreach ($sections as $section) {
            foreach ($scheduleData as $dayData) {
                foreach ($dayData['schedules'] as $schedule) {
                    $subjectIndex = $schedule['subject_index'] % $subjects->count();
                    $teacherIndex = $schedule['teacher_index'] % $teachers->count();
                    $roomIndex = $schedule['room_index'] % $rooms->count();
                    $colorIndex = $subjectIndex % count($subjectColors);

                    ClassSchedule::create([
                        'section_id' => $section->id,
                        'subject_id' => $subjects[$subjectIndex]->id,
                        'teacher_id' => $teachers[$teacherIndex]->id,
                        'room_id' => $rooms[$roomIndex]->id,
                        'day_of_week' => $dayData['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'color' => $subjectColors[$colorIndex],
                        'is_active' => true,
                        'notes' => $this->generateScheduleNotes($subjects[$subjectIndex]->subject_name)
                    ]);
                }
            }
        }

        echo "Class schedule sample data seeded successfully!\n";
    }

    /**
     * Generate schedule notes
     */
    private function generateScheduleNotes($subjectName)
    {
        $notes = [
            'Bring textbooks and notebooks',
            'Prepare for quiz',
            'Group activity today',
            'Lab session - bring safety equipment',
            'Presentation day',
            'Review session',
            'New topic introduction',
            'Practice exercises',
            'Discussion forum',
            'Assessment preparation'
        ];

        return $notes[array_rand($notes)];
    }
}
