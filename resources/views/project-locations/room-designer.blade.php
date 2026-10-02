@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Room Designer — {{ $projectRoom->name }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $projectRoom->floor?->block?->name }} / {{ $projectRoom->floor?->name }} / {{ $projectRoom->unit?->name ?? 'Floor Common Space' }} · Room ID {{ $projectRoom->id }}</p>
        </div>
        <a class="bg-gray-600 text-white rounded px-4 py-2" href="{{ route('project-locations.rooms.edit', $projectRoom) }}">← Edit Room</a>
    </div>
    @if(session('success'))<div class="bg-green-100 text-green-900 p-3 rounded">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 text-red-900 p-4 rounded"><strong>Please correct:</strong><ul class="list-disc ml-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @php($g = $projectRoom->geometry)
    <section class="bg-white shadow rounded p-5 space-y-4">
        <h2 class="text-xl font-semibold">1. Room Geometry & Measurements</h2>
        <p class="text-sm text-gray-600">All measurements are optional. Rectangular dimensions calculate area and perimeter; irregular rooms may use manual area. Enter dimensions in one consistent unit.</p>
        <form method="POST" action="{{ route('project-locations.rooms.designer.geometry', $projectRoom) }}" class="space-y-4">@csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="block">Shape<select name="shape_type" class="border rounded p-2 w-full"><option value="rectangle" @selected(old('shape_type', $g?->shape_type ?? 'rectangle') === 'rectangle')>Rectangle</option><option value="irregular" @selected(old('shape_type', $g?->shape_type) === 'irregular')>Irregular / Manual</option></select></label>
                <label class="block">Dimension unit<select name="dimension_unit" class="border rounded p-2 w-full"><option value="m" @selected(old('dimension_unit', $g?->dimension_unit ?? 'm') === 'm')>Metres (m)</option><option value="ft" @selected(old('dimension_unit', $g?->dimension_unit) === 'ft')>Feet (ft)</option></select></label>
                <label class="block">Length<input name="length" type="number" step="0.001" min="0.001" value="{{ old('length', $g?->length) }}" class="border rounded p-2 w-full"></label>
                <label class="block">Width<input name="width" type="number" step="0.001" min="0.001" value="{{ old('width', $g?->width) }}" class="border rounded p-2 w-full"></label>
                <label class="block">Clear height<input name="clear_height" type="number" step="0.001" min="0.001" value="{{ old('clear_height', $g?->clear_height) }}" class="border rounded p-2 w-full"></label>
                <label class="block">Manual floor area<input name="manual_floor_area" type="number" step="0.001" min="0" value="{{ old('manual_floor_area', $g?->manual_floor_area) }}" class="border rounded p-2 w-full"></label>
            </div>
            <label class="flex gap-2 items-center"><input type="hidden" name="use_manual_floor_area" value="0"><input type="checkbox" name="use_manual_floor_area" value="1" @checked(old('use_manual_floor_area', $g?->use_manual_floor_area))> Use manual floor area instead of calculated area</label>
            <label class="block">Geometry notes<textarea name="geometry_notes" rows="2" class="border rounded p-2 w-full">{{ old('geometry_notes', $g?->geometry_notes) }}</textarea></label>
            <button class="bg-blue-700 text-white rounded px-4 py-2">Save Measurements</button>
        </form>
        @if($g)<div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm bg-gray-50 rounded p-3"><div><strong>Calculated area</strong><br>{{ $g->calculated_floor_area ?? '—' }} {{ $g->area_unit }}</div><div><strong>Effective floor / ceiling area</strong><br>{{ $g->use_manual_floor_area ? $g->manual_floor_area : $g->calculated_floor_area }} {{ $g->area_unit }}</div><div><strong>Perimeter</strong><br>{{ $g->perimeter ?? '—' }} {{ $g->dimension_unit }}</div><div><strong>Volume</strong><br>{{ $g->volume ?? '—' }} {{ $g->dimension_unit }}³</div></div>@endif
    </section>
    <section class="bg-white shadow rounded p-5 space-y-4">
        <h2 class="text-xl font-semibold">2. Individual Walls</h2>
        <p class="text-sm text-gray-600">Create a wall using a component master. Gross area = length × height. Existing opening deductions are preserved when editing a wall.</p>
        <form method="POST" action="{{ route('project-locations.rooms.designer.wall', $projectRoom) }}" class="space-y-4">@csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label>Wall to edit<select name="wall_id" id="designer-wall-id" class="border rounded p-2 w-full"><option value="">+ New wall</option>@foreach($projectRoom->walls as $wall)<option value="{{ $wall->id }}" @selected(old('wall_id') == $wall->id)>{{ $wall->wall_code }} — {{ $wall->name ?: 'Wall' }}</option>@endforeach</select></label>
                <label>Component master<select name="spatial_component_type_id" id="designer-wall-type" required class="border rounded p-2 w-full"><option value="">Select component type</option>@foreach($componentTypes as $type)<option value="{{ $type->id }}" @selected(old('spatial_component_type_id') == $type->id)>{{ $type->name }}</option>@endforeach</select></label>
                <label>Wall code<input name="wall_code" id="designer-wall-code" required maxlength="80" placeholder="W1" value="{{ old('wall_code') }}" class="border rounded p-2 w-full"></label>
                <label>Wall name<input name="name" id="designer-wall-name" value="{{ old('name') }}" placeholder="North wall" class="border rounded p-2 w-full"></label>
                <label>Orientation<input name="orientation" id="designer-wall-orientation" value="{{ old('orientation') }}" placeholder="North / South / ..." class="border rounded p-2 w-full"></label>
                <label>Wall function<input name="wall_function" id="designer-wall-function" value="{{ old('wall_function') }}" placeholder="Partition / external / ..." class="border rounded p-2 w-full"></label>
                <label>Dimension unit<select name="dimension_unit" id="designer-wall-unit" class="border rounded p-2 w-full"><option value="m">Metres (m)</option><option value="ft">Feet (ft)</option></select></label>
                <label>Length<input name="length" id="designer-wall-length" type="number" step="0.001" min="0.001" value="{{ old('length') }}" class="border rounded p-2 w-full"></label>
                <label>Height<input name="height" id="designer-wall-height" type="number" step="0.001" min="0.001" value="{{ old('height') }}" class="border rounded p-2 w-full"></label>
                <label>Status<select name="is_active" id="designer-wall-active" class="border rounded p-2 w-full"><option value="1">Active</option><option value="0">Inactive</option></select></label>
                <label class="flex gap-2 items-center"><input type="checkbox" name="is_external" value="1" id="designer-wall-external"> External wall</label>
                <label class="flex gap-2 items-center"><input type="checkbox" name="is_shared" value="1" id="designer-wall-shared"> Shared wall</label>
            </div>
            <label class="block">Remarks<textarea name="remarks" id="designer-wall-remarks" rows="2" class="border rounded p-2 w-full">{{ old('remarks') }}</textarea></label>
            <button class="bg-blue-700 text-white rounded px-4 py-2">Save Wall</button>
            <button type="button" id="designer-wall-reset" class="bg-gray-200 rounded px-4 py-2">Clear / New Wall</button>
        </form>
        <div class="overflow-x-auto"><table class="w-full text-sm border-collapse"><thead><tr class="bg-gray-100 text-left"><th class="p-2">Code</th><th class="p-2">Name</th><th class="p-2">Length × Height</th><th class="p-2">Gross</th><th class="p-2">Openings</th><th class="p-2">Net</th><th class="p-2">Status</th></tr></thead><tbody>@forelse($projectRoom->walls as $wall)<tr class="border-t"><td class="p-2">{{ $wall->wall_code }}</td><td class="p-2">{{ $wall->name }}</td><td class="p-2">{{ $wall->length ?? '—' }} × {{ $wall->height ?? '—' }} {{ $wall->dimension_unit }}</td><td class="p-2">{{ $wall->gross_area ?? '—' }} {{ $wall->area_unit }}</td><td class="p-2">{{ $wall->openings_area ?? '—' }}</td><td class="p-2">{{ $wall->net_area ?? '—' }}</td><td class="p-2">{{ $wall->is_active ? 'Active' : 'Inactive' }}</td></tr>@empty<tr><td colspan="7" class="p-3 text-gray-500">No walls configured yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <section class="bg-white shadow rounded p-5 space-y-4">
        <h2 class="text-xl font-semibold">3. Doors, Windows &amp; Connections</h2>
        <p class="text-sm text-gray-600">Openings inherit their wall's measurement unit. Select an existing opening to edit it. A connected room or element must be on this floor.</p>
        <form method="POST" action="{{ route('project-locations.rooms.designer.opening', $projectRoom) }}" class="space-y-4">@csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label>Opening to edit<select name="opening_id" id="designer-opening-id" class="border rounded p-2 w-full"><option value="">+ New opening</option>@foreach($projectRoom->walls as $wall)@foreach($wall->openings as $opening)<option value="{{ $opening->id }}" @selected(old('opening_id') == $opening->id)>{{ $wall->wall_code }} / {{ $opening->opening_code }}</option>@endforeach @endforeach</select></label>
                <label>Parent wall<select name="project_room_wall_id" id="designer-opening-wall" required class="border rounded p-2 w-full"><option value="">Select wall</option>@foreach($projectRoom->walls as $wall)<option value="{{ $wall->id }}">{{ $wall->wall_code }} — {{ $wall->name }} ({{ $wall->dimension_unit }})</option>@endforeach</select></label>
                <label>Opening master<select name="spatial_opening_type_id" id="designer-opening-type" required class="border rounded p-2 w-full"><option value="">Select type</option>@foreach($openingTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></label>
                <label>Code<input name="opening_code" id="designer-opening-code" required maxlength="80" placeholder="D1" class="border rounded p-2 w-full"></label>
                <label>Name<input name="name" id="designer-opening-name" placeholder="Bedroom door" class="border rounded p-2 w-full"></label>
                <label>Width (wall unit)<input name="width" id="designer-opening-width" required type="number" step="0.001" min="0.001" class="border rounded p-2 w-full"></label>
                <label>Height (wall unit)<input name="height" id="designer-opening-height" required type="number" step="0.001" min="0.001" class="border rounded p-2 w-full"></label>
                <label>Quantity<input name="quantity" id="designer-opening-quantity" type="number" min="1" value="1" required class="border rounded p-2 w-full"></label>
                <label>Sill height (optional)<input name="sill_height" id="designer-opening-sill" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Lintel height (optional)<input name="lintel_height" id="designer-opening-lintel" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Connect to room<select name="connects_to_project_room_id" id="designer-opening-room" class="border rounded p-2 w-full"><option value="">None</option>@foreach($connectionRooms as $room)<option value="{{ $room->id }}">{{ $room->name }} (#{{ $room->id }})</option>@endforeach</select></label>
                <label>Or connect to existing element<select name="connects_to_project_subspace_id" id="designer-opening-subspace" class="border rounded p-2 w-full"><option value="">None</option>@foreach($connectionSubspaces as $element)<option value="{{ $element->id }}">{{ $element->name }} (#{{ $element->id }})</option>@endforeach</select></label>
                <label>Status<select name="is_active" id="designer-opening-active" class="border rounded p-2 w-full"><option value="1">Active</option><option value="0">Inactive</option></select></label>
                <label>Deduct from wall<select name="deduct_from_wall_area" id="designer-opening-deduct" class="border rounded p-2 w-full"><option value="1">Yes</option><option value="0">No</option></select></label>
            </div>
            <label class="block">Remarks<textarea name="remarks" id="designer-opening-remarks" class="border rounded p-2 w-full" rows="2"></textarea></label>
            <button class="bg-blue-700 text-white rounded px-4 py-2">Save Opening</button><button type="button" id="designer-opening-reset" class="bg-gray-200 rounded px-4 py-2">Clear / New</button>
        </form>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-100 text-left"><th class="p-2">Wall</th><th class="p-2">Opening</th><th class="p-2">Size × Qty</th><th class="p-2">Area</th><th class="p-2">Connection</th><th class="p-2">Deduct</th><th class="p-2">Status</th></tr></thead><tbody>@forelse($projectRoom->walls as $wall)@foreach($wall->openings as $opening)<tr class="border-t"><td class="p-2">{{ $wall->wall_code }}</td><td class="p-2">{{ $opening->opening_code }} — {{ $opening->name }}</td><td class="p-2">{{ $opening->width }} × {{ $opening->height }} × {{ $opening->quantity }} {{ $opening->dimension_unit }}</td><td class="p-2">{{ $opening->calculated_area }} {{ $opening->area_unit }}</td><td class="p-2">{{ $opening->connectedRoom?->name ?? $opening->connectedSubspace?->name ?? '—' }}</td><td class="p-2">{{ $opening->deduct_from_wall_area ? 'Yes' : 'No' }}</td><td class="p-2">{{ $opening->is_active ? 'Active' : 'Inactive' }}</td></tr>@endforeach @empty<tr><td colspan="7" class="p-3 text-gray-500">No walls yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <section class="bg-white shadow rounded p-5 space-y-4">
        <h2 class="text-xl font-semibold">4. Measurement Zones</h2>
        <p class="text-sm text-gray-600">Optional work measurement records. Select an existing zone to edit it; manual values are supported.</p>
        <form method="POST" action="{{ route('project-locations.rooms.designer.zone', $projectRoom) }}" class="space-y-4">@csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label>Zone to edit<select name="zone_id" id="designer-zone-id" class="border rounded p-2 w-full"><option value="">+ New zone</option>@foreach($projectRoom->measurementZones as $zone)<option value="{{ $zone->id }}" @selected(old('zone_id') == $zone->id)>#{{ $zone->id }} — {{ $zone->name }}</option>@endforeach</select></label>
                <label>Zone master<select name="spatial_measurement_zone_id" id="designer-zone-type" required class="border rounded p-2 w-full"><option value="">Select type</option>@foreach($zoneTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></label>
                <label>Wall (optional)<select name="project_room_wall_id" id="designer-zone-wall" class="border rounded p-2 w-full"><option value="">Entire room / independent</option>@foreach($projectRoom->walls as $wall)<option value="{{ $wall->id }}">{{ $wall->wall_code }} — {{ $wall->name }}</option>@endforeach</select></label>
                <label>Name<input name="name" id="designer-zone-name" class="border rounded p-2 w-full" maxlength="255"></label>
                <label>Measurement basis<select name="measurement_basis" id="designer-zone-basis" class="border rounded p-2 w-full"><option value="area">Area (L × W × Qty)</option><option value="length">Length (L × Qty)</option><option value="volume">Volume (L × W × H × Qty)</option><option value="count">Count (Qty)</option></select></label>
                <label>Unit<select name="unit" id="designer-zone-unit" class="border rounded p-2 w-full"><option value="sqft">sq.ft</option><option value="sqm">sq.m</option><option value="ft">ft</option><option value="m">m</option><option value="cuft">cu.ft</option><option value="cum">cu.m</option><option value="nos">nos</option></select></label>
                <label>Length<input name="length" id="designer-zone-length" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Width<input name="width" id="designer-zone-width" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Height<input name="height" id="designer-zone-height" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Quantity<input name="quantity" id="designer-zone-quantity" type="number" step="0.001" min="0" value="1" class="border rounded p-2 w-full"></label>
                <label>Manual override<select name="use_manual_value" id="designer-zone-manual" class="border rounded p-2 w-full"><option value="0">Calculate</option><option value="1">Use manual value</option></select></label>
                <label>Manual value<input name="manual_value" id="designer-zone-manual-value" type="number" step="0.001" min="0" class="border rounded p-2 w-full"></label>
                <label>Status<select name="is_active" id="designer-zone-active" class="border rounded p-2 w-full"><option value="1">Active</option><option value="0">Inactive</option></select></label>
            </div>
            <label>Remarks<textarea name="remarks" id="designer-zone-remarks" rows="2" class="border rounded p-2 w-full"></textarea></label>
            <div><button class="bg-blue-700 text-white rounded px-4 py-2">Save Zone</button> <button type="button" id="designer-zone-reset" class="bg-gray-200 rounded px-4 py-2">Clear / New</button></div>
        </form>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-100 text-left"><th class="p-2">Zone</th><th class="p-2">Basis</th><th class="p-2">Calculated</th><th class="p-2">Effective</th><th class="p-2">Status</th></tr></thead><tbody>@forelse($projectRoom->measurementZones as $zone)<tr class="border-t"><td class="p-2">{{ $zone->name ?: '#'.$zone->id }}</td><td class="p-2">{{ $zone->measurement_basis }}</td><td class="p-2">{{ $zone->calculated_value ?? '—' }} {{ $zone->unit }}</td><td class="p-2">{{ $zone->use_manual_value ? $zone->manual_value : $zone->calculated_value }} {{ $zone->unit }}</td><td class="p-2">{{ $zone->is_active ? 'Active' : 'Inactive' }}</td></tr>@empty<tr><td colspan="5" class="p-3 text-gray-500">No measurement zones yet.</td></tr>@endforelse</tbody></table></div>
    </section>
    <p class="text-sm text-gray-600">Existing room elements remain unchanged. Measurements are independent of Work Done transactions.</p>
</div>
<script>
(() => {
 const walls = {{ Illuminate\Support\Js::from($projectRoom->walls->map(fn ($w) => $w->only([
    'id',
    'spatial_component_type_id',
    'wall_code',
    'name',
    'orientation',
    'wall_function',
    'length',
    'height',
    'dimension_unit',
    'is_external',
    'is_shared',
    'is_active',
    'remarks',
]))->values()->all()) }};
 const openings = {{ Illuminate\Support\Js::from($projectRoom->walls->flatMap(fn ($wall) => $wall->openings->map(fn ($opening) => $opening->only([
    'id', 'project_room_wall_id', 'spatial_opening_type_id', 'opening_code',
    'name', 'width', 'height', 'quantity', 'sill_height', 'lintel_height',
    'connects_to_project_room_id', 'connects_to_project_subspace_id',
    'is_active', 'deduct_from_wall_area', 'remarks',
])))->values()->all()) }};
 const zones = {{ Illuminate\Support\Js::from($projectRoom->measurementZones->map(fn ($zone) => $zone->only([
    'id', 'spatial_measurement_zone_id', 'project_room_wall_id', 'name',
    'measurement_basis', 'unit', 'length', 'width', 'height', 'quantity',
    'use_manual_value', 'manual_value', 'is_active', 'remarks',
]))->values()->all()) }};
 const asSelectBoolean = value => value === true || value === 1 || value === '1' ? '1' : '0';
 const setDesignerValue = (prefix, suffix, value, fallback = '') => {
   const element = document.getElementById(prefix + suffix);
   if (element) element.value = value === null || value === undefined ? fallback : String(value);
 };
 const openingSelect = document.getElementById('designer-opening-id');
 function fillOpening() {
   const opening = openings.find(item => String(item.id) === openingSelect.value);
   const fields = {
     wall: 'project_room_wall_id', type: 'spatial_opening_type_id',
     code: 'opening_code', name: 'name', width: 'width', height: 'height',
     quantity: 'quantity', sill: 'sill_height', lintel: 'lintel_height',
     room: 'connects_to_project_room_id', subspace: 'connects_to_project_subspace_id',
     remarks: 'remarks'
   };
   Object.entries(fields).forEach(([suffix, key]) => setDesignerValue('designer-opening-', suffix, opening?.[key], suffix === 'quantity' ? '1' : ''));
   setDesignerValue('designer-opening-', 'active', opening ? asSelectBoolean(opening.is_active) : '1');
   setDesignerValue('designer-opening-', 'deduct', opening ? asSelectBoolean(opening.deduct_from_wall_area) : '1');
 }
 openingSelect.addEventListener('change', fillOpening);
 document.getElementById('designer-opening-reset').addEventListener('click', () => {
   openingSelect.value = '';
   fillOpening();
 });
 if (openingSelect.value) fillOpening();
 const zoneSelect = document.getElementById('designer-zone-id');
 function fillZone() {
   const zone = zones.find(item => String(item.id) === zoneSelect.value);
   const fields = {
     type: 'spatial_measurement_zone_id', wall: 'project_room_wall_id',
     name: 'name', basis: 'measurement_basis', unit: 'unit',
     length: 'length', width: 'width', height: 'height',
     quantity: 'quantity', 'manual-value': 'manual_value', remarks: 'remarks'
   };
   Object.entries(fields).forEach(([suffix, key]) => {
     const defaults = { basis: 'area', unit: 'sqft', quantity: '1' };
     setDesignerValue('designer-zone-', suffix, zone?.[key], defaults[suffix] ?? '');
   });
   setDesignerValue('designer-zone-', 'manual', zone ? asSelectBoolean(zone.use_manual_value) : '0');
   setDesignerValue('designer-zone-', 'active', zone ? asSelectBoolean(zone.is_active) : '1');
 }
 zoneSelect.addEventListener('change', fillZone);
 document.getElementById('designer-zone-reset').addEventListener('click', () => {
   zoneSelect.value = '';
   fillZone();
 });
 if (zoneSelect.value) fillZone();
 const select = document.getElementById('designer-wall-id');
 const field = (name) => document.getElementById('designer-wall-' + name);
 function fill() {
  const wall = walls.find(w => String(w.id) === select.value);
  ['type','code','name','orientation','function','unit','length','height','active','remarks'].forEach(k => { const el=field(k); if(!el)return; const map={type:'spatial_component_type_id',code:'wall_code',function:'wall_function',unit:'dimension_unit',active:'is_active'}; const v=wall?.[map[k]||k]; el.value=v==null ? (k==='active'?'1':k==='unit'?'m':'') : (k==='active' ? (v ? '1' : '0') : String(v)); });
  field('external').checked=!!wall?.is_external; field('shared').checked=!!wall?.is_shared;
 }
 select.addEventListener('change', fill);
 document.getElementById('designer-wall-reset').addEventListener('click',()=>{select.value='';fill();});
 if(select.value) fill();
})();
</script>
@endsection
