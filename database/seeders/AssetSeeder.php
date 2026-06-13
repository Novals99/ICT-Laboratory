<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            // sama seperti array di atas
        ];

        foreach ($assets as $asset) {
            Asset::firstOrCreate(['asset_name' => $asset['asset_name']], $asset);
        }
    }
}