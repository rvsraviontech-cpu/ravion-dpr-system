@extends('layouts.app')
@section('content')
<div class="p-4 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Notifications</h1>
        <form method="POST" action="{{ route('erp.notifications.read-all') }}">
            @csrf
            <button class="border rounded px-3 py-2 bg-white">Mark all as read</button>
        </form>
    </div>
    @forelse($notifications as $notification)
        <div class="bg-white border rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="font-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                <div class="text-sm text-gray-600">{{ $notification->data['ticket_number'] ?? '' }} · {{ $notification->created_at->format('d M Y H:i') }}</div>
                @if(!$notification->read_at)
                    <span class="text-xs font-semibold text-blue-700">Unread</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @if($notification->type === \App\Notifications\SupportTicketNotification::class)
                    <form method="POST" action="{{ route('erp.notifications.open', $notification->id) }}">
                        @csrf
                        <button class="rounded px-3 py-2 text-white" style="background:#0f766e">Open ticket</button>
                    </form>
                @endif
                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('erp.notifications.read', $notification->id) }}">
                        @csrf
                        <button class="border rounded px-3 py-2">Mark as read</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white border rounded-lg p-6">No notifications yet.</div>
    @endforelse
    {{ $notifications->links() }}
</div>
@endsection
