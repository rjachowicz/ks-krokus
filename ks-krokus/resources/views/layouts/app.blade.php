<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    @include('partials.head')
</head>
<body class="public-body @yield('body_class')">
<a class="skip-link" href="#main-content">Przejdź do treści</a>

@include('partials.header')
@include('partials.toasts')

<main id="main-content" class="public-main">
    @yield('content')
</main>

@include('partials.footer')

@stack('modals')
@stack('scripts')
</body>
</html>
