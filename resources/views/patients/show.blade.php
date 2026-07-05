@extends('layouts.app')
@section('title', 'Fiche Patient')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-fill"></i> {{ $patient->prenom }} {{ $patient->nom }}</h4>
    <div>
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary me-1"><i class="bi bi-arrow-left"></i> Retour</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        @if(Auth::user()->role !== 'medecin')
        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Modifier</a>
        @endif
        @if(Auth::user()->role === 'medecin')
        <a href="{{ route('dossiers-medicaux.show', $patient) }}" class="btn btn-info text-white"><i class="bi bi-folder2-open"></i> Dossier médical</a>
        @endif
        @if(Auth::user()->role === 'receptionniste')
        <a href="{{ route('rendez-vous.create', ['patient_id' => $patient->id]) }}" class="btn btn-success"><i class="bi bi-calendar-plus"></i> Rendez-vous</a>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-4 animate-fade-up animate-delay-1">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle"></i> Informations personnelles</div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td class="fw-bold text-muted">N° Dossier</td><td><span class="badge bg-secondary">{{ $patient->numero_dossier }}</span></td></tr>
                    <tr><td class="fw-bold text-muted">Email</td><td>{{ $patient->email ?? '-' }}</td></tr>
                    <tr><td class="fw-bold text-muted">Téléphone</td><td>{{ $patient->telephone ?? '-' }}</td></tr>
                    <tr><td class="fw-bold text-muted">Adresse</td><td>{{ $patient->adresse ?? '-' }}</td></tr>
                    <tr><td class="fw-bold text-muted">Date naissance</td><td>{{ $patient->date_naissance?->format('d/m/Y') ?? '-' }}</td></tr>
                    <tr><td class="fw-bold text-muted">Sexe</td><td>{{ $patient->sexe === 'M' ? 'Masculin' : ($patient->sexe === 'F' ? 'Féminin' : '-') }}</td></tr>
                    <tr><td class="fw-bold text-muted">Inscrit le</td><td>{{ $patient->created_at->format('d/m/Y H:i') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-4 animate-fade-up animate-delay-2">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-calendar-check"></i> Derniers rendez-vous <span class="badge bg-primary float-end">{{ $patient->rendezVous->count() }}</span></div>
            <div class="card-body">
                @if($patient->rendezVous->count() > 0)
                <div class="list-group">
                    @foreach($patient->rendezVous->take(5) as $rdv)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $rdv->date_rdv->format('d/m/Y') }} {{ substr($rdv->heure_rdv, 0, 5) }}</strong>
                            <br><small class="text-muted">Dr {{ optional($rdv->medecin)->prenom ?? 'N/A' }} {{ optional($rdv->medecin)->name ?? '' }} @if($rdv->medecin && $rdv->medecin->specialites->isNotEmpty())<span class="badge bg-info ms-1">{{ $rdv->medecin->specialites->pluck('libelle')->implode(', ') }}</span>@endif</small>
                        </div>
                        <span class="badge bg-{{ $rdv->statut === 'termine' ? 'success' : ($rdv->statut === 'annule' ? 'danger' : 'warning') }}">{{ $rdv->statut }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3 text-muted"><i class="bi bi-calendar"></i> Aucun rendez-vous</div>
                @endif
            </div>
        </div>
        @if(Auth::user()->role === 'medecin')
        <div class="card animate-fade-up animate-delay-3">
            <div class="card-header"><i class="bi bi-clipboard2-pulse"></i> Dernières consultations <span class="badge bg-info float-end">{{ $patient->consultations->count() }}</span></div>
            <div class="card-body">
                @if($patient->consultations->count() > 0)
                <div class="list-group">
                    @foreach($patient->consultations->take(5) as $c)
                    <a href="{{ route('consultations.show', $c) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $c->date_consultation->format('d/m/Y') }}</strong>
                            <br><small class="text-muted">Dr {{ $c->medecin->prenom }} {{ $c->medecin->name }} @if($c->medecin->specialites->isNotEmpty())<span class="badge bg-info ms-1">{{ $c->medecin->specialites->pluck('libelle')->implode(', ') }}</span>@endif</small>
                        </div>
                        <span class="badge bg-{{ $c->statut === 'termine' ? 'success' : 'warning' }}">{{ $c->statut }}</span>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3 text-muted"><i class="bi bi-clipboard2"></i> Aucune consultation</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection