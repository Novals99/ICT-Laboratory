<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\AssetSerialNumber;
use App\Models\Laboratory;
use App\Models\Pc;
use Illuminate\Http\Request;

/**
 * Sumber data untuk SEMUA dropdown serial number di aplikasi.
 *
 * Dipakai oleh:
 *  - Modal Edit Asset & Asset Information (lihat/edit S/N suatu asset).
 *  - Modal Add PC / Edit PC / Create Laboratory step-2
 *    (pilih komponen per slot, difilter component_type, hanya S/N yang available).
 */
class SerialNumberController extends Controller
{
    /**
     * Daftar asset SPV yang punya serial number available (untuk dropdown di modal Add Asset).
     * GET /api/spv-assets-with-serials
     *
     * Balikan: [ { id, asset_name, asset_category, component_type, available_count } ]
     */
    public function spvAssets()
    {
        $assets = Asset::whereIn('asset_category', ['electronic', 'component-pc', 'pc', 'non-electronic'])
            ->whereHas('serialNumbers', fn ($q) => $q->where('status', 'available'))
            ->withCount(['serialNumbers as available_count' => fn ($q) => $q->where('status', 'available')])
            ->orderBy('asset_name')
            ->get(['id', 'asset_name', 'asset_category', 'component_type']);

        return response()->json($assets);
    }

    /**
     * S/N yang available milik satu asset SPV (untuk dropdown serial di modal Add Asset).
     * GET /api/assets/{asset}/available-serials
     */
    public function availableSerials(Asset $asset)
    {
        return response()->json([
            'asset_id'   => $asset->id,
            'asset_name' => $asset->asset_name,
            'serials'    => $asset->serialNumbers()
                ->where('status', 'available')
                ->orderBy('serial_number')
                ->get(['id', 'serial_number', 'condition'])
                ->map(fn ($s) => [
                    'id'            => $s->id,
                    'serial_number' => $s->serial_number,
                    'condition'     => $s->condition,
                ]),
        ]);
    }

    /**
     * Semua S/N milik satu asset (untuk modal Edit Asset / Asset Information).
     * GET /api/assets/{asset}/serials
     */
    public function byAsset(Asset $asset)
    {
        return response()->json([
            'asset_id'   => $asset->id,
            'asset_name' => $asset->asset_name,
            'total'      => $asset->total_asset,
            'serials'    => $asset->serialNumbers()
                ->orderBy('serial_number')
                ->get(['id', 'serial_number', 'condition', 'status', 'lab_id', 'pc_id'])
                ->map(fn ($s) => [
                    'id'            => $s->id,
                    'serial_number' => $s->serial_number,
                    'condition'     => $s->condition,
                    'status'        => $s->status,        // available / in_use
                    'locked'        => $s->status === 'in_use', // sedang terpasang di PC → tidak boleh dihapus
                ]),
        ]);
    }

    /**
     * Komponen PC yang tersedia di sebuah lab, dikelompokkan per component_type,
     * lengkap dengan daftar S/N yang masih available.
     *
     * GET /api/laboratory/{laboratory}/pc-components?exclude_pc={pcId}
     *
     * `exclude_pc`: saat EDIT PC, S/N yang sedang dipakai PC itu sendiri tetap
     * ditampilkan (supaya nilainya tidak hilang dari dropdown).
     *
     * Struktur balikan:
     * {
     *   "processor": [ { asset_id, asset_name, serials: [ {id, serial_number} ] } ],
     *   "ram": [...], "ssd": [...], ...
     * }
     */
    public function pcComponents(Laboratory $laboratory, Request $request)
    {
        $excludePcId = $request->integer('exclude_pc') ?: null;

        // S/N yang sedang dipakai PC yang sedang diedit → tetap dianggap "boleh dipilih".
        $keepSerialIds = [];
        if ($excludePcId && ($pc = Pc::find($excludePcId))) {
            $keepSerialIds = $pc->usedSerialIds();
        }

        // Asset komponen/PC yang stok-nya ada di lab ini.
        $componentAssetIds = AssetLab::where('lab_id', $laboratory->id)
            ->whereHas('asset', fn ($q) => $q->whereIn('asset_category', ['component-pc', 'pc', 'non-electronic']))
            ->where('total_good_lab', '>', 0)
            ->pluck('asset_id');

        $assets = Asset::whereIn('id', $componentAssetIds)
            ->whereIn('asset_category', ['component-pc', 'pc', 'non-electronic'])
            ->with(['serialNumbers' => function ($q) use ($laboratory, $keepSerialIds) {
                $q->where('lab_id', $laboratory->id)
                  ->where(function ($w) use ($keepSerialIds) {
                      $w->where('status', 'available');
                      if (! empty($keepSerialIds)) {
                          $w->orWhereIn('id', $keepSerialIds);
                      }
                  })
                  ->orderBy('serial_number');
            }])
            ->orderBy('asset_name')
            ->get();

        // Kelompokkan per component_type (semua tipe muncul walau kosong).
        $grouped = [];
        $grouped['pc'] = []; // Add whole unit PC slot
        foreach (Pc::SLOT_COMPONENT_TYPE as $type) {
            $grouped[$type] = [];
        }

        foreach ($assets as $asset) {
            $type = $asset->asset_category === 'pc' ? 'pc' : $asset->component_type;
            if (! $type || ! array_key_exists($type, $grouped)) {
                continue;
            }

            $grouped[$type][] = [
                'asset_id'      => $asset->id,
                'asset_name'    => $asset->asset_name,
                'specification' => $asset->specification,
                'serials'       => $asset->serialNumbers
                    ->map(fn ($s) => [
                        'id'            => $s->id,
                        'serial_number' => $s->serial_number,
                    ])
                    ->values(),
            ];
        }

        return response()->json($grouped);
    }

    /**
     * (#14) Daftar serial sebuah aset YANG ADA DI LAB tertentu (untuk modal Asset Information).
     */
    public function byAssetInLab(Laboratory $laboratory, Asset $asset)
    {
        $serials = $asset->serialNumbers()
            ->where('lab_id', $laboratory->id)
            ->orderBy('serial_number')
            ->get(['id', 'serial_number', 'condition', 'status', 'pc_id']);

        return response()->json([
            'asset_name' => $asset->asset_name,
            'serials'    => $serials->map(fn ($s) => [
                'id'            => $s->id,
                'serial_number' => $s->serial_number,
                'condition'     => $s->condition,
                'status'        => $s->status,
                // terpasang di PC → tidak boleh diubah/hapus dari sini
                'locked'        => $s->status === 'in_use',
            ])->values(),
        ]);
    }

    /**
     * (#14) SPV menyimpan perubahan nomor seri unit yang ada di lab.
     * Hanya mengubah nilai serial_number (jumlah tetap mengikuti stok lab).
     */
    public function syncInLab(Request $request, Laboratory $laboratory, Asset $asset)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        $data = $request->validate([
            'serials'                 => ['array'],
            'serials.*.id'            => ['nullable', 'integer'],
            'serials.*.serial_number' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['serials'] ?? [] as $row) {
            if (empty($row['id']) || empty($row['serial_number'])) {
                continue;
            }

            $serial = $asset->serialNumbers()
                ->where('lab_id', $laboratory->id)
                ->find($row['id']);

            // Jangan ubah serial yang sedang terpasang di PC.
            if ($serial && $serial->status !== 'in_use') {
                $serial->update(['serial_number' => trim($row['serial_number'])]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * S/N yang available milik asset SPV / SPV warehouse (lab_id is null).
     * GET /api/assets/{asset}/available-spv-serials
     */
    public function availableSpvSerials(Asset $asset, Request $request)
    {
        $excludeItem = $request->input('exclude_item');
        return response()->json([
            'asset_id'   => $asset->id,
            'asset_name' => $asset->asset_name,
            'serials'    => $asset->serialNumbers()
                ->where('status', 'available')
                ->whereNull('lab_id')
                ->where(function ($q) use ($excludeItem) {
                    $q->whereNull('request_item_id');
                    if ($excludeItem) {
                        $q->orWhere('request_item_id', $excludeItem);
                    }
                })
                ->orderBy('serial_number')
                ->get(['id', 'serial_number', 'condition'])
                ->map(fn ($s) => [
                    'id'            => $s->id,
                    'serial_number' => $s->serial_number,
                    'condition'     => $s->condition,
                ]),
        ]);
    }

    /**
     * S/N milik asset tertentu di lab tertentu, termasuk yang terpasang di PC.
     * GET /api/laboratory/{laboratory}/assets/{asset}/serials-with-pc
     */
    public function byAssetInLabWithPc(Laboratory $laboratory, Asset $asset)
    {
        $serials = $asset->serialNumbers()
            ->where('lab_id', $laboratory->id)
            ->with('pc:id,sku')
            ->orderBy('serial_number')
            ->get();

        return response()->json([
            'asset_id'   => $asset->id,
            'asset_name' => $asset->asset_name,
            'serials'    => $serials->map(fn ($s) => [
                'id'            => $s->id,
                'serial_number' => $s->serial_number,
                'condition'     => $s->condition,
                'status'        => $s->status,
                'pc_id'         => $s->pc_id,
                'pc_sku'        => $s->pc?->sku,
            ]),
        ]);
    }
}
