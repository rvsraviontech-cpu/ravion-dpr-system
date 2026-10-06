<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use App\Models\SitePhoto;
use App\Models\SitePhotoEntry;
use App\Models\WorkActivity;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SitePhotoController extends Controller
{
    public function index(Request $request): View
    {
        $query = SitePhotoEntry::query()
            ->with([
                'project',
                'reporter',
                'block',
                'floor',
                'unit',
                'room',
                'subspace',
                'workPackage',
                'workSection',
                'workActivity',
                'contractor',
                'dpr',
                'photos.uploader',
            ])
            ->withCount('photos');

        if ($this->isEngineer()) {
            $query->where('reported_by', auth()->id());
        }

        if ($request->filled('project_id')) {
            $projectId = $request->integer('project_id');
            $this->ensureProjectAccess($projectId);
            $query->where('project_id', $projectId);
        }

        if ($request->filled('photo_date')) {
            $query->whereDate('photo_date', $request->input('photo_date'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('dpr_link')) {
            if ($request->input('dpr_link') === 'linked') {
                $query->whereNotNull('dpr_id');
            }

            if ($request->input('dpr_link') === 'unlinked') {
                $query->whereNull('dpr_id');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn (Builder $projectQuery) =>
                            $projectQuery->where('project_name', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'workActivity',
                        fn (Builder $activityQuery) =>
                            $activityQuery->where('name', 'like', "%{$search}%")
                    );
            });
        }

        $sitePhotoEntries = $query
            ->orderByDesc('photo_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('site-photos.index', [
            'sitePhotoEntries' => $sitePhotoEntries,
            'projects' => $this->availableProjects(),
            'categories' => SitePhotoEntry::categories(),
            'statuses' => SitePhotoEntry::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('site-photos.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $projectId = (int) $validated['project_id'];

        $this->ensureProjectAccess($projectId);
        $this->validateLocationHierarchy($projectId, $validated);
        $this->validateWorkHierarchy($validated);

        $storedPaths = [];

        try {
            $entry = DB::transaction(
                function () use ($request, $validated, &$storedPaths): SitePhotoEntry {
                    $status = $validated['status'] ?? 'Submitted';

                    $entry = SitePhotoEntry::create([
                        'project_id' => (int) $validated['project_id'],
                        'photo_date' => $validated['photo_date'],
                        'reported_by' => auth()->id(),

                        'project_block_id' => $validated['project_block_id'] ?? null,
                        'project_floor_id' => $validated['project_floor_id'] ?? null,
                        'project_unit_id' => $validated['project_unit_id'] ?? null,
                        'project_room_id' => $validated['project_room_id'] ?? null,
                        'project_subspace_id' => $validated['project_subspace_id'] ?? null,

                        'work_package_id' => $validated['work_package_id'] ?? null,
                        'work_section_id' => $validated['work_section_id'] ?? null,
                        'work_activity_id' => $validated['work_activity_id'] ?? null,

                        'contractor_id' => $validated['contractor_id'] ?? null,
                        'category' => $validated['category'],
                        'title' => $this->nullableTrim($validated['title'] ?? null),
                        'remarks' => $this->nullableTrim($validated['remarks'] ?? null),

                        'status' => $status,
                        'submitted_at' => $status === 'Submitted' ? now() : null,
                        'dpr_id' => null,
                    ]);

                    $this->storePhotos(
                        request: $request,
                        entry: $entry,
                        storedPaths: $storedPaths
                    );

                    if (! $entry->photos()->exists()) {
                        throw ValidationException::withMessages([
                            'photos' => 'Please take or select at least one Site Photo.',
                        ]);
                    }

                    $entry->load($this->entryRelationships());

                    AuditHelper::log(
                        'Site Photos',
                        'Created',
                        'SitePhotoEntry',
                        $entry->id,
                        'Site Photo entry created.',
                        null,
                        $this->auditValues($entry)
                    );

                    return $entry;
                }
            );

            return redirect()
                ->route('site-photos.show', $entry)
                ->with('success', 'Site Photos saved successfully.');
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to save Site Photos.');
        }
    }

    public function show(SitePhotoEntry $sitePhoto): View
{
    $sitePhoto->load($this->entryRelationships());

    $this->ensureEntryAccess($sitePhoto);

    return view('site-photos.show', [
        'sitePhoto' => $sitePhoto,
    ]);
}

    public function edit(SitePhotoEntry $sitePhoto): View
    {
        $sitePhoto->load($this->entryRelationships());

        $this->ensureEntryAccess($sitePhoto);

        abort_if(
            $this->isEngineer() && $sitePhoto->dpr_id !== null,
            403,
            'This Site Photo entry is already linked to a DPR and cannot be edited.'
        );

        return view(
            'site-photos.edit',
            array_merge(
                ['sitePhotoEntry' => $sitePhoto],
                $this->formData()
            )
        );
    }

    public function update(
        Request $request,
        SitePhotoEntry $sitePhoto
    ): RedirectResponse {
        $sitePhoto->load($this->entryRelationships());

        $this->ensureEntryAccess($sitePhoto);

        abort_if(
            $this->isEngineer() && $sitePhoto->dpr_id !== null,
            403,
            'This Site Photo entry is already linked to a DPR and cannot be edited.'
        );

        $validated = $this->validatePayload($request, updating: true);

        $projectId = (int) $validated['project_id'];

        $this->ensureProjectAccess($projectId);
        $this->validateLocationHierarchy($projectId, $validated);
        $this->validateWorkHierarchy($validated);

        $storedPaths = [];
        $pathsToDeleteAfterCommit = [];

        try {
            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $sitePhoto,
                    &$storedPaths,
                    &$pathsToDeleteAfterCommit
                ): void {
                    $oldValues = $this->auditValues($sitePhoto);

                    $status = $validated['status'] ?? $sitePhoto->status;

                    $sitePhoto->update([
                        'project_id' => (int) $validated['project_id'],
                        'photo_date' => $validated['photo_date'],

                        'project_block_id' => $validated['project_block_id'] ?? null,
                        'project_floor_id' => $validated['project_floor_id'] ?? null,
                        'project_unit_id' => $validated['project_unit_id'] ?? null,
                        'project_room_id' => $validated['project_room_id'] ?? null,
                        'project_subspace_id' => $validated['project_subspace_id'] ?? null,

                        'work_package_id' => $validated['work_package_id'] ?? null,
                        'work_section_id' => $validated['work_section_id'] ?? null,
                        'work_activity_id' => $validated['work_activity_id'] ?? null,

                        'contractor_id' => $validated['contractor_id'] ?? null,
                        'category' => $validated['category'],
                        'title' => $this->nullableTrim($validated['title'] ?? null),
                        'remarks' => $this->nullableTrim($validated['remarks'] ?? null),

                        'status' => $status,
                        'submitted_at' => $status === 'Submitted'
                            ? ($sitePhoto->submitted_at ?? now())
                            : null,
                    ]);

                    $removePhotoIds = collect($validated['remove_photo_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values();

                    if ($removePhotoIds->isNotEmpty()) {
                        $photos = $sitePhoto->photos()
                            ->whereIn('id', $removePhotoIds)
                            ->get();

                        foreach ($photos as $photo) {
                            $pathsToDeleteAfterCommit[] = $photo->file_path;
                            $photo->delete();
                        }
                    }

                    $this->storePhotos(
                        request: $request,
                        entry: $sitePhoto,
                        storedPaths: $storedPaths
                    );

                    if (! $sitePhoto->photos()->exists()) {
                        throw ValidationException::withMessages([
                            'photos' => 'A Site Photo entry must contain at least one photo.',
                        ]);
                    }

                    $sitePhoto->load($this->entryRelationships());

                    AuditHelper::log(
                        'Site Photos',
                        'Updated',
                        'SitePhotoEntry',
                        $sitePhoto->id,
                        'Site Photo entry updated.',
                        $oldValues,
                        $this->auditValues($sitePhoto)
                    );
                }
            );

            $this->deleteStoredPaths($pathsToDeleteAfterCommit);

            return redirect()
                ->route('site-photos.show', $sitePhoto)
                ->with('success', 'Site Photos updated successfully.');
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update Site Photos.');
        }
    }

    public function destroy(SitePhotoEntry $sitePhoto): RedirectResponse
{
    $sitePhoto->load($this->entryRelationships());

    $this->ensureEntryAccess($sitePhoto);

    /*
    |--------------------------------------------------------------------------
    | Engineer Protection
    |--------------------------------------------------------------------------
    |
    | Site Photos are construction evidence. Engineers may create and edit
    | their own unlinked entries, but they must never permanently delete an
    | entire Site Photo entry.
    |
    */
    abort_if(
        $this->isEngineer(),
        403,
        'Engineers cannot delete Site Photo entries.'
    );

    /*
    |--------------------------------------------------------------------------
    | DPR Protection
    |--------------------------------------------------------------------------
    |
    | Once the Site Photo entry has been linked to a DPR, it becomes part of
    | the formal daily record and must not be deleted.
    |
    */
    abort_if(
        $sitePhoto->dpr_id !== null,
        403,
        'This Site Photo entry is already linked to a DPR and cannot be deleted.'
    );

    $oldValues = $this->auditValues($sitePhoto);

    $photoPaths = $sitePhoto->photos
        ->pluck('file_path')
        ->filter()
        ->values()
        ->all();

    $entryId = $sitePhoto->id;

    DB::transaction(
        function () use ($sitePhoto, $entryId, $oldValues): void {
            $sitePhoto->delete();

            AuditHelper::log(
                'Site Photos',
                'Deleted',
                'SitePhotoEntry',
                $entryId,
                'Site Photo entry deleted.',
                $oldValues,
                null
            );
        }
    );

    $this->deleteStoredPaths($photoPaths);

    return redirect()
        ->route('site-photos.index')
        ->with('success', 'Site Photo entry deleted successfully.');
}

    public function destroyPhoto(
        SitePhotoEntry $sitePhoto,
        SitePhoto $photo
    ): RedirectResponse {
        $sitePhoto->load($this->entryRelationships());

        $this->ensureEntryAccess($sitePhoto);

        abort_if(
            $sitePhoto->dpr_id !== null,
            403,
            'Photos cannot be removed after this Site Photo entry is linked to a DPR.'
        );

        abort_unless(
            (int) $photo->site_photo_entry_id === (int) $sitePhoto->id,
            404
        );

        abort_if(
            $sitePhoto->photos()->count() <= 1,
            422,
            'A Site Photo entry must contain at least one photo.'
        );

        $oldValues = $this->auditValues($sitePhoto);
        $path = $photo->file_path;
        $photoId = $photo->id;

        DB::transaction(
            function () use (
                $sitePhoto,
                $photo,
                $photoId,
                $oldValues
            ): void {
                $photo->delete();

                $sitePhoto->load($this->entryRelationships());

                AuditHelper::log(
                    'Site Photos',
                    'Photo Deleted',
                    'SitePhotoEntry',
                    $sitePhoto->id,
                    "Site Photo #{$photoId} deleted.",
                    $oldValues,
                    $this->auditValues($sitePhoto)
                );
            }
        );

        $this->deleteStoredPaths([$path]);

        return back()->with('success', 'Photo deleted successfully.');
    }

    private function validatePayload(
        Request $request,
        bool $updating = false
    ): array {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'photo_date' => [
                'required',
                'date',
            ],

            'project_block_id' => [
                'nullable',
                'integer',
                'exists:project_blocks,id',
            ],

            'project_floor_id' => [
                'nullable',
                'integer',
                'exists:project_floors,id',
            ],

            'project_unit_id' => [
                'nullable',
                'integer',
                'exists:project_units,id',
            ],

            'project_room_id' => [
                'nullable',
                'integer',
                'exists:project_rooms,id',
            ],

            'project_subspace_id' => [
                'nullable',
                'integer',
                'exists:project_subspaces,id',
            ],

            'work_package_id' => [
                'nullable',
                'integer',
                'exists:work_packages,id',
            ],

            'work_section_id' => [
                'nullable',
                'integer',
                'exists:work_sections,id',
            ],

            'work_activity_id' => [
                'nullable',
                'integer',
                'exists:work_activities,id',
            ],

            'contractor_id' => [
                'nullable',
                'integer',
                'exists:contractors,id',
            ],

            'category' => [
                'required',
                'string',
                'in:' . implode(',', SitePhotoEntry::categories()),
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', SitePhotoEntry::statuses()),
            ],

            'photos' => [
                $updating ? 'nullable' : 'required',
                'array',
                $updating ? 'max:20' : 'min:1',
                'max:20',
            ],

            'photos.*.file' => [
                $updating ? 'nullable' : 'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'photos.*.photo_type' => [
                'nullable',
                'string',
                'in:' . implode(',', SitePhoto::photoTypes()),
            ],

            'photos.*.caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'photos.*.captured_at' => [
                'nullable',
                'date',
            ],

            'remove_photo_ids' => [
                'nullable',
                'array',
            ],

            'remove_photo_ids.*' => [
                'integer',
                'exists:site_photos,id',
            ],
        ], [
            'photos.required' =>
                'Please take or select at least one Site Photo.',

            'photos.min' =>
                'Please take or select at least one Site Photo.',

            'photos.*.file.required' =>
                'Each Site Photo row must contain an image.',

            'photos.*.file.max' =>
                'Each Site Photo may be up to 10 MB.',
        ]);
    }

    private function validateLocationHierarchy(
        int $projectId,
        array $data
    ): void {
        if (! empty($data['project_block_id'])) {
            abort_unless(
                ProjectBlock::query()
                    ->whereKey($data['project_block_id'])
                    ->where('project_id', $projectId)
                    ->exists(),
                422,
                'The selected Block does not belong to the selected Project.'
            );
        }

        if (! empty($data['project_floor_id'])) {
            $query = ProjectFloor::query()
                ->whereKey($data['project_floor_id'])
                ->where('project_id', $projectId);

            if (! empty($data['project_block_id'])) {
                $query->where(
                    'project_block_id',
                    $data['project_block_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Floor does not belong to the selected Project/Block.'
            );
        }

        if (! empty($data['project_unit_id'])) {
            $query = ProjectUnit::query()
                ->whereKey($data['project_unit_id'])
                ->where('project_id', $projectId);

            if (! empty($data['project_block_id'])) {
                $query->where(
                    'project_block_id',
                    $data['project_block_id']
                );
            }

            if (! empty($data['project_floor_id'])) {
                $query->where(
                    'project_floor_id',
                    $data['project_floor_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Unit does not belong to the selected Project/Floor.'
            );
        }

        if (! empty($data['project_room_id'])) {
            $query = ProjectRoom::query()
                ->whereKey($data['project_room_id'])
                ->where('project_id', $projectId);

            if (! empty($data['project_block_id'])) {
                $query->where(
                    'project_block_id',
                    $data['project_block_id']
                );
            }

            if (! empty($data['project_floor_id'])) {
                $query->where(
                    'project_floor_id',
                    $data['project_floor_id']
                );
            }

            if (! empty($data['project_unit_id'])) {
                $query->where(
                    'project_unit_id',
                    $data['project_unit_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Room does not belong to the selected Project/Location hierarchy.'
            );
        }

        if (! empty($data['project_subspace_id'])) {
            $query = ProjectSubspace::query()
                ->whereKey($data['project_subspace_id'])
                ->where('project_id', $projectId);

            if (! empty($data['project_block_id'])) {
                $query->where(
                    'project_block_id',
                    $data['project_block_id']
                );
            }

            if (! empty($data['project_floor_id'])) {
                $query->where(
                    'project_floor_id',
                    $data['project_floor_id']
                );
            }

            if (! empty($data['project_unit_id'])) {
                $query->where(
                    'project_unit_id',
                    $data['project_unit_id']
                );
            }

            if (! empty($data['project_room_id'])) {
                $query->where(
                    'project_room_id',
                    $data['project_room_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Sub-space does not belong to the selected Project/Location hierarchy.'
            );
        }
    }

    private function validateWorkHierarchy(array $data): void
    {
        $packageId = ! empty($data['work_package_id'])
            ? (int) $data['work_package_id']
            : null;

        $sectionId = ! empty($data['work_section_id'])
            ? (int) $data['work_section_id']
            : null;

        $activityId = ! empty($data['work_activity_id'])
            ? (int) $data['work_activity_id']
            : null;

        if ($sectionId !== null && $packageId === null) {
            throw ValidationException::withMessages([
                'work_package_id' =>
                    'Select the Work Package before selecting a Work Section.',
            ]);
        }

        if ($activityId !== null && ($packageId === null || $sectionId === null)) {
            throw ValidationException::withMessages([
                'work_activity_id' =>
                    'Select the Work Package and Work Section before selecting a Work Activity.',
            ]);
        }

        if ($packageId !== null) {
            abort_unless(
                WorkPackage::query()
                    ->whereKey($packageId)
                    ->where('is_active', true)
                    ->exists(),
                422,
                'The selected Work Package is inactive or invalid.'
            );
        }

        if ($sectionId !== null) {
            abort_unless(
                WorkSection::query()
                    ->whereKey($sectionId)
                    ->where('work_package_id', $packageId)
                    ->where('is_active', true)
                    ->exists(),
                422,
                'The selected Work Section does not belong to the selected Work Package.'
            );
        }

        if ($activityId !== null) {
            abort_unless(
                WorkActivity::query()
                    ->whereKey($activityId)
                    ->where('work_package_id', $packageId)
                    ->where('work_section_id', $sectionId)
                    ->where('is_active', true)
                    ->where('is_selectable', true)
                    ->where('allow_photos', true)
                    ->exists(),
                422,
                'The selected Work Activity is not available for Site Photos.'
            );
        }
    }

    private function storePhotos(
        Request $request,
        SitePhotoEntry $entry,
        array &$storedPaths
    ): void {
        $rows = $request->input('photos', []);
        $files = $request->file('photos', []);

        if (! is_array($files)) {
            return;
        }

        $entry->loadMissing([
            'project',
            'reporter',
        ]);

        $projectName = $entry->project?->project_name ?? 'Project';
        $reporterName =
            $entry->reporter?->name
            ?? auth()->user()?->name
            ?? 'User';

        $datePart =
            $entry->photo_date?->format('Ymd')
            ?? now()->format('Ymd');

        $timePart = now()->format('His');

        $sequence = (int) $entry->photos()->max('sort_order') + 1;

        foreach ($files as $index => $fileData) {
            $file = is_array($fileData)
                ? ($fileData['file'] ?? null)
                : null;

            if (! $file) {
                continue;
            }

            $metadata = $rows[$index] ?? [];

            $photoType = $this->normalizePhotoType(
                $metadata['photo_type'] ?? 'Progress Photo'
            );

            $caption = $this->nullableTrim(
                $metadata['caption'] ?? null
            );

            $capturedAt = $metadata['captured_at'] ?? null;

            $extension = strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'jpg'
            );

            $filename = implode('-', [
                $this->filenamePart($projectName, 50),
                'site-photo',
                $this->filenamePart($entry->category, 40),
                $this->filenamePart($photoType, 35),
                $this->filenamePart($reporterName, 40),
                $datePart,
                $timePart,
                str_pad(
                    (string) $sequence,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            ]) . '.' . $extension;

            $directory = implode('/', [
                'site-photos',
                'project-' . $entry->project_id,
                'entry-' . $entry->id,
            ]);

            $path = $file->storeAs(
                $directory,
                $filename,
                'public'
            );

            $storedPaths[] = $path;

            SitePhoto::create([
                'site_photo_entry_id' => $entry->id,
                'uploaded_by' => auth()->id(),
                'photo_type' => $photoType,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'caption' => $caption,
                'sort_order' => $sequence,
                'captured_at' => $capturedAt,
            ]);

            $sequence++;
        }
    }

    private function formData(): array
    {
        return [
            'projects' => $this->availableProjects(),

            'projectBlocks' => ProjectBlock::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'projectFloors' => ProjectFloor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'projectUnits' => ProjectUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'projectRooms' => ProjectRoom::query()
                ->orderBy('name')
                ->get(),

            'projectSubspaces' => ProjectSubspace::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'workPackages' => WorkPackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'workSections' => WorkSection::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'workActivities' => WorkActivity::query()
                ->where('is_active', true)
                ->where('is_selectable', true)
                ->where('allow_photos', true)
                ->with(['package', 'section'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'contractors' => Contractor::query()
                ->orderBy('contractor_name')
                ->get(),

            'categories' => SitePhotoEntry::categories(),
            'statuses' => SitePhotoEntry::statuses(),
            'photoTypes' => SitePhoto::photoTypes(),
        ];
    }

    private function availableProjects()
    {
        if ($this->isEngineer()) {
            return auth()->user()
                ->projects()
                ->whereNotIn(
                    'projects.status',
                    ['Completed', 'Handed Over', 'Closed']
                )
                ->orderBy('project_name')
                ->get();
        }

        return Project::query()
            ->whereNotIn(
                'status',
                ['Completed', 'Handed Over', 'Closed']
            )
            ->orderBy('project_name')
            ->get();
    }

    private function ensureEntryAccess(SitePhotoEntry $entry): void
    {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            (int) $entry->reported_by === (int) auth()->id(),
            403,
            'Unauthorized Site Photo access.'
        );

        $this->ensureProjectAccess((int) $entry->project_id);
    }

    private function ensureProjectAccess(int $projectId): void
    {
        if (! $this->isEngineer()) {
            return;
        }

        $hasAccess = auth()->user()
            ->projects()
            ->where('projects.id', $projectId)
            ->exists();

        abort_unless(
            $hasAccess,
            403,
            'You are not assigned to this Project.'
        );
    }

    private function entryRelationships(): array
    {
        return [
            'dpr',
            'project',
            'reporter',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'workPackage',
            'workSection',
            'workActivity.package',
            'workActivity.section',
            'contractor',
            'photos.uploader',
        ];
    }

    private function auditValues(SitePhotoEntry $entry): array
    {
        $entry->loadMissing($this->entryRelationships());

        return [
            'id' => $entry->id,
            'dpr_id' => $entry->dpr_id,

            'project_id' => $entry->project_id,
            'project' => $entry->project?->project_name,

            'photo_date' => $entry->photo_date?->format('Y-m-d'),
            'reported_by' => $entry->reported_by,
            'reporter' => $entry->reporter?->name,

            'location' => $entry->location_path,

            'work_package_id' => $entry->work_package_id,
            'work_package' => $entry->workPackage?->name,

            'work_section_id' => $entry->work_section_id,
            'work_section' => $entry->workSection?->name,

            'work_activity_id' => $entry->work_activity_id,
            'work_activity' => $entry->workActivity?->name,
            'work_path' => $entry->work_path,

            'contractor_id' => $entry->contractor_id,
            'category' => $entry->category,
            'title' => $entry->title,
            'remarks' => $entry->remarks,
            'status' => $entry->status,

            'submitted_at' =>
                $entry->submitted_at?->toDateTimeString(),

            'photo_ids' => $entry->photos
                ->pluck('id')
                ->values()
                ->all(),

            'photo_count' => $entry->photos->count(),
        ];
    }

    private function normalizePhotoType(mixed $photoType): string
    {
        $photoType = trim((string) $photoType);

        return in_array(
            $photoType,
            SitePhoto::photoTypes(),
            true
        )
            ? $photoType
            : 'General Photo';
    }

    private function filenamePart(
        string $value,
        int $maxLength
    ): string {
        $slug = Str::slug(
            Str::limit(
                trim($value),
                $maxLength,
                ''
            ),
            '-'
        );

        return $slug !== ''
            ? $slug
            : 'NA';
    }

    private function deleteStoredPaths(array $paths): void
    {
        $paths = collect($paths)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    private function isEngineer(): bool
    {
        return auth()->user()?->role?->name === 'Engineer';
    }
}
