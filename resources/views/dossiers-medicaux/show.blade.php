@extends('layouts.app')
@section('title', 'Dossier médical - ' . $patient->prenom . ' ' . $patient->nom)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><a href="{{ route('dashboard') }}" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i> Tableau de bord</a></h4>
</div>
<div class="row">
    <div class="col-12">
        <div class="card animate-fade-up mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder2-open"></i> Dossier médical de <strong>{{ $patient->prenom }} {{ $patient->nom }}</strong></span>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour au patient
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">N° dossier patient :</small>
                        <strong>{{ $patient->numero_dossier }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Date d'ouverture :</small>
                        <strong>{{ $dossier->date_ouverture->format('d/m/Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @php $notes = json_decode($dossier->notes_generales ?? '{}', true); @endphp
        <div class="card animate-fade-up mb-3">
            <div class="card-header"><i class="bi bi-pencil-square"></i> Mes notes</div>
            <div class="card-body">
                <form method="POST" action="{{ route('dossiers-medicaux.notes', $patient) }}">
                    @csrf
                    <div class="mb-3">
                        <textarea name="notes_generales" class="form-control" rows="4" placeholder="Mes observations sur le patient...">{{ old('notes_generales', $notes[Auth::id()] ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer mes notes</button>
                </form>
                @if($peutAutoriser && !$medecinsAutorisables->isEmpty())
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="shareNotesCheck">
                    <label class="form-check-label" for="shareNotesCheck">Inclure mes notes dans le partage</label>
                </div>
                @endif

                @if(count($notes) > 1 || (count($notes) === 1 && !isset($notes[Auth::id()])))
                <hr>
                <h6 class="text-muted">Notes partagées</h6>
                @php $afficheNotes = false; @endphp
                @foreach($notes as $medId => $note)
                @if($medId != Auth::id() && $note)
                @php
                    $auteur = \App\Models\User::find($medId);
                    $autorise = in_array($medId, $medecinsAutorisesIds);
                @endphp
                @if($auteur && $autorise)
                @php $afficheNotes = true; @endphp
                <div class="border rounded p-3 mb-2 bg-light">
                    <small class="text-muted">Dr {{ $auteur->prenom }} {{ $auteur->name }} <span class="badge bg-info">Autorisé</span></small>
                    <p class="mb-0 mt-1">{{ nl2br(e($note)) }}</p>
                </div>
                @endif
                @endif
                @endforeach
                @if(!$afficheNotes)
                <div class="alert alert-info mb-0 mt-2">
                    <i class="bi bi-info-circle"></i> Les notes des autres médecins sont masquées.
                </div>
                @endif
                @endif
            </div>
        </div>

        <div class="card animate-fade-up mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark"></i> Mes documents ({{ $mesDocs->count() }})</span>
                @if($peutAutoriser && $mesDocs->isNotEmpty())
                <div>
                    <input type="checkbox" id="selectAllDocs" class="form-check-input me-1">
                    <label for="selectAllDocs" class="form-check-label small">Tout sélectionner</label>
                </div>
                @endif
            </div>
            <div class="card-body">
                <div class="collapse mb-3" id="addDocumentForm">
                    <div class="border rounded p-3 bg-light">
                        <h6><i class="bi bi-upload"></i> Nouveau document</h6>
                        <form method="POST" action="{{ route('dossiers-medicaux.documents.store', $patient) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="ordonnance">Ordonnance</option>
                                        <option value="bilan">Bilan</option>
                                        <option value="radio">Radio / Imagerie</option>
                                        <option value="compte_rendu">Compte rendu</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                                    <input type="text" name="titre" class="form-control" placeholder="Ex: Bilan sanguin mars 2026" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fichier</label>
                                    <input type="file" name="fichier" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Description optionnelle..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Ajouter</button>
                        </form>
                    </div>
                </div>

                @if($mesDocs->isEmpty() && $docsAutorises->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Aucun document dans votre section.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                @if($peutAutoriser)<th style="width:40px;"></th>@endif
                                <th>Type</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Ajouté par</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mesDocs as $doc)
                            <tr>
                                @if($peutAutoriser)
                                <td><input type="checkbox" class="form-check-input doc-check" value="{{ $doc->id }}"></td>
                                @endif
                                <td><span class="badge bg-secondary">{{ ucfirst($doc->type) }}</span></td>
                                <td><strong>{{ $doc->titre }}</strong></td>
                                <td class="text-muted">{{ Str::limit($doc->description, 50) }}</td>
                                <td>Moi</td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @if($doc->fichier)
                                    <a href="{{ Storage::url($doc->fichier) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                    <form method="POST" action="{{ route('dossiers-medicaux.documents.destroy', $doc) }}" style="display:inline" data-confirm="Supprimer ce document ?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($peutAutoriser && $mesDocs->isNotEmpty())
                <div class="border rounded p-3 bg-light mt-3">
                    <form method="POST" action="{{ route('dossiers-medicaux.share', $patient) }}" id="shareForm">
                        @csrf
                        <input type="hidden" name="notes_partagees" id="notesPartageesInput" value="">
                        <input type="hidden" name="documents_partagees" id="documentsPartageesInput" value="">
                        <div class="row align-items-end">
                            <div class="col-md-5 mb-2">
                                <label class="form-label"><i class="bi bi-person-plus"></i> Partager la sélection avec</label>
                                <select name="to_medecin_id" class="form-select" required>
                                    <option value="">-- Choisir un médecin --</option>
                                    @foreach($medecinsAutorisables as $m)
                                    <option value="{{ $m->id }}">Dr {{ $m->prenom }} {{ $m->name }} ({{ $m->specialites->pluck('libelle')->implode(', ') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">&nbsp;</label>
                                <div><small class="text-muted" id="shareSummary">Aucune note/document sélectionné</small></div>
                            </div>
                            <div class="col-md-4 mb-2 text-end">
                                <button type="submit" class="btn btn-primary" id="shareBtn" disabled><i class="bi bi-send"></i> Envoyer la demande</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        @if($shareRequests->isNotEmpty())
        <div class="card animate-fade-up mb-3 border-warning">
            <div class="card-header bg-warning bg-opacity-10"><i class="bi bi-bell"></i> Demandes de partage reçues</div>
            <div class="card-body">
                @foreach($shareRequests as $sr)
                @php
                    $ownerInitiated = (int)$sr->requester_id === (int)$sr->from_medecin_id;
                    $isForMe = (int)$sr->to_medecin_id === (int)Auth::id();
                    $isFromMe = (int)$sr->from_medecin_id === (int)Auth::id();
                @endphp
                @if($ownerInitiated && $isForMe)
                <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                    <div>
                        <strong>Dr {{ $sr->fromMedecin->prenom }} {{ $sr->fromMedecin->name }}</strong>
                        <span class="badge bg-secondary">{{ $sr->fromMedecin->specialites->pluck('libelle')->implode(', ') ?: 'Généraliste' }}</span>
                        <br><small class="text-muted">
                            Souhaite partager :
                            @if(!empty($sr->notes_partagees)) <span class="badge bg-info">Notes</span> @endif
                            @if(!empty($sr->documents_partagees)) <span class="badge bg-info">{{ count($sr->documents_partagees) }} document(s)</span> @endif
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('dossiers-medicaux.accept-share', $sr) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Accepter</button>
                        </form>
                        <form method="POST" action="{{ route('dossiers-medicaux.refuse-share', $sr) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i> Refuser</button>
                        </form>
                    </div>
                </div>
                @elseif(!$ownerInitiated && $isFromMe)
                <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                    <div>
                        <strong>Dr {{ $sr->requester->prenom }} {{ $sr->requester->name }}</strong>
                        <span class="badge bg-secondary">{{ $sr->requester->specialites->pluck('libelle')->implode(', ') ?: 'Généraliste' }}</span>
                        <br><small class="text-muted">Demande l'accès à VOTRE section du dossier</small>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('dossiers-medicaux.accept-share', $sr) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Accepter</button>
                        </form>
                        <form method="POST" action="{{ route('dossiers-medicaux.refuse-share', $sr) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i> Refuser</button>
                        </form>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="card animate-fade-up mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check"></i> Accès au dossier</span>
                @if($peutAutoriser)
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#grantAccessForm">
                    <i class="bi bi-person-plus"></i> Autorisation directe
                </button>
                @endif
            </div>
            <div class="card-body">
                @if($peutAutoriser)
                <div class="collapse mb-3" id="grantAccessForm">
                    <div class="border rounded p-3 bg-light">
                        <h6><i class="bi bi-person-plus"></i> Accorder un accès immédiat à toute ma section</h6>
                        @if($medecinsAutorisables->isEmpty())
                        <p class="text-muted mb-0">Aucun médecin à autoriser pour le moment.</p>
                        @else
                        <form method="POST" action="{{ route('dossiers-medicaux.grant', $patient) }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-auto">
                                <select name="medecin_id" class="form-select" required>
                                    <option value="">-- Choisir un médecin --</option>
                                    @foreach($medecinsAutorisables as $m)
                                    <option value="{{ $m->id }}">Dr {{ $m->prenom }} {{ $m->name }} ({{ $m->specialites->pluck('libelle')->implode(', ') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Autoriser</button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
                @endif

                @if($requestableMedecins->isNotEmpty())
                <hr>
                <h6 class="text-muted"><i class="bi bi-search"></i> Demander l'accès aux notes d'un médecin</h6>
                <div class="border rounded p-3 bg-light">
                    <form method="POST" action="{{ route('dossiers-medicaux.request-access', $patient) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <select name="medecin_id" class="form-select" required>
                                <option value="">-- Choisir un médecin --</option>
                                @foreach($requestableMedecins as $rm)
                                <option value="{{ $rm['id'] }}">{{ $rm['label'] }} ({{ implode(', ', $rm['specialites']) ?: 'Généraliste' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus"></i> Demander l'accès</button>
                        </div>
                    </form>
                </div>
                @elseif(!$peutAutoriser)
                <hr>
                <div class="alert alert-info py-2 mb-0">
                    <i class="bi bi-info-circle"></i> Vous n'avez pas encore traité ce patient. Une fois que vous aurez des consultations ou rendez-vous, vous pourrez demander l'accès aux sections des autres médecins.
                </div>
                @endif

                @if($sentRequests->isNotEmpty())
                <h6 class="text-muted mb-2">Mes demandes envoyées</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Médecin</th>
                                <th>Éléments</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sentRequests as $sr)
                            @php
                                $ownerInitiated = (int)$sr->requester_id === (int)$sr->from_medecin_id;
                            @endphp
                            <tr>
                                <td>@if($ownerInitiated) <span class="badge bg-info">Partage</span> @else <span class="badge bg-warning">Demande</span> @endif</td>
                                <td>@if($ownerInitiated) Dr {{ $sr->toMedecin->prenom }} {{ $sr->toMedecin->name }} @else Dr {{ $sr->fromMedecin->prenom }} {{ $sr->fromMedecin->name }} @endif</td>
                                <td>
                                    @if($ownerInitiated)
                                        @if(!empty($sr->notes_partagees)) <span class="badge bg-info">Notes</span> @endif
                                        @if(!empty($sr->documents_partagees)) <span class="badge bg-info">{{ count($sr->documents_partagees) }} document(s)</span> @endif
                                    @else
                                        <span class="text-muted">Accès à sa section</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sr->statut === 'en_attente') <span class="badge bg-warning">En attente</span>
                                    @elseif($sr->statut === 'acceptee') <span class="badge bg-success">Acceptée</span>
                                    @else <span class="badge bg-danger">Refusée</span>
                                    @endif
                                </td>
                                <td>{{ $sr->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($dossier->authorizations->isEmpty())
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Aucune autorisation partagée pour le moment.
                </div>
                @else
                <h6 class="text-muted mb-2">Autorisations actives</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Médecin</th>
                                <th>Spécialité</th>
                                <th>Accès à la section de</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossier->authorizations as $auth)
                            <tr>
                                <td>Dr {{ $auth->medecin->prenom }} {{ $auth->medecin->name }}</td>
                                <td><span class="badge bg-info">{{ $auth->medecin->specialites->pluck('libelle')->implode(', ') ?: 'Généraliste' }}</span></td>
                                <td>Dr {{ $auth->autorisePar->prenom }} {{ $auth->autorisePar->name }} <span class="badge bg-secondary">{{ $auth->autorisePar->specialites->pluck('libelle')->implode(', ') ?: 'Généraliste' }}</span></td>
                                <td>{{ $auth->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @if($auth->autorise_par === Auth::id())
                                    <form method="POST" action="{{ route('dossiers-medicaux.revoke', $auth) }}" style="display:inline" data-confirm="Révoquer l'accès de Dr {{ $auth->medecin->prenom }} {{ $auth->medecin->name }} à votre section ?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Révoquer</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        @if($docsAutorises->isNotEmpty())
        <div class="card animate-fade-up mb-3">
            <div class="card-header">
                <span><i class="bi bi-file-earmark-lock"></i> Documents partagés ({{ $docsAutorises->count() }})</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Ajouté par</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($docsAutorises as $doc)
                            <tr>
                                <td><span class="badge bg-info">{{ ucfirst($doc->type) }}</span></td>
                                <td><strong>{{ $doc->titre }}</strong></td>
                                <td class="text-muted">{{ Str::limit($doc->description, 50) }}</td>
                                <td>Dr {{ $doc->uploader->prenom ?? '' }} {{ $doc->uploader->name ?? '' }}</td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @if($doc->fichier)
                                    <a href="{{ Storage::url($doc->fichier) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($autresDocs->isNotEmpty())
        <div class="card animate-fade-up">
            <div class="card-header">
                <span><i class="bi bi-lock"></i> Documents masqués ({{ $autresDocs->count() }})</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle"></i> {{ $autresDocs->count() }} document(s) ajouté(s) par d'autres médecins ne vous sont pas accessibles.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@if($peutAutoriser)
<script>
document.addEventListener('DOMContentLoaded', function() {
    var notesCheck = document.getElementById('shareNotesCheck');
    var docChecks = document.querySelectorAll('.doc-check');
    var shareBtn = document.getElementById('shareBtn');
    var shareSummary = document.getElementById('shareSummary');
    var notesInput = document.getElementById('notesPartageesInput');
    var docsInput = document.getElementById('documentsPartageesInput');
    var selectAll = document.getElementById('selectAllDocs');

    function updateShareState() {
        var notesSelected = notesCheck && notesCheck.checked;
        var docIds = [];
        docChecks.forEach(function(c) { if (c.checked) docIds.push(c.value); });

        notesInput.value = notesSelected ? '[' + {{ Auth::id() }} + ']' : '';
        docsInput.value = JSON.stringify(docIds);

        var parts = [];
        if (notesSelected) parts.push('Notes');
        if (docIds.length > 0) parts.push(docIds.length + ' document(s)');

        shareSummary.textContent = parts.length > 0 ? parts.join(', ') : 'Aucune note/document sélectionné';
        shareBtn.disabled = parts.length === 0 || document.querySelector('[name="to_medecin_id"]').value === '';
    }

    if (notesCheck) notesCheck.addEventListener('change', updateShareState);
    docChecks.forEach(function(c) { c.addEventListener('change', updateShareState); });
    var medecinSelect = document.querySelector('[name="to_medecin_id"]');
    if (medecinSelect) medecinSelect.addEventListener('change', updateShareState);

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            docChecks.forEach(function(c) { c.checked = selectAll.checked; });
            updateShareState();
        });
    }
});
</script>
@endif
@endsection
