<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'service', 'duration_minutes', 'appointment_at', 'ends_at',
        'timezone', 'name', 'email', 'phone', 'company', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
