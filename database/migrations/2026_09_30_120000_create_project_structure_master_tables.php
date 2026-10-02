<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('spatial_block_types', function(Blueprint $t){$t->id();$t->string('code',80)->unique();$t->string('name',150);$t->text('description')->nullable();$t->json('aliases')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_system')->default(false);$t->boolean('is_active')->default(true);$t->timestamps();$t->index(['is_active','sort_order'],'sbt_active_sort_idx');});
  Schema::create('spatial_level_types', function(Blueprint $t){$t->id();$t->string('code',80)->unique();$t->string('name',150);$t->string('name_pattern',150)->nullable();$t->boolean('requires_number')->default(true);$t->string('number_mode',30)->default('integer');$t->text('description')->nullable();$t->json('aliases')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_system')->default(false);$t->boolean('is_active')->default(true);$t->timestamps();$t->index(['is_active','sort_order'],'slt_active_sort_idx');});
  Schema::create('spatial_floor_usages', function(Blueprint $t){$t->id();$t->string('code',80)->unique();$t->string('name',150);$t->text('description')->nullable();$t->json('aliases')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_system')->default(false);$t->boolean('is_active')->default(true);$t->timestamps();$t->index(['is_active','sort_order'],'sfu_active_sort_idx');});
 }
 public function down(): void {Schema::dropIfExists('spatial_floor_usages');Schema::dropIfExists('spatial_level_types');Schema::dropIfExists('spatial_block_types');}
};
