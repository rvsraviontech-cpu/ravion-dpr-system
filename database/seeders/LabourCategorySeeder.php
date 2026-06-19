<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabourCategory;

class LabourCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            'Structural & Civil',

            'Finishing & Interior',

            'MEP (Mechanical, Electrical, Plumbing)',

            'Heavy Equipment & Machinery',

            'Specialized Trades',

            'Support & General',

        ];

        foreach ($categories as $category) {

            LabourCategory::updateOrCreate(
                ['category_name' => $category]
            );

        }
    }
}