<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_measurement_zones', function(Blueprint $table){
  $table->id(); $table->string('code',120)->unique(); $table->string('name',180); $table->string('zone_group',80);
  $table->string('measurement_basis',40)->default('area'); $table->string('default_unit',30)->nullable(); $table->text('description')->nullable();
  $table->json('aliases')->nullable(); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_system')->default(false);
  $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['zone_group','is_active','sort_order'],'smz_group_active_sort_idx'); $table->index('name');
 });}
 public function down(): void { Schema::dropIfExists('spatial_measurement_zones'); }
};
