@extends('layouts.app')
@section('title', 'Nouvelle Consultation')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-plus-circle"></i> Nouvelle Consultation</div>
            <div class="card-body">
                <form method="POST" action="{{ route('consultations.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Sélectionner un patient...</option>
                                @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id', $patient?->id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->numero_dossier }} - {{ $p->prenom }} {{ $p->nom }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date consultation</label>
                            <input type="date" name="date_consultation" class="form-control" value="{{ old('date_consultation', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    @if($rdv)
                    <input type="hidden" name="rendez_vous_id" value="{{ $rdv->id }}">
                    @endif
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-heart-pulse"></i> Tension artérielle</label>
                            <input type="text" name="constantes[tension]" class="form-control" placeholder="120/80" value="{{ old('constantes.tension') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-activity"></i> Pouls (bpm)</label>
                            <input type="number" name="constantes[pouls]" class="form-control" placeholder="72" value="{{ old('constantes.pouls') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="bi bi-thermometer-half"></i> Température (°C)</label>
                            <input type="number" step="0.1" name="constantes[temperature]" class="form-control" placeholder="37.0" value="{{ old('constantes.temperature') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-stethoscope"></i> Diagnostic</label>
                        <textarea name="diagnostic" class="form-control" rows="3" placeholder="Diagnostic médical...">{{ old('diagnostic') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-chat-dots"></i> Observations</label>
                        <textarea name="observations" class="form-control" rows="3" placeholder="Observations complémentaires...">{{ old('observations') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-receipt"></i> Prestations (tarifs)</label>
                        <select name="tarifs[]" class="form-select" multiple>
                            @foreach($tarifs as $t)
                            <option value="{{ $t->id }}">{{ $t->code_nomenclature }} - {{ $t->acte }} - {{ number_format($t->montant_ttc, 2) }} DT</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs prestations</small>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('consultations.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer la consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection