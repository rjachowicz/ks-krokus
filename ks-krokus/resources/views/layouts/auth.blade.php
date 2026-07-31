<!DOCTYPE html>
<html lang="pl" data-theme="light" class="no-js">
<head>
    @section('robots', 'noindex, nofollow, noarchive')
    @include('partials.head')
</head>
<body>
    <a class="skip-link" href="#auth-main-content">Przejdź do formularza logowania</a>
    <main id="auth-main-content" class="auth-page" tabindex="-1">
        @yield('content')
    </main>
</body>
</html>
