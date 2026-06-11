<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\LabRequest;
use App\Models\RequestLab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Asset;

class RequestLabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = LabRequest::latest()->paginate(11);

        return view('pages.dashboard.requestlab.index', compact('requests'));
    }

    // ---------------------------------------------------------------
    // DETAIL (AJAX/JSON) — dipanggil dari modal
    // GET /requestlab/{id}/detail
    //
    // Mengambil data LabRequest lalu join ke AssetLog untuk
    // mendapatkan daftar aset yang diminta (electronic & non-electronic)
    // berdasarkan field 'type' di model AssetLog
    // ---------------------------------------------------------------
   public function detail($id)
{
    $labRequest = LabRequest::findOrFail($id);

    $electronic = Asset::where('asset_category', 'electronic')
        ->get()
        ->map(function ($asset) {
            return [
                'asset_name' => $asset->asset_name,
                'quantity'   => $asset->total_asset,
            ];
        })
        ->toArray();

    $nonElectronic = Asset::where('asset_category', 'non-electronic')
        ->get()
        ->map(function ($asset) {
            return [
                'asset_name' => $asset->asset_name,
                'quantity'   => $asset->total_asset,
            ];
        })
        ->toArray();

    return response()->json([
        'request_id'     => $labRequest->request_id ?? $labRequest->id,
        'user_name'      => $labRequest->name ?? '-',
        'total_request'  => $labRequest->total_request ?? 0,
        'notes'          => null,
        'request_date'   => $labRequest->request_date,
        'status'         => $labRequest->status,
        'electronic'     => $electronic,
        'non_electronic' => $nonElectronic,
    ]);
}

    // ---------------------------------------------------------------
    // UPDATE STATUS — Approved / Rejected dari tombol modal
    // PATCH /requestlab/{id}/status
    // ---------------------------------------------------------------
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
        ]);

        $labRequest = LabRequest::findOrFail($id);
        $labRequest->update([
            'status'      => $validated['status'],
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('requestlab.index')
            ->with('success', 'Status berhasil diubah menjadi ' . $validated['status'] . '.');
    }

    // ---------------------------------------------------------------
    // DESTROY — hapus data request
    // DELETE /requestlab/{id}
    // ---------------------------------------------------------------
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $labRequest = LabRequest::findOrFail($id);
            $labRequest->delete();

            DB::commit();

            return redirect()->route('requestlab.index')
                ->with('success', 'Request berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // STORE — simpan request baru
    // POST /requestlab
    // ---------------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'total_request' => 'required|integer|min:1',
            'request_date'  => 'required|date',
            'status'        => 'nullable|in:Pending,Approved,Rejected',
        ]);

        LabRequest::create([
            'name'          => $validated['name'],
            'total_request' => $validated['total_request'],
            'request_date'  => $validated['request_date'],
            'status'        => $validated['status'] ?? 'Pending',
        ]);

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil ditambahkan.');
    }

    // ---------------------------------------------------------------
    // EDIT — form edit (halaman terpisah, opsional)
    // GET /requestlab/{id}/edit
    // ---------------------------------------------------------------
    public function edit($id)
    {
        $labRequest = LabRequest::findOrFail($id);
        return view('pages.dashboard.requestlab.edit', compact('labRequest'));
    }

    // ---------------------------------------------------------------
    // UPDATE — simpan perubahan
    // PUT /requestlab/{id}
    // ---------------------------------------------------------------
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'total_request' => 'required|integer|min:1',
            'request_date'  => 'required|date',
            'status'        => 'nullable|in:Pending,Approved,Rejected',
        ]);

        $labRequest = LabRequest::findOrFail($id);
        $labRequest->update($validated);

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil diperbarui.');
    }
}
