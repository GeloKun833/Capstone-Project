<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SampleNotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create sample notifications for each user
            $user->notifications()->createMany([
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\AnnouncementNotification',
                    'data' => [
                        'title' => 'Welcome to the New Academic Year',
                        'message' => 'We are excited to welcome you to the new academic year. Please check your schedule and prepare for your classes.',
                        'type' => 'announcement',
                        'priority' => 'normal'
                    ],
                    'read_at' => null,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\NewMessageNotification',
                    'data' => [
                        'title' => 'New Message from Teacher',
                        'message' => 'You have received a new message regarding your recent assignment submission.',
                        'type' => 'message',
                        'priority' => 'normal'
                    ],
                    'read_at' => now()->subDay(),
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDay(),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\LowGradeAlertNotification',
                    'data' => [
                        'title' => 'Grade Alert',
                        'message' => 'Your recent grade in Mathematics is below the expected threshold. Please review your performance.',
                        'type' => 'grade_alert',
                        'priority' => 'high'
                    ],
                    'read_at' => null,
                    'created_at' => now()->subHours(6),
                    'updated_at' => now()->subHours(6),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\AttendanceAlertNotification',
                    'data' => [
                        'title' => 'Attendance Reminder',
                        'message' => 'You have missed 3 consecutive classes. Please ensure regular attendance.',
                        'type' => 'attendance_alert',
                        'priority' => 'urgent'
                    ],
                    'read_at' => null,
                    'created_at' => now()->subHours(2),
                    'updated_at' => now()->subHours(2),
                ],
            ]);
        }

        echo "Sample notifications created successfully!\n";
    }
} 