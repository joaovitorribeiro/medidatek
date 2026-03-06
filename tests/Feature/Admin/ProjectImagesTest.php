<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_upload_creates_project_image_and_mobile_variant(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
            'is_staff' => true,
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'project_test_');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/xcAAwMCAO8K7mAAAAAASUVORK5CYII=');
        file_put_contents($tmp, $png);
        $upload = new \Illuminate\Http\UploadedFile($tmp, 'test.png', 'image/png', null, true);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.projects.store', absolute: false), [
                'name' => 'Projeto Teste',
                'url' => 'https://example.com',
                'image' => $upload,
                'is_published' => true,
            ]);

        if (!function_exists('imagecreatetruecolor')) {
            $response->assertSessionHasErrors('image');
            return;
        }

        $response->assertRedirect(route('admin.projects.index', absolute: false));

        $project = Project::query()->latest('id')->firstOrFail();
        $this->assertStringStartsWith('projects/', (string) $project->image_url);
        Storage::disk('public')->assertExists((string) $project->image_url);

        $mobileVariant = preg_replace('/(\.[^.]+)$/', '-sm$1', (string) $project->image_url);
        $this->assertIsString($mobileVariant);
        Storage::disk('public')->assertExists((string) $mobileVariant);
    }
}
