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

        // Asset komponen yang stok-nya ada di lab ini.
        $componentAssetIds = AssetLab::where('lab_id', $laboratory->id)
            ->whereHas('asset', fn ($q) => $q->where('asset_category', 'component-pc'))
            ->where('total_good_lab', '>', 0)
            ->pluck('asset_id');

        $assets = Asset::whereIn('id', $componentAssetIds)
            ->where('asset_category', 'component-pc')
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
        foreach (Pc::SLOT_COMPONENT_TYPE as $type) {
            $grouped[$type] = [];
        }

        foreach ($assets as $asset) {
            $type = $asset->component_type;
            if (! $type || ! array_key_exists($type, $grouped)) {
                continue;
            }

            $grouped[$type][] = [
                'asset_id'   => $asset->id,
                'asset_name' => $asset->asset_name,
                'serials'    => $asset->serialNumbers
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
}
