<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Escaner Total') }}</title>

    <!-- PWA (bloqueo de orientación al instalar en pantalla de inicio) -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#008c3a">

    <!-- Inicializar modo oscuro antes de que Vue cargue -->
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('darkMode');
                let isDark = false;

                if (saved !== null) {
                    isDark = saved === 'true';
                } else if (window.matchMedia) {
                    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                }

                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                console.error('Error initializing dark mode:', e);
            }
        })();
    </script>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
