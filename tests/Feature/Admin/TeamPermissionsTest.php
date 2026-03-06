<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_team_page_and_create_user(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_staff' => true,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.team.index', absolute: false))
            ->assertOk();

        $this
            ->actingAs($admin)
            ->post(route('admin.team.store', absolute: false), [
                'role' => 'socio',
                'email' => 'socio@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.team.index', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'socio@example.com',
            'is_admin' => false,
            'is_staff' => true,
        ]);
    }

    public function test_staff_without_admin_is_forbidden_from_team_page(): void
    {
        $staff = User::factory()->create([
            'is_admin' => false,
            'is_staff' => true,
        ]);

        $this
            ->actingAs($staff)
            ->get(route('admin.team.index', absolute: false))
            ->assertForbidden();
    }
}
