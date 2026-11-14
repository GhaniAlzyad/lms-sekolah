<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'unit_id',
        'nomor_induk',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke Unit Sekolah (MTs / MA)
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}