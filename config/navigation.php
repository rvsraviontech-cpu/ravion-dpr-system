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
            ['title' => 'Labour Attendance', 'route' => 'labour-attendances.index',  'permission' => 'labour_attendances.view', ],
            ['title' => 'Attendance Corrections','route' => 'attendance-corrections.index', 'permission' => 'attendance_corrections.view','icon' => 'history',],
            ['title' => 'Attendance Register', 'route' => 'labour-attendance-register.index','permission' => 'attendance_register.view','icon' => 'calendar',],
            ['title' => 'Weekly Wage Sheets',
    'route' => 'weekly-wage-sheets.index',
    'permission' => 'weekly_wage_sheets.view',
    'icon' => 'currency',
],
        ],
    ],

    [
    'title' => 'Material Tracking',
    'icon' => 'cube',
    'items' => [
        ['title' => 'Material Received', 'route' => 'material-received.index', 'permission' => 'material_received.view', 'icon' => 'truck'],
        ['title' => 'Material Consumed', 'route' => 'material-consumed.index', 'permission' => 'material_consumed.view', 'icon' => 'cube'],
        ['title' => 'Material Required', 'route' => 'material-requirements.index', 'permission' => 'material_required.view', 'icon' => 'document'],
        ['title' => 'Stock Register', 'route' => 'stock-register.index', 'permission' => 'material_ledger.view', 'icon' => 'layers'],
        ['title' => 'Material Ledger', 'route' => 'material-ledger.index', 'permission' => 'material_ledger.view', 'icon' => 'list'],
        ['title' => 'Shortage Report', 'route' => 'material-shortage-report.index', 'permission' => 'material_shortage_report.view', 'icon' => 'warning'],
    ],
],

[
    'title' => 'Planning',
    'icon' => 'calendar',
    'items' => [
        ['title' => 'Tomorrow Plan', 'route' => 'tomorrow-plans.index', 'permission' => 'tomorrow_plans.view', 'icon' => 'calendar'],
        ['title' => 'Weekly Plan', 'route' => 'weekly-plans.index', 'permission' => 'weekly_plans.view', 'icon' => 'calendar'],
        ['title' => 'Weekly Progress', 'route' => 'weekly-plans.progress-dashboard', 'permission' => 'weekly_plans.view', 'icon' => 'chart'],
        ['title' => 'Monthly Plan', 'route' => 'monthly-plans.index', 'permission' => 'monthly_plans.view', 'icon' => 'calendar'],
        ['title' => 'Monthly Progress', 'route' => 'monthly-plans.progress-dashboard', 'permission' => 'monthly_plans.view', 'icon' => 'chart'],
        ['title' => 'Plan vs Actual', 'route' => 'plan-vs-actual.index', 'permission' => 'plan_vs_actual.view', 'icon' => 'chart'],
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
            
    

        [
    'title' => 'Material Types',
    'route' => 'material-types.index',
    'permission' => 'material_types.view',
],

[
    'title' => 'Material Brands',
    'route' => 'brand-masters.index',
    'permission' => 'brand_masters.view',
],

[
    'title' => 'Material Specifications',
    'route' => 'material-specifications.index',
    'permission' => 'material_specifications.view',
],

[
    'title' => 'Material Grades / Ratings',
    'route' => 'material-grades.index',
    'permission' => 'material_grades.view',
],

       

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
    'icon'  => 'users',

    'items' => [

        [
            'title' => 'Labour Master',
            'route' => 'labours.index',
            'permission' => 'labour_masters.view',
            'icon' => 'users',
        ],

        [
            'title' => 'Labour Types',
            'route' => 'labour-types.index',
            'permission' => 'labour_types.view',
            'icon' => 'users',
        ],

        [
            'title' => 'Attendance Statuses',
            'route' => 'attendance-statuses.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'list',
        ],

        [
            'title' => 'Genders',
            'route' => 'genders.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'users',
        ],

        [
            'title' => 'Manpower Sources',
            'route' => 'manpower-sources.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'briefcase',
        ],

        [
            'title' => 'Skill Categories',
            'route' => 'skill-categories.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'list',
        ],

        [
            'title' => 'Designation Roles',
            'route' => 'designation-roles.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'list',
        ],

        [
            'title' => 'Working Statuses',
            'route' => 'working-statuses.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'list',
        ],

        [
            'title' => 'Shifts',
            'route' => 'shifts.index',
            'permission' => 'labour_master_data.view',
            'icon' => 'clock',
        ],

        [
            'title' => 'Contractors',
            'route' => 'contractors.index',
            'permission' => 'contractors.view',
            'icon' => 'briefcase',
        ],

        [
            'title' => 'Machinery / Tools',
            'route' => 'machinery-tools.index',
            'permission' => 'machinery_tools.view',
            'icon' => 'wrench',
        ],

        [
            'title' => 'Service Categories',
            'route' => 'contractor-service-categories.index',
            'permission' => 'contractors.view',
            'icon' => 'list',
        ],

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