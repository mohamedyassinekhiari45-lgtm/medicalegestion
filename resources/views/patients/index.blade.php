@extends('layouts.app')
@section('title', 'Liste des Patients')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-people"></i> Patients</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        @if(Auth::user()->role !== 'medecin')
        <a href="{{ route('patients.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nouveau patient</a>
        @endif
    </div>
</div>
<div class="card animate-fade-up">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, prénom, n° dossier ou téléphone..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">Rechercher</button>
                @if(request('search'))
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N° Dossier</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $patient->numero_dossier }}</span></td>
                        <td><strong>{{ $patient->nom }}</strong></td>
                        <td>{{ $patient->prenom }}</td>
                        <td>{{ $patient->telephone ?? '-' }}</td>
                        <td>{{ $patient->email ?? '-' }}</td>
                        <td>{{ $patient->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-info" title="Voir"><i class="bi bi-eye"></i></a>
                                @if(Auth::user()->role !== 'medecin')
                                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="d-inline" data-confirm="Archiver ce patient ?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Archiver"><i class="bi bi-archive"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Aucun patient trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $patients->links() }}
        </div>
    </div>
</div>
@endsection