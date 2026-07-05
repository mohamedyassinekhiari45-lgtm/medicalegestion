@extends('layouts.app')
@section('title', 'Modifier Tarif')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-pencil"></i> Modifier le tarif</div>
            <div class="card-body">
                <form method="POST" action="{{ route('tarifs.update', $tarif) }}" data-confirm="Confirmer la modification ?">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Code nomenclature</label>
                        <input type="text" name="code_nomenclature" class="form-control" value="{{ old('code_nomenclature', $tarif->code_nomenclature) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Acte médical</label>
                        <input type="text" name="acte" class="form-control" value="{{ old('acte', $tarif->acte) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Montant HT (DT)</label>
                            <input type="number" step="0.01" name="montant_ht" class="form-control" value="{{ old('montant_ht', $tarif->montant_ht) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TVA (%)</label>
                            <input type="number" step="0.01" name="tva" class="form-control" value="{{ old('tva', $tarif->tva) }}" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('tarifs.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
