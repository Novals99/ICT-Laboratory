<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\AssetSerialNumber;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\TransferRequest;
use App\Models\TransferRequestItem;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultipleSerialRequestsTest extends TestCase
{
    use DatabaseTransactions;

    private User $staffUser;
    private Laboratory $labA;
    private Laboratory $labB;
    private Asset $serialAsset;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a staff user
        $this->staffUser = User::factory()->create([
            'role' => 'staff',
            'nim' => '2512501492',
            'username' => 'teststaff',
        ]);

        // 2. Create labs
        $this->labA = Laboratory::create([
            'lab_name' => 'Lab A Test',
            'capacity' => 30,
        ]);
        $this->labB = Laboratory::create([
            'lab_name' => 'Lab B Test',
            'capacity' => 30,
        ]);

        // Assign staff to labA
        $this->staffUser->labs()->attach($this->labA->id);

        // 3. Create a serial-tracked asset
        $this->serialAsset = Asset::create([
            'asset_name' => 'Test GPU RTX 4080',
            'asset_category' => 'component-pc',
            'component_type' => 'vga',
            'total_good' => 10,
            'total_damaged' => 0,
            'total_loss' => 0,
        ]);

        // Link asset to Lab A stock
        AssetLab::create([
            'lab_id' => $this->labA->id,
            'asset_id' => $this->serialAsset->id,
            'total_good_lab' => 5,
            'total_damaged_lab' => 0,
            'total_loss_lab' => 0,
        ]);

        // Create serial numbers in Lab A
        for ($i = 1; $i <= 5; $i++) {
            AssetSerialNumber::create([
                'asset_id' => $this->serialAsset->id,
                'serial_number' => 'GPU-4080-' . $i,
                'condition' => 'good',
                'status' => 'available',
                'lab_id' => $this->labA->id,
            ]);
        }
    }

    public function test_transfer_request_creation_splits_multiple_serials_into_separate_items(): void
    {
        // Get the serials in labA
        $serials = AssetSerialNumber::where('lab_id', $this->labA->id)
            ->where('asset_id', $this->serialAsset->id)
            ->take(3)
            ->get();

        $serialIds = $serials->pluck('id')->toArray();

        $payload = [
            'from_lab_id' => $this->labA->id,
            'to_lab_id' => $this->labB->id,
            'notes' => 'Transfer request test',
            'items' => [
                [
                    'asset_id' => $this->serialAsset->id,
                    'quantity' => 3, // Synchronized with serial count
                    'notes' => 'Test GPU items',
                    'serial_number_ids' => $serialIds,
                ]
            ]
        ];

        $response = $this->actingAs($this->staffUser)
            ->post(route('transfer-requests.store'), $payload);

        $response->assertRedirect(route('transfer-requests.index'));

        // Check if TransferRequest was created
        $request = TransferRequest::where('from_lab_id', $this->labA->id)
            ->where('to_lab_id', $this->labB->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals(TransferRequest::STATUS_PENDING, $request->status);

        // Check that the request was split into 3 TransferRequestItems, each with qty 1 and a specific serial
        $items = TransferRequestItem::where('transfer_request_id', $request->id)->get();
        $this->assertCount(3, $items);

        foreach ($items as $item) {
            $this->assertEquals($this->serialAsset->id, $item->asset_id);
            $this->assertEquals(1, $item->quantity_requested);
            $this->assertContains($item->serial_number_id, $serialIds);
        }
    }

    public function test_return_request_creation_splits_multiple_serials_into_separate_items(): void
    {
        // Get the serials in labA
        $serials = AssetSerialNumber::where('lab_id', $this->labA->id)
            ->where('asset_id', $this->serialAsset->id)
            ->take(2)
            ->get();

        $serialIds = $serials->pluck('id')->toArray();

        $payload = [
            'lab_id' => $this->labA->id,
            'notes' => 'Return request test',
            'items' => [
                [
                    'asset_id' => $this->serialAsset->id,
                    'quantity' => 2, // Synchronized with serial count
                    'condition' => 'good',
                    'reason' => 'Returning GPU items',
                    'serial_number_ids' => $serialIds,
                ]
            ]
        ];

        $response = $this->actingAs($this->staffUser)
            ->post(route('return-requests.store'), $payload);

        $response->assertRedirect(route('return-requests.index'));

        // Check if ReturnRequest was created
        $request = ReturnRequest::where('lab_id', $this->labA->id)->first();

        $this->assertNotNull($request);
        $this->assertEquals(ReturnRequest::STATUS_PENDING, $request->status);

        // Check that the request was split into 2 ReturnRequestItems, each with qty 1 and a specific serial
        $items = ReturnRequestItem::where('return_request_id', $request->id)->get();
        $this->assertCount(2, $items);

        foreach ($items as $item) {
            $this->assertEquals($this->serialAsset->id, $item->asset_id);
            $this->assertEquals(1, $item->quantity_requested);
            $this->assertEquals('good', $item->condition);
            $this->assertContains($item->serial_number_id, $serialIds);
        }
    }
}
