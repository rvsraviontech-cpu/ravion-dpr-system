<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasColumn('work_done_item_labours','labour_group_id')) {
   Schema::table('work_done_item_labours', function(Blueprint $t) {$t->unsignedBigInteger('labour_group_id')->nullable()->index();});
  }
  if (!Schema::hasTable('work_done_reported_materials')) {
   Schema::create('work_done_reported_materials', function(Blueprint $t) {
    $t->id(); $t->foreignId('work_done_item_id')->constrained('work_done_items')->cascadeOnDelete();
    $t->string('stock_key',255); $t->unsignedBigInteger('material_type_id');
    $t->unsignedBigInteger('brand_master_id')->nullable(); $t->unsignedBigInteger('material_specification_id')->nullable();
    $t->unsignedBigInteger('material_grade_id')->nullable(); $t->unsignedBigInteger('unit_master_id');
    $t->decimal('quantity_reported',15,3); $t->unsignedInteger('sort_order')->default(1); $t->timestamps();
    $t->index(['work_done_item_id','stock_key'],'wd_material_identity_idx');
   });
  }
 }
 public function down(): void { Schema::dropIfExists('work_done_reported_materials'); /* Preserve labour_group_id: previous migration may own it. */ }
};
