<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabRequest extends Model
{
    use HasFactory;

    protected $table = 'lab_requests';

    protected $fillable = [
        'request_id',
        'name',
        'total_request',
        'request_date',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];
}