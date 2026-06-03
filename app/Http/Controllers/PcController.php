<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Pc;
use Illuminate\Http\Request;

class PcController extends Controller
{
    public function store(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'type_pc'     => 'required|in:dosen,mahasiswa',
            'processor'   => 'nullable|string|max:255',
            'ram'         => 'nullable|string|max:255',
            'ssd'         => 'nullable|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'vga'         => 'nullable|string|max:255',
            'cpu_fan'     => 'nullable|string|max:255',
            'powersupply' => 'nullable|string|max:255',
        ]);

        $laboratory->pcs()->create(array_merge($validated, [
            'status_pc' => 'active',
            'pc_entry'  => now()->toDateString(),
        ]));

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil ditambahkan.');
    }

    public function update(Request $request, Laboratory $laboratory, Pc $pc)
    {
        $validated = $request->validate([
            'type_pc'     => 'required|in:dosen,mahasiswa',
            'status_pc'   => 'required|in:active,inactive',
            'processor'   => 'nullable|string|max:255',
            'ram'         => 'nullable|string|max:255',
            'ssd'         => 'nullable|string|max:255',
            'motherboard' => 'nullable|string|max:255',
            'vga'         => 'nullable|string|max:255',
            'cpu_fan'     => 'nullable|string|max:255',
            'powersupply' => 'nullable|string|max:255',
            'keterangan'  => 'nullable|string',
        ]);

        $pc->update($validated);

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Data PC berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory, Pc $pc)
    {
        $pc->delete();

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'PC berhasil dihapus.');
    }

    public function updateStatus(Request $request, Pc $pc)
    {
        // 1. Validasi data yang masuk untuk memastikan hanya status yang valid yang diterima
        $request->validate([
            'status_pc' => 'required|in:active,inactive',
        ]);

        // 2. Update field status_pc di database
        $pc->update([
            'status_pc' => $request->status_pc,
        ]);

        // 3. Kembalikan ke halaman sebelumnya dengan alert sukses
        return redirect()->back()->with('success', 'Status PC berhasil diubah menjadi ' . $request->status_pc);
    }
}
