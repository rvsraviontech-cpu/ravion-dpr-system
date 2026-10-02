<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_space_types', function(Blueprint $table){
  $table->id(); $table->foreignId('spatial_space_category_id')->constrained('spatial_space_categories')->cascadeOnUpdate()->restrictOnDelete();
  $table->string('code',100)->unique(); $table->string('name',180); $table->text('description')->nullable(); $table->json('aliases')->nullable();
  $table->string('default_location_level',30)->default('room'); $table->boolean('allows_children')->default(true); $table->boolean('allows_geometry')->default(true);
  $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_system')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
  $table->index(['spatial_space_category_id','is_active','sort_order'],'sst_cat_active_sort_idx');
  $table->unique(['spatial_space_category_id','name'],'sst_cat_name_uq');
 });}
 public function down(): void { Schema::dropIfExists('spatial_space_types'); }
};
