<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('material_requirements', 'material_category_id')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->unsignedBigInteger('material_category_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_requirements', 'material_id')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->unsignedBigInteger('material_id')
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_requirements', 'required_quantity')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->decimal('required_quantity', 14, 3)
                    ->nullable()
                    ->change();
            });
        }

        if (Schema::hasColumn('material_requirements', 'fulfilled_quantity')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->decimal('fulfilled_quantity', 14, 3)
                    ->nullable()
                    ->default(0)
                    ->change();
            });
        }

        if (Schema::hasColumn('material_requirements', 'unit')) {
            Schema::table('material_requirements', function (Blueprint $table) {
                $table->string('unit', 50)
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
         * New multi-item requirements legitimately keep these
         * legacy single-material columns null. Reverting them to
         * NOT NULL could fail after new records are created.
         */
    }
};