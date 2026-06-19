<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {

            $table->string('vendor_code')->nullable()->after('id');

            $table->foreignId('material_category_id')
                ->nullable()
                ->after('vendor_code')
                ->constrained('material_categories')
                ->nullOnDelete();

            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();

            $table->string('alternate_mobile')->nullable();

            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();

            $table->string('payment_terms')->nullable();
            $table->integer('credit_days')->default(0);

            $table->boolean('is_active')->default(true);

            $table->text('remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {

            $table->dropConstrainedForeignId('material_category_id');

            $table->dropColumn([
                'vendor_code',
                'gst_number',
                'pan_number',
                'alternate_mobile',
                'city',
                'state',
                'pincode',
                'payment_terms',
                'credit_days',
                'is_active',
                'remarks'
            ]);

        });
    }
};