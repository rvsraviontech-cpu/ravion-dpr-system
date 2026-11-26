@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-4 md:p-6 space-y-5">
    <div><h1 class="text-2xl font-bold">Payments & Milestones</h1><p class="text-sm text-gray-500">Client contractual payments only · Internal management</p></div>
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-gray-50"><tr><th class="p-3">Project</th><th class="p-3">Contract</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody>
    @forelse($projects as $project)
    @php($contract = $contracts->get($project->id))
    <tr class="border-t"><td class="p-3 font-medium">{{ $project->project_name }} <span class="text-gray-500">({{ $project->project_code }})</span></td><td class="p-3">{{ $contract ? '₹'.number_format($contract->agreed_amount,2) : 'Not configured' }}</td><td class="p-3">{{ $contract->status ?? '—' }}</td><td class="p-3"><a class="text-blue-700 underline" href="{{ route('client-control.admin.payments.show', $project->id) }}">Manage schedule</a></td></tr>
    @empty <tr><td class="p-4" colspan="4">No accessible projects found.</td></tr> @endforelse
    </tbody></table></div>
</div>
@endsection
