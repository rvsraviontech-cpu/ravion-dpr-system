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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code', 30)
                ->nullable()
                ->unique()
                ->after('id');

            $table->string('mobile', 30)
                ->nullable()
                ->after('name');

            $table->string('designation', 150)
                ->nullable()
                ->after('mobile');

            $table->string('department', 150)
                ->nullable()
                ->after('designation');

            $table->date('joining_date')
                ->nullable()
                ->after('department');

            $table->string('profile_photo')
                ->nullable()
                ->after('joining_date');

            $table->enum('project_access_scope', [
                'all',
                'selected',
            ])
                ->default('selected')
                ->after('role_id');

            $table->enum('account_status', [
                'Active',
                'Inactive',
                'Suspended',
                'Exited',
            ])
                ->default('Active')
                ->after('project_access_scope');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('account_status');

            $table->string('last_login_ip', 45)
                ->nullable()
                ->after('last_login_at');

            $table->timestamp('password_changed_at')
                ->nullable()
                ->after('last_login_ip');

            $table->boolean('must_change_password')
                ->default(false)
                ->after('password_changed_at');

            $table->timestamp('deactivated_at')
                ->nullable()
                ->after('must_change_password');

            $table->foreignId('deactivated_by')
                ->nullable()
                ->after('deactivated_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->date('exit_date')
                ->nullable()
                ->after('deactivated_by');

            $table->text('remarks')
                ->nullable()
                ->after('exit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);

            $table->dropColumn([
                'employee_code',
                'mobile',
                'designation',
                'department',
                'joining_date',
                'profile_photo',
                'project_access_scope',
                'account_status',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
                'must_change_password',
                'deactivated_at',
                'deactivated_by',
                'exit_date',
                'remarks',
            ]);
        });
    }
};