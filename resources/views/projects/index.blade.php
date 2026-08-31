@extends('layouts.aceguard')

@section('title', 'Projects')

@section('page-title', 'Projects')

@section(
    'page-subtitle',
    'Track delivery, ownership, deadlines and progress across organisation workspaces.'
)

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Portfolio Intelligence
    |--------------------------------------------------------------------------
    |
    | Portfolio-wide metrics are supplied by ProjectController@index.
    | This prevents pagination from producing misleading business metrics.
    |
    */

    $portfolioHealthClass = match (true) {
        $averageDeliveryHealth >= 80 =>
            'healthy',

        $averageDeliveryHealth >= 50 =>
            'attention',

        $averageDeliveryHealth > 0 =>
            'risk',

        default =>
            'inactive',
    };


    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    $statusLabel = function ($status) {

        return match ($status) {
            'planned' =>
                'Planned',

            'in_progress' =>
                'In Progress',

            'on_hold' =>
                'On Hold',

            'completed' =>
                'Completed',

            'cancelled' =>
                'Cancelled',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $status ?? ''
                    )
                ),
        };

    };


    $statusClass = function ($status) {

        return match ($status) {
            'planned' =>
                'planned',

            'in_progress' =>
                'progress',

            'on_hold' =>
                'hold',

            'completed' =>
                'completed',

            'cancelled' =>
                'cancelled',

            default =>
                'planned',
        };

    };


    $priorityClass = function ($priority) {

        return match ($priority) {
            'critical' =>
                'critical',

            'high' =>
                'high',

            'medium' =>
                'medium',

            default =>
                'low',
        };

    };

@endphp


<div class="ag-projects">


    {{-- ================================================================
        HERO
    ================================================================ --}}

    <section class="ag-projects-hero">

        <div>

            <span class="ag-hero-kicker">
                AceGuard BOS · Delivery Management
            </span>

            <h2>
                Projects
            </h2>

            <p>
                Turn business priorities into accountable work
                with owners, deadlines and measurable progress.
            </p>

        </div>


        <a
            href="{{ route('projects.create') }}"
            class="ag-button ag-button--light"
        >
            <i class="fas fa-plus"></i>

            New Project
        </a>

    </section>


    {{-- ================================================================
        SUCCESS MESSAGE
    ================================================================ --}}

    @if(session('success'))

        <div class="ag-alert ag-alert--success">

            <i class="fas fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif


    {{-- ================================================================
        PORTFOLIO INTELLIGENCE
    ================================================================ --}}

    <section class="ag-project-summary">


        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--green">

                <i class="fas fa-circle-check"></i>

            </div>


            <div>

                <span>
                    Healthy
                </span>

                <strong>
                    {{ $healthyProjects }}
                </strong>

                <small>
                    Projects delivering without material risk
                </small>

            </div>

        </article>


        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--amber">

                <i class="fas fa-triangle-exclamation"></i>

            </div>


            <div>

                <span>
                    Attention
                </span>

                <strong>
                    {{ $attentionProjects }}
                </strong>

                <small>
                    Projects requiring management attention
                </small>

            </div>

        </article>


        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--red">

                <i class="fas fa-shield-halved"></i>

            </div>


            <div>

                <span>
                    At Risk
                </span>

                <strong>
                    {{ $atRiskProjects }}
                </strong>

                <small>
                    Projects with material delivery risk
                </small>

            </div>

        </article>


        <article
            class="
                ag-summary-card
                ag-summary-card--health
                ag-summary-card--{{ $portfolioHealthClass }}
            "
        >

            <div class="ag-summary-icon ag-summary-icon--navy">

                <i class="fas fa-chart-line"></i>

            </div>


            <div>

                <span>
                    Portfolio Health
                </span>

                <strong>
                    {{ $averageDeliveryHealth }}/100
                </strong>

                <small>
                    {{
                        $totalProjects > 0
                            ? $totalProjects . ' projects assessed'
                            : 'No projects available'
                    }}
                </small>

            </div>

        </article>

    </section>


    {{-- ================================================================
        PROJECT PORTFOLIO
    ================================================================ --}}

    <section class="ag-project-panel">

        <div class="ag-panel-heading">

            <div>

                <span class="ag-section-label">
                    Delivery Portfolio
                </span>

                <h3>
                    Active Project Register
                </h3>

                <p>
                    Review project ownership, priorities,
                    deadlines and current delivery progress.
                </p>

            </div>

        </div>


        @forelse($projects as $project)

            @php

                $status =
                    $project->status
                    ?? 'planned';

                $priority =
                    $project->priority
                    ?? 'medium';

                $progress =
                    max(
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
                        $status,
                        [
                            'completed',
                            'cancelled',
                        ]
                    );

                $deliveryHealthKey =
                    $project->deliveryHealthKey();

                $deliveryHealthLabel =
                    $project->deliveryHealthLabel();

                $deliveryHealthScore =
                    $project->deliveryHealthScore();

                $deliveryHealthClass =
                    match ($deliveryHealthKey) {
                        'healthy' =>
                            'healthy',

                        'attention' =>
                            'attention',

                        'at_risk' =>
                            'risk',

                        'inactive' =>
                            'inactive',

                        default =>
                            'neutral',
                    };

            @endphp


            <article class="ag-project-card">


                {{-- ====================================================
                    IDENTITY
                ==================================================== --}}

                <div class="ag-project-identity">

                    <div class="ag-project-icon">

                        <i class="fas fa-diagram-project"></i>

                    </div>


                    <div>

                        <div class="ag-project-title-row">

                            <h4>
                                {{ $project->name }}
                            </h4>


                            <span
                                class="
                                    ag-status-badge
                                    ag-status-badge--{{
                                        $statusClass($status)
                                    }}
                                "
                            >
                                {{ $statusLabel($status) }}
                            </span>


                            <span
                                class="
                                    ag-priority-badge
                                    ag-priority-badge--{{
                                        $priorityClass($priority)
                                    }}
                                "
                            >
                                {{ ucfirst($priority) }}
                            </span>


                            <span
                                class="
                                    ag-health-badge
                                    ag-health-badge--{{
                                        $deliveryHealthClass
                                    }}
                                "
                            >
                                <i class="fas fa-heart-pulse"></i>

                                {{ $deliveryHealthLabel }}

                                <strong>
                                    {{ $deliveryHealthScore }}/100
                                </strong>
                            </span>

                        </div>


                        <div class="ag-project-workspace">

                            <i class="fas fa-building"></i>

                            <span>
                                {{
                                    $project->client->company_name
                                    ?? 'Unknown Workspace'
                                }}
                            </span>

                        </div>


                        @if($project->description)

                            <p>
                                {{ \Illuminate\Support\Str::limit(
                                    $project->description,
                                    130
                                ) }}
                            </p>

                        @endif

                    </div>

                </div>


                {{-- ====================================================
                    DELIVERY INFORMATION
                ==================================================== --}}

                <div class="ag-project-details">


                    <div class="ag-project-detail">

                        <span>
                            Owner
                        </span>

                        <strong>
                            {{
                                $project->owner_name
                                ?: 'Unassigned'
                            }}
                        </strong>

                        @if($project->owner_email)

                            <small>
                                {{ $project->owner_email }}
                            </small>

                        @endif

                    </div>


                    <div class="ag-project-detail">

                        <span>
                            Due Date
                        </span>

                        <strong
                            class="{{
                                $isOverdue
                                    ? 'ag-overdue'
                                    : ''
                            }}"
                        >
                            {{
                                $project->due_date
                                    ? $project->due_date->format('d M Y')
                                    : 'No deadline'
                            }}
                        </strong>

                        @if($isOverdue)

                            <small class="ag-overdue">
                                Overdue
                            </small>

                        @endif

                    </div>


                    <div class="ag-project-detail">

                        <span>
                            Progress
                        </span>

                        <strong>
                            {{ $progress }}%
                        </strong>

                        <div class="ag-project-progress">

                            <div
                                style="
                                    width:
                                        {{ $progress }}%;
                                "
                            ></div>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    ACTIONS
                ==================================================== --}}

                <div class="ag-project-actions">

                    <a
                        href="{{ route('projects.show', $project) }}"
                        class="
                            ag-action
                            ag-action--primary
                        "
                    >
                        Open Project

                        <i class="fas fa-arrow-right"></i>
                    </a>


                    <a
                        href="{{ route('projects.edit', $project) }}"
                        class="ag-action"
                    >
                        <i class="fas fa-pen"></i>

                        Edit
                    </a>

                </div>

            </article>


        @empty

            <div class="ag-empty-state">

                <div class="ag-empty-icon">

                    <i class="fas fa-diagram-project"></i>

                </div>


                <h3>
                    No projects yet
                </h3>


                <p>
                    Create your first project to begin tracking
                    delivery, ownership and progress in AceGuard BOS.
                </p>


                <a
                    href="{{ route('projects.create') }}"
                    class="
                        ag-button
                        ag-button--primary
                    "
                >
                    <i class="fas fa-plus"></i>

                    Create First Project
                </a>

            </div>

        @endforelse


        {{-- ============================================================
            PAGINATION
        ============================================================ --}}

        @if($projects->hasPages())

            <div class="ag-pagination">

                {{ $projects->links() }}

            </div>

        @endif

    </section>

</div>


<style>

/* ================================================================
   PROJECTS
================================================================ */

.ag-projects {
    display: flex;
    flex-direction: column;

    gap: 24px;
}


/* ================================================================
   HERO
================================================================ */

.ag-projects-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 24px;

    padding: 30px;

    border-radius: 22px;

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


.ag-hero-kicker,
.ag-section-label {
    display: block;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: .11em;

    text-transform: uppercase;
}


.ag-hero-kicker {
    color: #93c5fd;
}


.ag-section-label {
    color: #64748b;
}


.ag-projects-hero h2 {
    margin: 6px 0 9px;

    font-size: 31px;
}


.ag-projects-hero p {
    max-width: 720px;

    margin: 0;

    color: #cbd5e1;

    line-height: 1.6;
}


/* ================================================================
   BUTTONS
================================================================ */

.ag-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 13px 18px;

    border-radius: 12px;

    font-weight: 750;

    text-decoration: none;

    transition: .2s ease;
}


.ag-button:hover {
    transform: translateY(-2px);

    text-decoration: none;
}


.ag-button--light {
    color: #0f172a;

    background: #ffffff;
}


.ag-button--primary {
    color: #ffffff;

    background: #2563eb;
}


/* ================================================================
   ALERT
================================================================ */

.ag-alert {
    display: flex;
    align-items: center;

    gap: 10px;

    padding: 14px 17px;

    border-radius: 13px;
}


.ag-alert--success {
    color: #047857;

    border: 1px solid #a7f3d0;

    background: #ecfdf5;
}


/* ================================================================
   SUMMARY
================================================================ */

.ag-project-summary {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 17px;
}


.ag-summary-card {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 20px;

    border: 1px solid #e5e7eb;

    border-radius: 17px;

    background: #ffffff;

    box-shadow:
        0 9px 24px
        rgba(15, 23, 42, .05);
}


.ag-summary-icon {
    width: 49px;
    height: 49px;

    flex: 0 0 49px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;
}


.ag-summary-icon--blue {
    color: #2563eb;

    background: #dbeafe;
}


.ag-summary-icon--green {
    color: #059669;

    background: #d1fae5;
}


.ag-summary-icon--amber {
    color: #d97706;

    background: #fef3c7;
}


.ag-summary-icon--purple {
    color: #7c3aed;

    background: #ede9fe;
}


.ag-summary-icon--red {
    color: #dc2626;

    background: #fee2e2;
}


.ag-summary-icon--navy {
    color: #1e3a8a;

    background: #dbeafe;
}


.ag-summary-card--health {
    position: relative;

    overflow: hidden;

    border-left-width: 4px;
}


.ag-summary-card--healthy {
    border-left-color: #10b981;
}


.ag-summary-card--attention {
    border-left-color: #f59e0b;
}


.ag-summary-card--risk {
    border-left-color: #ef4444;
}


.ag-summary-card--inactive {
    border-left-color: #94a3b8;
}


.ag-summary-card span,
.ag-summary-card strong,
.ag-summary-card small {
    display: block;
}


.ag-summary-card span {
    color: #64748b;

    font-size: 12px;
}


.ag-summary-card strong {
    margin: 2px 0;

    color: #0f172a;

    font-size: 25px;
}


.ag-summary-card small {
    color: #94a3b8;

    font-size: 10px;
}


/* ================================================================
   PANEL
================================================================ */

.ag-project-panel {
    padding: 25px;

    border: 1px solid #e5e7eb;

    border-radius: 20px;

    background: #ffffff;

    box-shadow:
        0 12px 30px
        rgba(15, 23, 42, .05);
}


.ag-panel-heading {
    margin-bottom: 20px;
}


.ag-panel-heading h3 {
    margin: 5px 0 7px;

    color: #0f172a;

    font-size: 22px;
}


.ag-panel-heading p {
    margin: 0;

    color: #64748b;

    line-height: 1.55;
}


/* ================================================================
   PROJECT CARD
================================================================ */

.ag-project-card {
    display: grid;

    grid-template-columns:
        minmax(300px, 1.2fr)
        minmax(430px, 1fr)
        auto;

    align-items: center;

    gap: 24px;

    padding: 22px 0;

    border-top:
        1px solid #eef2f7;
}


.ag-project-card:first-of-type {
    border-top: 0;
}


/* ================================================================
   PROJECT IDENTITY
================================================================ */

.ag-project-identity {
    display: grid;

    grid-template-columns:
        52px
        minmax(0, 1fr);

    gap: 14px;

    align-items: flex-start;
}


.ag-project-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    color: #2563eb;

    background: #dbeafe;

    font-size: 18px;
}


.ag-project-title-row {
    display: flex;
    align-items: center;

    flex-wrap: wrap;

    gap: 8px;
}


.ag-project-title-row h4 {
    margin: 0;

    color: #0f172a;

    font-size: 17px;
}


.ag-project-workspace {
    display: flex;
    align-items: center;

    gap: 6px;

    margin-top: 6px;

    color: #64748b;

    font-size: 11px;
}


.ag-project-identity p {
    margin:
        9px
        0
        0;

    color: #64748b;

    font-size: 12px;

    line-height: 1.5;
}


/* ================================================================
   STATUS
================================================================ */

.ag-status-badge,
.ag-priority-badge {
    padding: 4px 8px;

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
   DELIVERY HEALTH
================================================================ */

.ag-health-badge {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    padding: 5px 9px;

    border: 1px solid transparent;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
}


.ag-health-badge strong {
    font-size: 9px;
    font-weight: 900;
}


.ag-health-badge--healthy {
    color: #047857;

    border-color: #a7f3d0;

    background: #ecfdf5;
}


.ag-health-badge--attention {
    color: #92400e;

    border-color: #fde68a;

    background: #fffbeb;
}


.ag-health-badge--risk {
    color: #991b1b;

    border-color: #fecaca;

    background: #fef2f2;
}


.ag-health-badge--inactive {
    color: #475569;

    border-color: #cbd5e1;

    background: #f1f5f9;
}


.ag-health-badge--neutral {
    color: #475569;

    border-color: #e2e8f0;

    background: #f8fafc;
}


/* ================================================================
   PROJECT DETAILS
================================================================ */

.ag-project-details {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 16px;
}


.ag-project-detail span,
.ag-project-detail strong,
.ag-project-detail small {
    display: block;
}


.ag-project-detail span {
    color: #94a3b8;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .06em;
}


.ag-project-detail strong {
    margin-top: 4px;

    color: #0f172a;

    font-size: 12px;
}


.ag-project-detail small {
    margin-top: 2px;

    color: #94a3b8;

    font-size: 9px;
}


.ag-overdue {
    color: #dc2626 !important;
}


/* ================================================================
   PROGRESS
================================================================ */

.ag-project-progress {
    width: 100%;
    height: 6px;

    overflow: hidden;

    margin-top: 7px;

    border-radius: 999px;

    background: #e5e7eb;
}


.ag-project-progress div {
    height: 100%;

    border-radius: inherit;

    background: #2563eb;
}


/* ================================================================
   ACTIONS
================================================================ */

.ag-project-actions {
    display: flex;
    align-items: center;

    gap: 7px;
}


.ag-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 10px 12px;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    color: #475569;

    background: #ffffff;

    font-size: 11px;
    font-weight: 750;

    text-decoration: none;

    white-space: nowrap;

    transition: .18s ease;
}


.ag-action:hover {
    color: #0f172a;

    border-color: #cbd5e1;

    background: #f8fafc;

    text-decoration: none;
}


.ag-action--primary {
    color: #ffffff;

    border-color: #2563eb;

    background: #2563eb;
}


.ag-action--primary:hover {
    color: #ffffff;

    border-color: #1d4ed8;

    background: #1d4ed8;
}


/* ================================================================
   EMPTY STATE
================================================================ */

.ag-empty-state {
    padding: 55px 20px;

    text-align: center;
}


.ag-empty-icon {
    width: 64px;
    height: 64px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin:
        0
        auto
        16px;

    border-radius: 18px;

    color: #2563eb;

    background: #dbeafe;

    font-size: 23px;
}


.ag-empty-state h3 {
    margin: 0 0 8px;

    color: #0f172a;
}


.ag-empty-state p {
    max-width: 530px;

    margin:
        0
        auto
        20px;

    color: #64748b;

    line-height: 1.55;
}


/* ================================================================
   PAGINATION
================================================================ */

.ag-pagination {
    margin-top: 24px;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1250px) {

    .ag-project-summary {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }


    .ag-project-card {
        grid-template-columns: 1fr;
    }


    .ag-project-actions {
        justify-content: flex-start;
    }

}


@media (max-width: 750px) {

    .ag-projects-hero {
        align-items: flex-start;

        flex-direction: column;
    }


    .ag-project-summary {
        grid-template-columns: 1fr;
    }


    .ag-project-details {
        grid-template-columns: 1fr;
    }


    .ag-project-actions {
        align-items: stretch;

        flex-direction: column;
    }


    .ag-action {
        width: 100%;
    }

}

</style>

@endsection