@extends('layouts.app')
@section('title', 'Nouveau Rendez-vous')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-calendar-plus"></i> Nouveau Rendez-vous</div>
            <div class="card-body">
                <form method="POST" action="{{ route('rendez-vous.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Patient <span class="text-danger">*</span></label>
                            <input type="text" id="patient_search" class="form-control @error('patient_id') is-invalid @enderror" placeholder="Rechercher un patient (nom, prénom, n° dossier)">
                            <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id', request('patient_id')) }}">
                            <div id="patient_results" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>
                            <div id="patient_selected" class="mt-1">
                                @if(old('patient_id', request('patient_id')))
                                @php $selP = $patients->firstWhere('id', old('patient_id', request('patient_id'))) @endphp
                                @if($selP)
                                <span class="badge bg-info fs-6">{{ $selP->numero_dossier }} - {{ $selP->prenom }} {{ $selP->nom }}</span>
                                @endif
                                @endif
                            </div>
                            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-badge"></i> Médecin <span class="text-danger">*</span></label>
                            <select id="specialite_filter" class="form-select mb-2">
                                <option value="">Toutes les spécialités</option>
                                @foreach($specialites as $s)
                                <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="medecin_search" class="form-control @error('medecin_id') is-invalid @enderror" placeholder="Rechercher un médecin (nom, prénom)">
                            <input type="hidden" name="medecin_id" id="medecin_id" value="{{ old('medecin_id') }}">
                            <div id="medecin_results" class="list-group mt-1" style="max-height:200px;overflow-y:auto;display:none;position:absolute;z-index:1000;"></div>
                            <div id="medecin_selected" class="mt-1">
                                @if(old('medecin_id'))
                                @php $selM = $medecins->firstWhere('id', old('medecin_id')) @endphp
                                @if($selM)
                                <span class="badge bg-primary fs-6">Dr {{ $selM->prenom }} {{ $selM->name }}</span>
                                @endif
                                @endif
                            </div>
                            @error('medecin_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_rdv" id="date_rdv" class="form-control @error('date_rdv') is-invalid @enderror" value="{{ old('date_rdv') }}" required min="{{ date('Y-m-d') }}">
                            @error('date_rdv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-clock"></i> Heure <span class="text-danger">*</span></label>
                            <select name="heure_rdv" id="heure_rdv" class="form-select @error('heure_rdv') is-invalid @enderror" required>
                                <option value="">Sélectionner un créneau</option>
                            </select>
                            <div id="dispo-indicator" class="mt-1"></div>
                            @error('heure_rdv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat"></i> Motif</label>
                        <input type="text" name="motif" class="form-control" value="{{ old('motif') }}" placeholder="Motif de la consultation">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-journal-text"></i> Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Notes internes...">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('rendez-vous.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Planifier</button>
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
    verifierCreneaux();
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('#patient_search, #patient_results, #medecin_search, #medecin_results, #specialite_filter').length) {
        $('#patient_results, #medecin_results').hide();
    }
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
    $('#heure_rdv').empty().append('<option value="">Sélectionner un créneau</option>');
    $('#dispo-indicator').empty();
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
    verifierCreneaux();
});

function verifierCreneaux() {
    var medecin_id = $('#medecin_id').val();
    var date = $('#date_rdv').val();
    var $select = $('#heure_rdv');
    var $indicator = $('#dispo-indicator');
    if (medecin_id && date) {
        $.get('/api/disponibilites', { medecin_id, date }, function(data) {
            $select.empty().append('<option value="">Choisir un créneau</option>');
            var labels = { planifie: 'Planifié', confirme: 'Confirmé', en_cours: 'En cours', termine: 'Terminé' };
            data.forEach(function(c) {
                var opt = $('<option>').val(c.heure).text(c.heure);
                if (!c.disponible) opt.prop('disabled', true).text(c.heure + ' (' + (labels[c.statut] || c.statut) + ')');
                $select.append(opt);
            });
            $indicator.html('<span class="text-success"><i class="bi bi-check-circle"></i> ' + data.filter(c => c.disponible).length + ' créneaux disponibles</span>');
        });
    }
}

$('#date_rdv').change(verifierCreneaux);
</script>
@endpush
@endsection