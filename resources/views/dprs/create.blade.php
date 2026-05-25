@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Create DPR
</h1>

@if ($errors->any())

    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">

        <ul>

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('dprs.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <div class="bg-white rounded shadow p-6 mb-6">

        <div class="mb-4">

            <label class="block mb-2 font-bold">
                DPR Date
            </label>

            <input type="date"
                   name="dpr_date"
                   class="border p-2 rounded w-full">

        </div>

        <div class="mb-4">

            <label class="block mb-2 font-bold">
                Project
            </label>

            <select name="project_id"
                    class="border p-2 rounded w-full">

                @foreach($projects as $project)

                    <option value="{{ $project->id }}">
                        {{ $project->project_name }}
                    </option>

                @endforeach

            </select>

        </div>

    </div>

    <h2 class="text-2xl font-bold mb-4">
        Work Items
    </h2>

    <div id="work-items">

        <div class="work-item bg-white rounded shadow p-6 mb-4">

            <div class="mb-4">

                <label class="block mb-2 font-bold">
                    Activity
                </label>

                <select name="activity_id[]"
                        class="border p-2 rounded w-full">

                    @foreach($activities as $activity)

                        <option value="{{ $activity->id }}">
                            {{ $activity->activity_name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-4">

                <label class="block mb-2 font-bold">
                    Contractor
                </label>

                <select name="contractor_id[]"
                        class="border p-2 rounded w-full">

                    @foreach($contractors as $contractor)

                        <option value="{{ $contractor->id }}">
                            {{ $contractor->contractor_name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-4">

                <label class="block mb-2 font-bold">
                    Quantity Completed
                </label>

                <input type="number"
                       step="0.01"
                       name="quantity_completed[]"
                       class="border p-2 rounded w-full">

            </div>

            <div class="mb-4">

                <label class="block mb-2 font-bold">
                    Work Remarks
                </label>

                <textarea name="work_remarks[]"
                          class="border p-2 rounded w-full"></textarea>

            </div>

        </div>

    </div>

    <button type="button"
            onclick="addWorkItem()"
            class="bg-green-500 text-white px-4 py-2 rounded mb-6">

        Add More Work

    </button>

    <div class="bg-white rounded shadow p-6 mb-6">

        <div class="mb-4">

            <label class="block mb-2 font-bold">
                Weather
            </label>

            <input type="text"
                   name="weather"
                   class="border p-2 rounded w-full">

        </div>

        <div class="mb-4">

            <label class="block mb-2 font-bold">
                Remarks
            </label>

            <textarea name="remarks"
                      class="border p-2 rounded w-full"></textarea>

        </div>

    </div>

    <!-- Labour Details -->

<div class="bg-white rounded shadow p-6 mt-8">

    <h2 class="text-2xl font-bold mb-6">
        Labour Details
    </h2>

    <div id="labour-container">

        <div class="grid grid-cols-5 gap-4 mb-4 labour-row">

            <div>

                <label class="font-semibold">
                    Labour Type
                </label>

                <select name="labour_type[]"
        class="w-full border rounded p-2">

    <option value="">
        Select Labour Type
    </option>

    @foreach($labourTypes as $type)

        <option value="{{ $type->id }}">

            {{ $type->labour_type_name }}

        </option>

    @endforeach

</select>

            </div>

            <div>

                <label class="font-semibold">
                    Male
                </label>

                <input type="number"
                       name="male_count[]"
                       value="0"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="font-semibold">
                    Female
                </label>

                <input type="number"
                       name="female_count[]"
                       value="0"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="font-semibold">
                    Local
                </label>

                <input type="number"
                       name="local_count[]"
                       value="0"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="font-semibold">
                    Non Local
                </label>

                <input type="number"
                       name="non_local_count[]"
                       value="0"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

    <button type="button"
            onclick="addLabourRow()"
            class="bg-blue-600 text-white px-4 py-2 rounded mt-4">

        Add Labour Row

    </button>

</div>
    <div class="mt-6">

    <label class="block mb-2 font-bold">
        Site Photos
    </label>

    <input type="file"
           name="photos[]"
           multiple
           class="w-full border rounded px-4 py-2">

    <p class="text-sm text-gray-500 mt-2">
        You can upload multiple progress photos.
    </p>

</div>

    <button type="submit"
            class="bg-blue-500 text-white px-6 py-3 rounded">

        Save DPR

    </button>

</form>

<script>

function addWorkItem()
{
    let container = document.getElementById('work-items');

    let item = document.querySelector('.work-item').cloneNode(true);

    item.querySelectorAll('input').forEach(input => {
        input.value = '';
    });

    item.querySelectorAll('textarea').forEach(textarea => {
        textarea.value = '';
    });

    container.appendChild(item);
}

</script>
<script>

function addLabourRow()
{
    let html = `

    <div class="grid grid-cols-5 gap-4 mb-4 labour-row">

        <div>
            <input type="text"
                   name="labour_type[]"
                   placeholder="Mason / Helper"
                   class="w-full border rounded p-2">
        </div>

        <div>
            <input type="number"
                   name="male_count[]"
                   value="0"
                   class="w-full border rounded p-2">
        </div>

        <div>
            <input type="number"
                   name="female_count[]"
                   value="0"
                   class="w-full border rounded p-2">
        </div>

        <div>
            <input type="number"
                   name="local_count[]"
                   value="0"
                   class="w-full border rounded p-2">
        </div>

        <div>
            <input type="number"
                   name="non_local_count[]"
                   value="0"
                   class="w-full border rounded p-2">
        </div>

    </div>
    `;

    document.getElementById('labour-container')
        .insertAdjacentHTML('beforeend', html);
}

</script>

@endsection