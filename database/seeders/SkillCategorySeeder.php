<?php

namespace Database\Seeders;

use App\Models\SkillCategory;
use Illuminate\Database\Seeder;

class SkillCategorySeeder extends Seeder
{
    /**
     * Seed the skill category master.
     */
    public function run(): void
    {
        $skillCategories = [
            [
                'code' => 'UNSKILLED',
                'name' => 'Unskilled',
                'is_system' => true,
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => 'Labour performing general work that does not require specialized technical training or trade skills.',
            ],
            [
                'code' => 'SEMI_SKILLED',
                'name' => 'Semi-Skilled',
                'is_system' => true,
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => 'Labour performing work that requires limited training, practical experience, or supervision.',
            ],
            [
                'code' => 'SKILLED',
                'name' => 'Skilled',
                'is_system' => true,
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => 'Labour performing specialized trade work requiring technical knowledge, training, or substantial practical experience.',
            ],
            [
                'code' => 'HIGHLY_SKILLED',
                'name' => 'Highly Skilled',
                'is_system' => true,
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => 'Labour performing advanced specialized work requiring significant technical expertise, certification, or extensive experience.',
            ],
        ];

        foreach ($skillCategories as $skillCategory) {
            SkillCategory::updateOrCreate(
                ['code' => $skillCategory['code']],
                $skillCategory
            );
        }
    }
}