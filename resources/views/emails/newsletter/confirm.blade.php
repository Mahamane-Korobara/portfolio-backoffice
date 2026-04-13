<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation newsletter</title>
</head>
<body>
    <p>Bonjour{{ $subscriber->name ? ' ' . $subscriber->name : '' }},</p>

    <p>Merci pour votre inscription a la newsletter. Veuillez confirmer votre abonnement en cliquant sur le lien ci-dessous :</p>

    <p><a href="{{ $confirmUrl }}">Confirmer mon abonnement</a></p>

    <p>Si vous n'etes pas a l'origine de cette demande, vous pouvez ignorer cet email.</p>

    <hr>

    <p>Vous pouvez vous desabonner a tout moment ici :</p>
    <p><a href="{{ $unsubscribeUrl }}">Se desabonner</a></p>
</body>
</html>
