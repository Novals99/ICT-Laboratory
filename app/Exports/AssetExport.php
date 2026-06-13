<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Support\Collection;

class AssetExport extends BaseExport
{
    public function collection(): Collection
    {
        return Asset::select('asset_name', 'asset_category', 'total_asset', 'total_good', 'total_damaged', 'total_loss')
            ->get()
            ->map(function ($asset) {
                return [
                    'asset_name'     => $asset->asset_name,
                    'asset_category' => $asset->asset_category,
                    'total_asset'    => $asset->total_asset,
                    'total_good'     => $asset->total_good,
                    'total_damaged'  => $asset->total_damaged,
                    'total_loss'     => $asset->total_loss,
                ];
            });
    }

    public function headings(): array
    {
        return ['Asset Name', 'Category', 'Total Asset', 'Good', 'Damaged', 'Loss'];
    }

    public function title(): string
    {
        return 'Assets';
    }
}