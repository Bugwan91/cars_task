<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// TODO: Ideally, this should runs as a scheduled job to keep rates updated.
// But for now, it is fine to slow some random user once in a while.
class ExchangeRateService
{
    public const SUPPORTED_CURRENCIES = ['UAH', 'USD', 'EUR'];
    private const CACHE_KEY = 'exchange_rates';
    private const CACHE_TTL_MINUTES = 60;
    private const API_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

    private string $baseCurrency;

    public function __construct()
    {
        $configured = strtoupper(config('currency.base', 'USD'));
        $this->baseCurrency = in_array($configured, self::SUPPORTED_CURRENCIES, true) ? $configured : 'USD';
    }

    public function getRates(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            return $this->refreshRates();
        });
    }

    public function refreshRates(): array
    {
        $response = Http::timeout(5)->get(self::API_URL, ['json' => '']);

        if (!$response->ok()) {
            return $this->loadRatesFromDatabase();
        }

        $data = $response->json();
        $normalized = [];

        foreach ($data as $entry) {
            $currency = strtoupper($entry['cc'] ?? '');
            $rate = $entry['rate'] ?? null;

            if (in_array($currency, self::SUPPORTED_CURRENCIES, true) && $rate) {
                $normalized[$currency] = (float) $rate;
            }
        }

        foreach (self::SUPPORTED_CURRENCIES as $currency) {
            if (!array_key_exists($currency, $normalized)) {
                $existing = ExchangeRate::where('currency', $currency)->value('rate');
                if ($currency === 'UAH') {
                    $normalized[$currency] = 1.0;
                    continue;
                }

                $normalized[$currency] = $existing;
            }
        }

        if (!isset($normalized['UAH'])) {
            $normalized['UAH'] = 1.0;
        }

        foreach ($normalized as $currency => $rate) {
            if ($rate === null) {
                continue;
            }
            ExchangeRate::updateOrCreate(
                ['currency' => $currency],
                ['rate' => $rate]
            );
        }

        return $normalized;
    }

    private function loadRatesFromDatabase(): array
    {
        $rates = ExchangeRate::all()->pluck('rate', 'currency')->toArray();

        if (!isset($rates['UAH'])) {
            $rates['UAH'] = 1.0;
        }

        return $rates;
    }

    public function convert(?float $amount, string $targetCurrency, ?array $rates = null): ?float
    {
        if ($amount === null) {
            return null;
        }

        $rates = $rates ?? $this->getRates();
        $target = strtoupper($targetCurrency);
        $base = $this->baseCurrency;

        if ($target === $base) {
            return round($amount, 2);
        }

        $baseRate = $rates[$base] ?? null;

        if (!$baseRate || $baseRate <= 0) {
            return round($amount, 2);
        }

        $amountInUah = $amount * $baseRate;

        if ($target === 'UAH') {
            return round($amountInUah, 2);
        }

        $targetRate = $rates[$target] ?? null;

        if (!$targetRate || $targetRate <= 0) {
            return round($amount, 2);
        }

        return round($amountInUah / $targetRate, 2);
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function formatRatesForFrontend(array $rates): array
    {
        return collect(self::SUPPORTED_CURRENCIES)
            ->mapWithKeys(fn ($currency) => [$currency => $rates[$currency] ?? null])
            ->toArray();
    }
}
