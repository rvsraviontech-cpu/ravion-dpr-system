<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\ProjectRoom;
use App\Models\ProjectRoomGeometry;
use App\Models\ProjectRoomWall;
use App\Models\ProjectWallOpening;
use App\Models\SpatialComponentType;
use App\Models\SpatialOpeningType;
use App\Models\SpatialSpaceType;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Additive setup for an existing room. Never overwrites edited records. */
class IntelligentRoomSetupController extends Controller
{
    private function authorizeRoom(Request $request, ProjectRoom $room): void
    {
        $user = $request->user();
        abort_unless($user, 403);
        $role = optional($user->role)->name;
        abort_unless(in_array($role, ['Admin', 'CEO'], true) || $user->hasAllProjectAccess() || $user->hasProjectAccess((int) $room->project_id), 403);
    }

    private function defaults(ProjectRoom $room): array
    {
        $type = strtolower($room->name.' '.$room->room_type);
        $isBedroom = str_contains($type, 'bedroom');
        $isBathroom = str_contains($type, 'bath') || str_contains($type, 'washroom') || str_contains($type, 'toilet');
        $isKitchen = str_contains($type, 'kitchen');
        return ['doors' => 1, 'windows' => $isBathroom ? 0 : ($isKitchen ? 1 : ($isBedroom ? 2 : 1)), 'ventilators' => $isBathroom ? 1 : 0];
    }

    public function show(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $projectRoom->load(['geometry', 'walls.openings', 'floor.block', 'unit']);
        $defaults = $this->defaults($projectRoom);
        $spaceTypes = SpatialSpaceType::where('is_active', true)->orderBy('name')->get(['id','name']);
        return view('project-locations.intelligent-room-setup', compact('projectRoom', 'defaults', 'spaceTypes'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'dimension_unit' => 'required|in:ft,m',
            'length' => 'required|numeric|gt:0|max:100000',
            'width' => 'required|numeric|gt:0|max:100000',
            'clear_height' => 'required|numeric|gt:0|max:100000',
            'door_count' => 'required|integer|min:0|max:20',
            'window_count' => 'required|integer|min:0|max:30',
            'ventilator_count' => 'required|integer|min:0|max:20',
            'door_width' => 'required|numeric|gt:0|max:10000',
            'door_height' => 'required|numeric|gt:0|max:10000',
            'window_width' => 'required|numeric|gt:0|max:10000',
            'window_height' => 'required|numeric|gt:0|max:10000',
            'ventilator_width' => 'required|numeric|gt:0|max:10000',
            'ventilator_height' => 'required|numeric|gt:0|max:10000',
            'attached' => 'nullable|array|max:3',
            'attached.*.enabled' => 'nullable|boolean',
            'attached.*.name' => 'nullable|string|max:255',
            'attached.*.spatial_space_type_id' => 'nullable|integer|exists:spatial_space_types,id',
            'attached.*.length' => 'nullable|numeric|gt:0|max:100000',
            'attached.*.width' => 'nullable|numeric|gt:0|max:100000',
            'attached.*.clear_height' => 'nullable|numeric|gt:0|max:100000',
        ]);
    }

    private function plan(ProjectRoom $room, array $data): array
    {
        $unit = $data['dimension_unit'];
        $geometry = $room->geometry;
        if ($geometry && ($geometry->dimension_unit !== $unit || $geometry->shape_type !== 'rectangle')) {
            throw ValidationException::withMessages(['dimension_unit' => 'Existing geometry has a different unit or irregular shape. Use Advanced Room Designer.']);
        }
        $length = (float) $data['length'];
        $width = (float) $data['width'];
        $height = (float) $data['clear_height'];
        $wallSpecs = [
            ['W1', 'North', $length], ['W2', 'South', $length],
            ['W3', 'East', $width], ['W4', 'West', $width],
        ];
        $existing = $room->walls->keyBy('wall_code');
        $walls = [];
        foreach ($wallSpecs as [$code, $orientation, $wallLength]) {
            $wall = $existing->get($code);
            if ($wall && $wall->dimension_unit !== $unit) {
                throw ValidationException::withMessages(['dimension_unit' => "Existing {$code} uses a different unit."]);
            }
            $walls[] = ['code' => $code, 'orientation' => $orientation, 'length' => $wallLength, 'height' => $height,
                'action' => $wall ? 'Keep existing (no changes)' : 'Create'];
        }
        $openings = [];
        foreach (['Door' => 'door', 'Window' => 'window', 'Ventilator' => 'ventilator'] as $name => $prefix) {
            for ($i = 1; $i <= (int) $data[$prefix.'_count']; $i++) {
                $code = strtoupper(substr($prefix, 0, 1)).$i;
                $already = $room->walls->contains(fn ($wall) => $wall->openings->contains('opening_code', $code));
                $openings[] = ['code' => $code, 'name' => $name.' '.$i, 'width' => (float) $data[$prefix.'_width'],
                    'height' => (float) $data[$prefix.'_height'], 'action' => $already ? 'Keep existing' : 'Propose (wall assignment required)'];
            }
        }
        $attached = $this->attachedSpecs($room, $data);
        return ['walls' => $walls, 'openings' => $openings, 'attached' => $attached, 'geometry_action' => $geometry ? 'Keep existing' : 'Create',
            'floor_area' => round($length * $width, 3), 'perimeter' => round(2 * ($length + $width), 3)];
    }

    public function preview(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $this->validated($request);
        $projectRoom->load(['geometry', 'walls.openings']);
        $plan = $this->plan($projectRoom, $data);
        $openingTypes = SpatialOpeningType::where('is_active', true)->orderBy('name')->get(['id','name']);
        return view('project-locations.intelligent-room-preview', compact('projectRoom', 'data', 'plan', 'openingTypes'));
    }

    public function generate(Request $request, ProjectRoom $projectRoom)
    {
        $this->authorizeRoom($request, $projectRoom);
        $data = $this->validated($request);
        $assignments = $request->validate([
            'openings' => 'nullable|array|max:70',
            'openings.*.wall' => 'required|in:W1,W2,W3,W4',
            'openings.*.type_id' => 'required|integer|exists:spatial_opening_types,id',
            'openings.*.connects_to' => 'nullable|string|max:255',
        ])['openings'] ?? [];
        $result = DB::transaction(function () use ($projectRoom, $data, $assignments) {
            $room = ProjectRoom::whereKey($projectRoom->id)->lockForUpdate()->firstOrFail();
            $room->load(['geometry', 'walls.openings']);
            $plan = $this->plan($room, $data);
            $component = SpatialComponentType::where('is_active', true)
                ->where(fn ($q) => $q->where('name', 'like', '%wall%')->orWhere('name', 'like', '%partition%'))
                ->orderBy('id')->first();
            if (!$component) throw ValidationException::withMessages(['walls' => 'Create/activate a Wall component type in Spatial Masters before generating walls.']);
            $unit = $data['dimension_unit'];
            $areaUnit = $unit === 'ft' ? 'sqft' : 'sqm';
            $created = ['geometry' => 0, 'walls' => 0, 'openings' => 0, 'attached' => 0];
            if (!$room->geometry) {
                $geometry = ProjectRoomGeometry::create([
                    'project_room_id' => $room->id, 'spatial_space_type_id' => $room->spatial_space_type_id,
                    'spatial_space_subtype_id' => $room->spatial_space_subtype_id,
                    'shape_type' => 'rectangle', 'dimension_unit' => $unit, 'area_unit' => $areaUnit,
                    'length' => $data['length'], 'width' => $data['width'], 'clear_height' => $data['clear_height'],
                    'calculated_floor_area' => $plan['floor_area'], 'ceiling_area' => $plan['floor_area'],
                    'perimeter' => $plan['perimeter'], 'volume' => round($plan['floor_area'] * (float) $data['clear_height'], 3),
                    'use_manual_floor_area' => false, 'geometry_notes' => 'Created using Intelligent Room Setup',
                ]);
                AuditHelper::log('Project Locations', 'Room Geometry Auto Created', 'ProjectRoomGeometry', $geometry->id, 'Auto geometry: '.$room->name, null, $geometry->toArray());
                $created['geometry']++;
            }
            foreach ($plan['walls'] as $spec) {
                if ($spec['action'] !== 'Create') continue;
                // Insert only absent codes; never change existing wall measurements or openings.
                $gross = round($spec['length'] * $spec['height'], 3);
                $wall = ProjectRoomWall::create([
                    'project_room_id' => $room->id, 'spatial_component_type_id' => $component->id,
                    'wall_code' => $spec['code'], 'name' => $spec['orientation'].' Wall', 'orientation' => $spec['orientation'],
                    'dimension_unit' => $unit, 'area_unit' => $areaUnit, 'length' => $spec['length'],
                    'height' => $spec['height'], 'gross_area' => $gross, 'openings_area' => 0,
                    'net_area' => $gross, 'is_external' => false, 'is_shared' => false,
                    'is_active' => true, 'sort_order' => (int) substr($spec['code'], 1),
                    'remarks' => 'Created using Intelligent Room Setup',
                ]);
                AuditHelper::log('Project Locations', 'Room Wall Auto Created', 'ProjectRoomWall', $wall->id, 'Auto wall: '.$room->name.' '.$spec['code'], null, $wall->toArray());
                $created['walls']++;
            }
            // Add attached rooms before openings, so connections use real room IDs.
            $attachedRoomIds = [];
            foreach ($plan['attached'] as $spec) {
                if ($spec['existing_id']) {
                    $attachedRoomIds[$spec['key']] = $spec['existing_id'];
                    continue;
                }
                $newRoom = ProjectRoom::create([
                    'project_id' => $room->project_id, 'project_block_id' => $room->project_block_id,
                    'project_floor_id' => $room->project_floor_id, 'project_unit_id' => $room->project_unit_id,
                    'name' => $spec['name'], 'room_type' => $spec['type_name'],
                    'spatial_space_type_id' => $spec['type_id'], 'is_active' => true,
                    'remarks' => 'Intelligent Room Setup; attached to room ID '.$room->id,
                ]);
                $attachedRoomIds[$spec['key']] = $newRoom->id;
                AuditHelper::log('Project Locations', 'Attached Room Auto Created', 'ProjectRoom', $newRoom->id, 'Attached to '.$room->name, null, $newRoom->toArray());
                $this->createGeometryAndWalls($newRoom, $spec, $unit, $areaUnit, $component);
                $created['attached']++;
            }
            // Only create openings explicitly assigned to a wall in the confirmed preview.
            $room->load('walls.openings');
            $byCode = $room->walls->keyBy('wall_code');
            foreach ($plan['openings'] as $openingSpec) {
                if ($openingSpec['action'] === 'Keep existing') continue;
                $assignment = $assignments[$openingSpec['code']] ?? null;
                if (!$assignment) throw ValidationException::withMessages(['openings' => 'Assign every proposed opening to a wall before confirming.']);
                $wall = $byCode->get($assignment['wall']);
                if (!$wall) throw ValidationException::withMessages(['openings' => 'The selected wall does not exist.']);
                $openingType = SpatialOpeningType::whereKey($assignment['type_id'])->where('is_active', true)->firstOrFail();
                if ((float)$openingSpec['width'] > (float)$wall->length || (float)$openingSpec['height'] > (float)$wall->height) {
                    throw ValidationException::withMessages(['openings' => 'Opening '.$openingSpec['code'].' exceeds the selected wall dimensions.']);
                }
                if ((float)$openingSpec['width'] * (float)$openingSpec['height'] > (float)$wall->gross_area) {
                    throw ValidationException::withMessages(['openings' => 'Opening area exceeds the selected wall area.']);
                }
                $targetKey = $assignment['connects_to'] ?? '';
                if ($targetKey !== '' && !array_key_exists($targetKey, $attachedRoomIds)) {
                    throw ValidationException::withMessages(['openings' => 'Invalid attached-room connection.']);
                }
                $existingCode = $room->walls->contains(fn ($w) => $w->openings->contains('opening_code', $openingSpec['code']));
                if ($existingCode) continue;
                $opening = ProjectWallOpening::create([
                    'project_room_wall_id' => $wall->id, 'spatial_opening_type_id' => $openingType->id,
                    'opening_code' => $openingSpec['code'], 'name' => $openingSpec['name'],
                    'dimension_unit' => $unit, 'area_unit' => $areaUnit,
                    'width' => $openingSpec['width'], 'height' => $openingSpec['height'], 'quantity' => 1,
                    'calculated_area' => round($openingSpec['width'] * $openingSpec['height'], 3),
                    'connects_to_project_room_id' => $targetKey !== '' ? $attachedRoomIds[$targetKey] : null,
                    'deduct_from_wall_area' => true, 'is_active' => true,
                    'remarks' => 'Created using Intelligent Room Setup',
                ]);
                AuditHelper::log('Project Locations', 'Wall Opening Auto Created', 'ProjectWallOpening', $opening->id, 'Opening: '.$room->name.' '.$openingSpec['code'], null, $opening->toArray());
                $created['openings']++;
            }
            foreach ($room->walls()->get() as $wall) {
                $deductions = (float) $wall->openings()->where('is_active', true)->where('deduct_from_wall_area', true)->sum('calculated_area');
                if ($wall->gross_area !== null && $deductions > (float)$wall->gross_area + 0.001) {
                    throw ValidationException::withMessages(['openings' => 'Total opening deductions exceed gross wall area for '.$wall->wall_code.'.']);
                }
                $wall->update(['openings_area' => round($deductions, 3),
                    'net_area' => $wall->gross_area === null ? null : round(max(0, (float)$wall->gross_area - $deductions), 3)]);
            }
            return $created;
        });
        return redirect()->route('project-locations.rooms.designer', $projectRoom)
            ->with('success', "Room setup completed: {$result['geometry']} geometry, {$result['walls']} walls, {$result['attached']} attached rooms and {$result['openings']} openings created.");
    }
    private function attachedSpecs(ProjectRoom $room, array $data): array
    {
        $result = [];
        foreach (($data['attached'] ?? []) as $key => $input) {
            if (empty($input['enabled'])) continue;
            foreach (['name','spatial_space_type_id','length','width','clear_height'] as $field) {
                if (empty($input[$field])) throw ValidationException::withMessages(['attached' => "Complete all dimensions, name and space type for attached space {$key}."]);
            }
            $type = SpatialSpaceType::whereKey($input['spatial_space_type_id'])->where('is_active', true)->firstOrFail();
            $name = trim($input['name']);
            $existing = ProjectRoom::query()->where('project_id', $room->project_id)
                ->where('project_floor_id', $room->project_floor_id)
                ->where('project_unit_id', $room->project_unit_id)
                ->where('name', $name)->first();
            if ($existing && (int)$existing->id === (int)$room->id) throw ValidationException::withMessages(['attached' => 'An attached room cannot be the primary room.']);
            if ($existing && (int)$existing->spatial_space_type_id !== (int)$type->id) throw ValidationException::withMessages(['attached' => 'An existing room with this name has a different space type.']);
            $result[] = ['key' => (string)$key, 'name' => $name, 'type_id' => $type->id, 'type_name' => $type->name,
                'length' => (float)$input['length'], 'width' => (float)$input['width'], 'height' => (float)$input['clear_height'],
                'existing_id' => $existing?->id];
        }
        if (count(array_unique(array_column($result, 'name'))) !== count($result)) throw ValidationException::withMessages(['attached' => 'Attached room names must be unique.']);
        return $result;
    }

    private function createGeometryAndWalls(ProjectRoom $room, array $spec, string $unit, string $areaUnit, SpatialComponentType $component): void
    {
        $length = $spec['length']; $width = $spec['width']; $height = $spec['height'];
        $area = round($length * $width, 3);
        $geometry = ProjectRoomGeometry::create([
            'project_room_id' => $room->id, 'spatial_space_type_id' => $room->spatial_space_type_id,
            'shape_type' => 'rectangle', 'dimension_unit' => $unit, 'area_unit' => $areaUnit,
            'length' => $length, 'width' => $width, 'clear_height' => $height,
            'calculated_floor_area' => $area, 'ceiling_area' => $area,
            'perimeter' => round(2 * ($length + $width), 3), 'volume' => round($area * $height, 3),
            'use_manual_floor_area' => false, 'geometry_notes' => 'Created using Intelligent Room Setup',
        ]);
        AuditHelper::log('Project Locations', 'Room Geometry Auto Created', 'ProjectRoomGeometry', $geometry->id, 'Attached room geometry: '.$room->name, null, $geometry->toArray());
        foreach ([['W1','North',$length], ['W2','South',$length], ['W3','East',$width], ['W4','West',$width]] as [$code,$orientation,$wallLength]) {
            $gross = round($wallLength * $height, 3);
            $wall = ProjectRoomWall::create([
                'project_room_id' => $room->id, 'spatial_component_type_id' => $component->id,
                'wall_code' => $code, 'name' => $orientation.' Wall', 'orientation' => $orientation,
                'dimension_unit' => $unit, 'area_unit' => $areaUnit, 'length' => $wallLength,
                'height' => $height, 'gross_area' => $gross, 'openings_area' => 0, 'net_area' => $gross,
                'is_external' => false, 'is_shared' => false, 'is_active' => true,
                'sort_order' => (int)substr($code,1), 'remarks' => 'Created using Intelligent Room Setup',
            ]);
            AuditHelper::log('Project Locations', 'Room Wall Auto Created', 'ProjectRoomWall', $wall->id, 'Attached room wall: '.$room->name.' '.$code, null, $wall->toArray());
        }
    }

}
