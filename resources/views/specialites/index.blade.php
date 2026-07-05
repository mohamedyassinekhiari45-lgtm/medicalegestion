@extends('layouts.app')
@section('title', 'Spécialités')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-tags"></i> Spécialités médicales</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('specialites.pdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
    </div>
</div>
<div class="row">
    <div class="col-md-5 mb-4 animate-slide-left">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle"></i> Nouvelle spécialité</div>
            <div class="card-body">
                <form method="POST" action="{{ route('specialites.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" placeholder="Ex: Cardiologie" required>
                        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Description de la spécialité...">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4 animate-fade-up">
        <div class="card">
            <div class="card-header"><i class="bi bi-list"></i> Liste des spécialités <span class="badge bg-primary float-end">{{ $specialites->total() }}</span></div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par libellé..." value="{{ request('search') }}">
                        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                        @if(request('search'))
                        <a href="{{ route('specialites.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Libellé</th><th>Description</th><th>Médecins</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach($specialites as $s)
                            <tr>
                                <td><strong>{{ $s->libelle }}</strong></td>
                                <td>{{ $s->description ? \Illuminate\Support\Str::limit($s->description, 50) : '-' }}</td>
                                <td><span class="badge bg-info">{{ $s->medecins_count }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('specialites.update', $s) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="text" name="libelle" value="{{ $s->libelle }}" class="form-control form-control-sm d-inline" style="width:120px" required>
                                        <button class="btn btn-sm btn-warning" title="Modifier"><i class="bi bi-check"></i></button>
                                    </form>
                                    <form action="{{ route('specialites.destroy', $s) }}" method="POST" class="d-inline" data-confirm="Supprimer cette spécialité ?">
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
                    {{ $specialites->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection