<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExecutiveActionService
{
    /**
     * Convert live BOS intelligence into ranked, resolvable actions.
     */
    public function build(
        ?Client $client,
        Collection $projects,
        array $security,
        array $pulse
    ): array {
        $actions = collect();

        if (! $client) {
            $actions->push($this->action(
                domain: 'business',
                severity: 'high',
                title: 'Create the first organisation workspace',
                reason: 'BOS needs an organisation workspace before it can assess business and cyber risk.',
                impact: 'Unlocks executive intelligence',
                impactScore: 20,
                actionLabel: 'Create Workspace',
                actionUrl: route('clients.create')
            ));

            return $this->result($actions);
        }

        $this->addProjectActions($actions, $projects);
        $this->addSecurityActions($actions, $client, $security);
        $this->addBusinessActions($actions, $client, $pulse);

        return $this->result($actions);
    }

    private function addProjectActions(Collection $actions, Collection $projects): void
    {
        $projects
            ->filter(fn (Project $project) => in_array(
                $project->deliveryHealthKey(),
                ['attention', 'at_risk']
            ))
            ->each(function (Project $project) use ($actions) {
                $healthKey = $project->deliveryHealthKey();
                $reasons = [];

                if ($project->isProjectOverdue()) {
                    $reasons[] = abs($project->daysUntilDue()).' day(s) past the project deadline';
                }

                if ($project->overdueMilestoneCount() > 0) {
                    $reasons[] = $project->overdueMilestoneCount().' overdue milestone(s)';
                }

                if ($project->overdueTaskCount() > 0) {
                    $reasons[] = $project->overdueTaskCount().' overdue task(s)';
                }

                if ($project->incompleteHighPriorityTaskCount() > 0) {
                    $reasons[] = $project->incompleteHighPriorityTaskCount().' high-priority action(s) outstanding';
                }

                $daysUntilDue = $project->daysUntilDue();

                if (
                    empty($reasons)
                    && $daysUntilDue !== null
                    && $daysUntilDue >= 0
                    && $daysUntilDue <= 7
                ) {
                    $reasons[] = 'deadline falls within '.$daysUntilDue.' day(s) while delivery is incomplete';
                }

                $actions->push($this->action(
                    domain: 'projects',
                    severity: $healthKey === 'at_risk' ? 'critical' : 'high',
                    title: $project->name.' requires delivery action',
                    reason: ucfirst(implode('; ', $reasons) ?: $project->deliveryHealthDescription()),
                    impact: 'Delivery health '.$project->deliveryHealthScore().'/100',
                    impactScore: 100 - $project->deliveryHealthScore(),
                    actionLabel: 'Open Project',
                    actionUrl: route('projects.show', $project)
                ));
            });
    }

    private function addSecurityActions(
        Collection $actions,
        Client $client,
        array $security
    ): void {
        $controls = collect($security['controls'] ?? []);

        $controls
            ->where('enabled', false)
            ->each(function ($control) use ($actions, $client) {
                $isMicrosoft365 = Str::contains(
                    Str::lower((string) $control->category),
                    ['microsoft', '365']
                );
                $points = (int) $control->maximum_points;

                $actions->push($this->action(
                    domain: $isMicrosoft365 ? 'microsoft_365' : 'cyber',
                    severity: match (true) {
                        $points >= 15 => 'critical',
                        $points >= 10 => 'high',
                        $points >= 5 => 'medium',
                        default => 'low',
                    },
                    title: $control->control,
                    reason: $isMicrosoft365
                        ? 'A Microsoft 365 protection control is incomplete and may expose accounts, email or business data.'
                        : 'This cyber control is incomplete and leaves a measurable protection gap.',
                    impact: 'Recover up to '.$points.' security points',
                    impactScore: $points,
                    actionLabel: 'Resolve Control',
                    actionUrl: route('security.workspace', $client).'#control-'.$control->id
                ));
            });

        if (! $controls->contains(
            fn ($control) => Str::contains(
                Str::lower((string) $control->category),
                ['microsoft', '365']
            )
        )) {
            $actions->push($this->action(
                domain: 'microsoft_365',
                severity: 'medium',
                title: 'Assess Microsoft 365 security',
                reason: 'No Microsoft 365 security baseline is recorded for this workspace.',
                impact: 'Establishes cloud and email assurance',
                impactScore: 8,
                actionLabel: 'Open Cyber Centre',
                actionUrl: route('security.workspace', $client)
            ));
        }
    }

    private function addBusinessActions(
        Collection $actions,
        Client $client,
        array $pulse
    ): void {
        $assessmentExists = (bool) ($pulse['business_assessment']['exists'] ?? false);

        if (! $assessmentExists) {
            $actions->push($this->action(
                domain: 'business',
                severity: 'high',
                title: 'Complete the Business Health assessment',
                reason: 'Business Health is using legacy estimates, so management risk cannot yet be assessed from current evidence.',
                impact: 'Activates six-domain health intelligence',
                impactScore: 15,
                actionLabel: 'Start Assessment',
                actionUrl: route('business-pulse.workspace', $client)
            ));

            return;
        }

        foreach ($pulse['business_domains'] ?? [] as $key => $domain) {
            $score = (int) ($domain['score'] ?? 0);

            if ($score >= 70) {
                continue;
            }

            $label = $domain['label'] ?? Str::headline($key);

            $actions->push($this->action(
                domain: 'business',
                severity: match (true) {
                    $score < 40 => 'critical',
                    $score < 55 => 'high',
                    default => 'medium',
                },
                title: $label.' requires management attention',
                reason: $label.' is scoring '.$score.'% and is below the 70% BOS health threshold.',
                impact: 'Current health '.$score.'%',
                impactScore: 70 - $score,
                actionLabel: 'Review Assessment',
                actionUrl: route('business-pulse.workspace', $client).'#'.Str::slug($key, '_')
            ));
        }
    }

    private function action(
        string $domain,
        string $severity,
        string $title,
        string $reason,
        string $impact,
        int $impactScore,
        string $actionLabel,
        string $actionUrl
    ): array {
        return compact(
            'domain',
            'severity',
            'title',
            'reason',
            'impact',
            'impactScore',
            'actionLabel',
            'actionUrl'
        );
    }

    private function result(Collection $actions): array
    {
        $weights = [
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
        ];

        $ranked = $actions
            ->sortByDesc(fn (array $action) =>
                (($weights[$action['severity']] ?? 0) * 1000)
                + $action['impactScore']
            )
            ->values();

        $domains = collect([
            'projects' => 'Projects',
            'cyber' => 'Cyber Security',
            'business' => 'Business Health',
            'microsoft_365' => 'Microsoft 365',
        ])->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'count' => $ranked->where('domain', $key)->count(),
        ])->values();

        return [
            'actions' => $ranked,
            'total' => $ranked->count(),
            'critical' => $ranked->where('severity', 'critical')->count(),
            'high' => $ranked->where('severity', 'high')->count(),
            'domains' => $domains,
        ];
    }
}
