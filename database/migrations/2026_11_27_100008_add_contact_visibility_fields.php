<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('client_support_contacts', function(Blueprint $table){ $table->boolean('show_name')->default(false); $table->boolean('show_availability')->default(false); }); }
 public function down(): void { Schema::table('client_support_contacts', fn(Blueprint $table) => $table->dropColumn(['show_name','show_availability'])); }
};
