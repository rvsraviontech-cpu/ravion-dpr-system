<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('work_done_items', function (Blueprint $table) {
   $table->string('review_status',20)->default('Draft')->index();
   $table->foreignId('reviewed_by')->nullable();
   $table->timestamp('reviewed_at')->nullable();
   $table->text('review_remarks')->nullable();
  });
  // Existing Submitted/Verified/Returned headers are initialized conservatively.
  \Illuminate\Support\Facades\DB::table('work_done_items as i')->join('work_done_headers as h','h.id','=','i.work_done_header_id')
   ->where('h.status','Submitted')->update(['i.review_status'=>'Submitted']);
  \Illuminate\Support\Facades\DB::table('work_done_items as i')->join('work_done_headers as h','h.id','=','i.work_done_header_id')
   ->where('h.status','Verified')->update(['i.review_status'=>'Verified']);
  \Illuminate\Support\Facades\DB::table('work_done_items as i')->join('work_done_headers as h','h.id','=','i.work_done_header_id')
   ->where('h.status','Returned')->update(['i.review_status'=>'Returned']);
 }
 public function down(): void { Schema::table('work_done_items',fn(Blueprint $table)=>$table->dropColumn(['review_status','reviewed_by','reviewed_at','review_remarks'])); }
};
