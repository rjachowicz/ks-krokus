<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    @include('partials.head')
</head>
<body class="@yield('body_class')">
<a class="skip-link" href="#main-content">Przejdź do treści</a>

@include('partials.header')

<main id="main-content">
    @yield('content')
</main>

@include('partials.footer')

@stack('modals')
@stack('scripts')
</body>
</html>
