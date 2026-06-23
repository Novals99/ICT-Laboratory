<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;

class UserExport extends BaseExport
{
    public function collection(): Collection
    {
        return User::with('labs')
            ->select('id', 'name', 'nim', 'role', 'username', 'status_user', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'name'        => $user->name,
                    'nim'         => $user->nim,
                    'role'        => $user->role,
                    'labs'        => $user->labs->pluck('lab_name')->join(', ') ?: '-',
                    'username'    => $user->username,
                    'status_user' => $user->status_user,
                    'email'       => $user->email,
                ];
            });
    }

    public function headings(): array
    {
        return ['Name', 'NIM', 'Role', 'Laboratory', 'Username', 'Status', 'Email'];
    }

    public function title(): string
    {
        return 'Users';
    }
}