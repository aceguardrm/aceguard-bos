<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPulseAssessment extends Model
{
    protected $fillable = [
        'client_id',
        'operations_score',
        'continuity_score',
        'documentation_score',
        'compliance_score',
        'technology_score',
        'readiness_score',
        'overall_score',
        'status',
        'notes',
        'assessed_at',
    ];

    protected $casts = [
        'operations_score' => 'integer',
        'continuity_score' => 'integer',
        'documentation_score' => 'integer',
        'compliance_score' => 'integer',
        'technology_score' => 'integer',
        'readiness_score' => 'integer',
        'overall_score' => 'integer',
        'assessed_at' => 'datetime',
    ];

    /**
     * Workspace/client that owns this assessment.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Calculate Business Health from the six assessment domains.
     */
    public function calculateOverallScore(): int
    {
        $scores = [
            $this->operations_score,
            $this->continuity_score,
            $this->documentation_score,
            $this->compliance_score,
            $this->technology_score,
            $this->readiness_score,
        ];

        return (int) round(array_sum($scores) / count($scores));
    }

    /**
     * Recalculate and persist the Business Health score.
     */
    public function refreshOverallScore(): int
    {
        $this->overall_score = $this->calculateOverallScore();
        $this->save();

        return $this->overall_score;
    }
}