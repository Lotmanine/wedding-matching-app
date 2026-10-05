<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Support\LocaleCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_is_french_and_left_to_right_by_default(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="fr" dir="ltr"', false)
            ->assertSee('Trouvez des prestataires vérifiés pour votre mariage');
    }

    public function test_switch_stores_the_language_and_returns_to_the_page(): void
    {
        $this->from('/demande')
            ->get('/lang/ar')
            ->assertRedirect('/demande')
            ->assertPlainCookie(SetLocale::COOKIE, 'ar');
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->get('/lang/en')->assertNotFound();
    }

    public function test_arabic_pages_are_right_to_left_and_translated(): void
    {
        foreach (['/', '/demande', '/devenir-prestataire', '/confidentialite', '/conditions-utilisation', '/provider/login', '/provider/register'] as $url) {
            $this->withUnencryptedCookie(SetLocale::COOKIE, 'ar')
                ->get($url)
                ->assertOk()
                ->assertSee('<html lang="ar" dir="rtl"', false)
                ->assertDontSee('Soumettre une demande')
                ->assertDontSee('Espace prestataire');
        }

        $this->withUnencryptedCookie(SetLocale::COOKIE, 'ar')
            ->get('/')
            ->assertSee('اعثروا على مقدمي خدمات موثقين لعرسكم');
    }

    public function test_error_pages_follow_the_language(): void
    {
        $this->withUnencryptedCookie(SetLocale::COOKIE, 'ar')
            ->get('/cette-page-n-existe-pas')
            ->assertNotFound()
            ->assertSee('الصفحة غير موجودة');
    }

    public function test_back_office_stays_in_french(): void
    {
        $this->withUnencryptedCookie(SetLocale::COOKIE, 'ar')
            ->get('/admin/login')
            ->assertOk()
            ->assertDontSee('dir="rtl"', false);
    }

    public function test_every_french_language_line_has_an_arabic_one(): void
    {
        foreach (['form', 'provider', 'credits', 'app', 'validation'] as $file) {
            $fr = array_keys(\Illuminate\Support\Arr::dot(require lang_path("fr/{$file}.php")));
            $ar = array_keys(\Illuminate\Support\Arr::dot(require lang_path("ar/{$file}.php")));

            $this->assertSame([], array_values(array_diff($fr, $ar)), "Missing Arabic lines in {$file}.php");
        }
    }

    public function test_arabic_counts_agree_with_the_number(): void
    {
        app()->setLocale('ar');
        $this->assertSame('ساعتين', LocaleCount::hours(2));
        $this->assertSame('3 ساعات', LocaleCount::hours(3));
        $this->assertSame('14 يوما', LocaleCount::days(14));

        app()->setLocale('fr');
        $this->assertSame('2', LocaleCount::hours(2));
    }
}
