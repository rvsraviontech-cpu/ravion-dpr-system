<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('construction_work_packages', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Hierarchy
            |--------------------------------------------------------------------------
            |
            | NULL parent_id = major construction work group
            | Example:
            |   Electrical Works
            |
            | parent_id populated = work package under that group
            | Example:
            |   Conduiting & Boxes
            |   Wiring
            |   Distribution Boards & Panels
            |
            */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('construction_work_packages')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            $table->string('code', 50)->unique();
            $table->string('name', 150);

            /*
            |--------------------------------------------------------------------------
            | Optional execution mapping
            |--------------------------------------------------------------------------
            |
            | This allows a Work Package to be associated with the existing
            | Activity Division structure without making Activity Division
            | mandatory in the Material Received user interface.
            |
            */
            $table->foreignId('activity_division_id')
                ->nullable()
                ->constrained('activity_divisions')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Display / behaviour
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['parent_id', 'is_active']);
            $table->index(['activity_division_id', 'is_active']);
            $table->index(['sort_order', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('construction_work_packages');
    }
};