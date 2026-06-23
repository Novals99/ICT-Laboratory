<?php

namespace App\Http\Controllers;

use App\Models\AssetLab;
use App\Models\AssetSerialNumber;
use App\Models\Laboratory;
use App\Models\Pc;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PcController extends Controller
{
    /** Slot komponen + label (RAM 2 nullable). */
    private const SLOTS = [
        'processor', 'ram', 'ram2', 'ssd',
        'motherboard', 'vga', 'cpu_fan', 'powersupply',
    ];

    public function store(Request $request, Laboratory $laboratory)
    {
        $validated = $this->validatePc($request, withStatus: false);

        DB::transaction(function () use ($validated, $laboratory) {
            $pc = $laboratory->pcs()->create([
                'type_pc'   => $validated['type_pc'],
                'status_pc' => 'active',
                'pc_entry'  => now()->toDateString(),
            ]);

            $this->syncSerials($pc, $laboratory, $validated);

            $laboratory->update(['capacity' => $laboratory->pcs()->count()]);

            ActivityLog::create([
                'user_id'  => auth()->id(),
                'activity' => 'Created PC in laboratory: ' . $laboratory->lab_name,
            ]);
        });

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil ditambahkan.')
            ->with('section', 'pc');
    }

    public function update(Request $request, Laboratory $laboratory, Pc $pc)
    {
        $validated = $this->validatePc($request, withStatus: true);

        DB::transaction(function () use ($validated, $laboratory, $pc) {
            // Lepaskan semua serial lama PC ini dulu (balik ke stok lab).
            $this->releaseAllSerials($pc, $laboratory);

            $pc->update([
                'type_pc'   => $validated['type_pc'],
                'status_pc' => $validated['status_pc'],
            ]);

            $this->syncSerials($pc, $laboratory, $validated);

            ActivityLog::create([
                'user_id'  => auth()->id(),
                'activity' => 'Updated PC #' . $pc->id . ' in laboratory: ' . $laboratory->lab_name,
            ]);
        });

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Data PC berhasil diperbarui.')
            ->with('section', 'pc');
    }

    public function destroy(Laboratory $laboratory, Pc $pc)
    {
        DB::transaction(function () use ($laboratory, $pc) {
            $this->releaseAllSerials($pc, $laboratory);
            $pc->delete();
            $laboratory->update(['capacity' => $laboratory->pcs()->count()]);

            ActivityLog::create([
                'user_id'  => auth()->id(),
                'activity' => 'Deleted PC #' . $pc->id . ' from laboratory: ' . $laboratory->lab_name,
            ]);
        });

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil dihapus.');
    }

    public function updateStatus(Request $request, Pc $pc)
    {
        $request->validate(['status_pc' => 'required|in:active,inactive']);
        $pc->update(['status_pc' => $request->status_pc]);

        ActivityLog::create([
            'user_id'  => auth()->id(),
            'activity' => 'Changed PC #' . $pc->id . ' status to ' . ucfirst($request->status_pc),
        ]);

        return redirect()->back()
            ->with('success', 'Status PC berhasil diubah menjadi ' . $request->status_pc)
            ->with('section', 'pc');
    }

    /* ───────────────────────────── helpers ───────────────────────────── */

    private function validatePc(Request $request, bool $withStatus): array
    {
        $rules = [
            'type_pc' => 'required|in:dosen,mahasiswa',
        ];
        if ($withStatus) {
            $rules['status_pc'] = 'required|in:active,inactive';
        }
        foreach (self::SLOTS as $slot) {
            // tiap slot pilih SATU serial number (boleh kosong).
            $rules["{$slot}_serial_id"] = 'nullable|exists:asset_serial_numbers,id';
        }

        return $request->validate($rules);
    }

    /**
     * Pasang serial yang dipilih ke tiap slot:
     *  - set kolom *_serial_id + kolom string (nama asset, supaya tampilan lama jalan)
     *  - tandai serial in_use + pc_id + slot
     *  - kurangi stok good di asset_lab
     */
    private function syncSerials(Pc $pc, Laboratory $laboratory, array $validated): void
    {
        $usedIds = [];

        foreach (self::SLOTS as $slot) {
            $serialId = $validated["{$slot}_serial_id"] ?? null;

            if (! $serialId) {
                $pc->{$slot} = null;
                $pc->{$slot . '_serial_id'} = null;
                continue;
            }

            // 1 serial tidak boleh dipakai 2 slot dalam 1 submit.
            if (in_array($serialId, $usedIds, true)) {
                abort(422, 'Satu serial number tidak boleh dipakai di dua slot.');
            }
            $usedIds[] = $serialId;

            /** @var AssetSerialNumber $serial */
            $serial = AssetSerialNumber::with('asset')->findOrFail($serialId);

            // Pastikan serial available & berada di lab ini.
            if ($serial->status === 'in_use' && $serial->pc_id !== $pc->id) {
                abort(422, "Serial {$serial->serial_number} sudah terpasang di PC lain.");
            }

            $pc->{$slot} = $serial->asset->asset_name ?? null;
            $pc->{$slot . '_serial_id'} = $serial->id;

            $serial->update([
                'status' => 'in_use',
                'pc_id'  => $pc->id,
                'slot'   => $slot,
                'lab_id' => $laboratory->id,
            ]);
            // Tidak mengubah asset_lab: unit tetap dimiliki lab, hanya berubah
            // jadi "terpasang". Stok lab = total dimiliki (terpasang + bebas).
        }

        $pc->save();
    }

    /** Lepaskan semua serial milik PC → balik available + tambah stok lab. */
    private function releaseAllSerials(Pc $pc, Laboratory $laboratory): void
    {
        $serials = AssetSerialNumber::where('pc_id', $pc->id)->get();

        foreach ($serials as $serial) {
            // Lepas dari PC tapi TETAP milik lab (lab_id dipertahankan) →
            // jadi unit "bebas" di lab, siap dipasang ulang / diretur / ditransfer.
            $serial->update([
                'status' => 'available',
                'pc_id'  => null,
                'slot'   => null,
            ]);
        }

        // Kosongkan kolom slot di PC.
        foreach (self::SLOTS as $slot) {
            $pc->{$slot} = null;
            $pc->{$slot . '_serial_id'} = null;
        }
        $pc->save();
    }

    private function decrementLabStock(Laboratory $laboratory, int $assetId): void
    {
        $al = AssetLab::where('lab_id', $laboratory->id)->where('asset_id', $assetId)->first();
        if ($al && $al->total_good_lab > 0) {
            $al->decrement('total_good_lab');
            $al->update(['total_asset_lab' => $al->total_good_lab + $al->total_damaged_lab + $al->total_loss_lab]);
        }
    }

    private function incrementLabStock(Laboratory $laboratory, int $assetId): void
    {
        $al = AssetLab::where('lab_id', $laboratory->id)->where('asset_id', $assetId)->first();
        if ($al) {
            $al->increment('total_good_lab');
            $al->update(['total_asset_lab' => $al->total_good_lab + $al->total_damaged_lab + $al->total_loss_lab]);
        }
    }
}
