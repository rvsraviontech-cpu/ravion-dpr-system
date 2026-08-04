<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'labour_attendance_details',
            function (Blueprint $table): void {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Attendance References
                |--------------------------------------------------------------------------
                */

                $table->foreignId('labour_attendance_id')
                    ->constrained('labour_attendances')
                    ->cascadeOnDelete();

                $table->foreignId('labour_id')
                    ->constrained('labours')
                    ->restrictOnDelete();

                $table->foreignId('attendance_status_id')
                    ->constrained('attendance_statuses')
                    ->restrictOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Labour Snapshot
                |--------------------------------------------------------------------------
                */

                $table->foreignId('labour_category_id')
                    ->nullable()
                    ->constrained('labour_categories')
                    ->nullOnDelete();

                $table->foreignId('labour_type_id')
                    ->nullable()
                    ->constrained('labour_types')
                    ->nullOnDelete();

                $table->foreignId('designation_role_id')
                    ->nullable()
                    ->constrained('designation_roles')
                    ->nullOnDelete();

                $table->foreignId('skill_category_id')
                    ->nullable()
                    ->constrained('skill_categories')
                    ->nullOnDelete();

                $table->foreignId('contractor_id')
                    ->nullable()
                    ->constrained('contractors')
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Time and Hours
                |--------------------------------------------------------------------------
                */

                $table->time('check_in_time')
                    ->nullable();

                $table->time('check_out_time')
                    ->nullable();

                $table->decimal('normal_hours', 5, 2)
                    ->default(0);

                $table->decimal('ot_hours', 5, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Operational Information
                |--------------------------------------------------------------------------
                */

                $table->string('attendance_source', 30)
                    ->default('manual');

                $table->text('remarks')
                    ->nullable();

                $table->boolean('is_active')
                    ->default(true);

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Constraints and Indexes
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'labour_attendance_id',
                        'labour_id',
                    ],
                    'lad_attendance_labour_unique'
                );

                $table->index(
                    [
                        'labour_id',
                        'attendance_status_id',
                    ],
                    'lad_labour_status_idx'
                );

                $table->index(
                    [
                        'designation_role_id',
                        'attendance_status_id',
                    ],
                    'lad_designation_status_idx'
                );

                $table->index(
                    'contractor_id',
                    'lad_contractor_idx'
                );
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_attendance_details');
    }
};