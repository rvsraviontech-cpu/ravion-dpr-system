@props([
    'title' => 'Work Activities',
    'subtitle' => null,
    'addLabel' => '+ Add Another Work Activity',
    'containerId' => 'ref-activity-container',
    'templateId' => 'ref-activity-template',
])

<div {{ $attributes->merge(['class' => 'space-y-5']) }}
     data-ref-repeater>

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">
                {{ $title }}
            </h2>

            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <button type="button"
                class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:py-2.5"
                data-ref-add-activity
                data-ref-template-id="{{ $templateId }}"
                data-ref-container-id="{{ $containerId }}">
            {{ $addLabel }}
        </button>
    </div>

    <div id="{{ $containerId }}"
         class="space-y-5"
         data-ref-activity-container>
        {{ $slot }}
    </div>

    @isset($template)
        <template id="{{ $templateId }}">
            {{ $template }}
        </template>
    @endisset

    <div class="flex justify-center">
        <button type="button"
                class="w-full rounded-lg border border-blue-300 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100 sm:w-auto sm:py-2.5"
                data-ref-add-activity
                data-ref-template-id="{{ $templateId }}"
                data-ref-container-id="{{ $containerId }}">
            {{ $addLabel }}
        </button>
    </div>
</div>
