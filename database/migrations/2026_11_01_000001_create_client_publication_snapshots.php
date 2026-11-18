<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_publication_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_publication_id')->constrained('client_publications')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('source_module', 100);
            $table->unsignedBigInteger('source_record_id');
            $table->string('source_revision', 100);
            $table->string('content_type', 50);
            $table->string('title', 255);
            $table->longText('client_safe_body');
            $table->char('content_sha256', 64);
            $table->unsignedBigInteger('prepared_by');
            $table->timestamp('prepared_at');
            $table->timestamps();
            $table->unique(['client_publication_id', 'version'], 'client_pub_snapshot_version_unique');
            $table->index(['source_module', 'source_record_id'], 'client_pub_snapshot_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_publication_snapshots');
    }
};
