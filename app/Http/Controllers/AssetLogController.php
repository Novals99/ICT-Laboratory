<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use Illuminate\Http\Request;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\Laboratory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Exports\AssetlogExport;

class AssetLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = AssetLog::query()
        ->with(['asset', 'user', 'fromLab', 'toLab'])
        ->when(request('search'), function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($assetQuery) use ($search) {
                    $assetQuery->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_category', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                })
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('source', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        })
        ->when(request('type'), function ($query, $types) {
            $query->whereIn('type', (array) $types);
        })
        ->when(request('lab_id'), function ($query, $labIds) {
            $query->where(function ($q) use ($labIds) {
                $q->whereIn('from_lab_id', (array) $labIds)
                    ->orWhereIn('to_lab_id', (array) $labIds);
            });
        })
        ->when(request('date_from'), function ($query, $dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        })
        ->when(request('date_to'), function ($query, $dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    $laboratories = Laboratory::query()
        ->orderBy('lab_name')
        ->get();

    return view('pages.assetlog.index', compact('logs', 'laboratories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AssetLog $assetLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetLog $assetLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetLog $assetLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetLog $assetLog)
    {
        //
    }


    public function storeStockIn(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            $asset->update([
                'total_asset' => $asset->total_asset + $validated['quantity'],
                'total_good' => $asset->total_good + $validated['quantity'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'stock_in',
                'quantity' => $validated['quantity'],

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => $validated['source'] ?? null,
                'notes' => $validated['notes'] ?? 'Barang masuk ke master asset.',
            ]);
        });

        return back()->with('success', 'Stock in berhasil dicatat.');
    }

    /**
     * brg kluar dari asset.
     */
    public function storeStockOut(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['quantity'] > $asset->total_good) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah barang keluar tidak boleh lebih besar dari total barang bagus.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            $asset->update([
                'total_asset' => $asset->total_asset - $validated['quantity'],
                'total_good' => $asset->total_good - $validated['quantity'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'stock_out',
                'quantity' => $validated['quantity'],

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Barang keluar dari master asset.',
            ]);
        });

        return back()->with('success', 'Stock out berhasil dicatat.');
    }

    /**
     * tf asset antar lab.
     */
    public function storeTransfer(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'from_lab_id' => ['required', 'exists:laboratories,id'],
            'to_lab_id' => [
                'required',
                'exists:laboratories,id',
                'different:from_lab_id',
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $fromAssetLab = AssetLab::where('asset_id', $asset->id)
            ->where('lab_id', $validated['from_lab_id'])
            ->first();

        if (!$fromAssetLab) {
            return back()
                ->withInput()
                ->withErrors([
                    'from_lab_id' => 'Asset tidak tersedia di lab asal.',
                ]);
        }

        if ($validated['quantity'] > $fromAssetLab->total_asset_lab) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah transfer tidak boleh lebih besar dari stok lab asal.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated, $fromAssetLab) {
            $toAssetLab = AssetLab::firstOrCreate(
                [
                    'asset_id' => $asset->id,
                    'lab_id' => $validated['to_lab_id'],
                ],
                [
                    'total_asset_lab' => 0,
                ]
            );

            $beforeFromLabStock = $fromAssetLab->total_asset_lab;
            $beforeToLabStock = $toAssetLab->total_asset_lab;

            $fromAssetLab->update([
                'total_asset_lab' => $fromAssetLab->total_asset_lab - $validated['quantity'],
            ]);

            $toAssetLab->update([
                'total_asset_lab' => $toAssetLab->total_asset_lab + $validated['quantity'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'transfer',

                'from_lab_id' => $validated['from_lab_id'],
                'to_lab_id' => $validated['to_lab_id'],

                'quantity' => $validated['quantity'],

                'before_total_asset' => $asset->total_asset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $asset->total_good,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $asset->total_damaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $asset->total_loss,
                'after_total_loss' => $asset->total_loss,

                'before_from_lab_stock' => $beforeFromLabStock,
                'after_from_lab_stock' => $fromAssetLab->total_asset_lab,

                'before_to_lab_stock' => $beforeToLabStock,
                'after_to_lab_stock' => $toAssetLab->total_asset_lab,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Transfer asset antar lab.',
            ]);
        });

        return back()->with('success', 'Transfer asset berhasil dicatat.');
    }

    /**
     * uabh brg good jadi damged
     */
    public function storeDamaged(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['quantity'] > $asset->total_good) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah barang rusak tidak boleh lebih besar dari total barang bagus.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            $asset->update([
                'total_good' => $asset->total_good - $validated['quantity'],
                'total_damaged' => $asset->total_damaged + $validated['quantity'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'damaged',
                'quantity' => $validated['quantity'],

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Barang berubah status menjadi rusak.',
            ]);
        });

        return back()->with('success', 'Status barang rusak berhasil dicatat.');
    }

    /**
     * ubah brg good atau damaged jadi loss
     */
    public function storeLost(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'condition_from' => [
                'required',
                Rule::in(['good', 'damaged']),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['condition_from'] === 'good' && $validated['quantity'] > $asset->total_good) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah barang hilang tidak boleh lebih besar dari total barang bagus.',
                ]);
        }

        if ($validated['condition_from'] === 'damaged' && $validated['quantity'] > $asset->total_damaged) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah barang hilang tidak boleh lebih besar dari total barang rusak.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            if ($validated['condition_from'] === 'good') {
                $asset->update([
                    'total_asset' => $asset->total_asset - $validated['quantity'],
                    'total_good' => $asset->total_good - $validated['quantity'],
                    'total_loss' => $asset->total_loss + $validated['quantity'],
                ]);
            }

            if ($validated['condition_from'] === 'damaged') {
                $asset->update([
                    'total_asset' => $asset->total_asset - $validated['quantity'],
                    'total_damaged' => $asset->total_damaged - $validated['quantity'],
                    'total_loss' => $asset->total_loss + $validated['quantity'],
                ]);
            }

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'lost',
                'quantity' => $validated['quantity'],

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Barang dicatat sebagai hilang.',
            ]);
        });

        return back()->with('success', 'Barang hilang berhasil dicatat.');
    }

    /**
     *  brg damaged jadi good lagi/repaired
     */
    public function storeRepaired(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['quantity'] > $asset->total_damaged) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity' => 'Jumlah barang diperbaiki tidak boleh lebih besar dari total barang rusak.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            $asset->update([
                'total_good' => $asset->total_good + $validated['quantity'],
                'total_damaged' => $asset->total_damaged - $validated['quantity'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'repaired',
                'quantity' => $validated['quantity'],

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Barang rusak berhasil diperbaiki.',
            ]);
        });

        return back()->with('success', 'Barang diperbaiki berhasil dicatat.');
    }

    /**
     * koreksi stok nya manual.
     */
    public function storeAdjustment(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'total_asset' => ['required', 'integer', 'min:0'],
            'total_good' => ['required', 'integer', 'min:0'],
            'total_damaged' => ['required', 'integer', 'min:0'],
            'total_loss' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $totalPhysicalStock = $validated['total_good'] + $validated['total_damaged'];

        if ($totalPhysicalStock !== (int) $validated['total_asset']) {
            return back()
                ->withInput()
                ->withErrors([
                    'total_asset' => 'Total asset harus sama dengan total good + damaged.',
                ]);
        }

        DB::transaction(function () use ($asset, $validated) {
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;
            $beforeDamaged = $asset->total_damaged;
            $beforeLoss = $asset->total_loss;

            $asset->update([
                'total_asset' => $validated['total_asset'],
                'total_good' => $validated['total_good'],
                'total_damaged' => $validated['total_damaged'],
                'total_loss' => $validated['total_loss'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => $validated['total_asset'] - $beforeTotalAsset,

                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,

                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,

                'before_total_damaged' => $beforeDamaged,
                'after_total_damaged' => $asset->total_damaged,

                'before_total_loss' => $beforeLoss,
                'after_total_loss' => $asset->total_loss,

                'source' => null,
                'notes' => $validated['notes'] ?? 'Koreksi manual stok asset.',
            ]);
        });

        return back()->with('success', 'Adjustment asset berhasil dicatat.');
    }

    public function export(string $format)
    {
        $export = new AssetLogExport();

        return match ($format) {
            'pdf'   => $export->downloadPdf(),
            'excel' => $export->downloadExcel(),
            'csv'   => $export->downloadCsv(),
            default => abort(404),
        };
    }
}
