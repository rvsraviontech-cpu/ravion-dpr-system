<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spatial_functional_unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 150);
            $table->string('name_pattern', 150)->nullable();
            $table->text('description')->nullable();
            $table->json('aliases')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'sfut_active_sort_idx');
        });

        Schema::table('project_blocks', function (Blueprint $table) {
            $table->foreignId('spatial_block_type_id')->nullable()->after('type')
                ->constrained('spatial_block_types')->nullOnDelete();
            $table->string('identifier', 80)->nullable()->after('spatial_block_type_id');
        });

        Schema::table('project_floors', function (Blueprint $table) {
            $table->foreignId('spatial_level_type_id')->nullable()->after('usage_type')
                ->constrained('spatial_level_types')->nullOnDelete();
            $table->foreignId('spatial_floor_usage_id')->nullable()->after('spatial_level_type_id')
                ->constrained('spatial_floor_usages')->nullOnDelete();
            $table->string('level_identifier', 80)->nullable()->after('spatial_floor_usage_id');
        });

        Schema::table('project_units', function (Blueprint $table) {
            $table->foreignId('spatial_functional_unit_type_id')->nullable()->after('type')
                ->constrained('spatial_functional_unit_types')->nullOnDelete();
            $table->string('identifier', 80)->nullable()->after('spatial_functional_unit_type_id');
        });

        Schema::table('project_rooms', function (Blueprint $table) {
            $table->foreignId('spatial_space_type_id')->nullable()->after('room_type')
                ->constrained('spatial_space_types')->nullOnDelete();
            $table->foreignId('spatial_space_subtype_id')->nullable()->after('spatial_space_type_id')
                ->constrained('spatial_space_subtypes')->nullOnDelete();
            $table->string('identifier', 80)->nullable()->after('spatial_space_subtype_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            $table->dropForeign(['spatial_space_subtype_id']);
            $table->dropForeign(['spatial_space_type_id']);
            $table->dropColumn(['spatial_space_subtype_id', 'spatial_space_type_id', 'identifier']);
        });

        Schema::table('project_units', function (Blueprint $table) {
            $table->dropForeign(['spatial_functional_unit_type_id']);
            $table->dropColumn(['spatial_functional_unit_type_id', 'identifier']);
        });

        Schema::table('project_floors', function (Blueprint $table) {
            $table->dropForeign(['spatial_floor_usage_id']);
            $table->dropForeign(['spatial_level_type_id']);
            $table->dropColumn(['spatial_floor_usage_id', 'spatial_level_type_id', 'level_identifier']);
        });

        Schema::table('project_blocks', function (Blueprint $table) {
            $table->dropForeign(['spatial_block_type_id']);
            $table->dropColumn(['spatial_block_type_id', 'identifier']);
        });

        Schema::dropIfExists('spatial_functional_unit_types');
    }
};
