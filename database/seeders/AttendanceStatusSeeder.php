<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Seed the attendance status master.
     */
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'P',
                'name' => 'Present',
                'short_name' => 'Present',
                'counts_as_present' => true,
                'counts_as_absent' => false,
                'payable_factor' => 1.00,
                'allows_normal_hours' => true,
                'allows_ot_hours' => true,
                'requires_working_status' => true,
                'is_system' => true,
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => 'Labour was present for the full shift.',
            ],
            [
                'code' => 'A',
                'name' => 'Absent',
                'short_name' => 'Absent',
                'counts_as_present' => false,
                'counts_as_absent' => true,
                'payable_factor' => 0.00,
                'allows_normal_hours' => false,
                'allows_ot_hours' => false,
                'requires_working_status' => false,
                'is_system' => true,
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => 'Labour was absent.',
            ],
            [
                'code' => 'HD',
                'name' => 'Half Day',
                'short_name' => 'Half Day',
                'counts_as_present' => true,
                'counts_as_absent' => false,
                'payable_factor' => 0.50,
                'allows_normal_hours' => true,
                'allows_ot_hours' => false,
                'requires_working_status' => true,
                'is_system' => true,
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => 'Labour worked for half of the normal shift.',
            ],
            [
                'code' => 'L',
                'name' => 'Leave',
                'short_name' => 'Leave',
                'counts_as_present' => false,
                'counts_as_absent' => true,
                'payable_factor' => 0.00,
                'allows_normal_hours' => false,
                'allows_ot_hours' => false,
                'requires_working_status' => false,
                'is_system' => true,
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => 'Labour was on leave.',
            ],
            [
                'code' => 'WO',
                'name' => 'Weekly Off',
                'short_name' => 'Weekly Off',
                'counts_as_present' => false,
                'counts_as_absent' => false,
                'payable_factor' => 0.00,
                'allows_normal_hours' => false,
                'allows_ot_hours' => false,
                'requires_working_status' => false,
                'is_system' => true,
                'sort_order' => 50,
                'is_active' => true,
                'remarks' => 'Scheduled weekly off.',
            ],
            [
                'code' => 'H',
                'name' => 'Holiday',
                'short_name' => 'Holiday',
                'counts_as_present' => false,
                'counts_as_absent' => false,
                'payable_factor' => 0.00,
                'allows_normal_hours' => false,
                'allows_ot_hours' => false,
                'requires_working_status' => false,
                'is_system' => true,
                'sort_order' => 60,
                'is_active' => true,
                'remarks' => 'Declared holiday.',
            ],
            [
                'code' => 'RS',
                'name' => 'Rain Stop',
                'short_name' => 'Rain Stop',
                'counts_as_present' => true,
                'counts_as_absent' => false,
                'payable_factor' => 1.00,
                'allows_normal_hours' => true,
                'allows_ot_hours' => false,
                'requires_working_status' => true,
                'is_system' => true,
                'sort_order' => 70,
                'is_active' => true,
                'remarks' => 'Labour attended but work was affected by rain.',
            ],
            [
                'code' => 'ID',
                'name' => 'Idle',
                'short_name' => 'Idle',
                'counts_as_present' => true,
                'counts_as_absent' => false,
                'payable_factor' => 1.00,
                'allows_normal_hours' => true,
                'allows_ot_hours' => false,
                'requires_working_status' => true,
                'is_system' => true,
                'sort_order' => 80,
                'is_active' => true,
                'remarks' => 'Labour attended but remained idle.',
            ],
            [
                'code' => 'TR',
                'name' => 'Transferred',
                'short_name' => 'Transferred',
                'counts_as_present' => true,
                'counts_as_absent' => false,
                'payable_factor' => 1.00,
                'allows_normal_hours' => true,
                'allows_ot_hours' => false,
                'requires_working_status' => true,
                'is_system' => true,
                'sort_order' => 90,
                'is_active' => true,
                'remarks' => 'Labour attended and was transferred to another project or location.',
            ],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}