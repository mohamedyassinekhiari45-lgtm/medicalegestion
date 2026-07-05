@extends('layouts.app')
@section('title', 'Mon Profil')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person"></i> Mon Profil</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="#" class="btn btn-outline-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person"></i> Mon Profil</div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('avatar-input').click();">
                        @if($user->avatar)
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Photo de profil" class="border" style="width: 150px; height: 180px; object-fit: cover; border-radius: 8px;">
                        @else
                        <div class="bg-primary text-white d-flex align-items-center justify-content-center mx-auto border" style="width: 150px; height: 180px; font-size: 48px; border-radius: 8px;">
                            <i class="bi bi-person"></i>
                        </div>
                        @endif
                        <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px; transform: translate(-4px, -4px);">
                            <i class="bi bi-camera"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'medecin' ? 'primary' : 'success') }} fs-6">
                            <i class="bi bi-{{ $user->role === 'admin' ? 'shield-lock' : ($user->role === 'medecin' ? 'person-badge' : 'person') }}"></i>
                            {{ $user->role === 'admin' ? 'Administrateur' : ($user->role === 'medecin' ? 'Médecin' : 'Réceptionniste') }}
                        </span>
                        @if($user->role === 'medecin' && $user->specialites->count() > 0)
                            @foreach($user->specialites as $s)
                            <span class="badge bg-info fs-6">{{ $s->libelle }}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="mt-1">
                        <span class="badge bg-{{ $user->statut ? 'success' : 'secondary' }} fs-6">
                            <i class="bi bi-{{ $user->statut ? 'check-circle' : 'x-circle' }}"></i>
                            {{ $user->statut ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    @if($user->avatar)
                    <div class="mt-2">
                        <form method="POST" action="{{ route('profile.avatar.delete') }}" class="d-inline" data-confirm="Supprimer la photo de profil ?">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Supprimer photo</button>
                        </form>
                    </div>
                    @endif
                </div>

                <form id="avatar-upload-form" method="POST" action="{{ route('profile.avatar.upload') }}" enctype="multipart/form-data" style="display:none;">
                    @csrf
                    <input type="file" id="avatar-input" name="avatar" accept="image/*" onchange="this.form.submit();">
                </form>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom', $user->prenom) }}">
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', $user->date_naissance ? $user->date_naissance->format('Y-m-d') : '') }}">
                            @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $user->telephone) }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <hr>
                    <h6>Changer le mot de passe (optionnel)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirmer</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('password.forgot') }}" class="text-decoration-none small">
                            <i class="bi bi-shield-lock"></i> Mot de passe oublié ? Réinitialiser via email
                        </a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                    </div>
                </form>

                <hr>
                <h6><i class="bi bi-shield-lock"></i> Sécurité du compte</h6>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <div>
                        <p class="mb-0">Authentification à deux facteurs (2FA)</p>
                        <small class="text-muted">Protégez votre compte avec Google Authenticator.</small>
                    </div>
                    @if($user->google2fa_pending)
                    <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock"></i> En attente</span>
                    @elseif($user->google2fa_enabled)
                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Activé</span>
                    @else
                    <span class="badge bg-secondary fs-6">Désactivé</span>
                    @endif
                </div>
                <hr>

                @if($user->google2fa_pending)
                <div id="pending2faSection">
                    <p class="text-center">Scannez ce QR code avec <strong>Google Authenticator</strong> puis saisissez le code à 6 chiffres pour confirmer l'activation.</p>
                    <div id="pendingQrContainer" class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
                    </div>
                    <form method="POST" action="{{ route('profile.2fa.confirm') }}" class="mb-2">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Code de vérification</label>
                            <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" inputmode="numeric" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-2"><i class="bi bi-check-lg"></i> Confirmer</button>
                    </form>
                    <form method="POST" action="{{ route('profile.2fa.cancel-pending') }}" data-confirm="Annuler l'activation 2FA ?">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Annuler</button>
                    </form>
                </div>

                @elseif($user->google2fa_enabled)
                <div id="disableButtons">
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-primary" id="show2faQrBtn"><i class="bi bi-qr-code"></i> Afficher le QR code</button>
                        <button type="submit" form="regenerate2faForm" class="btn btn-outline-warning"><i class="bi bi-arrow-clockwise"></i> Régénérer</button>
                        <button type="button" class="btn btn-outline-danger" id="showDisableFormBtn"><i class="bi bi-x-circle"></i> Désactiver</button>
                    </div>
                    <div id="qrCodeContainer" class="text-center mb-3" style="display:none;">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div>
                    </div>
                    <form id="regenerate2faForm" method="POST" action="{{ route('profile.2fa.regenerate') }}" class="d-none" data-confirm="Régénérer la clé 2FA ?">
                        @csrf
                    </form>
                </div>

                <div id="disableForm" class="text-center" style="display:none;">
                    <p>Pour désactiver la 2FA, saisissez le code généré par votre application d'authentification.</p>
                    <form method="POST" action="{{ route('profile.2fa.disable') }}" class="mb-2">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" inputmode="numeric" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 mb-2"><i class="bi bi-x-circle"></i> Confirmer la désactivation</button>
                    </form>
                    <button type="button" class="btn btn-outline-secondary w-100" id="cancelDisableBtn"><i class="bi bi-arrow-left"></i> Annuler</button>
                </div>

                @else
                <form method="POST" action="{{ route('profile.2fa.enable') }}" data-confirm="Activer l'authentification à deux facteurs ?">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-shield-plus"></i> Activer la 2FA</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if($user->pending_email)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-shield-check"></i> Confirmer le changement d'email</h5>
                <form method="POST" action="{{ route('profile.cancel-email') }}" class="d-inline" data-confirm="Annuler le changement d'email ?">
                    @csrf
                    <button type="submit" class="btn-close btn-close-white" title="Annuler"></button>
                </form>
            </div>
            <div class="modal-body">
                <p>Un code de confirmation a été envoyé à <strong>{{ $user->pending_email }}</strong>.</p>
                <form method="POST" action="{{ route('profile.verify-email') }}" class="mb-2">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Code de vérification</label>
                        <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg"></i> Confirmer</button>
                </form>
                <form method="POST" action="{{ route('profile.resend-code') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise"></i> Renvoyer le code</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrContainer = document.getElementById('qrCodeContainer');
    const showQrBtn = document.getElementById('show2faQrBtn');
    if (showQrBtn && qrContainer) {
        showQrBtn.addEventListener('click', function() {
            if (qrContainer.style.display !== 'none' && qrContainer.querySelector('img')) {
                qrContainer.style.display = qrContainer.style.display === 'none' ? 'block' : 'none';
                return;
            }
            qrContainer.style.display = 'block';
            loadQrCode(qrContainer);
        });
    }
    const pendingContainer = document.getElementById('pendingQrContainer');
    if (pendingContainer) {
        loadQrCode(pendingContainer);
    }

    const disableBtn = document.getElementById('showDisableFormBtn');
    const disableForm = document.getElementById('disableForm');
    const disableButtons = document.getElementById('disableButtons');
    const cancelDisableBtn = document.getElementById('cancelDisableBtn');
    if (disableBtn && disableForm && disableButtons && cancelDisableBtn) {
        disableBtn.addEventListener('click', function() {
            disableButtons.style.display = 'none';
            disableForm.style.display = 'block';
        });
        cancelDisableBtn.addEventListener('click', function() {
            disableForm.style.display = 'none';
            disableButtons.style.display = 'block';
        });
        @if(session('error') && str_contains(session('error'), '2FA invalide'))
        disableButtons.style.display = 'none';
        disableForm.style.display = 'block';
        @endif
    }

    function loadQrCode(container) {
        fetch('{{ route("profile.2fa.qrcode") }}')
            .then(r => r.json())
            .then(data => {
                container.innerHTML = '<div class="mb-2"><img src="' + data.qr_code + '" alt="QR Code 2FA" class="img-fluid" style="max-width:200px;"></div><p class="small text-muted">Clé secrète : <code>' + data.secret + '</code></p>';
            })
            .catch(() => {
                container.innerHTML = '<p class="text-danger">Erreur de chargement du QR code.</p>';
            });
    }
});
</script>
@endpush
@endsection