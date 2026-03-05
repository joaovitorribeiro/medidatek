<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('entrar', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('entrar', [AuthenticatedSessionController::class, 'store']);

    Route::get('esqueci-senha', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('esqueci-senha', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('redefinir-senha/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('redefinir-senha', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create']);
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('verificar-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verificar-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/notificacao-verificacao', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirmar-senha', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirmar-senha', [ConfirmablePasswordController::class, 'store']);

    Route::put('senha', [PasswordController::class, 'update'])->name('password.update');

    Route::post('sair', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('verify-email', EmailVerificationPromptController::class);
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1']);
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show']);
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
});
