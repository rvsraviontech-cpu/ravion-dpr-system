<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_activity_project_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_activity_id')
                ->constrained('work_activities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('work_project_type_id')
                ->constrained('work_project_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['work_activity_id', 'work_project_type_id'],
                'work_activity_project_type_unique'
            );

            $table->index(
                ['work_project_type_id', 'work_activity_id'],
                'work_project_type_activity_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_activity_project_types');
    }
};