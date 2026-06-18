<?php

namespace App\Exports;

use App\Models\RequestLab;
use Illuminate\Support\Collection;

class RequestLabExport extends BaseExport
{
    public function collection(): Collection
    {
        return RequestLab::with(['user', 'lab', 'request_items'])
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'request_id' => 'REQ-'.str_pad($request->id, 3, '0', STR_PAD_LEFT),
                    'name' => $request->user->name ?? '-',
                    'laboratory' => $request->lab->lab_name ?? '-',
                    'total_request' => $request->request_items->sum('total_request'),
                    'request_date' => $request->request_date
                        ? \Carbon\Carbon::parse($request->request_date)->format('Y-m-d')
                        : '-',
                    'status' => $request->request_status === 'partial'
                        ? 'Partially Approved'
                        : ucwords($request->request_status),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID Request', 'Name', 'Laboratory', 'Total Request', 'Date', 'Status'];
    }

    public function title(): string
    {
        return 'Request Lab';
    }
}
