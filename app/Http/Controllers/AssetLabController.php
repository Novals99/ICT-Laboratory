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
                // Kurangi SPV
                $asset->total_good -= 1;
                $asset->save(); // trigger auto-calculate total_asset

                // Tambah lab
                $assetLab->total_good_lab += 1;

            } elseif ($field === 'total_damaged_lab') {
                if ($assetLab->total_good_lab < 1) {
                    return back()->with('error', 'Stok good di lab tidak mencukupi untuk merusakkan.');
                }
                $assetLab->total_good_lab -= 1;
                $assetLab->total_damaged_lab += 1;

            } elseif ($field === 'total_loss_lab') {
                if ($assetLab->total_good_lab < 1) {
                    return back()->with('error', 'Stok good di lab tidak mencukupi untuk menghilangkan.');
                }
                $assetLab->total_good_lab -= 1;
                $assetLab->total_loss_lab += 1;
            }

        } elseif ($action === 'decrement') {
            if ($field === 'total_good_lab') {
                if ($assetLab->total_good_lab < 1) {
                    return back()->with('error', 'Stok good di lab tidak mencukupi.');
                }
                if ($asset) {
                    $asset->total_good += 1;
                    $asset->save();
                }
                $assetLab->total_good_lab -= 1;

            } elseif ($field === 'total_damaged_lab') {
                if ($assetLab->total_damaged_lab < 1) {
                    return back()->with('error', 'Stok damaged di lab tidak mencukupi.');
                }
                $assetLab->total_damaged_lab -= 1;
                $assetLab->total_good_lab += 1;

            } elseif ($field === 'total_loss_lab') {
                if ($assetLab->total_loss_lab < 1) {
                    return back()->with('error', 'Stok loss di lab tidak mencukupi.');
                }
                $assetLab->total_loss_lab -= 1;
                $assetLab->total_good_lab += 1;
            }
        }

        $assetLab->save(); // trigger auto-calculate total_asset_lab

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
                if ($assetLab->total_good_lab > 0) {
                    $asset->total_good += $assetLab->total_good_lab;
                }
                if ($assetLab->total_damaged_lab > 0) {
                    $asset->total_damaged += $assetLab->total_damaged_lab;
                }
                if ($assetLab->total_loss_lab > 0) {
                    $asset->total_loss += $assetLab->total_loss_lab;
                }
                $asset->save(); // trigger auto-calculate
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