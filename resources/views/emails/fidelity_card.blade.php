<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte de Fidélité</title>
</head>
<body>
    <h1>Bonjour {{ $clientName }} !</h1>
    <p>Félicitations, votre compte a été créé avec succès.</p>
    <p>Vous trouverez ci-joint votre carte de fidélité avec vos informations.</p>
    <ul>
        <li><strong>Pseudo :</strong> {{ $pseudo }}</li>
        <li><strong>Email :</strong> {{ $email }}</li>
    </ul>
    <p>Merci de votre confiance et à bientôt !</p>
</body>
</html>
