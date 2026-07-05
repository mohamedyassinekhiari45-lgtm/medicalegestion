@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-bell"></i> Notifications</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        @if(Auth::user()->notifications->count() > 0)
        <form method="POST" action="{{ route('notifications.destroy-all') }}" class="d-inline" data-confirm="Supprimer toutes les notifications ?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Tout supprimer</button>
        </form>
        @endif
        @if(Auth::user()->unreadNotifications->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline" data-confirm="Marquer toutes les notifications comme lues ?">
            @csrf
            <button class="btn btn-outline-secondary"><i class="bi bi-check-all"></i> Tout marquer comme lu</button>
        </form>
        @endif
    </div>
</div>
<div class="card animate-fade-up">
    <div class="card-body">
        @forelse($notifications as $notification)
        <div class="d-flex align-items-start p-3 {{ $notification->read_at ? 'text-muted' : 'bg-light' }} rounded mb-2 border-start border-4 border-{{ $notification->read_at ? 'secondary' : 'primary' }}">
            <div class="me-3 fs-4">
                <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                <div>
                    <a href="{{ $notification->data['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary me-1" onclick="event.preventDefault(); fetch('{{ route('notifications.mark-read', $notification->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(() => window.location='{{ $notification->data['url'] ?? '#' }}');">
                        <i class="bi bi-eye"></i> Voir
                    </a>
                    @if(!$notification->read_at)
                    <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check"></i> Marquer comme lu</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="d-inline" data-confirm="Supprimer cette notification ?">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="text-center text-muted my-5"><i class="bi bi-bell-slash fs-1 d-block mb-2"></i>Aucune notification</p>
        @endforelse
        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
