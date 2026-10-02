<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('project_wall_openings', function(Blueprint $table){
  $table->id(); $table->foreignId('project_room_wall_id')->constrained('project_room_walls')->cascadeOnUpdate()->cascadeOnDelete();
  $table->foreignId('spatial_opening_type_id')->nullable()->constrained('spatial_opening_types')->cascadeOnUpdate()->nullOnDelete();
  $table->string('opening_code',50)->nullable(); $table->string('name',180); $table->string('opening_group',60)->nullable();
  $table->string('dimension_unit',10)->default('ft'); $table->string('area_unit',15)->default('sq.ft'); $table->decimal('width',12,3)->nullable();
  $table->decimal('height',12,3)->nullable(); $table->decimal('sill_height',12,3)->nullable(); $table->decimal('lintel_height',12,3)->nullable();
  $table->unsignedInteger('quantity')->default(1); $table->decimal('calculated_area',15,3)->nullable();
  $table->foreignId('connects_to_project_room_id')->nullable()->constrained('project_rooms')->cascadeOnUpdate()->nullOnDelete();
  $table->foreignId('connects_to_project_subspace_id')->nullable()->constrained('project_subspaces')->cascadeOnUpdate()->nullOnDelete();
  $table->string('material_type',120)->nullable(); $table->string('operation_type',80)->nullable(); $table->boolean('deduct_from_wall_area')->default(true);
  $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->text('remarks')->nullable(); $table->timestamps();
  $table->index(['project_room_wall_id','is_active','sort_order'],'pwo_wall_active_sort_idx'); $table->index('connects_to_project_room_id','pwo_connect_room_idx');
 });}
 public function down(): void { Schema::dropIfExists('project_wall_openings'); }
};
