<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'MedidaTek') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @php
            $hasViteAssets = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
        @endphp
        @if ($hasViteAssets && !app()->environment('testing'))
            @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @endif
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @if ($hasViteAssets)
            @inertia
        @else
            <div class="min-h-screen flex items-center justify-center p-6">
                <div class="max-w-xl w-full rounded-lg border border-gray-200 bg-white p-6">
                    <div class="text-lg font-semibold text-gray-900">{{ config('app.name', 'MedidaTek') }}</div>
                    <div class="mt-2 text-sm text-gray-600">Os assets do front-end ainda não foram compilados no servidor.</div>
                </div>
            </div>
        @endif
    </body>
</html>
