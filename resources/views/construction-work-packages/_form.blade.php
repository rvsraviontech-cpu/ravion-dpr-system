@php
    $isEdit = isset($constructionWorkPackage) && $constructionWorkPackage->exists;

    /*
    |--------------------------------------------------------------------------
    | Shared Create / Edit Context
    |--------------------------------------------------------------------------
    |
    | Create supplies:
    |   $parent
    |   $suggestedSortOrder
    |
    | Edit supplies:
    |   $constructionWorkPackage with its parent relationship loaded.
    |
    | Normalising them here allows this partial to be reused safely.
    |
    */

    $formParent = $isEdit
        ? $constructionWorkPackage->parent
        : ($parent ?? null);

    $selectedParentId = old(
        'parent_id',
        $isEdit
            ? $constructionWorkPackage->parent_id
            : $formParent?->id
    );

    $selectedActivityDivisionId = old(
        'activity_division_id',
        $isEdit
            ? $constructionWorkPackage->activity_division_id
            : $formParent?->activity_division_id
    );

    $selectedSortOrder = old(
        'sort_order',
        $isEdit
            ? $constructionWorkPackage->sort_order
            : ($suggestedSortOrder ?? 0)
    );

    $selectedStatus = (string) old(
        'is_active',
        $isEdit
            ? (int) $constructionWorkPackage->is_active
            : 1
    );

    $inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500';
@endphp

<div
    class="space-y-4"
    x-data="{
        parentId: @js((string) $selectedParentId),
        rootMode: @js(empty($selectedParentId))
    }"
>

    {{-- ============================================================
         VALIDATION ERRORS
    ============================================================ --}}
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">

            <div class="flex items-start gap-3">

                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    !
                </div>

                <div>
                    <p class="text-sm font-bold text-red-800">
                        Please correct the following information.
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

            </div>

        </div>
    @endif


    {{-- ============================================================
         MAIN FORM CARD
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3.5">

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-sm font-bold text-gray-900">
                        Work Package Information
                    </h2>

                    <p class="mt-0.5 text-xs text-gray-500">
                        Define a top-level Construction Group or a selectable Work Package beneath a group.
                    </p>
                </div>

                <div
                    class="mt-2 sm:mt-0"
                    x-show="rootMode"
                >
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                        Construction Group
                    </span>
                </div>

                <div
                    class="mt-2 sm:mt-0"
                    x-show="!rootMode"
                    x-cloak
                >
                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        Work Package
                    </span>
                </div>

            </div>

        </div>


        <div class="p-5">

            <div class="grid grid-cols-1 gap-x-5 gap-y-5 lg:grid-cols-12">

                {{-- Parent --}}
                <div class="lg:col-span-6">

                    <label for="parent_id" class="{{ $labelClass }}">
                        Parent Construction Group
                    </label>

                    <select
                        id="parent_id"
                        name="parent_id"
                        class="{{ $inputClass }}"
                        x-model="parentId"
                        @change="rootMode = parentId === ''"
                    >
                        <option value="">
                            None — Create as top-level Construction Group
                        </option>

                        @foreach($rootPackages as $root)
                            <option
                                value="{{ $root->id }}"
                                {{ (string) $selectedParentId === (string) $root->id ? 'selected' : '' }}
                            >
                                {{ $root->code }} — {{ $root->name }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1.5 text-xs text-gray-500">
                        Choose a group for normal Work Packages. Leave blank only when creating a new top-level construction group.
                    </p>

                </div>


                {{-- Code --}}
                <div class="lg:col-span-3">

                    <label for="code" class="{{ $labelClass }}">
                        Code <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code', $constructionWorkPackage->code ?? '') }}"
                        maxlength="50"
                        required
                        autocomplete="off"
                        placeholder="e.g. RCC-SLAB"
                        class="{{ $inputClass }} font-mono uppercase"
                    >

                    <p class="mt-1.5 text-xs text-gray-500">
                        Unique short code.
                    </p>

                </div>


                {{-- Sort Order --}}
                <div class="lg:col-span-3">

                    <label for="sort_order" class="{{ $labelClass }}">
                        Sort Order
                    </label>

                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="{{ $selectedSortOrder }}"
                        min="0"
                        step="1"
                        class="{{ $inputClass }}"
                    >

                    <p class="mt-1.5 text-xs text-gray-500">
                        Controls display sequence.
                    </p>

                </div>


                {{-- Name --}}
                <div class="lg:col-span-7">

                    <label for="name" class="{{ $labelClass }}">
                        Name <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $constructionWorkPackage->name ?? '') }}"
                        maxlength="255"
                        required
                        autocomplete="off"
                        placeholder="e.g. RCC Slab Work"
                        class="{{ $inputClass }}"
                    >

                </div>


                {{-- Activity Division --}}
                <div class="lg:col-span-5">

                    <label for="activity_division_id" class="{{ $labelClass }}">
                        Related Activity Division
                    </label>

                    <select
                        id="activity_division_id"
                        name="activity_division_id"
                        class="{{ $inputClass }}"
                    >
                        <option value="">
                            No direct Activity Division mapping
                        </option>

                        @foreach($activityDivisions as $division)
                            <option
                                value="{{ $division->id }}"
                                {{ (string) $selectedActivityDivisionId === (string) $division->id ? 'selected' : '' }}
                            >
                                @if(!empty($division->code))
                                    {{ $division->code }} —
                                @endif

                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1.5 text-xs text-gray-500">
                        Optional execution classification. Work Package and Activity Division remain separate concepts.
                    </p>

                </div>


                {{-- Status --}}
                <div class="lg:col-span-4">

                    <label for="is_active" class="{{ $labelClass }}">
                        Status
                    </label>

                    <select
                        id="is_active"
                        name="is_active"
                        class="{{ $inputClass }}"
                    >
                        <option
    value="1"
    {{ $selectedStatus === '1' ? 'selected' : '' }}
>
    Active
</option>

                        <option
    value="0"
    {{ $selectedStatus === '0' ? 'selected' : '' }}
>
    Inactive
</option>
                    </select>

                </div>


                {{-- Hierarchy Help --}}
                <div class="lg:col-span-8">

                    <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3">

                        <div class="flex items-start gap-3">

                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0 text-blue-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 16v-4M12 8h.01"/>
                            </svg>

                            <div class="text-xs leading-5 text-blue-900">

                                <p x-show="rootMode">
                                    You are creating a
                                    <strong>Construction Group</strong>.
                                    Groups organize related Work Packages and are not normally selected by engineers during material transactions.
                                </p>

                                <p x-show="!rootMode" x-cloak>
                                    You are creating a
                                    <strong>Work Package</strong>.
                                    This becomes a selectable intended-use option for Material Received, Material Consumed and related workflows.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Remarks --}}
                <div class="lg:col-span-12">

                    <label for="remarks" class="{{ $labelClass }}">
                        Remarks
                    </label>

                    <textarea
                        id="remarks"
                        name="remarks"
                        rows="3"
                        maxlength="5000"
                        placeholder="Optional description, usage notes or administrative remarks..."
                        class="{{ $inputClass }}"
                    >{{ old('remarks', $constructionWorkPackage->remarks ?? '') }}</textarea>

                </div>

            </div>

        </div>


        {{-- ========================================================
             FOOTER ACTIONS
        ========================================================= --}}
        <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('construction-work-packages.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                Cancel
            </a>

            <div class="flex flex-col gap-2 sm:flex-row">

                @if($isEdit)
                    <a
                        href="{{ route('construction-work-packages.show', $constructionWorkPackage) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        View Package
                    </a>
                @endif

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-[#10212F] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a3448] focus:outline-none focus:ring-2 focus:ring-[#10212F]/20"
                >
                    @if($isEdit)
                        Save Changes
                    @else
                        Create Work Package
                    @endif
                </button>

            </div>

        </div>

    </div>

</div>