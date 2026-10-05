@extends('layouts.app')
@section('title', 'Facture')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i> Tableau de bord</a></h4>
</div>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card animate-fade-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt"></i> Facture #{{ $facture->numero_facture }}</span>
                <div>
                    <a href="{{ route('factures.pdf', $facture) }}" class="btn btn-sm btn-danger me-2">
                        <i class="bi bi-filetype-pdf"></i> PDF
                    </a>
                    <span class="badge bg-{{ $facture->statut_paiement === 'paye' ? 'success' : ($facture->statut_paiement === 'partiel' ? 'warning' : ($facture->statut_paiement === 'genere' ? 'secondary' : 'danger')) }} fs-6">{{ $facture->statut_paiement === 'genere' ? 'Générée' : $facture->statut_paiement }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6><i class="bi bi-person"></i> Patient</h6>
                        <p>{{ optional($facture->consultation->patient)->prenom ?? 'N/A' }} {{ optional($facture->consultation->patient)->nom ?? '' }}<br>
                        N° dossier: {{ optional($facture->consultation->patient)->numero_dossier ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6><i class="bi bi-person-badge"></i> Médecin traitant</h6>
                        <p>Dr {{ optional($facture->consultation->medecin)->prenom ?? 'N/A' }} {{ optional($facture->consultation->medecin)->name ?? '' }}</p>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> {{ $facture->created_at->format('d/m/Y H:i') }}<br>
                            <i class="bi bi-person-circle"></i> Généré par {{ $facture->generateur?->prenom ?? 'N/A' }} {{ $facture->generateur?->name ?? '' }}
                        </small>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead><tr><th>Désignation</th><th class="text-end">Montant</th></tr></thead>
                    <tbody>
                        @forelse($facture->consultation->tarifs as $t)
                        <tr><td>{{ $t->acte }}</td><td class="text-end">{{ number_format($t->montant_ttc, 2) }} DT</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-muted text-center">Aucune prestation détaillée</td></tr>
                        @endforelse
                        <tr class="table-active">
                            <td><strong>Total</strong></td>
                            <td class="text-end"><strong>{{ number_format($facture->montant_total, 2) }} DT</strong></td>
                        </tr>
                        <tr>
                            <td>Montant payé</td>
                            <td class="text-end">{{ number_format($facture->montant_paye, 2) }} DT</td>
                        </tr>
                        <tr>
                            <td>Reste à payer</td>
                            <td class="text-end"><strong class="{{ $facture->montant_total - $facture->montant_paye > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($facture->montant_total - $facture->montant_paye, 2) }} DT</strong></td>
                        </tr>
                    </tbody>
                </table>

                @if(Auth::user()->role === 'receptionniste' && !in_array($facture->statut_paiement, ['paye']))
                <hr>
                <h6><i class="bi bi-cash-coin"></i> Enregistrer un paiement</h6>
                <form method="POST" action="{{ route('factures.paiement', $facture) }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <input type="number" step="0.01" name="montant" class="form-control" placeholder="Montant" required max="{{ $facture->montant_total - $facture->montant_paye }}">
                    </div>
                    <div class="col-md-4">
                        <select name="mode_reglement" class="form-select" required>
                            <option value="">Mode de paiement</option>
                            <option value="especes">Espèces</option>
                            <option value="cheque">Chèque</option>
                            <option value="carte_bancaire">Carte bancaire</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="reference" class="form-control" placeholder="Réf.">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success w-100"><i class="bi bi-check-lg"></i></button>
                    </div>
                </form>
                @endif

                @if($facture->paiements->count() > 0)
                <hr>
                <h6><i class="bi bi-clock-history"></i> Historique des paiements</h6>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th><th>Encaissé par</th></tr></thead>
                    <tbody>
                        @foreach($facture->paiements as $p)
                        <tr>
                            <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                            <td><strong>{{ number_format($p->montant, 2) }} DT</strong></td>
                            <td><span class="badge bg-info">{{ $p->mode_reglement }}</span></td>
                            <td>{{ $p->reference ?? '-' }}</td>
                            <td>{{ optional($p->encaisseur)->prenom ?? 'N/A' }} {{ optional($p->encaisseur)->name ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end">
                    <strong>Total payé : {{ number_format($facture->paiements->sum('montant'), 2) }} DT</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection