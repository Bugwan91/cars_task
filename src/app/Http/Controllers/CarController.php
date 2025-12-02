<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarOption;
use App\Http\Requests\CarRequest;
use App\Http\Resources\CarResource;
use App\Services\CarService;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRateService,
        private CarService $carService
    )
    {
    }

    public function index(Request $request)
    {
        $currency = $this->determineCurrency($request);
        
        $cars = Car::with(['photos', 'options'])->paginate(Car::PER_PAGE ?? 9);
        $cars->appends(['currency' => $currency]);

        return CarResource::collection($cars);
    }

    public function home(Request $request)
    {
        $currency = $this->determineCurrency($request);
        $rates = $this->exchangeRateService->getRates();

        $cars = Car::with(['photos', 'options'])->paginate(Car::PER_PAGE ?? 9);
        $cars->setPath(route('api.cars.index'));
        $cars->appends(['currency' => $currency]);
        
        return Inertia::render('Home', [
            'cars' => CarResource::collection($cars),
            'currency' => $this->currencyPayload($currency, $rates),
        ]);
    }

    public function create()
    {
        return Inertia::render('AddCar', [
            'availableOptions' => CarOption::orderBy('name')->pluck('name'),
        ]);
    }

    public function edit(Car $car)
    {
        $car->load(['photos', 'options']);

        return Inertia::render('EditCar', [
            'car' => (new CarResource($car))->resolve(),
            'availableOptions' => CarOption::orderBy('name')->pluck('name'),
        ]);
    }

    public function show(Request $request, Car $car)
    {
        $car->load(['photos', 'options']);
        $currency = $this->determineCurrency($request);
        $rates = $this->exchangeRateService->getRates();

        return Inertia::render('CarView', [
            'car' => (new CarResource($car))->resolve(),
            'currency' => $this->currencyPayload($currency, $rates),
        ]);
    }

    public function store(CarRequest $request)
    {
        $attributes = $request->only(['brand', 'model', 'year', 'price', 'description']);
        $options = $request->input('options', []);
        $photos = $request->file('photos', []);
        $primaryPhotoIndex = (int) $request->input('primary_photo_index', 0);

        $car = $this->carService->createCar($attributes, $options, $photos, $primaryPhotoIndex);

        return to_route('cars.show', $car);
    }

    public function update(CarRequest $request, Car $car)
    {
        $attributes = $request->only(['brand', 'model', 'year', 'price', 'description']);
        $options = $request->has('options') ? $request->input('options') : null;
        $newPhotos = $request->file('photos', []);
        $removedPhotoIds = $request->input('removed_photo_ids', []);
        $primaryPhotoId = $request->input('primary_photo_id');

        $this->carService->updateCar(
            $car, 
            $attributes, 
            $options, 
            $newPhotos, 
            $removedPhotoIds, 
            $primaryPhotoId
        );

        return to_route('cars.show', $car);
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $this->carService->deleteCar($car);
        
        return to_route('home');
    }

    private function determineCurrency(Request $request): string
    {
        $defaultCurrency = config('currency.default_display', 'USD');
        $currency = strtoupper($request->query(
            'currency',
            session('currency', $defaultCurrency)
        ));

        if (!in_array($currency, ExchangeRateService::SUPPORTED_CURRENCIES, true)) {
            $currency = $defaultCurrency;
        }

        session(['currency' => $currency]);

        return $currency;
    }

    private function currencyPayload(string $currency, array $rates): array
    {
        return [
            'selected' => $currency,
            'available' => ExchangeRateService::SUPPORTED_CURRENCIES,
            'base' => $this->exchangeRateService->getBaseCurrency(),
            'rates' => $this->exchangeRateService->formatRatesForFrontend($rates),
        ];
    }
}
