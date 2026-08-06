<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class SecurityControlSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::first();

        if (!$client) {
            $this->command?->warn(
                'No client exists. Create a client before running this seeder.'
            );

            return;
        }

        $controls = [
            [
                'category' => 'Identity',
                'control' => 'Multi-Factor Authentication',
                'enabled' => true,
                'points' => 15,
                'maximum_points' => 15,
            ],
            [
                'category' => 'Identity',
                'control' => 'Strong Password Policy',
                'enabled' => true,
                'points' => 5,
                'maximum_points' => 5,
            ],
            [
                'category' => 'Microsoft 365',
                'control' => 'Microsoft 365 Security',
                'enabled' => true,
                'points' => 15,
                'maximum_points' => 15,
            ],
            [
                'category' => 'Endpoint',
                'control' => 'Endpoint Protection',
                'enabled' => true,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'Network',
                'control' => 'Firewall Protection',
                'enabled' => true,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'Email',
                'control' => 'Email Security',
                'enabled' => true,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'Backup',
                'control' => 'Verified Backups',
                'enabled' => true,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'Risk',
                'control' => 'Vulnerability Scanning',
                'enabled' => false,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'People',
                'control' => 'Staff Awareness Training',
                'enabled' => true,
                'points' => 10,
                'maximum_points' => 10,
            ],
            [
                'category' => 'Compliance',
                'control' => 'Cyber Essentials',
                'enabled' => false,
                'points' => 5,
                'maximum_points' => 5,
            ],
        ];

        foreach ($controls as $control) {
            $client->securityControls()->updateOrCreate(
                [
                    'category' => $control['category'],
                    'control' => $control['control'],
                ],
                $control
            );
        }

        $this->command?->info(
            "Security controls created for {$client->company_name}."
        );
    }
}