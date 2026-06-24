@php
    $input = 'w-full border border-gray-300 rounded-md px-3 py-2 text-sm h-10 focus:ring-1 focus:ring-blue-500 focus:border-blue-500';
    $textarea = 'w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500';
    $label = 'block text-sm font-semibold text-gray-700 mb-1';
@endphp

<div x-data="{ tab: 'general' }">

    <input type="hidden" name="division_code" value="RH">

    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
            <p class="text-sm text-gray-500">{{ $subtitle }}</p>
        </div>

        <a href="{{ route('projects.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4 text-sm">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">

        <div class="bg-gray-100 border-b px-4 pt-3">
            <div class="flex flex-wrap gap-2">

                <button type="button"
                        @click="tab = 'general'"
                        :class="tab === 'general'
                            ? 'bg-white text-blue-700 border-blue-500 shadow-sm'
                            : 'bg-gray-200 text-gray-600 border-transparent hover:bg-gray-300'"
                        class="px-5 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold transition">
                    General
                </button>

                <button type="button"
                        @click="tab = 'team'"
                        :class="tab === 'team'
                            ? 'bg-white text-blue-700 border-blue-500 shadow-sm'
                            : 'bg-gray-200 text-gray-600 border-transparent hover:bg-gray-300'"
                        class="px-5 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold transition">
                    Team
                </button>

                <button type="button"
                        @click="tab = 'structure'"
                        :class="tab === 'structure'
                            ? 'bg-white text-blue-700 border-blue-500 shadow-sm'
                            : 'bg-gray-200 text-gray-600 border-transparent hover:bg-gray-300'"
                        class="px-5 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold transition">
                    Project Structure
                </button>

                <button type="button"
                        @click="tab = 'commercial'"
                        :class="tab === 'commercial'
                            ? 'bg-white text-blue-700 border-blue-500 shadow-sm'
                            : 'bg-gray-200 text-gray-600 border-transparent hover:bg-gray-300'"
                        class="px-5 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold transition">
                    Commercial
                </button>

                <button type="button"
                        @click="tab = 'remarks'"
                        :class="tab === 'remarks'
                            ? 'bg-white text-blue-700 border-blue-500 shadow-sm'
                            : 'bg-gray-200 text-gray-600 border-transparent hover:bg-gray-300'"
                        class="px-5 py-2 rounded-t-md border-t border-l border-r text-sm font-semibold transition">
                    Remarks
                </button>

            </div>
        </div>

        <div class="p-5 bg-white min-h-[360px]">

            <div x-show="tab === 'general'">
                @include('projects.partials.general')
            </div>

            <div x-show="tab === 'team'" style="display:none;">
                @include('projects.partials.team')
            </div>

            <div x-show="tab === 'structure'" style="display:none;">
                <div class="bg-white">

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">

                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-blue-600">
                                {{ $project ? $project->blocks()->count() : 0 }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Blocks
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-green-600">
                                {{ $project ? $project->floors()->count() : 0 }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Floors
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-purple-600">
                                {{ $project ? $project->units()->count() : 0 }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Units
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-orange-600">
                                {{ $project ? $project->rooms()->count() : 0 }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Rooms
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-red-600">
                                {{ $project ? $project->subspaces()->count() : 0 }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                Sub Spaces
                            </div>
                        </div>

                    </div>

                    @if($project)

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-5 mb-4">
                            <h2 class="text-lg font-bold text-blue-900 mb-2">
                                Project Structure Setup
                            </h2>

                            <p class="text-sm text-blue-800 mb-4">
                                Generate or manage the complete project hierarchy:
                                Blocks / Buildings → Floors → Units / Flats → Rooms → Sub-spaces.
                            </p>

                            <div class="flex flex-wrap gap-3">

                                <a href="{{ route('project-locations.wizard', $project->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                                    Generate Project Structure
                                </a>

                                <a href="{{ route('project-locations.index', ['project_id' => $project->id]) }}"
                                   class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-2 rounded-md text-sm font-semibold">
                                    Manage Project Structure
                                </a>

                            </div>
                        </div>

                    @else

                        <div class="bg-yellow-50 border border-yellow-300 rounded-md p-5">
                            <h3 class="font-bold text-lg mb-2">
                                Save Project First
                            </h3>

                            <p class="text-gray-600 text-sm">
                                Once the project is created, you can generate the complete project hierarchy automatically using the Structure Wizard.
                            </p>
                        </div>

                    @endif

                </div>
            </div>

            <div x-show="tab === 'commercial'" style="display:none;">
                @include('projects.partials.commercial')
            </div>

            <div x-show="tab === 'remarks'" style="display:none;">
                @include('projects.partials.remarks')
            </div>

        </div>
    </div>

    <div class="sticky bottom-0 bg-white border-t p-4 z-20 flex justify-between items-center mt-4">
        <a href="{{ route('projects.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-md text-sm">
            Cancel
        </a>

        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-md text-sm font-semibold">
            {{ $buttonText }}
        </button>
    </div>

</div>