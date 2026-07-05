@extends('layouts.app')
@section('title', 'Factures')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-receipt"></i> Factures</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        @if(in_array(Auth::user()->role, ['admin', 'receptionniste']))
        <a href="{{ route('factures.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nouvelle facture</a>
        @endif
    </div>
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
                    <option value="">Tous</option>
                    <option value="genere" {{ request('statut') === 'genere' ? 'selected' : '' }}>Générée</option>
                    <option value="impaye" {{ request('statut') === 'impaye' ? 'selected' : '' }}>Impayé</option>
                    <option value="partiel" {{ request('statut') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                    <option value="paye" {{ request('statut') === 'paye' ? 'selected' : '' }}>Payé</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('factures.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Réinitialiser</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>N° Facture</th><th>Patient</th><th>Médecin</th><th>Date</th><th>Total</th><th>Payé</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($factures as $f)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $f->numero_facture }}</span></td>
                        <td><strong>{{ optional($f->consultation?->patient)->prenom ?? 'N/A' }} {{ optional($f->consultation?->patient)->nom ?? '' }}</strong></td>
                        <td>{{ $f->consultation?->medecin ? 'Dr ' . $f->consultation->medecin->prenom . ' ' . $f->consultation->medecin->name : '-' }}</td>
                        <td>{{ $f->created_at->format('d/m/Y') }}</td>
                        <td>{{ number_format($f->montant_total, 2) }} DT</td>
                        <td>{{ number_format($f->montant_paye, 2) }} DT</td>
                        <td>
                            <span class="badge bg-{{ $f->statut_paiement === 'paye' ? 'success' : ($f->statut_paiement === 'partiel' ? 'warning' : ($f->statut_paiement === 'genere' ? 'secondary' : 'danger')) }}">
                                {{ $f->statut_paiement === 'genere' ? 'Générée' : $f->statut_paiement }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('factures.show', $f) }}" class="btn btn-sm btn-info" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('factures.pdf', $f) }}" class="btn btn-sm btn-danger" title="PDF"><i class="bi bi-filetype-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">Aucune facture trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $factures->links() }}
        </div>
    </div>
</div>
@endsection