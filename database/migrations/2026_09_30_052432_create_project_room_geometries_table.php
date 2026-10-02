<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('project_room_geometries', function(Blueprint $table){
  $table->id(); $table->foreignId('project_room_id')->unique()->constrained('project_rooms')->cascadeOnUpdate()->cascadeOnDelete();
  $table->foreignId('spatial_space_type_id')->nullable()->constrained('spatial_space_types')->cascadeOnUpdate()->nullOnDelete();
  $table->foreignId('spatial_space_subtype_id')->nullable()->constrained('spatial_space_subtypes')->cascadeOnUpdate()->nullOnDelete();
  $table->string('shape_type',40)->default('rectangular'); $table->string('dimension_unit',10)->default('ft'); $table->string('area_unit',15)->default('sq.ft');
  $table->decimal('length',12,3)->nullable(); $table->decimal('width',12,3)->nullable(); $table->decimal('clear_height',12,3)->nullable();
  $table->decimal('calculated_floor_area',15,3)->nullable(); $table->decimal('manual_floor_area',15,3)->nullable(); $table->boolean('use_manual_floor_area')->default(false);
  $table->decimal('ceiling_area',15,3)->nullable(); $table->decimal('perimeter',15,3)->nullable(); $table->decimal('volume',18,3)->nullable();
  $table->text('geometry_notes')->nullable(); $table->timestamps(); $table->index(['spatial_space_type_id','spatial_space_subtype_id'],'prg_space_type_subtype_idx');
 });}
 public function down(): void { Schema::dropIfExists('project_room_geometries'); }
};
