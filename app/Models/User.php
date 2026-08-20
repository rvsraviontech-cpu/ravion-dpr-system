<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'employee_code',
    'name',
    'mobile',

    // Legacy text fields - retained temporarily during migration.
    'designation',
    'department',

    // New controlled master relationships.
    'department_id',
    'employee_designation_id',

    'joining_date',
    'profile_photo',
    'email',
    'password',
    'role_id',
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
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
            'exit_date' => 'date',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'must_change_password' => 'boolean',
            'department_id' => 'integer',
            'employee_designation_id' => 'integer',
        ];
    }

    /**
     * ERP role assigned to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Department assigned through the Department Master.
     */
    public function departmentMaster()
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    /**
     * Employee designation assigned through the
     * Employee Designation Master.
     */
    public function employeeDesignation()
    {
        return $this->belongsTo(
            EmployeeDesignation::class,
            'employee_designation_id'
        );
    }

    /**
     * Projects specifically assigned to the user.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    /**
     * DPRs created by the user.
     */
    public function dprs()
    {
        return $this->hasMany(Dpr::class);
    }

    /**
     * User who deactivated this account.
     */
    public function deactivatedBy()
    {
        return $this->belongsTo(
            User::class,
            'deactivated_by'
        );
    }

    /**
     * Users deactivated by this user.
     */
    public function deactivatedUsers()
    {
        return $this->hasMany(
            User::class,
            'deactivated_by'
        );
    }

    /**
     * Determine whether the user has the given permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role
            ->permissions()
            ->where('name', $permissionName)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Determine whether this account is currently active.
     */
    public function isActive(): bool
    {
        return $this->account_status === 'Active';
    }

    /**
     * Determine whether the user has access to all projects.
     */
    public function hasAllProjectAccess(): bool
    {
        return $this->project_access_scope === 'all';
    }

    /**
     * Determine whether the user has access to a project.
     */
    public function hasProjectAccess(int $projectId): bool
    {
        if ($this->hasAllProjectAccess()) {
            return true;
        }

        return $this->projects()
            ->where('projects.id', $projectId)
            ->exists();
    }

    /**
     * Generate the next available employee code.
     */
    public static function generateEmployeeCode(): string
    {
        $lastUser = static::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', 'RVS-%')
            ->orderByRaw(
                "CAST(SUBSTRING(employee_code, 5) AS UNSIGNED) DESC"
            )
            ->first();

        $lastNumber = 0;

        if (
            $lastUser
            && preg_match(
                '/RVS-(\d+)/',
                $lastUser->employee_code,
                $matches
            )
        ) {
            $lastNumber = (int) $matches[1];
        }

        do {
            $lastNumber++;

            $employeeCode = 'RVS-' . str_pad(
                $lastNumber,
                4,
                '0',
                STR_PAD_LEFT
            );
        } while (
            static::query()
                ->where('employee_code', $employeeCode)
                ->exists()
        );

        return $employeeCode;
    }
}