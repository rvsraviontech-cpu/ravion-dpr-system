<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkDoneWeeklyAttendancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [
            [
                'name' => 'work_done.view',
                'module' => 'Work Done',
                'description' => 'View Work Done entries and module navigation.',
                'is_active' => true,
            ],
            [
                'name' => 'weekly_attendance.view',
                'module' => 'Labour Attendance',
                'description' => 'View Admin/PMO Weekly Attendance bulk-entry screen.',
                'is_active' => true,
            ],
        ];

        foreach ($permissions as $permission) {
            $existing = DB::table('permissions')
                ->where('name', $permission['name'])
                ->first();

            if ($existing) {
                DB::table('permissions')
                    ->where('id', $existing->id)
                    ->update([
                        'module' => $permission['module'],
                        'description' => $permission['description'],
                        'is_active' => $permission['is_active'],
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'module' => $permission['module'],
                'description' => $permission['description'],
                'is_active' => $permission['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
