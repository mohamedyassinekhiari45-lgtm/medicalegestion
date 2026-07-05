<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation de changement d'email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Confirmation de changement d'email</h2>
        <p>Vous avez demandé le changement de votre adresse email vers <strong>{{ $newEmail }}</strong>.</p>
        <p>Voici votre code de confirmation :</p>
        <p style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; background: #f8f9fa; padding: 15px 30px; border-radius: 5px; border: 1px solid #dee2e6;">
                {{ $code }}
            </span>
        </p>
        <p>Copiez ce code et collez-le dans le champ de vérification sur la page profil.</p>
        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
        <hr>
        <p style="color: #6c757d; font-size: 12px;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
