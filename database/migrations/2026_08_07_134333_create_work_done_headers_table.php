<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_done_headers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('work_date');

            $table->string('status', 30)
                ->default('Draft');

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['project_id', 'user_id', 'work_date'],
                'work_done_headers_project_user_date_unique'
            );

            $table->index(
                ['project_id', 'work_date'],
                'work_done_headers_project_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_done_headers');
    }
};
