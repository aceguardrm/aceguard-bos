<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'owner_name',
        'owner_email',
        'status',
        'priority',
        'start_date',
        'due_date',
        'progress',
        'source',
        'source_reference',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this
            ->hasMany(ProjectTask::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }


    /*
    |--------------------------------------------------------------------------
    | Task Intelligence
    |--------------------------------------------------------------------------
    */

    public function taskCount(): int
    {
        return $this->tasks()->count();
    }

    public function completedTaskCount(): int
    {
        return $this
            ->tasks()
            ->where('status', 'completed')
            ->count();
    }

    public function overdueTaskCount(): int
    {
        return $this
            ->tasks()
            ->whereNot('status', 'completed')
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '<',
                now()->toDateString()
            )
            ->count();
    }

    public function overdueMilestoneCount(): int
    {
        return $this
            ->tasks()
            ->where('is_milestone', true)
            ->whereNot('status', 'completed')
            ->whereNotNull('due_date')
            ->whereDate(
                'due_date',
                '<',
                now()->toDateString()
            )
            ->count();
    }

    public function incompleteHighPriorityTaskCount(): int
    {
        return $this
            ->tasks()
            ->whereIn(
                'priority',
                ['high', 'critical']
            )
            ->whereNot('status', 'completed')
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Automatic Project Progress
    |--------------------------------------------------------------------------
    */

    public function calculateTaskProgress(): int
    {
        $totalTasks = $this->taskCount();

        if ($totalTasks === 0) {
            return (int) $this->progress;
        }

        $completedTasks =
            $this->completedTaskCount();

        return (int) round(
            ($completedTasks / $totalTasks) * 100
        );
    }

    public function syncProgressFromTasks(): void
    {
        $totalTasks = $this->taskCount();

        /*
         * Preserve manually-entered project progress
         * until tasks actually exist.
         */
        if ($totalTasks === 0) {
            return;
        }

        $progress =
            $this->calculateTaskProgress();

        if ((int) $this->progress !== $progress) {
            $this->update([
                'progress' => $progress,
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delivery Health Intelligence
    |--------------------------------------------------------------------------
    */

    public function isProjectOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->copy()->startOfDay()->lt(now()->startOfDay())
            && !in_array(
                $this->status,
                ['completed', 'cancelled']
            );
    }

    public function daysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()
            ->startOfDay()
            ->diffInDays(
                $this->due_date->copy()->startOfDay(),
                false
            );
    }

    public function deliveryHealthKey(): string
    {
        /*
         * Completed projects are always considered healthy.
         */
        if ($this->status === 'completed') {
            return 'healthy';
        }

        /*
         * Cancelled projects are not active delivery risks.
         */
        if ($this->status === 'cancelled') {
            return 'inactive';
        }

        /*
         * Immediate project-level risk.
         */
        if ($this->isProjectOverdue()) {
            return 'at_risk';
        }

        /*
         * Overdue milestones indicate significant delivery risk.
         */
        if ($this->overdueMilestoneCount() > 0) {
            return 'at_risk';
        }

        /*
         * Multiple overdue tasks indicate material delivery risk.
         */
        if ($this->overdueTaskCount() >= 2) {
            return 'at_risk';
        }

        /*
         * A critical outstanding task should trigger attention.
         */
        $criticalOutstanding =
            $this
                ->tasks()
                ->where('priority', 'critical')
                ->whereNot('status', 'completed')
                ->exists();

        if ($criticalOutstanding) {
            return 'attention';
        }

        /*
         * Any overdue task should trigger attention.
         */
        if ($this->overdueTaskCount() > 0) {
            return 'attention';
        }

        /*
         * Approaching deadline with incomplete delivery.
         */
        $daysUntilDue =
            $this->daysUntilDue();

        if (
            $daysUntilDue !== null
            && $daysUntilDue >= 0
            && $daysUntilDue <= 7
            && (int) $this->progress < 100
        ) {
            return 'attention';
        }

        /*
         * High-priority incomplete work also needs executive attention.
         */
        if (
            $this->incompleteHighPriorityTaskCount() > 0
            && (int) $this->progress < 75
        ) {
            return 'attention';
        }

        return 'healthy';
    }

    public function deliveryHealthLabel(): string
    {
        return match ($this->deliveryHealthKey()) {
            'healthy' => 'Healthy',
            'attention' => 'Attention',
            'at_risk' => 'At Risk',
            'inactive' => 'Inactive',
            default => 'Unknown',
        };
    }

    public function deliveryHealthDescription(): string
    {
        return match ($this->deliveryHealthKey()) {
            'healthy' =>
                'Delivery is progressing without material risk indicators.',

            'attention' =>
                'Delivery requires attention due to deadlines, priority work or outstanding actions.',

            'at_risk' =>
                'Delivery is at risk due to overdue work, overdue milestones or project delay.',

            'inactive' =>
                'This project is no longer active.',

            default =>
                'Delivery health could not be determined.',
        };
    }

    public function deliveryHealthScore(): int
    {
        return match ($this->deliveryHealthKey()) {
            'healthy' => 100,
            'attention' => 65,
            'at_risk' => 30,
            'inactive' => 0,
            default => 50,
        };
    }
}