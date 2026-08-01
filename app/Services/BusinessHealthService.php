<?php

namespace App\Services;

use App\Models\Client;

class BusinessHealthService
{
    public function calculate(Client $client)
    {
        $scores = [

            'security' => 94,

            'finance' => 80,

            'documents' => 90,

            'tasks' => 100,

            'compliance' => 85,

        ];

        $overall = round(array_sum($scores) / count($scores));

        return [

            'overall' => $overall,

            'scores' => $scores

        ];
    }
}
