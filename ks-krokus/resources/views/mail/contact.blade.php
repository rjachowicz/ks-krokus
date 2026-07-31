<!DOCTYPE html>
<html lang="pl">
<body>
    <h1>Nowa wiadomość z formularza kontaktowego</h1>
    <p><strong>Imię i nazwisko:</strong> {{ $formData['name'] }}</p>
    <p><strong>E-mail:</strong> {{ $formData['email'] }}</p>
    <p><strong>Telefon:</strong> {{ $formData['phone'] ?: 'Nie podano' }}</p>
    <p><strong>Temat:</strong> {{ $formData['subject'] }}</p>
    <h2>Wiadomość</h2>
    <p>{!! nl2br(e($formData['message'])) !!}</p>
</body>
</html>
