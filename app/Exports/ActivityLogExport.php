<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLogExport extends BaseExport
{
    public function collection(): Collection
    {
        return ActivityLog::query()
            ->with('user')

            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('activity', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('role', 'like', "%{$search}%");
                      });
                });
            })

            ->when(request('role'), function ($query, $role) {
                $query->whereHas('user', function ($q) use ($role) {
                    $q->where('role', $role);
                });
            })

            ->latest()
            ->get()

            ->map(fn ($log) => [
                'date_time' => $log->created_at?->format('d-m-Y H:i') ?? '-',
                'user'      => $log->user->name ?? '-',
                'role'      => $log->user->role ?? '-',
                'activity'  => $log->activity ?? '-',
            ]);
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'User',
            'Role',
            'Activity',
        ];
    }

    public function title(): string
    {
        return 'Activity Log';
    }
}