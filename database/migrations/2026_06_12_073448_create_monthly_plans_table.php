<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('monthly_plans', function (Blueprint $table) {
        $table->id();

        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

        $table->integer('plan_month');
        $table->integer('plan_year');

        $table->date('month_start_date');
        $table->date('month_end_date');

        $table->decimal('planned_quantity', 12, 2)->default(0);
        $table->string('unit')->nullable();

        $table->integer('planned_labour')->nullable();

        $table->text('materials_required')->nullable();
        $table->text('machinery_required')->nullable();
        $table->text('risks_constraints')->nullable();

        $table->enum('status', [
            'Planned',
            'In Progress',
            'Completed',
            'Delayed'
        ])->default('Planned');

        $table->text('remarks')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_plans');
    }
};
