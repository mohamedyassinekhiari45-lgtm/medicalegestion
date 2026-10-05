@extends('layouts.app')
@section('title', isset($role) ? 'Nouveau ' . ($role === 'medecin' ? 'Médecin' : ($role === 'receptionniste' ? 'Réceptionniste' : 'Administrateur')) : 'Nouvel Utilisateur')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-person-plus"></i> {{ isset($role) ? 'Nouveau ' . ($role === 'medecin' ? 'Médecin' : ($role === 'receptionniste' ? 'Réceptionniste' : 'Administrateur')) : 'Nouvel Utilisateur' }}</div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('avatar-input-create').click();">
                        <div class="bg-light d-flex align-items-center justify-content-center mx-auto border" style="width: 150px; height: 180px; border-radius: 8px;">
                            <i class="bi bi-person" style="font-size: 48px; color: #aaa;"></i>
                        </div>
                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px; transform: translate(-4px, -4px);">
                            <i class="bi bi-camera"></i>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatar-input-create" name="avatar" accept="image/*" style="display:none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Nom</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nom de famille" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" placeholder="Prénom">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@cabinet.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance') }}">
                            @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-telephone"></i> Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="+216 XX XXX XXX">
                        </div>
                    </div>
                    @if(isset($role))
                    <input type="hidden" name="role" value="{{ $role }}">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-shield-lock"></i> Rôle</label>
                        <input type="text" class="form-control" value="{{ $role === 'medecin' ? 'Médecin' : ($role === 'receptionniste' ? 'Réceptionniste' : 'Administrateur') }}" readonly>
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-shield-lock"></i> Rôle</label>
                        <select name="role" id="role-select" class="form-select" required>
                            <option value="receptionniste" {{ old('role') === 'receptionniste' ? 'selected' : '' }}>Réceptionniste</option>
                            <option value="medecin" {{ old('role') === 'medecin' ? 'selected' : '' }}>Médecin</option>
                            @if(Auth::id() === 1)
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
                            @endif
                        </select>
                    </div>
                    @endif
                    <div class="mb-3" id="specialite-field" style="{{ (isset($role) && $role === 'medecin') || old('role') === 'medecin' ? 'display:block;' : 'display:none;' }}">
                        <label class="form-label"><i class="bi bi-tags"></i> Spécialité(s)</label>
                        <select name="specialites[]" class="form-select" multiple size="6">
                            @foreach($specialites as $s)
                            <option value="{{ $s->id }}" {{ collect(old('specialites', []))->contains($s->id) ? 'selected' : '' }}>{{ $s->libelle }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Maintenez Ctrl (ou Cmd) pour choisir plusieurs spécialités.</div>
                        @error('specialites')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error('specialites.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    @if(!isset($role))
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var sel = document.getElementById('role-select');
                        var fld = document.getElementById('specialite-field');
                        sel.addEventListener('change', function() {
                            fld.style.display = this.value === 'medecin' ? 'block' : 'none';
                        });
                        if (sel.value === 'medecin') fld.style.display = 'block';
                    });
                    </script>
                    @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-lock"></i> Mot de passe</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-lock-fill"></i> Confirmer</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('avatar-input-create').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var container = document.querySelector('.bg-light');
        if (container) container.outerHTML = '<img src="' + e.target.result + '" style="width:150px;height:180px;object-fit:cover;border-radius:8px;">';
    };
    reader.readAsDataURL(file);
});
@endsection