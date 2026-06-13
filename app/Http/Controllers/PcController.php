<?php

namespace App\Http\Controllers;

use App\Models\AssetLab;
use App\Models\Laboratory;
use App\Models\Pc;
use Illuminate\Http\Request;

class PcController extends Controller
{
    public function store(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'type_pc' => 'required|in:dosen,mahasiswa',
            'processor' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'ssd' => 'nullable|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'vga' => 'nullable|string|max:255',
            'cpu_fan' => 'nullable|string|max:255',
            'powersupply' => 'nullable|string|max:255',
        ]);

        $laboratory->pcs()->create(array_merge($validated, [
            'status_pc' => 'active',
            'pc_entry' => now()->toDateString(),
        ]));

        // 🔴 Kurangi stok component di Lab
        $components = array_filter([
            $validated['processor']   ?? null,
            $validated['ram']         ?? null,
            $validated['ssd']         ?? null,
            $validated['motherboard'] ?? null,
            $validated['vga']         ?? null,
            $validated['cpu_fan']     ?? null,
            $validated['powersupply'] ?? null,
        ]);
        $this->deductLabStock($laboratory, $components);

        $laboratory->update(['capacity' => $laboratory->pcs()->count()]);

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil ditambahkan.')
            ->with('section', 'pc');
    }

    public function update(Request $request, Laboratory $laboratory, Pc $pc)
    {
        $validated = $request->validate([
            'type_pc' => 'required|in:dosen,mahasiswa',
            'status_pc' => 'required|in:active,inactive',
            'processor' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'ssd' => 'nullable|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'vga' => 'nullable|string|max:255',
            'cpu_fan' => 'nullable|string|max:255',
            'powersupply' => 'nullable|string|max:255',
        ]);

        $old = array_filter([
            $pc->processor, $pc->ram, $pc->ssd, $pc->motherboard,
            $pc->vga, $pc->cpu_fan, $pc->powersupply
        ]);
        $new = array_filter([
            $validated['processor']   ?? null,
            $validated['ram']         ?? null,
            $validated['ssd']         ?? null,
            $validated['motherboard'] ?? null,
            $validated['vga']         ?? null,
            $validated['cpu_fan']     ?? null,
            $validated['powersupply'] ?? null,
        ]);

        // Kembalikan stok component yang dihapus / diganti
        $this->returnLabStock($laboratory, array_diff($old, $new));
        // Kurangi stok component yang baru ditambahkan
        $this->deductLabStock($laboratory, array_diff($new, $old));

        $pc->update($validated);

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Data PC berhasil diperbarui.')
            ->with('section', 'pc');
    }

    public function destroy(Laboratory $laboratory, Pc $pc)
    {
        // Kembalikan semua component ke stok Lab sebelum hapus PC
        $components = array_filter([
            $pc->processor, $pc->ram, $pc->ssd, $pc->motherboard,
            $pc->vga, $pc->cpu_fan, $pc->powersupply
        ]);
        $this->returnLabStock($laboratory, $components);

        $pc->delete();
        $laboratory->update(['capacity' => $laboratory->pcs()->count()]);

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil dihapus.');
    }

    public function updateStatus(Request $request, Pc $pc)
    {
<<<<<<< HEAD
        // 1. Validasi data yang masuk untuk memastikan hanya status yang valid yang diterima
        $request->validate([
            'status_pc' => 'required|in:active,inactive',
        ]);

        // 2. Update field status_pc di database
        $pc->update([
            'status_pc' => $request->status_pc,
        ]);

        // 3. Kembalikan ke halaman sebelumnya dengan alert sukses
        return redirect()->back()->with('success', 'Status PC berhasil diubah menjadi '.$request->status_pc);
=======
        $request->validate(['status_pc' => 'required|in:active,inactive']);
        $pc->update(['status_pc' => $request->status_pc]);
        return redirect()->back()
            ->with('success', 'Status PC berhasil diubah menjadi ' . $request->status_pc)
            ->with('section', 'pc');
>>>>>>> 206761705a3d0043394f15b54a392ae939e8253d
    }

    /* ─────────────────────────────────────────
       PRIVATE: Kurangi / Kembalikan stok Lab
       ───────────────────────────────────────── */
    private function deductLabStock(Laboratory $laboratory, array $names)
    {
        foreach ($names as $name) {
            $al = AssetLab::where('lab_id', $laboratory->id)
                ->whereHas('asset', function ($q) use ($name) {
                    $q->where('asset_category', 'component-pc')
                      ->whereRaw('LOWER(asset_name) = ?', [strtolower($name)]);
                })
                ->where('total_good_lab', '>', 0)
                ->first();

            if ($al) {
                $al->decrement('total_good_lab');
                $al->update([
                    'total_asset_lab' => $al->total_good_lab + $al->total_damaged_lab + $al->total_loss_lab
                ]);
            }
        }
    }

    private function returnLabStock(Laboratory $laboratory, array $names)
    {
        foreach ($names as $name) {
            $al = AssetLab::where('lab_id', $laboratory->id)
                ->whereHas('asset', function ($q) use ($name) {
                    $q->where('asset_category', 'component-pc')
                      ->whereRaw('LOWER(asset_name) = ?', [strtolower($name)]);
                })
                ->first();

            if ($al) {
                $al->increment('total_good_lab');
                $al->update([
                    'total_asset_lab' => $al->total_good_lab + $al->total_damaged_lab + $al->total_loss_lab
                ]);
            }
        }
    }
}