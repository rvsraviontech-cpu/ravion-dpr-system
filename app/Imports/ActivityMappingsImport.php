<?php

namespace App\Imports;

use App\Models\Activity;
use App\Models\ActivityMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ActivityMappingsImport implements ToCollection
{
    public int $importedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            if ($index == 0) {
                continue;
            }

            $rhCode = trim((string) ($row[0] ?? ''));
            $activityName = trim((string) ($row[1] ?? ''));
            $unit = trim((string) ($row[2] ?? ''));
            $odooTypeCode = trim((string) ($row[3] ?? ''));
            $odooType = trim((string) ($row[4] ?? ''));
            $workStage = trim((string) ($row[5] ?? 'General'));

            if (empty($rhCode) && empty($activityName)) {
                continue;
            }

            if (empty($activityName)) {
                continue;
            }

            $activity = Activity::firstOrCreate(
                [
                    'activity_name' => $activityName,
                ],
                [
                    'unit' => $unit ?: 'Nos',
                    'work_stage' => $workStage ?: 'General',
                    'is_active' => true,
                ]
            );

            ActivityMapping::updateOrCreate(
                [
                    'division_code' => 'RH',
                    'rh_cost_code' => $rhCode ?: 'NO-CODE-' . $index,
                ],
                [
                    'activity_id' => $activity->id,
                    'activity_name' => $activityName,
                    'unit' => $unit ?: 'Nos',
                    'odoo_type_code' => $odooTypeCode,
                    'odoo_type' => $odooType,
                    'work_stage' => $workStage ?: 'General',
                    'is_active' => true,
                ]
            );

            $this->importedCount++;
        }
    }
}