<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation de mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Réinitialisation de mot de passe</h2>
        <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
        <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('password.reset', $token) }}" style="background: #0d6efd; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px;">
                Réinitialiser mon mot de passe
            </a>
        </p>
        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
        <p>Ce lien expirera dans 60 minutes.</p>
        <hr>
        <p style="color: #6c757d; font-size: 12px;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
