<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nim',
        'role',
        'username',
        'password',
        'status_user',
        'email'
    ];

    public function labs() {
        return $this->belongsToMany(Laboratory::class, 'staff_labs', 'user_id', 'lab_id');
    }

    public function request_labs() {
        return $this->hasMany(RequestLab::class);
    }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     return $this->status_user === true;
    // }
}
