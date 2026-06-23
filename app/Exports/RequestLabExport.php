<?php

namespace App\Exports;

use App\Models\RequestLab;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class RequestLabExport extends BaseExport
{
    /**
     * Flat rows untuk Excel/CSV:
     * Setiap item barang menjadi satu baris tersendiri.
     * Kolom request (ID, Nama, Lab, Tanggal, Status) hanya diisi pada
     * baris pertama item; baris berikutnya dikosongkan agar terlihat seperti
     * grup visual.
     */
    public function collection(): Collection
    {
        $requests = RequestLab::with(['user', 'lab', 'request_items.asset'])
            ->latest()
            ->get();

        $rows = collect();

        foreach ($requests as $request) {
            $reqId     = 'REQ-' . str_pad($request->id, 3, '0', STR_PAD_LEFT);
            $name      = $request->user->name ?? '-';
            $lab       = $request->lab->lab_name ?? '-';
            $date      = $request->request_date
                ? \Carbon\Carbon::parse($request->request_date)->format('Y-m-d')
                : '-';
            $status    = match ($request->request_status) {
                'partial' => 'Partially Approved',
                'done'    => 'Done',
                default   => ucwords($request->request_status),
            };

            $items = $request->request_items;

            if ($items->isEmpty()) {
                // Tidak ada item — tetap tampilkan baris request dengan kolom item kosong
                $rows->push([
                    'request_id'   => $reqId,
                    'name'         => $name,
                    'laboratory'   => $lab,
                    'date'         => $date,
                    'status'       => $status,
                    'asset_name'   => '-',
                    'qty_request'  => '-',
                    'item_status'  => '-',
                ]);
            } else {
                foreach ($items as $index => $item) {
                    $rows->push([
                        // Kolom request hanya diisi pada baris pertama item
                        'request_id'  => $index === 0 ? $reqId : '',
                        'name'        => $index === 0 ? $name  : '',
                        'laboratory'  => $index === 0 ? $lab   : '',
                        'date'        => $index === 0 ? $date  : '',
                        'status'      => $index === 0 ? $status: '',
                        // Detail barang
                        'asset_name'  => $item->asset->asset_name ?? '-',
                        'qty_request' => $item->total_request,
                        'item_status' => match ($item->status) {
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            default    => 'Pending',
                        },
                    ]);
                }
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID Request',
            'Name',
            'Laboratory',
            'Date',
            'Status',
            'Asset Name',
            'Qty Requested',
            'Item Status',
        ];
    }

    public function title(): string
    {
        return 'Request Lab';
    }

    /**
     * Override downloadPdf() untuk menggunakan template PDF khusus
     * yang menampilkan barang dalam format yang lebih rapi (grouped).
     */
    public function downloadPdf(): mixed
    {
        $requests = RequestLab::with(['user', 'lab', 'request_items.asset'])
            ->latest()
            ->get()
            ->map(function ($request) {
                return [
                    'request_id' => 'REQ-' . str_pad($request->id, 3, '0', STR_PAD_LEFT),
                    'name'       => $request->user->name ?? '-',
                    'laboratory' => $request->lab->lab_name ?? '-',
                    'date'       => $request->request_date
                        ? \Carbon\Carbon::parse($request->request_date)->format('Y-m-d')
                        : '-',
                    'status'     => match ($request->request_status) {
                        'partial' => 'Partially Approved',
                        'done'    => 'Done',
                        default   => ucwords($request->request_status),
                    },
                    'items' => $request->request_items->map(fn($item) => [
                        'asset_name'  => $item->asset->asset_name ?? '-',
                        'qty'         => $item->total_request,
                        'item_status' => match ($item->status) {
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            default    => 'Pending',
                        },
                    ])->values()->toArray(),
                ];
            });

        $title = $this->title();

        $pdf = Pdf::loadView('exports.requestlab_pdf', compact('requests', 'title'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->filename() . '.pdf');
    }
}
