<?php

namespace Tests\Feature\Admin;

use App\Models\LandingBentoCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingBentoImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_bento_image_and_it_is_persisted(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $card = LandingBentoCard::query()->firstOrFail();
        $beforeImageUrl = $card->image_url;
        $beforeAlt = $card->alt;

        $tmp = tempnam(sys_get_temp_dir(), 'bento_test_');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/xcAAwMCAO8K7mAAAAAASUVORK5CYII=');
        file_put_contents($tmp, $png);
        $upload = new \Illuminate\Http\UploadedFile($tmp, 'test.png', 'image/png', null, true);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.landing.bento.card.save', $card->id, absolute: false), [
                'alt' => 'Imagem de teste',
                'image' => $upload,
            ]);

        if (!function_exists('imagecreatetruecolor')) {
            $response->assertSessionHasErrors('image');
            $card->refresh();
            $this->assertSame($beforeImageUrl, $card->image_url);
            $this->assertSame($beforeAlt, $card->alt);
            return;
        }

        $response->assertRedirect(route('admin.landing.bento.edit', absolute: false));

        $card->refresh();

        $this->assertSame('Imagem de teste', $card->alt);
        $this->assertStringStartsWith('landing/bento/', $card->image_url);

        Storage::disk('public')->assertExists($card->image_url);
        $mobileVariant = preg_replace('/(\.[^.]+)$/', '-sm$1', $card->image_url ?? '');
        $this->assertIsString($mobileVariant);
        Storage::disk('public')->assertExists((string) $mobileVariant);
    }

    public function test_bento_image_can_be_served_from_media_route(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('landing/bento/fixture.webp', 'fake-webp-bytes');

        $response = $this->get('/midia/landing/bento/fixture.webp');

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
    }
}
