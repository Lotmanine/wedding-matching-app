<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\BudgetRange;
use App\Support\Money;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_dollars_and_euros(): void
    {
        $this->assertSame('$1,250', Money::format(1250, 'USD'));
        $this->assertSame("1\u{00A0}250\u{00A0}€", Money::format(1250, 'EUR'));
    }

    public function test_currency_follows_the_country_of_the_city(): void
    {
        $this->seed(CitySeeder::class);

        $this->assertSame('USD', Money::currencyForCity('NY'));
        $this->assertSame('EUR', Money::currencyForCity('PA'));
    }

    public function test_without_a_city_the_language_decides(): void
    {
        app()->setLocale('fr');
        $this->assertSame('EUR', Money::currencyForCity(null));

        app()->setLocale('en');
        $this->assertSame('USD', Money::currencyForCity(null));
    }

    public function test_budget_label_is_priced_in_the_given_currency(): void
    {
        $this->assertSame('$10,000 – $20,000', BudgetRange::From10kTo20k->label('USD'));
        $this->assertSame("10\u{00A0}000\u{00A0}€ – 20\u{00A0}000\u{00A0}€", BudgetRange::From10kTo20k->label('EUR'));
    }
}
