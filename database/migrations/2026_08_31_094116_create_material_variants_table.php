<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_type_id');
            $table->string('variant_code', 100)->unique();
            $table->string('variant_name', 255);

            $table->foreignId('material_specification_id')->nullable();
            $table->foreignId('material_grade_id')->nullable();
            $table->foreignId('unit_master_id')->nullable();

            $table->string('size_dimension', 150)->nullable();
            $table->string('finish', 150)->nullable();
            $table->string('colour_shade', 150)->nullable();

            $table->string('search_aliases', 500)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('material_type_id', 'mv_material_type_fk')
                ->references('id')
                ->on('material_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('material_specification_id', 'mv_specification_fk')
                ->references('id')
                ->on('material_specifications')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('material_grade_id', 'mv_grade_fk')
                ->references('id')
                ->on('material_grades')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('unit_master_id', 'mv_unit_fk')
                ->references('id')
                ->on('unit_masters')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(
                ['material_type_id', 'is_active', 'sort_order'],
                'mv_type_active_sort_idx'
            );

            $table->index(
                ['material_specification_id', 'material_grade_id'],
                'mv_spec_grade_idx'
            );

            $table->index('variant_name', 'mv_variant_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_variants');
    }
};
