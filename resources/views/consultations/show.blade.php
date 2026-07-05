@extends('layouts.app')
@section('title', 'Consultation')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-clipboard2-pulse"></i> Consultation du {{ $consultation->date_consultation->format('d/m/Y') }}</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('consultations.edit', $consultation) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Modifier</a>
        @if(Auth::user()->role === 'medecin' && $consultation->statut === 'en_cours')
        <form method="POST" action="{{ route('consultations.update', $consultation) }}" class="d-inline" data-confirm="Terminer cette consultation ?">
            @csrf @method('PUT')
            <input type="hidden" name="action" value="terminer">
            <button class="btn btn-success"><i class="bi bi-check-circle"></i> Terminer la consultation</button>
        </form>
        @endif
        @if(in_array(Auth::user()->role, ['admin', 'receptionniste']) && $consultation->statut === 'termine' && !$consultation->facture)
        <form method="POST" action="{{ route('factures.generate', $consultation) }}" class="d-inline">
            @csrf
            <button class="btn btn-success"><i class="bi bi-receipt"></i> Générer la facture</button>
        </form>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-1">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person"></i> Patient</div>
            <div class="card-body">
                <h5>{{ $consultation->patient->prenom ?? 'Patient' }} {{ $consultation->patient->nom ?? 'supprimé' }}</h5>
                @if($consultation->patient)
                <p class="text-muted mb-0">N° dossier: <span class="badge bg-secondary">{{ $consultation->patient->numero_dossier }}</span></p>
                @endif
                @if(Auth::user()->role === 'medecin' && $consultation->patient)
                <a href="{{ route('dossiers-medicaux.show', $consultation->patient) }}" class="btn btn-outline-info btn-sm mt-2">
                    <i class="bi bi-folder2-open"></i> Dossier médical
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-2">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-badge"></i> Médecin</div>
            <div class="card-body">
                <h5>Dr {{ $consultation->medecin->prenom ?? 'Médecin' }} {{ $consultation->medecin->name ?? 'supprimé' }}</h5>
                <span class="badge bg-{{ $consultation->statut === 'termine' ? 'success' : 'warning' }}">{{ $consultation->statut }}</span>
            </div>
        </div>
    </div>
</div>

@if($consultation->constantes)
<div class="card mb-4 animate-fade-up animate-delay-3">
    <div class="card-header"><i class="bi bi-heart-pulse"></i> Constantes vitales</div>
    <div class="card-body">
        <div class="row text-center">
            @if(!empty($consultation->constantes['tension']))
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded">
                    <i class="bi bi-droplet-half text-danger" style="font-size:1.5rem;"></i>
                    <h5 class="mt-2 mb-0">{{ $consultation->constantes['tension'] }}</h5>
                    <small class="text-muted">Tension artérielle</small>
                </div>
            </div>
            @endif
            @if(!empty($consultation->constantes['pouls']))
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded">
                    <i class="bi bi-activity text-primary" style="font-size:1.5rem;"></i>
                    <h5 class="mt-2 mb-0">{{ $consultation->constantes['pouls'] }} <small>bpm</small></h5>
                    <small class="text-muted">Pouls</small>
                </div>
            </div>
            @endif
            @if(!empty($consultation->constantes['temperature']))
            <div class="col-md-4 mb-2">
                <div class="p-3 bg-light rounded">
                    <i class="bi bi-thermometer-half text-warning" style="font-size:1.5rem;"></i>
                    <h5 class="mt-2 mb-0">{{ $consultation->constantes['temperature'] }} °C</h5>
                    <small class="text-muted">Température</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-stethoscope"></i> Diagnostic</div>
            <div class="card-body">
                <p class="mb-0">{{ $consultation->diagnostic ?? 'Non renseigné' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-chat-dots"></i> Observations</div>
            <div class="card-body">
                <p class="mb-0">{{ $consultation->observations ?? 'Aucune observation' }}</p>
            </div>
        </div>
    </div>
</div>

@if($consultation->tarifs->count() > 0)
<div class="card mb-4 animate-fade-up">
    <div class="card-header"><i class="bi bi-receipt"></i> Prestations</div>
    <div class="card-body">
        <table class="table table-sm">
            <thead><tr><th>Code</th><th>Acte</th><th>Montant TTC</th></tr></thead>
            <tbody>
                @foreach($consultation->tarifs as $t)
                <tr>
                    <td><span class="badge bg-secondary">{{ $t->code_nomenclature }}</span></td>
                    <td>{{ $t->acte }}</td>
                    <td><strong>{{ number_format($t->montant_ttc, 2) }} DT</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($consultation->facture)
<div class="card animate-fade-up">
    <div class="card-header"><i class="bi bi-receipt"></i> Facture</div>
    <div class="card-body d-flex align-items-center gap-3">
        <a href="{{ route('factures.show', $consultation->facture) }}" class="btn btn-info">
            <i class="bi bi-eye"></i> Voir la facture #{{ $consultation->facture->numero_facture }}
        </a>
        <span class="badge bg-{{ $consultation->facture->statut_paiement === 'paye' ? 'success' : 'danger' }} fs-6">
            {{ $consultation->facture->statut_paiement }}
        </span>
    </div>
</div>
@endif
@endsection