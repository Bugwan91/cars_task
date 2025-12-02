<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarPhoto;
use App\Models\CarOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CarUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createCarWithPhotos(): Car
    {
        Storage::fake('public');

        $car = Car::create([
            'brand' => 'Audi',
            'model' => 'e-tron',
            'year' => 2023,
            'price' => 68000,
            'description' => 'Electric SUV.',
        ]);

        CarPhoto::create([
            'car_id' => $car->id,
            'photo_path' => 'cars/audi-primary.jpg',
            'is_primary' => true,
        ]);

        CarPhoto::create([
            'car_id' => $car->id,
            'photo_path' => 'cars/audi-secondary.jpg',
            'is_primary' => false,
        ]);

        // Seed fake storage files
        Storage::disk('public')->put('cars/audi-primary.jpg', 'primary');
        Storage::disk('public')->put('cars/audi-secondary.jpg', 'secondary');

        $option = CarOption::create(['name' => 'Quattro']);
        $car->options()->attach($option->id);

        return $car->fresh(['photos', 'options']);
    }

    public function test_edit_page_loads_via_inertia(): void
    {
        $car = $this->createCarWithPhotos();

        $this->get(route('cars.edit', $car))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('EditCar')
                ->where('car.id', $car->id)
                ->has('availableOptions')
            );
    }

    public function test_car_can_be_updated_with_photo_removal_and_new_upload(): void
    {
        $car = $this->createCarWithPhotos();
        $primaryPhoto = $car->photos->firstWhere('is_primary', true);
        $secondaryPhoto = $car->photos->firstWhere('is_primary', false);

        $newPhoto = UploadedFile::fake()->create('new-photo.jpg', 512, 'image/jpeg');

        $response = $this->call('POST', route('cars.update.web', $car), [
            '_method' => 'PUT',
            'brand' => 'Audi',
            'model' => 'Q8 e-tron',
            'year' => 2024,
            'price' => 72000,
            'description' => 'Updated description',
            'options' => ['Panoramic Roof'],
            'primary_photo_id' => $secondaryPhoto->id,
            'removed_photo_ids' => [$primaryPhoto->id],
        ], [], [
            'photos' => [$newPhoto],
        ]);

        $response->assertRedirect(route('cars.show', $car));

        Storage::disk('public')->assertMissing('cars/audi-primary.jpg');

        $car->refresh();
        $this->assertEquals('Q8 e-tron', $car->model);
        $this->assertDatabaseMissing('car_photos', ['id' => $primaryPhoto->id]);
        $this->assertDatabaseHas('car_photos', ['car_id' => $car->id, 'is_primary' => true, 'id' => $secondaryPhoto->id]);
        $this->assertEquals(2, $car->photos()->count());
    }

    public function test_car_can_be_updated_without_touching_photos(): void
    {
        $car = $this->createCarWithPhotos();

        $response = $this->call('POST', route('cars.update.web', $car), [
            '_method' => 'PUT',
            'brand' => $car->brand,
            'model' => $car->model,
            'year' => $car->year,
            'price' => $car->price,
            'description' => $car->description,
            'options' => ['Panoramic Roof'],
        ]);

        $response->assertRedirect(route('cars.show', $car));

        $car->refresh();
        $this->assertEquals('Panoramic Roof', $car->options->first()->name ?? null);
    }

    public function test_existing_options_are_retained_when_adding_new_ones(): void
    {
        $car = $this->createCarWithPhotos();

        $response = $this->call('POST', route('cars.update.web', $car), [
            '_method' => 'PUT',
            'brand' => 'Audi',
            'model' => 'e-tron',
            'year' => 2023,
            'price' => 68000,
            'description' => $car->description,
            'options' => ['Quattro', 'Heated Seats'],
        ]);

        $response->assertRedirect(route('cars.show', $car));

        $optionNames = $car->fresh()->options->pluck('name')->sort()->values();
        $this->assertEquals(['Heated Seats', 'Quattro'], $optionNames->toArray());
    }

    public function test_options_remain_when_payload_is_missing(): void
    {
        $car = $this->createCarWithPhotos();

        $response = $this->call('POST', route('cars.update.web', $car), [
            '_method' => 'PUT',
            'brand' => 'Audi',
            'model' => 'e-tron',
            'year' => 2023,
            'price' => 68000,
            'description' => $car->description,
        ]);

        $response->assertRedirect(route('cars.show', $car));

        $this->assertCount(1, $car->fresh()->options);
        $this->assertEquals('Quattro', $car->fresh()->options->first()->name);
    }

    public function test_required_fields_cannot_be_cleared_on_update(): void
    {
        $car = $this->createCarWithPhotos();

        $response = $this->from(route('cars.edit', $car))->call('POST', route('cars.update.web', $car), [
            '_method' => 'PUT',
            'brand' => '',
            'model' => '',
            'year' => '',
            'price' => '',
        ]);

        $response->assertSessionHasErrors(['brand', 'model', 'year', 'price']);
        $this->assertEquals('Audi', $car->fresh()->brand);
    }
}
