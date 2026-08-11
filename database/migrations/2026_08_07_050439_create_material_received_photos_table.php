<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_received_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_received_id')
                ->constrained('material_receiveds')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('photo_type', 50)
                ->default('Material Photo');

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
                [
                    'material_received_id',
                    'sort_order',
                ],
                'material_received_photos_receipt_sort_index'
            );

            $table->index(
                [
                    'material_received_id',
                    'photo_type',
                ],
                'material_received_photos_receipt_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_received_photos');
    }
};