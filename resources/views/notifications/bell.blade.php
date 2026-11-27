@php
    $notificationUser = auth('web')->user();
    $unreadCount = $notificationUser && $notificationUser->isActive() ? $notificationUser->unreadNotifications()->count() : 0;
@endphp
@if($notificationUser && $notificationUser->isActive())
    <a href="{{ route('erp.notifications.index') }}" class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 shrink-0" aria-label="Notifications ({{ $unreadCount }} unread)" title="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 rounded-full bg-red-600 text-white text-xs px-1">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>
@endif
