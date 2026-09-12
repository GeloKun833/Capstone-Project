<?php

namespace Database\Seeders;

use App\Models\ObservedValueIndicator;
use Illuminate\Database\Seeder;

class ObservedValueIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Idea of core-value reporting (short labels — not copied from a specific card)
            ['Maka-Diyos', 'Shows respect for faith and good character', 1],
            ['Maka-Diyos', 'Practices honesty and fairness', 2],
            ['Makatao', 'Respects others and their differences', 3],
            ['Makatao', 'Shows kindness and cooperation', 4],
            ['Maka-kalikasan', 'Cares for the environment', 5],
            ['Maka-kalikasan', 'Uses resources wisely', 6],
            ['Makabansa', 'Shows pride in being Filipino', 7],
            ['Makabansa', 'Follows school and community rules', 8],
        ];

        foreach ($rows as [$core, $statement, $order]) {
            ObservedValueIndicator::updateOrCreate(
                ['core_value' => $core, 'statement' => $statement],
                ['sort_order' => $order, 'is_active' => true]
            );
        }
    }
}
