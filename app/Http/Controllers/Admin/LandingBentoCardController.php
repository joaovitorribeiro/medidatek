<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingBentoCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Inertia\Inertia;

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
                    Storage::disk('public')->delete($card->image_url);
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
                        Storage::disk('public')->delete($card->image_url);
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

    private function storeBentoImage(string $key, UploadedFile $file): array
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('Extensão GD não instalada no servidor (php-gd).');
        }

        $sourcePath = $file->getRealPath();
        if (!$sourcePath) {
            throw new \RuntimeException('Upload inválido.');
        }

        $info = @getimagesize($sourcePath);
        if (!is_array($info) || count($info) < 3) {
            throw new \RuntimeException('Imagem inválida.');
        }

        [$width, $height, $type] = $info;

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (!$source) {
            throw new \RuntimeException('Formato de imagem não suportado.');
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

        $output = imagecreatetruecolor($outWidth, $outHeight);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparentOut = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefilledrectangle($output, 0, 0, $outWidth, $outHeight, $transparentOut);
        imagecopyresampled($output, $cropped, 0, 0, 0, 0, $outWidth, $outHeight, $cropWidth, $cropHeight);

        $tmp = tempnam(sys_get_temp_dir(), 'bento_');
        if (!$tmp) {
            throw new \RuntimeException('Falha ao processar imagem.');
        }

        $targetBytes = 350 * 1024;
        $qualitySteps = [82, 78, 74, 70, 66, 62, 58];

        $ext = 'webp';
        $ok = false;
        if (function_exists('imagewebp')) {
            foreach ($qualitySteps as $quality) {
                $ok = @imagewebp($output, $tmp, $quality);
                if (!$ok) {
                    break;
                }
                clearstatcache(true, $tmp);
                $size = @filesize($tmp);
                if (is_int($size) && $size > 0 && $size <= $targetBytes) {
                    break;
                }
            }
        }

        if (!$ok) {
            $ext = 'jpg';
            foreach ($qualitySteps as $quality) {
                $ok = @imagejpeg($output, $tmp, $quality);
                if (!$ok) {
                    break;
                }
                clearstatcache(true, $tmp);
                $size = @filesize($tmp);
                if (is_int($size) && $size > 0 && $size <= $targetBytes) {
                    break;
                }
            }
        }

        imagedestroy($output);
        imagedestroy($cropped);
        imagedestroy($source);

        if (!$ok) {
            @unlink($tmp);
            throw new \RuntimeException('Falha ao salvar imagem.');
        }

        $filename = $key.'-'.now()->format('YmdHis').'.'.$ext;
        $relativePath = 'landing/bento/'.$filename;
        $contents = file_get_contents($tmp);
        if (!is_string($contents) || $contents === '') {
            @unlink($tmp);
            throw new \RuntimeException('Falha ao ler o arquivo processado.');
        }

        $written = Storage::disk('public')->put($relativePath, $contents, 'public');
        @unlink($tmp);
        if (!$written) {
            throw new \RuntimeException('Falha ao gravar imagem no storage.');
        }

        return [$relativePath, $ext];
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
