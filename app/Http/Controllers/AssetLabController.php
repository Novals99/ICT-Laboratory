<?php

namespace App\Http\Controllers;

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

        if ($action === 'increment') {
            $assetLab->increment($field);
        } elseif ($action === 'decrement' && $assetLab->$field > 0) {
            $assetLab->decrement($field);
        }

        $assetLab->refresh();
        $assetLab->update([
            'total_asset_lab' => $assetLab->total_good_lab
                               + $assetLab->total_damaged_lab
                               + $assetLab->total_loss_lab,
        ]);

        return back()->with('success', 'Stok berhasil diperbarui.');
    }

    public function removeFromLab(Laboratory $laboratory, $assetId)
    {
        AssetLab::where('lab_id', $laboratory->id)
                ->where('asset_id', $assetId)
                ->delete();

        return back()->with('success', 'Aset berhasil dihapus dari lab.');
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
