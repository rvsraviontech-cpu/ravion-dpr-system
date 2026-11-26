<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drawing_id')->constrained('client_drawings')->restrictOnDelete();
            $table->foreignId('revision_id')->nullable()->constrained('client_drawing_revisions')->restrictOnDelete();
            $table->string('actor_guard',20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action',100);
            $table->json('details')->nullable();
            $table->string('ip_address',45)->nullable();
            $table->timestamp('created_at');
            $table->index(['drawing_id','created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawing_events');
    }
};
