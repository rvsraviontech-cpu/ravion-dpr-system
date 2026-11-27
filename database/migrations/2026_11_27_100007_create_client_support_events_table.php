<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('client_support_tickets')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('client_user_id')->nullable()->constrained('client_users')->restrictOnDelete();
            $table->string('action', 80);
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->index(['ticket_id','id'], 'cs_events_ticket_idx');
            $table->index(['entity_type','entity_id'], 'cs_events_entity_idx');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_events');
    }
};
