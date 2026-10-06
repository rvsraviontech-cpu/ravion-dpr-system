<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Contractor;
use App\Models\MachineryEquipment;
use App\Models\MachineryTool;
use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineryEquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = MachineryEquipment::query()
            ->with([
                'machineryTool:id,machine_name,category',
                'vendor:id,vendor_name',
                'contractor:id,contractor_name',
                'currentProject:id,project_code,project_name',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('equipment_code', 'like', "%{$search}%")
                    ->orWhere('asset_number', 'like', "%{$search}%")
                    ->orWhere('equipment_name', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('machineryTool', function ($toolQuery) use ($search) {
                        $toolQuery
                            ->where('machine_name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('ownership_type')) {
            $query->where(
                'ownership_type',
                $request->string('ownership_type')->toString()
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('project_id')) {
            $query->where(
                'current_project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('tracking_mode')) {
            $query->where(
                'tracking_mode',
                $request->string('tracking_mode')->toString()
            );
        }

        $equipment = $query
            ->orderBy('equipment_code')
            ->paginate(25)
            ->withQueryString();

        $projects = Project::query()
    ->orderBy('project_name')
    ->get(['id', 'project_code', 'project_name']);

        return view('machinery-equipment.index', [
            'equipment' => $equipment,
            'projects' => $projects,
            'ownershipTypes' => MachineryEquipment::OWNERSHIP_TYPES,
            'statuses' => MachineryEquipment::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('machinery-equipment.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $tool = MachineryTool::query()
            ->findOrFail($validated['machinery_tool_id']);

        /*
         * The Equipment Type Master controls these initial operational
         * characteristics. They may later be changed for the physical
         * machine through the Equipment Register.
         */
        $validated['tracking_mode'] = $tool->tracking_mode;
        $validated['unit'] = $tool->unit ?: 'Nos';
        $validated['meter_type'] = $tool->meter_type ?: 'none';
        $validated['fuel_type'] = $tool->fuel_type ?: 'none';

        if ($validated['tracking_mode'] === 'individual') {
            $validated['quantity'] = 1;
        }

        $this->normalizeOwnershipFields($validated);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $equipment = MachineryEquipment::create($validated);

        AuditHelper::log(
    'Machinery Equipment',
    'Created',
    MachineryEquipment::class,
    $equipment->id,
    'Created equipment register: ' . $equipment->equipment_code
);

        return redirect()
            ->route('machinery-equipment.index')
            ->with('success', 'Equipment registered successfully.');
    }

    public function show(MachineryEquipment $machineryEquipment): View
    {
        $machineryEquipment->load([
            'machineryTool',
            'vendor',
            'contractor',
            'currentProject',
            'creator',
            'updater',
        ]);

        return view(
            'machinery-equipment.show',
            compact('machineryEquipment')
        );
    }

    public function edit(MachineryEquipment $machineryEquipment): View
    {
        return view(
            'machinery-equipment.edit',
            array_merge(
                $this->formData(),
                compact('machineryEquipment')
            )
        );
    }

    public function update(
        Request $request,
        MachineryEquipment $machineryEquipment
    ): RedirectResponse {
        $validated = $this->validatedData(
            $request,
            $machineryEquipment
        );

        /*
         * We intentionally do not reset tracking/meter/fuel from the
         * master during every update. A physical machine may legitimately
         * differ from the generic master defaults.
         */
        if ($machineryEquipment->tracking_mode === 'individual') {
            $validated['quantity'] = 1;
        }

        $this->normalizeOwnershipFields($validated);

        $validated['updated_by'] = Auth::id();

        $machineryEquipment->update($validated);

        AuditHelper::log(
    'Machinery Equipment',
    'Updated',
    MachineryEquipment::class,
    $machineryEquipment->id,
    'Updated equipment register: ' . $machineryEquipment->equipment_code
);

        return redirect()
            ->route('machinery-equipment.index')
            ->with('success', 'Equipment updated successfully.');
    }

    public function destroy(
        MachineryEquipment $machineryEquipment
    ): RedirectResponse {
        /*
         * Never physically delete equipment from the register.
         * Daily logs, allocations, rentals and DPR history will eventually
         * depend on this ID.
         */
        $machineryEquipment->update([
            'is_active' => false,
            'status' => 'inactive',
            'updated_by' => Auth::id(),
        ]);

        AuditHelper::log(
    'Machinery Equipment',
    'Deactivated',
    MachineryEquipment::class,
    $machineryEquipment->id,
    'Deactivated equipment: ' . $machineryEquipment->equipment_code
);

        return redirect()
            ->route('machinery-equipment.index')
            ->with('success', 'Equipment deactivated successfully.');
    }

    private function formData(): array
    {
        return [
            'machineryTools' => MachineryTool::query()
                ->active()
                ->ordered()
                ->get(),

            'vendors' => Vendor::query()
    ->where('is_active', true)
    ->orderBy('vendor_name')
    ->get(),

            'contractors' => Contractor::query()
    ->where('status', 'active')
    ->orderBy('contractor_name')
    ->get(),

            'projects' => Project::query()
    ->orderBy('project_name')
    ->get(),

            'ownershipTypes' => MachineryEquipment::OWNERSHIP_TYPES,

            'statuses' => MachineryEquipment::STATUSES,
        ];
    }

    private function validatedData(
        Request $request,
        ?MachineryEquipment $machineryEquipment = null
    ): array {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        $currentYear = (int) now()->year;

        return $request->validate([
            'machinery_tool_id' => [
                'required',
                'integer',
                'exists:machinery_tools,id',
            ],

            'equipment_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('machinery_equipment', 'equipment_code')
                    ->ignore($machineryEquipment?->id),
            ],

            'asset_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'equipment_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.001',
            ],

            'ownership_type' => [
                'required',
                Rule::in(array_keys(MachineryEquipment::OWNERSHIP_TYPES)),
            ],

            'vendor_id' => [
                'nullable',
                'integer',
                'exists:vendors,id',
                Rule::requiredIf(
                    $request->input('ownership_type') === 'rented'
                ),
            ],

            'contractor_id' => [
                'nullable',
                'integer',
                'exists:contractors,id',
                Rule::requiredIf(
                    $request->input('ownership_type') === 'contractor_provided'
                ),
            ],

            'make' => [
                'nullable',
                'string',
                'max:150',
            ],

            'model' => [
                'nullable',
                'string',
                'max:150',
            ],

            'serial_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'manufacture_year' => [
                'nullable',
                'integer',
                'min:1900',
                'max:' . ($currentYear + 1),
            ],

            'capacity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'capacity_unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'current_meter_reading' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'purchase_date' => [
                'nullable',
                'date',
            ],

            'purchase_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            

            'current_project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            'status' => [
                'required',
                Rule::in(array_keys(MachineryEquipment::STATUSES)),
            ],

            'commissioned_date' => [
                'nullable',
                'date',
            ],

            'decommissioned_date' => [
                'nullable',
                'date',
                'after_or_equal:commissioned_date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'boolean',
            ],
        ]);
    }

    private function normalizeOwnershipFields(array &$validated): void
    {
        if ($validated['ownership_type'] !== 'rented') {
            $validated['vendor_id'] = null;
        }

        if ($validated['ownership_type'] !== 'contractor_provided') {
            $validated['contractor_id'] = null;
        }

        if ($validated['ownership_type'] !== 'company_owned') {
            $validated['asset_number'] = null;
            $validated['purchase_date'] = null;
            $validated['purchase_reference'] = null;
            $validated['purchase_value'] = null;
        }
    }
}