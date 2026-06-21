<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ActivityLogExport;

class ActivityLogController extends Controller
{
     public function index()
    {
    $search = request('search');
    $role = request('role');

    $logs = ActivityLog::query()
    ->with('user')

    ->when($search, function ($query) use ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('activity', 'like', "%{$search}%")
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('role', 'like', "%{$search}%");
              });
        });
    })

    ->when($role, function ($query) use ($role) {
        $query->whereHas('user', function ($q) use ($role) {
            $q->where('role', $role);
        });
    })

    ->latest()
    ->paginate(10)
    ->withQueryString();

    return view('pages.activity-log.index', [
        'logs' => $logs,
        'search' => $search,
        'startDate' => '',
        'endDate' => '',
        'role' => $role,
    ]);
    }

    public function export(string $format)
    {
    $export = new ActivityLogExport();

    return match ($format) {
        'pdf'   => $export->downloadPdf(),
        'excel' => $export->downloadExcel(),
        'csv'   => $export->downloadCsv(),
        default => abort(404),
    };
    }
}    