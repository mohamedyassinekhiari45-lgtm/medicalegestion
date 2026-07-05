@extends('layouts.app')
@section('title', 'Rendez-vous')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-calendar-check"></i> Rendez-vous du {{ $rendezVous->date_rdv->format('d/m/Y') }}</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('rendez-vous.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
        @if(in_array(Auth::user()->role, ['admin', 'receptionniste']) && in_array($rendezVous->statut, ['planifie', 'confirme', 'en_cours']))
        <a href="{{ route('rendez-vous.edit', $rendezVous) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Modifier</a>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person"></i> Patient</div>
            <div class="card-body">
                @if($rendezVous->patient)
                <h5>{{ $rendezVous->patient->prenom }} {{ $rendezVous->patient->nom }}</h5>
                <p class="text-muted mb-0">N° dossier: <span class="badge bg-secondary">{{ $rendezVous->patient->numero_dossier }}</span></p>
                <p class="text-muted mb-0">Tél: {{ $rendezVous->patient->telephone ?? 'N/A' }}</p>
                @else
                <p class="text-muted">Patient supprimé</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-badge"></i> Médecin</div>
            <div class="card-body">
                <h5>Dr {{ $rendezVous->medecin->prenom ?? '' }} {{ $rendezVous->medecin->name ?? '' }}</h5>
                @if($rendezVous->medecin && $rendezVous->medecin->specialites->count() > 0)
                <p class="text-muted mb-0">{{ $rendezVous->medecin->specialites->pluck('libelle')->implode(', ') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock"></i> Date & Heure</div>
            <div class="card-body">
                <h5>{{ $rendezVous->date_rdv->format('l d/m/Y') }}</h5>
                <h5>{{ substr($rendezVous->heure_rdv, 0, 5) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle"></i> Statut</div>
            <div class="card-body">
                <span class="badge bg-{{ $rendezVous->statut === 'termine' ? 'success' : ($rendezVous->statut === 'annule' ? 'danger' : ($rendezVous->statut === 'confirme' ? 'primary' : ($rendezVous->statut === 'en_cours' ? 'info' : 'warning'))) }} fs-5">
                    {{ $rendezVous->statut }}
                </span>
            </div>
        </div>
    </div>
</div>
@if($rendezVous->motif)
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-chat-dots"></i> Motif</div>
    <div class="card-body">
        <p class="mb-0">{{ $rendezVous->motif }}</p>
    </div>
</div>
@endif

@if($rendezVous->consultation)
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clipboard2-pulse"></i> Consultation associée</div>
    <div class="card-body d-flex align-items-center gap-2">
        <a href="{{ route('consultations.show', $rendezVous->consultation) }}" class="btn btn-info">
            <i class="bi bi-eye"></i> Voir la consultation
        </a>
        <span class="badge bg-{{ $rendezVous->consultation->statut === 'termine' ? 'success' : 'warning' }}">
            {{ $rendezVous->consultation->statut }}
        </span>
    </div>
</div>
@endif

@if(Auth::user()->role === 'medecin' && $rendezVous->statut === 'planifie')
<form action="{{ route('rendez-vous.confirm', $rendezVous) }}" method="POST" class="d-inline">
    @csrf
    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Confirmer le rendez-vous</button>
</form>
<form action="{{ route('rendez-vous.cancel', $rendezVous) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
    @csrf
    <button class="btn btn-danger"><i class="bi bi-x-circle"></i> Annuler le rendez-vous</button>
</form>
@endif
@if(Auth::user()->role === 'medecin' && $rendezVous->statut === 'confirme')
<form action="{{ route('rendez-vous.unconfirm', $rendezVous) }}" method="POST" class="d-inline" data-confirm="Annuler la confirmation ?">
    @csrf
    <button class="btn btn-secondary"><i class="bi bi-x-lg"></i> Annuler la confirmation</button>
</form>
<form action="{{ route('rendez-vous.cancel', $rendezVous) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
    @csrf
    <button class="btn btn-danger"><i class="bi bi-x-circle"></i> Annuler le rendez-vous</button>
</form>
<form action="{{ route('consultations.start') }}" method="POST" class="d-inline">
    @csrf
    <input type="hidden" name="patient_id" value="{{ $rendezVous->patient_id }}">
    <input type="hidden" name="rendez_vous_id" value="{{ $rendezVous->id }}">
    <button class="btn btn-success"><i class="bi bi-play"></i> Démarrer la consultation</button>
</form>
@endif
@endsection
