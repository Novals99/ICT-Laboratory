<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\AssetSerialNumber;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exports\AssetExport;

class AssetController extends Controller
{
    /** Kategori yang memakai kode inventaris per unit. */
    private const SERIAL_CATEGORIES = ['electronic', 'pc', 'non-electronic'];

    /** Sub-tipe komponen yang valid (untuk PC Component). */
    private const COMPONENT_TYPES = ['processor', 'ram', 'ssd', 'motherboard', 'vga', 'cpu_fan', 'powersupply', 'hdd'];

    public function index()
    {
        $assets = Asset::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_category', 'like', "%{$search}%")
                        ->orWhere('component_type', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(request('category'), function ($query, $categories) {
                $query->whereIn('asset_category', (array) $categories);
            })
            ->withCount('serialNumbers')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.asset.index', compact('assets'));
    }

    public function create()
    {
        return redirect()->route('asset.index');
    }

    public function store(Request $request)
    {
        // (#17) Category kini bisa per-item (dropdown di dalam Asset Information).
        // Tetap menerima asset_category top-level agar form lama tidak rusak.
        $validated = $request->validate([
            'asset_category' => ['nullable', Rule::in(['electronic', 'non-electronic', 'component-pc', 'pc'])],

            'items' => ['required', 'array', 'min:1'],

            'items.*.asset_name'     => ['required', 'string', 'max:255'],
            'items.*.asset_category' => ['nullable', Rule::in(['electronic', 'non-electronic', 'component-pc', 'pc'])],
            'items.*.component_type' => ['nullable', Rule::in(self::COMPONENT_TYPES)],
            'items.*.specification'  => ['nullable', 'string', 'max:255'],
            'items.*.total_asset'    => ['required', 'integer', 'min:0'],
            'items.*.total_good'     => ['required', 'integer', 'min:0'],
            // (#17) damaged & loss tidak lagi diinput → default 0.
            'items.*.total_damaged'  => ['nullable', 'integer', 'min:0'],
            'items.*.total_loss'     => ['nullable', 'integer', 'min:0'],
            'items.*.source'         => ['nullable', 'string', 'max:255'],
            'items.*.notes'          => ['nullable', 'string'],
            // (#17) serial number opsional (array) untuk kategori ber-S/N.
            'items.*.serials'        => ['nullable', 'array'],
            // Serial Number dari SPV Inventory (referensi asset_serial_numbers.id).
            'items.*.spv_serial_id'  => ['nullable', 'integer', 'exists:asset_serial_numbers,id'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            foreach ($validated['items'] as $item) {
                $category = $item['asset_category'] ?? $validated['asset_category'] ?? 'electronic';
                $good     = (int) $item['total_good'];
                $damaged  = (int) ($item['total_damaged'] ?? 0);
                $loss     = (int) ($item['total_loss'] ?? 0);

                // Jika ada spv_serial_id, ambil serial number-nya untuk referensi.
                $spvSerialNumber = null;
                if (!empty($item['spv_serial_id'])) {
                    $spvSerial = AssetSerialNumber::find($item['spv_serial_id']);
                    if ($spvSerial) {
                        $spvSerialNumber = $spvSerial->serial_number;
                    }
                }

                $asset = Asset::create([
                    'asset_name'     => $item['asset_name'],
                    'asset_category' => $category,
                    'component_type' => $category === 'component-pc' ? ($item['component_type'] ?? null) : null,
                    'specification'  => $category === 'component-pc' ? ($item['specification'] ?? null) : null,
                    'total_good'     => $good,
                    'total_damaged'  => $damaged,
                    'total_loss'     => $loss,
                ]);

                // (#16/#17) Buat unit serial number untuk kategori ber-S/N.
                // Jika ada referensi S/N dari SPV, sisipkan ke depan daftar manual.
                $manualSerials = $item['serials'] ?? [];
                if ($spvSerialNumber) {
                    array_unshift($manualSerials, $spvSerialNumber);
                }
                $this->generateSerials($asset, $good, $manualSerials);

                AssetLog::create([
                    'asset_id' => $asset->id,
                    'user_id'  => auth()->id(),
                    'type'     => 'stock_in',
                    'quantity' => $asset->total_asset,
                    'before_total_asset' => 0, 'after_total_asset' => $asset->total_asset,
                    'before_total_good' => 0, 'after_total_good' => $good,
                    'before_total_damaged' => 0, 'after_total_damaged' => $damaged,
                    'before_total_loss' => 0, 'after_total_loss' => $loss,
                    'source' => $item['source'] ?? null,
                    'notes'  => $item['notes'] ?? 'Initial asset stock.',
                ]);

                ActivityLog::create([
                    'user_id'  => auth()->id(),
                    'activity' => 'Created asset: ' . $asset->asset_name
                        . ($spvSerialNumber ? ' (S/N from SPV: ' . $spvSerialNumber . ')' : ''),
                ]);
            }
        });

        return redirect()->route('asset.index')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        return redirect()->route('asset.index');
    }

    public function edit(Asset $asset)
    {
        return redirect()->route('asset.index');
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_name'     => ['required', 'string', 'max:255'],
            'asset_category' => ['required', Rule::in(['electronic', 'non-electronic', 'component-pc', 'pc'])],
            'component_type' => ['nullable', Rule::in(self::COMPONENT_TYPES)],
            'specification'  => ['nullable', 'string', 'max:255'],
            'total_asset'    => ['required', 'integer', 'min:0'],
            'total_good'     => ['required', 'integer', 'min:0'],
            'total_damaged'  => ['required', 'integer', 'min:0'],
            'total_loss'     => ['required', 'integer', 'min:0'],
            'source'         => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
            'serials'        => ['nullable', 'array'],
        ]);

        if ($validated['total_good'] + $validated['total_damaged'] > $validated['total_asset']) {
            return back()->withInput()->withErrors([
                'total_asset' => 'Total good + damaged tidak boleh lebih besar dari total asset.',
            ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $old = $asset->only(['total_asset', 'total_good', 'total_damaged', 'total_loss']);

            $asset->update([
                'asset_name'     => $validated['asset_name'],
                'asset_category' => $validated['asset_category'],
                'component_type' => $validated['asset_category'] === 'component-pc' ? ($validated['component_type'] ?? null) : null,
                'specification'  => $validated['asset_category'] === 'component-pc' ? ($validated['specification'] ?? null) : null,
                'total_good'     => $validated['total_good'],
                'total_damaged'  => $validated['total_damaged'],
                'total_loss'     => $validated['total_loss'],
            ]);

            // (#16) Rekonsiliasi serial: hormati daftar S/N yang dikirim dari form Edit.
            //  - serial in_use (terpasang di PC) wajib dipertahankan
            //  - serial available yang tidak ada di daftar → dihapus
            //  - serial baru di daftar → dibuat
            //  - bila daftar kosong, samakan jumlah dgn total_good (auto-generate)
            $this->reconcileSerials($asset, $validated['serials'] ?? null, (int) $validated['total_good']);

            if ($old !== $asset->only(['total_asset', 'total_good', 'total_damaged', 'total_loss'])) {
                AssetLog::create([
                    'asset_id' => $asset->id,
                    'user_id'  => auth()->id(),
                    'type'     => 'adjustment',
                    'quantity' => $asset->total_asset - $old['total_asset'],
                    'before_total_asset' => $old['total_asset'], 'after_total_asset' => $asset->total_asset,
                    'before_total_good' => $old['total_good'], 'after_total_good' => $asset->total_good,
                    'before_total_damaged' => $old['total_damaged'], 'after_total_damaged' => $asset->total_damaged,
                    'before_total_loss' => $old['total_loss'], 'after_total_loss' => $asset->total_loss,
                    'source' => $validated['source'] ?? null,
                    'notes'  => $validated['notes'] ?? 'Asset stock updated.',
                ]);
            }

            ActivityLog::create([
                'user_id'  => auth()->id(),
                'activity' => 'Updated asset: ' . $asset->asset_name,
            ]);
        });

        return redirect()->route('asset.index')->with('success', 'Asset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        ActivityLog::create([
            'user_id'  => auth()->id(),
            'activity' => 'Deleted asset: ' . $asset->asset_name,
        ]);

        $asset->delete();

        return redirect()->route('asset.index')->with('success', 'Asset berhasil dihapus.');
    }

    public function export(string $format)
    {
        $export = new AssetExport();

        return match ($format) {
            'pdf'   => $export->downloadPdf(),
            'excel' => $export->downloadExcel(),
            'csv'   => $export->downloadCsv(),
            default => abort(404),
        };
    }

    /* ─────────────────────────── serial helpers ─────────────────────────── */

    /** Buat $count unit serial number. Pakai S/N manual bila ada, sisanya auto. */
    private function generateSerials(Asset $asset, int $count, array $manual = []): void
    {
        if ($count <= 0) {
            return;
        }

        // Normalize $manual to always be array of: ['serial_number', 'prefix', 'qr_code', 'condition']
        $normalized = [];
        foreach ($manual as $m) {
            if (is_array($m)) {
                $sn = isset($m['serial_number']) ? trim($m['serial_number']) : '';
                if ($sn === '') continue;
                $normalized[] = [
                    'serial_number' => $sn,
                    'prefix' => isset($m['prefix']) ? trim($m['prefix']) : null,
                    'qr_code' => isset($m['qr_code']) ? trim($m['qr_code']) : null,
                    'condition' => isset($m['condition']) ? trim($m['condition']) : 'good',
                ];
            } elseif (is_string($m)) {
                $sn = trim($m);
                if ($sn === '') continue;
                
                // Parse prefix/qr from string if needed (fallback)
                $prefix = null;
                $qrCode = $sn;
                $lastDash = strrpos($sn, '-');
                if ($lastDash !== false) {
                    $prefix = substr($sn, 0, $lastDash);
                    $qrCode = substr($sn, $lastDash + 1);
                }
                $normalized[] = [
                    'serial_number' => $sn,
                    'prefix' => $prefix,
                    'qr_code' => $qrCode,
                    'condition' => 'good',
                ];
            }
        }

        // Create the manual ones first
        foreach ($normalized as $row) {
            AssetSerialNumber::firstOrCreate(
                ['asset_id' => $asset->id, 'serial_number' => $row['serial_number']],
                [
                    'prefix' => $row['prefix'],
                    'qr_code' => $row['qr_code'],
                    'condition' => $row['condition'],
                    'status' => 'available'
                ]
            );
        }

        // Fill the rest automatically to match count
        $currentCount = $asset->serialNumbers()->count();
        if ($currentCount < $count) {
            $diff = $count - $currentCount;
            $autoCount = 0;
            $index = 1;
            while ($autoCount < $diff) {
                if ($asset->asset_category === 'component-pc' && !empty($asset->specification)) {
                    $cleanSpec = substr(trim($asset->specification), 0, 80);
                    $serial = $cleanSpec . '-' . str_pad($index, 3, '0', STR_PAD_LEFT);
                } else {
                    $serial = $asset->sku . '-' . str_pad($index, 3, '0', STR_PAD_LEFT);
                }
                $exists = AssetSerialNumber::where('asset_id', $asset->id)
                    ->where('serial_number', $serial)
                    ->exists();
                if (!$exists) {
                    AssetSerialNumber::create([
                        'asset_id' => $asset->id,
                        'serial_number' => $serial,
                        'prefix' => null,
                        'qr_code' => null,
                        'condition' => 'good',
                        'status' => 'available',
                    ]);
                    $autoCount++;
                }
                $index++;
                if ($index > 9999) {
                    break;
                }
            }
        }
    }

    /**
     * Rekonsiliasi unit serial saat edit asset.
     *
     * @param  array|null $submitted  daftar S/N dari form (null = tidak dikirim → mode jumlah)
     */
    private function reconcileSerials(Asset $asset, ?array $submitted, int $targetGood): void
    {
        // (semua kategori kini memiliki kode inventaris)

        // Mode jumlah: form tidak mengirim daftar serial.
        if ($submitted === null) {
            $existing = $asset->serialNumbers()->count();
            if ($targetGood > $existing) {
                $this->generateSerials($asset, $targetGood, []);
            } elseif ($targetGood < $existing) {
                $asset->serialNumbers()
                    ->where('status', 'available')
                    ->latest('id')
                    ->limit($existing - $targetGood)
                    ->delete();
            }
            return;
        }

        // Normalize submitted list
        $normalized = [];
        foreach ($submitted as $row) {
            if (is_array($row)) {
                $sn = isset($row['serial_number']) ? trim($row['serial_number']) : '';
                if ($sn === '') continue;
                $normalized[] = [
                    'id' => $row['id'] ?? null,
                    'serial_number' => $sn,
                    'prefix' => isset($row['prefix']) ? trim($row['prefix']) : null,
                    'qr_code' => isset($row['qr_code']) ? trim($row['qr_code']) : null,
                    'condition' => isset($row['condition']) ? trim($row['condition']) : 'good',
                ];
            } elseif (is_string($row)) {
                $sn = trim($row);
                if ($sn === '') continue;
                
                $prefix = null;
                $qrCode = $sn;
                $lastDash = strrpos($sn, '-');
                if ($lastDash !== false) {
                    $prefix = substr($sn, 0, $lastDash);
                    $qrCode = substr($sn, $lastDash + 1);
                }
                $normalized[] = [
                    'id' => null,
                    'serial_number' => $sn,
                    'prefix' => $prefix,
                    'qr_code' => $qrCode,
                    'condition' => 'good',
                ];
            }
        }

        // Keep track of IDs we keep or create
        $keepIds = [];
        foreach ($normalized as $row) {
            if (!empty($row['id'])) {
                // Update existing serial
                $serial = $asset->serialNumbers()->find($row['id']);
                if ($serial) {
                    $serial->update([
                        'serial_number' => $row['serial_number'],
                        'prefix' => $row['prefix'],
                        'qr_code' => $row['qr_code'],
                        'condition' => $row['condition'],
                    ]);
                    $keepIds[] = $serial->id;
                }
            } else {
                // Check if a serial number with the same serial_number already exists for this asset
                $serial = $asset->serialNumbers()->where('serial_number', $row['serial_number'])->first();
                if ($serial) {
                    $serial->update([
                        'prefix' => $row['prefix'],
                        'qr_code' => $row['qr_code'],
                        'condition' => $row['condition'],
                    ]);
                } else {
                    // Create new serial
                    $serial = AssetSerialNumber::create([
                        'asset_id' => $asset->id,
                        'serial_number' => $row['serial_number'],
                        'prefix' => $row['prefix'],
                        'qr_code' => $row['qr_code'],
                        'condition' => $row['condition'],
                        'status' => 'available',
                    ]);
                }
                $keepIds[] = $serial->id;
            }
        }

        // Serial yang terpasang di PC (status === in_use) tidak boleh didelete.
        $inUseIds = $asset->serialNumbers()->where('status', 'in_use')->pluck('id')->all();
        $finalKeepIds = array_values(array_unique(array_merge($inUseIds, $keepIds)));

        // Hapus serial available yang tidak ada di list finalKeepIds
        $asset->serialNumbers()
            ->where('status', 'available')
            ->whereNotIn('id', $finalKeepIds)
            ->delete();

        // Jika jumlah serial masih kurang dari targetGood, auto-generate sisanya
        $currentCount = $asset->serialNumbers()->count();
        if ($currentCount < $targetGood) {
            $diff = $targetGood - $currentCount;
            $this->generateSerials($asset, $currentCount + $diff, []);
        }
    }
}

