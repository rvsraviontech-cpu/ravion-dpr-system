<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('material_receiveds', 'material_category_id')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->unsignedBigInteger('material_category_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'material_id')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')
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

        if (Schema::hasColumn('material_receiveds', 'specification')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->string('specification')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'accepted_quantity')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->decimal('accepted_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'short_quantity')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->decimal('short_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'damaged_quantity')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->decimal('damaged_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }

        if (Schema::hasColumn('material_receiveds', 'rejected_quantity')) {
            Schema::table('material_receiveds', function (Blueprint $table) {
                $table->decimal('rejected_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }
    }

    public function down(): void
    {
        /*
         * We intentionally do not restore the legacy NOT NULL constraints.
         *
         * Once multi-item receipts exist, reverting these columns to NOT NULL
         * could fail because new receipt headers will legitimately contain
         * null values in the old single-material columns.
         */
    }
};