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

    public static function normalizeGradeLevel(?string $gradeLevel): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $gradeLevel)));
    }

    public static function isNurseryOrKindergarten(?string $gradeLevel): bool
    {
        return in_array(self::normalizeGradeLevel($gradeLevel), [
            'nursery',
            'kindergarten',
            'kinder',
        ], true);
    }

    public static function isDescriptiveGradeLevel(?string $gradeLevel): bool
    {
        $normalized = self::normalizeGradeLevel($gradeLevel);

        return self::isNurseryOrKindergarten($gradeLevel) || in_array($normalized, [
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

    public static function rank(?string $letter): ?int
    {
        return [
            'A' => 5,
            'B' => 4,
            'C' => 3,
            'D' => 2,
            'E' => 1,
        ][strtoupper(trim((string) $letter))] ?? null;
    }

    public static function letterFromAverage(float $average): string
    {
        $rank = (int) round(max(1, min(5, $average)));

        return [5 => 'A', 4 => 'B', 3 => 'C', 2 => 'D', 1 => 'E'][$rank];
    }

    public static function averageLetter(array $letters): ?string
    {
        $ranks = array_values(array_filter(array_map(
            fn ($letter) => self::rank($letter),
            $letters
        ), fn ($rank) => $rank !== null));

        if ($ranks === []) {
            return null;
        }

        return self::letterFromAverage(array_sum($ranks) / count($ranks));
    }

    public static function present(?string $letter, bool $withDescriptor = false): string
    {
        $meta = self::get($letter);
        if (! $meta) {
            return '—';
        }

        $letter = strtoupper(trim((string) $letter));

        return $withDescriptor ? $letter.' — '.$meta['english'] : $letter;
    }
}
