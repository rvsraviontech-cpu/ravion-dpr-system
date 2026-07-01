@props([
    'name',
    'label' => null,
    'items' => [],
    'selected' => [],
    'valueKey' => 'id',
    'labelKey' => 'name',
    'placeholder' => 'Select options',
])

@php
    $selected = collect($selected)->map(fn($v) => (string) $v)->toArray();

    $options = collect($items)->map(function ($item) use ($valueKey, $labelKey) {
        return [
            'value' => (string) data_get($item, $valueKey),
            'label' => data_get($item, $labelKey),
        ];
    })->values();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: @js($selected),
        options: @js($options),

        toggle(value) {
            value = String(value);
            this.selected = this.selected.includes(value)
                ? this.selected.filter(item => item !== value)
                : [...this.selected, value];
        },

        isSelected(value) {
            return this.selected.includes(String(value));
        },

        selectedOptions() {
            return this.options.filter(option => this.selected.includes(String(option.value)));
        },

        filteredOptions() {
            return this.options.filter(option =>
                option.label.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
    class="relative"
>
    @if($label)
        <label class="mb-1 block text-sm font-semibold text-gray-700">
            {{ $label }}
        </label>
    @endif

    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm"
    >
        <span x-show="selected.length === 0" class="text-gray-400">
            {{ $placeholder }}
        </span>

        <span x-show="selected.length > 0" class="text-gray-700">
            <span x-text="selected.length"></span> selected
        </span>

        <span class="text-gray-400">▾</span>
    </button>

    <div x-show="selected.length > 0" class="mt-2 flex flex-wrap gap-1">
        <template x-for="option in selectedOptions()" :key="option.value">
            <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                <span x-text="option.label"></span>
            </span>
        </template>
    </div>

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="absolute z-50 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
    >
        <div class="border-b p-2">
            <input
                type="text"
                x-model="search"
                placeholder="Search..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
            >
        </div>

        <div class="p-2 space-y-1">
            <template x-for="option in filteredOptions()" :key="option.value">
                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-gray-50">
                    <input
                        type="checkbox"
                        :checked="isSelected(option.value)"
                        @change="toggle(option.value)"
                        class="rounded border-gray-300"
                    >

                    <span x-text="option.label"></span>
                </label>
            </template>
        </div>
    </div>

    <template x-for="value in selected" :key="value">
        <input type="hidden" name="{{ $name }}[]" :value="value">
    </template>
</div>