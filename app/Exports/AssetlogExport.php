<?php

namespace App\Exports;

use App\Models\AssetLog;
use Illuminate\Support\Collection;

class AssetLogExport extends BaseExport
{
    public function collection(): Collection
    {
        return AssetLog::query()
            ->with(['asset', 'user', 'fromLab', 'toLab'])
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('asset', fn($a) => $a->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_category', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(request('type'), fn($q, $types) => $q->whereIn('type', (array) $types))
            ->when(request('lab_id'), fn($q, $labIds) => $q->where(function ($q) use ($labIds) {
                $q->whereIn('from_lab_id', (array) $labIds)
                  ->orWhereIn('to_lab_id', (array) $labIds);
            }))
            ->when(request('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when(request('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->get()
            ->map(fn($log) => [
                'date'           => $log->created_at?->format('d-m-Y H:i') ?? '-',
                'asset'          => $log->asset->asset_name ?? '-',
                'category'       => $log->asset->asset_category ?? '-',
                'type'           => ucwords(str_replace('_', ' ', $log->type)),
                'related_lab'    => match(true) {
                    $log->type === 'transfer' => ($log->fromLab->lab_name ?? '-') . ' → ' . ($log->toLab->lab_name ?? '-'),
                    (bool) $log->fromLab     => $log->fromLab->lab_name,
                    (bool) $log->toLab       => $log->toLab->lab_name,
                    default                  => '-',
                },
                'quantity'       => $log->quantity ?? 0,
                'before_total'   => $log->before_total_asset ?? 0,
                'before_good'    => $log->before_total_good ?? 0,
                'before_damaged' => $log->before_total_damaged ?? 0,
                'before_loss'    => $log->before_total_loss ?? 0,
                'after_total'    => $log->after_total_asset ?? 0,
                'after_good'     => $log->after_total_good ?? 0,
                'after_damaged'  => $log->after_total_damaged ?? 0,
                'after_loss'     => $log->after_total_loss ?? 0,
                'source'         => $log->source ?? '-',
                'handled_by'     => $log->user->name ?? '-',
                'role'           => $log->user?->role ? ucwords($log->user->role) : '-',
                'notes'          => $log->notes ?? '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'Date', 'Asset', 'Category', 'Type', 'Related Lab', 'Qty',
            'Before Total', 'Before Good', 'Before Damaged', 'Before Loss',
            'After Total', 'After Good', 'After Damaged', 'After Loss',
            'Source', 'Handled By', 'Role', 'Notes',
        ];
    }

    public function title(): string
    {
        return 'Asset Log';
    }
}