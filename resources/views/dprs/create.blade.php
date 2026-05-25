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
   

<!-- Material Received -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Material Received
        </h2>

        <button type="button"
                onclick="addMaterialReceivedRow()"
                class="bg-green-600 text-white px-4 py-2 rounded">

            + Add Received Material

        </button>

    </div>

    <div id="material-received-container">

        <div class="grid grid-cols-5 gap-4 mb-4 material-received-row">

            <div>

                <label class="block mb-2">
                    Material
                </label>

                <select name="received_material_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Material
                    </option>

                    @foreach($materials as $material)

                        <option value="{{ $material->id }}">

                            {{ $material->material_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Vendor
                </label>

                <select name="vendor_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Vendor
                    </option>

                    @foreach($vendors as $vendor)

                        <option value="{{ $vendor->id }}">

                            {{ $vendor->vendor_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Qty Received
                </label>

                <input type="number"
                       step="0.01"
                       name="quantity_received[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Challan No
                </label>

                <input type="text"
                       name="challan_number[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Bill No
                </label>

                <input type="text"
                       name="bill_number[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>
<!-- Material Consumption -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Material Consumption
        </h2>

        <button type="button"
                onclick="addMaterialRow()"
                class="bg-blue-600 text-white px-4 py-2 rounded">

            + Add Material

        </button>

    </div>

    <div id="material-container">

        <div class="grid grid-cols-3 gap-4 mb-4 material-row">

            <div>

                <label class="block mb-2">
                    Material
                </label>

                <select name="material_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Material
                    </option>

                    @foreach($materials as $material)

                        <option value="{{ $material->id }}">

                            {{ $material->material_name }}
                            ({{ $material->unit }})

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Quantity Used
                </label>

                <input type="number"
                       step="0.01"
                       name="quantity_used[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>
<!-- Material Required -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Material Required
        </h2>

        <button type="button"
                onclick="addMaterialRequiredRow()"
                class="bg-red-600 text-white px-4 py-2 rounded">

            + Add Required Material

        </button>

    </div>

    <div id="material-required-container">

        <div class="grid grid-cols-6 gap-4 mb-4 material-required-row">

            <div>

                <label class="block mb-2">
                    Material
                </label>

                <select name="required_material_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Material
                    </option>

                    @foreach($materials as $material)

                        <option value="{{ $material->id }}">

                            {{ $material->material_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Required Qty
                </label>

                <input type="number"
                       step="0.01"
                       name="required_quantity[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Required Date
                </label>

                <input type="date"
                       name="required_date[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Priority
                </label>

                <select name="priority[]"
                        class="w-full border rounded p-2">

                    <option value="Normal">
                        Normal
                    </option>

                    <option value="Urgent">
                        Urgent
                    </option>

                    <option value="Critical">
                        Critical
                    </option>

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Reason
                </label>

                <input type="text"
                       name="reason[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Remarks
                </label>

                <input type="text"
                       name="required_remarks[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>
<!-- Machinery / Tools -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Machinery / Tools Used
        </h2>

        <button type="button"
                onclick="addMachineryRow()"
                class="bg-indigo-600 text-white px-4 py-2 rounded">

            + Add Machinery

        </button>

    </div>

    <div id="machinery-container">

        <div class="grid grid-cols-5 gap-4 mb-4 machinery-row">

            <div>

                <label class="block mb-2">
                    Machine
                </label>

                <select name="machinery_tool_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Machine
                    </option>

                    @foreach($machineries as $machine)

                        <option value="{{ $machine->id }}">

                            {{ $machine->machine_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Quantity
                </label>

                <input type="number"
                       name="machine_quantity[]"
                       value="1"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Usage Hours
                </label>

                <input type="number"
                       step="0.01"
                       name="usage_hours[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Working Condition
                </label>

                <select name="working_condition[]"
                        class="w-full border rounded p-2">

                    <option value="Working">
                        Working
                    </option>

                    <option value="Breakdown">
                        Breakdown
                    </option>

                    <option value="Maintenance">
                        Maintenance
                    </option>

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Remarks
                </label>

                <input type="text"
                       name="machine_remarks[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>



<!-- Issues / Delays -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Issues / Delays
        </h2>

        <button type="button"
                onclick="addIssueRow()"
                class="bg-red-700 text-white px-4 py-2 rounded">

            + Add Issue

        </button>

    </div>

    <div id="issue-container">

        <div class="grid grid-cols-7 gap-4 mb-4 issue-row">

            <div>

                <label class="block mb-2">
                    Issue Type
                </label>

                <input type="text"
                       name="issue_type[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Related Activity
                </label>

                <input type="text"
                       name="related_activity[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Description
                </label>

                <input type="text"
                       name="issue_description[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Responsible Person
                </label>

                <input type="text"
                       name="responsible_person[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Priority
                </label>

                <select name="issue_priority[]"
                        class="w-full border rounded p-2">

                    <option value="Low">
                        Low
                    </option>

                    <option value="Medium">
                        Medium
                    </option>

                    <option value="High">
                        High
                    </option>

                    <option value="Critical">
                        Critical
                    </option>

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Status
                </label>

                <select name="issue_status[]"
                        class="w-full border rounded p-2">

                    <option value="Open">
                        Open
                    </option>

                    <option value="In Progress">
                        In Progress
                    </option>

                    <option value="Resolved">
                        Resolved
                    </option>

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Remarks
                </label>

                <input type="text"
                       name="issue_remarks[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>

<!-- Tomorrow Plan -->

<div class="bg-white rounded shadow p-6 mb-6">

    <div class="flex justify-between items-center mb-6">

        <h2 class="text-2xl font-bold">
            Tomorrow Plan
        </h2>

        <button type="button"
                onclick="addTomorrowPlanRow()"
                class="bg-teal-600 text-white px-4 py-2 rounded">

            + Add Tomorrow Plan

        </button>

    </div>

    <div id="tomorrow-plan-container">

        <div class="grid grid-cols-7 gap-4 mb-4 tomorrow-plan-row">

            <div>

                <label class="block mb-2">
                    Activity
                </label>

                <select name="plan_activity_id[]"
                        class="w-full border rounded p-2">

                    <option value="">
                        Select Activity
                    </option>

                    @foreach($activities as $activity)

                        <option value="{{ $activity->id }}">

                            {{ $activity->activity_name }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div>

                <label class="block mb-2">
                    Planned Qty
                </label>

                <input type="number"
                       step="0.01"
                       name="planned_quantity[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Unit
                </label>

                <input type="text"
                       name="planned_unit[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Planned Labour
                </label>

                <input type="number"
                       name="planned_labour[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Materials Required
                </label>

                <input type="text"
                       name="planned_materials[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Machinery Required
                </label>

                <input type="text"
                       name="planned_machinery[]"
                       class="w-full border rounded p-2">

            </div>

            <div>

                <label class="block mb-2">
                    Risks / Constraints
                </label>

                <input type="text"
                       name="planned_risks[]"
                       class="w-full border rounded p-2">

            </div>

        </div>

    </div>

</div>

<!-- Site Photos -->
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
<script>

function addMaterialRow()
{
    let row = document.querySelector('.material-row');

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        input.value = '';

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById('material-container')
        .appendChild(clone);
}

</script>
<script>

function addMaterialReceivedRow()
{
    let row = document.querySelector(
        '.material-received-row'
    );

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        input.value = '';

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById(
            'material-received-container'
        )
        .appendChild(clone);
}

</script>
<script>

function addMaterialRequiredRow()
{
    let row = document.querySelector(
        '.material-required-row'
    );

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        input.value = '';

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById(
            'material-required-container'
        )
        .appendChild(clone);
}

</script>
<script>

function addMachineryRow()
{
    let row = document.querySelector(
        '.machinery-row'
    );

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        if(input.type === 'number')
        {
            input.value = '';
        }
        else
        {
            input.value = '';
        }

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById(
            'machinery-container'
        )
        .appendChild(clone);
}

</script>
<script>

function addIssueRow()
{
    let row = document.querySelector(
        '.issue-row'
    );

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        input.value = '';

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById(
            'issue-container'
        )
        .appendChild(clone);
}

</script>
<script>

function addTomorrowPlanRow()
{
    let row = document.querySelector(
        '.tomorrow-plan-row'
    );

    let clone = row.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => {

        input.value = '';

    });

    clone.querySelectorAll('select').forEach(select => {

        select.selectedIndex = 0;

    });

    document
        .getElementById(
            'tomorrow-plan-container'
        )
        .appendChild(clone);
}

</script>

@endsection