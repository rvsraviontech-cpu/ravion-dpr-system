<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Seed the gender master.
     */
    public function run(): void
    {
        $genders = [
            [
                'code' => 'M',
                'name' => 'Male',
                'sort_order' => 10,
                'is_system' => true,
                'is_active' => true,
                'remarks' => 'Male gender classification.',
            ],
            [
                'code' => 'F',
                'name' => 'Female',
                'sort_order' => 20,
                'is_system' => true,
                'is_active' => true,
                'remarks' => 'Female gender classification.',
            ],
            [
                'code' => 'O',
                'name' => 'Other',
                'sort_order' => 30,
                'is_system' => true,
                'is_active' => true,
                'remarks' => 'Other gender classification.',
            ],
        ];

        foreach ($genders as $gender) {
            Gender::updateOrCreate(
                ['code' => $gender['code']],
                $gender
            );
        }
    }
}