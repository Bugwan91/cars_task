<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarOption;
use App\Http\Requests\CarRequest;

use Inertia\Inertia;

class CarController extends Controller
{
    public function index()
    {
        return Car::with(['photos', 'options'])->paginate(10);
    }

    public function home()
    {
        $cars = Car::with(['photos', 'options'])->paginate(10);
        $cars->setPath(route('cars.index'));
        
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

    public function show($id)
    {
        return Car::with(['photos', 'options'])->findOrFail($id);
    }


    public function store(CarRequest $request)
    {
        $car = Car::create($request->validated());
        $this->syncOptions($car, $request->input('options', []));

        if ($request->wantsJson() && !$request->inertia()) {
            return response()->json($car->load(['photos', 'options']), 201);
        }

        return to_route('home');
    }

    public function update(CarRequest $request, $id)
    {
        $car = Car::findOrFail($id);
        $car->update($request->validated());
        $this->syncOptions($car, $request->input('options', []));
        return response()->json($car->load(['photos', 'options']));
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();
        return response()->json(null, 204);
    }

    private function syncOptions(Car $car, array $options): void
    {
        $normalized = collect($options)
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            $car->options()->detach();
            return;
        }

        $ids = $normalized->map(fn (string $name) => CarOption::firstOrCreate(['name' => $name])->id);

        $car->options()->sync($ids->all());
    }
}
