<?php

namespace App\Console\Commands;

use App\Models\MachineryEquipment;
use App\Models\MachineryEquipmentAllocation;
use App\Services\MachineryEquipmentAllocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncMachineryEquipmentOpeningAllocations extends Command
{
    protected $signature = 'machinery:sync-opening-allocations
                            {--dry-run : Show what would be created without changing the database}';

    protected $description = 'Create opening allocation ledger entries for machinery/equipment that already has a current project but no allocation history.';

    public function handle(
        MachineryEquipmentAllocationService $allocationService
    ): int {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Machinery & Equipment Opening Allocation Sync');
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN — no database changes will be made.');
            $this->newLine();
        }

        $equipmentQuery = MachineryEquipment::query()
            ->with([
                'machineryTool',
                'currentProject',
            ])
            ->whereNotNull('current_project_id')
            ->orderBy('id');

        $total = (clone $equipmentQuery)->count();

        if ($total === 0) {
            $this->info('No equipment with an existing current project was found.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $equipmentQuery->chunkById(
            100,
            function ($equipmentItems) use (
                $allocationService,
                $dryRun,
                &$created,
                &$skipped,
                &$failed
            ) {
                foreach ($equipmentItems as $equipment) {
                    $existingAllocation = MachineryEquipmentAllocation::query()
                        ->where(
                            'machinery_equipment_id',
                            $equipment->id
                        )
                        ->exists();

                    if ($existingAllocation) {
                        $this->line(
                            sprintf(
                                'SKIP  %-20s Allocation history already exists.',
                                $equipment->equipment_code
                            )
                        );

                        $skipped++;

                        continue;
                    }

                    if (
                        !$equipment->current_project_id
                        || !$equipment->currentProject
                    ) {
                        $this->warn(
                            sprintf(
                                'SKIP  %-20s Current project is missing or invalid.',
                                $equipment->equipment_code
                            )
                        );

                        $skipped++;

                        continue;
                    }

                    $quantity = (float) $equipment->quantity;

                    if ($quantity <= 0) {
                        $this->warn(
                            sprintf(
                                'SKIP  %-20s Registered quantity is invalid.',
                                $equipment->equipment_code
                            )
                        );

                        $skipped++;

                        continue;
                    }

                    $projectName =
                        $equipment->currentProject->project_name
                        ?? ('Project #' . $equipment->current_project_id);

                    $displayQuantity = rtrim(
                        rtrim(
                            number_format($quantity, 3, '.', ''),
                            '0'
                        ),
                        '.'
                    );

                    if ($dryRun) {
                        $this->line(
                            sprintf(
                                'CREATE %-20s Company Yard -> %s | %s %s',
                                $equipment->equipment_code,
                                $projectName,
                                $displayQuantity,
                                $equipment->unit
                            )
                        );

                        $created++;

                        continue;
                    }

                    try {
                        DB::transaction(function () use (
                            $equipment,
                            $allocationService
                        ) {
                            /*
                             * Recheck inside the transaction so that the
                             * command remains safe if two sync processes
                             * somehow run at the same time.
                             */
                            $alreadyExists =
                                MachineryEquipmentAllocation::query()
                                    ->where(
                                        'machinery_equipment_id',
                                        $equipment->id
                                    )
                                    ->lockForUpdate()
                                    ->exists();

                            if ($alreadyExists) {
                                return;
                            }

                            $movementDate =
                                $equipment->commissioned_date
                                ?? $equipment->created_at?->toDateString()
                                ?? now()->toDateString();

                            $allocationService->createMovement(
                                $equipment,
                                [
                                    'movement_type' =>
                                        MachineryEquipmentAllocation::TYPE_INITIAL,

                                    'from_project_id' => null,

                                    'to_project_id' =>
                                        $equipment->current_project_id,

                                    'quantity' =>
                                        (float) $equipment->quantity,

                                    'movement_date' =>
                                        $movementDate,

                                    'movement_time' => null,

                                    'status' =>
                                        MachineryEquipmentAllocation::STATUS_RECEIVED,

                                    'reference_number' =>
                                        'OPENING-SYNC',

                                    'remarks' =>
                                        'Opening allocation created from existing equipment current project during allocation-ledger migration.',
                                ]
                            );
                        });

                        $this->info(
                            sprintf(
                                'DONE  %-20s Company Yard -> %s | %s %s',
                                $equipment->equipment_code,
                                $projectName,
                                $displayQuantity,
                                $equipment->unit
                            )
                        );

                        $created++;
                    } catch (Throwable $e) {
                        $this->error(
                            sprintf(
                                'FAIL  %-20s %s',
                                $equipment->equipment_code,
                                $e->getMessage()
                            )
                        );

                        $failed++;
                    }
                }
            }
        );

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Equipment examined', $total],
                [$dryRun ? 'Would create' : 'Created', $created],
                ['Skipped', $skipped],
                ['Failed', $failed],
            ]
        );

        if ($failed > 0) {
            $this->newLine();
            $this->error(
                'Opening allocation sync completed with one or more failures.'
            );

            return self::FAILURE;
        }

        $this->newLine();

        if ($dryRun) {
            $this->info(
                'Dry run completed. No database records were changed.'
            );
        } else {
            $this->info(
                'Opening allocation synchronization completed successfully.'
            );
        }

        return self::SUCCESS;
    }
}