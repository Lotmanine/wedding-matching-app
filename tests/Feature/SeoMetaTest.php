<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    private const NOINDEX = '<meta name="robots" content="noindex, nofollow">';

    private const HOME_DESCRIPTION = 'Portfolio demo of a wedding-services marketplace built with Laravel and Livewire.';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function english(string $url): TestResponse
    {
        return $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')->get($url);
    }

    public function test_home_has_description_canonical_and_social_tags(): void
    {
        $image = asset('images/og-image.jpg');

        $this->english('/')
            ->assertOk()
            ->assertSee('<meta name="description" content="'.self::HOME_DESCRIPTION, false)
            ->assertSee('<link rel="canonical" href="http://localhost">', false)
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<meta property="og:title" content="Wedding Matching App">', false)
            ->assertSee('<meta property="og:description" content="'.self::HOME_DESCRIPTION, false)
            ->assertSee('<meta property="og:url" content="http://localhost">', false)
            ->assertSee('<meta property="og:locale" content="en_US">', false)
            ->assertSee('<meta property="og:image" content="'.$image.'">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<meta name="twitter:image" content="'.$image.'">', false)
            ->assertDontSee(self::NOINDEX, false);
    }

    public function test_og_image_is_a_1200_by_630_file(): void
    {
        $path = public_path('images/og-image.jpg');

        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame([1200, 630], [$width, $height]);
    }

    public function test_canonical_drops_the_query_string(): void
    {
        $this->english('/request?utm_source=newsletter&ref=1')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="http://localhost/request">', false)
            ->assertSee('<meta property="og:url" content="http://localhost/request">', false)
            ->assertDontSee('utm_source', false);
    }

    public function test_each_public_page_has_its_own_description(): void
    {
        $pages = [
            '/request' => 'Describe your wedding once and the demo finds local providers',
            '/for-providers' => 'How wedding providers receive matched requests and unlock a couple&#039;s contact details',
            '/privacy' => 'Privacy policy of the Wedding Matching App portfolio demo',
            '/terms' => 'Terms of use of the Wedding Matching App portfolio demo',
        ];

        foreach ($pages as $url => $description) {
            $this->english($url)
                ->assertOk()
                ->assertSee('<meta name="description" content="'.$description, false)
                ->assertSee('<meta property="og:description" content="'.$description, false)
                ->assertDontSee(self::NOINDEX, false);
        }
    }

    public function test_page_title_is_reused_for_social_tags(): void
    {
        $this->english('/for-providers')
            ->assertOk()
            ->assertSee('<meta property="og:title" content="For providers · Wedding Matching App">', false)
            ->assertSee('<meta name="twitter:title" content="For providers · Wedding Matching App">', false);
    }

    public function test_description_and_locale_follow_french(): void
    {
        $this->withUnencryptedCookie(SetLocale::COOKIE, 'fr')
            ->get('/')
            ->assertOk()
            ->assertSee('<meta name="description" content="Démo de portfolio d&#039;une place de marché de prestataires de mariage', false)
            ->assertSee('<meta property="og:locale" content="fr_FR">', false);
    }

    public function test_home_json_ld_credits_the_developer(): void
    {
        $html = $this->english('/')->assertOk()->getContent();

        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $match));
        $data = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertSame('WebSite', $data['@type']);
        $this->assertSame('http://localhost', $data['url']);
        $this->assertSame([
            '@type' => 'Person',
            'name' => 'Lotfi Otmanine',
            'url' => 'https://oldev.site',
            'sameAs' => [
                'https://github.com/Lotmanine',
                'https://www.linkedin.com/in/lotfi-otmanine',
                'https://www.upwork.com/freelancers/~01bd748fcb665b90ca',
            ],
        ], $data['creator']);
    }

    public function test_json_ld_is_only_on_the_home_page(): void
    {
        $this->english('/request')
            ->assertOk()
            ->assertDontSee('application/ld+json', false);
    }

    public function test_public_footer_credits_the_developer_in_both_languages(): void
    {
        $link = '<a href="https://oldev.site/work/27-wedding-matching-app/"';

        $this->english('/')
            ->assertOk()
            ->assertSee($link, false)
            ->assertSee('Demo built by Lotfi Otmanine (OLDEV)')
            ->assertSee('Portfolio demo project. Not a real business');

        $this->withUnencryptedCookie(SetLocale::COOKIE, 'fr')
            ->get('/terms')
            ->assertOk()
            ->assertSee($link, false)
            ->assertSee('Démo créée par Lotfi Otmanine (OLDEV)');
    }

    public function test_login_and_account_pages_are_noindex(): void
    {
        foreach (['/provider/login', '/provider/register', '/provider/forgot-password', '/admin/login', '/login'] as $url) {
            $this->english($url)
                ->assertOk()
                ->assertSee(self::NOINDEX, false)
                ->assertDontSee('<link rel="canonical"', false);
        }
    }

    public function test_provider_area_is_noindex(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->assignRole('provider');

        $this->actingAs($provider)
            ->get('/provider/credits/history')
            ->assertOk()
            ->assertSee(self::NOINDEX, false);
    }

    public function test_admin_area_is_noindex(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee(self::NOINDEX, false);
    }
}
