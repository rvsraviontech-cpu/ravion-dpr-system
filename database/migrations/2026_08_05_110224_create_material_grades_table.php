<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_grades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_type_id')
                ->constrained('material_types')
                ->cascadeOnDelete();

            /*
             * Examples:
             * 53 Grade
             * Fe500
             * SDR 11
             * 10 kA
             * Class B
             */
            $table->string('grade_name');

            /*
             * Internal reference only.
             */
            $table->string('grade_code')
                ->nullable();

            $table->unsignedInteger('sequence')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->text('remarks')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['material_type_id', 'grade_name'],
                'material_grade_unique'
            );

            $table->index(
                ['material_type_id', 'is_active'],
                'material_grades_type_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_grades');
    }
};