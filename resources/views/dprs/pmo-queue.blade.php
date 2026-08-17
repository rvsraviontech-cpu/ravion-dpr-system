@extends('layouts.app')

@section('content')

<div class="mx-auto max-w-full">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">
            PMO DPR Review Queue
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Review pending DPR submissions and take approval action.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Mobile Review Cards --}}
    <div class="space-y-4 lg:hidden">
        @forelse($dprs as $dpr)
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="bg-[#0F2A52] px-4 py-4 text-white">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-blue-100">
                                {{ $dpr->dpr_date }}
                            </div>

                            <h2 class="mt-1 truncate text-base font-bold">
                                {{ $dpr->project?->project_name ?? '-' }}
                            </h2>

                            <div class="mt-1 truncate text-xs text-blue-100">
                                {{ $dpr->user?->name ?? '-' }}
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full bg-yellow-100 px-2.5 py-1 text-[11px] font-bold text-yellow-800">
                            {{ $dpr->status }}
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <a href="{{ route('dprs.show', $dpr) }}"
                       class="block w-full rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white">
                        Review Full DPR
                    </a>

                    <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-3">
                        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-green-800">
                            Approve DPR
                        </div>

                        <form action="{{ url('/dprs/' . $dpr->id . '/approve') }}" method="POST">
                            @csrf

                            <textarea name="pmo_remarks"
                                      rows="3"
                                      placeholder="Approval remarks (optional)"
                                      class="w-full rounded-lg border border-green-200 bg-white p-3 text-base focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100"></textarea>

                            <button type="submit"
                                    onclick="return confirm('Approve this DPR?')"
                                    class="mt-3 w-full rounded-xl bg-green-600 px-4 py-3 font-semibold text-white">
                                Approve DPR
                            </button>
                        </form>
                    </div>

                    <details class="mt-3 rounded-xl border border-red-200 bg-red-50">
                        <summary class="cursor-pointer px-4 py-3 text-center text-sm font-semibold text-red-700">
                            Return / Reject DPR
                        </summary>

                        <form action="{{ url('/dprs/' . $dpr->id . '/reject') }}"
                              method="POST"
                              class="border-t border-red-200 p-3">
                            @csrf

                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-red-800">
                                Correction / Rejection Remarks
                            </label>

                            <textarea name="pmo_remarks"
                                      rows="3"
                                      placeholder="Explain what needs correction..."
                                      class="w-full rounded-lg border border-red-200 bg-white p-3 text-base focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100"></textarea>

                            <button type="submit"
                                    onclick="return confirm('Return/reject this DPR?')"
                                    class="mt-3 w-full rounded-xl bg-red-600 px-4 py-3 font-semibold text-white">
                                Return / Reject DPR
                            </button>
                        </form>
                    </details>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-12 text-center shadow-sm">
                <div class="text-base font-semibold text-gray-700">
                    No DPRs pending PMO review
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    Submitted DPRs requiring review will appear here.
                </p>
            </div>
        @endforelse

        @if(method_exists($dprs, 'hasPages') && $dprs->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $dprs->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop Review Table --}}
    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="p-4 text-left">Project</th>
                        <th class="p-4 text-left">Engineer</th>
                        <th class="p-4 text-left">DPR Date</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="min-w-[380px] p-4 text-left">Review Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($dprs as $dpr)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="p-4 font-medium text-gray-800">
                                {{ $dpr->project?->project_name ?? '-' }}
                            </td>

                            <td class="p-4">
                                {{ $dpr->user?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap p-4">
                                {{ $dpr->dpr_date }}
                            </td>

                            <td class="p-4">
                                <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">
                                    {{ $dpr->status }}
                                </span>
                            </td>

                            <td class="p-4">
                                <div class="mb-3">
                                    <a href="{{ route('dprs.show', $dpr) }}"
                                       class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                        Review Full DPR
                                    </a>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <form action="{{ url('/dprs/' . $dpr->id . '/approve') }}"
                                          method="POST"
                                          class="rounded-lg border border-green-200 bg-green-50 p-3">
                                        @csrf

                                        <textarea name="pmo_remarks"
                                                  rows="2"
                                                  placeholder="Approval remarks..."
                                                  class="w-full rounded-lg border border-green-200 bg-white p-2 text-sm"></textarea>

                                        <button type="submit"
                                                onclick="return confirm('Approve this DPR?')"
                                                class="mt-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">
                                            Approve
                                        </button>
                                    </form>

                                    <form action="{{ url('/dprs/' . $dpr->id . '/reject') }}"
                                          method="POST"
                                          class="rounded-lg border border-red-200 bg-red-50 p-3">
                                        @csrf

                                        <textarea name="pmo_remarks"
                                                  rows="2"
                                                  placeholder="Correction / rejection remarks..."
                                                  class="w-full rounded-lg border border-red-200 bg-white p-2 text-sm"></textarea>

                                        <button type="submit"
                                                onclick="return confirm('Return/reject this DPR?')"
                                                class="mt-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white">
                                            Return / Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                No DPRs pending PMO review.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($dprs, 'hasPages') && $dprs->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">
                {{ $dprs->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
