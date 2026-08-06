<?php

namespace App\Services;

use App\Models\Client;

class SecurityScoreService
{
    /**
     * Calculate the client's security score from stored controls.
     */
    public function calculate(Client $client): array
    {
        $controls = $client->securityControls()
            ->orderBy('category')
            ->orderBy('control')
            ->get();

        if ($controls->isEmpty()) {
            return [
                'score' => 0,
                'earned' => 0,
                'maximum' => 0,
                'rating' => 'Not assessed',
                'controls' => collect(),
                'completed' => 0,
                'outstanding' => 0,
                'recommendations' => [
                    'Create the client’s first security assessment.',
                ],
            ];
        }

        $earned = $controls->sum(
            fn ($control) => $control->enabled
                ? $control->points
                : 0
        );

        $maximum = $controls->sum('maximum_points');

        $score = $maximum > 0
            ? (int) round(($earned / $maximum) * 100)
            : 0;

        $recommendations = $controls
            ->where('enabled', false)
            ->sortByDesc('maximum_points')
            ->take(3)
            ->map(
                fn ($control) =>
                    "Complete {$control->control} "
                    . "(+{$control->maximum_points} points)"
            )
            ->values()
            ->all();

        return [
            'score' => $score,
            'earned' => $earned,
            'maximum' => $maximum,
            'rating' => $this->rating($score),
            'controls' => $controls,
            'completed' => $controls->where('enabled', true)->count(),
            'outstanding' => $controls->where('enabled', false)->count(),
            'recommendations' => $recommendations,
        ];
    }

    private function rating(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Poor',
            default => 'Critical',
        };
    }
}