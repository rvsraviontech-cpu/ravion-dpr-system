<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialType;
use App\Models\MaterialVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MaterialVariantController extends Controller
{
    public function store(Request $request, MaterialType $materialType): RedirectResponse
    {
        $data = $this->validatedData($request, $materialType);
        $data['material_type_id'] = $materialType->id;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $variant = DB::transaction(function () use ($data) {
            $variant = MaterialVariant::create($data);
            AuditHelper::log('Product Variant', 'Created', 'MaterialVariant', $variant->id,
                'Variant created: '.$variant->variant_name, null, $variant->toArray());
            return $variant;
        });

        return redirect()->route('material-types.show', ['material_type' => $materialType->id])
            ->with('success', 'Variant '.$variant->variant_name.' created successfully.');
    }

    public function update(Request $request, MaterialType $materialType, MaterialVariant $materialVariant): RedirectResponse
    {
        $this->ensureOwnership($materialType, $materialVariant);
        $data = $this->validatedData($request, $materialType, $materialVariant);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        DB::transaction(function () use ($materialVariant, $data) {
            $before = $materialVariant->toArray();
            $materialVariant->update($data);
            AuditHelper::log('Product Variant', 'Updated', 'MaterialVariant', $materialVariant->id,
                'Variant updated: '.$materialVariant->variant_name, $before, $materialVariant->fresh()->toArray());
        });

        return redirect()->route('material-types.show', ['material_type' => $materialType->id])
            ->with('success', 'Variant updated successfully.');
    }

    public function toggleStatus(MaterialType $materialType, MaterialVariant $materialVariant): RedirectResponse
    {
        $this->ensureOwnership($materialType, $materialVariant);

        DB::transaction(function () use ($materialVariant) {
            $before = $materialVariant->toArray();
            $materialVariant->update(['is_active' => ! $materialVariant->is_active]);
            AuditHelper::log('Product Variant', $materialVariant->is_active ? 'Activated' : 'Deactivated',
                'MaterialVariant', $materialVariant->id,
                'Variant status changed: '.$materialVariant->variant_name,
                $before, $materialVariant->fresh()->toArray());
        });

        return back()->with('success', 'Variant status updated successfully.');
    }

    private function validatedData(Request $request, MaterialType $materialType, ?MaterialVariant $variant = null): array
    {
        $data = $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'variant_code' => ['nullable', 'string', 'max:100'],
            'material_specification_id' => ['nullable', 'integer', Rule::exists('material_specifications', 'id')
                ->where(fn ($query) => $query->where('material_type_id', $materialType->id))],
            'material_grade_id' => ['nullable', 'integer', Rule::exists('material_grades', 'id')
                ->where(fn ($query) => $query->where('material_type_id', $materialType->id))],
            'unit_master_id' => ['nullable', 'integer', Rule::exists('unit_masters', 'id')],
            'size_dimension' => ['nullable', 'string', 'max:255'],
            'finish' => ['nullable', 'string', 'max:255'],
            'colour_shade' => ['nullable', 'string', 'max:255'],
            'search_aliases' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $name = mb_strtolower(trim($data['variant_name']));
        $duplicate = MaterialVariant::query()
            ->where('material_type_id', $materialType->id)
            ->when($variant, fn ($query) => $query->whereKeyNot($variant->id))
            ->get(['id', 'variant_name'])
            ->contains(fn ($item) => mb_strtolower(trim($item->variant_name)) === $name);

        if ($duplicate) {
            throw ValidationException::withMessages([
                'variant_name' => 'A variant with this name already exists for the product.',
            ]);
        }

        return $data;
    }

    private function ensureOwnership(MaterialType $materialType, MaterialVariant $materialVariant): void
    {
        abort_unless((int) $materialVariant->material_type_id === (int) $materialType->id, 404);
    }
}
