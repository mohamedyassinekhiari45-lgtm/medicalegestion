@forelse($notifications as $notification)
<a href="{{ $notification->data['url'] ?? '#' }}" class="notif-item d-block text-decoration-none" onclick="event.preventDefault(); fetch('{{ route('notifications.mark-read', $notification->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(() => window.location='{{ $notification->data['url'] ?? '#' }}');">
    <div class="d-flex align-items-start gap-2">
        <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }} mt-1" style="color: var(--primary);"></i>
        <div class="flex-grow-1" style="min-width: 0;">
            <strong class="d-block small text-dark">{{ $notification->data['title'] ?? '' }}</strong>
            <span class="small text-secondary">{{ Str::limit($notification->data['message'] ?? '', 60) }}</span>
            <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
        </div>
    </div>
</a>
@empty
<div class="text-center py-3" style="font-size: 0.85rem; color: var(--text-muted);"><i class="bi bi-bell-slash"></i> Aucune notification</div>
@endforelse
