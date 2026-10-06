<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('site_photo_entry_id')
                ->constrained('site_photo_entries')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Photo Classification
            |--------------------------------------------------------------------------
            */
            $table->string('photo_type', 50)
                ->default('Progress Photo');

            /*
            |--------------------------------------------------------------------------
            | Stored File Information
            |--------------------------------------------------------------------------
            */
            $table->string('file_path', 500);

            $table->string('original_name', 255)
                ->nullable();

            $table->string('mime_type', 100)
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Photo Information
            |--------------------------------------------------------------------------
            */
            $table->string('caption', 500)
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Capture Information
            |--------------------------------------------------------------------------
            |
            | Nullable because gallery images may not provide reliable capture
            | metadata. We must not fabricate capture timestamps.
            |
            */
            $table->timestamp('captured_at')
                ->nullable();

            $table->timestamps();

            $table->index(
                ['site_photo_entry_id', 'sort_order'],
                'site_photos_entry_sort_idx'
            );

            $table->index(
                ['uploaded_by', 'created_at'],
                'site_photos_uploader_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_photos');
    }
};