<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Laboratory;
use App\Models\RequestLab;
use App\Models\RequestItem;
use App\Models\Asset;
use App\Models\AssetLab;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'spv inventory') {
            return $this->spvDashboard();
        }

        return $this->staffDashboard($user);
    }

    private function spvDashboard()
    {
        $totalUsers = User::count();

        $totalLaboratory = Laboratory::count();

        $totalRequestLab = RequestLab::count();
        $totalReturnRequests = \App\Models\ReturnRequest::count();
        $totalTransferRequests = \App\Models\TransferRequest::count();

        $laboratories = Laboratory::with('pcs')
            ->orderBy('lab_name')
            ->get();

        $chartData = $laboratories->map(function ($lab) {
            return [
                'label' => $lab->lab_name,
                'active' => $lab->pcs->where('status_pc', 'active')->count(),
                'inactive' => $lab->pcs->where('status_pc', 'inactive')->count(),
            ];
        })->values();

        // Low stock GUDANG (asset SPV) — tetap seperti semula.
        $lowStockItems = Asset::where('total_good', '<=', 10)
            ->orderBy('total_good')
            ->limit(6)
            ->get(['id', 'asset_name', 'total_good']);

        $recentRequests = RequestLab::with('user')
            ->withSum('request_items as total_requested_items', 'total_request')
            ->latest()
            ->limit(10)
            ->get();

        $recentReturnRequests = \App\Models\ReturnRequest::with(['laboratory', 'requestedBy'])
            ->latest()
            ->limit(10)
            ->get();

        $recentTransferRequests = \App\Models\TransferRequest::with(['fromLab', 'toLab', 'requestedBy'])
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.dashboard.spv-dashboard', compact(
            'totalUsers',
            'totalLaboratory',
            'totalRequestLab',
            'totalReturnRequests',
            'totalTransferRequests',
            'chartData',
            'lowStockItems',
            'recentRequests',
            'recentReturnRequests',
            'recentTransferRequests'
        ));
    }

    private function staffDashboard(User $user)
    {
        $laboratories = $user->labs()
            ->with(['pcs', 'users'])
            ->orderBy('lab_name')
            ->get();

        $labIds = $laboratories->pluck('id');

        $totalLaboratory  = $laboratories->count();
        $totalPcActive    = $laboratories->flatMap(fn($lab) => $lab->pcs)->where('status_pc', 'active')->count();
        $totalPcInactive  = $laboratories->flatMap(fn($lab) => $lab->pcs)->where('status_pc', 'inactive')->count();
        $totalPc          = $totalPcActive + $totalPcInactive;

        $totalRequestLab  = RequestLab::whereIn('lab_id', $labIds)->count();
        $totalReturnRequests = \App\Models\ReturnRequest::whereIn('lab_id', $labIds)->count();
        $totalTransferRequests = \App\Models\TransferRequest::whereIn('from_lab_id', $labIds)
            ->orWhereIn('to_lab_id', $labIds)
            ->count();

        // staff di lab yang sama
        $labStaff = User::whereHas('labs', fn($q) => $q->whereIn('laboratories.id', $labIds))
            ->get(['id', 'name', 'nim', 'role']);

        $totalUsers = $labStaff->count();

        $chartData = $laboratories->map(fn($lab) => [
            'label'    => $lab->lab_name,
            'active'   => $lab->pcs->where('status_pc', 'active')->count(),
            'inactive' => $lab->pcs->where('status_pc', 'inactive')->count(),
        ])->values();

        // Ambil semua user unik dari lab-lab yang dimiliki staff
        $labUsers = $laboratories->flatMap(function ($lab) {
            return $lab->users;
        })->unique('id')->sortBy('name')->values();

        $totalUsers = $labUsers->count();

        // ── (#3) LOW STOCK ITEMS — ambil dari ASSET LAB (stok di lab staff),
        //         bukan asset gudang SPV. Tampilkan yang total_good_lab < 3.
        $labNameById = $laboratories->pluck('lab_name', 'id');

        $labLowStockItems = AssetLab::with('asset')
            ->whereIn('lab_id', $labIds)
            ->where('total_good_lab', '<', 3)
            ->whereHas('asset')
            ->orderBy('total_good_lab')
            ->limit(6)
            ->get()
            ->map(fn ($al) => [
                'asset_name' => $al->asset->asset_name ?? '-',
                'lab_name'   => $labNameById[$al->lab_id] ?? '-',
                'in_stock'   => $al->total_good_lab,
            ]);

        $recentRequests = RequestLab::with('user')
            ->withSum('request_items as total_requested_items', 'total_request')
            ->whereIn('lab_id', $labIds)
            ->latest()
            ->limit(10)
            ->get();

        $recentReturnRequests = \App\Models\ReturnRequest::with(['laboratory', 'requestedBy'])
            ->whereIn('lab_id', $labIds)
            ->latest()
            ->limit(10)
            ->get();

        $recentTransferRequests = \App\Models\TransferRequest::with(['fromLab', 'toLab', 'requestedBy'])
            ->where(function ($q) use ($labIds) {
                $q->whereIn('from_lab_id', $labIds)
                  ->orWhereIn('to_lab_id', $labIds);
            })
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.dashboard.staff-dashboard', compact(
            'user',
            'laboratories',
            'totalLaboratory',
            'totalPcActive',
            'totalPcInactive',
            'totalPc',
            'totalRequestLab',
            'totalReturnRequests',
            'totalTransferRequests',
            'totalUsers',
            'labStaff',
            'chartData',
            'labUsers',
            'labLowStockItems',
            'recentRequests',
            'recentReturnRequests',
            'recentTransferRequests'
        ));
    }
}