<?php

use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
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

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/ia', [AiDashboardController::class, 'index'])->name('admin.ai');
    });

require __DIR__.'/auth.php';
