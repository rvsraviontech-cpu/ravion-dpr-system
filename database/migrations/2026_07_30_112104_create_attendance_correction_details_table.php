<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_correction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained('attendance_corrections')->cascadeOnDelete();
            $table->unsignedBigInteger('labour_attendance_detail_id')->nullable();

$table->foreign(
    'labour_attendance_detail_id',
    'acrd_att_detail_fk'
)
->references('id')
->on('labour_attendance_details')
->nullOnDelete();
            $table->foreignId('labour_id')->constrained('labours')->restrictOnDelete();
            $table->string('action_type', 30)->default('modify')->index();

            $table->foreignId('old_attendance_status_id')->nullable()->constrained('attendance_statuses')->nullOnDelete();
            $table->foreignId('new_attendance_status_id')->nullable()->constrained('attendance_statuses')->nullOnDelete();
            $table->foreignId('old_working_status_id')->nullable()->constrained('working_statuses')->nullOnDelete();
            $table->foreignId('new_working_status_id')->nullable()->constrained('working_statuses')->nullOnDelete();

            $table->time('old_check_in_time')->nullable();
            $table->time('new_check_in_time')->nullable();
            $table->time('old_check_out_time')->nullable();
            $table->time('new_check_out_time')->nullable();
            $table->decimal('old_normal_hours', 6, 2)->nullable();
            $table->decimal('new_normal_hours', 6, 2)->nullable();
            $table->decimal('old_ot_hours', 6, 2)->nullable();
            $table->decimal('new_ot_hours', 6, 2)->nullable();
            $table->text('old_remarks')->nullable();
            $table->text('new_remarks')->nullable();

            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->text('line_reason');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attendance_correction_id', 'labour_id'], 'acrd_correction_labour_index');
            $table->index(['labour_attendance_detail_id', 'action_type'], 'acrd_attendance_detail_action_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_correction_details');
    }
};