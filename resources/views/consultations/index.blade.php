@extends('layouts.app')
@section('title', 'Consultations')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-clipboard2-pulse"></i> Consultations</h4>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
</div>
<div class="card animate-fade-up">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                    <option value="facture" {{ request('statut') === 'facture' ? 'selected' : '' }}>Facturé</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Réinitialiser</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Diagnostic</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $c)
                    <tr>
                        <td>{{ $c->date_consultation->format('d/m/Y') }}</td>
                        <td><strong>{{ optional($c->patient)->prenom ?? 'N/A' }} {{ optional($c->patient)->nom ?? '' }}</strong></td>
                        <td>Dr {{ optional($c->medecin)->prenom ?? 'N/A' }} {{ optional($c->medecin)->name ?? '' }}</td>
                        <td>{{ $c->diagnostic ? \Illuminate\Support\Str::limit($c->diagnostic, 50) : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $c->statut === 'termine' ? 'success' : ($c->statut === 'facture' ? 'info' : 'warning') }}">
                                {{ $c->statut }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('consultations.show', $c) }}" class="btn btn-sm btn-info" title="Voir"><i class="bi bi-eye"></i></a>
                                @if($c->statut === 'en_cours' && (Auth::id() === $c->medecin_id || Auth::user()->role === 'admin'))
                                <form method="POST" action="{{ route('consultations.update', $c) }}" class="d-inline" data-confirm="Terminer cette consultation ?">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="action" value="terminer">
                                    <button type="submit" class="btn btn-sm btn-success" title="Terminer"><i class="bi bi-check-circle"></i></button>
                                </form>
                                @endif
                                @if(Auth::user()->role === 'receptionniste' && $c->statut === 'termine' && !$c->facture)
                                <form method="POST" action="{{ route('factures.generate', $c) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Générer facture"><i class="bi bi-receipt"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucune consultation trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $consultations->links() }}
        </div>
    </div>
</div>
@endsection