<?php

namespace Database\Seeders;

use App\Models\WorkingStatus;
use Illuminate\Database\Seeder;

class WorkingStatusSeeder extends Seeder
{
    /**
     * Seed the Working Status Master.
     */
    public function run(): void
    {
        $workingStatuses = [
            [
                'code' => 'WORKING',
                'name' => 'Working',
                'counts_as_idle' => false,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => 'Labour is actively engaged in assigned construction or site work.',
            ],
            [
                'code' => 'IDLE',
                'name' => 'Idle',
                'counts_as_idle' => true,
                'requires_reason' => true,
                'is_system' => true,
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => 'Labour is present at site but is not engaged in productive work.',
            ],
            [
                'code' => 'WAITING_MATERIAL',
                'name' => 'Waiting for Material',
                'counts_as_idle' => true,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => 'Labour cannot proceed because required construction material is unavailable.',
            ],
            [
                'code' => 'WAITING_DRAWING',
                'name' => 'Waiting for Drawing / Approval',
                'counts_as_idle' => true,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => 'Labour cannot proceed because a required drawing, decision, or approval is pending.',
            ],
            [
                'code' => 'WAITING_WORKFRONT',
                'name' => 'Waiting for Work Front',
                'counts_as_idle' => true,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 50,
                'is_active' => true,
                'remarks' => 'Labour is available but the required work front or site area is not ready.',
            ],
            [
                'code' => 'EQUIPMENT_BREAKDOWN',
                'name' => 'Equipment Breakdown',
                'counts_as_idle' => true,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 60,
                'is_active' => true,
                'remarks' => 'Labour cannot proceed because required machinery, equipment, or tools are unavailable due to breakdown.',
            ],
            [
                'code' => 'WEATHER_DELAY',
                'name' => 'Weather Delay',
                'counts_as_idle' => true,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 70,
                'is_active' => true,
                'remarks' => 'Labour work is interrupted or prevented because of adverse weather conditions.',
            ],
            [
                'code' => 'SAFETY_HOLD',
                'name' => 'Safety Hold',
                'counts_as_idle' => true,
                'requires_reason' => true,
                'is_system' => true,
                'sort_order' => 80,
                'is_active' => true,
                'remarks' => 'Work has been stopped because of a safety concern, unsafe condition, or safety instruction.',
            ],
            [
                'code' => 'REWORK',
                'name' => 'Rework',
                'counts_as_idle' => false,
                'requires_reason' => true,
                'is_system' => true,
                'sort_order' => 90,
                'is_active' => true,
                'remarks' => 'Labour is engaged in correcting previously completed work that does not meet required specifications or quality standards.',
            ],
            [
                'code' => 'TRAINING',
                'name' => 'Training / Toolbox Talk',
                'counts_as_idle' => false,
                'requires_reason' => false,
                'is_system' => true,
                'sort_order' => 100,
                'is_active' => true,
                'remarks' => 'Labour is participating in authorized training, induction, briefing, or toolbox talk activities.',
            ],
            [
                'code' => 'MEDICAL',
                'name' => 'Medical / First Aid',
                'counts_as_idle' => false,
                'requires_reason' => true,
                'is_system' => true,
                'sort_order' => 110,
                'is_active' => true,
                'remarks' => 'Labour is temporarily unavailable for work because of medical attention or first-aid treatment.',
            ],
            [
                'code' => 'OTHER',
                'name' => 'Other',
                'counts_as_idle' => false,
                'requires_reason' => true,
                'is_system' => true,
                'sort_order' => 120,
                'is_active' => true,
                'remarks' => 'Working status does not match any predefined system status and requires an explanation.',
            ],
        ];

        foreach ($workingStatuses as $workingStatus) {
            WorkingStatus::updateOrCreate(
                [
                    'code' => $workingStatus['code'],
                ],
                $workingStatus
            );
        }
    }
}