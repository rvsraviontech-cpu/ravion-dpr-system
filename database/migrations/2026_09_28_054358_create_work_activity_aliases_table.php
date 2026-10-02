<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_activity_aliases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_activity_id')
                ->constrained('work_activities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Engineer/site terminology, alternate spelling,
             * abbreviations and common search phrases.
             *
             * Examples:
             * deshuttering
             * de shuttering
             * shutter removal
             * centering removal
             * muram filling
             * murram filling
             */
            $table->string('alias', 200);

            /*
             * Normalized representation used by search.
             * Application/seeder will populate this.
             *
             * Example:
             * "De-Shuttering" -> "de shuttering"
             */
            $table->string('normalized_alias', 200);

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['work_activity_id', 'normalized_alias'],
                'work_activity_alias_unique'
            );

            $table->index(
                ['normalized_alias', 'is_active'],
                'work_activity_alias_search_idx'
            );

            $table->index(
                ['work_activity_id', 'is_active', 'sort_order'],
                'work_activity_alias_activity_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_activity_aliases');
    }
};