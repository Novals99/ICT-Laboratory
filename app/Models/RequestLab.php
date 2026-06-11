<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLab extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'lab_id',
        'request_status',
        'request_date'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lab() {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function request_items() {
        return $this->hasMany(RequestItem::class, 'request_lab_id');
    }
}
