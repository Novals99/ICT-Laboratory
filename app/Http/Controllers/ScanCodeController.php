<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScanCodeController extends Controller
{
    /**
     * Tampilkan halaman Scan Code.
     */
    public function index()
    {
        return view('pages.scan-code.index');
    }

    /**
     * Lookup barcode value: cari di SKU asset atau Serial Number.
     * Return JSON response.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
        ]);

        $barcode = trim($request->input('barcode'));

        // 1. Cari berdasarkan SKU asset
        $asset = Asset::where('sku', $barcode)->first();
        if ($asset) {
            return response()->json([
                'found'  => true,
                'type'   => 'sku',
                'asset'  => [
                    'id'             => $asset->id,
                    'asset_name'     => $asset->asset_name,
                    'sku'            => $asset->sku,
                    'asset_category' => $asset->asset_category,
                    'component_type' => $asset->component_type,
                    'total_asset'    => $asset->total_asset,
                    'total_good'     => $asset->total_good,
                    'total_damaged'  => $asset->total_damaged,
                    'total_loss'     => $asset->total_loss,
                ],
                'serial' => null,
            ]);
        }

        // 2. Cari berdasarkan Serial Number
        $serial = AssetSerialNumber::with(['asset', 'lab'])
            ->where('serial_number', $barcode)
            ->first();

        if ($serial) {
            return response()->json([
                'found'  => true,
                'type'   => 'serial',
                'asset'  => [
                    'id'             => $serial->asset->id ?? null,
                    'asset_name'     => $serial->asset->asset_name ?? '-',
                    'sku'            => $serial->asset->sku ?? '-',
                    'asset_category' => $serial->asset->asset_category ?? '-',
                    'component_type' => $serial->asset->component_type ?? null,
                    'total_asset'    => $serial->asset->total_asset ?? 0,
                    'total_good'     => $serial->asset->total_good ?? 0,
                    'total_damaged'  => $serial->asset->total_damaged ?? 0,
                    'total_loss'     => $serial->asset->total_loss ?? 0,
                ],
                'serial' => [
                    'id'            => $serial->id,
                    'serial_number' => $serial->serial_number,
                    'condition'     => $serial->condition,
                    'status'        => $serial->status,
                    'lab_name'      => $serial->lab->lab_name ?? null,
                    'slot'          => $serial->slot,
                    'notes'         => $serial->notes,
                ],
            ]);
        }

        // 3. Tidak ditemukan
        return response()->json([
            'found'   => false,
            'type'    => null,
            'asset'   => null,
            'serial'  => null,
            'message' => "Barcode \"{$barcode}\" tidak ditemukan dalam sistem.",
        ]);
    }
}
