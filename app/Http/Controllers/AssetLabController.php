<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class AssetLabController extends Controller
{
    public function adjust(Request $request, Laboratory $laboratory, $assetId)
    {
        $field  = $request->input('field');
        $action = $request->input('action');

        $allowed = ['total_good_lab', 'total_damaged_lab', 'total_loss_lab'];
        if (!in_array($field, $allowed)) abort(400);

        $assetLab = AssetLab::where('lab_id', $laboratory->id)
                            ->where('asset_id', $assetId)
                            ->firstOrFail();

        $asset = Asset::find($assetId);

        if ($action === 'increment') {
            if ($field === 'total_good_lab') {
                if (!$asset || $asset->total_good < 1) {
                    return back()->with('error', 'Stok good di inventory tidak mencukupi.');
                }
                $asset->decrement('total_good');
                $this->recalculateAssetTotal($asset);

                $assetLab->increment('total_good_lab');

            } elseif ($field === 'total_damaged_lab') {
                if ($assetLab->total_good_lab > 0) {
                    $assetLab->decrement('total_good_lab');
                    $assetLab->increment('total_damaged_lab');
                } else {
                    return back()->with('error', 'Stok good di lab tidak mencukupi untuk merusakkan.');
                }

            } elseif ($field === 'total_loss_lab') {
                if ($assetLab->total_good_lab > 0) {
                    $assetLab->decrement('total_good_lab');
                    $assetLab->increment('total_loss_lab');
                    $assetLab->decrement('total_asset_lab');
                } else {
                    return back()->with('error', 'Stok good di lab tidak mencukupi untuk menghilangkan.');
                }
            }

        } elseif ($action === 'decrement' && $assetLab->$field > 0) {
            if ($field === 'total_good_lab') {
                if ($asset) {
                    $asset->increment('total_good');
                    $this->recalculateAssetTotal($asset);
                }
                $assetLab->decrement('total_good_lab');

            } elseif ($field === 'total_damaged_lab') {
                $assetLab->decrement('total_damaged_lab');
                $assetLab->increment('total_good_lab');

            } elseif ($field === 'total_loss_lab') {
                $assetLab->decrement('total_loss_lab');
                $assetLab->increment('total_good_lab');
                $assetLab->increment('total_asset_lab');
            }
        }

        $assetLab->refresh();
        $assetLab->update([
            'total_asset_lab' => $assetLab->total_good_lab
                               + $assetLab->total_damaged_lab
                               + $assetLab->total_loss_lab,
        ]);

        return back()->with('success', 'Stok berhasil diperbarui.')->with('section', 'asset');
    }

    public function removeFromLab(Laboratory $laboratory, $assetId)
    {
        $assetLab = AssetLab::where('lab_id', $laboratory->id)
                            ->where('asset_id', $assetId)
                            ->first();

        if ($assetLab) {
            $asset = Asset::find($assetId);
            if ($asset) {
                // Balikin semua kondisi ke SPV sesuai kondisi terakhir di lab
                if ($assetLab->total_good_lab > 0) {
                    $asset->increment('total_good', $assetLab->total_good_lab);
                }
                if ($assetLab->total_damaged_lab > 0) {
                    $asset->increment('total_damaged', $assetLab->total_damaged_lab);
                }
                if ($assetLab->total_loss_lab > 0) {
                    $asset->increment('total_loss', $assetLab->total_loss_lab);
                }
                $this->recalculateAssetTotal($asset);
            }
            $assetLab->delete();
        }

        return back()->with('success', 'Aset berhasil dihapus dari lab.')->with('section', 'asset');
    }

    private function recalculateAssetTotal(Asset $asset)
    {
        $asset->refresh();
        $asset->update([
            'total_asset' => $asset->total_good + $asset->total_damaged + $asset->total_loss
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(AssetLab $assetLab)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetLab $assetLab)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetLab $assetLab)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetLab $assetLab)
    {
        //
    }
}
