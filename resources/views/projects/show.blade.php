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
        && $project->due_date->isPast()
        && !in_array(
            $project->status,
            ['completed', 'cancelled']
        );

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

}

</style>

@endsection