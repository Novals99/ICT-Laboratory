<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AssetStockSerialTest extends TestCase
{
    use DatabaseTransactions;

    private User $spvUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spvUser = User::factory()->create([
            'role' => 'spv inventory',
            'nim' => '2512501491',
            'username' => 'testspv',
        ]);
    }

    public function test_create_stock_without_manual_serials_auto_generates_serials(): void
    {
        $payload = [
            'asset_category' => 'component-pc',
            'items' => [
                [
                    'asset_name' => 'Gigabyte RTX 4060',
                    'asset_category' => 'component-pc',
                    'component_type' => 'vga',
                    'total_asset' => 15,
                    'total_good' => 15,
                    'total_damaged' => 0,
                    'total_loss' => 0,
                    'source' => 'Purchase',
                    'notes' => 'New stock',
                    'serials' => [], // Empty serials
                ]
            ]
        ];

        $response = $this->actingAs($this->spvUser)
            ->post(route('asset.store'), $payload);

        $response->assertRedirect(route('asset.index'));

        $asset = Asset::where('asset_name', 'Gigabyte RTX 4060')->first();
        $this->assertNotNull($asset);
        $this->assertEquals(15, $asset->total_good);

        // Verify serial numbers count
        $this->assertEquals(15, $asset->serialNumbers()->count());
        $this->assertStringEndsWith('-001', $asset->serialNumbers()->first()->serial_number);
    }

    public function test_edit_stock_without_adding_serials_auto_generates_difference(): void
    {
        // 1. Create asset
        $asset = Asset::create([
            'asset_name' => 'Intel Core i5-12400F',
            'asset_category' => 'component-pc',
            'component_type' => 'processor',
            'total_good' => 2,
            'total_damaged' => 0,
            'total_loss' => 0,
        ]);

        // Manually create the 2 initial serials since direct Eloquent create doesn't go through Controller
        \App\Models\AssetSerialNumber::create([
            'asset_id' => $asset->id,
            'serial_number' => $asset->sku . '-001',
            'condition' => 'good',
            'status' => 'available',
        ]);
        \App\Models\AssetSerialNumber::create([
            'asset_id' => $asset->id,
            'serial_number' => $asset->sku . '-002',
            'condition' => 'good',
            'status' => 'available',
        ]);
        
        $asset->refresh();
        $this->assertEquals(2, $asset->serialNumbers()->count());

        // 2. Update good stock from 2 to 10, but only keep/submit the first 2 serials in payload
        $serials = $asset->serialNumbers->pluck('serial_number')->toArray(); // e.g. ['CPU-0001-001', 'CPU-0001-002']

        $payload = [
            'asset_name' => 'Intel Core i5-12400F',
            'asset_category' => 'component-pc',
            'component_type' => 'processor',
            'total_asset' => 10,
            'total_good' => 10,
            'total_damaged' => 0,
            'total_loss' => 0,
            'serials' => $serials, // only 2 serials submitted
        ];

        $response = $this->actingAs($this->spvUser)
            ->put(route('asset.update', $asset->id), $payload);

        $response->assertRedirect(route('asset.index'));

        $asset->refresh();
        $this->assertEquals(10, $asset->total_good);

        // Verify serial numbers count is now 10
        $this->assertEquals(10, $asset->serialNumbers()->count());
    }
}
