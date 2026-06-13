<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'user_id',
    ];

    public function lab()
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
