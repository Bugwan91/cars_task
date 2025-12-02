<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarRequest;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Services\CarService;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function __construct(
        private CarService $carService,
        private ExchangeRateService $exchangeRateService
    ) {}

    public function index(Request $request)
    {
        // Determine currency for session consistency if needed, 
        // though API usually is stateless. 
        // We'll let the Resource handle the display logic based on query param.
        
        $cars = Car::with(['photos', 'options'])->paginate(Car::PER_PAGE ?? 9);
        
        // Pass query params to pagination links
        $cars->appends($request->query());

        return CarResource::collection($cars);
    }

    public function show(Request $request, Car $car)
    {
        $car->load(['photos', 'options']);
        return new CarResource($car);
    }

    public function store(CarRequest $request)
    {
        $attributes = $request->only(['brand', 'model', 'year', 'price', 'description']);
        $options = $request->input('options', []);
        $photos = $request->file('photos', []);
        $primaryPhotoIndex = (int) $request->input('primary_photo_index', 0);

        $car = $this->carService->createCar($attributes, $options, $photos, $primaryPhotoIndex);

        return new CarResource($car->load(['photos', 'options']));
    }

    public function update(CarRequest $request, Car $car)
    {
        $attributes = $request->only(['brand', 'model', 'year', 'price', 'description']);
        $options = $request->has('options') ? $request->input('options') : null;
        $newPhotos = $request->file('photos', []);
        $removedPhotoIds = $request->input('removed_photo_ids', []);
        $primaryPhotoId = $request->input('primary_photo_id');

        $car = $this->carService->updateCar(
            $car, 
            $attributes, 
            $options, 
            $newPhotos, 
            $removedPhotoIds, 
            $primaryPhotoId
        );

        return new CarResource($car->load(['photos', 'options']));
    }

    public function destroy(Car $car)
    {
        $this->carService->deleteCar($car);
        return response()->json(null, 204);
    }
}
