<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_support_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('division_code', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unique(['division_code','year'], 'cs_sequence_div_year_uq');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_support_sequences');
    }
};
