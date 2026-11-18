<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('client_progress_reports', function(Blueprint $t) {
   $t->unsignedInteger('submission_cycle')->default(0);
   $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
   $t->timestamp('submitted_at')->nullable();
   $t->foreignId('pm_verified_by')->nullable()->constrained('users')->nullOnDelete();
   $t->timestamp('pm_verified_at')->nullable();
  });
  Schema::create('client_progress_report_reviews', function(Blueprint $t) {
   $t->id(); $t->foreignId('report_id')->constrained('client_progress_reports')->restrictOnDelete();
   $t->unsignedInteger('submission_cycle'); $t->string('department',40);
   $t->string('decision',20); $t->text('remarks')->nullable();
   $t->foreignId('acted_by')->constrained('users')->restrictOnDelete();
   $t->timestamp('acted_at'); $t->timestamps();
   $t->index(['report_id','submission_cycle','department'],'cpr_reviews_lookup');
  });
 }
 public function down(): void { Schema::dropIfExists('client_progress_report_reviews'); Schema::table('client_progress_reports',function(Blueprint $t){$t->dropConstrainedForeignId('submitted_by');$t->dropConstrainedForeignId('pm_verified_by');$t->dropColumn(['submission_cycle','submitted_at','pm_verified_at']);}); }
};
