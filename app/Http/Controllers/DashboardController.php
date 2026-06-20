<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Laboratory;
use App\Models\RequestLab;
use App\Models\RequestItem;
use App\Models\Asset;

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

        $lowStockItems = Asset::where('total_good', '<=', 10)
            ->orderBy('total_good')
            ->limit(6)
            ->get([
                'id',
                'asset_name',
                'total_good',
            ]);

        $recentRequests = RequestLab::with('user')
            ->withSum('request_items as total_requested_items', 'total_request')
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.dashboard.spv-dashboard', compact(
            'totalUsers',
            'totalLaboratory',
            'totalRequestLab',
            'chartData',
            'lowStockItems',
            'recentRequests',
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
        $totalPc = $totalPcActive + $totalPcInactive;

        $totalRequestLab  = RequestLab::whereIn('lab_id', $labIds)->count();

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

        $recentRequests = RequestLab::with('user')
            ->withSum('request_items as total_requested_items', 'total_request')
            ->whereIn('lab_id', $labIds)
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
            'totalPc',
            'totalRequestLab',
            'totalUsers',
            'labStaff',
            'chartData',
            'totalUsers',
            'labUsers',
            'recentRequests',
        ));
    }
}
