<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA - Gestion Médicale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="login-card animate-fade-up">
            <div class="brand">
                <i class="bi bi-shield-lock"></i>
                <h2>Authentification à deux facteurs</h2>
                <p>Saisissez le code à 6 chiffres généré par votre application d'authentification.</p>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-fade-in">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-key"></i> Code de vérification</label>
                    <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
                </div>
                <div class="d-grid mb-2">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-shield-check"></i> Vérifier</button>
                </div>
            </form>
            <form method="POST" action="{{ route('2fa.cancel') }}">
                @csrf
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
