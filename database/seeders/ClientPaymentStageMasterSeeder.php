<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientPaymentStageMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Approved canonical stage names and sequence, 19 November 2026.
        $names = [
            'Agreement / Advance Payment',
            'Foundation Works Completion',
            'Ground Floor Slab Completion',
            'First Floor Slab Completion',
            'Second Floor Slab Completion',
            'Third Floor Slab Completion',
            'Brickwork Completion',
            'Internal & External Plastering',
            'Plumbing Works',
            'Electrical Works',
            'Flooring Works',
            'Doors & Windows Installation',
            'Painting & Finishing',
            'Final Handover',
        ];

        DB::transaction(function () use ($names): void {
            foreach ($names as $index => $name) {
                $code = sprintf('PAY-%02d', $index + 1);
                // Preserve existing admin edits, activation flags and ordering on rerun.
                if (DB::table('client_payment_stage_masters')->where('stage_code', $code)->exists()) {
                    continue;
                }
                DB::table('client_payment_stage_masters')->insert([
                    'stage_code' => $code,
                    'stage_name' => $name,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
