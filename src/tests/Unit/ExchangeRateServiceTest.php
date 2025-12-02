<?php

namespace Tests\Unit;

use App\Services\ExchangeRateService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    #[Test]
    public function it_uses_the_configured_base_currency(): void
    {
        config(['currency.base' => 'EUR']);

        $service = new ExchangeRateService();

        $this->assertSame('EUR', $service->getBaseCurrency());
    }

    #[Test]
    public function it_falls_back_to_usd_when_base_currency_is_invalid(): void
    {
        config(['currency.base' => 'GBP']);

        $service = new ExchangeRateService();

        $this->assertSame('USD', $service->getBaseCurrency());
    }

    #[Test]
    public function it_converts_base_currency_amounts_to_uah(): void
    {
        config(['currency.base' => 'USD']);
        $service = new ExchangeRateService();

        $rates = [
            'USD' => 37.0,
            'UAH' => 1.0,
        ];

        $this->assertSame(3700.0, $service->convert(100, 'UAH', $rates));
    }

    #[Test]
    public function it_converts_between_non_base_currencies(): void
    {
        config(['currency.base' => 'USD']);
        $service = new ExchangeRateService();

        $rates = [
            'USD' => 37.0,
            'UAH' => 1.0,
            'EUR' => 40.0,
        ];

        $this->assertSame(92.5, $service->convert(100, 'EUR', $rates));
    }

    #[Test]
    public function it_returns_original_amount_when_rates_are_missing(): void
    {
        config(['currency.base' => 'USD']);
        $service = new ExchangeRateService();

        $rates = [
            'USD' => null,
        ];

        $this->assertSame(100.0, $service->convert(100, 'EUR', $rates));
    }
}
