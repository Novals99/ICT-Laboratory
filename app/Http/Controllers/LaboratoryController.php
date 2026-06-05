<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $laboratories = Laboratory::withCount([
            'pcs as total_pc_active'   => fn($q) => $q->where('status_pc', 'active'),
            'pcs as total_pc_inactive' => fn($q) => $q->where('status_pc', 'inactive'),
        ])
        ->with('users')
        ->orderBy('lab_name')
        ->paginate(15);

        // lab_id milik user yang login
        $myLabIds = $user->labs()->pluck('laboratories.id')->toArray();

        return view('pages.laboratory.index', compact('laboratories', 'myLabIds', 'user'));
    }

    public function show(Laboratory $laboratory)
    {
        $laboratory->load([
            'pcs' => fn($q) => $q->orderBy('id'),
            'users'
            // 'assets',
        ]);

        $totalActive   = $laboratory->pcs->where('status_pc', 'active')->count();
        $totalInactive = $laboratory->pcs->where('status_pc', 'inactive')->count();

        return view('pages.laboratory.show', compact(
            'laboratory',
            'totalActive',
            'totalInactive',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_name' => 'required|string|max:255|unique:laboratories,lab_name',
            'capacity' => 'required|integer|min:1',
            'pcs'      => 'required|array|min:1',
            'pcs.*.type_pc'     => 'required|in:dosen,mahasiswa',
            'pcs.*.processor'   => 'nullable|string|max:255',
            'pcs.*.ram'         => 'nullable|string|max:255',
            'pcs.*.ssd'         => 'nullable|string|max:255',
            'pcs.*.motherboard' => 'nullable|string|max:255',
            'pcs.*.vga'         => 'nullable|string|max:255',
            'pcs.*.cpu_fan'     => 'nullable|string|max:255',
            'pcs.*.powersupply' => 'nullable|string|max:255',
        ]);

        $lab = Laboratory::create([
            'lab_name' => $validated['lab_name'],
            'capacity' => $validated['capacity'],
        ]);

        foreach ($validated['pcs'] as $pcData) {
            $lab->pcs()->create(array_merge($pcData, [
                'status_pc' => 'active',
                'pc_entry'  => now()->toDateString(),
            ]));
        }

        return redirect()->route('laboratory.index')
            ->with('success', "Lab {$lab->lab_name} berhasil ditambahkan.");
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'lab_name' => "required|string|max:255|unique:laboratories,lab_name,{$laboratory->id}",
            'capacity' => 'required|integer|min:1',
        ]);

        $laboratory->update($validated);

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Data lab berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('laboratory.index')
            ->with('success', "Lab {$laboratory->lab_name} berhasil dihapus.");
    }
}
