<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ravion DPR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

@php
    $can = function ($permission) {
        return auth()->check()
            && auth()->user()->hasPermission($permission);
    };

    $sidebarLink = function ($routeName, $label) {
        $activeClass = request()->routeIs($routeName) ? 'bg-gray-700' : '';

        if (Route::has($routeName)) {
            return '<a href="' . route($routeName) . '" class="block px-4 py-2 hover:bg-gray-700 ' . $activeClass . '">' . $label . '</a>';
        }

        return '<a href="#" class="block px-4 py-2 text-gray-500 cursor-not-allowed">' . $label . ' <span class="text-xs">(Soon)</span></a>';
    };
@endphp

<div class="flex min-h-screen">

    <aside class="w-64 bg-gray-900 text-white min-h-screen overflow-y-auto">

        <div class="p-4 text-2xl font-bold border-b border-gray-700">
            Ravion ERP
        </div>

        <ul class="py-4 space-y-1">

            @if($can('dashboard.view') || $can('dashboard.executive') || $can('project_health_dashboard.view') || $can('pmo_exception_dashboard.view'))
                <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">
                    Dashboards
                </div>

                @if($can('dashboard.view'))
                    <li>{!! $sidebarLink('dashboard', 'Main Dashboard') !!}</li>
                @endif

                @if($can('dashboard.executive') || $can('project_progress_dashboard.view'))
                    <li>{!! $sidebarLink('project-progress-dashboard.index', 'Executive Dashboard') !!}</li>
                @endif

                @if($can('dashboard.project_health') || $can('project_health_dashboard.view'))
                    <li>{!! $sidebarLink('project-health-dashboard.index', 'Project Health Dashboard') !!}</li>
                @endif

                @if($can('dashboard.pmo_exception') || $can('pmo_exception_dashboard.view'))
                    <li>{!! $sidebarLink('pmo-exception-dashboard.index', 'PMO Exception Dashboard') !!}</li>
                @endif
            @endif

            @if(
                $can('projects.view') ||
                $can('activities.view') ||
                $can('activity_mappings.view') ||
                $can('materials.view') ||
                $can('contractors.view') ||
                $can('vendors.view') ||
                $can('machinery_tools.view')
            )
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Masters
                </div>

                @if($can('projects.view')) <li>{!! $sidebarLink('projects.index', 'Projects') !!}</li> @endif
                @if($can('activities.view')) <li>{!! $sidebarLink('activities.index', 'Activities') !!}</li> @endif
                @if($can('activity_mappings.view')) <li>{!! $sidebarLink('activity-mappings.index', 'Activity Mappings') !!}</li> @endif
                @if($can('materials.view')) <li>{!! $sidebarLink('materials.index', 'Materials') !!}</li> @endif
                @if($can('material_categories.view')) <li>{!! $sidebarLink('material-categories.index', 'Material Categories') !!}</li> @endif
                @if($can('contractors.view')) <li>{!! $sidebarLink('contractors.index', 'Contractors') !!}</li> @endif
                @if($can('vendors.view')) <li>{!! $sidebarLink('vendors.index', 'Vendors') !!}</li> @endif
                @if($can('machinery_tools.view')) <li>{!! $sidebarLink('machinery-tools.index', 'Machinery / Tools') !!}</li> @endif
            @endif

            @if($can('location_masters.view'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Location Masters
                </div>

                <li>{!! $sidebarLink('location-block-masters.index', 'Block Masters') !!}</li>
                <li>{!! $sidebarLink('location-floor-masters.index', 'Floor Masters') !!}</li>
                <li>{!! $sidebarLink('location-room-masters.index', 'Room Masters') !!}</li>
                <li>{!! $sidebarLink('location-subspace-masters.index', 'Subspace Masters') !!}</li>
                <li>{!! $sidebarLink('location-unit-masters.index', 'Unit Masters') !!}</li>
            @endif

            @if($can('labour_types.view'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Labour Masters
                </div>

                <li>{!! $sidebarLink('labour-types.index', 'Labour Types') !!}</li>
            @endif

            @if(
                $can('dpr.view') ||
                $can('labour_reports.view') ||
                $can('material_received.view') ||
                $can('material_consumed.view') ||
                $can('material_required.view') ||
                $can('site_issues.view') ||
                $can('tomorrow_plans.view')
            )
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Daily Execution
                </div>

                @if($can('dpr.view')) <li>{!! $sidebarLink('dprs.index', 'DPR Entries') !!}</li> @endif
                @if($can('labour_reports.view')) <li>{!! $sidebarLink('labour-reports.index', 'Labour Reporting') !!}</li> @endif
                @if($can('material_received.view')) <li>{!! $sidebarLink('material-received.index', 'Material Received') !!}</li> @endif
                @if($can('material_consumed.view')) <li>{!! $sidebarLink('material-consumed.index', 'Material Consumed') !!}</li> @endif
                @if($can('material_required.view')) <li>{!! $sidebarLink('material-requirements.index', 'Material Requirements') !!}</li> @endif
                @if($can('site_issues.view')) <li>{!! $sidebarLink('site-issues.index', 'Site Issues') !!}</li> @endif
                @if($can('tomorrow_plans.view')) <li>{!! $sidebarLink('tomorrow-plans.index', 'Tomorrow Plans') !!}</li> @endif
            @endif

            @if($can('weekly_plans.view') || $can('monthly_plans.view') || $can('plan_vs_actual.view') || $can('activity_progress.view'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Planning & Controls
                </div>

                @if($can('weekly_plans.view')) <li>{!! $sidebarLink('weekly-plans.index', 'Weekly Plans') !!}</li> @endif
                @if($can('weekly_plans.view')) <li>{!! $sidebarLink('weekly-plans.progress-dashboard', 'Weekly Progress') !!}</li> @endif
                @if($can('monthly_plans.view')) <li>{!! $sidebarLink('monthly-plans.index', 'Monthly Plans') !!}</li> @endif
                @if($can('monthly_plans.view')) <li>{!! $sidebarLink('monthly-plans.progress-dashboard', 'Monthly Progress') !!}</li> @endif
                @if($can('plan_vs_actual.view')) <li>{!! $sidebarLink('plan-vs-actual.index', 'Plan vs Actual') !!}</li> @endif

                @if($can('activity_progress.view'))
                    <li>
                        <a href="{{ Route::has('projects.index') ? route('projects.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Activity Progress
                            <span class="text-xs text-gray-400">(Select Project)</span>
                        </a>
                    </li>
                @endif
            @endif

            @if($can('material_verification.view') || $can('mapping_queue.view') || $can('dpr_reviews.view') || $can('material_shortage_report.view'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    PMO & Verification
                </div>

                @if($can('material_verification.view')) <li>{!! $sidebarLink('material-verifications.index', 'Material Verification') !!}</li> @endif
                @if($can('material_shortage_report.view')) <li>{!! $sidebarLink('material-shortage-report.index', 'Material Shortage Report') !!}</li> @endif
                @if($can('mapping_queue.view')) <li>{!! $sidebarLink('mapping-pending-queue.index', 'Mapping Pending Queue') !!}</li> @endif
                @if($can('dpr_reviews.view')) <li>{!! $sidebarLink('pmo.dprs', 'PMO DPR Reviews') !!}</li> @endif
            @endif

            @if($can('reports.view') || $can('reports.dpr') || $can('reports.labour') || $can('reports.material') || $can('reports.project_summary'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Reports
                </div>

                @if($can('reports.view')) <li>{!! $sidebarLink('engineer-dashboard', 'Engineer Dashboard') !!}</li> @endif
                @if($can('reports.view')) <li>{!! $sidebarLink('engineer-productivity', 'Engineer Productivity') !!}</li> @endif
                @if($can('reports.view')) <li>{!! $sidebarLink('ceo-dashboard', 'CEO Dashboard') !!}</li> @endif
                @if($can('reports.view')) <li>{!! $sidebarLink('accountant-dashboard', 'Accountant Dashboard') !!}</li> @endif
            @endif

            @if($can('users.view') || $can('roles.view') || $can('permissions.view') || $can('role_permissions.view'))
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Administration
                </div>

                @if($can('users.view')) <li>{!! $sidebarLink('users.index', 'Users') !!}</li> @endif
                @if($can('roles.view')) <li>{!! $sidebarLink('roles.index', 'Roles') !!}</li> @endif
                @if($can('permissions.view')) <li>{!! $sidebarLink('permissions.index', 'Permissions') !!}</li> @endif
                @if($can('role_permissions.view')) <li>{!! $sidebarLink('role-permissions.index', 'Role Permissions') !!}</li> @endif
                @if($can('audit_trail.view')) <li>{!! $sidebarLink('audit-trail.index', 'Audit Trail') !!}</li> @endif
                @if($can('system_settings.manage')) <li>{!! $sidebarLink('system-settings.index', 'System Settings') !!}</li> @endif
            @endif

            @if(
                $can('cost_dashboard.view') ||
                $can('contractor_performance.view') ||
                $can('productivity_dashboard.view') ||
                $can('material_balance_dashboard.view') ||
                $can('boq.manage') ||
                $can('purchase_requisitions.manage') ||
                $can('work_orders.manage') ||
                $can('client_billing.manage') ||
                $can('vendor_billing.manage') ||
                $can('quality_checklists.manage') ||
                $can('safety_observations.manage') ||
                $can('documents.manage')
            )
                <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                    Future Modules
                </div>

                @if($can('cost_dashboard.view')) <li>{!! $sidebarLink('cost-dashboard.index', 'Cost Dashboard') !!}</li> @endif
                @if($can('contractor_performance.view')) <li>{!! $sidebarLink('contractor-performance.index', 'Contractor Performance') !!}</li> @endif
                @if($can('productivity_dashboard.view')) <li>{!! $sidebarLink('productivity-dashboard.index', 'Productivity Dashboard') !!}</li> @endif
                @if($can('material_balance_dashboard.view')) <li>{!! $sidebarLink('material-balance-dashboard.index', 'Material Balance Dashboard') !!}</li> @endif
                @if($can('boq.manage')) <li>{!! $sidebarLink('boq.index', 'BOQ Management') !!}</li> @endif
                @if($can('purchase_requisitions.manage')) <li>{!! $sidebarLink('purchase-requisitions.index', 'Purchase Requisitions') !!}</li> @endif
                @if($can('work_orders.manage')) <li>{!! $sidebarLink('work-orders.index', 'Work Orders') !!}</li> @endif
                @if($can('client_billing.manage')) <li>{!! $sidebarLink('client-billing.index', 'Client Billing') !!}</li> @endif
                @if($can('vendor_billing.manage')) <li>{!! $sidebarLink('vendor-billing.index', 'Vendor Billing') !!}</li> @endif
                @if($can('quality_checklists.manage')) <li>{!! $sidebarLink('quality-checklists.index', 'Quality Checklists') !!}</li> @endif
                @if($can('safety_observations.manage')) <li>{!! $sidebarLink('safety-observations.index', 'Safety Observations') !!}</li> @endif
                @if($can('documents.manage')) <li>{!! $sidebarLink('documents.index', 'Document Management') !!}</li> @endif
            @endif

        </ul>
    </aside>

    <main class="flex-1 p-6">

        <div class="bg-white rounded shadow p-4 mb-6 flex justify-between items-center">

            <div class="font-bold">
                Ravion DPR System
            </div>

            <div class="flex items-center gap-4">

                @auth
                    <span>
                        {{ auth()->user()->name }}
                        ({{ auth()->user()->role->name ?? '' }})
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                                class="bg-red-600 text-white px-3 py-1 rounded">
                            Logout
                        </button>
                    </form>
                @endauth

            </div>

        </div>

        @yield('content')

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>