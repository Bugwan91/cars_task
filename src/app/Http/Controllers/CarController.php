<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarOption;
use App\Http\Requests\CarRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CarController extends Controller
{
    public function index()
    {
        return Car::with(['photos', 'options'])->paginate(9);
    }

    public function home()
    {
        $cars = Car::with(['photos', 'options'])->paginate(9);
        $cars->setPath(route('api.cars.index'));
        
        return Inertia::render('Home', [
            'cars' => $cars,
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

    public function show(Car $car)
    {
        $car->load(['photos', 'options']);

        if (request()->wantsJson() && !request()->inertia()) {
            return response()->json($car);
        }

        return Inertia::render('CarView', [
            'car' => $car,
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

            $car->photos()->create([
                'photo_path' => $path,
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
}
