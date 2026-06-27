<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboratory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lab_name',
        'capacity',
        'total_pc_active',
        'total_pc_inactive',
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'staff_labs',
            'lab_id',
            'user_id'
        );
    }

    public function staffLabs()
    {
        return $this->hasMany(
            StaffLab::class,
            'lab_id'
        );
    }

    public function assets()
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_labs',
            'lab_id',
            'asset_id'
        )->withPivot(['total_asset_lab', 'total_good_lab', 'total_damaged_lab', 'total_loss_lab'])
         ->withTimestamps();
    }

    public function pcs()
    {
        return $this->hasMany(
            Pc::class,
            'lab_id'
        );
    }

    public function request_labs()
    {
        return $this->hasMany(
            RequestLab::class,
            'lab_id'
        );
    }
}
