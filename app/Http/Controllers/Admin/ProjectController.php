<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->simplePaginate(25, [
                'id',
                'name',
                'url',
                'image_url',
                'image_alt',
                'tag',
                'note',
                'sort_order',
                'is_published',
                'created_at',
            ])
            ->through(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'url' => $p->url,
                'image_url' => $p->image_url,
                'image_alt' => $p->image_alt,
                'image_src' => $this->resolveImageSrc($p->image_url),
                'tag' => $p->tag,
                'note' => $p->note,
                'sort_order' => $p->sort_order,
                'is_published' => $p->is_published,
                'created_at' => $p->created_at?->toISOString(),
            ])
            ->withQueryString();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'can_manage_projects' => (bool) $request->user()?->is_admin,
        ]);
    }

    public function create()
    {
        $nextSortOrder = (int) (Project::query()->max('sort_order') ?? 0);
        $nextSortOrder = $nextSortOrder + 1;

        return Inertia::render('Admin/Projects/Form', [
            'project' => null,
            'default_sort_order' => $nextSortOrder,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_alt' => ['nullable', 'string', 'max:160'],
            'tag' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_published' => ['nullable', 'boolean'],
        ], $this->projectValidationMessages());

        $desiredOrder = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = (bool) ($data['is_published'] ?? true);

        $imageAlt = trim((string) ($data['image_alt'] ?? ''));
        $createData = $data;
        unset($createData['image'], $createData['image_alt']);

        $storedPath = null;
        try {
            DB::transaction(function () use ($request, $desiredOrder, $createData, $imageAlt, &$storedPath) {
                $this->ensureUniqueSortOrders();

                $desiredOrder = $this->clampDesiredSortOrderForInsert($desiredOrder);
                Project::query()
                    ->where('sort_order', '>=', $desiredOrder)
                    ->increment('sort_order');

                $createData['sort_order'] = $desiredOrder;
                $project = Project::create($createData);

                $updates = [
                    'image_alt' => $imageAlt !== '' ? $imageAlt : null,
                ];

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    if ($file instanceof UploadedFile) {
                        try {
                            [$relativePath] = $this->storeProjectImage((string) $project->id, $file);
                            $storedPath = $relativePath;
                            $updates['image_url'] = $relativePath;
                        } catch (\Throwable $e) {
                            throw ValidationException::withMessages([
                                'image' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                $project->update($updates);
            });
        } catch (\Throwable $e) {
            if (is_string($storedPath) && $storedPath !== '') {
                Storage::disk('public')->delete($storedPath);
            }
            throw $e;
        }

        return redirect()->route('admin.projects.index');
    }

    public function edit(Project $project)
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => [
                ...$project->only(['id', 'name', 'url', 'image_url', 'image_alt', 'tag', 'note', 'sort_order', 'is_published']),
                'image_src' => $this->resolveImageSrc($project->image_url),
            ],
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_alt' => ['nullable', 'string', 'max:160'],
            'remove_image' => ['nullable', 'boolean'],
            'tag' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_published' => ['nullable', 'boolean'],
        ], $this->projectValidationMessages());

        $desiredOrder = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = (bool) ($data['is_published'] ?? false);

        $imageAlt = trim((string) ($data['image_alt'] ?? ''));
        $removeImage = (bool) ($data['remove_image'] ?? false);

        $newStoredPath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file instanceof UploadedFile) {
                try {
                    [$relativePath] = $this->storeProjectImage((string) $project->id, $file);
                    $newStoredPath = $relativePath;
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'image' => $e->getMessage(),
                    ]);
                }
            }
        }

        $previousImageUrl = $project->image_url;

        try {
            DB::transaction(function () use ($project, $data, $desiredOrder, $imageAlt, $removeImage, $newStoredPath) {
                $this->ensureUniqueSortOrders();
                $project->refresh();
                $this->moveProjectToSortOrder($project, $desiredOrder);

                $updateData = $data;
                unset($updateData['image'], $updateData['image_alt'], $updateData['remove_image'], $updateData['sort_order']);

                $updateData['image_alt'] = $imageAlt !== '' ? $imageAlt : null;

                if ($removeImage) {
                    $updateData['image_url'] = null;
                }

                if (is_string($newStoredPath) && $newStoredPath !== '') {
                    $updateData['image_url'] = $newStoredPath;
                }

                $project->update([
                    ...$updateData,
                    'sort_order' => $project->sort_order,
                ]);
            });
        } catch (\Throwable $e) {
            if (is_string($newStoredPath) && $newStoredPath !== '') {
                Storage::disk('public')->delete($newStoredPath);
            }
            throw $e;
        }

        if (is_string($newStoredPath) && $newStoredPath !== '' && $previousImageUrl !== $newStoredPath) {
            $this->deleteProjectImageIfInternal($previousImageUrl);
        } elseif ($removeImage) {
            $this->deleteProjectImageIfInternal($previousImageUrl);
        }

        return redirect()->route('admin.projects.index');
    }

    public function destroy(Project $project)
    {
        $previousImageUrl = $project->image_url;
        $deletedOrder = (int) $project->sort_order;

        DB::transaction(function () use ($project, $deletedOrder) {
            $project->delete();

            Project::query()
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        });

        $this->deleteProjectImageIfInternal($previousImageUrl);

        return redirect()->route('admin.projects.index');
    }

    private function clampDesiredSortOrderForInsert(int $desiredOrder): int
    {
        $desiredOrder = max(0, $desiredOrder);
        $max = (int) (Project::query()->max('sort_order') ?? 0);
        $maxPlusOne = $max + 1;

        return min($desiredOrder, $maxPlusOne);
    }

    private function moveProjectToSortOrder(Project $project, int $desiredOrder): void
    {
        $desiredOrder = max(0, $desiredOrder);
        $currentOrder = (int) $project->sort_order;

        if ($desiredOrder === $currentOrder) {
            return;
        }

        $max = (int) (Project::query()->whereKeyNot($project->id)->max('sort_order') ?? 0);
        $maxPlusOne = $max + 1;
        $desiredOrder = min($desiredOrder, $maxPlusOne);

        if ($desiredOrder < $currentOrder) {
            Project::query()
                ->whereKeyNot($project->id)
                ->where('sort_order', '>=', $desiredOrder)
                ->where('sort_order', '<', $currentOrder)
                ->increment('sort_order');
        } else {
            Project::query()
                ->whereKeyNot($project->id)
                ->where('sort_order', '>', $currentOrder)
                ->where('sort_order', '<=', $desiredOrder)
                ->decrement('sort_order');
        }

        $project->sort_order = $desiredOrder;
    }

    private function ensureUniqueSortOrders(): void
    {
        $rows = Project::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id', 'sort_order']);

        $used = [];

        foreach ($rows as $row) {
            $order = (int) $row->sort_order;
            while (isset($used[$order])) {
                $order++;
            }
            $used[$order] = true;

            if ($order !== (int) $row->sort_order) {
                Project::query()->whereKey($row->id)->update(['sort_order' => $order]);
            }
        }
    }

    private function resolveImageSrc(?string $imageUrl): ?string
    {
        $imageUrl = $imageUrl ? trim($imageUrl) : null;
        if (!$imageUrl) {
            return null;
        }

        if (Str::startsWith($imageUrl, ['http://', 'https://'])) {
            return $imageUrl;
        }

        if (Str::startsWith($imageUrl, 'projects/')) {
            return route('media.projects', ['filename' => basename($imageUrl)], absolute: false);
        }

        return Storage::disk('public')->url($imageUrl);
    }

    private function deleteProjectImageIfInternal(?string $imageUrl): void
    {
        $imageUrl = $imageUrl ? trim($imageUrl) : null;
        if (!$imageUrl) {
            return;
        }

        if (Str::startsWith($imageUrl, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($imageUrl);
    }

    private function storeProjectImage(string $key, UploadedFile $file): array
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

        $tmp = tempnam(sys_get_temp_dir(), 'project_');
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
        $relativePath = 'projects/'.$filename;
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

    private function projectValidationMessages(): array
    {
        return [
            'image.uploaded' => 'Falha no upload da imagem. O servidor recusou o arquivo; tente uma imagem menor ou aumente os limites upload_max_filesize/post_max_size.',
            'image.image' => 'Envie um arquivo de imagem válido.',
            'image.mimes' => 'A imagem deve estar em JPG, PNG ou WebP.',
            'image.max' => 'A imagem deve ter no máximo 5 MB.',
        ];
    }
}
