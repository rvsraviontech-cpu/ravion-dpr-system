<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_space_subtypes', function(Blueprint $table){
  $table->id(); $table->foreignId('spatial_space_type_id')->constrained('spatial_space_types')->cascadeOnUpdate()->restrictOnDelete();
  $table->string('code',120)->unique(); $table->string('name',180); $table->text('description')->nullable(); $table->json('aliases')->nullable();
  $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_system')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
  $table->index(['spatial_space_type_id','is_active','sort_order'],'ssst_type_active_sort_idx');
  $table->unique(['spatial_space_type_id','name'],'ssst_type_name_uq');
 });}
 public function down(): void { Schema::dropIfExists('spatial_space_subtypes'); }
};
