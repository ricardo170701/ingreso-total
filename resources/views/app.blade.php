<!DOCTYPE html>
<html lang="es" translate="no">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google" content="notranslate">
    <meta http-equiv="Content-Language" content="es">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'acceso gob meta') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="shortcut icon" href="/images/favicon-32x32.png">

    <!-- PWA (bloqueo de orientación al instalar en pantalla de inicio) -->
    <link rel="manifest" href="/manifest.webmanifest?v={{ config('app.version', time()) }}">
    <meta name="theme-color" content="#008c3a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="acceso gob meta">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon-180.png">

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
