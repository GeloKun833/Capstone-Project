<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Message;
use App\Models\ClassPostComment;

class User extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, HasRoles, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'date_of_birth',
        'join_date',
        'phone_number',
        'status',
        'role_name',
        'avatar',
        'position',
        'department',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Role constants
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_REGISTRAR = 'Registrar';
    public const ROLE_TEACHER = 'Teacher';
    public const ROLE_STUDENT = 'Student';
    public const ROLE_PARENT = 'Parent';

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role_name === $role;
    }

    /**
     * Get the student profile associated with the user.
     */
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

    /**
     * Get the teacher profile associated with the user.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'user_id');
    }

    /**
     * Get the children (students) associated with the parent user.
     */
    public function children()
    {
        return Student::where('parent_email', $this->email)->get();
    }

    /**
     * Get the messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get the class post comments by this user.
     */
    public function classPostComments()
    {
        return $this->hasMany(ClassPostComment::class, 'user_id');
    }

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role_name'])
            ->useLogName('user')
            ->logOnlyDirty();
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            // Single MAX lookup instead of ORDER BY + while-exists loop (saves RTTs on Aiven).
            $latest = self::query()->max('user_id');
            $latestID = 0;
            if (is_string($latest) && preg_match('/(\d+)$/', $latest, $m)) {
                $latestID = (int) $m[1];
            }
            $nextID = $latestID + 1;
            $model->user_id = '000'.sprintf('%03s', $nextID);
            // Rare collision only — one retry max.
            if (self::where('user_id', $model->user_id)->exists()) {
                $nextID++;
                $model->user_id = '000'.sprintf('%03s', $nextID);
            }
        });
    }
}
