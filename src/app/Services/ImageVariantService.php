<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Throwable;

class ImageVariantService
{
    private ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        $this->manager = $manager ?? new ImageManager(new Driver());
    }

    public function createThumbnail(string $sourcePath, int $width = 640, int $height = 360, int $quality = 75): ?string
    {
        if (empty($sourcePath) || !Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        try {
            $absolutePath = Storage::disk('public')->path($sourcePath);
            $image = $this->manager->read($absolutePath);
            $image = $this->fitImage($image, $width, $height);

            $thumbnailDirectory = 'cars/thumbnails';
            $extension = 'webp';
            $fileName = pathinfo($sourcePath, PATHINFO_FILENAME);
            $uniqueSuffix = Str::random(6);
            $thumbnailPath = sprintf(
                '%s/%s_%s.%s',
                $thumbnailDirectory,
                Str::slug($fileName) . '_' . $uniqueSuffix,
                $width . 'x' . $height,
                $extension
            );

            Storage::disk('public')->put($thumbnailPath, (string) $image->toWebp($quality));

            return $thumbnailPath;
        } catch (Throwable $exception) {
            report($exception);
            return null;
        }
    }

    private function fitImage(ImageInterface $image, int $width, int $height): ImageInterface
    {
        return $image->cover($width, $height);
    }
}
