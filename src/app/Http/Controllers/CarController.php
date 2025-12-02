<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarOption;
use App\Http\Requests\CarRequest;
use App\Services\ExchangeRateService;
use App\Services\ImageVariantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CarController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRateService,
        private ImageVariantService $imageVariantService
    )
    {
    }

    public function index(Request $request)
    {
        $currency = $this->determineCurrency($request);
        $rates = $this->exchangeRateService->getRates();

        $cars = Car::with(['photos', 'options'])->paginate(9);
        $cars->appends(['currency' => $currency]);

        $cars->getCollection()->transform(fn ($car) => $this->formatCarForResponse($car, $currency, $rates));

        return $cars;
    }

    public function home(Request $request)
    {
        $currency = $this->determineCurrency($request);
        $rates = $this->exchangeRateService->getRates();

        $cars = Car::with(['photos', 'options'])->paginate(9);
        $cars->setPath(route('api.cars.index'));
        $cars->appends(['currency' => $currency]);
        $cars->getCollection()->transform(fn ($car) => $this->formatCarForResponse($car, $currency, $rates));
        
        return Inertia::render('Home', [
            'cars' => $cars,
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
            'car' => $car,
            'availableOptions' => CarOption::orderBy('name')->pluck('name'),
        ]);
    }

    public function show(Request $request, Car $car)
    {
        $car->load(['photos', 'options']);
        $currency = $this->determineCurrency($request);
        $rates = $this->exchangeRateService->getRates();
        $this->formatCarForResponse($car, $currency, $rates);

        if ($request->wantsJson() && !$request->inertia()) {
            return response()->json($car);
        }

        return Inertia::render('CarView', [
            'car' => $car,
            'currency' => $this->currencyPayload($currency, $rates),
        ]);
    }


    public function store(CarRequest $request)
    {
        $car = Car::create($this->extractCarAttributes($request));
        $this->storePhotos($car, $request);
        $this->syncOptions($car, $request->input('options', []), true);

        if ($request->wantsJson() && !$request->inertia()) {
            return response()->json($car->load(['photos', 'options']), 201);
        }

        return to_route('home');
    }

    public function update(CarRequest $request, Car $car)
    {
        $car->update($this->extractCarAttributes($request, $car));
        $optionsPayload = $request->has('options') ? $request->input('options') : null;
        $this->syncOptions($car, $optionsPayload, $request->has('options'));
        $this->removePhotos($car, $request->input('removed_photo_ids', []));
        $this->storePhotos($car, $request);

        $car->load(['photos', 'options']);
        $this->setPrimaryPhoto($car, $request);

        if ($request->wantsJson() && !$request->inertia()) {
            return response()->json($car);
        }

        return to_route('cars.show', $car);
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();
        return response()->json(null, 204);
    }

    private function syncOptions(Car $car, ?array $options, bool $detachWhenEmpty = false): void
    {
        if ($options === null) {
            return;
        }

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
    
    private function extractCarAttributes(CarRequest $request, ?Car $car = null): array
    {
        $fields = ['brand', 'model', 'year', 'price', 'description'];
        $payload = [];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $payload[$field] = $request->input($field);
            } elseif ($car) {
                $payload[$field] = $car->{$field};
            }
        }

        return $payload;
    }
    
    private function storePhotos(Car $car, CarRequest $request): void
    {
        if (!$request->hasFile('photos')) {
            return;
        }

        $files = $request->file('photos');
        $count = count($files);

        if ($count === 0) {
            return;
        }

        $primaryIndex = min(
            max((int) $request->input('primary_photo_index', 0), 0),
            $count - 1
        );

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

    private function setPrimaryPhoto(Car $car, CarRequest $request): void
    {
        $photoId = $request->input('primary_photo_id');

        if ($photoId && $car->photos()->where('id', $photoId)->exists()) {
            $car->photos()->update(['is_primary' => false]);
            $car->photos()->where('id', $photoId)->update(['is_primary' => true]);
            return;
        }

        $currentPrimary = $car->photos()->where('is_primary', true)->orderByDesc('id')->first();

        if ($currentPrimary) {
            $car->photos()->where('id', '!=', $currentPrimary->id)->update(['is_primary' => false]);
            return;
        }

        $fallback = $car->photos()->orderBy('id')->first();

        if ($fallback) {
            $car->photos()->update(['is_primary' => false]);
            $fallback->update(['is_primary' => true]);
        }
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

    private function formatCarForResponse(Car $car, string $currency, array $rates): Car
    {
        $primaryPhoto = $car->photos->first();

        $baseCurrency = $this->exchangeRateService->getBaseCurrency();

        $car->setAttribute('display_currency', $currency);
        $car->setAttribute('original_price', $car->price);
        $car->setAttribute('base_currency', $baseCurrency);
        $car->setAttribute('display_price', $this->exchangeRateService->convert($car->price, $currency, $rates));

        if ($primaryPhoto) {
            $car->setAttribute('primary_photo_path', $primaryPhoto->photo_path);
            $car->setAttribute('primary_thumbnail_path', $primaryPhoto->thumbnail_path);
        }

        return $car;
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
