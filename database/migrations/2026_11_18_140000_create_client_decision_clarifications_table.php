<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_decision_clarifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('client_decisions')->restrictOnDelete();
            $table->foreignId('client_user_id')->constrained('client_users')->restrictOnDelete();
            $table->text('question');
            $table->string('client_ip_address', 45)->nullable();
            $table->timestamp('requested_at');
            $table->text('reply')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reply_ip_address', 45)->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->index(['decision_id', 'replied_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_decision_clarifications');
    }
};
