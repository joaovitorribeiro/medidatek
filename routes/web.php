<?php

use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $proofLinks = Project::query()
        ->where('is_published', true)
        ->orderBy('sort_order')
        ->orderByDesc('id')
        ->limit(12)
        ->get(['name', 'url', 'tag', 'note'])
        ->map(fn (Project $p) => [
            'name' => $p->name,
            'url' => $p->url,
            'tag' => $p->tag,
            'note' => $p->note,
        ])
        ->values();

    return Inertia::render('Landing', [
        'proofLinks' => $proofLinks,
    ]);
})->name('landing');

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

Route::post('/ai/chat', [AiChatController::class, 'chat'])
    ->middleware('throttle:30,1')
    ->name('ai.chat');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
        Route::get('/ia', [AiDashboardController::class, 'index'])->name('admin.ai');
    });

require __DIR__.'/auth.php';
