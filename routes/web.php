<?php

use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\LandingBentoCardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Models\LandingBentoCard;
use App\Models\LegalDocument;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/sitemap.xml', function () {
    $lastmod = now()->toDateString();
    $urls = [
        [
            'loc' => route('landing'),
            'lastmod' => $lastmod,
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ],
        [
            'loc' => route('legal.privacy'),
            'lastmod' => $lastmod,
            'changefreq' => 'yearly',
            'priority' => '0.2',
        ],
        [
            'loc' => route('legal.terms'),
            'lastmod' => $lastmod,
            'changefreq' => 'yearly',
            'priority' => '0.2',
        ],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $u) {
        $loc = htmlspecialchars($u['loc'], ENT_XML1);
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$u['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>'."\n";

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

$resolveMediaFilePath = function (string $relativePath): ?string {
    $relativePath = ltrim($relativePath, '/');

    $relativeCandidates = [$relativePath];

    if (Str::startsWith($relativePath, 'projects/')) {
        $suffix = substr($relativePath, strlen('projects/'));
        $relativeCandidates[] = 'projetos/'.$suffix;
        $relativeCandidates[] = 'midia/projetos/'.$suffix;
        $relativeCandidates[] = 'midia/projects/'.$suffix;
    }

    if (Str::startsWith($relativePath, 'landing/bento/')) {
        $suffix = substr($relativePath, strlen('landing/bento/'));
        $relativeCandidates[] = 'midia/landing/bento/'.$suffix;
    }

    foreach (array_unique($relativeCandidates) as $candidateRelativePath) {
        if (!is_string($candidateRelativePath) || $candidateRelativePath === '') {
            continue;
        }

        $candidates = [
            Storage::disk('public')->path($candidateRelativePath),
            public_path('storage/'.$candidateRelativePath),
            public_path($candidateRelativePath),
        ];

        if (!Str::startsWith($candidateRelativePath, 'midia/')) {
            $candidates[] = public_path('midia/'.$candidateRelativePath);
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }
    }

    return null;
};

$mediaFileExists = fn (string $relativePath): bool => $resolveMediaFilePath($relativePath) !== null;

$detectImageMime = function (string $fullPath, string $fallbackName): string {
    $mime = @mime_content_type($fullPath);
    if (is_string($mime) && $mime !== '' && $mime !== 'application/octet-stream') {
        return $mime;
    }

    $ext = strtolower(pathinfo($fallbackName, PATHINFO_EXTENSION));

    return match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };
};

$legacyUnsplashMap = [
    'photo-2JJ3wBHu4_0' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=1200&q=80',
    'photo-Ib2e4-Qy9mQ' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
    'photo-zips8ILZd04' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
    'photo-qaedPly-Uro' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
    'photo-BlWbfrQrI5k' => 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&fit=crop&w=1200&q=80',
    'photo-RMIsZlv8qv4' => 'https://images.unsplash.com/photo-1484417894907-623942c8ee29?auto=format&fit=crop&w=1200&q=80',
];

$remapLegacyExternalImageUrl = function (string $imageUrl) use ($legacyUnsplashMap): string {
    foreach ($legacyUnsplashMap as $legacyFragment => $replacementUrl) {
        if (Str::contains($imageUrl, $legacyFragment)) {
            return $replacementUrl;
        }
    }

    return $imageUrl;
};

$ensureUnsplashJpeg = function (string $imageUrl): string {
    $parts = parse_url($imageUrl);
    if (!$parts || !isset($parts['host']) || strtolower($parts['host']) !== 'images.unsplash.com') {
        return $imageUrl;
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    $query['fm'] = 'jpg';
    $query['auto'] = 'format';
    $query['q'] = $query['q'] ?? '80';
    $query['w'] = $query['w'] ?? '1200';

    $rebuilt = ($parts['scheme'] ?? 'https').'://'.$parts['host'].($parts['path'] ?? '');
    $rebuilt .= '?'.http_build_query($query);

    return $rebuilt;
};

$encodeBase64Url = function (string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
};

$decodeBase64Url = function (string $value): ?string {
    $padding = (4 - (strlen($value) % 4)) % 4;
    $decoded = base64_decode(strtr($value.str_repeat('=', $padding), '-_', '+/'), true);

    return is_string($decoded) && $decoded !== '' ? $decoded : null;
};

$resolvePublicImageSrc = function (?string $imageUrl, string $mediaRouteName, string $storagePrefix, bool $allowExternal = true) use ($remapLegacyExternalImageUrl, $ensureUnsplashJpeg, $encodeBase64Url, $mediaFileExists): ?string {
    $imageUrl = $imageUrl ? trim($imageUrl) : null;
    if (!$imageUrl) {
        return null;
    }

    if (Str::startsWith($imageUrl, ['http://', 'https://'])) {
        if (!$allowExternal) {
            $host = strtolower((string) parse_url($imageUrl, PHP_URL_HOST));
            $path = (string) parse_url($imageUrl, PHP_URL_PATH);
            $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
            $requestHost = strtolower((string) request()->getHost());

            $isSameHost = $host !== '' && ($host === $requestHost || ($appHost !== '' && $host === $appHost));
            if (!$isSameHost || $path === '') {
                return null;
            }

            $imageUrl = $path;
        }

        $imageUrl = $remapLegacyExternalImageUrl($imageUrl);
        $imageUrl = $ensureUnsplashJpeg($imageUrl);
        $host = strtolower(parse_url($imageUrl, PHP_URL_HOST) ?? '');
        if ($host === 'images.unsplash.com') {
            return route('media.remote', ['encoded' => $encodeBase64Url($imageUrl)], absolute: false);
        }

        return $imageUrl;
    }

    if (Str::startsWith($imageUrl, ['/midia/', 'midia/'])) {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);
        $filename = basename($path !== '' ? $path : $imageUrl);
        $relative = $storagePrefix.$filename;
        if (!$mediaFileExists($relative)) {
            return null;
        }

        return '/'.ltrim($imageUrl, '/');
    }

    $normalized = ltrim($imageUrl, '/');
    if (Str::startsWith($normalized, [$storagePrefix, 'storage/'.$storagePrefix])) {
        if (Str::startsWith($normalized, 'storage/'.$storagePrefix)) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        if (!$mediaFileExists($normalized)) {
            return null;
        }

        return route($mediaRouteName, ['filename' => basename($normalized)], absolute: false);
    }

    if (!$mediaFileExists($normalized)) {
        return null;
    }

    return Storage::disk('public')->url($normalized);
};

$extractRelativeStoragePath = function (?string $imageUrl, string $storagePrefix): ?string {
    $imageUrl = $imageUrl ? trim($imageUrl) : null;
    if (!$imageUrl || Str::startsWith($imageUrl, ['http://', 'https://'])) {
        return null;
    }

    $normalized = ltrim($imageUrl, '/');
    if (Str::startsWith($normalized, 'storage/'.$storagePrefix)) {
        $normalized = substr($normalized, strlen('storage/'));
    } elseif (Str::startsWith($normalized, 'midia/')) {
        $normalized = $storagePrefix.basename($normalized);
    }

    return Str::startsWith($normalized, $storagePrefix) ? $normalized : null;
};

$buildMobileVariantPath = function (string $relativePath): string {
    $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
    $filename = pathinfo($relativePath, PATHINFO_FILENAME);
    $directory = pathinfo($relativePath, PATHINFO_DIRNAME);
    $directory = $directory === '.' ? '' : $directory.'/';

    return $directory.$filename.'-sm'.($extension ? '.'.$extension : '');
};

$resolveResponsiveImage = function (?string $imageUrl, string $mediaRouteName, string $storagePrefix, string $sizes = '100vw', bool $allowExternal = true) use ($resolvePublicImageSrc, $extractRelativeStoragePath, $buildMobileVariantPath, $mediaFileExists): array {
    $src = $resolvePublicImageSrc($imageUrl, $mediaRouteName, $storagePrefix, $allowExternal);
    if (!$src) {
        return ['src' => null, 'srcset' => null, 'sizes' => null];
    }

    $relativePath = $extractRelativeStoragePath($imageUrl, $storagePrefix);
    if (!$relativePath) {
        return ['src' => $src, 'srcset' => null, 'sizes' => null];
    }

    $mobileVariant = $buildMobileVariantPath($relativePath);
    if (!$mediaFileExists($mobileVariant)) {
        return ['src' => $src, 'srcset' => null, 'sizes' => null];
    }

    $mobileSrc = route($mediaRouteName, ['filename' => basename($mobileVariant)], absolute: false);

    return [
        'src' => $src,
        'srcset' => $mobileSrc.' 640w, '.$src.' 1200w',
        'sizes' => $sizes,
    ];
};

Route::get('/', function () use ($resolveResponsiveImage) {
    $resolveBentoImage = fn (?string $imageUrl): array => $resolveResponsiveImage(
        $imageUrl,
        'media.landing.bento',
        'landing/bento/',
        '(max-width: 768px) 100vw, (max-width: 1280px) 50vw, 1200px',
        false,
    );
    $resolveProjectImage = fn (?string $imageUrl): array => $resolveResponsiveImage(
        $imageUrl,
        'media.projects',
        'projects/',
        '(max-width: 768px) 88vw, (max-width: 1280px) 420px, 420px',
        false,
    );

    $bentoImages = collect([
        'architecture' => ['src' => '/og/medidatek-og.png', 'alt' => 'Arquitetura escalável'],
        'speed' => ['src' => '/og/medidatek-og.png', 'alt' => 'Velocidade e performance'],
        'ai' => ['src' => '/og/medidatek-og.png', 'alt' => 'Inteligência artificial'],
        'design' => ['src' => '/og/medidatek-og.png', 'alt' => 'Design system'],
        'mobile' => ['src' => '/og/medidatek-og.png', 'alt' => 'Mobile-first'],
        'security' => ['src' => '/og/medidatek-og.png', 'alt' => 'Segurança e autenticação'],
    ]);

    if (Schema::hasTable('landing_bento_cards')) {
        $bentoImages = LandingBentoCard::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['key', 'image_url', 'alt'])
            ->mapWithKeys(function (LandingBentoCard $c) use ($resolveBentoImage) {
                $image = $resolveBentoImage($c->image_url);

                return [
                    $c->key => [
                        'src' => $image['src'],
                        'srcset' => $image['srcset'],
                        'sizes' => $image['sizes'],
                        'alt' => $c->alt,
                    ],
                ];
            });
    }

    $proofLinks = collect();
    if (Schema::hasTable('projects')) {
        $proofLinks = Project::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get(['name', 'url', 'image_url', 'image_alt', 'tag', 'note'])
            ->map(function (Project $p) use ($resolveProjectImage) {
                $image = $resolveProjectImage($p->image_url);

                return [
                    'name' => $p->name,
                    'url' => $p->url,
                    'image_url' => $p->image_url,
                    'image_src' => $image['src'],
                    'image_srcset' => $image['srcset'],
                    'image_sizes' => $image['sizes'],
                    'image_alt' => $p->image_alt,
                    'tag' => $p->tag,
                    'note' => $p->note,
                ];
            })
            ->values();
    }

    return Inertia::render('Landing', [
        'bentoImages' => $bentoImages,
        'proofLinks' => $proofLinks,
    ]);
})->name('landing');

$serveLandingBento = function (string $filename) use ($resolveMediaFilePath, $detectImageMime) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
        abort(404);
    }

    $relative = 'landing/bento/'.$filename;
    $fullPath = $resolveMediaFilePath($relative);
    if (!$fullPath) {
        abort(404);
    }

    $mime = $detectImageMime($fullPath, $filename);

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
};

Route::get('/midia/landing/bento/{filename}', $serveLandingBento)->name('media.landing.bento');
Route::get('/storage/landing/bento/{filename}', $serveLandingBento);

$serveProjectImage = function (string $filename) use ($resolveMediaFilePath, $detectImageMime) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
        abort(404);
    }

    $relative = 'projects/'.$filename;
    $fullPath = $resolveMediaFilePath($relative);
    if (!$fullPath) {
        abort(404);
    }

    $mime = $detectImageMime($fullPath, $filename);

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
};

Route::get('/midia/projetos/{filename}', $serveProjectImage)->name('media.projects');
Route::get('/midia/projects/{filename}', $serveProjectImage);
Route::get('/storage/projects/{filename}', $serveProjectImage);
Route::get('/storage/projetos/{filename}', $serveProjectImage);

$serveRemoteImage = function (string $encoded) use ($decodeBase64Url, $remapLegacyExternalImageUrl, $ensureUnsplashJpeg) {
    if (!preg_match('/^[A-Za-z0-9\-_]+$/', $encoded)) {
        abort(404);
    }

    $decoded = $decodeBase64Url($encoded);
    if (!$decoded || !filter_var($decoded, FILTER_VALIDATE_URL)) {
        abort(404);
    }

    $targetUrl = $ensureUnsplashJpeg($remapLegacyExternalImageUrl($decoded));
    $scheme = strtolower(parse_url($targetUrl, PHP_URL_SCHEME) ?? '');
    $host = strtolower(parse_url($targetUrl, PHP_URL_HOST) ?? '');
    if ($scheme !== 'https' || $host !== 'images.unsplash.com') {
        abort(404);
    }

    $disk = Storage::disk('public');
    $cacheBase = 'landing/remote-cache';
    $cacheKey = sha1($targetUrl);
    $metaPath = $cacheBase.'/'.$cacheKey.'.json';

    if ($disk->exists($metaPath)) {
        $meta = json_decode($disk->get($metaPath), true);
        $cachedPath = is_array($meta) ? ($meta['path'] ?? null) : null;
        $cachedMime = is_array($meta) ? ($meta['mime'] ?? null) : null;

        if (is_string($cachedPath) && $disk->exists($cachedPath)) {
            return response()->file($disk->path($cachedPath), [
                'Content-Type' => $cachedMime ?: 'image/jpeg',
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Content-Disposition' => 'inline; filename="'.basename($cachedPath).'"',
            ]);
        }
    }

    $response = Http::timeout(15)->retry(1, 200)->get($targetUrl);
    if (!$response->successful()) {
        abort(404);
    }

    $mime = strtolower(trim(explode(';', $response->header('Content-Type') ?? '')[0] ?? ''));
    if (!Str::startsWith($mime, 'image/')) {
        abort(404);
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/gif' => 'gif',
        default => 'img',
    };

    $cachedPath = $cacheBase.'/'.$cacheKey.'.'.$ext;
    $disk->put($cachedPath, $response->body());
    $disk->put($metaPath, json_encode(['path' => $cachedPath, 'mime' => $mime], JSON_UNESCAPED_SLASHES));

    return response()->file($disk->path($cachedPath), [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.basename($cachedPath).'"',
    ]);
};

Route::get('/midia/remota/{encoded}', $serveRemoteImage)->name('media.remote');

Route::get('/politica-de-privacidade', function () {
    $title = 'Política de Privacidade';
    $content = '';
    $updatedAt = null;

    if (Schema::hasTable('legal_documents')) {
        $doc = LegalDocument::query()
            ->where('key', 'privacy')
            ->where('is_published', true)
            ->first();

        if ($doc) {
            $title = $doc->title;
            $content = $doc->content;
            $updatedAt = $doc->updated_at?->toISOString();
        }
    }

    return Inertia::render('Legal', [
        'title' => $title,
        'content' => $content,
        'updated_at' => $updatedAt,
    ]);
})->name('legal.privacy');

Route::get('/termos-de-uso', function () {
    $title = 'Termos de Uso';
    $content = '';
    $updatedAt = null;

    if (Schema::hasTable('legal_documents')) {
        $doc = LegalDocument::query()
            ->where('key', 'terms')
            ->where('is_published', true)
            ->first();

        if ($doc) {
            $title = $doc->title;
            $content = $doc->content;
            $updatedAt = $doc->updated_at?->toISOString();
        }
    }

    return Inertia::render('Legal', [
        'title' => $title,
        'content' => $content,
        'updated_at' => $updatedAt,
    ]);
})->name('legal.terms');

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

Route::post('/ia/chat', [AiChatController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('ai.chat');

Route::post('/ai/chat', [AiChatController::class, 'chat'])
    ->middleware('throttle:30,1');

Route::get('/painel', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    });

    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});

Route::middleware(['auth', 'verified', 'admin_or_staff'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.projects.index');
        })->name('admin.home');

        Route::get('/projetos', [ProjectController::class, 'index'])->name('admin.projects.index');
        Route::get('/projetos/novo', [ProjectController::class, 'create'])->middleware('admin')->name('admin.projects.create');
        Route::post('/projetos', [ProjectController::class, 'store'])->middleware('admin')->name('admin.projects.store');
        Route::get('/projetos/{project}/editar', [ProjectController::class, 'edit'])->middleware('admin')->name('admin.projects.edit');
        Route::put('/projetos/{project}', [ProjectController::class, 'update'])->middleware('admin')->name('admin.projects.update');
        Route::delete('/projetos/{project}', [ProjectController::class, 'destroy'])->middleware('admin')->name('admin.projects.destroy');
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('admin.leads.index');
        Route::get('/ia', [AiDashboardController::class, 'index'])->name('admin.ai');
        Route::get('/site/imagens', [LandingBentoCardController::class, 'edit'])->name('admin.landing.bento.edit');
        Route::put('/site/imagens', [LandingBentoCardController::class, 'update'])->middleware('admin')->name('admin.landing.bento.update');
        Route::post('/site/imagens/{card}', [LandingBentoCardController::class, 'save'])->middleware('admin')->name('admin.landing.bento.card.save');

        Route::get('/equipe', [TeamController::class, 'index'])->middleware('admin')->name('admin.team.index');
        Route::post('/equipe', [TeamController::class, 'store'])->middleware('admin')->name('admin.team.store');
        Route::put('/equipe/{user}/senha', [TeamController::class, 'updatePassword'])->middleware('admin')->name('admin.team.password.update');
        Route::patch('/equipe/{user}/admin', [TeamController::class, 'updateAdmin'])->middleware('admin')->name('admin.team.admin.update');

        Route::get('/landing/imagens', [LandingBentoCardController::class, 'edit']);
        Route::put('/landing/imagens', [LandingBentoCardController::class, 'update'])->middleware('admin');
        Route::post('/landing/imagens/{card}', [LandingBentoCardController::class, 'save'])->middleware('admin');
    });

require __DIR__.'/auth.php';
