<?php

namespace App\Exports;

use App\Models\Laboratory;
use Illuminate\Support\Collection;

class LabExport extends BaseExport
{
       public function collection(): Collection
       {
              return Laboratory::with('users')
                     ->select('id', 'lab_name', 'capacity', 'total_pc_active', 'total_pc_inactive')->get()
                     ->map(function ($laboratories) {
                            return [
                                   'lab_name'           => $laboratories->lab_name,
                                   'capacity'           => $laboratories->capacity,
                                   'admin'              => $laboratories->users->where('role', 'admin')->pluck('name')->join(', '),
                                   'total_pc_active'    => $laboratories->total_pc_active,
                                   'total_pc_inactive'  => $laboratories->total_pc_inactive,
                            ];
                     });
       }

       public function headings(): array
       {
              return ['Lab Name', 'Capacity', 'Admin', 'Active', 'Inactive'];
       }

       public function title(): string
       {
              return 'Laboratory';
       }
}
