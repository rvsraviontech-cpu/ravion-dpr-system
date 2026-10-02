<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\ProjectRoom;
use App\Models\ProjectRoomGeometry;
use App\Models\ProjectRoomWall;
use App\Models\SpatialComponentType;
use App\Models\SpatialOpeningType;
use App\Models\SpatialMeasurementZone;
use App\Models\ProjectWallOpening;
use App\Models\ProjectMeasurementZone;
use App\Models\ProjectSubspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectRoomDesignerController extends Controller
{
    private function authorizeRoom(Request $request, ProjectRoom $room): void
    {
        $user = $request->user();
        abort_unless($user, 403);
        $role = optional($user->role)->name;
        if (in_array($role, ['Admin', 'CEO'], true) || $user->hasAllProjectAccess()) {
            return;
        }
        abort_unless($user->hasProjectAccess((int) $room->project_id), 403);
    }

    public function show(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $projectRoom->load(['unit', 'floor.block', 'geometry', 'walls.openings', 'measurementZones']);
        $componentTypes = SpatialComponentType::query()->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $openingTypes = SpatialOpeningType::where('is_active', true)->orderBy('name')->get(['id','name']);
        $zoneTypes = SpatialMeasurementZone::where('is_active', true)->orderBy('name')->get(['id','name']);
        $connectionRooms = ProjectRoom::where('project_id', $projectRoom->project_id)->where('project_floor_id', $projectRoom->project_floor_id)->where('id', '!=', $projectRoom->id)->orderBy('name')->get(['id','name','project_unit_id']);
        $connectionSubspaces = ProjectSubspace::where('project_id', $projectRoom->project_id)->where('project_floor_id', $projectRoom->project_floor_id)->orderBy('name')->get(['id','name','project_room_id']);
        return view('project-locations.room-designer', compact('projectRoom', 'componentTypes', 'openingTypes', 'zoneTypes', 'connectionRooms', 'connectionSubspaces'));
    }

    public function saveGeometry(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $request->validate([
            'shape_type' => 'required|in:rectangle,irregular',
            'dimension_unit' => 'required|in:m,ft',
            'length' => 'nullable|numeric|gt:0|max:100000',
            'width' => 'nullable|numeric|gt:0|max:100000',
            'clear_height' => 'nullable|numeric|gt:0|max:100000',
            'use_manual_floor_area' => 'nullable|boolean',
            'manual_floor_area' => 'nullable|numeric|gte:0|max:100000000',
            'geometry_notes' => 'nullable|string|max:5000',
        ]);
        $manual = $request->boolean('use_manual_floor_area');
        if ($manual && !isset($data['manual_floor_area'])) {
            throw ValidationException::withMessages(['manual_floor_area' => 'Enter a manual floor area when manual mode is enabled.']);
        }
        $length = isset($data['length']) ? (float) $data['length'] : null;
        $width = isset($data['width']) ? (float) $data['width'] : null;
        $height = isset($data['clear_height']) ? (float) $data['clear_height'] : null;
        $rectangular = $data['shape_type'] === 'rectangle';
        $computedArea = $rectangular && $length !== null && $width !== null ? round($length * $width, 3) : null;
        $floorArea = $manual ? (float) $data['manual_floor_area'] : $computedArea;
        $perimeter = $rectangular && $length !== null && $width !== null ? round(2 * ($length + $width), 3) : null;
        $volume = $floorArea !== null && $height !== null ? round($floorArea * $height, 3) : null;
        $areaUnit = $data['dimension_unit'] === 'ft' ? 'sqft' : 'sqm';
        DB::transaction(function () use ($projectRoom, $data, $manual, $computedArea, $floorArea, $perimeter, $volume, $areaUnit) {
            $geometry = ProjectRoomGeometry::firstOrNew(['project_room_id' => $projectRoom->id]);
            $before = $geometry->exists ? $geometry->toArray() : null;
            $geometry->fill([
                'shape_type' => $data['shape_type'],
                'dimension_unit' => $data['dimension_unit'],
                'area_unit' => $areaUnit,
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'clear_height' => $data['clear_height'] ?? null,
                'calculated_floor_area' => $computedArea,
                'manual_floor_area' => $manual ? $data['manual_floor_area'] : null,
                'use_manual_floor_area' => $manual,
                'ceiling_area' => $floorArea,
                'perimeter' => $perimeter,
                'volume' => $volume,
                'geometry_notes' => $data['geometry_notes'] ?? null,
            ]);
            $geometry->save();
            AuditHelper::log('Project Locations', 'Room Geometry Saved', 'ProjectRoomGeometry', $geometry->id,
                'Geometry saved for room '.$projectRoom->name, $before, $geometry->fresh()->toArray());
        });
        return redirect()->route('project-locations.rooms.designer', $projectRoom)->with('success', 'Room measurements saved.');
    }

    public function saveWall(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $request->validate([
            'wall_id' => 'nullable|integer',
            'spatial_component_type_id' => 'required|exists:spatial_component_types,id',
            'wall_code' => 'required|string|max:80',
            'name' => 'nullable|string|max:255',
            'orientation' => 'nullable|string|max:80',
            'wall_function' => 'nullable|string|max:100',
            'dimension_unit' => 'required|in:m,ft',
            'length' => 'nullable|numeric|gt:0|max:100000',
            'height' => 'nullable|numeric|gt:0|max:100000',
            'is_external' => 'nullable|boolean',
            'is_shared' => 'nullable|boolean',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:5000',
        ]);
        $wall = isset($data['wall_id'])
            ? ProjectRoomWall::where('project_room_id', $projectRoom->id)->findOrFail($data['wall_id'])
            : new ProjectRoomWall(['project_room_id' => $projectRoom->id]);
        $type = SpatialComponentType::query()->whereKey($data['spatial_component_type_id'])
            ->where(function ($q) use ($wall) {
                $q->where('is_active', true);
                if ($wall->exists && $wall->spatial_component_type_id) {
                    $q->orWhere('id', $wall->spatial_component_type_id);
                }
            })->firstOrFail();
        $duplicate = ProjectRoomWall::query()->where('project_room_id', $projectRoom->id)
            ->where('wall_code', $data['wall_code']);
        if ($wall->exists) {
            $duplicate->where('id', '!=', $wall->id);
        }
        if ($duplicate->exists()) {
            throw ValidationException::withMessages(['wall_code' => 'This wall code already exists in the room.']);
        }
        $length = isset($data['length']) ? (float) $data['length'] : null;
        $height = isset($data['height']) ? (float) $data['height'] : null;
        $gross = $length !== null && $height !== null ? round($length * $height, 3) : null;
        DB::transaction(function () use ($projectRoom, $wall, $data, $type, $gross) {
            $before = $wall->exists ? $wall->toArray() : null;
            // Opening dimensions must be in the same unit as the wall. Until the openings
            // editor is installed, existing deductions are preserved and not recalculated.
            if ($wall->exists && $wall->openings()->exists() && $wall->dimension_unit !== $data['dimension_unit']) {
                throw ValidationException::withMessages(['dimension_unit' => 'A wall with openings cannot change its dimension unit. Edit the openings first.']);
            }
            $deductions = $wall->exists ? $wall->openings()->where('is_active', true)->where('deduct_from_wall_area', true)->sum('calculated_area') : 0;
            $wall->fill([
                'spatial_component_type_id' => $type->id,
                'wall_code' => $data['wall_code'],
                'name' => $data['name'] ?? null,
                'orientation' => $data['orientation'] ?? null,
                'wall_function' => $data['wall_function'] ?? null,
                'dimension_unit' => $data['dimension_unit'],
                'area_unit' => $data['dimension_unit'] === 'ft' ? 'sqft' : 'sqm',
                'length' => $data['length'] ?? null,
                'height' => $data['height'] ?? null,
                'gross_area' => $gross,
                'openings_area' => $deductions,
                'net_area' => $gross !== null ? round(max(0, $gross - (float) $deductions), 3) : null,
                'is_external' => !empty($data['is_external']),
                'is_shared' => !empty($data['is_shared']),
                'is_active' => (bool) $data['is_active'],
                'remarks' => $data['remarks'] ?? null,
            ]);
            $wall->save();
            AuditHelper::log('Project Locations', 'Room Wall Saved', 'ProjectRoomWall', $wall->id,
                'Wall saved for room '.$projectRoom->name, $before, $wall->fresh()->toArray());
        });
        return redirect()->route('project-locations.rooms.designer', $projectRoom)->with('success', 'Wall saved.');
    }

    public function saveOpening(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $request->validate([
            'opening_id' => 'nullable|integer', 'project_room_wall_id' => 'required|integer',
            'spatial_opening_type_id' => 'required|exists:spatial_opening_types,id',
            'opening_code' => 'required|string|max:80', 'name' => 'nullable|string|max:255',
            'width' => 'required|numeric|gt:0|max:100000', 'height' => 'required|numeric|gt:0|max:100000',
            'quantity' => 'required|integer|min:1|max:10000',
            'sill_height' => 'nullable|numeric|gte:0|max:100000', 'lintel_height' => 'nullable|numeric|gte:0|max:100000',
            'connects_to_project_room_id' => 'nullable|integer', 'connects_to_project_subspace_id' => 'nullable|integer',
            'deduct_from_wall_area' => 'required|boolean', 'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:5000',
        ]);
        DB::transaction(function () use ($data, $projectRoom) {
            // Find the opening within this ROOM, not under the destination wall.
            // Otherwise moving an opening from W2 to W3 incorrectly returns 404.
            $opening = !empty($data['opening_id'])
                ? ProjectWallOpening::query()
                    ->whereHas('wall', fn ($q) => $q->where('project_room_id', $projectRoom->id))
                    ->lockForUpdate()
                    ->findOrFail($data['opening_id'])
                : null;

            $oldWallId = $opening?->project_room_wall_id;
            $newWallId = (int) $data['project_room_wall_id'];

            // Lock affected walls in a consistent order and verify room ownership.
            $walls = ProjectRoomWall::query()
                ->where('project_room_id', $projectRoom->id)
                ->whereIn('id', array_values(array_unique(array_filter([$oldWallId, $newWallId]))))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $wall = $walls->get($newWallId);
            abort_unless($wall, 404);
            if ($oldWallId !== null) abort_unless($walls->has((int) $oldWallId), 404);

            if ($opening && (int) $oldWallId !== $newWallId && $walls->get((int) $oldWallId)->dimension_unit !== $wall->dimension_unit) {
                throw ValidationException::withMessages([
                    'project_room_wall_id' => 'The destination wall uses a different measurement unit. Convert the opening dimensions before moving it.',
                ]);
            }

            $opening ??= new ProjectWallOpening(['project_room_wall_id' => $wall->id]);
            $type = SpatialOpeningType::whereKey($data['spatial_opening_type_id'])->where(function ($q) use ($opening) {
                $q->where('is_active', true);
                if ($opening->exists) $q->orWhere('id', $opening->spatial_opening_type_id);
            })->firstOrFail();
            $other = $wall->openings()->where('opening_code', $data['opening_code']);
            if ($opening->exists) $other->where('id', '!=', $opening->id);
            if ($other->exists()) throw ValidationException::withMessages(['opening_code' => 'Opening code already exists on this wall.']);
            $roomId = $data['connects_to_project_room_id'] ?? null;
            $subspaceId = $data['connects_to_project_subspace_id'] ?? null;
            if ($roomId && $subspaceId) throw ValidationException::withMessages(['connects_to_project_room_id' => 'Select a room OR an element, not both.']);
            if ($roomId) ProjectRoom::whereKey($roomId)->where('project_id', $projectRoom->project_id)->where('project_floor_id', $projectRoom->project_floor_id)->where('id', '!=', $projectRoom->id)->firstOrFail();
            if ($subspaceId) ProjectSubspace::whereKey($subspaceId)->where('project_id', $projectRoom->project_id)->where('project_floor_id', $projectRoom->project_floor_id)->firstOrFail();
            $before = $opening->exists ? $opening->toArray() : null;
            $opening->fill([
                'spatial_opening_type_id' => $type->id, 'opening_code' => $data['opening_code'],
                'name' => $data['name'] ?? null, 'dimension_unit' => $wall->dimension_unit,
                'area_unit' => $wall->area_unit, 'width' => $data['width'], 'height' => $data['height'],
                'quantity' => $data['quantity'], 'sill_height' => $data['sill_height'] ?? null,
                'lintel_height' => $data['lintel_height'] ?? null,
                'calculated_area' => round((float)$data['width'] * (float)$data['height'] * (int)$data['quantity'], 3),
                'connects_to_project_room_id' => $roomId, 'connects_to_project_subspace_id' => $subspaceId,
                'deduct_from_wall_area' => (bool)$data['deduct_from_wall_area'], 'is_active' => (bool)$data['is_active'],
                'remarks' => $data['remarks'] ?? null,
            ]);
            $opening->project_room_wall_id = $wall->id;
            $opening->save();

            // Recalculate BOTH walls when an opening moves; inactive openings do not deduct.
            foreach ($walls as $affectedWall) {
                $deductions = (float) $affectedWall->openings()
                    ->where('is_active', true)->where('deduct_from_wall_area', true)
                    ->sum('calculated_area');
                $affectedWall->update([
                    'openings_area' => round($deductions, 3),
                    'net_area' => $affectedWall->gross_area === null
                        ? null : round(max(0, (float) $affectedWall->gross_area - $deductions), 3),
                ]);
            }
            AuditHelper::log('Project Locations', 'Wall Opening Saved', 'ProjectWallOpening', $opening->id,
                'Opening saved for room '.$projectRoom->name, $before, $opening->fresh()->toArray());
        });
        return redirect()->route('project-locations.rooms.designer', $projectRoom)->with('success', 'Opening saved and wall area recalculated.');
    }

    public function saveZone(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $request->validate([
            'zone_id' => 'nullable|integer', 'spatial_measurement_zone_id' => 'required|exists:spatial_measurement_zones,id',
            'project_room_wall_id' => 'nullable|integer', 'name' => 'nullable|string|max:255',
            'measurement_basis' => 'required|in:area,length,volume,count', 'unit' => 'required|in:sqft,sqm,ft,m,cuft,cum,nos',
            'length' => 'nullable|numeric|gte:0|max:100000', 'width' => 'nullable|numeric|gte:0|max:100000',
            'height' => 'nullable|numeric|gte:0|max:100000', 'quantity' => 'nullable|numeric|gte:0|max:100000',
            'use_manual_value' => 'required|boolean', 'manual_value' => 'nullable|numeric|gte:0|max:100000000',
            'is_active' => 'required|boolean', 'remarks' => 'nullable|string|max:5000',
        ]);
        $units = ['area' => ['sqft','sqm'], 'length' => ['ft','m'], 'volume' => ['cuft','cum'], 'count' => ['nos']];
        if (!in_array($data['unit'], $units[$data['measurement_basis']], true)) throw ValidationException::withMessages(['unit' => 'Unit does not match measurement basis.']);
        if ((bool)$data['use_manual_value'] && !isset($data['manual_value'])) throw ValidationException::withMessages(['manual_value' => 'Enter a manual value.']);
        DB::transaction(function () use ($data, $projectRoom) {
            $zone = !empty($data['zone_id']) ? ProjectMeasurementZone::where('project_room_id', $projectRoom->id)->findOrFail($data['zone_id']) : new ProjectMeasurementZone(['project_room_id' => $projectRoom->id]);
            SpatialMeasurementZone::whereKey($data['spatial_measurement_zone_id'])->where(function ($q) use ($zone) {
                $q->where('is_active', true);
                if ($zone->exists) $q->orWhere('id', $zone->spatial_measurement_zone_id);
            })->firstOrFail();
            if (!empty($data['project_room_wall_id'])) {
                $wall = ProjectRoomWall::where('project_room_id', $projectRoom->id)->findOrFail($data['project_room_wall_id']);
                $wallAreaUnit = $wall->dimension_unit === 'ft' ? 'sqft' : 'sqm';
                if ($data['measurement_basis'] === 'area' && $data['unit'] !== $wallAreaUnit) throw ValidationException::withMessages(['unit' => 'Wall area zones must use the same area unit as the wall.']);
            }
            $l = isset($data['length']) ? (float)$data['length'] : null;
            $w = isset($data['width']) ? (float)$data['width'] : null;
            $h = isset($data['height']) ? (float)$data['height'] : null;
            $q = isset($data['quantity']) ? (float)$data['quantity'] : 1.0;
            $value = match ($data['measurement_basis']) {
                'area' => $l !== null && $w !== null ? $l * $w * $q : null,
                'length' => $l !== null ? $l * $q : null,
                'volume' => $l !== null && $w !== null && $h !== null ? $l * $w * $h * $q : null,
                'count' => $q,
            };
            $before = $zone->exists ? $zone->toArray() : null;
            $zone->fill([
                'project_room_wall_id' => $data['project_room_wall_id'] ?? null,
                'spatial_measurement_zone_id' => $data['spatial_measurement_zone_id'],
                'name' => $data['name'] ?? null, 'measurement_basis' => $data['measurement_basis'], 'unit' => $data['unit'],
                'length' => $data['length'] ?? null, 'width' => $data['width'] ?? null, 'height' => $data['height'] ?? null,
                'quantity' => $data['quantity'] ?? null, 'calculated_value' => $value === null ? null : round($value, 3),
                'manual_value' => (bool)$data['use_manual_value'] ? $data['manual_value'] : null,
                'use_manual_value' => (bool)$data['use_manual_value'], 'is_active' => (bool)$data['is_active'],
                'remarks' => $data['remarks'] ?? null,
            ]);
            $zone->save();
            AuditHelper::log('Project Locations', 'Measurement Zone Saved', 'ProjectMeasurementZone', $zone->id,
                'Measurement zone saved for room '.$projectRoom->name, $before, $zone->fresh()->toArray());
        });
        return redirect()->route('project-locations.rooms.designer', $projectRoom)->with('success', 'Measurement zone saved.');
    }
}
