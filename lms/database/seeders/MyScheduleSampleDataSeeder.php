<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Room;
use App\Models\Student;

class MyScheduleSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create sample data
        $sections = Section::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::all();

        if ($sections->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty() || $rooms->isEmpty()) {
            echo "Please run the basic seeders first to create sections, subjects, teachers, and rooms.\n";
            return;
        }

        // Clear existing schedules
        ClassSchedule::truncate();

        // Sample schedule data matching the screenshot
        $scheduleData = [
            // Tuesday
            [
                'day_of_week' => 'tuesday',
                'schedules' => [
                    [
                        'start_time' => '09:30:00',
                        'end_time' => '11:00:00',
                        'subject_index' => 0, // ITP110
                        'teacher_index' => 0, // L. Eusebio
                        'room_index' => 0, // 310
                        'class_type' => 'lecture',
                        'color' => '#e83e8c' // Magenta
                    ],
                    [
                        'start_time' => '11:30:00',
                        'end_time' => '13:00:00',
                        'subject_index' => 1, // RIZ101
                        'teacher_index' => 1, // TBA
                        'room_index' => 1, // Network Room
                        'class_type' => 'lecture',
                        'color' => '#dc3545' // Dark Red
                    ],
                    [
                        'start_time' => '14:30:00',
                        'end_time' => '16:00:00',
                        'subject_index' => 2, // ENV101
                        'teacher_index' => 1, // TBA
                        'room_index' => 1, // Network Room
                        'class_type' => 'lecture',
                        'color' => '#0d6efd' // Dark Blue
                    ]
                ]
            ],
            // Wednesday
            [
                'day_of_week' => 'wednesday',
                'schedules' => [
                    [
                        'start_time' => '08:30:00',
                        'end_time' => '10:00:00',
                        'subject_index' => 3, // ITEW5
                        'teacher_index' => 0, // L. Eusebio
                        'room_index' => 2, // COMLAB 3
                        'class_type' => 'lecture',
                        'color' => '#20c997' // Teal
                    ],
                    [
                        'start_time' => '10:00:00',
                        'end_time' => '11:30:00',
                        'subject_index' => 4, // ITP112
                        'teacher_index' => 2, // J. Ogalesco
                        'room_index' => 3, // VRCCE-2
                        'class_type' => 'lecture',
                        'color' => '#fd7e14' // Orange
                    ]
                ]
            ],
            // Friday
            [
                'day_of_week' => 'friday',
                'schedules' => [
                    [
                        'start_time' => '09:30:00',
                        'end_time' => '11:00:00',
                        'subject_index' => 0, // ITP110
                        'teacher_index' => 0, // L. Eusebio
                        'room_index' => 4, // COMLAB 6
                        'class_type' => 'laboratory',
                        'color' => '#e83e8c' // Magenta
                    ],
                    [
                        'start_time' => '11:30:00',
                        'end_time' => '13:00:00',
                        'subject_index' => 1, // RIZ101
                        'teacher_index' => 1, // TBA
                        'room_index' => 1, // Network Room
                        'class_type' => 'lecture',
                        'color' => '#dc3545' // Dark Red
                    ],
                    [
                        'start_time' => '14:30:00',
                        'end_time' => '16:00:00',
                        'subject_index' => 2, // ENV101
                        'teacher_index' => 1, // TBA
                        'room_index' => 1, // Network Room
                        'class_type' => 'lecture',
                        'color' => '#0d6efd' // Dark Blue
                    ]
                ]
            ],
            // Saturday
            [
                'day_of_week' => 'saturday',
                'schedules' => [
                    [
                        'start_time' => '14:00:00',
                        'end_time' => '15:30:00',
                        'subject_index' => 5, // ITP111
                        'teacher_index' => 3, // M. Redondo
                        'room_index' => 0, // 310
                        'class_type' => 'lecture',
                        'color' => '#198754' // Dark Green
                    ],
                    [
                        'start_time' => '16:00:00',
                        'end_time' => '17:30:00',
                        'subject_index' => 5, // ITP111
                        'teacher_index' => 3, // M. Redondo
                        'room_index' => 5, // COMLAB 1
                        'class_type' => 'laboratory',
                        'color' => '#198754' // Dark Green
                    ]
                ]
            ]
        ];

        // Create sample subjects if they don't exist
        $sampleSubjects = [
            ['subject_name' => 'ITP110', 'class' => 'Computer Science'],
            ['subject_name' => 'RIZ101', 'class' => 'Computer Science'],
            ['subject_name' => 'ENV101', 'class' => 'Computer Science'],
            ['subject_name' => 'ITEW5', 'class' => 'Computer Science'],
            ['subject_name' => 'ITP112', 'class' => 'Computer Science'],
            ['subject_name' => 'ITP111', 'class' => 'Computer Science']
        ];

        foreach ($sampleSubjects as $subjectData) {
            Subject::firstOrCreate(
                ['subject_name' => $subjectData['subject_name']],
                $subjectData
            );
        }

        // Create sample teachers if they don't exist
        $sampleTeachers = [
            ['full_name' => 'L. Eusebio', 'phone_number' => '1234567890'],
            ['full_name' => 'TBA', 'phone_number' => '0000000000'],
            ['full_name' => 'J. Ogalesco', 'phone_number' => '1234567891'],
            ['full_name' => 'M. Redondo', 'phone_number' => '1234567892']
        ];

        foreach ($sampleTeachers as $teacherData) {
            Teacher::firstOrCreate(
                ['full_name' => $teacherData['full_name']],
                $teacherData
            );
        }

        // Create sample rooms if they don't exist
        $sampleRooms = [
            ['room_name' => 'Room 310', 'room_number' => '310', 'capacity' => 30],
            ['room_name' => 'Network Room', 'room_number' => 'NET', 'capacity' => 25],
            ['room_name' => 'COMLAB 3', 'room_number' => 'CL3', 'capacity' => 20],
            ['room_name' => 'VRCCE-2', 'room_number' => 'VR2', 'capacity' => 15],
            ['room_name' => 'COMLAB 6', 'room_number' => 'CL6', 'capacity' => 20],
            ['room_name' => 'COMLAB 1', 'room_number' => 'CL1', 'capacity' => 20]
        ];

        foreach ($sampleRooms as $roomData) {
            Room::firstOrCreate(
                ['room_name' => $roomData['room_name']],
                $roomData
            );
        }

        // Refresh the collections
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::all();

        // Create schedules for each section
        foreach ($sections as $section) {
            foreach ($scheduleData as $dayData) {
                foreach ($dayData['schedules'] as $schedule) {
                    $subjectIndex = $schedule['subject_index'] % $subjects->count();
                    $teacherIndex = $schedule['teacher_index'] % $teachers->count();
                    $roomIndex = $schedule['room_index'] % $rooms->count();

                    ClassSchedule::create([
                        'section_id' => $section->id,
                        'subject_id' => $subjects[$subjectIndex]->id,
                        'teacher_id' => $teachers[$teacherIndex]->id,
                        'room_id' => $rooms[$roomIndex]->id,
                        'day_of_week' => $dayData['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'class_type' => $schedule['class_type'],
                        'color' => $schedule['color'],
                        'is_active' => true,
                        'notes' => $this->generateScheduleNotes($subjects[$subjectIndex]->subject_name)
                    ]);
                }
            }
        }

        echo "My Schedule sample data seeded successfully!\n";
        echo "Created schedules for " . $sections->count() . " sections\n";
        echo "Schedule includes: ITP110, RIZ101, ENV101, ITEW5, ITP112, ITP111\n";
        echo "Days with classes: Tuesday, Wednesday, Friday, Saturday\n";
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