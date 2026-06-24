<?php

return [

    [
        'title' => 'Dashboards',
        'icon' => 'home',
        'items' => [
            ['title' => 'Main Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view', 'icon' => 'home'],
            ['title' => 'Engineer Dashboard', 'route' => 'engineer-dashboard', 'permission' => 'engineer_dashboard.view', 'icon' => 'user'],
            ['title' => 'Executive Dashboard', 'route' => 'project-progress-dashboard.index', 'permission' => 'project_progress_dashboard.view', 'icon' => 'chart'],
        ],
    ],

    [
        'title' => 'Daily Execution',
        'icon' => 'clipboard',
        'items' => [
            ['title' => 'DPR Entries', 'route' => 'dprs.index', 'permission' => 'dpr.view', 'icon' => 'document'],
            ['title' => 'Labour Reporting', 'route' => 'labour-reports.index', 'permission' => 'labour_reports.view', 'icon' => 'users'],
            ['title' => 'Site Issues', 'route' => 'site-issues.index', 'permission' => 'site_issues.view', 'icon' => 'warning'],
        ],
    ],

    [
        'title' => 'Project Setup',
        'icon' => 'building',
        'items' => [
            ['title' => 'Projects', 'route' => 'projects.index', 'permission' => 'projects.view', 'icon' => 'building'],
            ['title' => 'Project Structure', 'route' => 'project-locations.index', 'permission' => 'location_masters.view', 'icon' => 'tree'],
        ],
    ],

    [
        'title' => 'Execution Masters',
        'icon' => 'settings',
        'items' => [
            ['title' => 'Activity Divisions', 'route' => 'activity-divisions.index', 'permission' => 'activities.view', 'icon' => 'grid'],
            ['title' => 'Work Stages', 'route' => 'work-stages.index', 'permission' => 'activities.view', 'icon' => 'list'],
            ['title' => 'Activities', 'route' => 'activities.index', 'permission' => 'activities.view', 'icon' => 'clipboard'],
            ['title' => 'Activity Mapping', 'route' => 'activity-mappings.index', 'permission' => 'activity_mappings.view', 'icon' => 'link'],
        ],
    ],

    [
        'title' => 'Material Masters',
        'icon' => 'cube',
        'items' => [
            ['title' => 'Material Categories', 'route' => 'material-categories.index', 'permission' => 'material_categories.view', 'icon' => 'folder'],
            ['title' => 'Materials', 'route' => 'materials.index', 'permission' => 'materials.view', 'icon' => 'cube'],
            ['title' => 'Unit Masters', 'route' => 'unit-masters.index', 'permission' => 'materials.view', 'icon' => 'scale'],
            ['title' => 'Vendors', 'route' => 'vendors.index', 'permission' => 'vendors.view', 'icon' => 'truck'],
        ],
    ],

    [
        'title' => 'Location Masters',
        'icon' => 'map',
        'items' => [
            ['title' => 'Block Masters', 'route' => 'location-block-masters.index', 'permission' => 'location_masters.view', 'icon' => 'building'],
            ['title' => 'Floor Masters', 'route' => 'location-floor-masters.index', 'permission' => 'location_masters.view', 'icon' => 'layers'],
            ['title' => 'Unit Masters', 'route' => 'location-unit-masters.index', 'permission' => 'location_masters.view', 'icon' => 'home'],
            ['title' => 'Room Masters', 'route' => 'location-room-masters.index', 'permission' => 'location_masters.view', 'icon' => 'door'],
            ['title' => 'Subspace Masters', 'route' => 'location-subspace-masters.index', 'permission' => 'location_masters.view', 'icon' => 'square'],
        ],
    ],

    [
        'title' => 'Labour & Contractors',
        'icon' => 'users',
        'items' => [
            ['title' => 'Labour Types', 'route' => 'labour-types.index', 'permission' => 'labour_types.view', 'icon' => 'users'],
            ['title' => 'Contractors', 'route' => 'contractors.index', 'permission' => 'contractors.view', 'icon' => 'briefcase'],
            ['title' => 'Machinery / Tools', 'route' => 'machinery-tools.index', 'permission' => 'machinery_tools.view', 'icon' => 'wrench'],
        ],
    ],

    [
        'title' => 'PMO & Verification',
        'icon' => 'shield',
        'items' => [
            ['title' => 'Material Verification', 'route' => 'material-verifications.index', 'permission' => 'material_verification.view', 'icon' => 'check'],
            ['title' => 'Mapping Queue', 'route' => 'mapping-pending-queue.index', 'permission' => 'mapping_queue.view', 'icon' => 'queue'],
            ['title' => 'PMO DPR Reviews', 'route' => 'pmo.dprs', 'permission' => 'dpr_reviews.view', 'icon' => 'review'],
        ],
    ],

    [
        'title' => 'Administration',
        'icon' => 'cog',
        'items' => [
            ['title' => 'Users', 'route' => 'users.index', 'permission' => 'users.view', 'icon' => 'user'],
            ['title' => 'Roles', 'route' => 'roles.index', 'permission' => 'roles.view', 'icon' => 'key'],
            ['title' => 'Permissions', 'route' => 'permissions.index', 'permission' => 'permissions.view', 'icon' => 'lock'],
            ['title' => 'Role Permissions', 'route' => 'role-permissions.index', 'permission' => 'role_permissions.view', 'icon' => 'shield'],
            ['title' => 'Audit Trail', 'route' => 'audit-logs.index', 'permission' => 'audit_trail.view', 'icon' => 'history'],
        ],
    ],

];