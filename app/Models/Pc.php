<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pc extends Model
{
    use HasFactory;
    protected $fillable = [
        'lab_id',
        'type_pc',
        'status_pc',
        'pc_entry',
        'processor',
        'ram',
        'ssd',
        'motherboard',
        'vga',
        'cpu_fan',
        'powersupply'
    ];

    public function lab() {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }
}
