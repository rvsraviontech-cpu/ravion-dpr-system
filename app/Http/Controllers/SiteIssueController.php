<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use App\Models\SiteIssue;
use App\Models\SiteIssuePhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SiteIssueController extends Controller
{
    private const ISSUE_TYPES = [
        'Material Shortage',
        'Drawing Pending',
        'Client Approval Pending',
        'Labour Shortage',
        'Contractor Delay',
        'Machinery Breakdown',
        'Safety Issue',
        'Quality Issue',
        'Other',
    ];

    private const PRIORITIES = [
        'Low',
        'Medium',
        'High',
        'Critical',
    ];

    private const STATUSES = [
        'Open',
        'Assigned',
        'In Progress',
        'Resolved',
        'Verified',
        'Closed',
    ];

    private const PHOTO_TYPES = [
        'Issue',
        'Damage',
        'Safety',
        'Quality',
        'Delay',
        'Before Resolution',
        'After Resolution',
        'Other',
    ];

    public function index(Request $request): View
    {
        $query = SiteIssue::query()
            ->with([
                'project',
                'block',
                'floor',
                'unit',
                'room',
                'subspace',
                'activity',
                'creator',
                'photos',
            ]);

        if ($this->isEngineer()) {
            $query->where('created_by', auth()->id());
        }

        if ($request->filled('issue_date')) {
            $query->whereDate(
                'issue_date',
                $request->input('issue_date')
            );
        }

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->input('priority')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
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
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('issue_type', 'like', "%{$search}%")
                    ->orWhere('responsible_person', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn (Builder $projectQuery) =>
                            $projectQuery->where(
                                'project_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'activity',
                        fn (Builder $activityQuery) =>
                            $activityQuery->where(
                                'activity_name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        $siteIssues = $query
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $projects = $this->availableProjects();

        return view(
            'site-issues.index',
            compact(
                'siteIssues',
                'projects'
            ) + [
                'issueTypes' => self::ISSUE_TYPES,
                'priorities' => self::PRIORITIES,
                'statuses' => self::STATUSES,
            ]
        );
    }

    public function create(): View
    {
        return view(
            'site-issues.create',
            $this->formData()
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $projectId = (int) $validated['project_id'];

        $this->ensureProjectAccess($projectId);

        $this->validateLocationHierarchy(
            projectId: $projectId,
            data: $validated
        );

        $storedPaths = [];

        try {
            $siteIssue = DB::transaction(
                function () use (
                    $request,
                    $validated,
                    &$storedPaths
                ): SiteIssue {
                    $siteIssue = SiteIssue::create([
                        'dpr_id' => null,

                        'project_id' =>
                            $validated['project_id'],

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'project_floor_id' =>
                            $validated['project_floor_id'] ?? null,

                        'project_unit_id' =>
                            $validated['project_unit_id'] ?? null,

                        'project_room_id' =>
                            $validated['project_room_id'] ?? null,

                        'project_subspace_id' =>
                            $validated['project_subspace_id'] ?? null,

                        'activity_id' =>
                            $validated['activity_id'] ?? null,

                        'issue_date' =>
                            $validated['issue_date'],

                        'issue_type' =>
                            $validated['issue_type'],

                        'title' =>
                            trim($validated['title']),

                        'related_activity' =>
                            $this->nullableTrim(
                                $validated['related_activity'] ?? null
                            ),

                        'description' =>
                            trim($validated['description']),

                        'root_cause' =>
                            $this->nullableTrim(
                                $validated['root_cause'] ?? null
                            ),

                        'responsible_person' =>
                            $this->nullableTrim(
                                $validated['responsible_person'] ?? null
                            ),

                        'target_closure_date' =>
                            $validated['target_closure_date'] ?? null,

                        'actual_closure_date' => null,

                        'priority' =>
                            $validated['priority'],

                        'status' =>
                            $validated['status'],

                        'escalated_to_pmo' =>
                            $request->boolean('escalated_to_pmo'),

                        'escalated_to_management' =>
                            $request->boolean('escalated_to_management'),

                        'resolution' => null,

                        'created_by' =>
                            auth()->id(),

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),
                    ]);

                    $this->storePhotos(
                        request: $request,
                        siteIssue: $siteIssue,
                        storedPaths: $storedPaths
                    );

                    $siteIssue->load(
                        $this->issueRelationships()
                    );

                    AuditHelper::log(
                        'Site Issues',
                        'Created',
                        'SiteIssue',
                        $siteIssue->id,
                        'Site issue created',
                        null,
                        $this->auditValues($siteIssue)
                    );

                    return $siteIssue;
                }
            );

            return redirect()
                ->route('site-issues.show', $siteIssue)
                ->with(
                    'success',
                    'Site issue created successfully.'
                );
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to save Site Issue.'
                );
        }
    }

    public function show(SiteIssue $siteIssue): View
    {
        $siteIssue->load(
            $this->issueRelationships()
        );

        $this->ensureIssueAccess($siteIssue);

        return view(
            'site-issues.show',
            compact('siteIssue')
        );
    }

    public function edit(SiteIssue $siteIssue): View
    {
        $siteIssue->load(
            $this->issueRelationships()
        );

        $this->ensureIssueAccess($siteIssue);

        return view(
            'site-issues.edit',
            array_merge(
                compact('siteIssue'),
                $this->formData()
            )
        );
    }

    public function update(
        Request $request,
        SiteIssue $siteIssue
    ): RedirectResponse {
        $siteIssue->load(
            $this->issueRelationships()
        );

        $this->ensureIssueAccess($siteIssue);

        $validated = $this->validatePayload(
            $request,
            updating: true
        );

        $projectId = (int) $validated['project_id'];

        $this->ensureProjectAccess($projectId);

        $this->validateLocationHierarchy(
            projectId: $projectId,
            data: $validated
        );

        $storedPaths = [];
        $pathsToDeleteAfterCommit = [];

        try {
            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $siteIssue,
                    &$storedPaths,
                    &$pathsToDeleteAfterCommit
                ): void {
                    $oldValues =
                        $this->auditValues($siteIssue);

                    $oldStatus = $siteIssue->status;

                    $actualClosureDate =
                        $validated['actual_closure_date'] ?? null;

                    if (
                        in_array(
                            $validated['status'],
                            ['Resolved', 'Verified', 'Closed'],
                            true
                        )
                        && empty($actualClosureDate)
                    ) {
                        $actualClosureDate = now()->toDateString();
                    }

                    $siteIssue->update([
                        'project_id' =>
                            $validated['project_id'],

                        'project_block_id' =>
                            $validated['project_block_id'] ?? null,

                        'project_floor_id' =>
                            $validated['project_floor_id'] ?? null,

                        'project_unit_id' =>
                            $validated['project_unit_id'] ?? null,

                        'project_room_id' =>
                            $validated['project_room_id'] ?? null,

                        'project_subspace_id' =>
                            $validated['project_subspace_id'] ?? null,

                        'activity_id' =>
                            $validated['activity_id'] ?? null,

                        'issue_date' =>
                            $validated['issue_date'],

                        'issue_type' =>
                            $validated['issue_type'],

                        'title' =>
                            trim($validated['title']),

                        'related_activity' =>
                            $this->nullableTrim(
                                $validated['related_activity'] ?? null
                            ),

                        'description' =>
                            trim($validated['description']),

                        'root_cause' =>
                            $this->nullableTrim(
                                $validated['root_cause'] ?? null
                            ),

                        'responsible_person' =>
                            $this->nullableTrim(
                                $validated['responsible_person'] ?? null
                            ),

                        'target_closure_date' =>
                            $validated['target_closure_date'] ?? null,

                        'actual_closure_date' =>
                            $actualClosureDate,

                        'priority' =>
                            $validated['priority'],

                        'status' =>
                            $validated['status'],

                        'escalated_to_pmo' =>
                            $request->boolean('escalated_to_pmo'),

                        'escalated_to_management' =>
                            $request->boolean('escalated_to_management'),

                        'resolution' =>
                            $this->nullableTrim(
                                $validated['resolution'] ?? null
                            ),

                        'remarks' =>
                            $this->nullableTrim(
                                $validated['remarks'] ?? null
                            ),
                    ]);

                    $removePhotoIds = collect(
                        $validated['remove_photo_ids'] ?? []
                    )
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values();

                    if ($removePhotoIds->isNotEmpty()) {
                        $photos = $siteIssue->photos()
                            ->whereIn('id', $removePhotoIds)
                            ->get();

                        foreach ($photos as $photo) {
                            $pathsToDeleteAfterCommit[] =
                                $photo->file_path;

                            $photo->delete();
                        }
                    }

                    $this->storePhotos(
                        request: $request,
                        siteIssue: $siteIssue,
                        storedPaths: $storedPaths
                    );

                    $siteIssue->load(
                        $this->issueRelationships()
                    );

                    $newValues =
                        $this->auditValues($siteIssue);

                    $action = 'Updated';
                    $description =
                        'Site issue updated';

                    if ($oldStatus !== $siteIssue->status) {
                        $action =
                            $siteIssue->status;

                        $description =
                            'Site issue status changed from '
                            . $oldStatus
                            . ' to '
                            . $siteIssue->status;
                    }

                    AuditHelper::log(
                        'Site Issues',
                        $action,
                        'SiteIssue',
                        $siteIssue->id,
                        $description,
                        $oldValues,
                        $newValues
                    );
                }
            );

            $this->deleteStoredPaths(
                $pathsToDeleteAfterCommit
            );

            return redirect()
                ->route('site-issues.show', $siteIssue)
                ->with(
                    'success',
                    'Site issue updated successfully.'
                );
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update Site Issue.'
                );
        }
    }

    public function destroy(
        SiteIssue $siteIssue
    ): RedirectResponse {
        $siteIssue->load(
            $this->issueRelationships()
        );

        $this->ensureIssueAccess($siteIssue);

        abort_if(
            $siteIssue->dpr_id !== null,
            403,
            'This Site Issue is already linked to a DPR and cannot be deleted.'
        );

        $oldValues =
            $this->auditValues($siteIssue);

        $photoPaths =
            $siteIssue->photos
                ->pluck('file_path')
                ->filter()
                ->values()
                ->all();

        $issueId = $siteIssue->id;

        DB::transaction(
            function () use (
                $siteIssue,
                $issueId,
                $oldValues
            ): void {
                $siteIssue->delete();

                AuditHelper::log(
                    'Site Issues',
                    'Deleted',
                    'SiteIssue',
                    $issueId,
                    'Site issue deleted',
                    $oldValues,
                    null
                );
            }
        );

        $this->deleteStoredPaths(
            $photoPaths
        );

        return redirect()
            ->route('site-issues.index')
            ->with(
                'success',
                'Site issue deleted successfully.'
            );
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

            'activity_id' => [
                'nullable',
                'integer',
                'exists:activities,id',
            ],

            'issue_date' => [
                'required',
                'date',
            ],

            'issue_type' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::ISSUE_TYPES
                ),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'related_activity' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:10000',
            ],

            'root_cause' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'responsible_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'target_closure_date' => [
                'nullable',
                'date',
                'after_or_equal:issue_date',
            ],

            'actual_closure_date' => [
                $updating ? 'nullable' : 'prohibited',
                'date',
                'after_or_equal:issue_date',
            ],

            'priority' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::PRIORITIES
                ),
            ],

            'status' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::STATUSES
                ),
            ],

            'escalated_to_pmo' => [
                'nullable',
                'boolean',
            ],

            'escalated_to_management' => [
                'nullable',
                'boolean',
            ],

            'resolution' => [
                $updating ? 'nullable' : 'prohibited',
                'string',
                'max:10000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'photos' => [
                'nullable',
                'array',
                'max:20',
            ],

            'photos.*.file' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'photos.*.photo_type' => [
                'nullable',
                'string',
                'in:' . implode(
                    ',',
                    self::PHOTO_TYPES
                ),
            ],

            'photos.*.caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'remove_photo_ids' => [
                'nullable',
                'array',
            ],

            'remove_photo_ids.*' => [
                'integer',
                'exists:site_issue_photos,id',
            ],
        ], [
            'target_closure_date.after_or_equal' =>
                'Target Closure Date cannot be before Issue Date.',

            'actual_closure_date.after_or_equal' =>
                'Actual Closure Date cannot be before Issue Date.',

            'photos.*.file.max' =>
                'Each Site Issue photo may be up to 10 MB.',
        ]);
    }

    private function validateLocationHierarchy(
        int $projectId,
        array $data
    ): void {
        if (
            ! empty($data['project_block_id'])
            && ! ProjectBlock::query()
                ->whereKey(
                    $data['project_block_id']
                )
                ->where(
                    'project_id',
                    $projectId
                )
                ->exists()
        ) {
            abort(
                422,
                'The selected Block does not belong to the selected Project.'
            );
        }

        if (! empty($data['project_floor_id'])) {
            $query = ProjectFloor::query()
                ->whereKey(
                    $data['project_floor_id']
                )
                ->where(
                    'project_id',
                    $projectId
                );

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
                ->whereKey(
                    $data['project_unit_id']
                )
                ->where(
                    'project_id',
                    $projectId
                );

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
                ->whereKey(
                    $data['project_room_id']
                );

            if (! empty($data['project_unit_id'])) {
                $query->where(
                    'project_unit_id',
                    $data['project_unit_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Room does not belong to the selected Unit.'
            );
        }

        if (! empty($data['project_subspace_id'])) {
            $query = ProjectSubspace::query()
                ->whereKey(
                    $data['project_subspace_id']
                );

            if (! empty($data['project_room_id'])) {
                $query->where(
                    'project_room_id',
                    $data['project_room_id']
                );
            }

            abort_unless(
                $query->exists(),
                422,
                'The selected Sub-space does not belong to the selected Room.'
            );
        }
    }

    private function storePhotos(
        Request $request,
        SiteIssue $siteIssue,
        array &$storedPaths
    ): void {
        $rows =
            $request->input(
                'photos',
                []
            );

        $files =
            $request->file(
                'photos',
                []
            );

        if (! is_array($files)) {
            return;
        }

        $siteIssue->loadMissing([
            'project',
            'creator',
        ]);

        $projectName =
            $siteIssue->project?->project_name
            ?? 'Project';

        $issueTitle =
            $siteIssue->title
            ?: 'Site-Issue';

        $engineerName =
            $siteIssue->creator?->name
            ?? auth()->user()?->name
            ?? 'User';

        $datePart =
            $siteIssue->issue_date
                ?->format('Ymd')
            ?? now()->format('Ymd');

        $timePart =
            now()->format('His');

        $sequence =
            $siteIssue->photos()->count() + 1;

        foreach ($files as $index => $fileData) {
            $file = is_array($fileData)
                ? ($fileData['file'] ?? null)
                : null;

            if (! $file) {
                continue;
            }

            $metadata =
                $rows[$index]
                ?? [];

            $photoType =
                $this->normalizePhotoType(
                    $metadata['photo_type']
                    ?? 'Issue'
                );

            $caption =
                $this->nullableTrim(
                    $metadata['caption']
                    ?? null
                );

            $extension = strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'jpg'
            );

            $filename = implode('-', [
                $this->filenamePart(
                    $projectName,
                    50
                ),

                $this->filenamePart(
                    $issueTitle,
                    60
                ),

                $this->filenamePart(
                    $photoType,
                    35
                ),

                $this->filenamePart(
                    $engineerName,
                    40
                ),

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
                'site-issues',
                'project-' . $siteIssue->project_id,
                'issue-' . $siteIssue->id,
            ]);

            $path =
                $file->storeAs(
                    $directory,
                    $filename,
                    'public'
                );

            $storedPaths[] =
                $path;

            SiteIssuePhoto::create([
                'site_issue_id' =>
                    $siteIssue->id,

                'uploaded_by' =>
                    auth()->id(),

                'photo_type' =>
                    $photoType,

                'file_path' =>
                    $path,

                'original_name' =>
                    $file->getClientOriginalName(),

                'mime_type' =>
                    $file->getMimeType(),

                'file_size' =>
                    $file->getSize(),

                'caption' =>
                    $caption,

                'sort_order' =>
                    $sequence,
            ]);

            $sequence++;
        }
    }

    private function formData(): array
    {
        return [
            'projects' =>
                $this->availableProjects(),

            'projectBlocks' =>
                ProjectBlock::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectFloors' =>
                ProjectFloor::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectUnits' =>
                ProjectUnit::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectRooms' =>
                ProjectRoom::query()
                    ->orderBy('name')
                    ->get(),

            'projectSubspaces' =>
                ProjectSubspace::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'activityDivisions' =>
                ActivityDivision::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'activities' =>
                Activity::query()
                    ->where('is_active', true)
                    ->orderBy('activity_name')
                    ->get(),

            'issueTypes' =>
                self::ISSUE_TYPES,

            'priorities' =>
                self::PRIORITIES,

            'statuses' =>
                self::STATUSES,

            'photoTypes' =>
                self::PHOTO_TYPES,
        ];
    }

    private function availableProjects()
    {
        if ($this->isEngineer()) {
            return auth()->user()
                ->projects()
                ->whereNotIn(
                    'projects.status',
                    [
                        'Completed',
                        'Handed Over',
                        'Closed',
                    ]
                )
                ->orderBy('project_name')
                ->get();
        }

        return Project::query()
            ->whereNotIn(
                'status',
                [
                    'Completed',
                    'Handed Over',
                    'Closed',
                ]
            )
            ->orderBy('project_name')
            ->get();
    }

    private function ensureIssueAccess(
        SiteIssue $siteIssue
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            (int) $siteIssue->created_by
                === (int) auth()->id(),
            403,
            'Unauthorized Site Issue access.'
        );
    }

    private function ensureProjectAccess(
        int $projectId
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        $hasAccess = auth()->user()
            ->projects()
            ->where(
                'projects.id',
                $projectId
            )
            ->exists();

        abort_unless(
            $hasAccess,
            403,
            'You are not assigned to this Project.'
        );
    }

    private function issueRelationships(): array
    {
        return [
            'dpr',
            'project',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'activity',
            'creator',
            'photos.uploader',
        ];
    }

    private function auditValues(
        SiteIssue $siteIssue
    ): array {
        $siteIssue->loadMissing(
            $this->issueRelationships()
        );

        return [
            'id' =>
                $siteIssue->id,

            'dpr_id' =>
                $siteIssue->dpr_id,

            'project_id' =>
                $siteIssue->project_id,

            'project' =>
                $siteIssue->project?->project_name,

            'issue_date' =>
                $siteIssue->issue_date
                    ?->format('Y-m-d'),

            'issue_type' =>
                $siteIssue->issue_type,

            'title' =>
                $siteIssue->title,

            'activity_id' =>
                $siteIssue->activity_id,

            'activity' =>
                $siteIssue->activity?->activity_name,

            'location' =>
                $siteIssue->location_path,

            'description' =>
                $siteIssue->description,

            'root_cause' =>
                $siteIssue->root_cause,

            'responsible_person' =>
                $siteIssue->responsible_person,

            'target_closure_date' =>
                $siteIssue->target_closure_date
                    ?->format('Y-m-d'),

            'actual_closure_date' =>
                $siteIssue->actual_closure_date
                    ?->format('Y-m-d'),

            'priority' =>
                $siteIssue->priority,

            'status' =>
                $siteIssue->status,

            'escalated_to_pmo' =>
                $siteIssue->escalated_to_pmo,

            'escalated_to_management' =>
                $siteIssue->escalated_to_management,

            'resolution' =>
                $siteIssue->resolution,

            'created_by' =>
                $siteIssue->created_by,

            'remarks' =>
                $siteIssue->remarks,

            'photo_ids' =>
                $siteIssue->photos
                    ->pluck('id')
                    ->values()
                    ->all(),
        ];
    }

    private function normalizePhotoType(
        mixed $photoType
    ): string {
        $photoType =
            trim((string) $photoType);

        return in_array(
            $photoType,
            self::PHOTO_TYPES,
            true
        )
            ? $photoType
            : 'Other';
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

    private function deleteStoredPaths(
        array $paths
    ): void {
        $paths = collect($paths)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')
                ->delete($paths);
        }
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value =
            trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    private function isEngineer(): bool
    {
        return auth()->user()?->role?->name
            === 'Engineer';
    }
}
