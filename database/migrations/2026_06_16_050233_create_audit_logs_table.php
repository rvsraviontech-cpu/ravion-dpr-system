<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('module');

            $table->string('action');

            $table->string('record_type')
                ->nullable();

            $table->unsignedBigInteger('record_id')
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->string('ip_address')
                ->nullable();

            $table->string('user_agent')
                ->nullable();

            $table->json('old_values')
                ->nullable();

            $table->json('new_values')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};