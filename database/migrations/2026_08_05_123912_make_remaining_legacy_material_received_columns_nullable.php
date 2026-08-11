<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('material_receiveds', 'material_category')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('material_category')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'material_name')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('material_name')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'specification')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('specification')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'brand')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('brand')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'quantity_received')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->decimal('quantity_received', 14, 3)
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'unit')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('unit')
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
         * New multi-item receipt headers legitimately keep these
         * legacy single-material columns null. Restoring NOT NULL
         * constraints could fail once new receipts exist.
         */
    }
};