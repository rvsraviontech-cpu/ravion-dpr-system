<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawing_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_id')->constrained('client_drawing_revisions')->restrictOnDelete();
            $table->foreignId('drawing_id')->constrained('client_drawings')->restrictOnDelete();
            $table->unsignedBigInteger('active_drawing_id')->nullable()->unique();
            $table->foreign('active_drawing_id')->references('id')->on('client_drawings')->restrictOnDelete();
            $table->json('snapshot');
            $table->boolean('client_download_allowed')->default(false);
            $table->boolean('client_approval_required')->default(false);
            $table->date('client_approval_due_date')->nullable();
            $table->foreignId('published_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('published_at');
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('ended_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('end_reason',30)->nullable();
            $table->text('end_notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawing_publications');
    }
};
