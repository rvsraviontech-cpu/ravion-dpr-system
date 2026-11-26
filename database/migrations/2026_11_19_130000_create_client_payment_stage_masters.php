<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_payment_stage_masters', function (Blueprint $table) {
            $table->id();
            $table->string('stage_code', 30)->unique();
            $table->string('stage_name', 255);
            $table->unsignedInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('client_payment_milestones', function (Blueprint $table) {
            $table->foreignId('payment_stage_master_id')->nullable()->after('type')
                ->constrained('client_payment_stage_masters')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_payment_milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_stage_master_id');
        });
        Schema::dropIfExists('client_payment_stage_masters');
    }
};
