<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('client_support_messages')->restrictOnDelete();
            $table->string('original_filename', 255);
            $table->string('disk', 30)->default('local');
            $table->string('storage_path', 255)->unique();
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_attachments');
    }
};
