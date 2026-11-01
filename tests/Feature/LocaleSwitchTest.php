<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_uses_the_application_locale_without_a_cookie(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="' . config('app.locale') . '"', false)
            ->assertSee('Trouvez les prestataires de votre mariage');
    }

    public function test_switch_stores_the_language_and_returns_to_the_page(): void
    {
        $this->from('/demande')
            ->get('/lang/en')
            ->assertRedirect('/demande')
            ->assertPlainCookie(SetLocale::COOKIE, 'en');
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->get('/lang/ar')->assertNotFound();
    }

    public function test_pages_follow_the_language_cookie(): void
    {
        foreach (['/', '/demande', '/devenir-prestataire', '/confidentialite', '/conditions-utilisation', '/provider/login', '/provider/register'] as $url) {
            $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')
                ->get($url)
                ->assertOk()
                ->assertSee('<html lang="en"', false);
        }
    }

    public function test_unsupported_cookie_falls_back_to_the_application_locale(): void
    {
        $this->withUnencryptedCookie(SetLocale::COOKIE, 'ar')
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="' . config('app.locale') . '"', false);
    }

    public function test_back_office_follows_the_language_too(): void
    {
        $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')
            ->get('/admin/login')
            ->assertOk()
            ->assertSee('<html lang="en"', false);
    }

    public function test_language_switch_offers_english_and_french(): void
    {
        $this->get('/')
            ->assertSee(route('locale.switch', 'en'), false)
            ->assertSee(route('locale.switch', 'fr'), false)
            ->assertDontSee('/lang/ar', false);
    }
}
