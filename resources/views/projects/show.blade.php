@extends('layouts.aceguard')

@section('title', $project->name)

@section('page-title', 'Project Workspace')

@section(
    'page-subtitle',
    'Delivery oversight, ownership and progress for ' . $project->name . '.'
)

@section('content')

@php

    $statusLabel = match ($project->status) {
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $project->status ?? '')),
    };

    $statusClass = match ($project->status) {
        'planned' => 'planned',
        'in_progress' => 'progress',
        'on_hold' => 'hold',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        default => 'planned',
    };

    $priorityClass = match ($project->priority) {
        'critical' => 'critical',
        'high' => 'high',
        'medium' => 'medium',
        default => 'low',
    };

    $progress = max(
        0,
        min(
            100,
            (int) $project->progress
        )
    );

    $isOverdue =
        $project->due_date
        && $project->due_date->copy()->startOfDay()->lt(now()->startOfDay())
        && !in_array(
            $project->status,
            ['completed', 'cancelled']
        );

    $tasks = $project->tasks ?? collect();

    $taskCount = $tasks->count();

    $completedTaskCount = $tasks
        ->where('status', 'completed')
        ->count();

    $milestoneCount = $tasks
        ->where('is_milestone', true)
        ->count();

    $overdueTaskCount = $tasks
        ->filter(function ($task) {
            return $task->due_date
                && $task->due_date->copy()->startOfDay()->lt(now()->startOfDay())
                && $task->status !== 'completed';
        })
        ->count();

@endphp


<div class="ag-project-workspace">

    {{-- ================================================================
        HERO
    ================================================================ --}}

    <section class="ag-project-hero">

        <div>

            <span class="ag-project-kicker">
                AceGuard BOS · Project Workspace
            </span>

            <div class="ag-project-title-row">

                <h2>
                    {{ $project->name }}
                </h2>

                <span
                    class="
                        ag-status-badge
                        ag-status-badge--{{ $statusClass }}
                    "
                >
                    {{ $statusLabel }}
                </span>

                <span
                    class="
                        ag-priority-badge
                        ag-priority-badge--{{ $priorityClass }}
                    "
                >
                    {{ ucfirst($project->priority) }}
                </span>

            </div>

            <div class="ag-project-hero-meta">

                <span>
                    <i class="fas fa-building"></i>

                    {{
                        $project->client->company_name
                        ?? 'Unknown Workspace'
                    }}
                </span>

                <span>
                    <i class="fas fa-user"></i>

                    {{
                        $project->owner_name
                        ?: 'Unassigned'
                    }}
                </span>

                @if($project->due_date)

                    <span class="{{ $isOverdue ? 'ag-overdue-text' : '' }}">
                        <i class="fas fa-calendar-days"></i>

                        Due
                        {{ $project->due_date->format('d M Y') }}
                    </span>

                @endif

            </div>

        </div>


        <div class="ag-project-hero-actions">

            <a
                href="{{ route('projects.edit', $project) }}"
                class="
                    ag-project-button
                    ag-project-button--secondary
                "
            >
                <i class="fas fa-pen"></i>

                Edit Project
            </a>

            <a
                href="{{ route('projects.index') }}"
                class="
                    ag-project-button
                    ag-project-button--light
                "
            >
                <i class="fas fa-arrow-left"></i>

                All Projects
            </a>

        </div>

    </section>


    {{-- ================================================================
        SUCCESS MESSAGE
    ================================================================ --}}

    @if(session('success'))

        <div class="ag-project-alert">

            <i class="fas fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    @if($errors->any())

        <div class="ag-project-alert ag-project-alert--danger">

            <i class="fas fa-triangle-exclamation"></i>

            <div>

                <strong>
                    Please review the task information.
                </strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        </div>

    @endif


    {{-- ================================================================
        SUMMARY CARDS
    ================================================================ --}}

    <section class="ag-project-summary-grid">

        <article class="ag-project-summary-card">

            <div class="ag-project-summary-icon ag-project-summary-icon--blue">
                <i class="fas fa-chart-line"></i>
            </div>

            <div>
                <span>Progress</span>

                <strong>
                    {{ $progress }}%
                </strong>

                <small>
                    Current delivery completion
                </small>
            </div>

        </article>


        <article class="ag-project-summary-card">

            <div class="ag-project-summary-icon ag-project-summary-icon--green">
                <i class="fas fa-user-check"></i>
            </div>

            <div>
                <span>Owner</span>

                <strong class="ag-project-summary-card__text">
                    {{
                        $project->owner_name
                        ?: 'Unassigned'
                    }}
                </strong>

                <small>
                    Accountable project owner
                </small>
            </div>

        </article>


        <article class="ag-project-summary-card">

            <div class="ag-project-summary-icon ag-project-summary-icon--amber">
                <i class="fas fa-flag"></i>
            </div>

            <div>
                <span>Priority</span>

                <strong class="ag-project-summary-card__text">
                    {{ ucfirst($project->priority) }}
                </strong>

                <small>
                    Delivery importance
                </small>
            </div>

        </article>


        <article class="ag-project-summary-card">

            <div class="ag-project-summary-icon ag-project-summary-icon--purple">
                <i class="fas fa-calendar-check"></i>
            </div>

            <div>
                <span>Due Date</span>

                <strong class="ag-project-summary-card__text {{ $isOverdue ? 'ag-overdue-text' : '' }}">
                    {{
                        $project->due_date
                            ? $project->due_date->format('d M Y')
                            : 'Not Set'
                    }}
                </strong>

                <small class="{{ $isOverdue ? 'ag-overdue-text' : '' }}">
                    {{
                        $isOverdue
                            ? 'Project is overdue'
                            : 'Delivery deadline'
                    }}
                </small>
            </div>

        </article>

    </section>


    {{-- ================================================================
        DELIVERY PROGRESS
    ================================================================ --}}

    <section class="ag-project-progress-panel">

        <div class="ag-project-panel-heading">

            <div>

                <span class="ag-project-section-label">
                    Delivery Control
                </span>

                <h3>
                    Project Progress
                </h3>

                <p>
                    Current completion position for this project.
                </p>

            </div>


            <strong class="ag-project-progress-value">
                {{ $progress }}%
            </strong>

        </div>


        <div class="ag-project-progress-track">

            <div
                class="ag-project-progress-fill"
                style="
                    width:
                        {{ $progress }}%;
                "
            ></div>

        </div>


        <div class="ag-project-progress-scale">
            <span>0%</span>
            <span>25%</span>
            <span>50%</span>
            <span>75%</span>
            <span>100%</span>
        </div>

    </section>


    {{-- ================================================================
        DELIVERY HEALTH
    ================================================================ --}}

    @php
        $deliveryHealthKey = $project->deliveryHealthKey();
        $deliveryHealthLabel = $project->deliveryHealthLabel();
        $deliveryHealthScore = $project->deliveryHealthScore();
        $deliveryHealthDescription = $project->deliveryHealthDescription();

        $deliveryHealthClass = match ($deliveryHealthKey) {
            'healthy' => 'healthy',
            'attention' => 'attention',
            'at_risk' => 'risk',
            'inactive' => 'inactive',
            default => 'neutral',
        };

        $deliveryHealthIcon = match ($deliveryHealthKey) {
            'healthy' => 'fa-circle-check',
            'attention' => 'fa-triangle-exclamation',
            'at_risk' => 'fa-shield-halved',
            'inactive' => 'fa-circle-minus',
            default => 'fa-circle-info',
        };

        $deliveryOverdueTasks = $project->overdueTaskCount();
        $deliveryOverdueMilestones = $project->overdueMilestoneCount();
        $deliveryHighPriorityTasks = $project->incompleteHighPriorityTaskCount();
        $deliveryDaysUntilDue = $project->daysUntilDue();
    @endphp

    <section class="ag-delivery-health ag-delivery-health--{{ $deliveryHealthClass }}">

        <div class="ag-delivery-health__header">

            <div class="ag-delivery-health__title-group">

                <div class="ag-delivery-health__icon">
                    <i class="fa-solid {{ $deliveryHealthIcon }}"></i>
                </div>

                <div>
                    <span class="ag-project-section-label">
                        BOS Intelligence
                    </span>

                    <h3>Delivery Health</h3>

                    <p>
                        Automated assessment of project delivery risk and execution health.
                    </p>
                </div>

            </div>

            <div class="ag-delivery-health__status">

                <span class="ag-delivery-health__badge">
                    {{ $deliveryHealthLabel }}
                </span>

                <div class="ag-delivery-health__score">
                    <strong>{{ $deliveryHealthScore }}</strong>
                    <span>/ 100</span>
                </div>

            </div>

        </div>

        <div class="ag-delivery-health__body">

            <div class="ag-delivery-health__assessment">

                <span>Executive Assessment</span>

                <p>
                    {{ $deliveryHealthDescription }}
                </p>

            </div>

            <div class="ag-delivery-health__metrics">

                <div class="ag-delivery-health__metric">
                    <span>Overdue Tasks</span>
                    <strong>{{ $deliveryOverdueTasks }}</strong>
                </div>

                <div class="ag-delivery-health__metric">
                    <span>Overdue Milestones</span>
                    <strong>{{ $deliveryOverdueMilestones }}</strong>
                </div>

                <div class="ag-delivery-health__metric">
                    <span>Priority Actions</span>
                    <strong>{{ $deliveryHighPriorityTasks }}</strong>
                </div>

                <div class="ag-delivery-health__metric">
                    <span>Deadline Position</span>

                    <strong>
                        @if ($deliveryDaysUntilDue === null)
                            No date
                        @elseif ($deliveryDaysUntilDue < 0)
                            {{ abs($deliveryDaysUntilDue) }}d overdue
                        @elseif ($deliveryDaysUntilDue === 0)
                            Due today
                        @else
                            {{ $deliveryDaysUntilDue }}d remaining
                        @endif
                    </strong>
                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
        TASKS & MILESTONES
    ================================================================ --}}

    <section class="ag-task-workspace">

        <div class="ag-task-heading">

            <div>
                <span class="ag-project-section-label">
                    Delivery Execution
                </span>

                <h3>
                    Tasks & Milestones
                </h3>

                <p>
                    Break the project into accountable delivery actions and key milestones.
                </p>
            </div>

            <div class="ag-task-heading-status">
                <strong>{{ $completedTaskCount }} / {{ $taskCount }}</strong>
                <span>Completed</span>
            </div>

        </div>


        <div class="ag-task-summary-grid">

            <article class="ag-task-summary-card">
                <div class="ag-task-summary-icon ag-task-summary-icon--blue">
                    <i class="fas fa-list-check"></i>
                </div>
                <div>
                    <span>Total Tasks</span>
                    <strong>{{ $taskCount }}</strong>
                    <small>Delivery actions</small>
                </div>
            </article>

            <article class="ag-task-summary-card">
                <div class="ag-task-summary-icon ag-task-summary-icon--green">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div>
                    <span>Completed</span>
                    <strong>{{ $completedTaskCount }}</strong>
                    <small>Finished actions</small>
                </div>
            </article>

            <article class="ag-task-summary-card">
                <div class="ag-task-summary-icon ag-task-summary-icon--purple">
                    <i class="fas fa-diamond"></i>
                </div>
                <div>
                    <span>Milestones</span>
                    <strong>{{ $milestoneCount }}</strong>
                    <small>Key checkpoints</small>
                </div>
            </article>

            <article class="ag-task-summary-card">
                <div class="ag-task-summary-icon ag-task-summary-icon--red">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <span>Overdue</span>
                    <strong>{{ $overdueTaskCount }}</strong>
                    <small>Need attention</small>
                </div>
            </article>

        </div>


        <div class="ag-task-layout">

            <div class="ag-task-register">

                <div class="ag-task-subheading">
                    <div>
                        <span class="ag-project-section-label">
                            Project Register
                        </span>
                        <h4>Delivery Actions</h4>
                    </div>

                    <span class="ag-task-count-badge">
                        {{ $taskCount }}
                    </span>
                </div>


                @forelse($tasks as $task)

                    @php
                        $taskStatusLabel = match ($task->status) {
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'blocked' => 'Blocked',
                            'completed' => 'Completed',
                            default => ucfirst(str_replace('_', ' ', $task->status ?? '')),
                        };

                        $taskStatusClass = match ($task->status) {
                            'pending' => 'pending',
                            'in_progress' => 'progress',
                            'blocked' => 'blocked',
                            'completed' => 'completed',
                            default => 'pending',
                        };

                        $taskPriorityClass = match ($task->priority) {
                            'critical' => 'critical',
                            'high' => 'high',
                            'medium' => 'medium',
                            default => 'low',
                        };

                        $taskIsOverdue =
                            $task->due_date
                            && $task->due_date->copy()->startOfDay()->lt(now()->startOfDay())
                            && $task->status !== 'completed';
                    @endphp


                    <article class="ag-task-card {{ $task->status === 'completed' ? 'ag-task-card--completed' : '' }} {{ $taskIsOverdue ? 'ag-task-card--overdue' : '' }}">

                        <div class="ag-task-card-main">

                            <form
                                method="POST"
                                action="{{ route('project-tasks.toggle', [$project, $task]) }}"
                                class="ag-task-toggle-form"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="ag-task-toggle {{ $task->status === 'completed' ? 'ag-task-toggle--completed' : '' }}"
                                    title="{{ $task->status === 'completed' ? 'Reopen task' : 'Mark task complete' }}"
                                >
                                    <i class="fas {{ $task->status === 'completed' ? 'fa-check' : 'fa-circle' }}"></i>
                                </button>
                            </form>


                            <div class="ag-task-card-content">

                                <div class="ag-task-badges">

                                    @if($task->is_milestone)
                                        <span class="ag-task-milestone">
                                            <i class="fas fa-diamond"></i>
                                            Milestone
                                        </span>
                                    @endif

                                    <span class="ag-task-status ag-task-status--{{ $taskStatusClass }}">
                                        {{ $taskStatusLabel }}
                                    </span>

                                    <span class="ag-priority-badge ag-priority-badge--{{ $taskPriorityClass }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>

                                    @if($taskIsOverdue)
                                        <span class="ag-task-overdue">
                                            Overdue
                                        </span>
                                    @endif

                                </div>

                                <h5>{{ $task->title }}</h5>

                                @if($task->description)
                                    <p class="ag-task-description">
                                        {{ $task->description }}
                                    </p>
                                @endif

                                <div class="ag-task-meta">
                                    <span>
                                        <i class="fas fa-user"></i>
                                        {{ $task->owner_name ?: 'Unassigned' }}
                                    </span>

                                    <span class="{{ $taskIsOverdue ? 'ag-overdue-text' : '' }}">
                                        <i class="fas fa-calendar-days"></i>
                                        {{
                                            $task->due_date
                                                ? 'Due ' . $task->due_date->format('d M Y')
                                                : 'No due date'
                                        }}
                                    </span>

                                    @if($task->completed_at)
                                        <span>
                                            <i class="fas fa-circle-check"></i>
                                            Completed {{ $task->completed_at->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>

                                @if($task->notes)
                                    <div class="ag-task-note">
                                        <i class="fas fa-note-sticky"></i>
                                        <span>{{ $task->notes }}</span>
                                    </div>
                                @endif

                            </div>

                        </div>


                        <div class="ag-task-actions">

                            <details class="ag-task-edit">

                                <summary>
                                    <i class="fas fa-pen"></i>
                                    Edit
                                </summary>

                                <form
                                    method="POST"
                                    action="{{ route('project-tasks.update', [$project, $task]) }}"
                                    class="ag-task-edit-form"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <div class="ag-task-form-grid">

                                        <div class="ag-task-field ag-task-field--wide">
                                            <label for="task-title-{{ $task->id }}">
                                                Task Title *
                                            </label>
                                            <input
                                                id="task-title-{{ $task->id }}"
                                                type="text"
                                                name="title"
                                                value="{{ $task->title }}"
                                                required
                                            >
                                        </div>

                                        <div class="ag-task-field">
                                            <label for="task-owner-{{ $task->id }}">
                                                Owner
                                            </label>
                                            <input
                                                id="task-owner-{{ $task->id }}"
                                                type="text"
                                                name="owner_name"
                                                value="{{ $task->owner_name }}"
                                            >
                                        </div>

                                        <div class="ag-task-field">
                                            <label for="task-owner-email-{{ $task->id }}">
                                                Owner Email
                                            </label>
                                            <input
                                                id="task-owner-email-{{ $task->id }}"
                                                type="email"
                                                name="owner_email"
                                                value="{{ $task->owner_email }}"
                                            >
                                        </div>

                                        <div class="ag-task-field">
                                            <label for="task-status-{{ $task->id }}">
                                                Status *
                                            </label>
                                            <select
                                                id="task-status-{{ $task->id }}"
                                                name="status"
                                                required
                                            >
                                                <option value="pending" @selected($task->status === 'pending')>Pending</option>
                                                <option value="in_progress" @selected($task->status === 'in_progress')>In Progress</option>
                                                <option value="blocked" @selected($task->status === 'blocked')>Blocked</option>
                                                <option value="completed" @selected($task->status === 'completed')>Completed</option>
                                            </select>
                                        </div>

                                        <div class="ag-task-field">
                                            <label for="task-priority-{{ $task->id }}">
                                                Priority *
                                            </label>
                                            <select
                                                id="task-priority-{{ $task->id }}"
                                                name="priority"
                                                required
                                            >
                                                <option value="low" @selected($task->priority === 'low')>Low</option>
                                                <option value="medium" @selected($task->priority === 'medium')>Medium</option>
                                                <option value="high" @selected($task->priority === 'high')>High</option>
                                                <option value="critical" @selected($task->priority === 'critical')>Critical</option>
                                            </select>
                                        </div>

                                        <div class="ag-task-field">
                                            <label for="task-due-{{ $task->id }}">
                                                Due Date
                                            </label>
                                            <input
                                                id="task-due-{{ $task->id }}"
                                                type="date"
                                                name="due_date"
                                                value="{{ $task->due_date?->format('Y-m-d') }}"
                                            >
                                        </div>

                                        <div class="ag-task-field ag-task-field--checkbox">
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    name="is_milestone"
                                                    value="1"
                                                    @checked($task->is_milestone)
                                                >
                                                Milestone
                                            </label>
                                        </div>

                                        <div class="ag-task-field ag-task-field--wide">
                                            <label for="task-description-{{ $task->id }}">
                                                Description
                                            </label>
                                            <textarea
                                                id="task-description-{{ $task->id }}"
                                                name="description"
                                                rows="3"
                                            >{{ $task->description }}</textarea>
                                        </div>

                                        <div class="ag-task-field ag-task-field--wide">
                                            <label for="task-notes-{{ $task->id }}">
                                                Internal Notes
                                            </label>
                                            <textarea
                                                id="task-notes-{{ $task->id }}"
                                                name="notes"
                                                rows="3"
                                            >{{ $task->notes }}</textarea>
                                        </div>

                                    </div>

                                    <button
                                        type="submit"
                                        class="ag-task-primary-button"
                                    >
                                        <i class="fas fa-floppy-disk"></i>
                                        Save Changes
                                    </button>

                                </form>

                            </details>


                            <form
                                method="POST"
                                action="{{ route('project-tasks.destroy', [$project, $task]) }}"
                                onsubmit="return confirm('Are you sure you want to permanently delete this task?');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="ag-task-delete-button"
                                >
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </button>
                            </form>

                        </div>

                    </article>

                @empty

                    <div class="ag-task-empty">
                        <div class="ag-task-empty-icon">
                            <i class="fas fa-list-check"></i>
                        </div>

                        <h4>No tasks yet</h4>

                        <p>
                            Add the first delivery action or milestone using the form beside this register.
                        </p>
                    </div>

                @endforelse

            </div>


            <aside class="ag-task-create-panel">

                <div class="ag-task-create-heading">
                    <span class="ag-project-section-label">
                        New Delivery Action
                    </span>

                    <h4>Add Task</h4>

                    <p>
                        Create an accountable task or milestone for this project.
                    </p>
                </div>


                <form
                    method="POST"
                    action="{{ route('project-tasks.store', $project) }}"
                    class="ag-task-create-form"
                >
                    @csrf

                    <div class="ag-task-field">
                        <label for="new-task-title">Task Title *</label>
                        <input
                            id="new-task-title"
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="e.g. Configure Conditional Access"
                            required
                        >
                    </div>

                    <div class="ag-task-field">
                        <label for="new-task-description">Description</label>
                        <textarea
                            id="new-task-description"
                            name="description"
                            rows="3"
                            placeholder="What needs to be delivered?"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="ag-task-field">
                        <label for="new-task-owner">Owner</label>
                        <input
                            id="new-task-owner"
                            type="text"
                            name="owner_name"
                            value="{{ old('owner_name', $project->owner_name) }}"
                            placeholder="Owner name"
                        >
                    </div>

                    <div class="ag-task-field">
                        <label for="new-task-owner-email">Owner Email</label>
                        <input
                            id="new-task-owner-email"
                            type="email"
                            name="owner_email"
                            value="{{ old('owner_email', $project->owner_email) }}"
                            placeholder="owner@example.com"
                        >
                    </div>

                    <div class="ag-task-form-grid ag-task-form-grid--create">

                        <div class="ag-task-field">
                            <label for="new-task-status">Status *</label>
                            <select
                                id="new-task-status"
                                name="status"
                                required
                            >
                                <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                                <option value="in_progress" @selected(old('status') === 'in_progress')>In Progress</option>
                                <option value="blocked" @selected(old('status') === 'blocked')>Blocked</option>
                                <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                            </select>
                        </div>

                        <div class="ag-task-field">
                            <label for="new-task-priority">Priority *</label>
                            <select
                                id="new-task-priority"
                                name="priority"
                                required
                            >
                                <option value="low" @selected(old('priority') === 'low')>Low</option>
                                <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                                <option value="high" @selected(old('priority') === 'high')>High</option>
                                <option value="critical" @selected(old('priority') === 'critical')>Critical</option>
                            </select>
                        </div>

                        <div class="ag-task-field">
                            <label for="new-task-due-date">Due Date</label>
                            <input
                                id="new-task-due-date"
                                type="date"
                                name="due_date"
                                value="{{ old('due_date') }}"
                            >
                        </div>

                        <div class="ag-task-field ag-task-field--checkbox">
                            <label>
                                <input
                                    type="checkbox"
                                    name="is_milestone"
                                    value="1"
                                    @checked(old('is_milestone'))
                                >
                                Mark as milestone
                            </label>
                        </div>

                    </div>

                    <div class="ag-task-field">
                        <label for="new-task-notes">Internal Notes</label>
                        <textarea
                            id="new-task-notes"
                            name="notes"
                            rows="3"
                            placeholder="Optional internal notes"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        class="ag-task-primary-button ag-task-primary-button--full"
                    >
                        <i class="fas fa-plus"></i>
                        Add Task
                    </button>

                </form>

            </aside>

        </div>

    </section>


    {{-- ================================================================
        MAIN GRID
    ================================================================ --}}

    <section class="ag-project-main-grid">


        {{-- PROJECT INFORMATION --}}

        <article class="ag-project-panel">

            <div class="ag-project-panel-heading">

                <div>

                    <span class="ag-project-section-label">
                        Project Details
                    </span>

                    <h3>
                        Delivery Information
                    </h3>

                </div>

            </div>


            <div class="ag-project-info-list">

                <div class="ag-project-info-row">

                    <span>
                        Organisation
                    </span>

                    <strong>

                        @if($project->client)

                            <a
                                href="{{ route('clients.show', $project->client) }}"
                            >
                                {{ $project->client->company_name }}
                            </a>

                        @else

                            Unknown Workspace

                        @endif

                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Status
                    </span>

                    <strong>
                        {{ $statusLabel }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Priority
                    </span>

                    <strong>
                        {{ ucfirst($project->priority) }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Owner
                    </span>

                    <strong>
                        {{
                            $project->owner_name
                            ?: 'Unassigned'
                        }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Owner Email
                    </span>

                    <strong>

                        @if($project->owner_email)

                            <a
                                href="mailto:{{ $project->owner_email }}"
                            >
                                {{ $project->owner_email }}
                            </a>

                        @else

                            Not recorded

                        @endif

                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Start Date
                    </span>

                    <strong>
                        {{
                            $project->start_date
                                ? $project->start_date->format('d M Y')
                                : 'Not set'
                        }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Due Date
                    </span>

                    <strong class="{{ $isOverdue ? 'ag-overdue-text' : '' }}">
                        {{
                            $project->due_date
                                ? $project->due_date->format('d M Y')
                                : 'Not set'
                        }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Source
                    </span>

                    <strong>
                        {{
                            $project->source
                            ?: 'Not recorded'
                        }}
                    </strong>

                </div>


                <div class="ag-project-info-row">

                    <span>
                        Source Reference
                    </span>

                    <strong>
                        {{
                            $project->source_reference
                            ?: 'Not recorded'
                        }}
                    </strong>

                </div>

            </div>

        </article>


        {{-- DESCRIPTION / NOTES --}}

        <article class="ag-project-panel">

            <div class="ag-project-panel-heading">

                <div>

                    <span class="ag-project-section-label">
                        Project Context
                    </span>

                    <h3>
                        Scope & Notes
                    </h3>

                </div>

            </div>


            <div class="ag-project-copy-block">

                <span>
                    Description
                </span>

                <p>
                    {{
                        $project->description
                        ?: 'No project description has been recorded.'
                    }}
                </p>

            </div>


            <div class="ag-project-copy-block">

                <span>
                    Internal Notes
                </span>

                <p>
                    {{
                        $project->notes
                        ?: 'No internal project notes have been recorded.'
                    }}
                </p>

            </div>

        </article>

    </section>


    {{-- ================================================================
        QUICK LINKS
    ================================================================ --}}

    <section class="ag-project-module-grid">

        <a
            href="{{ route('clients.show', $project->client) }}"
            class="ag-project-module"
        >

            <div class="ag-project-module__icon ag-project-module__icon--blue">
                <i class="fas fa-building"></i>
            </div>

            <div>
                <strong>
                    Organisation Workspace
                </strong>

                <span>
                    Open the organisation command centre.
                </span>
            </div>

            <i class="fas fa-arrow-right"></i>

        </a>


        <a
            href="{{ route('business-pulse.workspace', $project->client) }}"
            class="ag-project-module"
        >

            <div class="ag-project-module__icon ag-project-module__icon--green">
                <i class="fas fa-heart-pulse"></i>
            </div>

            <div>
                <strong>
                    Business Pulse™
                </strong>

                <span>
                    Review business health and operational readiness.
                </span>
            </div>

            <i class="fas fa-arrow-right"></i>

        </a>


        <a
            href="{{ route('security.workspace', $project->client) }}"
            class="ag-project-module"
        >

            <div class="ag-project-module__icon ag-project-module__icon--purple">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <strong>
                    Cyber Centre
                </strong>

                <span>
                    Review cyber resilience and security controls.
                </span>
            </div>

            <i class="fas fa-arrow-right"></i>

        </a>

    </section>


    {{-- ================================================================
        ADMINISTRATION
    ================================================================ --}}

    <section class="ag-project-admin-panel">

        <div>

            <span class="ag-project-section-label">
                Administration
            </span>

            <h3>
                Project Controls
            </h3>

            <p>
                Update or permanently remove this project.
            </p>

        </div>


        <div class="ag-project-admin-actions">

            <a
                href="{{ route('projects.edit', $project) }}"
                class="ag-project-admin-button"
            >
                <i class="fas fa-pen"></i>

                Edit Project
            </a>


            <form
                method="POST"
                action="{{ route('projects.destroy', $project) }}"
                onsubmit="
                    return confirm(
                        'Are you sure you want to permanently delete this project?'
                    );
                "
            >

                @csrf
                @method('DELETE')


                <button
                    type="submit"
                    class="
                        ag-project-admin-button
                        ag-project-admin-button--danger
                    "
                >
                    <i class="fas fa-trash"></i>

                    Delete Project
                </button>

            </form>

        </div>

    </section>

</div>


<style>

/* ================================================================
   PROJECT WORKSPACE
================================================================ */

.ag-project-workspace {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/* ================================================================
   HERO
================================================================ */

.ag-project-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 24px;

    padding: 32px;

    border-radius: 24px;

    color: #ffffff;

    background:
        radial-gradient(
            circle at top right,
            rgba(37, 99, 235, .58),
            transparent 38%
        ),
        linear-gradient(
            135deg,
            #0f172a,
            #172554
        );

    box-shadow:
        0 18px 42px
        rgba(15, 23, 42, .16);
}

.ag-project-kicker,
.ag-project-section-label {
    display: block;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: .1em;

    text-transform: uppercase;
}

.ag-project-kicker {
    color: #93c5fd;
}

.ag-project-section-label {
    color: #64748b;
}

.ag-project-title-row {
    display: flex;
    align-items: center;

    flex-wrap: wrap;

    gap: 10px;
}

.ag-project-title-row h2 {
    margin: 7px 0 11px;

    font-size: 31px;
}

.ag-project-hero-meta {
    display: flex;
    flex-wrap: wrap;

    gap: 18px;

    color: #cbd5e1;

    font-size: 13px;
}

.ag-project-hero-meta span {
    display: inline-flex;
    align-items: center;

    gap: 7px;
}

.ag-project-hero-actions {
    display: flex;

    gap: 10px;
}

.ag-project-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 13px 16px;

    border-radius: 12px;

    text-decoration: none;

    font-weight: 750;

    transition: .2s ease;
}

.ag-project-button:hover {
    transform: translateY(-2px);

    text-decoration: none;
}

.ag-project-button--light {
    color: #0f172a;

    background: #ffffff;
}

.ag-project-button--secondary {
    color: #ffffff;

    border:
        1px solid
        rgba(255, 255, 255, .25);

    background:
        rgba(255, 255, 255, .08);
}


/* ================================================================
   BADGES
================================================================ */

.ag-status-badge,
.ag-priority-badge {
    padding: 5px 8px;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
}

.ag-status-badge--planned {
    color: #475569;
    background: #f1f5f9;
}

.ag-status-badge--progress {
    color: #1d4ed8;
    background: #dbeafe;
}

.ag-status-badge--hold {
    color: #92400e;
    background: #fef3c7;
}

.ag-status-badge--completed {
    color: #047857;
    background: #d1fae5;
}

.ag-status-badge--cancelled {
    color: #991b1b;
    background: #fee2e2;
}

.ag-priority-badge--critical {
    color: #991b1b;
    background: #fee2e2;
}

.ag-priority-badge--high {
    color: #9a3412;
    background: #ffedd5;
}

.ag-priority-badge--medium {
    color: #92400e;
    background: #fef3c7;
}

.ag-priority-badge--low {
    color: #1d4ed8;
    background: #dbeafe;
}


/* ================================================================
   ALERT
================================================================ */

.ag-project-alert {
    display: flex;
    align-items: center;

    gap: 10px;

    padding: 14px 17px;

    border: 1px solid #a7f3d0;

    border-radius: 13px;

    color: #047857;

    background: #ecfdf5;
}


/* ================================================================
   SUMMARY
================================================================ */

.ag-project-summary-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 17px;
}

.ag-project-summary-card,
.ag-project-progress-panel,
.ag-project-panel,
.ag-project-admin-panel {
    border: 1px solid #e5e7eb;

    background: #ffffff;

    box-shadow:
        0 10px 28px
        rgba(15, 23, 42, .05);
}

.ag-project-summary-card {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 20px;

    border-radius: 17px;
}

.ag-project-summary-icon {
    width: 49px;
    height: 49px;

    flex: 0 0 49px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;
}

.ag-project-summary-icon--blue {
    color: #2563eb;
    background: #dbeafe;
}

.ag-project-summary-icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-project-summary-icon--amber {
    color: #d97706;
    background: #fef3c7;
}

.ag-project-summary-icon--purple {
    color: #7c3aed;
    background: #ede9fe;
}

.ag-project-summary-card span,
.ag-project-summary-card strong,
.ag-project-summary-card small {
    display: block;
}

.ag-project-summary-card span {
    color: #64748b;

    font-size: 12px;
}

.ag-project-summary-card strong {
    margin: 2px 0;

    color: #0f172a;

    font-size: 24px;
}

.ag-project-summary-card__text {
    font-size: 14px !important;
}

.ag-project-summary-card small {
    color: #94a3b8;

    font-size: 10px;
}


/* ================================================================
   PROGRESS
================================================================ */

.ag-project-progress-panel,
.ag-project-panel,
.ag-project-admin-panel {
    padding: 24px;

    border-radius: 19px;
}

.ag-project-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 18px;

    margin-bottom: 20px;
}

.ag-project-panel-heading h3 {
    margin: 5px 0 6px;

    color: #0f172a;

    font-size: 21px;
}

.ag-project-panel-heading p {
    margin: 0;

    color: #64748b;
}

.ag-project-progress-value {
    color: #2563eb;

    font-size: 27px;
}

.ag-project-progress-track {
    width: 100%;

    height: 12px;

    overflow: hidden;

    border-radius: 999px;

    background: #e5e7eb;
}

.ag-project-progress-fill {
    height: 100%;

    border-radius: inherit;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            #10b981
        );
}

.ag-project-progress-scale {
    display: flex;
    justify-content: space-between;

    margin-top: 8px;

    color: #94a3b8;

    font-size: 10px;
}



/* ================================================================
   DELIVERY HEALTH
================================================================ */

.ag-delivery-health {
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 19px;
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
}

.ag-delivery-health__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
}

.ag-delivery-health__title-group {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.ag-delivery-health__icon {
    width: 52px;
    height: 52px;
    flex: 0 0 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    font-size: 21px;
}

.ag-delivery-health__title-group h3 {
    margin: 5px 0 6px;
    color: #0f172a;
    font-size: 21px;
}

.ag-delivery-health__title-group p {
    margin: 0;
    color: #64748b;
}

.ag-delivery-health__status {
    display: flex;
    align-items: center;
    gap: 18px;
}

.ag-delivery-health__badge {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 7px 13px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .02em;
}

.ag-delivery-health__score {
    min-width: 82px;
    text-align: right;
}

.ag-delivery-health__score strong {
    color: #0f172a;
    font-size: 28px;
    line-height: 1;
}

.ag-delivery-health__score span {
    color: #94a3b8;
    font-size: 11px;
}

.ag-delivery-health__body {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 2fr);
    gap: 22px;
    margin-top: 22px;
}

.ag-delivery-health__assessment {
    padding: 18px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: #f8fafc;
}

.ag-delivery-health__assessment span {
    display: block;
    margin-bottom: 7px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
}

.ag-delivery-health__assessment p {
    margin: 0;
    color: #334155;
    font-size: 13px;
    line-height: 1.65;
}

.ag-delivery-health__metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.ag-delivery-health__metric {
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: #ffffff;
}

.ag-delivery-health__metric span,
.ag-delivery-health__metric strong {
    display: block;
}

.ag-delivery-health__metric span {
    margin-bottom: 6px;
    color: #64748b;
    font-size: 11px;
}

.ag-delivery-health__metric strong {
    color: #0f172a;
    font-size: 17px;
}

/* HEALTHY */

.ag-delivery-health--healthy {
    border-left: 4px solid #10b981;
}

.ag-delivery-health--healthy .ag-delivery-health__icon,
.ag-delivery-health--healthy .ag-delivery-health__badge {
    color: #047857;
    background: #d1fae5;
}

/* ATTENTION */

.ag-delivery-health--attention {
    border-left: 4px solid #f59e0b;
}

.ag-delivery-health--attention .ag-delivery-health__icon,
.ag-delivery-health--attention .ag-delivery-health__badge {
    color: #b45309;
    background: #fef3c7;
}

/* AT RISK */

.ag-delivery-health--risk {
    border-left: 4px solid #ef4444;
}

.ag-delivery-health--risk .ag-delivery-health__icon,
.ag-delivery-health--risk .ag-delivery-health__badge {
    color: #b91c1c;
    background: #fee2e2;
}

/* INACTIVE */

.ag-delivery-health--inactive {
    border-left: 4px solid #94a3b8;
}

.ag-delivery-health--inactive .ag-delivery-health__icon,
.ag-delivery-health--inactive .ag-delivery-health__badge {
    color: #475569;
    background: #f1f5f9;
}

@media (max-width: 1100px) {
    .ag-delivery-health__body {
        grid-template-columns: 1fr;
    }

    .ag-delivery-health__metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .ag-delivery-health__header {
        flex-direction: column;
    }

    .ag-delivery-health__status {
        width: 100%;
        justify-content: space-between;
    }

    .ag-delivery-health__metrics {
        grid-template-columns: 1fr;
    }
}


/* ================================================================
   MAIN GRID
================================================================ */

.ag-project-main-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;
}


/* ================================================================
   INFORMATION
================================================================ */

.ag-project-info-list {
    border-top: 1px solid #eef2f7;
}

.ag-project-info-row {
    display: grid;

    grid-template-columns:
        140px
        minmax(0, 1fr);

    gap: 14px;

    padding: 13px 0;

    border-bottom: 1px solid #eef2f7;
}

.ag-project-info-row span {
    color: #64748b;

    font-size: 12px;
}

.ag-project-info-row strong {
    color: #0f172a;

    font-size: 13px;
}

.ag-project-info-row a {
    color: #2563eb;

    text-decoration: none;
}


/* ================================================================
   COPY
================================================================ */

.ag-project-copy-block {
    padding: 16px;

    border-radius: 13px;

    background: #f8fafc;
}

.ag-project-copy-block + .ag-project-copy-block {
    margin-top: 14px;
}

.ag-project-copy-block span {
    color: #64748b;

    font-size: 11px;
    font-weight: 750;

    text-transform: uppercase;

    letter-spacing: .05em;
}

.ag-project-copy-block p {
    margin: 7px 0 0;

    color: #334155;

    line-height: 1.6;
}


/* ================================================================
   TASKS & MILESTONES
================================================================ */

.ag-task-workspace {
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 19px;
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
}

.ag-task-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.ag-task-heading h3 {
    margin: 5px 0 6px;
    color: #0f172a;
    font-size: 21px;
}

.ag-task-heading p {
    margin: 0;
    color: #64748b;
}

.ag-task-heading-status {
    min-width: 92px;
    padding: 10px 12px;
    border-radius: 13px;
    text-align: center;
    background: #eff6ff;
}

.ag-task-heading-status strong,
.ag-task-heading-status span {
    display: block;
}

.ag-task-heading-status strong {
    color: #2563eb;
    font-size: 18px;
}

.ag-task-heading-status span {
    margin-top: 2px;
    color: #64748b;
    font-size: 9px;
    text-transform: uppercase;
}

.ag-task-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.ag-task-summary-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #f8fafc;
}

.ag-task-summary-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.ag-task-summary-icon--blue { color: #2563eb; background: #dbeafe; }
.ag-task-summary-icon--green { color: #059669; background: #d1fae5; }
.ag-task-summary-icon--purple { color: #7c3aed; background: #ede9fe; }
.ag-task-summary-icon--red { color: #dc2626; background: #fee2e2; }

.ag-task-summary-card span,
.ag-task-summary-card strong,
.ag-task-summary-card small {
    display: block;
}

.ag-task-summary-card span { color: #64748b; font-size: 11px; }
.ag-task-summary-card strong { margin: 1px 0; color: #0f172a; font-size: 21px; }
.ag-task-summary-card small { color: #94a3b8; font-size: 9px; }

.ag-task-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(300px, .75fr);
    gap: 18px;
    align-items: start;
}

.ag-task-subheading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.ag-task-subheading h4,
.ag-task-create-heading h4 {
    margin: 4px 0 0;
    color: #0f172a;
    font-size: 18px;
}

.ag-task-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border-radius: 999px;
    color: #1d4ed8;
    background: #dbeafe;
    font-size: 10px;
    font-weight: 800;
}

.ag-task-card {
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: #ffffff;
    transition: .18s ease;
}

.ag-task-card + .ag-task-card { margin-top: 10px; }
.ag-task-card:hover { border-color: #cbd5e1; box-shadow: 0 8px 22px rgba(15, 23, 42, .05); }
.ag-task-card--completed { border-color: #bbf7d0; background: #f8fffb; }
.ag-task-card--overdue { border-color: #fecaca; }

.ag-task-card-main {
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr);
    gap: 12px;
}

.ag-task-toggle-form { margin: 0; }

.ag-task-toggle {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    color: #94a3b8;
    background: #ffffff;
    cursor: pointer;
    transition: .18s ease;
}

.ag-task-toggle:hover { color: #2563eb; border-color: #93c5fd; background: #eff6ff; }
.ag-task-toggle--completed { color: #ffffff; border-color: #10b981; background: #10b981; }

.ag-task-card-content { min-width: 0; }

.ag-task-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 7px;
}

.ag-task-card h5 { margin: 0; color: #0f172a; font-size: 15px; }
.ag-task-card--completed h5 { color: #64748b; text-decoration: line-through; }

.ag-task-description {
    margin: 7px 0 0;
    color: #475569;
    font-size: 12px;
    line-height: 1.55;
}

.ag-task-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 10px;
    color: #64748b;
    font-size: 10px;
}

.ag-task-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.ag-task-note {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    margin-top: 10px;
    padding: 9px 10px;
    border-radius: 9px;
    color: #64748b;
    background: #f8fafc;
    font-size: 10px;
    line-height: 1.5;
}

.ag-task-status,
.ag-task-milestone,
.ag-task-overdue {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 7px;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
}

.ag-task-status--pending { color: #475569; background: #f1f5f9; }
.ag-task-status--progress { color: #1d4ed8; background: #dbeafe; }
.ag-task-status--blocked { color: #991b1b; background: #fee2e2; }
.ag-task-status--completed { color: #047857; background: #d1fae5; }
.ag-task-milestone { color: #6d28d9; background: #ede9fe; }
.ag-task-overdue { color: #b91c1c; background: #fee2e2; }

.ag-task-actions {
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 13px;
    padding-top: 12px;
    border-top: 1px solid #eef2f7;
}

.ag-task-edit { position: relative; }

.ag-task-edit summary,
.ag-task-delete-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    color: #475569;
    background: #ffffff;
    font-size: 10px;
    font-weight: 750;
    cursor: pointer;
    list-style: none;
}

.ag-task-edit summary::-webkit-details-marker { display: none; }
.ag-task-delete-button { color: #b91c1c; }
.ag-task-delete-button:hover { border-color: #fecaca; background: #fef2f2; }
.ag-task-edit[open] { width: 100%; }
.ag-task-edit[open] summary { margin-left: auto; }

.ag-task-edit-form {
    margin-top: 12px;
    padding: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
}

.ag-task-create-panel {
    position: sticky;
    top: 20px;
    padding: 18px;
    border: 1px solid #dbeafe;
    border-radius: 16px;
    background: linear-gradient(180deg, #f8fbff, #ffffff);
}

.ag-task-create-heading { margin-bottom: 14px; }
.ag-task-create-heading p { margin: 6px 0 0; color: #64748b; font-size: 11px; line-height: 1.5; }

.ag-task-create-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ag-task-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 11px;
}

.ag-task-form-grid--create {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.ag-task-field { min-width: 0; }
.ag-task-field--wide { grid-column: 1 / -1; }

.ag-task-field label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 10px;
    font-weight: 750;
}

.ag-task-field input,
.ag-task-field select,
.ag-task-field textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    color: #0f172a;
    background: #ffffff;
    font: inherit;
    font-size: 11px;
    outline: none;
}

.ag-task-field textarea { resize: vertical; }

.ag-task-field input:focus,
.ag-task-field select:focus,
.ag-task-field textarea:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
}

.ag-task-field--checkbox {
    display: flex;
    align-items: flex-end;
}

.ag-task-field--checkbox label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 10px 0;
}

.ag-task-field--checkbox input { width: auto; }

.ag-task-primary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 13px;
    border: 0;
    border-radius: 9px;
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    font-size: 10px;
    font-weight: 800;
    cursor: pointer;
    transition: .18s ease;
}

.ag-task-primary-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 7px 18px rgba(37, 99, 235, .18);
}

.ag-task-primary-button--full { width: 100%; }

.ag-task-empty {
    padding: 34px 20px;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    text-align: center;
    background: #f8fafc;
}

.ag-task-empty-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 11px;
    border-radius: 14px;
    color: #2563eb;
    background: #dbeafe;
}

.ag-task-empty h4 { margin: 0; color: #0f172a; }
.ag-task-empty p { max-width: 430px; margin: 7px auto 0; color: #64748b; font-size: 11px; line-height: 1.6; }

.ag-project-alert--danger {
    align-items: flex-start;
    border-color: #fecaca;
    color: #b91c1c;
    background: #fef2f2;
}

.ag-project-alert--danger strong { display: block; margin-bottom: 5px; }
.ag-project-alert--danger ul { margin: 0; padding-left: 18px; }


/* ================================================================
   MODULES
================================================================ */

.ag-project-module-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 14px;
}

.ag-project-module {
    display: grid;

    grid-template-columns:
        46px
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 12px;

    padding: 16px;

    border: 1px solid #e5e7eb;

    border-radius: 15px;

    color: inherit;

    background: #ffffff;

    text-decoration: none;

    transition: .18s ease;
}

.ag-project-module:hover {
    color: inherit;

    text-decoration: none;

    transform: translateY(-2px);

    box-shadow:
        0 10px 22px
        rgba(15, 23, 42, .06);
}

.ag-project-module__icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;
}

.ag-project-module__icon--blue {
    color: #2563eb;
    background: #dbeafe;
}

.ag-project-module__icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-project-module__icon--purple {
    color: #7c3aed;
    background: #ede9fe;
}

.ag-project-module strong,
.ag-project-module span {
    display: block;
}

.ag-project-module strong {
    color: #0f172a;

    font-size: 13px;
}

.ag-project-module span {
    margin-top: 3px;

    color: #94a3b8;

    font-size: 10px;
}


/* ================================================================
   ADMIN
================================================================ */

.ag-project-admin-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;
}

.ag-project-admin-panel h3 {
    margin: 5px 0 6px;
}

.ag-project-admin-panel p {
    margin: 0;

    color: #64748b;
}

.ag-project-admin-actions {
    display: flex;

    gap: 9px;
}

.ag-project-admin-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 10px 13px;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    color: #475569;

    background: #ffffff;

    font-size: 11px;
    font-weight: 750;

    text-decoration: none;

    cursor: pointer;
}

.ag-project-admin-button--danger {
    color: #b91c1c;
}

.ag-project-admin-button--danger:hover {
    border-color: #fecaca;

    background: #fef2f2;
}


/* ================================================================
   OVERDUE
================================================================ */

.ag-overdue-text {
    color: #dc2626 !important;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1100px) {

    .ag-project-summary-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .ag-project-main-grid {
        grid-template-columns: 1fr;
    }

    .ag-project-module-grid {
        grid-template-columns: 1fr;
    }

    .ag-task-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ag-task-layout {
        grid-template-columns: 1fr;
    }

    .ag-task-create-panel {
        position: static;
    }

}


@media (max-width: 700px) {

    .ag-project-hero {
        align-items: flex-start;

        flex-direction: column;
    }

    .ag-project-hero-actions {
        width: 100%;

        flex-direction: column;
    }

    .ag-project-summary-grid {
        grid-template-columns: 1fr;
    }

    .ag-project-info-row {
        grid-template-columns: 1fr;

        gap: 4px;
    }

    .ag-project-admin-panel {
        align-items: flex-start;

        flex-direction: column;
    }

    .ag-project-admin-actions {
        width: 100%;

        flex-direction: column;
    }

    .ag-project-admin-button {
        width: 100%;
    }

    .ag-task-heading,
    .ag-task-subheading {
        align-items: flex-start;
        flex-direction: column;
    }

    .ag-task-summary-grid {
        grid-template-columns: 1fr;
    }

    .ag-task-form-grid,
    .ag-task-form-grid--create {
        grid-template-columns: 1fr;
    }

    .ag-task-field--wide {
        grid-column: auto;
    }

    .ag-task-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .ag-task-edit,
    .ag-task-edit[open] {
        width: 100%;
    }

    .ag-task-edit summary,
    .ag-task-delete-button {
        width: 100%;
        box-sizing: border-box;
    }

}

</style>

@endsection