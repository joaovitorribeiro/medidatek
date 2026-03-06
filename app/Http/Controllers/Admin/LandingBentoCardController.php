<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingBentoCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LandingBentoCardController extends Controller
{
    public function edit()
    {
        $cards = LandingBentoCard::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'title', 'image_url', 'alt', 'sort_order'])
            ->map(fn (LandingBentoCard $c) => [
                'id' => $c->id,
                'key' => $c->key,
                'title' => $c->title,
                'image_url' => $c->image_url,
                'image_src' => Str::startsWith($c->image_url, ['http://', 'https://'])
                    ? $c->image_url
                    : (Str::startsWith($c->image_url, 'landing/bento/')
                        ? route('media.landing.bento', ['filename' => basename($c->image_url)], absolute: false)
                        : Storage::disk('public')->url($c->image_url)),
                'alt' => $c->alt,
                'sort_order' => $c->sort_order,
            ])
            ->values();

        return Inertia::render('Admin/Landing/BentoImages', [
            'cards' => $cards,
        ]);
    }

    public function save(Request $request, LandingBentoCard $card)
    {
        $data = $request->validate([
            'alt' => ['nullable', 'string', 'max:160'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $alt = trim((string) ($data['alt'] ?? ''));
        if ($alt === '') {
            $alt = $card->alt ?: $card->title;
        }

        $updates = [
            'alt' => $alt,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file instanceof UploadedFile) {
                if ($card->image_url && !Str::startsWith($card->image_url, ['http://', 'https://'])) {
                    $this->deleteBentoImageVariantsIfInternal($card->image_url);
                }

                try {
                    [$relativePath] = $this->storeBentoImage($card->key, $file);
                    $updates['image_url'] = $relativePath;
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'image' => $e->getMessage(),
                    ]);
                }
            }
        }

        $card->update($updates);

        return redirect()->route('admin.landing.bento.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cards' => ['required', 'array', 'min:1'],
            'cards.*.id' => ['required', 'integer', 'exists:landing_bento_cards,id'],
            'cards.*.alt' => ['nullable', 'string', 'max:160'],
            'cards.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['cards'] as $row) {
                $card = LandingBentoCard::query()->whereKey($row['id'])->firstOrFail();

                $alt = trim((string) ($row['alt'] ?? ''));
                if ($alt === '') {
                    $alt = $card->alt ?: $card->title;
                }

                $updates = [
                    'alt' => $alt,
                ];

                if (isset($row['image']) && $row['image']) {
                    if ($card->image_url && !Str::startsWith($card->image_url, ['http://', 'https://'])) {
                        $this->deleteBentoImageVariantsIfInternal($card->image_url);
                    }

                    if ($row['image'] instanceof UploadedFile) {
                        try {
                            [$relativePath] = $this->storeBentoImage($card->key, $row['image']);
                            $updates['image_url'] = $relativePath;
                        } catch (\Throwable $e) {
                            throw ValidationException::withMessages([
                                'image' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                $card->update($updates);
            }
        });

        return redirect()->route('admin.landing.bento.edit');
    }

    private function deleteBentoImageVariantsIfInternal(?string $imageUrl): void
    {
        $imageUrl = $imageUrl ? trim($imageUrl) : null;
        if (!$imageUrl) {
            return;
        }

        if (Str::startsWith($imageUrl, ['http://', 'https://'])) {
            return;
        }

        $mobileVariant = $this->mobileVariantPath($imageUrl);
        Storage::disk('public')->delete(array_values(array_filter([$imageUrl, $mobileVariant])));
    }

    private function mobileVariantPath(string $relativePath): string
    {
        $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        $directory = pathinfo($relativePath, PATHINFO_DIRNAME);
        $directory = $directory === '.' ? '' : $directory.'/';

        return $directory.$filename.'-sm'.($extension ? '.'.$extension : '');
    }

    private function createResizedCanvas($source, int $width, int $height)
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source),
        );

        return $canvas;
    }

    private function encodeImageResourceToFile($image, string $tmpPath, int $targetBytes, ?string $preferredExt = null): array
    {
        $qualitySteps = [82, 78, 74, 70, 66, 62, 58];

        if (($preferredExt === null || $preferredExt === 'webp') && function_exists('imagewebp')) {
            foreach ($qualitySteps as $quality) {
                $ok = @imagewebp($image, $tmpPath, $quality);
                if (!$ok) {
                    break;
                }

                clearstatcache(true, $tmpPath);
                $size = @filesize($tmpPath);
                if (is_int($size) && $size > 0 && $size <= $targetBytes) {
                    return [true, 'webp'];
                }
            }

            clearstatcache(true, $tmpPath);
            $size = @filesize($tmpPath);
            if (is_int($size) && $size > 0) {
                return [true, 'webp'];
            }
        }

        if ($preferredExt === 'webp') {
            return [false, 'webp'];
        }

        foreach ($qualitySteps as $quality) {
            $ok = @imagejpeg($image, $tmpPath, $quality);
            if (!$ok) {
                break;
            }

            clearstatcache(true, $tmpPath);
            $size = @filesize($tmpPath);
            if (is_int($size) && $size > 0 && $size <= $targetBytes) {
                return [true, 'jpg'];
            }
        }

        clearstatcache(true, $tmpPath);
        $size = @filesize($tmpPath);
        if (is_int($size) && $size > 0) {
            return [true, 'jpg'];
        }

        return [false, $preferredExt ?? 'jpg'];
    }

    private function writeProcessedImage(string $relativePath, string $tmpPath): void
    {
        $contents = file_get_contents($tmpPath);
        if (!is_string($contents) || $contents === '') {
            throw new \RuntimeException('Falha ao ler o arquivo processado.');
        }

        $written = Storage::disk('public')->put($relativePath, $contents, 'public');
        if (!$written) {
            throw new \RuntimeException('Falha ao gravar imagem no storage.');
        }
    }

    private function storeBentoImage(string $key, UploadedFile $file): array
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('Extensao GD nao instalada no servidor (php-gd).');
        }

        $sourcePath = $file->getRealPath();
        if (!$sourcePath) {
            throw new \RuntimeException('Upload invalido.');
        }

        $info = @getimagesize($sourcePath);
        if (!is_array($info) || count($info) < 3) {
            throw new \RuntimeException('Imagem invalida.');
        }

        [$width, $height, $type] = $info;

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (!$source) {
            throw new \RuntimeException('Formato de imagem nao suportado.');
        }

        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            $orientation = is_array($exif) ? ($exif['Orientation'] ?? null) : null;
            if (is_int($orientation)) {
                $source = $this->applyExifOrientation($source, $orientation);
                $width = imagesx($source);
                $height = imagesy($source);
            }
        }

        $targetRatio = 4 / 3;
        $currentRatio = $width / max(1, $height);

        if ($currentRatio > $targetRatio) {
            $cropHeight = $height;
            $cropWidth = (int) round($height * $targetRatio);
            $srcX = (int) floor(($width - $cropWidth) / 2);
            $srcY = 0;
        } else {
            $cropWidth = $width;
            $cropHeight = (int) round($width / $targetRatio);
            $srcX = 0;
            $srcY = (int) floor(($height - $cropHeight) / 2);
        }

        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefilledrectangle($cropped, 0, 0, $cropWidth, $cropHeight, $transparent);
        imagecopyresampled($cropped, $source, 0, 0, $srcX, $srcY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);

        $maxWidth = 1200;
        $outWidth = $cropWidth > $maxWidth ? $maxWidth : $cropWidth;
        $outHeight = (int) round($outWidth * 3 / 4);
        $mobileWidth = min(640, $outWidth);
        $mobileHeight = (int) round($mobileWidth * 3 / 4);

        $outputDesktop = $this->createResizedCanvas($cropped, $outWidth, $outHeight);
        $outputMobile = $this->createResizedCanvas($cropped, $mobileWidth, $mobileHeight);

        $tmpDesktop = tempnam(sys_get_temp_dir(), 'bento_desktop_');
        $tmpMobile = tempnam(sys_get_temp_dir(), 'bento_mobile_');
        if (!$tmpDesktop || !$tmpMobile) {
            if ($tmpDesktop) {
                @unlink($tmpDesktop);
            }
            if ($tmpMobile) {
                @unlink($tmpMobile);
            }
            imagedestroy($outputDesktop);
            imagedestroy($outputMobile);
            imagedestroy($cropped);
            imagedestroy($source);
            throw new \RuntimeException('Falha ao processar imagem.');
        }

        [$desktopOk, $ext] = $this->encodeImageResourceToFile($outputDesktop, $tmpDesktop, 350 * 1024);
        [$mobileOk] = $this->encodeImageResourceToFile($outputMobile, $tmpMobile, 190 * 1024, $ext);

        imagedestroy($outputDesktop);
        imagedestroy($outputMobile);
        imagedestroy($cropped);
        imagedestroy($source);

        if (!$desktopOk || !$mobileOk) {
            @unlink($tmpDesktop);
            @unlink($tmpMobile);
            throw new \RuntimeException('Falha ao salvar imagem.');
        }

        $baseName = $key.'-'.now()->format('YmdHis');
        $filename = $baseName.'.'.$ext;
        $relativePath = 'landing/bento/'.$filename;
        $mobileRelativePath = 'landing/bento/'.$baseName.'-sm.'.$ext;

        try {
            $this->writeProcessedImage($relativePath, $tmpDesktop);
            $this->writeProcessedImage($mobileRelativePath, $tmpMobile);
        } catch (\Throwable $e) {
            $this->deleteBentoImageVariantsIfInternal($relativePath);
            throw $e;
        } finally {
            @unlink($tmpDesktop);
            @unlink($tmpMobile);
        }

        return [$relativePath, $ext, $mobileRelativePath];
    }

    private function applyExifOrientation($image, int $orientation)
    {
        return match ($orientation) {
            2 => $this->flipImage($image, IMG_FLIP_HORIZONTAL),
            3 => imagerotate($image, 180, 0),
            4 => $this->flipImage($image, IMG_FLIP_VERTICAL),
            5 => imagerotate($this->flipImage($image, IMG_FLIP_HORIZONTAL), 270, 0),
            6 => imagerotate($image, 270, 0),
            7 => imagerotate($this->flipImage($image, IMG_FLIP_HORIZONTAL), 90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    private function flipImage($image, int $mode)
    {
        if (function_exists('imageflip')) {
            imageflip($image, $mode);
            return $image;
        }

        $w = imagesx($image);
        $h = imagesy($image);
        $flipped = imagecreatetruecolor($w, $h);
        imagealphablending($flipped, false);
        imagesavealpha($flipped, true);
        $transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);
        imagefilledrectangle($flipped, 0, 0, $w, $h, $transparent);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $srcX = $mode === IMG_FLIP_HORIZONTAL ? ($w - 1 - $x) : $x;
                $srcY = $mode === IMG_FLIP_VERTICAL ? ($h - 1 - $y) : $y;
                imagesetpixel($flipped, $x, $y, imagecolorat($image, $srcX, $srcY));
            }
        }

        imagedestroy($image);
        return $flipped;
    }
}
