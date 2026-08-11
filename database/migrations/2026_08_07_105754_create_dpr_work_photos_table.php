<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpr_work_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dpr_work_item_id')
                ->constrained('dpr_work_items')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('photo_type', 50)
                ->default('Completed Work');

            $table->string('file_path');

            $table->string('original_name')
                ->nullable();

            $table->string('mime_type', 100)
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();

            $table->string('caption', 500)
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index(
                ['dpr_work_item_id', 'sort_order'],
                'dpr_work_photos_item_sort_index'
            );

            $table->index(
                ['dpr_work_item_id', 'photo_type'],
                'dpr_work_photos_item_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpr_work_photos');
    }
};