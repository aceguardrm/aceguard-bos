@extends('layouts.aceguard')

@section('title', 'Executive Dashboard')

@section('page-title', 'Executive Dashboard')

@section(
    'page-subtitle',
    'Your business health, cyber posture and executive priorities.'
)

@section('content')

@php
    $clientCount = \App\Models\Client::count();

    /*
    |--------------------------------------------------------------------------
    | Business Health
    |--------------------------------------------------------------------------
    |
    | Prefer the database-backed Business Pulse™ assessment score.
    | Fall back to the original BusinessHealthService value for older
    | workspaces that do not yet have an assessment.
    |
    */

    $businessScore = $pulse['components']['business_health']['score']
        ?? ($health['overall'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    $securityScore = $security['score'] ?? 0;
    $securityRating = $security['rating'] ?? 'Not assessed';

    /*
    |--------------------------------------------------------------------------
    | Business Pulse™
    |--------------------------------------------------------------------------
    */

    $pulseScore = $pulse['score'] ?? 0;
    $pulseRating = $pulse['rating'] ?? 'Not assessed';

    $pulseSummary = $pulse['summary']
        ?? 'Business Pulse™ has not yet been calculated.';

    $pulsePriorities = collect(
        $pulse['priorities'] ?? []
    );

    /*
    |--------------------------------------------------------------------------
    | Business Health Domains
    |--------------------------------------------------------------------------
    */

    $businessDomains = collect(
        $pulse['business_domains'] ?? []
    );

    $domainIcons = [
        'operations' => 'fa-gears',
        'continuity' => 'fa-arrows-rotate',
        'documentation' => 'fa-file-lines',
        'compliance' => 'fa-scale-balanced',
        'technology' => 'fa-laptop-code',
        'readiness' => 'fa-bolt',
    ];

    /*
    |--------------------------------------------------------------------------
    | Pulse Components
    |--------------------------------------------------------------------------
    */

    $securityComponent = $pulse['components']['security'] ?? [
        'label' => 'Security',
        'score' => 0,
        'weight' => 0,
    ];

    $businessHealthComponent =
        $pulse['components']['business_health'] ?? [
            'label' => 'Business Health',
            'score' => 0,
            'weight' => 0,
        ];

    /*
    |--------------------------------------------------------------------------
    | Business Health Rating
    |--------------------------------------------------------------------------
    */

    $businessRating = match (true) {
        $businessScore >= 90 => 'Excellent',
        $businessScore >= 75 => 'Healthy',
        $businessScore >= 60 => 'Watch',
        $businessScore >= 40 => 'At Risk',
        default => 'Critical',
    };

    /*
    |--------------------------------------------------------------------------
    | Colours
    |--------------------------------------------------------------------------
    */

    $pulseColour = match (true) {
        $pulseScore >= 90 => '#10b981',
        $pulseScore >= 75 => '#2563eb',
        $pulseScore >= 60 => '#f59e0b',
        $pulseScore >= 40 => '#f97316',
        default => '#dc2626',
    };

    $securityColour = match (true) {
        $securityScore >= 90 => '#10b981',
        $securityScore >= 75 => '#2563eb',
        $securityScore >= 60 => '#f59e0b',
        $securityScore >= 40 => '#f97316',
        default => '#dc2626',
    };

    $businessColour = match (true) {
        $businessScore >= 90 => '#10b981',
        $businessScore >= 75 => '#2563eb',
        $businessScore >= 60 => '#f59e0b',
        $businessScore >= 40 => '#f97316',
        default => '#dc2626',
    };
@endphp

<div class="ag-dashboard">

    {{-- ================================================================
        EXECUTIVE HERO
    ================================================================ --}}

    <section class="ag-hero">

        <div>
            <span class="ag-kicker">
                AceGuard BOS Intelligence
            </span>

            <h2>
                Welcome back,
                {{ Auth::user()->name ?? 'Administrator' }}.
            </h2>

            <p>
                Here is what needs your attention across the business today.
            </p>
        </div>

        <a
            href="{{ route('clients.create') }}"
            class="ag-button ag-button--light"
        >
            <i class="fas fa-plus"></i>
            New Workspace
        </a>

    </section>


    {{-- ================================================================
        BUSINESS PULSE™
    ================================================================ --}}

    <section class="ag-pulse-hero">

        <div class="ag-pulse-hero__left">

            <div
                class="ag-pulse-ring"
                style="
                    --pulse-score: {{ $pulseScore }};
                    --pulse-colour: {{ $pulseColour }};
                "
            >
                <div class="ag-pulse-ring__centre">
                    <strong>{{ $pulseScore }}%</strong>
                    <span>{{ $pulseRating }}</span>
                </div>
            </div>

            <div class="ag-pulse-copy">

                <span class="ag-kicker ag-kicker--dark">
                    Business Pulse™
                </span>

                <h3>
                    {{ $pulseRating }} business condition
                </h3>

                <p>
                    {{ $pulseSummary }}
                </p>

                <div class="ag-progress">
                    <div
                        class="ag-progress__value"
                        style="
                            width: {{ $pulseScore }}%;
                            background: {{ $pulseColour }};
                        "
                    ></div>
                </div>

            </div>

        </div>

        <div class="ag-pulse-hero__right">

            <div class="ag-pulse-component">

                <div>
                    <span>
                        {{ $securityComponent['label'] }}
                    </span>

                    <small>
                        Weight:
                        {{ $securityComponent['weight'] }}%
                    </small>
                </div>

                <strong>
                    {{ $securityComponent['score'] }}%
                </strong>

            </div>

            <div class="ag-pulse-component">

                <div>
                    <span>
                        {{ $businessHealthComponent['label'] }}
                    </span>

                    <small>
                        Weight:
                        {{ $businessHealthComponent['weight'] }}%
                    </small>
                </div>

                <strong>
                    {{ $businessHealthComponent['score'] }}%
                </strong>

            </div>

        </div>

    </section>


    {{-- ================================================================
        CORE SCORES
    ================================================================ --}}

    <section class="ag-score-grid">

        {{-- Business Health --}}

        <article class="ag-card">

            <div class="ag-card-header">

                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Operational Health
                    </span>

                    <h3>
                        Overall Business Health
                    </h3>
                </div>

                <span
                    class="ag-business-rating"
                    style="
                        color: {{ $businessColour }};
                        background: {{ $businessColour }}14;
                    "
                >
                    {{ $businessRating }}
                </span>

            </div>

            <div class="ag-score-layout">

                <div
                    class="ag-large-score"
                    style="color: {{ $businessColour }};"
                >
                    {{ $businessScore }}%
                </div>

                <div class="ag-score-description">

                    <strong>
                        {{ $businessRating }} business health.
                    </strong>

                    <p>
                        This score is calculated from Operations,
                        Continuity, Documentation, Compliance,
                        Technology and Readiness.
                    </p>

                    <div class="ag-progress">
                        <div
                            class="ag-progress__value"
                            style="
                                width: {{ $businessScore }}%;
                                background: {{ $businessColour }};
                            "
                        ></div>
                    </div>

                </div>

            </div>

        </article>


        {{-- Security --}}

        <article class="ag-card">

            <div class="ag-card-header">

                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Cyber Resilience
                    </span>

                    <h3>
                        Business Security Score
                    </h3>
                </div>

                <div class="ag-shield-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>

            </div>

            <div class="ag-security-layout">

                <div
                    class="ag-security-ring"
                    style="
                        --security-score: {{ $securityScore }};
                        --security-colour: {{ $securityColour }};
                    "
                >

                    <div class="ag-security-ring__centre">
                        <strong>
                            {{ $securityScore }}%
                        </strong>

                        <span>
                            {{ $securityRating }}
                        </span>
                    </div>

                </div>

                <div class="ag-score-description">

                    <strong>
                        {{ $security['earned'] ?? 0 }}
                        of
                        {{ $security['maximum'] ?? 0 }}
                        security points achieved
                    </strong>

                    <p>
                        Your current score reflects the organisation's
                        assessed cyber security controls.
                    </p>

                    <div class="ag-progress">
                        <div
                            class="ag-progress__value"
                            style="
                                width: {{ $securityScore }}%;
                                background: {{ $securityColour }};
                            "
                        ></div>
                    </div>

                </div>

            </div>

        </article>

    </section>


    {{-- ================================================================
        BUSINESS HEALTH DOMAIN INTELLIGENCE
    ================================================================ --}}

    <section class="ag-domain-section">

        <div class="ag-domain-section__header">

            <div>
                <span class="ag-kicker ag-kicker--dark">
                    Business Pulse™ Intelligence
                </span>

                <h3>
                    Business Health Domains
                </h3>

                <p>
                    Six operational domains explain the organisation's
                    Business Health score and show exactly where
                    management attention may be required.
                </p>
            </div>

            <div class="ag-domain-overall">

                <span>
                    Business Health
                </span>

                <strong
                    style="color: {{ $businessColour }};"
                >
                    {{ $businessScore }}%
                </strong>

                <small>
                    {{ $businessRating }}
                </small>

            </div>

        </div>


        @if($businessDomains->isNotEmpty())

            <div class="ag-domain-grid">

                @foreach($businessDomains as $key => $domain)

                    @php
                        $domainScore =
                            (int) ($domain['score'] ?? 0);

                        $domainLabel =
                            $domain['label']
                            ?? ucfirst($key);

                        $domainIcon =
                            $domainIcons[$key]
                            ?? 'fa-chart-line';

                        $domainColour = match (true) {
                            $domainScore >= 90 => '#10b981',
                            $domainScore >= 75 => '#2563eb',
                            $domainScore >= 60 => '#f59e0b',
                            $domainScore >= 40 => '#f97316',
                            default => '#dc2626',
                        };

                        $domainRating = match (true) {
                            $domainScore >= 90 => 'Excellent',
                            $domainScore >= 75 => 'Healthy',
                            $domainScore >= 60 => 'Watch',
                            $domainScore >= 40 => 'At Risk',
                            default => 'Critical',
                        };
                    @endphp


                    <article class="ag-domain-card">

                        <div class="ag-domain-card__top">

                            <div
                                class="ag-domain-icon"
                                style="
                                    color: {{ $domainColour }};
                                    background:
                                        {{ $domainColour }}14;
                                "
                            >
                                <i
                                    class="fas {{ $domainIcon }}"
                                ></i>
                            </div>

                            <div
                                class="ag-domain-score"
                                style="
                                    color: {{ $domainColour }};
                                "
                            >
                                {{ $domainScore }}%
                            </div>

                        </div>


                        <div class="ag-domain-card__body">

                            <h4>
                                {{ $domainLabel }}
                            </h4>

                            <span
                                class="ag-domain-rating"
                                style="
                                    color: {{ $domainColour }};
                                "
                            >
                                {{ $domainRating }}
                            </span>

                            <div class="ag-domain-progress">

                                <div
                                    class="ag-domain-progress__value"
                                    style="
                                        width:
                                            {{ $domainScore }}%;
                                        background:
                                            {{ $domainColour }};
                                    "
                                ></div>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        @else

            <div class="ag-domain-empty">

                <i class="fas fa-chart-pie"></i>

                <div>
                    <strong>
                        No Business Pulse™ assessment available
                    </strong>

                    <p>
                        Complete a Business Pulse™ assessment to
                        generate domain intelligence.
                    </p>
                </div>

            </div>

        @endif

    </section>


    {{-- ================================================================
        QUICK METRICS
    ================================================================ --}}

    <section class="ag-stat-grid">

        <a
            href="{{ route('clients.index') }}"
            class="ag-stat-card"
        >

            <div class="ag-stat-icon ag-stat-icon--blue">
                <i class="fas fa-building"></i>
            </div>

            <div>
                <span>
                    Workspaces
                </span>

                <strong>
                    {{ $clientCount }}
                </strong>

                <small>
                    Open organisation workspaces
                </small>
            </div>

        </a>


        <article class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--green">
                <i class="fas fa-heart-pulse"></i>
            </div>

            <div>
                <span>
                    Business Pulse™
                </span>

                <strong>
                    {{ $pulseScore }}%
                </strong>

                <small>
                    {{ $pulseRating }}
                </small>
            </div>

        </article>


        <article class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--purple">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <span>
                    Security Score
                </span>

                <strong>
                    {{ $securityScore }}%
                </strong>

                <small>
                    {{ $securityRating }}
                </small>
            </div>

        </article>


        <article class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--amber">
                <i class="fas fa-list-check"></i>
            </div>

            <div>
                <span>
                    Executive Priorities
                </span>

                <strong>
                    {{ $pulse['priority_count'] ?? 0 }}
                </strong>

                <small>
                    Items requiring attention
                </small>
            </div>

        </article>

    </section>


    {{-- ================================================================
        EXECUTIVE INTELLIGENCE
    ================================================================ --}}

    <section class="ag-lower-grid">


        {{-- Today's Priorities --}}

        <article class="ag-card">

            <div class="ag-card-header">

                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Executive Intelligence
                    </span>

                    <h3>
                        Today's Priorities
                    </h3>
                </div>

                <span class="ag-priority-count">
                    {{ $pulsePriorities->count() }}
                </span>

            </div>


            <div class="ag-priority-list">

                @forelse($pulsePriorities as $priority)

                    @php
                        $severity =
                            $priority['severity']
                            ?? 'low';

                        $severityClass =
                            match ($severity) {
                                'critical' =>
                                    'ag-severity--critical',

                                'high' =>
                                    'ag-severity--high',

                                'medium' =>
                                    'ag-severity--medium',

                                default =>
                                    'ag-severity--low',
                            };
                    @endphp


                    <div class="ag-priority-item">

                        <div
                            class="
                                ag-priority-marker
                                {{ $severityClass }}
                            "
                        ></div>


                        <div class="ag-priority-copy">

                            <div
                                class="
                                    ag-priority-title-row
                                "
                            >

                                <strong>
                                    {{ $priority['title'] }}
                                </strong>

                                <span
                                    class="
                                        ag-severity-badge
                                        {{ $severityClass }}
                                    "
                                >
                                    {{ ucfirst($severity) }}
                                </span>

                            </div>


                            <p>
                                {{ $priority['message'] }}
                            </p>

                        </div>


                        <div class="ag-priority-impact">

                            <strong>
                                +{{ $priority['impact'] ?? 0 }}
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
                                Business Pulse™ has detected no
                                current issues requiring management
                                attention.
                            </p>

                        </div>

                    </div>

                @endforelse

            </div>

        </article>


        {{-- Executive Brief --}}

        <article class="ag-card">

            <div class="ag-card-header">

                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Executive Brief
                    </span>

                    <h3>
                        Today's Business Summary
                    </h3>
                </div>

            </div>


            <div class="ag-brief">

                <div class="ag-brief-item">

                    <div
                        class="
                            ag-brief-icon
                            ag-brief-icon--green
                        "
                    >
                        <i class="fas fa-heart-pulse"></i>
                    </div>

                    <div>

                        <strong>
                            Business Pulse™
                        </strong>

                        <p>
                            Current overall business condition:
                            {{ $pulseScore }}%
                            — {{ $pulseRating }}.
                        </p>

                    </div>

                </div>


                <div class="ag-brief-item">

                    <div
                        class="
                            ag-brief-icon
                            ag-brief-icon--blue
                        "
                    >
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <div>

                        <strong>
                            Business Health
                        </strong>

                        <p>
                            Database-backed operational health:
                            {{ $businessScore }}%
                            — {{ $businessRating }}.
                        </p>

                    </div>

                </div>


                <div class="ag-brief-item">

                    <div
                        class="
                            ag-brief-icon
                            ag-brief-icon--purple
                        "
                    >
                        <i class="fas fa-shield-halved"></i>
                    </div>

                    <div>

                        <strong>
                            Cyber Security
                        </strong>

                        <p>
                            Current security posture:
                            {{ $securityScore }}%
                            — {{ $securityRating }}.
                        </p>

                    </div>

                </div>


                <div class="ag-brief-item">

                    <div
                        class="
                            ag-brief-icon
                            ag-brief-icon--blue
                        "
                    >
                        <i class="fas fa-building"></i>
                    </div>

                    <div>

                        <strong>
                            Active Workspaces
                        </strong>

                        <p>
                            {{ $clientCount }}

                            {{ \Illuminate\Support\Str::plural(
                                'organisation workspace',
                                $clientCount
                            ) }}

                            currently registered.
                        </p>

                    </div>

                </div>


                <div class="ag-brief-item">

                    <div
                        class="
                            ag-brief-icon
                            ag-brief-icon--amber
                        "
                    >
                        <i class="fas fa-bullseye"></i>
                    </div>

                    <div>

                        <strong>
                            Management Attention
                        </strong>

                        <p>
                            {{ $pulse['priority_count'] ?? 0 }}

                            priority

                            {{
                                ($pulse['priority_count'] ?? 0)
                                    === 1
                                    ? 'item requires'
                                    : 'items require'
                            }}

                            attention.
                        </p>

                    </div>

                </div>

            </div>

        </article>

    </section>

</div>


<style>

/* ================================================================
   DASHBOARD
================================================================ */

.ag-dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/* ================================================================
   HERO
================================================================ */

.ag-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 32px;
    overflow: hidden;
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

.ag-hero h2 {
    margin: 5px 0 0;
    font-size: 29px;
}

.ag-hero p {
    margin: 10px 0 0;
    color: #cbd5e1;
}


/* ================================================================
   TYPOGRAPHY
================================================================ */

.ag-kicker {
    display: block;
    color: #93c5fd;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.ag-kicker--dark {
    color: #64748b;
}


/* ================================================================
   BUTTONS
================================================================ */

.ag-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 13px 18px;
    border-radius: 12px;
    font-weight: 750;
    text-decoration: none;
    transition: .2s ease;
}

.ag-button--light {
    color: #0f172a;
    background: #ffffff;
}

.ag-button:hover {
    color: inherit;
    transform: translateY(-2px);
}


/* ================================================================
   BUSINESS PULSE
================================================================ */

.ag-pulse-hero {
    display: grid;
    grid-template-columns:
        minmax(0, 1.5fr)
        minmax(280px, .7fr);
    gap: 24px;
    padding: 26px;
    border: 1px solid #dbeafe;
    border-radius: 20px;
    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #f8fbff 100%
        );
    box-shadow:
        0 14px 34px
        rgba(15, 23, 42, .06);
}

.ag-pulse-hero__left {
    display: grid;
    grid-template-columns: 155px 1fr;
    align-items: center;
    gap: 26px;
}


/* ================================================================
   RINGS
================================================================ */

.ag-pulse-ring,
.ag-security-ring {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.ag-pulse-ring {
    width: 150px;
    height: 150px;
    background:
        conic-gradient(
            var(--pulse-colour)
            calc(var(--pulse-score) * 1%),
            #e5e7eb 0
        );
}

.ag-pulse-ring::before,
.ag-security-ring::before {
    content: "";
    position: absolute;
    border-radius: 50%;
    background: #ffffff;
}

.ag-pulse-ring::before {
    width: 116px;
    height: 116px;
}

.ag-pulse-ring__centre,
.ag-security-ring__centre {
    position: relative;
    z-index: 1;
    text-align: center;
}

.ag-pulse-ring__centre strong,
.ag-pulse-ring__centre span,
.ag-security-ring__centre strong,
.ag-security-ring__centre span {
    display: block;
}

.ag-pulse-ring__centre strong {
    color: #0f172a;
    font-size: 34px;
}

.ag-pulse-ring__centre span {
    margin-top: 4px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}


/* ================================================================
   PULSE COPY
================================================================ */

.ag-pulse-copy h3 {
    margin: 5px 0 8px;
    color: #0f172a;
    font-size: 23px;
}

.ag-pulse-copy p {
    margin: 0 0 18px;
    color: #64748b;
    line-height: 1.65;
}

.ag-pulse-hero__right {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 13px;
}

.ag-pulse-component {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 17px 18px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #ffffff;
}

.ag-pulse-component span,
.ag-pulse-component small {
    display: block;
}

.ag-pulse-component span {
    color: #0f172a;
    font-weight: 700;
}

.ag-pulse-component small {
    margin-top: 3px;
    color: #94a3b8;
}

.ag-pulse-component strong {
    color: #2563eb;
    font-size: 24px;
}


/* ================================================================
   SCORE CARDS
================================================================ */

.ag-score-grid {
    display: grid;
    grid-template-columns:
        repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.ag-card {
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    box-shadow:
        0 12px 30px
        rgba(15, 23, 42, .05);
}

.ag-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
}

.ag-card-header h3 {
    margin: 5px 0 0;
    color: #0f172a;
    font-size: 21px;
}

.ag-business-rating {
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.ag-score-layout,
.ag-security-layout {
    display: grid;
    align-items: center;
    gap: 24px;
}

.ag-score-layout {
    grid-template-columns: 150px 1fr;
}

.ag-security-layout {
    grid-template-columns: 155px 1fr;
}

.ag-large-score {
    font-size: 54px;
    font-weight: 850;
}

.ag-score-description strong {
    color: #0f172a;
}

.ag-score-description p {
    margin: 8px 0 18px;
    color: #64748b;
    line-height: 1.6;
}


/* ================================================================
   SECURITY RING
================================================================ */

.ag-security-ring {
    width: 145px;
    height: 145px;
    background:
        conic-gradient(
            var(--security-colour)
            calc(var(--security-score) * 1%),
            #e5e7eb 0
        );
}

.ag-security-ring::before {
    width: 112px;
    height: 112px;
}

.ag-security-ring__centre strong {
    color: #0f172a;
    font-size: 31px;
}

.ag-security-ring__centre span {
    margin-top: 4px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.ag-shield-icon {
    width: 47px;
    height: 47px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    color: #2563eb;
    background: #eff6ff;
    font-size: 20px;
}


/* ================================================================
   PROGRESS
================================================================ */

.ag-progress {
    width: 100%;
    height: 10px;
    overflow: hidden;
    border-radius: 999px;
    background: #e5e7eb;
}

.ag-progress__value {
    height: 100%;
    border-radius: inherit;
    transition:
        width .45s ease,
        background .45s ease;
}


/* ================================================================
   BUSINESS DOMAINS
================================================================ */

.ag-domain-section {
    padding: 25px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    background: #ffffff;
    box-shadow:
        0 12px 30px
        rgba(15, 23, 42, .05);
}

.ag-domain-section__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 23px;
}

.ag-domain-section__header h3 {
    margin: 5px 0 7px;
    color: #0f172a;
    font-size: 22px;
}

.ag-domain-section__header p {
    max-width: 720px;
    margin: 0;
    color: #64748b;
    line-height: 1.6;
}

.ag-domain-overall {
    min-width: 155px;
    padding: 15px 18px;
    border-radius: 14px;
    background: #f8fafc;
    text-align: right;
}

.ag-domain-overall span,
.ag-domain-overall strong,
.ag-domain-overall small {
    display: block;
}

.ag-domain-overall span {
    color: #64748b;
    font-size: 12px;
}

.ag-domain-overall strong {
    margin-top: 3px;
    font-size: 27px;
}

.ag-domain-overall small {
    margin-top: 2px;
    color: #94a3b8;
}

.ag-domain-grid {
    display: grid;
    grid-template-columns:
        repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.ag-domain-card {
    padding: 19px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #ffffff;
    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;
}

.ag-domain-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow:
        0 12px 25px
        rgba(15, 23, 42, .07);
}

.ag-domain-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.ag-domain-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    font-size: 18px;
}

.ag-domain-score {
    font-size: 25px;
    font-weight: 850;
}

.ag-domain-card__body h4 {
    margin: 15px 0 3px;
    color: #0f172a;
    font-size: 16px;
}

.ag-domain-rating {
    display: block;
    margin-bottom: 13px;
    font-size: 12px;
    font-weight: 750;
}

.ag-domain-progress {
    width: 100%;
    height: 7px;
    overflow: hidden;
    border-radius: 999px;
    background: #e5e7eb;
}

.ag-domain-progress__value {
    height: 100%;
    border-radius: inherit;
    transition: width .45s ease;
}

.ag-domain-empty {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 24px;
    border-radius: 15px;
    color: #64748b;
    background: #f8fafc;
}

.ag-domain-empty > i {
    color: #2563eb;
    font-size: 25px;
}

.ag-domain-empty strong {
    color: #0f172a;
}

.ag-domain-empty p {
    margin: 4px 0 0;
}


/* ================================================================
   QUICK METRICS
================================================================ */

.ag-stat-grid {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.ag-stat-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    color: inherit;
    background: #ffffff;
    text-decoration: none;
    box-shadow:
        0 8px 22px
        rgba(15, 23, 42, .04);
    transition: .2s ease;
}

.ag-stat-card:hover {
    color: inherit;
    transform: translateY(-3px);
    box-shadow:
        0 14px 28px
        rgba(15, 23, 42, .08);
}

.ag-stat-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 18px;
}

.ag-stat-icon--blue {
    color: #2563eb;
    background: #dbeafe;
}

.ag-stat-icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-stat-icon--purple {
    color: #7c3aed;
    background: #ede9fe;
}

.ag-stat-icon--amber {
    color: #d97706;
    background: #fef3c7;
}

.ag-stat-card span,
.ag-stat-card strong,
.ag-stat-card small {
    display: block;
}

.ag-stat-card span {
    color: #64748b;
    font-size: 13px;
}

.ag-stat-card strong {
    margin: 3px 0;
    color: #0f172a;
    font-size: 25px;
}

.ag-stat-card small {
    color: #94a3b8;
}


/* ================================================================
   LOWER EXECUTIVE GRID
================================================================ */

.ag-lower-grid {
    display: grid;
    grid-template-columns:
        1.25fr .75fr;
    gap: 24px;
}


/* ================================================================
   PRIORITIES
================================================================ */

.ag-priority-count {
    width: 31px;
    height: 31px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #ffffff;
    background: #2563eb;
    font-size: 13px;
    font-weight: 800;
}

.ag-priority-item {
    display: grid;
    grid-template-columns:
        12px 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 18px 0;
    border-top:
        1px solid #eef2f7;
}

.ag-priority-item:first-child {
    border-top: 0;
}

.ag-priority-marker {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.ag-priority-title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
}

.ag-priority-copy strong {
    color: #0f172a;
}

.ag-priority-copy p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 13px;
}

.ag-severity-badge {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.ag-severity--critical {
    color: #991b1b;
    background: #fee2e2;
}

.ag-priority-marker.ag-severity--critical {
    background: #dc2626;
}

.ag-severity--high {
    color: #9a3412;
    background: #ffedd5;
}

.ag-priority-marker.ag-severity--high {
    background: #f97316;
}

.ag-severity--medium {
    color: #92400e;
    background: #fef3c7;
}

.ag-priority-marker.ag-severity--medium {
    background: #f59e0b;
}

.ag-severity--low {
    color: #1d4ed8;
    background: #dbeafe;
}

.ag-priority-marker.ag-severity--low {
    background: #2563eb;
}

.ag-priority-impact {
    min-width: 62px;
    text-align: right;
}

.ag-priority-impact strong,
.ag-priority-impact span {
    display: block;
}

.ag-priority-impact strong {
    color: #2563eb;
    font-size: 18px;
}

.ag-priority-impact span {
    color: #94a3b8;
    font-size: 10px;
}

.ag-positive-state {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 20px 0;
    color: #059669;
}

.ag-positive-state i {
    margin-top: 2px;
    font-size: 20px;
}

.ag-positive-state strong {
    color: #047857;
}

.ag-positive-state p {
    margin: 5px 0 0;
    color: #64748b;
}


/* ================================================================
   EXECUTIVE BRIEF
================================================================ */

.ag-brief {
    display: flex;
    flex-direction: column;
}

.ag-brief-item {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 16px 0;
    border-top:
        1px solid #eef2f7;
}

.ag-brief-item:first-child {
    border-top: 0;
}

.ag-brief-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
}

.ag-brief-icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-brief-icon--blue {
    color: #2563eb;
    background: #dbeafe;
}

.ag-brief-icon--purple {
    color: #7c3aed;
    background: #ede9fe;
}

.ag-brief-icon--amber {
    color: #d97706;
    background: #fef3c7;
}

.ag-brief-item strong {
    color: #0f172a;
}

.ag-brief-item p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1150px) {

    .ag-pulse-hero,
    .ag-score-grid,
    .ag-lower-grid {
        grid-template-columns: 1fr;
    }

    .ag-stat-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .ag-domain-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 700px) {

    .ag-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .ag-pulse-hero__left,
    .ag-score-layout,
    .ag-security-layout,
    .ag-stat-grid {
        grid-template-columns: 1fr;
    }

    .ag-pulse-ring,
    .ag-security-ring {
        margin: auto;
    }

    .ag-domain-section__header {
        flex-direction: column;
    }

    .ag-domain-overall {
        width: 100%;
        text-align: left;
    }

    .ag-domain-grid {
        grid-template-columns: 1fr;
    }

    .ag-priority-item {
        grid-template-columns:
            12px 1fr;
    }

    .ag-priority-impact {
        grid-column: 2;
        text-align: left;
    }

}

</style>

@endsection