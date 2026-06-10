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
    Schema::table('tomorrow_plans', function (Blueprint $table) {
        $table->foreignId('project_id')->nullable()->after('dpr_id')->constrained()->nullOnDelete();
        $table->foreignId('project_block_id')->nullable()->after('project_id')->constrained('project_blocks')->nullOnDelete();
        $table->foreignId('project_floor_id')->nullable()->after('project_block_id')->constrained('project_floors')->nullOnDelete();
        $table->foreignId('project_unit_id')->nullable()->after('project_floor_id')->constrained('project_units')->nullOnDelete();

        $table->foreignId('project_room_id')->nullable()->after('project_unit_id')->constrained('project_rooms')->nullOnDelete();
        $table->foreignId('project_subspace_id')->nullable()->after('project_room_id')->constrained('project_subspaces')->nullOnDelete();

        $table->foreignId('contractor_id')->nullable()->after('activity_id')->constrained('contractors')->nullOnDelete();

        $table->integer('required_skilled_labour')->default(0)->after('planned_labour');
        $table->integer('required_semiskilled_labour')->default(0)->after('required_skilled_labour');
        $table->integer('required_helpers')->default(0)->after('required_semiskilled_labour');

        $table->boolean('drawing_required')->default(false)->after('machinery_required');
        $table->boolean('client_approval_required')->default(false)->after('drawing_required');

        $table->string('responsible_person')->nullable()->after('client_approval_required');

        $table->date('planned_date')->nullable()->after('responsible_person');

        $table->string('priority')->default('Normal')->after('planned_date');
        $table->string('status')->default('Draft')->after('priority');

        $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        $table->timestamp('approved_at')->nullable()->after('approved_by');
    });
}

public function down(): void
{
    Schema::table('tomorrow_plans', function (Blueprint $table) {
        $table->dropConstrainedForeignId('project_id');
        $table->dropConstrainedForeignId('project_block_id');
        $table->dropConstrainedForeignId('project_floor_id');
        $table->dropConstrainedForeignId('project_unit_id');
        $table->dropConstrainedForeignId('project_room_id');
        $table->dropConstrainedForeignId('project_subspace_id');
        $table->dropConstrainedForeignId('contractor_id');

        $table->dropColumn([
            'required_skilled_labour',
            'required_semiskilled_labour',
            'required_helpers',
            'drawing_required',
            'client_approval_required',
            'responsible_person',
            'planned_date',
            'priority',
            'status',
            'created_by',
            'approved_by',
            'approved_at',
        ]);
    });
}
};
