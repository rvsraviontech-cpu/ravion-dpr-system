@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Create Weekly Plan
</h1>

<div class="bg-white rounded shadow p-6">

    <form action="/weekly-plans"
          method="POST">

        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>

                <label class="block mb-2">
                    Project
                </label>

                <select name="project_id"
                        class="w-full border rounded p-2">

                    @foreach($projects as $project)

                        <option value="{{ $project->id }}">

                            {{ $project->project_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Activity
                </label>

                <select name="activity_id"
                        class="w-full border rounded p-2">

                    @foreach($activities as $activity)

                        <option value="{{ $activity->id }}">

                            {{ $activity->activity_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Engineer
                </label>

                <select name="user_id"
                        class="w-full border rounded p-2">

                    @foreach($engineers as $engineer)

                        <option value="{{ $engineer->id }}">

                            {{ $engineer->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Planned Quantity
                </label>

                <input type="number"
                       step="0.01"
                       name="planned_quantity"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Unit
                </label>

                <input type="text"
                       name="unit"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Planned Labour
                </label>

                <input type="number"
                       name="planned_labour"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Week Start Date
                </label>

                <input type="date"
                       name="week_start_date"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Week End Date
                </label>

                <input type="date"
                       name="week_end_date"
                       class="w-full border rounded p-2">

            </div>

        </div>

        <div class="mt-6">

            <label class="block mb-2">
                Materials Required
            </label>

            <textarea name="materials_required"
                      class="w-full border rounded p-2"></textarea>

        </div>

        <div class="mt-6">

            <label class="block mb-2">
                Machinery Required
            </label>

            <textarea name="machinery_required"
                      class="w-full border rounded p-2"></textarea>

        </div>

        <div class="mt-6">

            <label class="block mb-2">
                Risks / Constraints
            </label>

            <textarea name="risks_constraints"
                      class="w-full border rounded p-2"></textarea>

        </div>

        <div class="mt-6">

            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded">

                Save Weekly Plan

            </button>

        </div>

    </form>

</div>

@endsection