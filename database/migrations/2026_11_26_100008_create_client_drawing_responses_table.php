<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_drawing_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('client_drawing_publications')->restrictOnDelete();
            $table->foreignId('client_user_id')->constrained('client_users')->restrictOnDelete();
            $table->foreignId('grant_id')->constrained('client_project_grants')->restrictOnDelete();
            $table->string('response',30);
            $table->text('remarks')->nullable();
            $table->string('ip_address',45)->nullable();
            $table->timestamp('responded_at');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unique(['publication_id','client_user_id']);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('client_drawing_responses');
    }
};
