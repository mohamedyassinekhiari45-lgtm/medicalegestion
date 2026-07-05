@extends('layouts.app')
@section('title', 'Accueil')
@section('subtitle', 'Tableau de bord - Réception')
@section('content')
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-1">
        <div class="stat-card stat-card-primary">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <h5>{{ $rdv_aujourdhui }}</h5>
                <p>Rendez-vous aujourd'hui</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-2">
        <div class="stat-card stat-card-success">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                <h5>{{ $patients_aujourdhui }}</h5>
                <p>Nouveaux patients aujourd'hui</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="stat-card stat-card-warning">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-list-check"></i></div>
                <h5>{{ $prochains_rdv->count() }}</h5>
                <p>Prochains rendez-vous</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5 mb-4 animate-fade-up animate-delay-3">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightning-charge"></i> Actions rapides</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('patients.create') }}" class="btn btn-primary btn-lg text-start">
                        <i class="bi bi-person-plus"></i> Nouveau patient
                    </a>
                    <a href="{{ route('rendez-vous.create') }}" class="btn btn-success btn-lg text-start">
                        <i class="bi bi-calendar-plus"></i> Nouveau rendez-vous
                    </a>
                    <a href="{{ route('factures.create') }}" class="btn btn-info btn-lg text-start">
                        <i class="bi bi-receipt"></i> Générer une facture
                    </a>
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-lg text-start">
                        <i class="bi bi-search"></i> Rechercher un patient
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4 animate-fade-up animate-delay-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-list-check"></i> Prochains rendez-vous
                <span class="badge bg-warning float-end">{{ $prochains_rdv->count() }}</span>
            </div>
            <div class="card-body">
                @if($prochains_rdv->count() > 0)
                <div class="list-group">
                    @foreach($prochains_rdv as $rdv)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ optional($rdv->patient)->prenom ?? 'N/A' }} {{ optional($rdv->patient)->nom ?? '' }}</strong>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-person-badge"></i> Dr {{ optional($rdv->medecin)->prenom ?? 'N/A' }} {{ optional($rdv->medecin)->name ?? '' }}
                                @if($rdv->motif)
                                &middot; {{ $rdv->motif }}
                                @endif
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $rdv->statut === 'en_cours' ? 'info' : 'primary' }} rounded-pill">
                                {{ $rdv->date_rdv->format('d/m') }}
                                {{ substr($rdv->heure_rdv, 0, 5) }}
                            </span>
                            <br><small class="text-muted">{{ $rdv->statut === 'en_cours' ? 'En cours' : ($rdv->statut === 'confirme' ? 'Confirmé' : 'Planifié') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-plus" style="font-size: 2rem;"></i>
                    <p class="mt-2">Aucun rendez-vous à venir</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection