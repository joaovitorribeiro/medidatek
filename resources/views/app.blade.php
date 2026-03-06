<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $appName = config('app.name', 'MedidaTek');
            $siteUrl = rtrim(config('app.url') ?: url('/'), '/');
            $currentUrl = url()->current();
            $metaTitle = 'Sistema sob medida para sua operacao | ' . $appName;
            $metaDescription = 'Criamos sistemas sob medida para eliminar planilhas, automatizar processos e escalar seu negocio com mais controle.';
            $metaImage = $siteUrl . '/og/medidatek-og.png';
            $faviconSvg = $siteUrl . '/favicon.svg';
            $faviconPng = $siteUrl . '/favicon-32x32.png';
            $appleTouchIcon = $siteUrl . '/apple-touch-icon.png';
            $structuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $appName,
                'url' => $siteUrl,
                'logo' => $metaImage,
                'description' => $metaDescription,
            ];
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="application-name" content="{{ $appName }}">
        <meta name="apple-mobile-web-app-title" content="{{ $appName }}">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#090e1e">
        <link rel="canonical" href="{{ $currentUrl }}">

        <meta property="og:locale" content="pt_BR">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $appName }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:url" content="{{ $currentUrl }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:image:secure_url" content="{{ $metaImage }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $appName }} - Sistema sob medida para sua operacao">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">

        <link rel="icon" href="{{ $faviconSvg }}" type="image/svg+xml">
        <link rel="icon" href="{{ $faviconPng }}" sizes="32x32" type="image/png">
        <link rel="apple-touch-icon" href="{{ $appleTouchIcon }}" sizes="180x180">
        <link rel="manifest" href="{{ $siteUrl }}/site.webmanifest">

        <title inertia>{{ $metaTitle }}</title>

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
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
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
