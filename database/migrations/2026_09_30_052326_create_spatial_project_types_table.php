<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_project_types', function(Blueprint $table){
  $table->id(); $table->foreignId('spatial_project_category_id')->constrained('spatial_project_categories')->cascadeOnUpdate()->restrictOnDelete();
  $table->string('code',80)->unique(); $table->string('name',150); $table->text('description')->nullable(); $table->json('aliases')->nullable();
  $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_system')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
  $table->index(['spatial_project_category_id','is_active','sort_order'],'spt_cat_active_sort_idx');
  $table->unique(['spatial_project_category_id','name'],'spt_cat_name_uq');
 });}
 public function down(): void { Schema::dropIfExists('spatial_project_types'); }
};
