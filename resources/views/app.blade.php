<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Geatel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <!-- AG Grid CSS (local) -->

    <link rel="icon" href="https://geatel-telecom.com/wp-content/uploads/2023/07/ISOTIPO.png" sizes="32x32">
    <link rel="icon" href="https://geatel-telecom.com/wp-content/uploads/2023/07/ISOTIPO.png" sizes="192x192">
    <link rel="apple-touch-icon" href="https://geatel-telecom.com/wp-content/uploads/2023/07/ISOTIPO.png">
    <meta name="msapplication-TileImage" content="https://geatel-telecom.com/wp-content/uploads/2023/07/ISOTIPO.png">
    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>