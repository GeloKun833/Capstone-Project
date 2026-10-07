<?php

namespace App\Support;

class DescriptiveScale
{
    public const LEVELS = [
        'A' => [
            'english' => 'Advancing',
            'filipino' => 'Namumukod-tangi',
            'description' => 'Demonstrates skills beyond grade-level expectations; performs with confidence, accuracy, and high quality.',
        ],
        'B' => [
            'english' => 'Benchmarking',
            'filipino' => 'Napamamalas',
            'description' => 'Demonstrates skills at the expected grade level; work is accurate and meets standard expectations.',
        ],
        'C' => [
            'english' => 'Connecting',
            'filipino' => 'Natutungo',
            'description' => 'Approaching grade-level expectations; demonstrates skills in some tasks but may require guidance in others.',
        ],
        'D' => [
            'english' => 'Developing',
            'filipino' => 'Napauunlad',
            'description' => 'Skills are emerging and developing; requires continued practice and support to improve performance.',
        ],
        'E' => [
            'english' => 'Emerging',
            'filipino' => 'Nagsisimula',
            'description' => 'Beginning to demonstrate skills; requires ongoing support and guided instruction to develop proficiency.',
        ],
    ];

    public static function letters(): array
    {
        return array_keys(self::LEVELS);
    }

    public static function get(?string $letter): ?array
    {
        $letter = strtoupper(trim((string) $letter));

        return self::LEVELS[$letter] ?? null;
    }

    public static function isDescriptiveGradeLevel(?string $gradeLevel): bool
    {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', (string) $gradeLevel)));

        return in_array($normalized, [
            'nursery',
            'kindergarten',
            'kinder',
            'grade 1',
            'grade1',
            'grade 2',
            'grade2',
            'grade 3',
            'grade3',
        ], true);
    }

    public static function methodFor(?string $gradeLevel): string
    {
        return self::isDescriptiveGradeLevel($gradeLevel) ? 'descriptive' : 'numerical';
    }
}
