@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-5">

    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Project Structure Wizard
            </h1>
            <p class="text-sm text-gray-500">
                {{ $project->project_code }} — {{ $project->project_name }}
            </p>
        </div>

        <a href="{{ route('project-locations.index', ['project_id' => $project->id]) }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md mb-4 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('project-locations.wizard.generate', $project->id) }}"
          method="POST"
          class="bg-white border rounded-lg shadow-sm overflow-hidden">

        @csrf

        <div class="bg-blue-700 text-white px-5 py-4">
            <h2 class="text-lg font-bold">Building Configuration</h2>
            <p class="text-sm text-blue-100">
                Generate cellar, ground floor, residential floors, flats, rooms and sub-spaces.
            </p>
        </div>

        <div class="p-5 space-y-6">

            {{-- BASIC CONFIG --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">
                    1. Project Type & Blocks
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">Project Type</label>
                        <select name="project_type" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="Apartment">Apartment</option>
                            <option value="Villa">Villa</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Mixed Use">Mixed Use</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Block Type</label>
                        <select name="block_type" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="Building">Building</option>
                            <option value="Block">Block</option>
                            <option value="Tower">Tower</option>
                            <option value="Villa">Villa</option>
                            <option value="External Area">External Area</option>
                            <option value="Not Applicable">Not Applicable</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Number of Blocks</label>
                        <input type="number"
                               name="blocks"
                               value="1"
                               min="1"
                               max="20"
                               class="w-full border rounded-md px-3 py-2 text-sm"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Units per Residential Floor</label>
                        <input type="number"
                               name="units_per_floor"
                               value="2"
                               min="0"
                               max="100"
                               class="w-full border rounded-md px-3 py-2 text-sm"
                               required>
                    </div>

                </div>
            </div>

            {{-- PARKING --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">
                    2. Parking & Floor Logic
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">Parking Type</label>
                        <select name="parking_type" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="Ground Parking">Ground Parking</option>
                            <option value="Cellar Parking">Cellar Parking</option>
                            <option value="No Parking">No Parking</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Number of Cellars</label>
                        <input type="number"
                               name="cellars"
                               value="0"
                               min="0"
                               max="5"
                               class="w-full border rounded-md px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            Use only when parking type is Cellar Parking.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Residential Floors Above Ground</label>
                        <input type="number"
                               name="residential_floors"
                               value="5"
                               min="0"
                               max="100"
                               class="w-full border rounded-md px-3 py-2 text-sm"
                               required>
                        <p class="text-xs text-gray-500 mt-1">
                            5 means Ground + Floor 1 to Floor 5.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Ground Floor Residential?</label>
                        <select name="ground_has_residential" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- GROUND FLOOR EXTRAS --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">
                    3. Ground Floor Optional Spaces
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">Shops</label>
                        <input type="number"
                               name="shops"
                               value="0"
                               min="0"
                               max="100"
                               class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Watchman Room</label>
                        <select name="has_watchman_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Security Room</label>
                        <select name="has_security_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Washroom</label>
                        <select name="has_ground_washroom" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Electrical Room</label>
                        <select name="has_electrical_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Pump Room</label>
                        <select name="has_pump_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Meter Room</label>
                        <select name="has_meter_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">DG Room</label>
                        <select name="has_dg_room" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- FLAT CONFIG --}}
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3">
                    4. Flat / Unit Room Configuration
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-1">Bedrooms</label>
                        <input type="number"
                               name="bedrooms"
                               value="2"
                               min="0"
                               max="20"
                               class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Use Master Bedroom?</label>
                        <select name="has_master_bedroom" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Bathrooms / Washrooms</label>
                        <input type="number"
                               name="bathrooms"
                               value="2"
                               min="0"
                               max="20"
                               class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Balconies</label>
                        <input type="number"
                               name="balconies"
                               value="1"
                               min="0"
                               max="20"
                               class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Living Room</label>
                        <select name="has_living" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Dining</label>
                        <select name="has_dining" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Kitchen</label>
                        <select name="has_kitchen" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Utility</label>
                        <select name="has_utility" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Pooja Room</label>
                        <select name="has_pooja" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Study Room</label>
                        <select name="has_study" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Store Room</label>
                        <select name="has_store" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Home Office</label>
                        <select name="has_home_office" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-4 text-sm">
                Existing locations will not be deleted. Duplicate names are skipped automatically.
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        onclick="return confirm('Generate project structure now?')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-semibold">
                    Generate Structure
                </button>
            </div>

        </div>

    </form>

</div>

@endsection