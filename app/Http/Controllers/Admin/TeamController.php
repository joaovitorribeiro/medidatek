<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderByDesc('id')
            ->simplePaginate(25, ['id', 'name', 'email', 'is_admin', 'is_staff', 'created_at'])
            ->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_admin' => (bool) $u->is_admin,
                'is_staff' => (bool) $u->is_staff,
                'created_at' => $u->created_at?->toISOString(),
            ])
            ->withQueryString();

        return Inertia::render('Admin/Team/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:socio,admin'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $name = $this->nameFromEmail($email);

        $isAdmin = $data['role'] === 'admin';

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_staff' => true,
            'is_admin' => $isAdmin,
        ]);

        return redirect()->route('admin.team.index');
    }

    public function updatePassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user->update([
            'password' => $data['password'],
        ]);

        return redirect()->route('admin.team.index');
    }

    public function updateAdmin(Request $request, User $user)
    {
        $data = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        $acting = $request->user();
        if ($acting && $acting->id === $user->id && !$data['is_admin']) {
            throw ValidationException::withMessages([
                'is_admin' => 'Você não pode remover seu próprio acesso de admin.',
            ]);
        }

        if (!$data['is_admin']) {
            $adminCount = User::query()->where('is_admin', true)->count();
            if ($user->is_admin && $adminCount <= 1) {
                throw ValidationException::withMessages([
                    'is_admin' => 'É necessário manter pelo menos 1 admin.',
                ]);
            }
        }

        $updates = [
            'is_admin' => (bool) $data['is_admin'],
        ];
        if ($data['is_admin']) {
            $updates['is_staff'] = true;
        }

        $user->update($updates);

        return redirect()->route('admin.team.index');
    }

    private function nameFromEmail(string $email): string
    {
        $local = explode('@', $email)[0] ?? '';
        $local = preg_replace('/[^a-z0-9._-]+/i', ' ', (string) $local);
        $local = str_replace(['.', '_', '-'], ' ', (string) $local);
        $local = trim(preg_replace('/\s+/', ' ', (string) $local));
        if ($local === '') {
            return 'Membro';
        }
        return mb_convert_case($local, MB_CASE_TITLE, 'UTF-8');
    }
}
