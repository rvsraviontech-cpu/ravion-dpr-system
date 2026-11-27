<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('client_support_tickets', fn(Blueprint $t)=>$t->string('request_type',30)->default('Request')); }
 public function down(): void { Schema::table('client_support_tickets', fn(Blueprint $t)=>$t->dropColumn('request_type')); }
};
