@extends('layouts.app')
@section('title', 'Rendez-vous')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-calendar-check"></i> Rendez-vous</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-2"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('rendez-vous.calendar') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-calendar3"></i> Calendrier
        </a>
        <a href="{{ route('rendez-vous.pdf', request()->only(['date_debut', 'date_fin', 'statut', 'medecin_id'])) }}" class="btn btn-outline-danger me-2">
            <i class="bi bi-filetype-pdf"></i> PDF
        </a>
        <a href="{{ route('rendez-vous.xlsx', request()->only(['date_debut', 'date_fin', 'statut', 'medecin_id'])) }}" class="btn btn-outline-success me-2">
            <i class="bi bi-file-earmark-excel"></i> XLSX
        </a>
        @if(in_array(Auth::user()->role, ['admin', 'receptionniste']))
        <a href="{{ route('rendez-vous.create') }}" class="btn btn-primary"><i class="bi bi-calendar-plus"></i> Nouveau rendez-vous</a>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}" placeholder="Date début">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}" placeholder="Date fin">
            </div>
            <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="planifie" {{ request('statut') === 'planifie' ? 'selected' : '' }}>Planifié</option>
                        <option value="confirme" {{ request('statut') === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                        <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ request('statut') === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="annule" {{ request('statut') === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
            </div>
            @if(Auth::user()->role !== 'medecin')
            <div class="col-md-2">
                <input type="text" name="medecin_nom" class="form-control" value="{{ request('medecin_nom') }}" placeholder="Nom du médecin">
            </div>
            <div class="col-md-2">
                <select name="specialite_id" class="form-select">
                    <option value="">Toutes spécialités</option>
                    @foreach($specialites as $s)
                    <option value="{{ $s->id }}" {{ request('specialite_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->libelle }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Patient</th>
                        <th>Médecin</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rendezVous as $rdv)
                    <tr>
                        <td>{{ $rdv->date_rdv->format('d/m/Y') }}</td>
                        <td>{{ substr($rdv->heure_rdv, 0, 5) }}</td>
                        <td>
                            @if($rdv->patient)
                            <a href="{{ route('patients.show', $rdv->patient) }}" class="text-decoration-none">
                                {{ $rdv->patient->prenom }} {{ $rdv->patient->nom }}
                            </a>
                            @else
                            N/A
                            @endif
                        </td>
                        <td>Dr {{ optional($rdv->medecin)->prenom ?? 'N/A' }} {{ optional($rdv->medecin)->name ?? '' }}
                            @if($rdv->medecin && $rdv->medecin->specialites->count() > 0)
                            <small class="text-muted d-block">{{ $rdv->medecin->specialites->pluck('libelle')->implode(', ') }}</small>
                            @endif
                        </td>
                        <td>{{ $rdv->motif ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $rdv->statut === 'termine' ? 'success' : ($rdv->statut === 'annule' ? 'danger' : ($rdv->statut === 'confirme' ? 'primary' : ($rdv->statut === 'en_cours' ? 'info' : 'warning'))) }}">
                                {{ $rdv->statut }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('rendez-vous.show', $rdv) }}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                            @if(in_array(Auth::user()->role, ['admin', 'receptionniste']) && in_array($rdv->statut, ['planifie', 'confirme', 'en_cours']))
                            <a href="{{ route('rendez-vous.edit', $rdv) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            @endif
                            @if(in_array(Auth::user()->role, ['admin', 'receptionniste']) && in_array($rdv->statut, ['planifie', 'confirme']))
                            <form action="{{ route('rendez-vous.destroy', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i></button>
                            </form>
                            @endif
                            @if(Auth::user()->role === 'medecin' && $rdv->statut === 'planifie')
                            <form action="{{ route('rendez-vous.confirm', $rdv) }}" method="POST" class="d-inline" data-confirm="Confirmer ce rendez-vous ?">
                                @csrf
                                <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Confirmer</button>
                            </form>
                            <form action="{{ route('rendez-vous.cancel', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
                                @csrf
                                <button class="btn btn-sm btn-danger" title="Annuler"><i class="bi bi-x-circle"></i></button>
                            </form>
                            @endif
                            @if(Auth::user()->role === 'medecin' && $rdv->statut === 'confirme')
                            <form action="{{ route('rendez-vous.unconfirm', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler la confirmation ?">
                                @csrf
                                <button class="btn btn-sm btn-secondary" title="Annuler la confirmation"><i class="bi bi-x-lg"></i></button>
                            </form>
                            <form action="{{ route('rendez-vous.cancel', $rdv) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
                                @csrf
                                <button class="btn btn-sm btn-danger" title="Annuler"><i class="bi bi-x-circle"></i></button>
                            </form>
                            <form action="{{ route('consultations.start') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="patient_id" value="{{ $rdv->patient_id }}">
                                <input type="hidden" name="rendez_vous_id" value="{{ $rdv->id }}">
                                <button class="btn btn-sm btn-success"><i class="bi bi-play"></i> Démarrer</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center">Aucun rendez-vous trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rendezVous->links() }}
    </div>
</div>
@endsection
