<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('client_support_tickets')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('client_user_id')->nullable()->constrained('client_users')->restrictOnDelete();
            $table->string('visibility', 20)->default('Internal');
            $table->text('body');
            $table->index(['ticket_id','visibility','id'], 'cs_messages_visibility_idx');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_messages');
    }
};
