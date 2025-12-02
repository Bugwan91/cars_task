<?php

namespace App\Http\Resources;

use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    // We can inject these or set them via a static method/middleware if needed.
    // For now, we'll resolve the service and use the request to determine currency,
    // similar to how the controller did it, but encapsulated.
    
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Car $car */
        $car = $this->resource;
        
        // Resolve dependencies
        $exchangeRateService = app(ExchangeRateService::class);
        
        // Determine currency (logic adapted from Controller)
        $defaultCurrency = config('currency.default_display', 'USD');
        $currency = strtoupper($request->query(
            'currency',
            session('currency', $defaultCurrency)
        ));

        if (!in_array($currency, ExchangeRateService::SUPPORTED_CURRENCIES, true)) {
            $currency = $defaultCurrency;
        }
        
        // We don't set session here as that's a side effect, 
        // but for consistency with the app's behavior we might rely on the controller doing it 
        // or just read what's available. 
        // Ideally, resources shouldn't have side effects like setting session.
        
        $rates = $exchangeRateService->getRates();
        $baseCurrency = $exchangeRateService->getBaseCurrency();
        
        $primaryPhoto = $car->photos->first(); // Assumes eager loaded or will lazy load

        return [
            'id' => $car->id,
            'brand' => $car->brand,
            'model' => $car->model,
            'year' => $car->year,
            'price' => $car->price,
            'description' => $car->description,
            'created_at' => $car->created_at,
            'updated_at' => $car->updated_at,
            
            // Computed/Formatted fields
            'display_currency' => $currency,
            'original_price' => $car->price,
            'base_currency' => $baseCurrency,
            'display_price' => $exchangeRateService->convert($car->price, $currency, $rates),
            
            'primary_photo_path' => $primaryPhoto ? $primaryPhoto->photo_path : null,
            'primary_thumbnail_path' => $primaryPhoto ? $primaryPhoto->thumbnail_path : null,
            
            // Relationships
            'photos' => $car->photos, // You might want a PhotoResource
            'options' => $car->options, // You might want an OptionResource
        ];
    }
}
