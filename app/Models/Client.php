<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Security controls assigned to this workspace.
     */
    public function securityControls(): HasMany
    {
        return $this->hasMany(SecurityControl::class);
    }

    /**
     * Current Business Pulse™ assessment.
     */
    public function businessPulseAssessment(): HasOne
    {
        return $this->hasOne(BusinessPulseAssessment::class);
    }

    /**
     * Projects belonging to this organisation workspace.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}