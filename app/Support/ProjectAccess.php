<?php

namespace App\Support;

use App\Models\Project;

class ProjectAccess
{
    /**
     * Return all projects the current user is allowed to access.
     */
    public static function availableProjects()
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        $role = $user->role?->name;

        /*
        |--------------------------------------------------------------------------
        | Management Roles
        |--------------------------------------------------------------------------
        |
        | These roles can access every active project.
        |
        */

        if (in_array($role, [
            'Admin',
            'CEO',
            'PMO',
            'DGM',
            'Accountant',
            'Store',
        ])) {

            return Project::query()
                ->active()
                ->orderBy('project_name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Engineer Roles
        |--------------------------------------------------------------------------
        |
        | Engineers can only access assigned projects.
        |
        */

        if (in_array($role, [
            'Engineer',
            'Site Engineer',
        ])) {

            return $user->projects()
                ->active()
                ->orderBy('project_name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return collect();
    }

    /**
     * Return allowed project IDs.
     */
    public static function allowedProjectIds(): array
    {
        return self::availableProjects()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Determine whether the current user can access a project.
     */
    public static function canAccess(int $projectId): bool
    {
        return in_array(
            $projectId,
            self::allowedProjectIds(),
            true
        );
    }

    /**
     * Abort if the current user cannot access the project.
     */
    public static function authorize(int $projectId): void
    {
        abort_unless(
            self::canAccess($projectId),
            403,
            'You are not authorised to access this project.'
        );
    }
}