<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Models\ActivityDivision;
use App\Models\Contractor;
use App\Models\ContractorServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractorController extends Controller
{
    use MasterAuditTrait;

    public function index(Request $request)
{
    $contractors = Contractor::with([
            'divisions',
            'serviceCategories.division',
        ])
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('contractor_code', 'like', '%' . $request->search . '%')
                    ->orWhere('contractor_name', 'like', '%' . $request->search . '%')
                    ->orWhere('company_name', 'like', '%' . $request->search . '%')
                    ->orWhere('mobile', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        })
        ->when($request->activity_division_id, function ($query) use ($request) {
            $query->whereHas('divisions', function ($q) use ($request) {
                $q->where('activity_divisions.id', $request->activity_division_id);
            });
        })
        ->when($request->city, function ($query) use ($request) {
            $query->where('city', $request->city);
        })
        ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->orderBy('contractor_name')
        ->paginate(config('rds.pagination.per_page', 25))
        ->withQueryString();

    $activityDivisions = ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->orderBy('name')
        ->get();

    $cities = Contractor::whereNotNull('city')
        ->where('city', '!=', '')
        ->distinct()
        ->orderBy('city')
        ->pluck('city');

    return view('contractors.index', compact(
        'contractors',
        'activityDivisions',
        'cities'
    ));
}

    public function create()
    {
        return view('contractors.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateContractor($request);

        DB::transaction(function () use ($validated, &$contractor) {
            $divisionIds = $validated['division_ids'] ?? [];
            $serviceCategoryIds = $validated['service_category_ids'] ?? [];

            unset($validated['division_ids'], $validated['service_category_ids']);

            $validated['contractor_code'] = $validated['contractor_code'] ?: $this->nextContractorCode();
            $validated['status'] = $validated['status'] ?? 'Active';
            $validated['is_preferred'] = isset($validated['is_preferred']) ? 1 : 0;

            $contractor = Contractor::create($validated);

            $contractor->divisions()->sync($divisionIds);
            $contractor->serviceCategories()->sync($serviceCategoryIds);
        });

        $this->auditCreated(
            'Contractors',
            'Contractor',
            $contractor->id,
            $contractor->contractor_name,
            $this->auditValues($contractor)
        );

        return redirect()
            ->route('contractors.index')
            ->with('success', 'Contractor registered successfully.');
    }

    public function show(Contractor $contractor)
    {
        $contractor->load([
            'divisions',
            'serviceCategories.division',
        ]);

        return view('contractors.show', compact('contractor'));
    }

    public function edit(Contractor $contractor)
    {
        $contractor->load([
            'divisions',
            'serviceCategories',
        ]);

        return view('contractors.edit', array_merge(
            compact('contractor'),
            $this->formData()
        ));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $this->validateContractor($request, $contractor);

        $oldValues = $this->auditValues($contractor);

        DB::transaction(function () use ($validated, $contractor) {
            $divisionIds = $validated['division_ids'] ?? [];
            $serviceCategoryIds = $validated['service_category_ids'] ?? [];

            unset($validated['division_ids'], $validated['service_category_ids']);

            $validated['is_preferred'] = isset($validated['is_preferred']) ? 1 : 0;

            $contractor->update($validated);
            $contractor->divisions()->sync($divisionIds);
            $contractor->serviceCategories()->sync($serviceCategoryIds);
        });

        $contractor->refresh();

        $this->auditUpdated(
            'Contractors',
            'Contractor',
            $contractor->id,
            $contractor->contractor_name,
            $oldValues,
            $this->auditValues($contractor)
        );

        return redirect()
            ->route('contractors.index')
            ->with('success', 'Contractor updated successfully.');
    }

    public function destroy(Contractor $contractor)
    {
        $oldValues = $this->auditValues($contractor);

        $contractor->update([
            'status' => $contractor->status === 'Active' ? 'Inactive' : 'Active',
        ]);

        $contractor->refresh();

        $this->auditStatusChanged(
            'Contractors',
            'Contractor',
            $contractor->id,
            $contractor->contractor_name,
            $contractor->status === 'Active',
            $oldValues,
            $this->auditValues($contractor)
        );

        return redirect()
            ->route('contractors.index')
            ->with('success', 'Contractor status updated successfully.');
    }

    private function formData(): array
    {
        return [
            'activityDivisions' => ActivityDivision::where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'serviceCategories' => ContractorServiceCategory::with('division')
                ->where('is_active', true)
                ->orderBy('activity_division_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function validateContractor(Request $request, ?Contractor $contractor = null): array
    {
        $contractorId = $contractor?->id;

        return $request->validate([
            'contractor_code' => 'nullable|string|max:50|unique:contractors,contractor_code,' . $contractorId,
            'contractor_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',

            'city' => 'nullable|string|max:150',
            'district' => 'nullable|string|max:150',
            'state' => 'nullable|string|max:150',
            'pincode' => 'nullable|string|max:20',
            'address' => 'nullable|string',

            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'aadhaar_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:100',

            'rating' => 'nullable|integer|min:1|max:5',
            'experience_years' => 'nullable|integer|min:0|max:80',
            'is_preferred' => 'nullable|boolean',

            'status' => 'required|in:Active,Inactive',
            'remarks' => 'nullable|string',

            'division_ids' => 'nullable|array',
            'division_ids.*' => 'exists:activity_divisions,id',

            'service_category_ids' => 'nullable|array',
            'service_category_ids.*' => 'exists:contractor_service_categories,id',
        ]);
    }

    private function nextContractorCode(): string
    {
        $lastId = Contractor::max('id') + 1;

        return 'CONT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
    }

    private function auditValues(Contractor $contractor): array
    {
        return $contractor->only([
            'id',
            'contractor_code',
            'contractor_name',
            'company_name',
            'mobile',
            'alternate_mobile',
            'email',
            'city',
            'district',
            'state',
            'pincode',
            'address',
            'gst_number',
            'pan_number',
            'aadhaar_number',
            'license_number',
            'rating',
            'experience_years',
            'is_preferred',
            'status',
            'remarks',
        ]);
    }
}