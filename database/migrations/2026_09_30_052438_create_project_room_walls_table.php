<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('project_room_walls', function(Blueprint $table){
  $table->id(); $table->foreignId('project_room_id')->constrained('project_rooms')->cascadeOnUpdate()->cascadeOnDelete();
  $table->foreignId('spatial_component_type_id')->nullable()->constrained('spatial_component_types')->cascadeOnUpdate()->nullOnDelete();
  $table->string('wall_code',50); $table->string('name',180)->nullable(); $table->string('orientation',30)->nullable(); $table->string('wall_function',120)->nullable();
  $table->string('dimension_unit',10)->default('ft'); $table->string('area_unit',15)->default('sq.ft'); $table->decimal('length',12,3)->nullable();
  $table->decimal('height',12,3)->nullable(); $table->decimal('gross_area',15,3)->nullable(); $table->decimal('openings_area',15,3)->nullable(); $table->decimal('net_area',15,3)->nullable();
  $table->boolean('is_external')->default(false); $table->boolean('is_shared')->default(false); $table->unsignedInteger('sort_order')->default(0);
  $table->boolean('is_active')->default(true); $table->text('remarks')->nullable(); $table->timestamps();
  $table->unique(['project_room_id','wall_code'],'prw_room_wall_code_uq'); $table->index(['project_room_id','is_active','sort_order'],'prw_room_active_sort_idx');
 });}
 public function down(): void { Schema::dropIfExists('project_room_walls'); }
};
