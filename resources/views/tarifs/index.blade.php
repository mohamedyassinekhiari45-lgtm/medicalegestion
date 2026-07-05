@extends('layouts.app')
@section('title', 'Tarifs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-currency-euro"></i> Grille tarifaire</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('tarifs.pdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
</div>
<div class="row">
    <div class="col-md-5 mb-4 animate-slide-left">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle"></i> Nouveau tarif</div>
            <div class="card-body">
                <form method="POST" action="{{ route('tarifs.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Code nomenclature</label>
                        <input type="text" name="code_nomenclature" class="form-control @error('code_nomenclature') is-invalid @enderror" value="{{ old('code_nomenclature') }}" placeholder="Ex: ACT001" required>
                        @error('code_nomenclature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Acte médical</label>
                        <input type="text" name="acte" class="form-control" value="{{ old('acte') }}" placeholder="Ex: Consultation générale" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Montant HT (DT)</label>
                            <input type="number" step="0.01" name="montant_ht" class="form-control" value="{{ old('montant_ht') }}" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TVA (%)</label>
                            <input type="number" step="0.01" name="tva" class="form-control" value="{{ old('tva', 19) }}" placeholder="19" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4 animate-fade-up">
        <div class="card">
            <div class="card-header"><i class="bi bi-list"></i> Liste des tarifs <span class="badge bg-primary float-end">{{ $tarifs->total() }}</span></div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par code ou acte..." value="{{ request('search') }}">
                        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                        @if(request('search'))
                        <a href="{{ route('tarifs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Code</th><th>Acte</th><th>HT</th><th>TVA</th><th>TTC</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach($tarifs as $t)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $t->code_nomenclature }}</span></td>
                                <td><strong>{{ $t->acte }}</strong></td>
                                <td>{{ number_format($t->montant_ht, 2) }} DT</td>
                                <td>{{ $t->tva }}%</td>
                                <td><strong class="text-primary">{{ number_format($t->montant_ttc, 2) }} DT</strong></td>
                                <td>
                                    <a href="{{ route('tarifs.edit', $t) }}" class="btn btn-sm btn-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('tarifs.destroy', $t) }}" method="POST" class="d-inline" data-confirm="Supprimer ce tarif ?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $tarifs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection