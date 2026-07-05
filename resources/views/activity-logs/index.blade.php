@extends('layouts.app')
@section('title', "Journal d'activité")
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-activity"></i> Journal d'activité</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-2"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('activity-logs.xlsx', request()->only(Auth::user()->role === 'admin' ? ['action', 'user_id', 'role', 'date_debut', 'date_fin'] : ['action', 'date_debut', 'date_fin'])) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> XLSX</a>
    </div>
</div>
<div class="card animate-fade-up mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            @if(Auth::user()->role === 'admin')
            <div class="col-md-2 position-relative">
                <input type="text" id="user_search" class="form-control" placeholder="Rechercher un utilisateur..." value="{{ $selectedUser ? $selectedUser->prenom . ' ' . $selectedUser->name : '' }}" autocomplete="off">
                <input type="hidden" name="user_id" id="user_id" value="{{ request('user_id') }}">
                <div id="user_results" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;width:calc(100% - 12px);"></div>
            </div>
            <div class="col-md-1">
                <select name="role" class="form-select">
                    <option value="">Rôle</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="medecin" {{ request('role') == 'medecin' ? 'selected' : '' }}>Médecin</option>
                    <option value="receptionniste" {{ request('role') == 'receptionniste' ? 'selected' : '' }}>Récep.</option>
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">Toutes les actions</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Création</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Modification</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Suppression</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Connexion</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Déconnexion</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}" placeholder="Date début">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}" placeholder="Date fin">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card animate-fade-up">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        @if(Auth::user()->role === 'admin')
                        <th>Utilisateur</th>
                        <th>Statut</th>
                        @endif
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $enLigne = $log->user->last_seen_at && $log->user->last_seen_at->gt(now()->subMinutes(5));
                    @endphp
                    <tr>
                        <td class="small text-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        @if(Auth::user()->role === 'admin')
                        <td>
                            <span class="badge bg-{{ $log->user->role === 'admin' ? 'danger' : ($log->user->role === 'medecin' ? 'primary' : 'success') }} me-1">
                                {{ $log->user->role === 'admin' ? 'Admin' : ($log->user->role === 'medecin' ? 'Médecin' : 'Récep.') }}
                            </span>
                            {{ $log->user->prenom }} {{ $log->user->name }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $enLigne ? 'success' : 'secondary' }}">
                                <i class="bi bi-{{ $enLigne ? 'circle-fill' : 'circle' }} me-1"></i>
                                {{ $enLigne ? 'En ligne' : 'Hors ligne' }}
                            </span>
                        </td>
                        @endif
                        <td>
                            <span class="badge bg-{{ $log->action === 'create' ? 'success' : ($log->action === 'delete' ? 'danger' : ($log->action === 'login' ? 'info' : ($log->action === 'logout' ? 'secondary' : 'warning'))) }}">
                                {{ $log->action === 'create' ? 'Création' : ($log->action === 'delete' ? 'Suppression' : ($log->action === 'login' ? 'Connexion' : ($log->action === 'logout' ? 'Déconnexion' : 'Modification'))) }}
                            </span>
                        </td>
                        <td>{{ $log->description }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ Auth::user()->role === 'admin' ? 5 : 3 }}" class="text-center text-muted py-4">Aucune activité enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection

@push('scripts')
@if(Auth::user()->role === 'admin')
<script>
var users = @json($users);
var searchTimer;

$('#user_search').on('input', function() {
    clearTimeout(searchTimer);
    var q = $(this).val().toLowerCase();
    var $results = $('#user_results');

    if (q.length < 1) {
        $results.hide();
        $('#user_id').val('');
        return;
    }

    searchTimer = setTimeout(function() {
        var filtered = users.filter(function(u) {
            return (u.prenom + ' ' + u.name).toLowerCase().includes(q);
        });

        $results.empty();

        if (filtered.length === 0) {
            $results.append('<div class="list-group-item text-muted">Aucun utilisateur trouvé</div>');
        } else {
            filtered.forEach(function(u) {
                var role = u.role === 'admin' ? 'Admin' : (u.role === 'medecin' ? 'Médecin' : 'Récep.');
                var enLigne = u.last_seen_at && new Date(u.last_seen_at).getTime() > Date.now() - 300000;
                $results.append(
                    '<a href="#" class="list-group-item list-group-item-action" data-id="' + u.id + '" data-nom="' + u.prenom + ' ' + u.name + '">' +
                    '<strong>' + u.prenom + ' ' + u.name + '</strong> ' +
                    '<span class="badge bg-' + (enLigne ? 'success' : 'secondary') + '"><i class="bi bi-' + (enLigne ? 'circle-fill' : 'circle') + '"></i> ' + (enLigne ? 'En ligne' : 'Hors ligne') + '</span> ' +
                    '<small class="text-muted">(' + role + ')</small>' +
                    '</a>'
                );
            });
        }

        $results.show();
    }, 200);
});

$(document).on('click', '#user_results a', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var nom = $(this).data('nom');
    $('#user_id').val(id);
    $('#user_search').val(nom);
    $('#user_results').hide();
    $(this).closest('form').submit();
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('#user_search, #user_results').length) {
        $('#user_results').hide();
    }
});
</script>
@endif
@endpush
