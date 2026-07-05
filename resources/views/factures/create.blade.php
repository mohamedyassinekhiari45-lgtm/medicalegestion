@extends('layouts.app')
@section('title', 'Nouvelle Facture')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card animate-fade-up mb-3">
            <div class="card-header"><i class="bi bi-search"></i> Rechercher une consultation</div>
            <div class="card-body">
                <form method="GET" action="{{ route('factures.create') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Nom ou prénom du patient..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
                        @if(request()->filled('search'))
                        <a href="{{ route('factures.create') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Effacer</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-receipt"></i> Consultations terminées</div>
            <div class="card-body">
                @if($consultations->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    @if(request()->filled('search'))
                        Aucune consultation terminée trouvée pour "{{ request('search') }}".
                    @else
                        Aucune consultation terminée disponible. Les consultations doivent être terminées et sans facture existante.
                    @endif
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Montant</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consultations as $c)
                            <tr>
                                <td>{{ $c->date_consultation->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ optional($c->patient)->prenom ?? 'N/A' }} {{ optional($c->patient)->nom ?? '' }}</strong>
                                </td>
                                <td>{{ optional($c->medecin)->prenom ?? 'N/A' }} {{ optional($c->medecin)->name ?? '' }}</td>
                                <td>
                                    @if($c->tarifs->count() > 0)
                                        <span class="badge bg-info">{{ number_format($c->tarifs->sum('montant_ttc'), 2) }} DT</span>
                                    @else
                                        <span class="badge bg-secondary">0.00 DT</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('factures.generate', $c) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" @if($c->tarifs->sum('montant_ttc') <= 0) disabled title="Ajoutez des prestations d'abord" @endif>
                                            <i class="bi bi-receipt"></i> Générer la facture
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-muted small mt-2">
                    {{ $consultations->count() }} consultation(s) trouvée(s)
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection