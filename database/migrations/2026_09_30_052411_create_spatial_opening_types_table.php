<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_opening_types', function(Blueprint $table){
  $table->id(); $table->string('code',100)->unique(); $table->string('name',150); $table->string('opening_group',60);
  $table->text('description')->nullable(); $table->json('aliases')->nullable(); $table->boolean('can_connect_spaces')->default(false);
  $table->boolean('deduct_from_wall_area')->default(true); $table->unsignedInteger('sort_order')->default(0);
  $table->boolean('is_system')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
  $table->index(['opening_group','is_active','sort_order'],'sot_group_active_sort_idx'); $table->index('name');
 });}
 public function down(): void { Schema::dropIfExists('spatial_opening_types'); }
};
