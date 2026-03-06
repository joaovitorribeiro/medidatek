<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:ensure-admin', function () {
    $email = strtolower(trim((string) env('ADMIN_EMAIL', '')));
    $password = env('ADMIN_PASSWORD');
    $name = env('ADMIN_NAME', 'Admin');

    if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Missing or invalid ADMIN_EMAIL / ADMIN_PASSWORD.');
        return 1;
    }

    $user = User::query()
        ->whereRaw('LOWER(email) = ?', [$email])
        ->first();

    if (!$user) {
        $user = new User();
        $user->email = $email;
    } else {
        $user->email = $email;
    }

    $user->name = $user->exists ? ($user->name ?: $name) : $name;
    $user->password = Hash::make($password);
    $user->is_admin = true;
    $user->is_staff = true;
    $user->email_verified_at = $user->email_verified_at ?: now();
    $user->save();

    $this->info("Admin ensured: {$user->email}");
    return 0;
})->purpose('Create or update the admin user from env vars');
