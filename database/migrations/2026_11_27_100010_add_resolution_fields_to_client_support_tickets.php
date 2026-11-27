<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('client_support_tickets',function(Blueprint $table){ $table->date('expected_resolution_date')->nullable(); $table->text('resolution_remarks')->nullable(); }); }
 public function down(): void { Schema::table('client_support_tickets',fn(Blueprint $table)=>$table->dropColumn(['expected_resolution_date','resolution_remarks'])); }
};
