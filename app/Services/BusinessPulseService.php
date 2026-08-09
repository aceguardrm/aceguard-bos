<?php

namespace App\Services;

use App\Models\Client;

class BusinessPulseService
{
    public function calculate(Client $client): array
    {
        /*
        |--------------------------------------------------------------------------
        | Security Intelligence
        |--------------------------------------------------------------------------
        */

        $security = app(SecurityScoreService::class)
            ->calculate($client);

        $securityScore = (int) ($security['score'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Business Health Intelligence
        |--------------------------------------------------------------------------
        |
        | Prefer the real database-backed Business Pulse assessment.
        | If one does not yet exist, fall back to the existing
        | BusinessHealthService so older workspaces continue to function.
        |
        */

        $assessment = $client->businessPulseAssessment;

        if ($assessment) {
            $businessScore = (int) $assessment->overall_score;

            $businessHealth = [
                'overall' => $businessScore,
                'source' => 'assessment',

                'domains' => [
                    'operations' => [
                        'label' => 'Operations',
                        'score' => (int) $assessment->operations_score,
                    ],

                    'continuity' => [
                        'label' => 'Continuity',
                        'score' => (int) $assessment->continuity_score,
                    ],

                    'documentation' => [
                        'label' => 'Documentation',
                        'score' => (int) $assessment->documentation_score,
                    ],

                    'compliance' => [
                        'label' => 'Compliance',
                        'score' => (int) $assessment->compliance_score,
                    ],

                    'technology' => [
                        'label' => 'Technology',
                        'score' => (int) $assessment->technology_score,
                    ],

                    'readiness' => [
                        'label' => 'Readiness',
                        'score' => (int) $assessment->readiness_score,
                    ],
                ],

                'status' => $assessment->status,
                'assessed_at' => $assessment->assessed_at,
            ];
        } else {
            $businessHealth = app(BusinessHealthService::class)
                ->calculate($client);

            $businessScore = (int) (
                $businessHealth['overall'] ?? 0
            );

            $businessHealth['source'] = 'legacy';
            $businessHealth['domains'] = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Business Pulse™ Calculation
        |--------------------------------------------------------------------------
        |
        | Security       = 60%
        | Business Health = 40%
        |
        */

        $pulseScore = (int) round(
            ($securityScore * 0.60)
            + ($businessScore * 0.40)
        );

        /*
        |--------------------------------------------------------------------------
        | Executive Priorities
        |--------------------------------------------------------------------------
        */

        $priorities = $this->buildPriorities(
            $security,
            $businessHealth
        );

        return [
            'score' => $pulseScore,

            'rating' => $this->rating($pulseScore),

            'components' => [
                'security' => [
                    'label' => 'Security',
                    'score' => $securityScore,
                    'weight' => 60,
                ],

                'business_health' => [
                    'label' => 'Business Health',
                    'score' => $businessScore,
                    'weight' => 40,
                    'source' => $businessHealth['source'] ?? 'unknown',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Business Health Domain Intelligence
            |--------------------------------------------------------------------------
            */

            'business_domains' => $businessHealth['domains'] ?? [],

            'business_assessment' => [
                'exists' => (bool) $assessment,
                'status' => $assessment?->status,
                'assessed_at' => $assessment?->assessed_at,
            ],

            /*
            |--------------------------------------------------------------------------
            | Executive Intelligence
            |--------------------------------------------------------------------------
            */

            'priorities' => $priorities,

            'priority_count' => count($priorities),

            'summary' => $this->summary(
                $pulseScore,
                count($priorities)
            ),
        ];
    }

    /**
     * Build the executive priority list.
     */
    private function buildPriorities(
        array $security,
        array $businessHealth
    ): array {
        $priorities = [];

        /*
        |--------------------------------------------------------------------------
        | Security Priorities
        |--------------------------------------------------------------------------
        */

        foreach ($security['controls'] ?? [] as $control) {
            if ($control->enabled) {
                continue;
            }

            $priorities[] = [
                'type' => 'security',

                'severity' => $this->severityFromPoints(
                    (int) $control->maximum_points
                ),

                'title' => $control->control,

                'message' => sprintf(
                    'Complete this security control to recover %d points.',
                    $control->maximum_points
                ),

                'impact' => (int) $control->maximum_points,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Overall Business Health Priority
        |--------------------------------------------------------------------------
        */

        $businessScore = (int) (
            $businessHealth['overall'] ?? 100
        );

        if ($businessScore < 70) {
            $priorities[] = [
                'type' => 'business',
                'severity' => 'high',
                'title' => 'Business Health Requires Attention',
                'message' => sprintf(
                    'The current Business Health score is %d%%.',
                    $businessScore
                ),
                'impact' => 10,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Business Domain Priorities
        |--------------------------------------------------------------------------
        |
        | Any domain below 70% becomes an executive priority.
        |
        */

        foreach ($businessHealth['domains'] ?? [] as $domain) {
            $domainScore = (int) ($domain['score'] ?? 0);

            if ($domainScore >= 70) {
                continue;
            }

            $label = $domain['label'] ?? 'Business Domain';

            $priorities[] = [
                'type' => 'business',

                'severity' => $this->businessDomainSeverity(
                    $domainScore
                ),

                'title' => "{$label} Requires Attention",

                'message' => sprintf(
                    '%s is currently scoring %d%% and should be reviewed.',
                    $label,
                    $domainScore
                ),

                'impact' => $this->businessDomainImpact(
                    $domainScore
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Highest Severity First
        |--------------------------------------------------------------------------
        */

        usort(
            $priorities,
            fn (array $a, array $b) =>
                $this->severityWeight($b['severity'])
                <=> $this->severityWeight($a['severity'])
        );

        return array_slice($priorities, 0, 5);
    }

    /**
     * Determine security priority severity from available points.
     */
    private function severityFromPoints(int $points): string
    {
        return match (true) {
            $points >= 15 => 'critical',
            $points >= 10 => 'high',
            $points >= 5 => 'medium',
            default => 'low',
        };
    }

    /**
     * Determine business domain severity from score.
     */
    private function businessDomainSeverity(int $score): string
    {
        return match (true) {
            $score < 40 => 'critical',
            $score < 55 => 'high',
            $score < 70 => 'medium',
            default => 'low',
        };
    }

    /**
     * Calculate a simple executive impact indicator.
     */
    private function businessDomainImpact(int $score): int
    {
        return match (true) {
            $score < 40 => 15,
            $score < 55 => 10,
            $score < 70 => 5,
            default => 0,
        };
    }

    /**
     * Convert severity into a sortable weight.
     */
    private function severityWeight(string $severity): int
    {
        return match ($severity) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }

    /**
     * Business Pulse™ rating.
     */
    private function rating(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Healthy',
            $score >= 60 => 'Watch',
            $score >= 40 => 'At Risk',
            default => 'Critical',
        };
    }

    /**
     * Executive summary.
     */
    private function summary(
        int $score,
        int $priorityCount
    ): string {
        if ($priorityCount === 0) {
            return 'No immediate business priorities require attention.';
        }

        if ($score >= 90) {
            return sprintf(
                'Business performance is strong, with %d priority item%s remaining.',
                $priorityCount,
                $priorityCount === 1 ? '' : 's'
            );
        }

        if ($score >= 75) {
            return sprintf(
                'Business health is stable, but %d priority item%s should be reviewed.',
                $priorityCount,
                $priorityCount === 1 ? '' : 's'
            );
        }

        return sprintf(
            '%d priority item%s require management attention.',
            $priorityCount,
            $priorityCount === 1 ? '' : 's'
        );
    }
}