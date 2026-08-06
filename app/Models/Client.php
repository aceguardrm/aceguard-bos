<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'postcode',
        'country',
        'status',
        'notes',
    ];

    /**
     * Security controls assigned to this client.
     */
    public function securityControls(): HasMany
    {
        return $this->hasMany(SecurityControl::class);
    }
}