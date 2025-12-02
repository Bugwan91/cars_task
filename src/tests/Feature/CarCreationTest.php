<?php

namespace Tests\Feature;

use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarCreationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_car_with_basic_data(): void
    {
        $response = $this->post(route('cars.store.web'), [
            'brand' => 'Test',
            'model' => 'Model',
            'year' => 2024,
            'price' => 1000,
            'description' => 'desc',
            'options' => ['MP3'],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('cars', ['brand' => 'Test', 'model' => 'Model']);
    }

    #[Test]
    public function it_creates_car_with_photos_and_sets_primary(): void
    {
        Storage::fake('public');

        $response = $this->post(route('cars.store.web'), [
            'brand' => 'Photo',
            'model' => 'Car',
            'year' => 2024,
            'price' => 12345,
            'description' => 'with photos',
            'photos' => [
                UploadedFile::fake()->create('first.jpg', 50, 'image/jpeg'),
                UploadedFile::fake()->create('second.jpg', 75, 'image/jpeg'),
            ],
            'primary_photo_index' => 1,
        ]);

        $response->assertRedirect(route('home'));

        $car = Car::where('brand', 'Photo')->where('model', 'Car')->first();
        $this->assertNotNull($car);
        $this->assertCount(2, $car->photos);
        $this->assertTrue($car->photos->first()->is_primary);

        Storage::disk('public')->assertExists($car->photos->first()->photo_path);
    }

    #[Test]
    public function price_is_required_when_creating_a_car(): void
    {
        $response = $this->post(route('cars.store.web'), [
            'brand' => 'NoPrice',
            'model' => 'Car',
            'year' => 2024,
            'description' => 'missing price',
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    #[Test]
    public function it_accepts_avif_photos(): void
    {
        Storage::fake('public');

        $response = $this->post(route('cars.store.web'), [
            'brand' => 'AVIF',
            'model' => 'Ready',
            'year' => 2024,
            'price' => 50000,
            'photos' => [
                UploadedFile::fake()->create('primary.avif', 200, 'image/avif'),
            ],
        ]);

        $response->assertRedirect(route('home'));

        $car = Car::where('brand', 'AVIF')->first();
        $this->assertNotNull($car);
        $this->assertCount(1, $car->photos);
        Storage::disk('public')->assertExists($car->photos->first()->photo_path);
    }
}
