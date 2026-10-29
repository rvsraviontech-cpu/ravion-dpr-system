<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_done_bible_material_relations', function (Blueprint $table) {
            $table->id(); $table->string('code', 30)->unique(); $table->string('relationship', 120);
            $table->text('definition')->nullable(); $table->string('can_earn_qty', 10)->nullable();
            $table->string('track_consumption', 10)->nullable(); $table->json('source_row'); $table->timestamps();
        });
        Schema::create('work_done_bible_measurement_methods', function (Blueprint $table) {
            $table->id(); $table->string('method', 30)->unique(); $table->string('name', 255);
            $table->string('default_uom', 60)->nullable(); $table->text('logic')->nullable();
            $table->json('source_row'); $table->timestamps();
        });
        Schema::create('work_done_bible_qa_gates', function (Blueprint $table) {
            $table->id(); $table->string('gate_type', 120)->unique(); $table->text('behaviour')->nullable();
            $table->json('source_row'); $table->timestamps();
        });
        Schema::create('work_done_bible_statuses', function (Blueprint $table) {
            $table->id(); $table->string('status', 120)->unique(); $table->text('meaning')->nullable();
            $table->json('source_row'); $table->timestamps();
        });
        Schema::create('work_done_bible_interfaces', function (Blueprint $table) {
            $table->id(); $table->string('primary_assembly', 255)->unique();
            $table->text('mandatory_interfaces')->nullable(); $table->text('php_logic')->nullable();
            $table->json('source_row'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_bible_interfaces');
        Schema::dropIfExists('work_done_bible_statuses');
        Schema::dropIfExists('work_done_bible_qa_gates');
        Schema::dropIfExists('work_done_bible_measurement_methods');
        Schema::dropIfExists('work_done_bible_material_relations');
    }
};
