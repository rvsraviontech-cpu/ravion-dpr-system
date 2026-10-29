<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('delivery_contact_name', 150)->nullable()->after('delivery_address');
            $table->string('delivery_contact_phone', 30)->nullable()->after('delivery_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_contact_name', 'delivery_contact_phone']);
        });
    }
};
