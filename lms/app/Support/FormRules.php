<?php

namespace App\Support;

class FormRules
{
    /** Letters, spaces, hyphens, apostrophes, periods — no emoji. */
    public const NAME = 'required|string|max:255|regex:/^[\p{L}\p{M}\s\'\-\.]+$/u|no_emoji';

    public const NAME_OPTIONAL = 'nullable|string|max:255|regex:/^[\p{L}\p{M}\s\'\-\.]+$/u|no_emoji';

    /** Plain text without emoji / control chars. */
    public const TEXT = 'nullable|string|max:255|regex:/^[\p{L}\p{M}\p{N}\s\'\-\.\,\#\/\&\(\)]+$/u|no_emoji';

    public const TEXT_REQUIRED = 'required|string|max:255|regex:/^[\p{L}\p{M}\p{N}\s\'\-\.\,\#\/\&\(\)]+$/u|no_emoji';

    public const PHONE = 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/|no_emoji';

    public const PHONE_REQUIRED = 'required|string|max:20|regex:/^[0-9+\-\s()]+$/|no_emoji';

    public const DOB = 'nullable|date|before_or_equal:today';

    public const DOB_REQUIRED = 'required|date|before_or_equal:today';

    public const AVATAR = 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:2048';

    public const TITLE_NO_EMOJI = 'required|string|max:255|regex:/^[\p{L}\p{M}\p{N}\s\'\-\.\,\:\!\?\#\/\&\(\)]+$/u|no_emoji';

    /** Reusable rule fragment for any string field. */
    public const NO_EMOJI = 'no_emoji';

    public static function messages(): array
    {
        return [
            'name.regex' => 'Name may only contain letters, spaces, hyphens, and apostrophes (no emojis).',
            'full_name.regex' => 'Name may only contain letters, spaces, hyphens, and apostrophes (no emojis).',
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, and apostrophes (no emojis).',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, and apostrophes (no emojis).',
            'middle_name.regex' => 'Middle name may only contain letters, spaces, hyphens, and apostrophes (no emojis).',
            'phone_number.regex' => 'Phone number may only contain digits and + - ( ) spaces.',
            'position.regex' => 'Position contains invalid characters or emojis.',
            'department.regex' => 'Department contains invalid characters or emojis.',
            'title.regex' => 'Title cannot contain emojis or special symbols.',
            'subject_name.regex' => 'Subject name cannot contain emojis.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be a future date.',
            'avatar.mimes' => 'Profile image must be JPG, PNG, GIF, or WEBP.',
            'avatar.max' => 'Profile image must not exceed 2MB.',
            'no_emoji' => 'Emojis are not allowed in this field.',
            '*.no_emoji' => 'Emojis are not allowed in this field.',
        ];
    }
}
