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
         * Preserve the manually-entered project
         * progress until tasks actually exist.
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
}