<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\BrandMaster;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandMasterController extends Controller
{
    /**
     * Display the Material Brand master list.
     */
    public function index(Request $request): View
    {
        $query = BrandMaster::query()
            ->with('materialType.unit');

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
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('brand_code', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'materialType',
                        fn (Builder $typeQuery) => $typeQuery
                            ->where('material_type_name', 'like', "%{$search}%")
                            ->orWhere('material_group', 'like', "%{$search}%")
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

        $brands = $query
            ->orderByDesc('is_active')
            ->orderBy('material_type_id')
            ->orderBy('sequence')
            ->orderBy('brand_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'brand-masters.index',
            array_merge(
                compact('brands'),
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
            'brand-masters.create',
            $this->formData()
        );
    }

    /**
     * Store a new brand.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBrand($request);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = true;

        $brand = BrandMaster::create($validated);

        AuditHelper::log(
            'Brand Masters',
            'Created',
            'BrandMaster',
            $brand->id,
            'Material brand created: ' . $brand->brand_name,
            null,
            $this->auditValues($brand->fresh('materialType'))
        );

        return redirect()
            ->route('brand-masters.index')
            ->with('success', 'Material brand created successfully.');
    }

    /**
     * Show the edit form.
     */
    public function edit(BrandMaster $brandMaster): View
    {
        $brandMaster->load('materialType');

        return view(
            'brand-masters.edit',
            array_merge(
                compact('brandMaster'),
                $this->formData()
            )
        );
    }

    /**
     * Update an existing brand.
     */
    public function update(
        Request $request,
        BrandMaster $brandMaster
    ): RedirectResponse {
        $validated = $this->validateBrand(
            $request,
            $brandMaster
        );

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $this->auditValues(
            $brandMaster->load('materialType')
        );

        $brandMaster->update($validated);

        AuditHelper::log(
            'Brand Masters',
            'Updated',
            'BrandMaster',
            $brandMaster->id,
            'Material brand updated: ' . $brandMaster->brand_name,
            $oldValues,
            $this->auditValues(
                $brandMaster->fresh('materialType')
            )
        );

        return redirect()
            ->route('brand-masters.index')
            ->with('success', 'Material brand updated successfully.');
    }

    /**
     * Activate or deactivate a brand.
     */
    public function toggleStatus(
        BrandMaster $brandMaster
    ): RedirectResponse {
        $oldValues = $this->auditValues(
            $brandMaster->load('materialType')
        );

        $brandMaster->update([
            'is_active' => ! $brandMaster->is_active,
        ]);

        $brandMaster->refresh();

        AuditHelper::log(
            'Brand Masters',
            $brandMaster->is_active
                ? 'Activated'
                : 'Deactivated',
            'BrandMaster',
            $brandMaster->id,
            $brandMaster->is_active
                ? 'Material brand activated: ' . $brandMaster->brand_name
                : 'Material brand deactivated: ' . $brandMaster->brand_name,
            $oldValues,
            $this->auditValues(
                $brandMaster->load('materialType')
            )
        );

        return back()->with(
            'success',
            'Material brand status updated successfully.'
        );
    }

    /**
     * Shared form and filter data.
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
     * Validate brand data.
     */
    private function validateBrand(
        Request $request,
        ?BrandMaster $brandMaster = null
    ): array {
        return $request->validate([
            'material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
            ],

            'brand_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('brand_masters', 'brand_name')
                    ->where(
                        fn ($query) => $query->where(
                            'material_type_id',
                            $request->integer('material_type_id')
                        )
                    )
                    ->ignore($brandMaster?->id),
            ],

            'brand_code' => [
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
            'brand_name.unique' =>
                'This brand already exists for the selected Material Type.',
        ]);
    }

    /**
     * Audit values.
     */
    private function auditValues(BrandMaster $brand): array
    {
        return [
            'id' => $brand->id,
            'material_type_id' => $brand->material_type_id,
            'material_type_name' =>
                $brand->materialType?->material_type_name,
            'material_group' =>
                $brand->materialType?->material_group,
            'brand_name' => $brand->brand_name,
            'brand_code' => $brand->brand_code,
            'sequence' => $brand->sequence,
            'is_active' => $brand->is_active,
            'remarks' => $brand->remarks,
        ];
    }
}