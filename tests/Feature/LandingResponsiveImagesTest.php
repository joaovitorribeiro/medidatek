<?php

namespace Tests\Feature;

use App\Models\LandingBentoCard;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingResponsiveImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_includes_srcset_for_internal_project_and_bento_images(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('projects/demo.webp', 'desktop');
        Storage::disk('public')->put('projects/demo-sm.webp', 'mobile');
        Storage::disk('public')->put('landing/bento/architecture-test.webp', 'desktop');
        Storage::disk('public')->put('landing/bento/architecture-test-sm.webp', 'mobile');

        Project::query()->create([
            'name' => 'Projeto Demo',
            'url' => 'https://example.com',
            'image_url' => 'projects/demo.webp',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        LandingBentoCard::query()
            ->where('key', 'architecture')
            ->update([
                'image_url' => 'landing/bento/architecture-test.webp',
                'alt' => 'Arquitetura teste',
            ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('proofLinks.0.image_srcset', '/midia/projetos/demo-sm.webp 640w, /midia/projetos/demo.webp 1200w')
            ->where('bentoImages.architecture.srcset', '/midia/landing/bento/architecture-test-sm.webp 640w, /midia/landing/bento/architecture-test.webp 1200w')
        );
    }
}
