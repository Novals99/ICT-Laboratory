<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'type',
        'quantity',

        'from_lab_id',
        'to_lab_id',

        'before_total_asset',
        'after_total_asset',

        'before_total_good',
        'after_total_good',

        'before_total_damaged',
        'after_total_damaged',

        'before_total_loss',
        'after_total_loss',

        'before_from_lab_stock',
        'after_from_lab_stock',

        'before_to_lab_stock',
        'after_to_lab_stock',

        'source',
        'notes',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromLab()
    {
        return $this->belongsTo(Laboratory::class, 'from_lab_id');
    }

    public function toLab()
    {
        return $this->belongsTo(Laboratory::class, 'to_lab_id');
    }
}
