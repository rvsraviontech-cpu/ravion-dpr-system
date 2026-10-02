<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('project_measurement_zones', function(Blueprint $table){
  $table->id(); $table->foreignId('project_room_id')->constrained('project_rooms')->cascadeOnUpdate()->cascadeOnDelete();
  $table->foreignId('project_room_wall_id')->nullable()->constrained('project_room_walls')->cascadeOnUpdate()->cascadeOnDelete();
  $table->foreignId('spatial_measurement_zone_id')->nullable()->constrained('spatial_measurement_zones')->cascadeOnUpdate()->nullOnDelete();
  $table->string('name',180); $table->string('measurement_basis',40)->default('area'); $table->string('unit',30)->nullable();
  $table->decimal('length',12,3)->nullable(); $table->decimal('width',12,3)->nullable(); $table->decimal('height',12,3)->nullable();
  $table->decimal('quantity',15,3)->nullable(); $table->decimal('calculated_value',18,3)->nullable(); $table->decimal('manual_value',18,3)->nullable();
  $table->boolean('use_manual_value')->default(false); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true);
  $table->text('remarks')->nullable(); $table->timestamps(); $table->index(['project_room_id','is_active','sort_order'],'pmz_room_active_sort_idx');
  $table->index(['project_room_wall_id','is_active'],'pmz_wall_active_idx');
 });}
 public function down(): void { Schema::dropIfExists('project_measurement_zones'); }
};
