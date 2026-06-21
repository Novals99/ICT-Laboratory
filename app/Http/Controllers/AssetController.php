<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Exports\AssetExport;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = Asset::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_category', 'like', "%{$search}%")
                        ->orWhere('total_asset', 'like', "%{$search}%")
                        ->orWhere('total_good', 'like', "%{$search}%")
                        ->orWhere('total_damaged', 'like', "%{$search}%")
                        ->orWhere('total_loss', 'like', "%{$search}%");
                });
            })
            ->when(request('category'), function ($query, $categories) {
                $query->whereIn('asset_category', (array) $categories);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.asset.index', compact('assets'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('asset.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_category' => [
                'required',
                Rule::in(['electronic', 'non-electronic', 'component-pc']),
            ],

            'items' => ['required', 'array', 'min:1'],

            'items.*.asset_name' => ['required', 'string', 'max:255'],
            'items.*.total_asset' => ['required', 'integer', 'min:0'],
            'items.*.total_good' => ['required', 'integer', 'min:0'],
            'items.*.total_damaged' => ['required', 'integer', 'min:0'],
            'items.*.total_loss' => ['required', 'integer', 'min:0'],
            'items.*.source' => ['nullable', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        foreach ($validated['items'] as $index => $item) {
            $totalPhysicalStock = $item['total_good'] + $item['total_damaged'];

            if ($totalPhysicalStock !== (int) $item['total_asset']) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "items.{$index}.total_asset" => 'Total good + damaged tidak boleh lebih besar dari total asset.',
                    ]);
            }
        }

        foreach ($validated['items'] as $item) {
            $asset = Asset::create([
                'asset_name' => $item['asset_name'],
                'asset_category' => $validated['asset_category'],
                'total_asset' => $item['total_asset'],
                'total_good' => $item['total_good'],
                'total_damaged' => $item['total_damaged'],
                'total_loss' => $item['total_loss'],
            ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'stock_in',
                'quantity' => $item['total_asset'],

                'before_total_asset' => 0,
                'after_total_asset' => (int) $item['total_asset'],

                'before_total_good' => 0,
                'after_total_good' => (int) $item['total_good'],

                'before_total_damaged' => 0,
                'after_total_damaged' => (int) $item['total_damaged'],

                'before_total_loss' => 0,
                'after_total_loss' => (int) $item['total_loss'],

                'source' => $item['source'] ?? null,
                'notes' => $item['notes'] ?? 'Initial asset stock.',
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Created asset: ' . $asset->asset_name,
            ]);
        }

        return redirect()
            ->route('asset.index')
            ->with('success', 'Asset berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        return redirect()->route('asset.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        return redirect()->route('asset.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_name' => ['required', 'string', 'max:255'],
            'asset_category' => [
                'required',
                Rule::in(['electronic', 'non-electronic', 'component-pc']),
            ],
            'total_asset' => ['required', 'integer', 'min:0'],
            'total_good' => ['required', 'integer', 'min:0'],
            'total_damaged' => ['required', 'integer', 'min:0'],
            'total_loss' => ['required', 'integer', 'min:0'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $totalPhysicalStock = $validated['total_good'] + $validated['total_damaged'];

        if ($totalPhysicalStock > $validated['total_asset']) {
            return back()
                ->withInput()
                ->withErrors([
                    'total_asset' => 'Total good + damaged tidak boleh lebih besar dari total asset.',
                ]);
        }

        $oldTotalAsset = $asset->total_asset;
        $oldGood = $asset->total_good;
        $oldDamaged = $asset->total_damaged;
        $oldLoss = $asset->total_loss;

        $asset->update([
            'asset_name' => $validated['asset_name'],
            'asset_category' => $validated['asset_category'],
            'total_asset' => $validated['total_asset'],
            'total_good' => $validated['total_good'],
            'total_damaged' => $validated['total_damaged'],
            'total_loss' => $validated['total_loss'],
        ]);

        $stockChanged =
            $oldTotalAsset !== (int) $validated['total_asset'] ||
            $oldGood !== (int) $validated['total_good'] ||
            $oldDamaged !== (int) $validated['total_damaged'] ||
            $oldLoss !== (int) $validated['total_loss'];

        if ($stockChanged) {
            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => $validated['total_asset'] - $oldTotalAsset,

                'before_total_asset' => $oldTotalAsset,
                'after_total_asset' => (int) $validated['total_asset'],

                'before_total_good' => $oldGood,
                'after_total_good' => (int) $validated['total_good'],

                'before_total_damaged' => $oldDamaged,
                'after_total_damaged' => (int) $validated['total_damaged'],

                'before_total_loss' => $oldLoss,
                'after_total_loss' => (int) $validated['total_loss'],

                'source' => $validated['source'] ?? null,
                'notes' => $validated['notes'] ?? 'Asset stock updated.',
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Updated asset: ' . $asset->asset_name,
        ]);

        return redirect()
            ->route('asset.index')
            ->with('success', 'Asset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => 'Deleted asset: ' . $asset->asset_name,
        ]);
        
        $asset->delete();

        return redirect()
            ->route('asset.index')
            ->with('success', 'Asset berhasil dihapus.');
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
}
