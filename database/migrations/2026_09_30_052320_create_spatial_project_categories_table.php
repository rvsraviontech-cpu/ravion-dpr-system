<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('spatial_project_categories', function(Blueprint $table){
  $table->id(); $table->string('code',50)->unique(); $table->string('name',150); $table->text('description')->nullable();
  $table->json('aliases')->nullable(); $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_system')->default(false);
  $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['is_active','sort_order']); $table->index('name');
 });}
 public function down(): void { Schema::dropIfExists('spatial_project_categories'); }
};
