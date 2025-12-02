<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class CarService
{
    public function __construct(
        private ImageVariantService $imageVariantService
    ) {}

    public function createCar(array $attributes, array $options = [], array $photos = [], int $primaryPhotoIndex = 0): Car
    {
        return DB::transaction(function () use ($attributes, $options, $photos, $primaryPhotoIndex) {
            $car = Car::create($attributes);
            
            $this->syncOptions($car, $options, true);
            $this->storePhotos($car, $photos, $primaryPhotoIndex);

            return $car;
        });
    }

    public function updateCar(Car $car, array $attributes, ?array $options, array $newPhotos = [], array $removedPhotoIds = [], ?int $primaryPhotoId = null): Car
    {
        return DB::transaction(function () use ($car, $attributes, $options, $newPhotos, $removedPhotoIds, $primaryPhotoId) {
            $car->update($attributes);

            if ($options !== null) {
                $this->syncOptions($car, $options, true); // detachWhenEmpty is true for update if options provided
            }

            $this->removePhotos($car, $removedPhotoIds);
            $this->storePhotos($car, $newPhotos);

            // Reload photos to ensure we have the latest set for primary calculation
            $car->load('photos');
            $this->setPrimaryPhoto($car, $primaryPhotoId);

            return $car;
        });
    }

    public function deleteCar(Car $car): void
    {
        DB::transaction(function () use ($car) {
            $car->delete();
        });
    }

    private function syncOptions(Car $car, array $options, bool $detachWhenEmpty = false): void
    {
        $normalized = collect($options)
            ->map(function ($option) {
                if (is_array($option) || is_object($option)) {
                    $option = data_get($option, 'name', data_get($option, 'value', ''));
                }

                return trim((string) $option);
            })
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            if ($detachWhenEmpty) {
                $car->options()->detach();
            }
            return;
        }

        $ids = $normalized->map(fn (string $name) => CarOption::firstOrCreate(['name' => $name])->id);

        $car->options()->sync($ids->all());
    }

    /**
     * @param Car $car
     * @param UploadedFile[] $files
     * @param int $primaryIndex
     */
    private function storePhotos(Car $car, array $files, int $primaryIndex = 0): void
    {
        // TODO: Ideally, we should not do the CPU-intensive image processing during the request.
        // A better approach would be to queue jobs for image processing after storing the original files.
        // But this should be fine for now.
        $count = count($files);
        if ($count === 0) {
            return;
        }

        // Ensure primary index is valid
        $primaryIndex = min(max($primaryIndex, 0), $count - 1);

        foreach ($files as $index => $file) {
            $path = $file->store('cars', 'public');
            $thumbnailPath = $this->imageVariantService->createThumbnail($path);

            $car->photos()->create([
                'photo_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'is_primary' => $index === $primaryIndex,
            ]);
        }
    }

    private function removePhotos(Car $car, array $photoIds): void
    {
        if (empty($photoIds)) {
            return;
        }

        $photos = $car->photos()->whereIn('id', $photoIds)->get();

        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
            if ($photo->thumbnail_path) {
                Storage::disk('public')->delete($photo->thumbnail_path);
            }
            $photo->delete();
        }
    }

    private function setPrimaryPhoto(Car $car, ?int $photoId): void
    {
        if ($photoId && $car->photos()->where('id', $photoId)->exists()) {
            $car->photos()->update(['is_primary' => false]);
            $car->photos()->where('id', $photoId)->update(['is_primary' => true]);
            return;
        }

        // If no specific photo requested, ensure one is primary if photos exist
        $currentPrimary = $car->photos()->where('is_primary', true)->orderByDesc('id')->first();

        if ($currentPrimary) {
            // Ensure only one is primary (cleanup)
            $car->photos()->where('id', '!=', $currentPrimary->id)->update(['is_primary' => false]);
            return;
        }

        // Fallback: make the first one primary
        $fallback = $car->photos()->orderBy('id')->first();

        if ($fallback) {
            $car->photos()->update(['is_primary' => false]);
            $fallback->update(['is_primary' => true]);
        }
    }
}
