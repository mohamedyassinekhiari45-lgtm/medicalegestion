@extends('layouts.app')
@section('title', 'Modifier Utilisateur')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-pencil"></i> Modifier Utilisateur</div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('avatar-input-edit').click();">
                        @if($user->avatar)
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Photo" class="border" style="width: 150px; height: 180px; object-fit: cover; border-radius: 8px;" id="avatar-preview-edit">
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center mx-auto border" id="avatar-placeholder-edit" style="width: 150px; height: 180px; border-radius: 8px;">
                            <i class="bi bi-person" style="font-size: 48px; color: #aaa;"></i>
                        </div>
                        @endif
                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px; transform: translate(-4px, -4px);">
                            <i class="bi bi-camera"></i>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('users.update', $user) }}" data-confirm="Confirmer la modification ?" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="file" id="avatar-input-edit" name="avatar" accept="image/*" style="display:none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Nom</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person"></i> Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $user->prenom) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                            @if($user->role === 'admin')
                            <input type="email" class="form-control" value="{{ old('email', $user->email) }}" readonly>
                            <small class="text-muted">L'admin doit modifier son email depuis son profil.</small>
                            @else
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar"></i> Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', $user->date_naissance ? $user->date_naissance->format('Y-m-d') : '') }}">
                            @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-telephone"></i> Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-shield-lock"></i> Rôle</label>
                            @if($user->id === Auth::id() || ($user->role === 'admin' && Auth::id() !== 1))
                            <input type="text" class="form-control" value="{{ $user->role === 'admin' ? 'Administrateur' : ($user->role === 'medecin' ? 'Médecin' : 'Réceptionniste') }}" readonly>
                            @else
                            <select name="role" id="role-edit-select" class="form-select">
                                <option value="receptionniste" {{ $user->role === 'receptionniste' ? 'selected' : '' }}>Réceptionniste</option>
                                <option value="medecin" {{ $user->role === 'medecin' ? 'selected' : '' }}>Médecin</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrateur</option>
                            </select>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-toggle-on"></i> Statut</label>
                            @if($user->id === Auth::id() || ($user->role === 'admin' && Auth::id() !== 1))
                            <input type="text" class="form-control" value="{{ $user->statut ? 'Actif' : 'Inactif' }}" readonly>
                            @else
                            <select name="statut" class="form-select">
                                <option value="1" {{ $user->statut ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ !$user->statut ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3" id="specialite-edit-field" style="display:none;">
                        <label class="form-label"><i class="bi bi-tags"></i> Spécialité</label>
                        <select name="specialites[]" class="form-select">
                            <option value="">-- Sélectionner une spécialité --</option>
                            @foreach($specialites as $s)
                            <option value="{{ $s->id }}" {{ $user->specialites->contains($s->id) ? 'selected' : '' }}>{{ $s->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var sel = document.getElementById('role-edit-select');
                        var fld = document.getElementById('specialite-edit-field');
                        sel.addEventListener('change', function() {
                            fld.style.display = this.value === 'medecin' ? 'block' : 'none';
                        });
                        if (sel.value === 'medecin') fld.style.display = 'block';
                    });
                    </script>
                    <script>
                    document.getElementById('avatar-input-edit').addEventListener('change', function() {
                        var file = this.files[0];
                        if (!file) return;
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var preview = document.getElementById('avatar-preview-edit') || document.getElementById('avatar-placeholder-edit');
                            if (preview.tagName === 'DIV') {
                                preview.outerHTML = '<img src="' + e.target.result + '" id="avatar-preview-edit" style="width:150px;height:180px;object-fit:cover;border-radius:8px;">';
                            } else {
                                preview.src = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                    </script>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection