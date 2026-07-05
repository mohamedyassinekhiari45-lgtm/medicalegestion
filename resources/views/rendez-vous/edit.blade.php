@extends('layouts.app')
@section('title', 'Modifier Rendez-vous')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-pencil"></i> Modifier Rendez-vous</div>
            <div class="card-body">
                @php $readonly = $rendezVous->statut === 'en_cours'; @endphp
                <form method="POST" action="{{ route('rendez-vous.update', $rendezVous) }}" data-confirm="Confirmer la modification ?">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Patient <span class="text-danger">*</span></label>
                            <input type="text" id="patient_search" class="form-control @error('patient_id') is-invalid @enderror" placeholder="Rechercher un patient (nom, prénom, n° dossier)" value="{{ optional($rendezVous->patient)->prenom ?? '' }} {{ optional($rendezVous->patient)->nom ?? '' }}" {{ $readonly ? 'disabled' : '' }}>
                            <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id', $rendezVous->patient_id) }}">
                            <div id="patient_results" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>
                            <div id="patient_selected" class="mt-1">
                                @if($rendezVous->patient)
                                <span class="badge bg-info fs-6">{{ $rendezVous->patient->numero_dossier }} - {{ $rendezVous->patient->prenom }} {{ $rendezVous->patient->nom }}</span>
                                @endif
                            </div>
                            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-badge"></i> Médecin <span class="text-danger">*</span></label>
                            <select id="specialite_filter" class="form-select mb-2" {{ $readonly ? 'disabled' : '' }}>
                                <option value="">Toutes les spécialités</option>
                                @foreach($specialites as $s)
                                <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="medecin_search" class="form-control @error('medecin_id') is-invalid @enderror" placeholder="Rechercher un médecin (nom, prénom)" value="Dr {{ $rendezVous->medecin->prenom ?? '' }} {{ $rendezVous->medecin->name ?? '' }}" {{ $readonly ? 'disabled' : '' }}>
                            <input type="hidden" name="medecin_id" id="medecin_id" value="{{ old('medecin_id', $rendezVous->medecin_id) }}">
                            <div id="medecin_results" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>
                            <div id="medecin_selected" class="mt-1">
                                <span class="badge bg-primary fs-6">Dr {{ optional($rendezVous->medecin)->prenom ?? '' }} {{ optional($rendezVous->medecin)->name ?? '' }}</span>
                            </div>
                            @error('medecin_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date</label>
                            <input type="date" name="date_rdv" class="form-control" value="{{ old('date_rdv', $rendezVous->date_rdv->format('Y-m-d')) }}" required {{ $readonly ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-clock"></i> Heure</label>
                            <input type="time" name="heure_rdv" class="form-control" value="{{ old('heure_rdv', $rendezVous->heure_rdv) }}" required {{ $readonly ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-flag"></i> Statut</label>
                            <div class="pt-2">
                                <span class="badge bg-{{ $rendezVous->statut === 'termine' ? 'success' : ($rendezVous->statut === 'annule' ? 'danger' : ($rendezVous->statut === 'confirme' ? 'primary' : ($rendezVous->statut === 'en_cours' ? 'info' : 'warning'))) }} fs-6">
                                    {{ $rendezVous->statut }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat"></i> Motif</label>
                        <input type="text" name="motif" class="form-control" value="{{ old('motif', $rendezVous->motif) }}" {{ $readonly ? 'disabled' : '' }}>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            @if(in_array(Auth::user()->role, ['admin', 'receptionniste']) && in_array($rendezVous->statut, ['planifie', 'confirme']))
                            <form action="{{ route('rendez-vous.destroy', $rendezVous) }}" method="POST" class="d-inline" data-confirm="Annuler ce rendez-vous ?">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="bi bi-x-circle"></i> Annuler le rendez-vous</button>
                            </form>
                            @endif
                        </div>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="{{ route('rendez-vous.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
                            @if(!$readonly)
                            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Mettre à jour</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
var searchTimer;
var medecinTimer;

$('#patient_search').on('input', function() {
    clearTimeout(searchTimer);
    var q = $(this).val();
    if (q.length < 1) {
        $('#patient_results').hide();
        return;
    }
    searchTimer = setTimeout(function() {
        $.get('/api/patients', { q: q }, function(data) {
            var $results = $('#patient_results').empty().show();
            if (data.length === 0) {
                $results.append('<div class="list-group-item text-muted">Aucun patient trouvé</div>');
            } else {
                data.forEach(function(p) {
                    $results.append(
                        '<a href="#" class="list-group-item list-group-item-action" data-id="' + p.id + '" data-nom="' + p.prenom + ' ' + p.nom + '" data-dossier="' + p.numero_dossier + '">' +
                        '<strong>' + p.numero_dossier + '</strong> - ' + p.prenom + ' ' + p.nom +
                        '</a>'
                    );
                });
            }
        });
    }, 300);
});

$(document).on('click', '#patient_results a', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var nom = $(this).data('nom');
    var dossier = $(this).data('dossier');
    $('#patient_id').val(id);
    $('#patient_search').val(nom);
    $('#patient_selected').html('<span class="badge bg-info fs-6">' + dossier + ' - ' + nom + '</span>');
    $('#patient_results').hide();
});

function chercherMedecins() {
    clearTimeout(medecinTimer);
    var q = $('#medecin_search').val();
    var specialite_id = $('#specialite_filter').val();
    if (q.length < 1 && !specialite_id) {
        $('#medecin_results').hide();
        return;
    }
    medecinTimer = setTimeout(function() {
        $.get('/api/medecins', { q: q, specialite_id: specialite_id }, function(data) {
            var $results = $('#medecin_results').empty().show();
            if (data.length === 0) {
                $results.append('<div class="list-group-item text-muted">Aucun médecin trouvé</div>');
            } else {
                data.forEach(function(m) {
                    var specialites = m.specialites.map(function(s) { return s.libelle; }).join(', ');
                    $results.append(
                        '<a href="#" class="list-group-item list-group-item-action" data-id="' + m.id + '" data-nom="Dr ' + m.prenom + ' ' + m.name + '">' +
                        '<strong>Dr ' + m.prenom + ' ' + m.name + '</strong>' +
                        (specialites ? ' <small class="text-muted">' + specialites + '</small>' : '') +
                        '</a>'
                    );
                });
            }
        });
    }, 300);
}

$('#medecin_search').on('input', chercherMedecins);
$('#specialite_filter').on('change', function() {
    $('#medecin_id').val('');
    $('#medecin_search').val('');
    $('#medecin_selected').empty();
    chercherMedecins();
});

$(document).on('click', '#medecin_results a', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    var nom = $(this).data('nom');
    $('#medecin_id').val(id);
    $('#medecin_search').val(nom);
    $('#medecin_selected').html('<span class="badge bg-primary fs-6">' + nom + '</span>');
    $('#medecin_results').hide();
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('#patient_search, #patient_results, #medecin_search, #medecin_results, #specialite_filter').length) {
        $('#patient_results, #medecin_results').hide();
    }
});
</script>
@endpush
@endsection
