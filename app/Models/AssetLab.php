<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLab extends Model
{
use HasFactory;
protected $fillable = [
    'lab_id',
    'asset_id',
    'total_asset_lab'
];
}
