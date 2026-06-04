<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabRequest extends Model
{
    protected $fillable = [
        'request_id',
        'name',
        'total_request',
        'request_date',
        'status',
        'approved_by',
    ];
}
