<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:ensure-admin', function () {
    $email = env('ADMIN_EMAIL');
    $password = env('ADMIN_PASSWORD');
    $name = env('ADMIN_NAME', 'Admin');

    if (!$email || !$password) {
        $this->error('Missing ADMIN_EMAIL or ADMIN_PASSWORD.');
        return 1;
    }

    $user = User::query()->firstOrNew(['email' => $email]);
    $user->name = $user->exists ? $user->name : $name;
    $user->password = Hash::make($password);
    $user->is_admin = true;
    $user->email_verified_at = $user->email_verified_at ?: now();
    $user->save();

    $this->info("Admin ensured: {$user->email}");
    return 0;
})->purpose('Create or update the admin user from env vars');
