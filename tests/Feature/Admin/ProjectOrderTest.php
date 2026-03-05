<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_resolves_sort_order_conflict_by_shifting_existing_projects(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this
            ->actingAs($admin)
            ->post(route('admin.projects.store', absolute: false), [
                'name' => 'Projeto A',
                'url' => 'https://example.com/a',
                'sort_order' => 0,
                'is_published' => true,
            ])
            ->assertRedirect(route('admin.projects.index', absolute: false));

        $this
            ->actingAs($admin)
            ->post(route('admin.projects.store', absolute: false), [
                'name' => 'Projeto B',
                'url' => 'https://example.com/b',
                'sort_order' => 0,
                'is_published' => true,
            ])
            ->assertRedirect(route('admin.projects.index', absolute: false));

        $this->assertSame(1, (int) Project::query()->where('name', 'Projeto A')->value('sort_order'));
        $this->assertSame(0, (int) Project::query()->where('name', 'Projeto B')->value('sort_order'));
    }

    public function test_update_moves_project_and_reorders_the_others(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $a = Project::create([
            'name' => 'Projeto A',
            'url' => 'https://example.com/a',
            'sort_order' => 0,
            'is_published' => true,
        ]);

        $b = Project::create([
            'name' => 'Projeto B',
            'url' => 'https://example.com/b',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $c = Project::create([
            'name' => 'Projeto C',
            'url' => 'https://example.com/c',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this
            ->actingAs($admin)
            ->put(route('admin.projects.update', $a->id, absolute: false), [
                'name' => $a->name,
                'url' => $a->url,
                'sort_order' => 2,
                'is_published' => true,
            ])
            ->assertRedirect(route('admin.projects.index', absolute: false));

        $a->refresh();
        $b->refresh();
        $c->refresh();

        $this->assertSame(2, (int) $a->sort_order);
        $this->assertSame(0, (int) $b->sort_order);
        $this->assertSame(1, (int) $c->sort_order);
    }

    public function test_destroy_compacts_sort_orders(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $a = Project::create([
            'name' => 'Projeto A',
            'url' => 'https://example.com/a',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $b = Project::create([
            'name' => 'Projeto B',
            'url' => 'https://example.com/b',
            'sort_order' => 0,
            'is_published' => true,
        ]);

        $c = Project::create([
            'name' => 'Projeto C',
            'url' => 'https://example.com/c',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this
            ->actingAs($admin)
            ->delete(route('admin.projects.destroy', $b->id, absolute: false))
            ->assertRedirect(route('admin.projects.index', absolute: false));

        $a->refresh();
        $c->refresh();

        $this->assertSame(1, (int) $a->sort_order);
        $this->assertSame(0, (int) $c->sort_order);
    }
}

