<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_issue_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('site_issue_id')
                ->constrained('site_issues')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('photo_type', 50)
                ->default('Issue');

            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('caption', 500)->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
                ['site_issue_id', 'sort_order'],
                'site_issue_photos_issue_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_issue_photos');
    }
};
