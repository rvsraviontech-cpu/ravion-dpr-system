<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawing_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('client_drawing_revisions')->restrictOnDelete();
            $table->string('kind',20);
            $table->string('original_filename');
            $table->string('disk',40)->default('local');
            $table->string('storage_path',500);
            $table->string('mime_type',150);
            $table->unsignedBigInteger('file_size');
            $table->char('sha256',64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->unique(['revision_id','kind']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawing_files');
    }
};
