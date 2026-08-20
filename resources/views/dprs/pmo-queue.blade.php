@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-full space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-blue-700">
                <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                PMO Review
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                DPR Review Queue
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Review submitted DPRs and complete the required PMO action.
            </p>
        </div>

        <div class="inline-flex w-fit items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Pending Review</div>
                <div class="text-xl font-bold leading-tight text-gray-900">{{ $dprs->count() }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Mobile / PWA --}}
    <div class="space-y-3 lg:hidden">
        @forelse($dprs as $dpr)
            <article x-data="{ action: null }"
                     class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate text-base font-bold text-gray-900">
                                {{ $dpr->project?->project_name ?? '-' }}
                            </h2>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ $dpr->user?->name ?? '-' }}
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                            {{ $dpr->status }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-y border-gray-100 py-3 text-sm">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">DPR Date</div>
                            <div class="mt-0.5 font-medium text-gray-800">
                                {{ $dpr->dpr_date ? \Carbon\Carbon::parse($dpr->dpr_date)->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Project</div>
                            <div class="mt-0.5 truncate font-medium text-gray-800">
                                {{ $dpr->project?->project_code ?? 'DPR #' . $dpr->id }}
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('dprs.show', $dpr) }}"
                       class="mt-4 flex min-h-11 w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Review Full DPR
                    </a>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button"
                                @click="action = action === 'approve' ? null : 'approve'"
                                class="min-h-11 rounded-xl border border-green-200 bg-green-50 px-3 py-2.5 text-sm font-semibold text-green-700">
                            Approve
                        </button>

                        <button type="button"
                                @click="action = action === 'reject' ? null : 'reject'"
                                class="min-h-11 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700">
                            Return
                        </button>
                    </div>

                    <div x-show="action === 'approve'" x-cloak class="mt-3 rounded-xl border border-green-200 bg-green-50 p-3">
                        <form action="{{ route('dprs.approve', $dpr->id) }}" method="POST">
                            @csrf
                            <label class="mb-1.5 block text-xs font-semibold text-green-800">
                                Approval remarks <span class="font-normal text-green-600">(optional)</span>
                            </label>
                            <textarea name="pmo_remarks"
                                      rows="3"
                                      placeholder="Add approval remarks..."
                                      class="w-full rounded-lg border border-green-200 bg-white p-3 text-base text-gray-800 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"></textarea>
                            <div class="mt-3 flex gap-2">
                                <button type="button"
                                        @click="action = null"
                                        class="min-h-11 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                    Cancel
                                </button>
                                <button type="submit"
                                        onclick="return confirm('Approve this DPR?')"
                                        class="min-h-11 flex-1 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                    Confirm Approval
                                </button>
                            </div>
                        </form>
                    </div>

                    <div x-show="action === 'reject'" x-cloak class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3">
                        <form action="{{ route('dprs.reject', $dpr->id) }}" method="POST">
                            @csrf
                            <label class="mb-1.5 block text-xs font-semibold text-red-800">
                                Correction / rejection remarks <span class="text-red-600">*</span>
                            </label>
                            <textarea name="pmo_remarks"
                                      rows="3"
                                      required
                                      placeholder="Explain what needs correction..."
                                      class="w-full rounded-lg border border-red-200 bg-white p-3 text-base text-gray-800 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                            <div class="mt-3 flex gap-2">
                                <button type="button"
                                        @click="action = null"
                                        class="min-h-11 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                                    Cancel
                                </button>
                                <button type="submit"
                                        onclick="return confirm('Return this DPR for correction?')"
                                        class="min-h-11 flex-1 rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                    Return DPR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-12 text-center shadow-sm">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-green-50 text-lg text-green-700">✓</div>
                <div class="mt-3 text-base font-semibold text-gray-900">DPR review queue is clear</div>
                <p class="mt-1 text-sm text-gray-500">New DPR submissions requiring PMO review will appear here.</p>
            </div>
        @endforelse

        @if(method_exists($dprs, 'hasPages') && $dprs->hasPages())
            <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-sm">
                {{ $dprs->links() }}
            </div>
        @endif
    </div>

    {{-- Desktop --}}
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:block">
        <div class="border-b border-gray-200 bg-gray-50/80 px-5 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Pending DPR Submissions</div>
                    <div class="mt-0.5 text-xs text-gray-500">Open a DPR to review details, then approve or return it for correction.</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600">
                    {{ $dprs->count() }} pending
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-fixed text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-white text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                        <th class="w-[28%] px-5 py-3 text-left">Project</th>
                        <th class="w-[18%] px-4 py-3 text-left">Engineer</th>
                        <th class="w-[16%] px-4 py-3 text-left">DPR Date</th>
                        <th class="w-[12%] px-4 py-3 text-left">Status</th>
                        <th class="w-[26%] px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($dprs as $dpr)
                        <tr x-data="{ action: null }" class="group align-middle hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $dpr->project?->project_name ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-gray-400">
                                    {{ $dpr->project?->project_code ?? 'DPR #' . $dpr->id }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-700">{{ $dpr->user?->name ?? '-' }}</div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="font-medium text-gray-800">
                                    {{ $dpr->dpr_date ? \Carbon\Carbon::parse($dpr->dpr_date)->format('d M Y') : '-' }}
                                </div>
                                <div class="mt-1 text-xs text-gray-400">
                                    {{ $dpr->dpr_date ? \Carbon\Carbon::parse($dpr->dpr_date)->format('l') : '' }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    {{ $dpr->status }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('dprs.show', $dpr) }}"
                                       class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        Review DPR
                                    </a>

                                    <button type="button"
                                            @click="action = action === 'approve' ? null : 'approve'"
                                            class="inline-flex h-9 items-center justify-center rounded-lg bg-green-600 px-3 text-xs font-semibold text-white shadow-sm hover:bg-green-700">
                                        Approve
                                    </button>

                                    <button type="button"
                                            @click="action = action === 'reject' ? null : 'reject'"
                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 hover:bg-red-50">
                                        Return
                                    </button>
                                </div>

                                <div x-show="action === 'approve'"
                                     x-cloak
                                     class="mt-3 rounded-xl border border-green-200 bg-green-50 p-3 text-left">
                                    <form action="{{ route('dprs.approve', $dpr->id) }}" method="POST">
                                        @csrf
                                        <div class="flex items-end gap-3">
                                            <div class="min-w-0 flex-1">
                                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-green-800">
                                                    Approval Remarks
                                                </label>
                                                <input type="text"
                                                       name="pmo_remarks"
                                                       placeholder="Optional remarks..."
                                                       class="h-9 w-full rounded-lg border border-green-200 bg-white px-3 text-sm text-gray-800 outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                                            </div>
                                            <button type="button"
                                                    @click="action = null"
                                                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-600">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    onclick="return confirm('Approve this DPR?')"
                                                    class="h-9 whitespace-nowrap rounded-lg bg-green-600 px-4 text-xs font-semibold text-white hover:bg-green-700">
                                                Confirm Approval
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div x-show="action === 'reject'"
                                     x-cloak
                                     class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-left">
                                    <form action="{{ route('dprs.reject', $dpr->id) }}" method="POST">
                                        @csrf
                                        <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-red-800">
                                            Correction / Rejection Remarks
                                        </label>
                                        <textarea name="pmo_remarks"
                                                  rows="2"
                                                  required
                                                  placeholder="Explain the correction required..."
                                                  class="w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button"
                                                    @click="action = null"
                                                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-600">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    onclick="return confirm('Return this DPR for correction?')"
                                                    class="h-9 rounded-lg bg-red-600 px-4 text-xs font-semibold text-white hover:bg-red-700">
                                                Return DPR
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-green-50 text-lg font-bold text-green-700">✓</div>
                                <div class="mt-3 font-semibold text-gray-900">DPR review queue is clear</div>
                                <div class="mt-1 text-sm text-gray-500">New pending DPRs will appear here automatically.</div>
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
