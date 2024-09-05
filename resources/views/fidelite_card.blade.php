<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte de Fidélité</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            border: 1px solid #000;
            padding: 20px;
            width: 350px;
            margin: 0 auto;
        }
        .title {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .info {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .qr-code {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Carte de Fidélité</div>
        <img src="{{ $photoUrl }}" alt="Photo du client" class="photo">
        <div class="info">
            <strong>Pseudo:</strong> {{ $pseudo }}<br>
            <strong>Email:</strong> {{ $email }}<br>
        </div>
        <div class="qr-code">
            <img src="{{ storage_path('app/' . $qrCodePath) }}" alt="QR Code">
        </div>
    </div>
</body>
</html>
