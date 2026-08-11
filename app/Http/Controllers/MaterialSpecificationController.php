<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialSpecification;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialSpecificationController extends Controller
{
    /**
     * Display Material Specifications.
     */
    public function index(Request $request): View
    {
        $query = MaterialSpecification::query()
            ->with([
                'materialType.unit',
                'creator',
            ]);

        if ($request->filled('material_group')) {
            $query->whereHas(
                'materialType',
                fn (Builder $builder) => $builder->where(
                    'material_group',
                    $request->string('material_group')->toString()
                )
            );
        }

        if ($request->filled('material_type_id')) {
            $query->where(
                'material_type_id',
                $request->integer('material_type_id')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where(
                        'specification_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'specification_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'remarks',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'materialType',
                        fn (Builder $typeQuery) => $typeQuery
                            ->where(
                                'material_type_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'material_group',
                                'like',
                                "%{$search}%"
                            )
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

        $materialSpecifications = $query
            ->orderByDesc('is_active')
            ->orderBy('material_type_id')
            ->orderBy('sequence')
            ->orderBy('specification_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'material-specifications.index',
            array_merge(
                compact('materialSpecifications'),
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
            'material-specifications.create',
            $this->formData()
        );
    }

    /**
     * Store a Material Specification.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSpecification($request);

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        $specification = MaterialSpecification::create(
            $validated
        );

        AuditHelper::log(
            'Material Specifications',
            'Created',
            'MaterialSpecification',
            $specification->id,
            'Material specification created: '
                . $specification->specification_name,
            null,
            $this->auditValues(
                $specification->fresh('materialType')
            )
        );

        return redirect()
            ->route('material-specifications.index')
            ->with(
                'success',
                'Material specification created successfully.'
            );
    }

    /**
     * Display specification details.
     */
    public function show(
        MaterialSpecification $materialSpecification
    ): View {
        $materialSpecification->load([
            'materialType.unit',
            'creator',
        ]);

        return view(
            'material-specifications.show',
            compact('materialSpecification')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        MaterialSpecification $materialSpecification
    ): View {
        $materialSpecification->load('materialType');

        return view(
            'material-specifications.edit',
            array_merge(
                compact('materialSpecification'),
                $this->formData()
            )
        );
    }

    /**
     * Update a Material Specification.
     */
    public function update(
        Request $request,
        MaterialSpecification $materialSpecification
    ): RedirectResponse {
        $validated = $this->validateSpecification(
            $request,
            $materialSpecification
        );

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        $oldValues = $this->auditValues(
            $materialSpecification->load('materialType')
        );

        $materialSpecification->update($validated);

        AuditHelper::log(
            'Material Specifications',
            'Updated',
            'MaterialSpecification',
            $materialSpecification->id,
            'Material specification updated: '
                . $materialSpecification->specification_name,
            $oldValues,
            $this->auditValues(
                $materialSpecification->fresh('materialType')
            )
        );

        return redirect()
            ->route('material-specifications.index')
            ->with(
                'success',
                'Material specification updated successfully.'
            );
    }

    /**
     * Activate or deactivate a specification.
     */
    public function destroy(
        MaterialSpecification $materialSpecification
    ): RedirectResponse {
        $oldValues = $this->auditValues(
            $materialSpecification->load('materialType')
        );

        $materialSpecification->update([
            'is_active' => ! $materialSpecification->is_active,
        ]);

        $materialSpecification->refresh();

        AuditHelper::log(
            'Material Specifications',
            $materialSpecification->is_active
                ? 'Activated'
                : 'Deactivated',
            'MaterialSpecification',
            $materialSpecification->id,
            $materialSpecification->is_active
                ? 'Material specification activated: '
                    . $materialSpecification->specification_name
                : 'Material specification deactivated: '
                    . $materialSpecification->specification_name,
            $oldValues,
            $this->auditValues(
                $materialSpecification->load('materialType')
            )
        );

        return back()->with(
            'success',
            'Material specification status updated successfully.'
        );
    }

    /**
     * Shared dropdown and filter data.
     */
    private function formData(): array
    {
        $materialTypes = MaterialType::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->get();

        return [
            'materialTypes' => $materialTypes,

            'materialGroups' => $materialTypes
                ->pluck('material_group')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ];
    }

    /**
     * Validate Material Specification data.
     */
    private function validateSpecification(
        Request $request,
        ?MaterialSpecification $materialSpecification = null
    ): array {
        return $request->validate([
            'material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
            ],

            'specification_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'material_specifications',
                    'specification_name'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'material_type_id',
                            $request->integer('material_type_id')
                        )
                    )
                    ->ignore($materialSpecification?->id),
            ],

            'specification_code' => [
                'nullable',
                'string',
                'max:100',
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
            'specification_name.unique' =>
                'This specification already exists for the selected Material Type.',
        ]);
    }

    /**
     * Audit values.
     */
    private function auditValues(
        MaterialSpecification $specification
    ): array {
        return [
            'id' => $specification->id,
            'material_type_id' =>
                $specification->material_type_id,
            'material_type_name' =>
                $specification->materialType?->material_type_name,
            'material_group' =>
                $specification->materialType?->material_group,
            'specification_name' =>
                $specification->specification_name,
            'specification_code' =>
                $specification->specification_code,
            'sequence' =>
                $specification->sequence,
            'is_active' =>
                $specification->is_active,
            'remarks' =>
                $specification->remarks,
            'created_by' =>
                $specification->created_by,
        ];
    }
}