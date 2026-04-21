<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\UnlockPack;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function admin(): User
    {
        $a = User::factory()->create(['role' => 'admin']);
        $a->assignRole('admin');

        return $a;
    }

    public function test_create_pack(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.packs.store'), [
                'name'          => 'Pack XL',
                'unlocks_count' => 20,
                'price_dzd'     => 20000,
                'is_popular'    => false,
                'active'        => true,
                'sort_order'    => 5,
            ])
            ->assertRedirect(route('admin.packs.index'));

        $pack = UnlockPack::where('name', 'Pack XL')->first();
        $this->assertNotNull($pack);
        $this->assertSame(20, $pack->unlocks_count);
        $this->assertTrue(AuditLog::where('action', 'pack.create')->exists());
    }

    public function test_update_pack(): void
    {
        $pack = UnlockPack::create([
            'name' => 'Old', 'slug' => 'old-test', 'unlocks_count' => 5, 'price_dzd' => 5000,
            'is_popular' => false, 'active' => true, 'sort_order' => 1,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.packs.update', $pack->id), [
                'name'          => 'Updated',
                'unlocks_count' => 6,
                'price_dzd'     => 6000,
            ])
            ->assertRedirect();

        $pack->refresh();
        $this->assertSame('Updated', $pack->name);
        $this->assertSame(6, $pack->unlocks_count);
    }

    public function test_toggle_pack(): void
    {
        $pack = UnlockPack::create([
            'name' => 'X', 'slug' => 'x-test', 'unlocks_count' => 1, 'price_dzd' => 100,
            'is_popular' => false, 'active' => true, 'sort_order' => 0,
        ]);

        $this->actingAs($this->admin())->post(route('admin.packs.toggle', $pack->id))->assertRedirect();
        $this->assertFalse($pack->fresh()->active);
    }
}
