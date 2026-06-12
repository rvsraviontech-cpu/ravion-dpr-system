@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            PMO DPR Review Queue
        </h1>

        <p class="text-gray-500 mt-1">
            Review, approve or reject pending DPR submissions.
        </p>
    </div>
</div>

@if(session('success'))
<div class="bg-green-100 text-green-800 p-4 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded shadow overflow-hidden">

<table class="w-full">

    <thead class="bg-gray-100">

        <tr>
            <th class="p-4 text-left">Project</th>
            <th class="p-4 text-left">Engineer</th>
            <th class="p-4 text-left">DPR Date</th>
            <th class="p-4 text-left">Status</th>
            <th class="p-4 text-left">Actions</th>
        </tr>

    </thead>

    <tbody>

    @forelse($dprs as $dpr)

        <tr class="border-t">

            <td class="p-4">
                {{ $dpr->project->project_name ?? '-' }}
            </td>

            <td class="p-4">
                {{ $dpr->user->name ?? '-' }}
            </td>

            <td class="p-4">
                {{ $dpr->dpr_date }}
            </td>

            <td class="p-4">

                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded">
                    {{ $dpr->status }}
                </span>

            </td>

            <td class="p-4">

                <div class="space-y-3">

                    <a href="{{ route('dprs.show', $dpr->id) }}"
                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded">
                        View DPR
                    </a>

                    <form action="/dprs/{{ $dpr->id }}/approve"
                          method="POST">

                        @csrf

                        <textarea
                            name="pmo_remarks"
                            rows="2"
                            placeholder="Approval remarks..."
                            class="border rounded w-full p-2 mb-2"></textarea>

                        <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded">
                            Approve
                        </button>

                    </form>

                    <form action="/dprs/{{ $dpr->id }}/reject"
                          method="POST">

                        @csrf

                        <textarea
                            name="pmo_remarks"
                            rows="2"
                            placeholder="Rejection remarks..."
                            class="border rounded w-full p-2 mb-2"></textarea>

                        <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded">
                            Reject
                        </button>

                    </form>

                </div>

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="5" class="p-6 text-center text-gray-500">
                No DPRs pending PMO review.
            </td>

        </tr>

    @endforelse

    </tbody>

</table>

</div>

@endsection