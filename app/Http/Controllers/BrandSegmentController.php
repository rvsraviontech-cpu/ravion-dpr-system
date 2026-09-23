<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\BrandSegment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandSegmentController extends Controller
{
    /**
     * Display the Brand Segment master list.
     */
    public function index(Request $request): View
    {
        $query = BrandSegment::query()
            ->withCount('brands');

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';

            $query->where(function ($builder) use ($like) {
                $builder
                    ->whereRaw('LOWER(segment_code) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(segment_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(remarks, "")) LIKE ?', [$like]);
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        $brandSegments = $query
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('segment_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'brand-segments.index',
            compact('brandSegments')
        );
    }

    /**
     * Show the form for creating a Brand Segment.
     */
    public function create(): View
    {
        return view('brand-segments.create');
    }

    /**
     * Store a new Brand Segment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBrandSegment($request);

        $validated['segment_code'] = strtoupper(
            trim($validated['segment_code'])
        );

        $validated['segment_name'] = trim(
            $validated['segment_name']
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = true;
        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $brandSegment = BrandSegment::create($validated);

        AuditHelper::log(
            'Brand Segment',
            'Created',
            'BrandSegment',
            $brandSegment->id,
            'Brand Segment created: ' . $brandSegment->segment_name,
            null,
            $this->auditValues($brandSegment)
        );

        return redirect()
            ->route('brand-segments.index')
            ->with('success', 'Brand Segment created successfully.');
    }

    /**
     * Show the form for editing a Brand Segment.
     */
    public function edit(BrandSegment $brandSegment): View
    {
        $brandSegment->loadCount('brands');

        return view(
            'brand-segments.edit',
            compact('brandSegment')
        );
    }

    /**
     * Update a Brand Segment.
     */
    public function update(
        Request $request,
        BrandSegment $brandSegment
    ): RedirectResponse {
        $validated = $this->validateBrandSegment(
            $request,
            $brandSegment
        );

        $oldValues = $this->auditValues($brandSegment);

        $validated['segment_code'] = strtoupper(
            trim($validated['segment_code'])
        );

        $validated['segment_name'] = trim(
            $validated['segment_name']
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $brandSegment->update($validated);
        $brandSegment->refresh();

        AuditHelper::log(
            'Brand Segment',
            'Updated',
            'BrandSegment',
            $brandSegment->id,
            'Brand Segment updated: ' . $brandSegment->segment_name,
            $oldValues,
            $this->auditValues($brandSegment)
        );

        return redirect()
            ->route('brand-segments.index')
            ->with('success', 'Brand Segment updated successfully.');
    }

    /**
     * Activate or deactivate a Brand Segment.
     *
     * Existing Brand records remain untouched. Deactivation only prevents
     * the Segment from being selected for new/updated Brand classifications.
     */
    public function toggleStatus(
        BrandSegment $brandSegment
    ): RedirectResponse {
        $oldValues = $this->auditValues($brandSegment);

        $brandSegment->update([
            'is_active' => ! $brandSegment->is_active,
        ]);

        $brandSegment->refresh();

        AuditHelper::log(
            'Brand Segment',
            $brandSegment->is_active ? 'Activated' : 'Deactivated',
            'BrandSegment',
            $brandSegment->id,
            $brandSegment->is_active
                ? 'Brand Segment activated: ' . $brandSegment->segment_name
                : 'Brand Segment deactivated: ' . $brandSegment->segment_name,
            $oldValues,
            $this->auditValues($brandSegment)
        );

        return back()->with(
            'success',
            $brandSegment->is_active
                ? 'Brand Segment activated successfully.'
                : 'Brand Segment deactivated successfully.'
        );
    }

    /**
     * Validate Brand Segment input.
     */
    private function validateBrandSegment(
        Request $request,
        ?BrandSegment $brandSegment = null
    ): array {
        return $request->validate(
            [
                'segment_code' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('brand_segments', 'segment_code')
                        ->ignore($brandSegment?->id),
                ],

                'segment_name' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('brand_segments', 'segment_name')
                        ->ignore($brandSegment?->id),
                ],

                'sort_order' => [
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
            ],
            [
                'segment_code.required' =>
                    'Brand Segment Code is required.',

                'segment_code.unique' =>
                    'This Brand Segment Code already exists.',

                'segment_name.required' =>
                    'Brand Segment Name is required.',

                'segment_name.unique' =>
                    'This Brand Segment Name already exists.',
            ]
        );
    }

    /**
     * Values stored in the audit trail.
     */
    private function auditValues(BrandSegment $brandSegment): array
    {
        return [
            'id' => $brandSegment->id,
            'segment_code' => $brandSegment->segment_code,
            'segment_name' => $brandSegment->segment_name,
            'sort_order' => $brandSegment->sort_order,
            'is_active' => $brandSegment->is_active,
            'remarks' => $brandSegment->remarks,
        ];
    }

    /**
     * Convert an empty text value to NULL.
     */
    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}