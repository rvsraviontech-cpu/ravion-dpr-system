<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawing_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drawing_id')->constrained('client_drawings')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->string('revision_reference', 50);
            $table->string('title');
            $table->foreignId('purpose_id')->constrained('client_drawing_purposes')->restrictOnDelete();
            $table->date('drawing_date');
            $table->string('consultant_reference')->nullable();
            $table->string('block')->nullable();
            $table->string('floor')->nullable();
            $table->string('location')->nullable();
            $table->string('consultant')->nullable();
            $table->string('scale')->nullable();
            $table->string('sheet_number')->nullable();
            $table->text('client_description')->nullable();
            $table->text('internal_remarks')->nullable();
            $table->text('revision_description')->nullable();
            $table->text('review_notes')->nullable();
            $table->boolean('client_approval_required')->default(false);
            $table->date('client_approval_due_date')->nullable();
            $table->boolean('client_download_allowed')->default(false);
            $table->string('status',30)->default('Draft');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unique(['drawing_id','revision']);
            $table->unique(['drawing_id','revision_reference']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawing_revisions');
    }
};
