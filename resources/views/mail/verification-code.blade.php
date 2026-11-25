<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $purpose === \App\Enums\CodePurpose::EmailChange ? 'Tu código para cambiar el correo' : 'Tu código para recuperar la contraseña' }}</title>
</head>
<body>
    <p>{{ $purpose === \App\Enums\CodePurpose::EmailChange ? 'Usá este código para confirmar tu correo nuevo en el panel:' : 'Usá este código para crear una contraseña nueva:' }}</p>
    <p style="font-size: 28px; letter-spacing: 6px; font-weight: 700">{{ $code }}</p>
    <p>Vence en 10 minutos. Si no fuiste vos, ignorá este mensaje.</p>
</body>
</html>
