<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_bible_stages', function (Blueprint $t) {
            $t->id();
            $t->string('stage_code', 100)->unique();
            $t->string('stage_name');
            $t->json('source_row')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('work_done_bible_assemblies', function (Blueprint $t) {
            $t->id();
            $t->string('assembly_name', 255)->unique();
            $t->timestamps();
        });
        Schema::create('work_done_bible_work_items', function (Blueprint $t) {
            $t->id();
            $t->string('work_item_name', 255)->unique();
            $t->timestamps();
        });
        Schema::create('work_done_bible_operations', function (Blueprint $t) {
            $t->id();
            $t->string('wd_code', 100)->unique();
            $t->foreignId('stage_id')->constrained('work_done_bible_stages')->restrictOnDelete();
            $t->foreignId('assembly_id')->nullable()->constrained('work_done_bible_assemblies')->restrictOnDelete();
            $t->foreignId('work_item_id')->nullable()->constrained('work_done_bible_work_items')->restrictOnDelete();
            $t->string('trade')->nullable();
            $t->string('element')->nullable();
            $t->string('micro_operation', 1000);
            $t->string('record_type', 100)->nullable();
            $t->string('uom', 100)->nullable();
            $t->string('measurement_method', 1000)->nullable();
            $t->string('predecessor', 1000)->nullable();
            $t->string('qa_gate', 255)->nullable();
            $t->json('source_row');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['stage_id','assembly_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_operations');
        Schema::dropIfExists('work_done_bible_work_items');
        Schema::dropIfExists('work_done_bible_assemblies');
        Schema::dropIfExists('work_done_bible_stages');
    }
};
