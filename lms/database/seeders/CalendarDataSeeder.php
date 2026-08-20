<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\CalendarEvent;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;

class CalendarDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createRooms();
        $this->createCalendarEvents();
    }

    /**
     * Create sample rooms
     */
    private function createRooms()
    {
        $rooms = [
            ['room_name' => 'Computer Lab 1', 'room_number' => 'CL101', 'capacity' => 25, 'location' => 'First Floor'],
            ['room_name' => 'Computer Lab 2', 'room_number' => 'CL102', 'capacity' => 25, 'location' => 'First Floor'],
            ['room_name' => 'Science Laboratory', 'room_number' => 'SL201', 'capacity' => 30, 'location' => 'Second Floor'],
            ['room_name' => 'Mathematics Room', 'room_number' => 'MR301', 'capacity' => 35, 'location' => 'Third Floor'],
            ['room_name' => 'English Room', 'room_number' => 'ER302', 'capacity' => 35, 'location' => 'Third Floor'],
            ['room_name' => 'Conference Room', 'room_number' => 'CR401', 'capacity' => 20, 'location' => 'Fourth Floor'],
            ['room_name' => 'Library', 'room_number' => 'LIB501', 'capacity' => 50, 'location' => 'Fifth Floor'],
            ['room_name' => 'Auditorium', 'room_number' => 'AUD601', 'capacity' => 200, 'location' => 'Sixth Floor'],
        ];

        foreach ($rooms as $roomData) {
            Room::firstOrCreate(
                ['room_number' => $roomData['room_number']],
                $roomData
            );
        }
    }

    /**
     * Create sample calendar events
     */
    private function createCalendarEvents()
    {
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::all();
        $users = User::whereIn('role_name', ['Admin', 'Teacher'])->get();

        if ($subjects->isEmpty() || $teachers->isEmpty() || $rooms->isEmpty() || $users->isEmpty()) {
            return; // Skip if no data available
        }

        $eventTypes = ['exam', 'activity', 'meeting', 'deadline', 'holiday', 'other'];
        $eventTitles = [
            'exam' => ['Midterm Exam', 'Final Exam', 'Quiz 1', 'Quiz 2', 'Unit Test'],
            'activity' => ['Group Project', 'Lab Session', 'Field Trip', 'Workshop', 'Presentation'],
            'meeting' => ['Faculty Meeting', 'Parent Conference', 'Department Meeting', 'Staff Meeting', 'Committee Meeting'],
            'deadline' => ['Assignment Due', 'Project Submission', 'Report Deadline', 'Paper Submission', 'Portfolio Due'],
            'holiday' => ['Christmas Break', 'Easter Holiday', 'Summer Vacation', 'Independence Day', 'Labor Day'],
            'other' => ['School Assembly', 'Sports Day', 'Cultural Event', 'Open House', 'Graduation Ceremony']
        ];

        // Create events for the next 3 months
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonths(3)->endOfMonth();

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip weekends for regular events
            if (!$currentDate->isWeekend()) {
                // Create 2-4 events per day
                $numEvents = rand(2, 4);
                
                for ($i = 0; $i < $numEvents; $i++) {
                    $eventType = $eventTypes[array_rand($eventTypes)];
                    $title = $eventTitles[$eventType][array_rand($eventTitles[$eventType])];
                    
                    // Generate random time between 8 AM and 5 PM
                    $startHour = rand(8, 16);
                    $startMinute = rand(0, 3) * 15; // 0, 15, 30, or 45 minutes
                    $duration = rand(1, 3) * 60; // 1-3 hours in minutes
                    
                    $startTime = $currentDate->copy()->setTime($startHour, $startMinute);
                    $endTime = $startTime->copy()->addMinutes($duration);
                    
                    // Skip if end time is after 6 PM
                    if ($endTime->hour >= 18) {
                        continue;
                    }

                    $eventData = [
                        'title' => $title,
                        'description' => $this->generateDescription($eventType, $title),
                        'event_type' => $eventType,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'subject_id' => $subjects->random()->id,
                        'teacher_id' => $teachers->random()->id,
                        'room_id' => $rooms->random()->id,
                        'created_by' => $users->random()->id,
                        'is_all_day' => false,
                        'is_recurring' => rand(1, 10) <= 2, // 20% chance of recurring
                        'recurrence_pattern' => null,
                        'recurrence_end_date' => null
                    ];

                    // Set recurrence pattern if recurring
                    if ($eventData['is_recurring']) {
                        $patterns = ['daily', 'weekly', 'monthly'];
                        $eventData['recurrence_pattern'] = $patterns[array_rand($patterns)];
                        $eventData['recurrence_end_date'] = $currentDate->copy()->addWeeks(rand(2, 8));
                    }

                    CalendarEvent::create($eventData);
                }
            }

            // Create some all-day events (holidays, etc.)
            if (rand(1, 10) <= 3) { // 30% chance
                $holidayTitle = $eventTitles['holiday'][array_rand($eventTitles['holiday'])];
                CalendarEvent::create([
                    'title' => $holidayTitle,
                    'description' => 'School holiday - no classes',
                    'event_type' => 'holiday',
                    'start_time' => $currentDate->copy()->setTime(0, 0),
                    'end_time' => $currentDate->copy()->setTime(23, 59),
                    'subject_id' => null,
                    'teacher_id' => null,
                    'room_id' => null,
                    'created_by' => $users->where('role_name', 'Admin')->first()->id,
                    'is_all_day' => true,
                    'is_recurring' => false
                ]);
            }

            $currentDate->addDay();
        }

        echo "Calendar sample data seeded successfully!\n";
    }

    /**
     * Generate description for events
     */
    private function generateDescription($eventType, $title)
    {
        $descriptions = [
            'exam' => [
                'Comprehensive assessment covering all topics discussed in class.',
                'Students should bring their own writing materials and calculators.',
                'Duration: 2 hours. No electronic devices allowed.',
                'Review all course materials before the exam.'
            ],
            'activity' => [
                'Hands-on learning experience to reinforce classroom concepts.',
                'Group collaboration required. Prepare necessary materials.',
                'Interactive session with practical applications.',
                'Students will work in teams of 3-4 members.'
            ],
            'meeting' => [
                'Important discussion about academic progress and planning.',
                'All faculty members are required to attend.',
                'Agenda will be distributed prior to the meeting.',
                'Please come prepared with your reports and updates.'
            ],
            'deadline' => [
                'Final submission deadline. Late submissions will not be accepted.',
                'Ensure all requirements are met before submission.',
                'Submit both hard copy and digital version.',
                'Include all supporting documents and references.'
            ],
            'holiday' => [
                'School will be closed for the holiday.',
                'No classes scheduled. Enjoy your break!',
                'Administrative offices will also be closed.',
                'Classes will resume on the following business day.'
            ],
            'other' => [
                'Special event for the entire school community.',
                'All students and staff are encouraged to participate.',
                'This is a mandatory event for all grade levels.',
                'Parents and guardians are welcome to attend.'
            ]
        ];

        $typeDescriptions = $descriptions[$eventType];
        return $typeDescriptions[array_rand($typeDescriptions)];
    }
}
