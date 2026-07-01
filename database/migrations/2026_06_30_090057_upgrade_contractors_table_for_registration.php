<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('contractor_code')->nullable()->unique()->after('id');

            $table->string('company_name')->nullable()->after('contractor_name');
            $table->string('alternate_mobile', 20)->nullable()->after('mobile');
            $table->string('email')->nullable()->after('alternate_mobile');

            $table->foreignId('work_stage_id')->nullable()->after('work_category')->constrained('work_stages')->nullOnDelete();
            $table->foreignId('contractor_service_category_id')->nullable()->after('work_stage_id')->constrained('contractor_service_categories')->nullOnDelete();

            $table->string('city', 150)->nullable()->after('contractor_service_category_id');
            $table->string('district', 150)->nullable()->after('city');
            $table->string('state', 150)->nullable()->after('district');
            $table->string('pincode', 20)->nullable()->after('state');
            $table->text('address')->nullable()->after('pincode');

            $table->string('gst_number', 50)->nullable()->after('address');
            $table->string('pan_number', 50)->nullable()->after('gst_number');
            $table->string('aadhaar_number', 50)->nullable()->after('pan_number');
            $table->string('license_number', 100)->nullable()->after('aadhaar_number');

            $table->unsignedTinyInteger('rating')->nullable()->after('license_number');
            $table->unsignedSmallInteger('experience_years')->nullable()->after('rating');
            $table->boolean('is_preferred')->default(false)->after('experience_years');

            $table->text('remarks')->nullable()->after('status');

            $table->index(['work_stage_id', 'contractor_service_category_id']);
            $table->index(['city', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropForeign(['work_stage_id']);
            $table->dropForeign(['contractor_service_category_id']);

            $table->dropColumn([
                'contractor_code',
                'company_name',
                'alternate_mobile',
                'email',
                'work_stage_id',
                'contractor_service_category_id',
                'city',
                'district',
                'state',
                'pincode',
                'address',
                'gst_number',
                'pan_number',
                'aadhaar_number',
                'license_number',
                'rating',
                'experience_years',
                'is_preferred',
                'remarks',
            ]);
        });
    }
};