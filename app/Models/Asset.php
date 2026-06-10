<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_name',
        'asset_category',
        'total_asset',
        'total_good',
        'total_damaged',
        'total_loss',
        'asset_entry',
    ];

    public function assets()
    {
        //
    }
}
