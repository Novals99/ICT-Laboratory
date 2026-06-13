<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use App\Models\AssetLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Exports\LabExport;

class LaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $user = auth()->user();

        $search = $request->input('search');

        $laboratories = Laboratory::query()
            ->withCount([
                'pcs as total_pc_active' => fn($q) => $q->where('status_pc', 'active'),
                'pcs as total_pc_inactive' => fn($q) => $q->where('status_pc', 'inactive'),
            ])
            ->with(['users', 'assets'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('lab_name', 'like', "%{$search}%")
                        ->orWhere('capacity', 'like', "%{$search}%")
                        ->orWhereHas('users', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('lab_name')
            ->paginate(15)
            ->withQueryString();

        $myLabIds = [];
        $allAssets = Asset::orderBy('asset_name')->get();

        return view(
            'pages.laboratory.index',
            compact('laboratories', 'myLabIds', 'user', 'allAssets')
        );
    }

    public function show(Laboratory $laboratory)
    {
        $user = auth()->user();

        $laboratory->load([
            'pcs'    => fn($q) => $q->orderBy('id'),
            'users',
            'assets',
        ]);

        $totalActive   = $laboratory->pcs->where('status_pc', 'active')->count();
        $totalInactive = $laboratory->pcs->where('status_pc', 'inactive')->count();
        $allAssets     = Asset::orderBy('asset_name')->get();

        $pic        = $laboratory->users->firstWhere('role', 'pic');
        $admins     = $laboratory->users->where('role', 'admin')->values();
        $assistants = $laboratory->users->where('role', 'assistant')->values();

        $myLabIds  = $user->labs()->pluck('laboratories.id')->toArray();
        $canEdit   = $user->role === 'spv inventory' || in_array($laboratory->id, $myLabIds);

        $pcComponents = AssetLab::where('lab_id', $laboratory->id)
            ->whereHas('asset', fn($q) => $q->where('asset_category', 'component-pc'))
            ->with('asset')
            ->get()
            ->map(fn($al) => [
                'asset_lab_id' => $al->id,
                'name'         => $al->asset->asset_name,
                'stock'        => $al->total_good_lab,
            ])
            ->values();

        return view('pages.laboratory.show', compact(
            'laboratory',
            'totalActive',
            'totalInactive',
            'allAssets',
            'pic',
            'admins',
            'assistants',
            'canEdit',
            'myLabIds',
            'pcComponents'
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

        // ── SYNC ASSET KE LAB + KURANGI STOK SPV ──
        if (!empty($validated['lab_assets'])) {
            $sync = [];
            foreach ($validated['lab_assets'] as $a) {
                if (!empty($a['asset_id'])) {
                    $asset = Asset::find($a['asset_id']);
                    $qty   = $a['quantity'] ?? 0;

                    if ($asset && $qty > 0) {
                        if ($asset->total_good < $qty) {
                            return back()
                                ->withInput()
                                ->withErrors(['lab_assets' => "Stok good untuk {$asset->asset_name} tidak mencukupi (tersedia: {$asset->total_good})."]);
                        }

                        $asset->decrement('total_good', $qty);
                        $asset->refresh();
                        $asset->update(['total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss]);

                        $sync[$a['asset_id']] = [
                            'total_asset_lab'     => $qty,
                            'total_good_lab'      => $qty,
                            'total_damaged_lab'   => 0,
                            'total_loss_lab'      => 0,
                        ];
                    }
                }
            }
            if ($sync) {
                $lab->assets()->sync($sync);
            }
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

        // ── SYNC ASSET + SESUAIKAN STOK SPV ──
        if (isset($validated['lab_assets'])) {
            $existingInLab = $laboratory->assets()->get()->keyBy('id');
            $sync = [];

            foreach ($validated['lab_assets'] as $a) {
                if (!empty($a['asset_id'])) {
                    $asset   = Asset::find($a['asset_id']);
                    $qty     = $a['quantity'] ?? 0;
                    $oldQty  = $existingInLab->has($a['asset_id'])
                        ? $existingInLab[$a['asset_id']]->pivot->total_good_lab
                        : 0;

                    if ($asset) {
                        if ($qty > $oldQty) {
                            $diff = $qty - $oldQty;
                            if ($asset->total_good < $diff) {
                                return back()
                                    ->withInput()
                                    ->withErrors(['lab_assets' => "Stok good untuk {$asset->asset_name} tidak mencukupi (tersedia: {$asset->total_good})."]);
                            }
                            $asset->decrement('total_good', $diff);
                            $asset->refresh();
                            $asset->update(['total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss]);
                        } elseif ($qty < $oldQty) {
                            $diff = $oldQty - $qty;
                            $asset->increment('total_good', $diff);
                            $asset->refresh();
                            $asset->update(['total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss]);
                        }

                        if ($qty > 0) {
                            $sync[$a['asset_id']] = [
                                'total_asset_lab'     => $qty
                                    + ($existingInLab->has($a['asset_id']) ? $existingInLab[$a['asset_id']]->pivot->total_damaged_lab : 0)
                                    + ($existingInLab->has($a['asset_id']) ? $existingInLab[$a['asset_id']]->pivot->total_loss_lab : 0),
                                'total_good_lab'      => $qty,
                                'total_damaged_lab'   => $existingInLab->has($a['asset_id'])
                                    ? $existingInLab[$a['asset_id']]->pivot->total_damaged_lab
                                    : 0,
                                'total_loss_lab'      => $existingInLab->has($a['asset_id'])
                                    ? $existingInLab[$a['asset_id']]->pivot->total_loss_lab
                                    : 0,
                            ];
                        }
                    }
                }
            }

            // Asset yang dihapus dari form → balikin semua kondisi ke SPV
            $newAssetIds = collect($validated['lab_assets'])->pluck('asset_id')->filter()->toArray();
            foreach ($existingInLab as $assetId => $assetLab) {
                if (!in_array($assetId, $newAssetIds)) {
                    $asset = Asset::find($assetId);
                    if ($asset) {
                        if ($assetLab->pivot->total_good_lab > 0) {
                            $asset->increment('total_good', $assetLab->pivot->total_good_lab);
                        }
                        if ($assetLab->pivot->total_damaged_lab > 0) {
                            $asset->increment('total_damaged', $assetLab->pivot->total_damaged_lab);
                        }
                        if ($assetLab->pivot->total_loss_lab > 0) {
                            $asset->increment('total_loss', $assetLab->pivot->total_loss_lab);
                        }
                        $asset->refresh();
                        $asset->update(['total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss]);
                    }
                }
            }

            $laboratory->assets()->sync($sync);
        }

        return redirect()->route('laboratory.show', $laboratory)
            ->with('success', 'Lab berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        // 1. Kembalikan component PC ke stok asset_lab
        foreach ($laboratory->pcs as $pc) {
            $components = array_filter([
                $pc->processor,
                $pc->ram,
                $pc->ssd,
                $pc->motherboard,
                $pc->vga,
                $pc->cpu_fan,
                $pc->powersupply
            ]);
            foreach ($components as $name) {
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

        // 2. Kembalikan asset lab ke SPV sesuai kondisi (good/damaged/loss)
        foreach ($laboratory->assets as $asset) {
            $pivot = $asset->pivot;
            if ($pivot->total_good_lab > 0) {
                $asset->increment('total_good', $pivot->total_good_lab);
            }
            if ($pivot->total_damaged_lab > 0) {
                $asset->increment('total_damaged', $pivot->total_damaged_lab);
            }
            if ($pivot->total_loss_lab > 0) {
                $asset->increment('total_loss', $pivot->total_loss_lab);
            }
            $asset->refresh();
            $asset->update([
                'total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss
            ]);
        }

        $laboratory->delete();

        return redirect()->route('laboratory.index')
            ->with('success', "Lab {$laboratory->lab_name} berhasil dihapus.");
    }

    public function export(string $format)
    {
        $export = new LabExport();

        return match ($format) {
            'pdf'   => $export->downloadPdf(),
            'excel' => $export->downloadExcel(),
            'csv'   => $export->downloadCsv(),
            default => abort(404),
        };
    }
}
