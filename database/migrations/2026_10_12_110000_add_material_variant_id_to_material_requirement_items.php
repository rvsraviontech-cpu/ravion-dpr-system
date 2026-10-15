<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('material_requirement_items', 'material_variant_id')) {
            Schema::table('material_requirement_items', function (Blueprint $table) {
                $table->unsignedBigInteger('material_variant_id')
                    ->nullable()
                    ->after('material_type_id');
            });
        }

        // A separate foreign key ensures referenced variants cannot be deleted
        // while they are used by a material requirement item.
        Schema::table('material_requirement_items', function (Blueprint $table) {
            $table->foreign('material_variant_id', 'mri_material_variant_fk')
                ->references('id')
                ->on('material_variants')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_requirement_items', function (Blueprint $table) {
            $table->dropForeign('mri_material_variant_fk');
            $table->dropColumn('material_variant_id');
        });
    }
};
