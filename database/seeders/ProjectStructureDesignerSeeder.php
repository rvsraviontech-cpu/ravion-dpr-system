<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectStructureDesignerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['FLAT', 'Flat / Apartment', 'Flat {id}', ['Apartment Unit']],
            ['ENTIRE_FLOOR_UNIT', 'Entire Floor Unit', '{id}', ['Full Floor Unit']],
            ['VILLA_PORTION', 'Villa Portion', 'Villa {id}', []],
            ['SHOP', 'Shop', 'Shop {id}', ['Retail Shop']],
            ['SHOWROOM', 'Showroom', 'Showroom {id}', []],
            ['OFFICE', 'Office', 'Office {id}', ['Office Unit']],
            ['BANK', 'Bank / Financial Unit', 'Bank {id}', ['Financial Institution']],
            ['CLASSROOM_CLUSTER', 'Classroom / Academic Unit', 'Academic Unit {id}', []],
            ['LABORATORY', 'Laboratory Unit', 'Laboratory {id}', ['Lab']],
            ['HOSPITAL_DEPARTMENT', 'Hospital Department', '{id}', ['Clinical Department']],
            ['WARD', 'Hospital Ward', 'Ward {id}', []],
            ['PATIENT_ROOM_CLUSTER', 'Patient Room Cluster', 'Patient Unit {id}', []],
            ['OPD_UNIT', 'OPD Unit', 'OPD {id}', []],
            ['OT_UNIT', 'Operation Theatre Unit', 'OT {id}', []],
            ['BANQUET_HALL', 'Banquet / Function Hall', 'Hall {id}', ['Function Hall']],
            ['HOTEL_GUEST_UNIT', 'Hotel Guest Unit', 'Guest Unit {id}', []],
            ['KITCHEN_UNIT', 'Kitchen / Food Service Unit', 'Kitchen {id}', []],
            ['STORE_UNIT', 'Store / Storage Unit', 'Store {id}', []],
            ['WAREHOUSE_UNIT', 'Warehouse Unit', 'Warehouse {id}', []],
            ['SERVICE_UNIT', 'Service / Utility Unit', 'Service Unit {id}', []],
            ['AMENITY_UNIT', 'Amenity Unit', '{id}', []],
            ['OTHER', 'Other / Custom', '{id}', ['Custom']],
        ];

        foreach ($rows as $i => [$code, $name, $pattern, $aliases]) {
            DB::table('spatial_functional_unit_types')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'name_pattern' => $pattern,
                    'description' => null,
                    'aliases' => $aliases ? json_encode($aliases) : null,
                    'sort_order' => ($i + 1) * 10,
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
