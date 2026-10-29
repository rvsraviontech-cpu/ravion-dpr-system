<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rfq_quotations', function (Blueprint $table) {
            $table->boolean('gst_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('rfq_quotations', function (Blueprint $table) {
            $table->dropColumn('gst_enabled');
        });
    }
};
