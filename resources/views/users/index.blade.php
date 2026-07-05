@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-badge"></i> {{ request('role') === 'medecin' ? 'Médecins' : (request('role') === 'receptionniste' ? 'Réceptionnistes' : 'Utilisateurs') }}</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('users.pdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
        @if(request('role'))
            @if(request('role') !== 'admin' || Auth::id() === 1)
        <a href="{{ route('users.create-with-role', request('role')) }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nouveau {{ request('role') === 'medecin' ? 'Médecin' : (request('role') === 'receptionniste' ? 'Réceptionniste' : '') }}</a>
            @endif
        @else
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nouvel utilisateur</a>
        @endif
    </div>
</div>
<div class="card animate-fade-up">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="hidden" name="role" value="{{ request('role') }}">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, prénom ou email..." value="{{ request('search') }}">
                <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                @if(request('search') || request('role'))
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Spécialité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            @if($user->avatar)
                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            @else
                            <span class="badge bg-secondary rounded-circle p-2" style="width: 32px; height: 32px;">{{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->prenom }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'medecin' ? 'primary' : 'success') }}">
                                <i class="bi bi-{{ $user->role === 'admin' ? 'shield-lock' : ($user->role === 'medecin' ? 'person-badge' : 'person') }}"></i>
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>
                            @if($user->role === 'medecin' && $user->specialites->count() > 0)
                                @foreach($user->specialites as $s)
                                <span class="badge bg-info">{{ $s->libelle }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->statut ? 'success' : 'secondary' }}">
                                {{ $user->statut ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                                @if($user->id !== Auth::id())
                                @if($user->statut)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" data-confirm="Désactiver cet utilisateur ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-secondary" title="Désactiver"><i class="bi bi-person-x"></i></button>
                                </form>
                                @else
                                <form action="{{ route('users.activate', $user) }}" method="POST" class="d-inline" data-confirm="Activer cet utilisateur ?">
                                    @csrf @method('PUT')
                                    <button class="btn btn-sm btn-success" title="Activer"><i class="bi bi-person-check"></i></button>
                                </form>
                                @endif
                                <form action="{{ route('users.force-destroy', $user) }}" method="POST" class="d-inline" data-confirm="Supprimer définitivement cet utilisateur ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Supprimer définitivement"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection