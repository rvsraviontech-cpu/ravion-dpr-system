<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialType;
use App\Models\UnitMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialTypeController extends Controller
{
    /**
     * Display Material Types.
     */
    public function index(Request $request): View
    {
        $query = MaterialType::query()
            ->with([
                'unit',
                'creator',
            ]);

        if ($request->filled('material_group')) {
            $query->where(
                'material_group',
                $request->string('material_group')->toString()
            );
        }

        if ($request->filled('unit_master_id')) {
            $query->where(
                'unit_master_id',
                $request->integer('unit_master_id')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where(
                        'material_type_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'material_type_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'material_group',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'remarks',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if (
            $request->has('status')
            && $request->status !== ''
            && $request->status !== null
        ) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        $materialTypes = $query
            ->orderByDesc('is_active')
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'material-types.index',
            array_merge(
                compact('materialTypes'),
                $this->formData()
            )
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view(
            'material-types.create',
            $this->formData()
        );
    }

    /**
     * Store a Material Type.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaterialType($request);

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        $materialType = MaterialType::create($validated);

        AuditHelper::log(
            'Material Types',
            'Created',
            'MaterialType',
            $materialType->id,
            'Material Type created: '
                . $materialType->material_type_name,
            null,
            $this->auditValues(
                $materialType->fresh('unit')
            )
        );

        return redirect()
            ->route('material-types.index')
            ->with(
                'success',
                'Material Type created successfully.'
            );
    }

    /**
     * Display Material Type details.
     */
    public function show(MaterialType $materialType): View
    {
        $materialType->load([
            'unit',
            'creator',
        ]);

        return view(
            'material-types.show',
            compact('materialType')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(MaterialType $materialType): View
    {
        $materialType->load('unit');

        return view(
            'material-types.edit',
            array_merge(
                compact('materialType'),
                $this->formData()
            )
        );
    }

    /**
     * Update a Material Type.
     */
    public function update(
        Request $request,
        MaterialType $materialType
    ): RedirectResponse {
        $validated = $this->validateMaterialType(
            $request,
            $materialType
        );

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        $oldValues = $this->auditValues(
            $materialType->load('unit')
        );

        $materialType->update($validated);

        AuditHelper::log(
            'Material Types',
            'Updated',
            'MaterialType',
            $materialType->id,
            'Material Type updated: '
                . $materialType->material_type_name,
            $oldValues,
            $this->auditValues(
                $materialType->fresh('unit')
            )
        );

        return redirect()
            ->route('material-types.index')
            ->with(
                'success',
                'Material Type updated successfully.'
            );
    }

    /**
     * Activate or deactivate a Material Type.
     */
    public function destroy(
        MaterialType $materialType
    ): RedirectResponse {
        $oldValues = $this->auditValues(
            $materialType->load('unit')
        );

        $materialType->update([
            'is_active' => ! $materialType->is_active,
        ]);

        $materialType->refresh();

        AuditHelper::log(
            'Material Types',
            $materialType->is_active
                ? 'Activated'
                : 'Deactivated',
            'MaterialType',
            $materialType->id,
            $materialType->is_active
                ? 'Material Type activated: '
                    . $materialType->material_type_name
                : 'Material Type deactivated: '
                    . $materialType->material_type_name,
            $oldValues,
            $this->auditValues(
                $materialType->load('unit')
            )
        );

        return back()->with(
            'success',
            'Material Type status updated successfully.'
        );
    }

    /**
     * Shared view data.
     */
    private function formData(): array
    {
        $materialGroups = MaterialType::query()
            ->whereNotNull('material_group')
            ->where('material_group', '!=', '')
            ->select('material_group')
            ->distinct()
            ->orderBy('material_group')
            ->pluck('material_group');

        $units = UnitMaster::query()
            ->where('is_active', true)
            ->orderBy('unit_name')
            ->get();

        return compact(
            'materialGroups',
            'units'
        );
    }

    /**
     * Validate Material Type data.
     */
    private function validateMaterialType(
        Request $request,
        ?MaterialType $materialType = null
    ): array {
        return $request->validate([
            'material_group' => [
                'required',
                'string',
                'max:255',
            ],

            'material_type_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'material_types',
                    'material_type_name'
                )->ignore($materialType?->id),
            ],

            'material_type_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'unit_master_id' => [
                'required',
                'integer',
                'exists:unit_masters,id',
            ],

            'sequence' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'material_type_name.unique' =>
                'This Material Type already exists.',

            'unit_master_id.required' =>
                'Please select the default unit.',
        ]);
    }

    /**
     * Audit values.
     */
    private function auditValues(
        MaterialType $materialType
    ): array {
        return [
            'id' => $materialType->id,
            'material_group' =>
                $materialType->material_group,
            'material_type_name' =>
                $materialType->material_type_name,
            'material_type_code' =>
                $materialType->material_type_code,
            'unit_master_id' =>
                $materialType->unit_master_id,
            'unit_name' =>
                $materialType->unit?->unit_name,
            'sequence' =>
                $materialType->sequence,
            'is_active' =>
                $materialType->is_active,
            'remarks' =>
                $materialType->remarks,
            'created_by' =>
                $materialType->created_by,
        ];
    }
}