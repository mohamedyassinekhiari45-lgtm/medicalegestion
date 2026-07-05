@extends('layouts.app')
@section('title', 'Modifier Consultation')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-pencil"></i> Modifier Consultation</div>
            <div class="card-body">
                <form method="POST" action="{{ route('consultations.update', $consultation) }}" data-confirm="Confirmer ?">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date</label>
                            <input type="text" class="form-control" value="{{ $consultation->date_consultation->format('d/m/Y') }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-flag"></i> Statut</label>
                            <input type="text" class="form-control" value="{{ ucfirst($consultation->statut) }}" disabled>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-heart-pulse"></i> Tension</label>
                            <input type="text" name="constantes[tension]" class="form-control" value="{{ old('constantes.tension', $consultation->constantes['tension'] ?? '') }}" placeholder="120/80">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-activity"></i> Pouls (bpm)</label>
                            <input type="number" name="constantes[pouls]" class="form-control" value="{{ old('constantes.pouls', $consultation->constantes['pouls'] ?? '') }}" placeholder="72">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-thermometer-half"></i> Température</label>
                            <input type="number" step="0.1" name="constantes[temperature]" class="form-control" value="{{ old('constantes.temperature', $consultation->constantes['temperature'] ?? '') }}" placeholder="37.0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-stethoscope"></i> Diagnostic</label>
                        <textarea name="diagnostic" class="form-control" rows="3">{{ old('diagnostic', $consultation->diagnostic) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat-dots"></i> Observations</label>
                        <textarea name="observations" class="form-control" rows="3">{{ old('observations', $consultation->observations) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-receipt"></i> Prestations</label>
                        <input type="hidden" name="tarifs" value="">
                        @error('tarifs')<div class="text-danger small mb-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        <div class="row">
                            @foreach($tarifs as $t)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tarifs[]" value="{{ $t->id }}" id="tarif_{{ $t->id }}"
                                        {{ $consultation->tarifs->contains($t->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tarif_{{ $t->id }}">
                                        <strong>{{ $t->code_nomenclature }}</strong> — {{ $t->acte }}
                                        <span class="text-muted">{{ number_format($t->montant_ttc, 2) }} DT</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-warning" name="action" value="save"><i class="bi bi-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection