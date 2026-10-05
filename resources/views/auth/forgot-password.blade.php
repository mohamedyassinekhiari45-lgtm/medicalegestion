<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion Médicale</title>
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
        <div class="login-card animate-fade-up">
            <div class="brand">
                <i class="bi bi-heart-pulse-fill"></i>
                <h2>Mot de passe oublié</h2>
                <p>Entrez votre email pour réinitialiser votre mot de passe</p>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle"></i> {{ $errors->first('email') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('password.forgot') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $email }}" placeholder="votre@email.com" required>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send"></i> Envoyer
                    </button>
                </div>
            </form>

            <div class="text-center">
                @if(Auth::check())
                <a href="{{ route('profile') }}" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Retour au profil
                </a>
                @else
                <a href="{{ route('login') }}" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Retour à la connexion
                </a>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
