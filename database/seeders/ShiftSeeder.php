<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Seed the Shift Master.
     */
    public function run(): void
    {
        $shifts = [
            [
                'code' => 'GENERAL',
                'name' => 'General Shift',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'normal_hours' => 8.00,
                'crosses_midnight' => false,
                'is_system' => true,
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => 'Standard daytime shift used for regular site operations.',
            ],
            [
                'code' => 'DAY',
                'name' => 'Day Shift',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'normal_hours' => 8.00,
                'crosses_midnight' => false,
                'is_system' => true,
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => 'Daytime shift for site labour and construction activities.',
            ],
            [
                'code' => 'MORNING',
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'normal_hours' => 8.00,
                'crosses_midnight' => false,
                'is_system' => true,
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => 'Early morning shift used where site operations begin before normal working hours.',
            ],
            [
                'code' => 'EVENING',
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'normal_hours' => 8.00,
                'crosses_midnight' => false,
                'is_system' => true,
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => 'Evening shift for extended or staggered site operations.',
            ],
            [
                'code' => 'NIGHT',
                'name' => 'Night Shift',
                'start_time' => '20:00:00',
                'end_time' => '05:00:00',
                'normal_hours' => 8.00,
                'crosses_midnight' => true,
                'is_system' => true,
                'sort_order' => 50,
                'is_active' => true,
                'remarks' => 'Night shift that ends on the following calendar day.',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                [
                    'code' => $shift['code'],
                ],
                $shift
            );
        }
    }
}