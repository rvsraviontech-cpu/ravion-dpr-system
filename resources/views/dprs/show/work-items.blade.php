<div class="rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="border-b border-gray-200 px-6 py-4">

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Work Progress Details
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Activities and quantities completed at the project site.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                {{ $dpr->workItems->count() }}
                {{ $dpr->workItems->count() === 1 ? 'Entry' : 'Entries' }}
            </span>

        </div>

    </div>

    @if($dpr->workItems->isNotEmpty())

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            #
                        </th>

                        <th
                            scope="col"
                            class="min-w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Division
                        </th>

                        <th
                            scope="col"
                            class="min-w-64 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Activity
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Block
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Floor
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Unit
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Room
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Sub-space
                        </th>

                        <th
                            scope="col"
                            class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Completed Qty
                        </th>

                        <th
                            scope="col"
                            class="min-w-56 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600"
                        >
                            Remarks
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @foreach($dpr->workItems as $item)

                        <tr class="transition hover:bg-gray-50">

                            <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                {{ $item->activityMapping?->division?->name ?? '-' }}
                            </td>

                            <td class="px-4 py-4 text-sm">

                                <p class="font-semibold text-gray-900">
                                    {{
                                        $item->activityMapping?->activity_name
                                        ?? $item->activity?->activity_name
                                        ?? '-'
                                    }}
                                </p>

                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $item->block?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $item->floor?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $item->unit?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $item->room?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700">
                                {{ $item->subspace?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm">

                                <span class="font-semibold text-gray-900">
                                    {{ number_format((float) $item->quantity_completed, 2) }}
                                </span>

                                @if($item->activityMapping?->unit)

                                    <span class="ml-1 text-gray-500">
                                        {{ $item->activityMapping->unit }}
                                    </span>

                                @endif

                            </td>

                            <td class="px-4 py-4 text-sm text-gray-700">
                                <span class="whitespace-pre-line">
                                    {{ $item->remarks ?: '-' }}
                                </span>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="px-6 py-12 text-center">

            <p class="text-sm font-medium text-gray-500">
                No work progress entries are available for this DPR.
            </p>

        </div>

    @endif

</div>