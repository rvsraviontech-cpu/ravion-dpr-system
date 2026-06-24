<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'division_code')) {
                $table->string('division_code')->default('RH')->after('project_name');
            }

            if (!Schema::hasColumn('projects', 'client_mobile')) {
                $table->string('client_mobile')->nullable()->after('client_name');
            }

            if (!Schema::hasColumn('projects', 'client_email')) {
                $table->string('client_email')->nullable()->after('client_mobile');
            }

            if (!Schema::hasColumn('projects', 'client_address')) {
                $table->text('client_address')->nullable()->after('client_email');
            }

            if (!Schema::hasColumn('projects', 'google_map_link')) {
                $table->text('google_map_link')->nullable()->after('location');
            }

            if (!Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type')->nullable()->after('google_map_link');
            }

            if (!Schema::hasColumn('projects', 'structure_type')) {
                $table->string('structure_type')->nullable()->after('project_type');
            }

            if (!Schema::hasColumn('projects', 'contract_value')) {
                $table->decimal('contract_value', 15, 2)->nullable()->after('structure_type');
            }

            if (!Schema::hasColumn('projects', 'assigned_pmo_id')) {
                $table->foreignId('assigned_pmo_id')->nullable()->after('contract_value')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('projects', 'status')) {
                $table->string('status')->default('Active')->after('target_completion_date');
            }

            if (!Schema::hasColumn('projects', 'odoo_analytic_account_code')) {
                $table->string('odoo_analytic_account_code')->nullable()->after('status');
            }

            if (!Schema::hasColumn('projects', 'remarks')) {
                $table->text('remarks')->nullable()->after('odoo_analytic_account_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $columns = [
                'division_code',
                'client_mobile',
                'client_email',
                'client_address',
                'google_map_link',
                'project_type',
                'structure_type',
                'contract_value',
                'assigned_pmo_id',
                'status',
                'odoo_analytic_account_code',
                'remarks',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};