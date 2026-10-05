<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Médicale</title>
    <script>
    (function() {
        var t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
        if (localStorage.getItem('eyeComfort') === '1') {
            document.documentElement.setAttribute('data-eye-comfort', 'true');
        }
    })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="login-card animate-fade-up">
            <div class="brand">
                <div class="logo-hero">
                    <div class="logo-glow"></div>
                    <div class="logo-glow-2"></div>
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="login-logo">
                </div>
                <h2>Cabinet Médical</h2>
                <p class="subtitle">Plateforme intelligente de gestion des cabinets médicaux</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate-fade-in">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-fade-in">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show animate-fade-in">
                <i class="bi bi-exclamation-circle"></i> {{ $errors->first('email') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope-fill"></i> Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="votre@email.com" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-lock-fill"></i> Mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="text-end mb-3">
                    <a href="{{ route('password.forgot') }}" class="forgot-link">Mot de passe oublié ?</a>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </button>
                </div>
            </form>

            <div class="demo-accounts text-center">
                <p class="mb-1"><i class="bi bi-info-circle"></i> Comptes de démonstration</p>
                <small>
                    admin@medical.com / password<br>
                    medecin1@medical.com / password<br>
                    reception@medical.com / password
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
