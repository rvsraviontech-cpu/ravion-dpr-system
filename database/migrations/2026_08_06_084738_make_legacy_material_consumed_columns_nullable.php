<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('material_consumeds', 'activity_division_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->unsignedBigInteger('activity_division_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'activity_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->unsignedBigInteger('activity_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'material_category_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->unsignedBigInteger('material_category_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'material_id')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'quantity_consumed')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->decimal('quantity_consumed', 14, 3)
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'unit')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->string('unit')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'wastage_quantity')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->decimal('wastage_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }

        if (Schema::hasColumn('material_consumeds', 'wastage_reason')) {
            Schema::table('material_consumeds', function (Blueprint $table) {
                $table->text('wastage_reason')
                    ->nullable()
                    ->change();
            });
        }
    }

    public function down(): void
    {
        /*
         * Intentionally left empty.
         *
         * New multi-item consumption headers legitimately keep these
         * legacy single-material columns null. Restoring NOT NULL
         * constraints could fail once new records exist.
         */
    }
};