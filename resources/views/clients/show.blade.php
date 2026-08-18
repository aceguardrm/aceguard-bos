@extends('layouts.aceguard')

@section('title', $client->company_name . ' Workspace')

@section('page-title', 'Organisation Workspace')

@section(
    'page-subtitle',
    'Business intelligence, cyber resilience and operational oversight for '
    . $client->company_name . '.'
)

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Workspace Status
    |--------------------------------------------------------------------------
    */

    $status =
        strtolower(
            $client->status
            ?? 'inactive'
        );

    $statusLabel =
        ucfirst($status);

    $statusClass =
        match ($status) {
            'active' => 'ag-status--active',
            'lead' => 'ag-status--lead',
            default => 'ag-status--inactive',
        };


    /*
    |--------------------------------------------------------------------------
    | Score Helpers
    |--------------------------------------------------------------------------
    */

    $ratingForScore = function ($score) {

        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Healthy',
            $score >= 60 => 'Watch',
            $score >= 40 => 'At Risk',
            default => 'Critical',
        };

    };


    $colourForScore = function ($score) {

        return match (true) {
            $score >= 90 => '#10b981',
            $score >= 75 => '#2563eb',
            $score >= 60 => '#f59e0b',
            $score >= 40 => '#f97316',
            default => '#dc2626',
        };

    };


    $businessHealthRating =
        $ratingForScore(
            $businessHealthScore
        );


    $businessPulseColour =
        $colourForScore(
            $businessPulseScore
        );


    $businessHealthColour =
        $colourForScore(
            $businessHealthScore
        );


    $securityColour =
        $colourForScore(
            $securityScore
        );

@endphp


<div class="ag-org-workspace">


    {{-- ================================================================
        ORGANISATION HERO
    ================================================================ --}}

    <section class="ag-org-hero">

        <div>

            <span class="ag-hero-kicker">
                AceGuard BOS · Organisation Workspace
            </span>

            <div class="ag-org-title-row">

                <h2>
                    {{ $client->company_name }}
                </h2>

                <span
                    class="
                        ag-status
                        {{ $statusClass }}
                    "
                >
                    {{ $statusLabel }}
                </span>

            </div>


            <div class="ag-org-meta">

                <span>
                    <i class="fas fa-user"></i>

                    {{
                        $client->contact_name
                            ?: 'No contact recorded'
                    }}
                </span>


                <span>
                    <i class="fas fa-envelope"></i>

                    {{
                        $client->email
                            ?: 'No email recorded'
                    }}
                </span>


                @if($client->phone)

                    <span>
                        <i class="fas fa-phone"></i>

                        {{ $client->phone }}
                    </span>

                @endif

            </div>

        </div>


        <div class="ag-hero-actions">

            <a
                href="{{ route('clients.edit', $client) }}"
                class="ag-hero-button ag-hero-button--secondary"
            >
                <i class="fas fa-pen"></i>

                Edit Workspace
            </a>


            <a
                href="{{ route('clients.index') }}"
                class="ag-hero-button ag-hero-button--light"
            >
                <i class="fas fa-arrow-left"></i>

                All Workspaces
            </a>

        </div>

    </section>


    {{-- ================================================================
        EXECUTIVE SCORECARDS
    ================================================================ --}}

    <section class="ag-score-grid">


        {{-- Business Pulse --}}

        <article class="ag-score-card">

            <div
                class="ag-score-ring"
                style="
                    --score: {{ $businessPulseScore }};
                    --score-colour: {{ $businessPulseColour }};
                "
            >

                <div class="ag-score-ring__centre">

                    <strong>
                        {{ $businessPulseScore }}%
                    </strong>

                    <span>
                        {{ $businessPulseRating }}
                    </span>

                </div>

            </div>


            <div class="ag-score-card__copy">

                <span class="ag-card-label">
                    Business Pulse™
                </span>

                <h3>
                    {{ $businessPulseRating }}
                </h3>

                <p>
                    Combined executive condition from
                    business health and cyber security.
                </p>

            </div>

        </article>


        {{-- Business Health --}}

        <article class="ag-metric-card">

            <div
                class="ag-metric-icon"
                style="
                    color: {{ $businessHealthColour }};
                    background: {{ $businessHealthColour }}14;
                "
            >
                <i class="fas fa-heart-pulse"></i>
            </div>


            <div>

                <span>
                    Business Health
                </span>

                <strong
                    style="
                        color:
                            {{ $businessHealthColour }};
                    "
                >
                    {{ $businessHealthScore }}%
                </strong>

                <small>
                    {{ $businessHealthRating }}
                </small>

            </div>

        </article>


        {{-- Security --}}

        <article class="ag-metric-card">

            <div
                class="ag-metric-icon"
                style="
                    color: {{ $securityColour }};
                    background: {{ $securityColour }}14;
                "
            >
                <i class="fas fa-shield-halved"></i>
            </div>


            <div>

                <span>
                    Cyber Security
                </span>

                <strong
                    style="
                        color:
                            {{ $securityColour }};
                    "
                >
                    {{ $securityScore }}%
                </strong>

                <small>
                    {{ $securityRating }}
                </small>

            </div>

        </article>


        {{-- Priorities --}}

        <article class="ag-metric-card">

            <div class="ag-metric-icon ag-metric-icon--amber">

                <i class="fas fa-bullseye"></i>

            </div>


            <div>

                <span>
                    Executive Priorities
                </span>

                <strong>
                    {{ $priorityCount }}
                </strong>

                <small>
                    Items requiring attention
                </small>

            </div>

        </article>

    </section>


    {{-- ================================================================
        MODULES
    ================================================================ --}}

    <section class="ag-module-panel">

        <div class="ag-panel-heading">

            <div>

                <span class="ag-card-label">
                    Organisation Command Centre
                </span>

                <h3>
                    Business Modules
                </h3>

                <p>
                    Open the specialist BOS modules associated with this organisation.
                </p>

            </div>

        </div>


        <div class="ag-module-grid">


            {{-- Business Pulse --}}

            <a
                href="{{ route('business-pulse.workspace', $client) }}"
                class="ag-module-tile ag-module-tile--pulse"
            >

                <div class="ag-module-tile__icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>


                <div>

                    <strong>
                        Business Pulse™
                    </strong>

                    <span>
                        Review operational health,
                        resilience and readiness.
                    </span>

                </div>


                <div class="ag-module-score">

                    {{ $businessHealthScore }}%

                    <i class="fas fa-arrow-right"></i>

                </div>

            </a>


            {{-- Cyber Centre --}}

            <a
                href="{{ route('security.workspace', $client) }}"
                class="ag-module-tile ag-module-tile--security"
            >

                <div class="ag-module-tile__icon">
                    <i class="fas fa-shield-halved"></i>
                </div>


                <div>

                    <strong>
                        Cyber Centre
                    </strong>

                    <span>
                        Assess controls,
                        cyber resilience and risk.
                    </span>

                </div>


                <div class="ag-module-score">

                    {{ $securityScore }}%

                    <i class="fas fa-arrow-right"></i>

                </div>

            </a>


            {{-- Projects --}}

            <div class="ag-module-tile ag-module-tile--disabled">

                <div class="ag-module-tile__icon">
                    <i class="fas fa-diagram-project"></i>
                </div>


                <div>

                    <strong>
                        Projects
                    </strong>

                    <span>
                        Actions, owners,
                        deadlines and delivery.
                    </span>

                </div>


                <div class="ag-module-badge">
                    Soon
                </div>

            </div>


            {{-- Microsoft 365 --}}

            <div class="ag-module-tile ag-module-tile--disabled">

                <div class="ag-module-tile__icon">
                    <i class="fab fa-microsoft"></i>
                </div>


                <div>

                    <strong>
                        Microsoft 365
                    </strong>

                    <span>
                        Identity, email,
                        configuration and security.
                    </span>

                </div>


                <div class="ag-module-badge">
                    Soon
                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
        MAIN WORKSPACE GRID
    ================================================================ --}}

    <section class="ag-workspace-grid">


        {{-- ============================================================
            ORGANISATION INFORMATION
        ============================================================ --}}

        <article class="ag-info-panel">

            <div class="ag-panel-heading">

                <div>

                    <span class="ag-card-label">
                        Organisation
                    </span>

                    <h3>
                        Workspace Information
                    </h3>

                </div>

            </div>


            <div class="ag-info-list">

                <div class="ag-info-row">

                    <span>
                        Contact
                    </span>

                    <strong>
                        {{
                            $client->contact_name
                                ?: 'Not recorded'
                        }}
                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Email
                    </span>

                    <strong>

                        @if($client->email)

                            <a
                                href="mailto:{{ $client->email }}"
                            >
                                {{ $client->email }}
                            </a>

                        @else

                            Not recorded

                        @endif

                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Phone
                    </span>

                    <strong>
                        {{
                            $client->phone
                                ?: 'Not recorded'
                        }}
                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Website
                    </span>

                    <strong>

                        @if($client->website)

                            {{ $client->website }}

                        @else

                            Not recorded

                        @endif

                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Address
                    </span>

                    <strong>

                        {{
                            $client->address
                                ?: 'Not recorded'
                        }}

                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        City
                    </span>

                    <strong>
                        {{
                            $client->city
                                ?: 'Not recorded'
                        }}
                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Postcode
                    </span>

                    <strong>
                        {{
                            $client->postcode
                                ?: 'Not recorded'
                        }}
                    </strong>

                </div>


                <div class="ag-info-row">

                    <span>
                        Country
                    </span>

                    <strong>
                        {{
                            $client->country
                                ?: 'Not recorded'
                        }}
                    </strong>

                </div>

            </div>


            @if($client->notes)

                <div class="ag-org-notes">

                    <span>
                        Workspace Notes
                    </span>

                    <p>
                        {{ $client->notes }}
                    </p>

                </div>

            @endif

        </article>


        {{-- ============================================================
            EXECUTIVE PRIORITIES
        ============================================================ --}}

        <article class="ag-priority-panel">

            <div class="ag-panel-heading">

                <div>

                    <span class="ag-card-label">
                        Executive Intelligence
                    </span>

                    <h3>
                        Current Priorities
                    </h3>

                </div>

                <span class="ag-count-badge">
                    {{ $priorityCount }}
                </span>

            </div>


            <div class="ag-priority-list">

                @forelse($priorities as $priority)

                    @php

                        $severity =
                            $priority['severity']
                            ?? 'low';

                        $severityClass =
                            match ($severity) {
                                'critical' => 'critical',
                                'high' => 'high',
                                'medium' => 'medium',
                                default => 'low',
                            };

                    @endphp


                    <div class="ag-priority-item">

                        <div
                            class="
                                ag-priority-dot
                                ag-priority-dot--{{ $severityClass }}
                            "
                        ></div>


                        <div class="ag-priority-copy">

                            <div class="ag-priority-heading">

                                <strong>
                                    {{
                                        $priority['title']
                                        ?? 'Priority'
                                    }}
                                </strong>


                                <span
                                    class="
                                        ag-priority-badge
                                        ag-priority-badge--{{ $severityClass }}
                                    "
                                >
                                    {{ ucfirst($severity) }}
                                </span>

                            </div>


                            <p>
                                {{
                                    $priority['message']
                                    ?? ''
                                }}
                            </p>

                        </div>


                        <div class="ag-priority-impact">

                            <strong>
                                +{{
                                    $priority['impact']
                                    ?? 0
                                }}
                            </strong>

                            <span>
                                impact
                            </span>

                        </div>

                    </div>


                @empty

                    <div class="ag-positive-state">

                        <i class="fas fa-circle-check"></i>

                        <div>

                            <strong>
                                No immediate priorities
                            </strong>

                            <p>
                                No current Business Pulse™
                                or cyber security issues
                                require management attention.
                            </p>

                        </div>

                    </div>

                @endforelse

            </div>

        </article>

    </section>


    {{-- ================================================================
        BUSINESS HEALTH DOMAINS
    ================================================================ --}}

    <section class="ag-domain-panel">

        <div class="ag-panel-heading">

            <div>

                <span class="ag-card-label">
                    Business Pulse™ Intelligence
                </span>

                <h3>
                    Business Health Domains
                </h3>

                <p>
                    The six operational areas contributing
                    to the organisation's Business Health score.
                </p>

            </div>


            <div class="ag-overall-score">

                <span>
                    Overall
                </span>

                <strong
                    style="
                        color:
                            {{ $businessHealthColour }};
                    "
                >
                    {{ $businessHealthScore }}%
                </strong>

                <small>
                    {{ $businessHealthRating }}
                </small>

            </div>

        </div>


        <div class="ag-domain-grid">

            @forelse($businessDomains as $key => $domain)

                @php

                    $domainScore =
                        (int) (
                            $domain['score']
                            ?? 0
                        );

                    $domainLabel =
                        $domain['label']
                        ?? ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $key
                            )
                        );

                    $domainRating =
                        $ratingForScore(
                            $domainScore
                        );

                    $domainColour =
                        $colourForScore(
                            $domainScore
                        );

                @endphp


                <article class="ag-domain-card">

                    <div class="ag-domain-card__top">

                        <div
                            class="ag-domain-icon"
                            style="
                                color:
                                    {{ $domainColour }};

                                background:
                                    {{ $domainColour }}14;
                            "
                        >
                            <i class="fas fa-chart-line"></i>
                        </div>


                        <strong
                            style="
                                color:
                                    {{ $domainColour }};
                            "
                        >
                            {{ $domainScore }}%
                        </strong>

                    </div>


                    <h4>
                        {{ $domainLabel }}
                    </h4>


                    <span
                        style="
                            color:
                                {{ $domainColour }};
                        "
                    >
                        {{ $domainRating }}
                    </span>


                    <div class="ag-domain-progress">

                        <div
                            style="
                                width:
                                    {{ $domainScore }}%;

                                background:
                                    {{ $domainColour }};
                            "
                        ></div>

                    </div>

                </article>


            @empty

                <div class="ag-domain-empty">

                    Business Pulse™ assessment
                    has not yet been completed.

                </div>

            @endforelse

        </div>

    </section>


    {{-- ================================================================
        OPERATIONAL MODULE PREVIEW
    ================================================================ --}}

    <section class="ag-operational-grid">


        <article class="ag-operation-card">

            <div class="ag-operation-icon ag-operation-icon--blue">
                <i class="fas fa-diagram-project"></i>
            </div>

            <div>

                <span>
                    Active Projects
                </span>

                <strong>
                    {{ $openProjects }}
                </strong>

                <small>
                    Project module coming next
                </small>

            </div>

        </article>


        <article class="ag-operation-card">

            <div class="ag-operation-icon ag-operation-icon--green">
                <i class="fas fa-folder-open"></i>
            </div>

            <div>

                <span>
                    Documents
                </span>

                <strong>
                    {{ $documentsCount }}
                </strong>

                <small>
                    Knowledge Hub integration planned
                </small>

            </div>

        </article>


        <article class="ag-operation-card">

            <div class="ag-operation-icon ag-operation-icon--amber">
                <i class="fas fa-calendar-days"></i>
            </div>

            <div>

                <span>
                    Scheduled Activities
                </span>

                <strong>
                    {{ $appointmentsCount }}
                </strong>

                <small>
                    Planner integration planned
                </small>

            </div>

        </article>

    </section>

</div>


<style>

/* ================================================================
   WORKSPACE
================================================================ */

.ag-org-workspace {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/* ================================================================
   HERO
================================================================ */

.ag-org-hero {
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
.ag-card-label {
    display: block;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: .1em;

    text-transform: uppercase;
}

.ag-hero-kicker {
    color: #93c5fd;
}

.ag-card-label {
    color: #64748b;
}

.ag-org-title-row {
    display: flex;
    align-items: center;

    flex-wrap: wrap;

    gap: 12px;
}

.ag-org-title-row h2 {
    margin: 7px 0 11px;

    font-size: 31px;
}

.ag-org-meta {
    display: flex;
    flex-wrap: wrap;

    gap: 18px;

    color: #cbd5e1;

    font-size: 13px;
}

.ag-org-meta span {
    display: inline-flex;
    align-items: center;

    gap: 7px;
}


/* ================================================================
   STATUS
================================================================ */

.ag-status {
    display: inline-flex;

    padding: 5px 9px;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
}

.ag-status--active {
    color: #047857;
    background: #d1fae5;
}

.ag-status--lead {
    color: #92400e;
    background: #fef3c7;
}

.ag-status--inactive {
    color: #991b1b;
    background: #fee2e2;
}


/* ================================================================
   HERO ACTIONS
================================================================ */

.ag-hero-actions {
    display: flex;
    gap: 10px;
}

.ag-hero-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 13px 16px;

    border-radius: 12px;

    font-weight: 750;

    text-decoration: none;

    transition: .2s ease;
}

.ag-hero-button:hover {
    transform: translateY(-2px);

    text-decoration: none;
}

.ag-hero-button--light {
    color: #0f172a;

    background: #ffffff;
}

.ag-hero-button--secondary {
    color: #ffffff;

    border:
        1px solid
        rgba(255, 255, 255, .25);

    background:
        rgba(255, 255, 255, .08);
}


/* ================================================================
   SCORE GRID
================================================================ */

.ag-score-grid {
    display: grid;

    grid-template-columns:
        minmax(300px, 1.4fr)
        repeat(3, minmax(180px, .6fr));

    gap: 17px;
}

.ag-score-card,
.ag-metric-card,
.ag-module-panel,
.ag-info-panel,
.ag-priority-panel,
.ag-domain-panel,
.ag-operation-card {
    border:
        1px solid
        #e5e7eb;

    background:
        #ffffff;

    box-shadow:
        0 10px 28px
        rgba(15, 23, 42, .05);
}

.ag-score-card {
    display: flex;
    align-items: center;

    gap: 20px;

    padding: 20px;

    border-radius: 18px;
}


/* ================================================================
   SCORE RING
================================================================ */

.ag-score-ring {
    width: 112px;
    height: 112px;

    flex: 0 0 112px;

    position: relative;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background:
        conic-gradient(
            var(--score-colour)
            calc(var(--score) * 1%),
            #e5e7eb 0
        );
}

.ag-score-ring::before {
    content: "";

    position: absolute;

    width: 84px;
    height: 84px;

    border-radius: 50%;

    background: #ffffff;
}

.ag-score-ring__centre {
    position: relative;

    z-index: 1;

    text-align: center;
}

.ag-score-ring__centre strong,
.ag-score-ring__centre span {
    display: block;
}

.ag-score-ring__centre strong {
    color: #0f172a;

    font-size: 25px;
}

.ag-score-ring__centre span {
    margin-top: 3px;

    color: #64748b;

    font-size: 10px;
    font-weight: 750;
}

.ag-score-card__copy h3 {
    margin: 5px 0 6px;

    color: #0f172a;

    font-size: 20px;
}

.ag-score-card__copy p {
    margin: 0;

    color: #64748b;

    font-size: 13px;

    line-height: 1.5;
}


/* ================================================================
   METRIC CARDS
================================================================ */

.ag-metric-card {
    display: flex;
    align-items: center;

    gap: 13px;

    padding: 20px;

    border-radius: 18px;
}

.ag-metric-icon {
    width: 48px;
    height: 48px;

    flex: 0 0 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;
}

.ag-metric-icon--amber {
    color: #d97706;

    background: #fef3c7;
}

.ag-metric-card span,
.ag-metric-card strong,
.ag-metric-card small {
    display: block;
}

.ag-metric-card span {
    color: #64748b;

    font-size: 12px;
}

.ag-metric-card strong {
    margin: 2px 0;

    color: #0f172a;

    font-size: 24px;
}

.ag-metric-card small {
    color: #94a3b8;

    font-size: 11px;
}


/* ================================================================
   PANELS
================================================================ */

.ag-module-panel,
.ag-info-panel,
.ag-priority-panel,
.ag-domain-panel {
    padding: 24px;

    border-radius: 19px;
}

.ag-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 18px;

    margin-bottom: 20px;
}

.ag-panel-heading h3 {
    margin: 5px 0 6px;

    color: #0f172a;

    font-size: 21px;
}

.ag-panel-heading p {
    margin: 0;

    color: #64748b;

    line-height: 1.55;
}


/* ================================================================
   MODULE GRID
================================================================ */

.ag-module-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;
}

.ag-module-tile {
    min-width: 0;

    display: grid;

    grid-template-columns:
        46px
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 12px;

    padding: 15px;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    color: inherit;

    background: #ffffff;

    text-decoration: none;

    transition:
        transform .18s ease,
        box-shadow .18s ease,
        border-color .18s ease;
}

a.ag-module-tile:hover {
    color: inherit;

    border-color: #cbd5e1;

    text-decoration: none;

    transform: translateY(-2px);

    box-shadow:
        0 10px 22px
        rgba(15, 23, 42, .06);
}

.ag-module-tile__icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;
}

.ag-module-tile--pulse
.ag-module-tile__icon {
    color: #059669;

    background: #d1fae5;
}

.ag-module-tile--security
.ag-module-tile__icon {
    color: #2563eb;

    background: #dbeafe;
}

.ag-module-tile--disabled
.ag-module-tile__icon {
    color: #64748b;

    background: #f1f5f9;
}

.ag-module-tile strong,
.ag-module-tile span {
    display: block;
}

.ag-module-tile strong {
    color: #0f172a;

    font-size: 13px;
}

.ag-module-tile span {
    margin-top: 3px;

    color: #94a3b8;

    font-size: 10px;

    line-height: 1.4;
}

.ag-module-score {
    display: flex;
    align-items: center;

    gap: 8px;

    color: #2563eb;

    font-size: 15px;
    font-weight: 800;
}

.ag-module-badge {
    padding: 5px 8px;

    border-radius: 999px;

    color: #64748b;

    background: #f1f5f9;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
}


/* ================================================================
   WORKSPACE GRID
================================================================ */

.ag-workspace-grid {
    display: grid;

    grid-template-columns:
        minmax(0, .85fr)
        minmax(0, 1.15fr);

    gap: 18px;
}


/* ================================================================
   INFORMATION
================================================================ */

.ag-info-list {
    border-top:
        1px solid
        #eef2f7;
}

.ag-info-row {
    display: grid;

    grid-template-columns:
        120px
        minmax(0, 1fr);

    gap: 15px;

    padding: 13px 0;

    border-bottom:
        1px solid
        #eef2f7;
}

.ag-info-row span {
    color: #64748b;

    font-size: 12px;
}

.ag-info-row strong {
    color: #0f172a;

    font-size: 13px;
}

.ag-info-row a {
    color: #2563eb;

    text-decoration: none;
}

.ag-org-notes {
    margin-top: 18px;

    padding: 15px;

    border-radius: 13px;

    background: #f8fafc;
}

.ag-org-notes span {
    color: #64748b;

    font-size: 11px;
    font-weight: 750;
}

.ag-org-notes p {
    margin: 6px 0 0;

    color: #334155;

    line-height: 1.55;
}


/* ================================================================
   PRIORITIES
================================================================ */

.ag-count-badge {
    min-width: 32px;
    height: 32px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    color: #ffffff;

    background: #2563eb;

    font-size: 12px;
    font-weight: 800;
}

.ag-priority-item {
    display: grid;

    grid-template-columns:
        10px
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 13px;

    padding: 15px 0;

    border-top:
        1px solid
        #eef2f7;
}

.ag-priority-item:first-child {
    border-top: 0;
}

.ag-priority-dot {
    width: 9px;
    height: 9px;

    border-radius: 50%;
}

.ag-priority-dot--critical {
    background: #dc2626;
}

.ag-priority-dot--high {
    background: #f97316;
}

.ag-priority-dot--medium {
    background: #f59e0b;
}

.ag-priority-dot--low {
    background: #2563eb;
}

.ag-priority-heading {
    display: flex;
    align-items: center;

    flex-wrap: wrap;

    gap: 8px;
}

.ag-priority-copy strong {
    color: #0f172a;
}

.ag-priority-copy p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 12px;
}

.ag-priority-badge {
    padding: 4px 7px;

    border-radius: 999px;

    font-size: 9px;
    font-weight: 800;

    text-transform: uppercase;
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

.ag-priority-impact {
    text-align: right;
}

.ag-priority-impact strong,
.ag-priority-impact span {
    display: block;
}

.ag-priority-impact strong {
    color: #2563eb;

    font-size: 16px;
}

.ag-priority-impact span {
    color: #94a3b8;

    font-size: 9px;
}

.ag-positive-state {
    display: flex;
    align-items: flex-start;

    gap: 12px;

    padding: 15px 0;

    color: #059669;
}

.ag-positive-state p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 12px;
}


/* ================================================================
   DOMAINS
================================================================ */

.ag-overall-score {
    min-width: 120px;

    padding: 11px 15px;

    border-radius: 13px;

    text-align: right;

    background: #f8fafc;
}

.ag-overall-score span,
.ag-overall-score strong,
.ag-overall-score small {
    display: block;
}

.ag-overall-score span,
.ag-overall-score small {
    color: #64748b;

    font-size: 10px;
}

.ag-overall-score strong {
    margin: 2px 0;

    font-size: 23px;
}

.ag-domain-grid {
    display: grid;

    grid-template-columns:
        repeat(6, minmax(0, 1fr));

    gap: 11px;
}

.ag-domain-card {
    padding: 15px;

    border: 1px solid #e5e7eb;

    border-radius: 14px;
}

.ag-domain-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;
}

.ag-domain-icon {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;
}

.ag-domain-card__top strong {
    font-size: 19px;
}

.ag-domain-card h4 {
    margin: 13px 0 3px;

    color: #0f172a;

    font-size: 13px;
}

.ag-domain-card > span {
    font-size: 10px;
    font-weight: 750;
}

.ag-domain-progress {
    width: 100%;
    height: 6px;

    overflow: hidden;

    margin-top: 12px;

    border-radius: 999px;

    background: #e5e7eb;
}

.ag-domain-progress div {
    height: 100%;

    border-radius: inherit;
}

.ag-domain-empty {
    grid-column: 1 / -1;

    padding: 22px;

    border-radius: 13px;

    color: #64748b;

    background: #f8fafc;

    text-align: center;
}


/* ================================================================
   OPERATIONS
================================================================ */

.ag-operational-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 17px;
}

.ag-operation-card {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 18px;

    border-radius: 16px;
}

.ag-operation-icon {
    width: 46px;
    height: 46px;

    flex: 0 0 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;
}

.ag-operation-icon--blue {
    color: #2563eb;

    background: #dbeafe;
}

.ag-operation-icon--green {
    color: #059669;

    background: #d1fae5;
}

.ag-operation-icon--amber {
    color: #d97706;

    background: #fef3c7;
}

.ag-operation-card span,
.ag-operation-card strong,
.ag-operation-card small {
    display: block;
}

.ag-operation-card span {
    color: #64748b;

    font-size: 12px;
}

.ag-operation-card strong {
    margin: 2px 0;

    color: #0f172a;

    font-size: 23px;
}

.ag-operation-card small {
    color: #94a3b8;

    font-size: 10px;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1300px) {

    .ag-score-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

    .ag-score-card {
        grid-column: 1 / -1;
    }

    .ag-module-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .ag-domain-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


@media (max-width: 950px) {

    .ag-workspace-grid {
        grid-template-columns: 1fr;
    }

    .ag-operational-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .ag-org-hero {
        align-items: flex-start;

        flex-direction: column;
    }

    .ag-hero-actions {
        width: 100%;

        flex-direction: column;
    }

    .ag-score-grid,
    .ag-module-grid,
    .ag-domain-grid {
        grid-template-columns: 1fr;
    }

    .ag-score-card {
        align-items: flex-start;

        flex-direction: column;
    }

    .ag-info-row {
        grid-template-columns: 1fr;
        gap: 4px;
    }

}

</style>

@endsection