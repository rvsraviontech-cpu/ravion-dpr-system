<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_component_types', function(Blueprint $table){
  $table->id(); $table->string('code',120)->unique(); $table->string('name',180); $table->string('component_group',80);
  $table->text('description')->nullable(); $table->json('aliases')->nullable(); $table->boolean('supports_openings')->default(false);
  $table->boolean('supports_connection')->default(false); $table->boolean('is_measurable')->default(true); $table->unsignedInteger('sort_order')->default(0);
  $table->boolean('is_system')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
  $table->index(['component_group','is_active','sort_order'],'sct_group_active_sort_idx'); $table->index('name');
 });}
 public function down(): void { Schema::dropIfExists('spatial_component_types'); }
};
