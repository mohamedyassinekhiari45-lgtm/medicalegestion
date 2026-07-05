@extends('layouts.app')
@section('title', 'Mon tableau de bord')
@section('subtitle')
Dr {{ Auth::user()->prenom }} {{ Auth::user()->name }}
@stop
@section('content')
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-1">
        <div class="stat-card stat-card-primary">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <h5>{{ $patients_count }}</h5>
                <p>Patients suivis</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-2">
        <div class="stat-card stat-card-success">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <h5>{{ $rdv_aujourdhui->count() }}</h5>
                <p>Rendez-vous aujourd'hui</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="stat-card stat-card-info">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <h5>{{ $consultations_recentes->count() }}</h5>
                <p>Dernières consultations</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-calendar-day"></i> Rendez-vous aujourd'hui
                <span class="badge bg-primary float-end">{{ $rdv_aujourdhui->count() }}</span>
            </div>
            <div class="card-body">
                @if($rdv_aujourdhui->count() > 0)
                <div class="list-group">
                    @foreach($rdv_aujourdhui as $rdv)
                    @if($rdv->statut === 'en_cours')
                    <a href="{{ route('consultations.edit', $rdv->consultation) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ optional($rdv->patient)->prenom ?? 'N/A' }} {{ optional($rdv->patient)->nom ?? '' }}</strong>
                            <br><small class="text-muted">{{ $rdv->motif ?? 'Consultation générale' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-info rounded-pill">{{ substr($rdv->heure_rdv, 0, 5) }}</span>
                            <br><small class="text-muted">En cours</small>
                        </div>
                    </a>
                    @elseif($rdv->statut === 'confirme')
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <strong>{{ optional($rdv->patient)->prenom ?? 'N/A' }} {{ optional($rdv->patient)->nom ?? '' }}</strong>
                            <br><small class="text-muted">{{ $rdv->motif ?? 'Consultation générale' }}</small>
                        </div>
                        <div class="text-end d-flex gap-1 align-items-center">
                            <form action="{{ route('rendez-vous.unconfirm', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler la confirmation ?">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Annuler la confirmation"><i class="bi bi-x-lg"></i></button>
                            </form>
                            <form action="{{ route('consultations.start') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="patient_id" value="{{ $rdv->patient_id }}">
                                <input type="hidden" name="rendez_vous_id" value="{{ $rdv->id }}">
                                <button class="btn btn-sm btn-success"><i class="bi bi-play"></i> Démarrer</button>
                            </form>
                        </div>
                    </div>
                    @elseif($rdv->statut === 'planifie')
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <strong>{{ optional($rdv->patient)->prenom ?? 'N/A' }} {{ optional($rdv->patient)->nom ?? '' }}</strong>
                            <br><small class="text-muted">{{ $rdv->motif ?? 'Consultation générale' }}</small>
                        </div>
                        <div class="text-end d-flex gap-1 align-items-center">
                            <span class="badge bg-warning rounded-pill me-2">{{ substr($rdv->heure_rdv, 0, 5) }}</span>
                            <form action="{{ route('rendez-vous.confirm', $rdv) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-primary" title="Confirmer"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('rendez-vous.cancel', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
                                @csrf
                                <button class="btn btn-sm btn-danger" title="Annuler"><i class="bi bi-x-circle"></i></button>
                            </form>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                    <p class="mt-2">Aucun rendez-vous aujourd'hui</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Consultations récentes
                <span class="badge bg-info float-end">{{ $consultations_recentes->count() }}</span>
            </div>
            <div class="card-body">
                @if($consultations_recentes->count() > 0)
                <div class="list-group">
                    @foreach($consultations_recentes as $c)
                    <a href="{{ route('consultations.show', $c) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ optional($c->patient)->prenom ?? 'N/A' }} {{ optional($c->patient)->nom ?? '' }}</strong>
                            <br><small class="text-muted">{{ $c->diagnostic ? \Illuminate\Support\Str::limit($c->diagnostic, 40) : 'Aucun diagnostic' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">Terminée</span>
                            <br><small class="text-muted">{{ $c->date_consultation->format('d/m/Y') }}</small>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clipboard2-pulse" style="font-size: 2rem;"></i>
                    <p class="mt-2">Aucune consultation récente</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection