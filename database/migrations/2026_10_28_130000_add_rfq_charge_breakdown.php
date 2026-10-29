<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rfq_quotations', function (Blueprint $table) {
            $table->decimal('auto_charges', 15, 2)->default(0);
            $table->decimal('hamali_charges', 15, 2)->default(0);
            $table->decimal('loading_charges', 15, 2)->default(0);
            $table->decimal('unloading_charges', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('rfq_quotations', function (Blueprint $table) {
            $table->dropColumn(['auto_charges', 'hamali_charges', 'loading_charges', 'unloading_charges']);
        });
    }
};
