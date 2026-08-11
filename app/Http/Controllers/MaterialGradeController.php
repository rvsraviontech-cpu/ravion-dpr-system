<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialGrade;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialGradeController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialGrade::query()
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
                    ->where('grade_name', 'like', "%{$search}%")
                    ->orWhere('grade_code', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
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

        $materialGrades = $query
            ->orderByDesc('is_active')
            ->orderBy('material_type_id')
            ->orderBy('sequence')
            ->orderBy('grade_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'material-grades.index',
            array_merge(
                compact('materialGrades'),
                $this->formData()
            )
        );
    }

    public function create(): View
    {
        return view(
            'material-grades.create',
            $this->formData()
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGrade($request);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        $materialGrade = MaterialGrade::create($validated);

        AuditHelper::log(
            'Material Grades',
            'Created',
            'MaterialGrade',
            $materialGrade->id,
            'Material grade/rating created: '
                . $materialGrade->grade_name,
            null,
            $this->auditValues(
                $materialGrade->fresh('materialType')
            )
        );

        return redirect()
            ->route('material-grades.index')
            ->with(
                'success',
                'Material grade/rating created successfully.'
            );
    }

    public function show(MaterialGrade $materialGrade): View
    {
        $materialGrade->load([
            'materialType.unit',
            'creator',
        ]);

        return view(
            'material-grades.show',
            compact('materialGrade')
        );
    }

    public function edit(MaterialGrade $materialGrade): View
    {
        $materialGrade->load('materialType');

        return view(
            'material-grades.edit',
            array_merge(
                compact('materialGrade'),
                $this->formData()
            )
        );
    }

    public function update(
        Request $request,
        MaterialGrade $materialGrade
    ): RedirectResponse {
        $validated = $this->validateGrade(
            $request,
            $materialGrade
        );

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $this->auditValues(
            $materialGrade->load('materialType')
        );

        $materialGrade->update($validated);

        AuditHelper::log(
            'Material Grades',
            'Updated',
            'MaterialGrade',
            $materialGrade->id,
            'Material grade/rating updated: '
                . $materialGrade->grade_name,
            $oldValues,
            $this->auditValues(
                $materialGrade->fresh('materialType')
            )
        );

        return redirect()
            ->route('material-grades.index')
            ->with(
                'success',
                'Material grade/rating updated successfully.'
            );
    }

    public function destroy(
        MaterialGrade $materialGrade
    ): RedirectResponse {
        $oldValues = $this->auditValues(
            $materialGrade->load('materialType')
        );

        $materialGrade->update([
            'is_active' => ! $materialGrade->is_active,
        ]);

        $materialGrade->refresh();

        AuditHelper::log(
            'Material Grades',
            $materialGrade->is_active
                ? 'Activated'
                : 'Deactivated',
            'MaterialGrade',
            $materialGrade->id,
            $materialGrade->is_active
                ? 'Material grade/rating activated: '
                    . $materialGrade->grade_name
                : 'Material grade/rating deactivated: '
                    . $materialGrade->grade_name,
            $oldValues,
            $this->auditValues(
                $materialGrade->load('materialType')
            )
        );

        return back()->with(
            'success',
            'Material grade/rating status updated successfully.'
        );
    }

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

    private function validateGrade(
        Request $request,
        ?MaterialGrade $materialGrade = null
    ): array {
        return $request->validate([
            'material_type_id' => [
                'required',
                'integer',
                'exists:material_types,id',
            ],

            'grade_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'material_grades',
                    'grade_name'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'material_type_id',
                            $request->integer('material_type_id')
                        )
                    )
                    ->ignore($materialGrade?->id),
            ],

            'grade_code' => [
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
            'grade_name.unique' =>
                'This grade/rating already exists for the selected Material Type.',
        ]);
    }

    private function auditValues(
        MaterialGrade $materialGrade
    ): array {
        return [
            'id' => $materialGrade->id,
            'material_type_id' =>
                $materialGrade->material_type_id,
            'material_type_name' =>
                $materialGrade->materialType?->material_type_name,
            'material_group' =>
                $materialGrade->materialType?->material_group,
            'grade_name' =>
                $materialGrade->grade_name,
            'grade_code' =>
                $materialGrade->grade_code,
            'sequence' =>
                $materialGrade->sequence,
            'is_active' =>
                $materialGrade->is_active,
            'remarks' =>
                $materialGrade->remarks,
            'created_by' =>
                $materialGrade->created_by,
        ];
    }
}