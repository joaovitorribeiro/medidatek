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

$resolvePublicImageSrc = function (?string $imageUrl, string $mediaRouteName, string $storagePrefix): ?string {
    $imageUrl = $imageUrl ? trim($imageUrl) : null;
    if (!$imageUrl) {
        return null;
    }

    if (Str::startsWith($imageUrl, ['http://', 'https://'])) {
        return $imageUrl;
    }

    if (Str::startsWith($imageUrl, ['/midia/', 'midia/'])) {
        return '/'.ltrim($imageUrl, '/');
    }

    $normalized = ltrim($imageUrl, '/');
    if (Str::startsWith($normalized, [$storagePrefix, 'storage/'.$storagePrefix])) {
        return route($mediaRouteName, ['filename' => basename($normalized)], absolute: false);
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

$resolveResponsiveImage = function (?string $imageUrl, string $mediaRouteName, string $storagePrefix, string $sizes = '100vw') use ($resolvePublicImageSrc, $extractRelativeStoragePath, $buildMobileVariantPath): array {
    $src = $resolvePublicImageSrc($imageUrl, $mediaRouteName, $storagePrefix);
    if (!$src) {
        return ['src' => null, 'srcset' => null, 'sizes' => null];
    }

    $relativePath = $extractRelativeStoragePath($imageUrl, $storagePrefix);
    if (!$relativePath) {
        return ['src' => $src, 'srcset' => null, 'sizes' => null];
    }

    $mobileVariant = $buildMobileVariantPath($relativePath);
    if (!Storage::disk('public')->exists($mobileVariant)) {
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
    );
    $resolveProjectImage = fn (?string $imageUrl): array => $resolveResponsiveImage(
        $imageUrl,
        'media.projects',
        'projects/',
        '(max-width: 768px) 88vw, (max-width: 1280px) 420px, 420px',
    );

    $bentoImages = collect([
        'architecture' => ['src' => 'https://images.unsplash.com/photo-2JJ3wBHu4_0?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Arquitetura escalável'],
        'speed' => ['src' => 'https://images.unsplash.com/photo-Ib2e4-Qy9mQ?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Velocidade e performance'],
        'ai' => ['src' => 'https://images.unsplash.com/photo-zips8ILZd04?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Inteligência artificial'],
        'design' => ['src' => 'https://images.unsplash.com/photo-qaedPly-Uro?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Design system'],
        'mobile' => ['src' => 'https://images.unsplash.com/photo-BlWbfrQrI5k?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Mobile-first'],
        'security' => ['src' => 'https://images.unsplash.com/photo-RMIsZlv8qv4?q=80&w=1200&auto=format&fit=crop', 'alt' => 'Segurança e autenticação'],
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

$serveLandingBento = function (string $filename) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
        abort(404);
    }

    $relative = 'landing/bento/'.$filename;
    if (!Storage::disk('public')->exists($relative)) {
        abort(404);
    }

    $fullPath = Storage::disk('public')->path($relative);
    $mime = Storage::disk('public')->mimeType($relative);
    if (!$mime || $mime === 'application/octet-stream') {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
};

Route::get('/midia/landing/bento/{filename}', $serveLandingBento)->name('media.landing.bento');
Route::get('/storage/landing/bento/{filename}', $serveLandingBento);

$serveProjectImage = function (string $filename) {
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
        abort(404);
    }

    $relative = 'projects/'.$filename;
    if (!Storage::disk('public')->exists($relative)) {
        abort(404);
    }

    $fullPath = Storage::disk('public')->path($relative);
    $mime = Storage::disk('public')->mimeType($relative);
    if (!$mime || $mime === 'application/octet-stream') {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
};

Route::get('/midia/projetos/{filename}', $serveProjectImage)->name('media.projects');
Route::get('/storage/projects/{filename}', $serveProjectImage);

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
