<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\CarPhoto;
use App\Http\Requests\CarRequest;
use Illuminate\Support\Facades\Storage;

use Inertia\Inertia;

class CarController extends Controller
{
    public function index()
    {
        return Car::with('photos')->paginate(10);
    }

    public function home()
    {
        $cars = Car::with('photos')->paginate(10);
        $cars->setPath(route('cars.index'));
        
        return Inertia::render('Home', [
            'cars' => $cars,
        ]);
    }

    public function create()
    {
        return Inertia::render('AddCar');
    }

    public function show($id)
    {
        return Car::with('photos')->findOrFail($id);
    }


    public function store(CarRequest $request)
    {
        $car = Car::create($request->validated());

        if ($request->wantsJson() && !$request->inertia()) {
            return response()->json($car, 201);
        }

        return to_route('home');
    }

    public function update(CarRequest $request, $id)
    {
        $car = Car::findOrFail($id);
        $car->update($request->validated());
        return response()->json($car);
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();
        return response()->json(null, 204);
    }
}
