<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('work_done_headers', function(Blueprint $table) {
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->text('review_remarks')->nullable();
        });
    }
    public function down(): void {
        Schema::table('work_done_headers', function(Blueprint $table) {
            $table->dropColumn(['submitted_at','verified_at','verified_by','returned_at','returned_by','review_remarks']);
        });
    }
};
