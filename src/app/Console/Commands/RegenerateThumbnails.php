<?php

namespace App\Console\Commands;

use App\Models\CarPhoto;
use App\Services\ImageVariantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:regenerate-thumbnails {--force : Force regeneration even if thumbnail exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate thumbnails for all car photos';

    /**
     * Execute the console command.
     */
    public function handle(ImageVariantService $imageVariantService)
    {
        $force = $this->option('force');
        
        $query = CarPhoto::query();
        
        if (!$force) {
            $query->whereNull('thumbnail_path');
        }

        $photos = $query->get();
        $count = $photos->count();

        if ($count === 0) {
            $this->info('No photos need thumbnail regeneration.');
            return;
        }

        $this->info("Regenerating thumbnails for {$count} photos...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($photos as $photo) {
            if (!Storage::disk('public')->exists($photo->photo_path)) {
                $this->error("\nPhoto not found: {$photo->photo_path}");
                $bar->advance();
                continue;
            }

            // If force is true and thumbnail exists, delete old one
            if ($force && $photo->thumbnail_path && Storage::disk('public')->exists($photo->thumbnail_path)) {
                Storage::disk('public')->delete($photo->thumbnail_path);
            }

            $thumbnailPath = $imageVariantService->createThumbnail($photo->photo_path);

            if ($thumbnailPath) {
                $photo->update(['thumbnail_path' => $thumbnailPath]);
            } else {
                $this->error("\nFailed to generate thumbnail for: {$photo->photo_path}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Thumbnails regenerated successfully.');
    }
}
