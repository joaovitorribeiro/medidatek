<?php

use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\Admin\LandingBentoCardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Models\LandingBentoCard;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', function () {
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
            ->mapWithKeys(fn (LandingBentoCard $c) => [
                $c->key => [
                    'src' => Str::startsWith($c->image_url, ['http://', 'https://'])
                        ? $c->image_url
                        : (Str::startsWith($c->image_url, 'landing/bento/')
                            ? route('media.landing.bento', ['filename' => basename($c->image_url)], absolute: false)
                            : Storage::disk('public')->url($c->image_url)),
                    'alt' => $c->alt,
                ],
            ]);
    }

    $proofLinks = collect();
    if (Schema::hasTable('projects')) {
        $proofLinks = Project::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get(['name', 'url', 'image_url', 'image_alt', 'tag', 'note'])
            ->map(fn (Project $p) => [
                'name' => $p->name,
                'url' => $p->url,
                'image_url' => $p->image_url,
                'image_src' => $p->image_url
                    ? (Str::startsWith($p->image_url, ['http://', 'https://'])
                        ? $p->image_url
                        : (Str::startsWith($p->image_url, 'projects/')
                            ? route('media.projects', ['filename' => basename($p->image_url)], absolute: false)
                            : Storage::disk('public')->url($p->image_url)))
                    : null,
                'image_alt' => $p->image_alt,
                'tag' => $p->tag,
                'note' => $p->note,
                ])
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
    $mime = Storage::disk('public')->mimeType($relative) ?: 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
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
    $mime = Storage::disk('public')->mimeType($relative) ?: 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
};

Route::get('/midia/projetos/{filename}', $serveProjectImage)->name('media.projects');
Route::get('/storage/projects/{filename}', $serveProjectImage);

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

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.projects.index');
        })->name('admin.home');

        Route::get('/projetos', [ProjectController::class, 'index'])->name('admin.projects.index');
        Route::get('/projetos/novo', [ProjectController::class, 'create'])->name('admin.projects.create');
        Route::post('/projetos', [ProjectController::class, 'store'])->name('admin.projects.store');
        Route::get('/projetos/{project}/editar', [ProjectController::class, 'edit'])->name('admin.projects.edit');
        Route::put('/projetos/{project}', [ProjectController::class, 'update'])->name('admin.projects.update');
        Route::delete('/projetos/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
        Route::get('/leads', [AdminLeadController::class, 'index'])->name('admin.leads.index');
        Route::get('/ia', [AiDashboardController::class, 'index'])->name('admin.ai');
        Route::get('/site/imagens', [LandingBentoCardController::class, 'edit'])->name('admin.landing.bento.edit');
        Route::put('/site/imagens', [LandingBentoCardController::class, 'update'])->name('admin.landing.bento.update');
        Route::post('/site/imagens/{card}', [LandingBentoCardController::class, 'save'])->name('admin.landing.bento.card.save');

        Route::get('/landing/imagens', [LandingBentoCardController::class, 'edit']);
        Route::put('/landing/imagens', [LandingBentoCardController::class, 'update']);
        Route::post('/landing/imagens/{card}', [LandingBentoCardController::class, 'save']);
    });

require __DIR__.'/auth.php';
