<?php

namespace Database\Seeders;

use App\Models\DesignationRole;
use App\Models\Labour;
use App\Models\LabourGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabourGroupSeeder extends Seeder
{
    /**
     * Seed Labour Groups and assign existing labourers
     * according to their current Designation Role.
     */
    public function run(): void
    {
        $groups = [

            [
                'designation_code' => 'TELUGU_MASON',
                'group_code' => 'TELUGU_MASON',
                'group_name' => 'Telugu Mason',
                'sort_order' => 10,
            ],

            [
                'designation_code' => 'TELUGU_FEMALE_MASON_HELPER',
                'group_code' => 'TELUGU_FEMALE_MASON_HELPER',
                'group_name' => 'Telugu Female Mason Helper',
                'sort_order' => 20,
            ],

            [
                'designation_code' => 'TELUGU_MALE_MASON_HELPER',
                'group_code' => 'TELUGU_MALE_MASON_HELPER',
                'group_name' => 'Telugu Male Mason Helper',
                'sort_order' => 30,
            ],

            [
                'designation_code' => 'HINDI_MASON',
                'group_code' => 'HINDI_MASON',
                'group_name' => 'Hindi Mason',
                'sort_order' => 40,
            ],

            [
                'designation_code' => 'HINDI_FEMALE_MASON_HELPER',
                'group_code' => 'HINDI_FEMALE_MASON_HELPER',
                'group_name' => 'Hindi Female Mason Helper',
                'sort_order' => 50,
            ],

            [
                'designation_code' => 'HINDI_MALE_MASON_HELPER',
                'group_code' => 'HINDI_MALE_MASON_HELPER',
                'group_name' => 'Hindi Male Mason Helper',
                'sort_order' => 60,
            ],

            [
                'designation_code' => 'MASON',
                'group_code' => 'MASON',
                'group_name' => 'Mason',
                'sort_order' => 70,
            ],

            [
                'designation_code' => 'ELECTRICIANS',
                'group_code' => 'ELECTRICIAN',
                'group_name' => 'Electrician',
                'sort_order' => 80,
            ],

            [
                'designation_code' => 'PLUMBER',
                'group_code' => 'PLUMBER',
                'group_name' => 'Plumber',
                'sort_order' => 90,
            ],
        ];

        DB::transaction(function () use ($groups): void {

            foreach ($groups as $item) {

                $labourGroup = LabourGroup::updateOrCreate(
                    [
                        'code' => $item['group_code'],
                    ],
                    [
                        'name' => $item['group_name'],
                        'sort_order' => $item['sort_order'],
                        'is_active' => true,
                    ]
                );

                $designationRole = DesignationRole::query()
                    ->where(
                        'code',
                        $item['designation_code']
                    )
                    ->first();

                if (! $designationRole) {
                    continue;
                }

                Labour::query()
                    ->where(
                        'designation_role_id',
                        $designationRole->id
                    )
                    ->update([
                        'labour_group_id' => $labourGroup->id,
                    ]);
            }
        });
    }
}