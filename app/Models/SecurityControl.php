<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityControl extends Model
{
    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'client_id',
        'category',
        'control',
        'enabled',
        'points',
        'maximum_points',
        'notes',
        'evidence',
        'last_reviewed_at',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'enabled' => 'boolean',
        'last_reviewed_at' => 'datetime',
    ];

    /**
     * Parent Client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Earned points.
     */
    public function earnedPoints(): int
    {
        return $this->enabled
            ? $this->points
            : 0;
    }

    /**
     * Maximum possible points.
     */
    public function maximumPoints(): int
    {
        return $this->maximum_points;
    }

    /**
     * Percentage score for this control.
     */
    public function percentage(): float
    {
        if ($this->maximum_points === 0) {
            return 0;
        }

        return round(
            ($this->earnedPoints() / $this->maximum_points) * 100,
            1
        );
    }

    /**
     * Display status.
     */
    public function status(): string
    {
        return $this->enabled
            ? 'Complete'
            : 'Outstanding';
    }
}