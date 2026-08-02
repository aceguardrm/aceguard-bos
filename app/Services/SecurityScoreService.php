<?php

namespace App\Services;

use App\Models\Client;

class SecurityScoreService
{
    /**
     * Calculate the overall security score.
     */
    public function calculate(Client $client): array
    {
        $controls = [

            'Multi-Factor Authentication' => [
                'enabled' => true,
                'points' => 15,
            ],

            'Microsoft 365 Security' => [
                'enabled' => true,
                'points' => 15,
            ],

            'Endpoint Protection' => [
                'enabled' => true,
                'points' => 10,
            ],

            'Firewall' => [
                'enabled' => true,
                'points' => 10,
            ],

            'Email Security' => [
                'enabled' => true,
                'points' => 10,
            ],

            'Backups' => [
                'enabled' => true,
                'points' => 10,
            ],

            'Vulnerability Scanning' => [
                'enabled' => false,
                'points' => 10,
            ],

            'Staff Awareness Training' => [
                'enabled' => true,
                'points' => 10,
            ],

            'Cyber Essentials' => [
                'enabled' => false,
                'points' => 5,
            ],

            'Password Policy' => [
                'enabled' => true,
                'points' => 5,
            ],
        ];

        $score = 0;
        $maximum = 0;

        foreach ($controls as $control) {

            $maximum += $control['points'];

            if ($control['enabled']) {
                $score += $control['points'];
            }

        }

        return [

            'score' => round(($score / $maximum) * 100),

            'earned' => $score,

            'maximum' => $maximum,

            'controls' => $controls,

            'rating' => $this->rating(
                round(($score / $maximum) * 100)
            ),

        ];
    }

    /**
     * Convert score into rating.
     */
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