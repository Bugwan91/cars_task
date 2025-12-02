<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CarShowTest extends TestCase
{
    use RefreshDatabase;

    private function createCar(): Car
    {
        $car = Car::create([
            'brand' => 'Tesla',
            'model' => 'Model S',
            'year' => 2022,
            'price' => 79999,
            'description' => 'Flagship EV sedan.',
        ]);

        CarPhoto::create([
            'car_id' => $car->id,
            'photo_path' => 'cars/sample.jpg',
            'is_primary' => true,
        ]);

        return $car->fresh(['photos', 'options']);
    }

    public function test_car_show_returns_inertia_page(): void
    {
        $car = $this->createCar();

        $response = $this->get(route('cars.show', $car));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('CarView')
                ->where('car.id', $car->id)
                ->where('car.brand', 'Tesla')
                ->has('car.photos', 1)
            );
    }

    public function test_car_show_returns_json_when_requested(): void
    {
        $car = $this->createCar();

        $response = $this->getJson(route('cars.show', $car));

        $response->assertOk()
            ->assertJsonPath('id', $car->id)
            ->assertJsonCount(1, 'photos');
    }
}
