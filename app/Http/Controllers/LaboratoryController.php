<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryController extends Controller
{
    public function index()
{
    $user = Auth::user();

    $laboratories = Laboratory::withCount([
        'pcs as total_pc_active' => fn($q) => $q->where('status_pc', 'active'),
        'pcs as total_pc_inactive' => fn($q) => $q->where('status_pc', 'inactive'),
    ])
    ->with(['users', 'assets'])
    ->orderBy('lab_name')
    ->paginate(15);

    $myLabIds = [];
    $allAssets = Asset::orderBy('asset_name')->get();

    return view(
        'pages.laboratory.index',
        compact('laboratories', 'myLabIds', 'user', 'allAssets')
    );
}

    public function show(Laboratory $laboratory)
    {
        $laboratory->load([
            'pcs'    => fn($q) => $q->orderBy('id'),
            'users',
            'assets',
        ]);

        $totalActive   = $laboratory->pcs->where('status_pc', 'active')->count();
        $totalInactive = $laboratory->pcs->where('status_pc', 'inactive')->count();
        $allAssets     = Asset::orderBy('asset_name')->get();

        // Staff per role
        $pic        = $laboratory->users->firstWhere('role', 'pic');
        $admins     = $laboratory->users->where('role', 'admin')->values();
        $assistants = $laboratory->users->where('role', 'assistant')->values();

        return view('pages.laboratory.show', compact(
            'laboratory',
            'totalActive',
            'totalInactive',
            'allAssets',
            'pic',
            'admins',
            'assistants'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_name'                  => 'required|string|max:255|unique:laboratories,lab_name',
            'capacity'                  => 'required|integer|min:1',
            'pcs'                       => 'required|array|min:1',
            'pcs.*.type_pc'             => 'required|in:dosen,mahasiswa',
            'pcs.*.processor'           => 'nullable|string|max:255',
            'pcs.*.ram'                 => 'nullable|string|max:255',
            'pcs.*.ssd'                 => 'nullable|string|max:255',
            'pcs.*.motherboard'         => 'nullable|string|max:255',
            'pcs.*.vga'                 => 'nullable|string|max:255',
            'pcs.*.cpu_fan'             => 'nullable|string|max:255',
            'pcs.*.powersupply'         => 'nullable|string|max:255',
            'lab_assets'                => 'nullable|array',
            'lab_assets.*.asset_id'     => 'nullable|exists:assets,id',
            'lab_assets.*.quantity'     => 'nullable|integer|min:0',
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

        if (!empty($validated['lab_assets'])) {
            $sync = [];
            foreach ($validated['lab_assets'] as $a) {
                if (!empty($a['asset_id'])) {
                    $sync[$a['asset_id']] = ['total_asset_lab' => $a['quantity'] ?? 0];
                }
            }
            if ($sync) $lab->assets()->sync($sync);
        }

        return redirect()->route('laboratory.index')
            ->with('success', "Lab {$lab->lab_name} berhasil ditambahkan.");
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'lab_name'                  => "required|string|max:255|unique:laboratories,lab_name,{$laboratory->id}",
            'capacity'                  => 'required|integer|min:1',
            'pcs'                       => 'nullable|array',
            'pcs.*.type_pc'             => 'required|in:dosen,mahasiswa',
            'pcs.*.status_pc'           => 'required|in:active,inactive',
            'pcs.*.processor'           => 'nullable|string|max:255',
            'pcs.*.ram'                 => 'nullable|string|max:255',
            'pcs.*.ssd'                 => 'nullable|string|max:255',
            'pcs.*.motherboard'         => 'nullable|string|max:255',
            'pcs.*.vga'                 => 'nullable|string|max:255',
            'pcs.*.cpu_fan'             => 'nullable|string|max:255',
            'pcs.*.powersupply'         => 'nullable|string|max:255',
            'lab_assets'                => 'nullable|array',
            'lab_assets.*.asset_id'     => 'nullable|exists:assets,id',
            'lab_assets.*.quantity'     => 'nullable|integer|min:0',
        ]);

        $laboratory->update([
            'lab_name' => $validated['lab_name'],
            'capacity' => $validated['capacity'],
        ]);

        if (!empty($validated['pcs'])) {
            $keepIds = [];
            foreach ($validated['pcs'] as $pcData) {
                $pcId = $pcData['id'] ?? null;
                if ($pcId) {
                    $laboratory->pcs()->where('id', $pcId)
                        ->update(collect($pcData)->except('id')->toArray());
                    $keepIds[] = $pcId;
                } else {
                    $new = $laboratory->pcs()->create(array_merge(
                        collect($pcData)->except('id')->toArray(),
                        ['pc_entry' => now()->toDateString()]
                    ));
                    $keepIds[] = $new->id;
                }
            }
            $laboratory->pcs()->whereNotIn('id', $keepIds)->delete();
        }

        if (isset($validated['lab_assets'])) {
            $sync = [];
            foreach ($validated['lab_assets'] as $a) {
                if (!empty($a['asset_id'])) {
                    $sync[$a['asset_id']] = ['total_asset_lab' => $a['quantity'] ?? 0];
                }
            }
            $laboratory->assets()->sync($sync);
        }

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Lab berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();
        return redirect()->route('laboratory.index')
            ->with('success', "Lab {$laboratory->lab_name} berhasil dihapus.");
    }
}
