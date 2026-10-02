@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-5"><div><p class="text-xs text-slate-500">Intelligent Room Setup / Preview</p><h1 class="text-2xl font-bold">{{ $projectRoom->name }}</h1></div>
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">Floor area: <strong>{{ $plan['floor_area'] }}</strong> {{ $data['dimension_unit']==='ft'?'sqft':'sqm' }} · Perimeter: <strong>{{ $plan['perimeter'] }}</strong> {{ $data['dimension_unit'] }} · Geometry: <strong>{{ $plan['geometry_action'] }}</strong></div>
<div class="bg-white border rounded-xl overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-3 text-left">Wall</th><th class="p-3 text-left">Orientation</th><th class="p-3 text-left">Dimensions</th><th class="p-3 text-left">Action</th></tr></thead><tbody>@foreach($plan['walls'] as $wall)<tr class="border-t"><td class="p-3">{{ $wall['code'] }}</td><td class="p-3">{{ $wall['orientation'] }}</td><td class="p-3">{{ $wall['length'] }} × {{ $wall['height'] }} {{ $data['dimension_unit'] }}</td><td class="p-3">{{ $wall['action'] }}</td></tr>@endforeach</tbody></table></div>
@if(count($plan['attached']))<div class="bg-white border rounded-xl p-4"><h2 class="font-bold mb-2">Attached spaces</h2>@foreach($plan['attached'] as $spec)<div class="text-sm border-t py-2">{{ $spec['name'] }} — {{ $spec['type_name'] }} — {{ $spec['length'] }} × {{ $spec['width'] }} × {{ $spec['height'] }} {{ $data['dimension_unit'] }} — <strong>{{ $spec['existing_id'] ? 'Keep existing room #'.$spec['existing_id'] : 'Create new room' }}</strong></div>@endforeach</div>@endif
<form method="POST" action="{{ route('project-locations.rooms.quick-setup.generate',$projectRoom) }}" class="space-y-4">@csrf
@foreach ($data as $key => $value)
    @if (is_array($value))
        @foreach ($value as $subkey => $fields)
            @foreach ($fields as $field => $fieldValue)
                <input type="hidden" name="{{ $key }}[{{ $subkey }}][{{ $field }}]" value="{{ $fieldValue }}">
            @endforeach
        @endforeach
    @else
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
@endforeach
<div class="bg-white border rounded-xl p-4"><h2 class="font-bold mb-2">Opening wall assignments</h2><p class="text-sm text-slate-600 mb-3">Choose the real wall and opening master for each new opening. Connections to attached rooms are optional. Existing opening records are never changed.</p>
@forelse($plan['openings'] as $opening)<div class="grid md:grid-cols-4 gap-2 items-end border-t py-3 text-sm"><div><strong>{{ $opening['code'] }} — {{ $opening['name'] }}</strong><br>{{ $opening['width'] }} × {{ $opening['height'] }} {{ $data['dimension_unit'] }}<br><span class="text-slate-500">{{ $opening['action'] }}</span></div>
@if($opening['action']==='Keep existing')<div class="md:col-span-3 text-emerald-700">Existing opening preserved</div>@else
<label>Wall<select name="openings[{{ $opening['code'] }}][wall]" required class="block w-full rounded-lg border-slate-300"><option value="">Select wall</option>@foreach($plan['walls'] as $wall)<option value="{{ $wall['code'] }}">{{ $wall['code'] }} — {{ $wall['orientation'] }}</option>@endforeach</select></label>
<label>Opening master<select name="openings[{{ $opening['code'] }}][type_id]" required class="block w-full rounded-lg border-slate-300"><option value="">Select type</option>@foreach($openingTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></label>
<label>Connect to attached space<select name="openings[{{ $opening['code'] }}][connects_to]" class="block w-full rounded-lg border-slate-300"><option value="">No connection</option>@foreach($plan['attached'] as $spec)<option value="{{ $spec['key'] }}">{{ $spec['name'] }}</option>@endforeach</select></label>@endif</div>@empty<p class="text-slate-500">No openings selected</p>@endforelse</div>
<div class="flex gap-3"><button class="bg-emerald-700 text-white px-5 py-3 rounded-lg font-semibold">Confirm & generate structure</button><a class="border rounded-lg px-5 py-3" href="{{ route('project-locations.rooms.quick-setup',$projectRoom) }}">Back to setup</a></div></form></div>
@endsection
