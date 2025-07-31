<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @if(config('server-manager.use_frontend'))
            @viteReactRefresh
            @vite(['resources/css/app.css', 'resources/js/app.jsx'], 'vendor/server-manager/build')
        @else
            <script src="{{ asset('vendor/server-manager/app.js') }}" defer></script>
            <link rel="stylesheet" href="{{ asset('vendor/server-manager/app.css') }}">
        @endif
        
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>