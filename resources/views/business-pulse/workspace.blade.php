@extends('layouts.aceguard')

@section(
    'title',
    $client->company_name . ' Business Pulse™'
)

@section(
    'page-title',
    'Business Pulse™ Assessment'
)

@section(
    'page-subtitle',
    'Assess operational health, resilience and business readiness for '
    . $client->company_name . '.'
)

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Assessment Values
    |--------------------------------------------------------------------------
    */

    $operations = (int) $assessment->operations_score;
    $continuity = (int) $assessment->continuity_score;
    $documentation = (int) $assessment->documentation_score;
    $compliance = (int) $assessment->compliance_score;
    $technology = (int) $assessment->technology_score;
    $readiness = (int) $assessment->readiness_score;

    $businessScore = (int) $assessment->overall_score;

    /*
    |--------------------------------------------------------------------------
    | Business Pulse™
    |--------------------------------------------------------------------------
    */

    $pulseScore = (int) ($pulse['score'] ?? 0);
    $pulseRating = $pulse['rating'] ?? 'Not assessed';

    $securityScore =
        (int) (
            $pulse['components']['security']['score']
            ?? 0
        );

    $priorityCount =
        (int) ($pulse['priority_count'] ?? 0);

    $priorities =
        collect($pulse['priorities'] ?? []);

    /*
    |--------------------------------------------------------------------------
    | Rating Helpers
    |--------------------------------------------------------------------------
    */

    $businessRating = match (true) {
        $businessScore >= 90 => 'Excellent',
        $businessScore >= 75 => 'Healthy',
        $businessScore >= 60 => 'Watch',
        $businessScore >= 40 => 'At Risk',
        default => 'Critical',
    };

    $businessColour = match (true) {
        $businessScore >= 90 => '#10b981',
        $businessScore >= 75 => '#2563eb',
        $businessScore >= 60 => '#f59e0b',
        $businessScore >= 40 => '#f97316',
        default => '#dc2626',
    };

    $pulseColour = match (true) {
        $pulseScore >= 90 => '#10b981',
        $pulseScore >= 75 => '#2563eb',
        $pulseScore >= 60 => '#f59e0b',
        $pulseScore >= 40 => '#f97316',
        default => '#dc2626',
    };

    /*
    |--------------------------------------------------------------------------
    | Domain Configuration
    |--------------------------------------------------------------------------
    */

    $domains = [
        'operations_score' => [
            'label' => 'Operations',
            'description' =>
                'Day-to-day processes, ownership and operational effectiveness.',
            'icon' => 'fa-gears',
            'value' => $operations,
        ],

        'continuity_score' => [
            'label' => 'Continuity',
            'description' =>
                'Ability to continue operating during disruption or unexpected events.',
            'icon' => 'fa-arrows-rotate',
            'value' => $continuity,
        ],

        'documentation_score' => [
            'label' => 'Documentation',
            'description' =>
                'Quality and availability of business procedures, records and policies.',
            'icon' => 'fa-file-lines',
            'value' => $documentation,
        ],

        'compliance_score' => [
            'label' => 'Compliance',
            'description' =>
                'Readiness against regulatory, contractual and governance obligations.',
            'icon' => 'fa-scale-balanced',
            'value' => $compliance,
        ],

        'technology_score' => [
            'label' => 'Technology',
            'description' =>
                'Reliability, suitability and management of business technology.',
            'icon' => 'fa-laptop-code',
            'value' => $technology,
        ],

        'readiness_score' => [
            'label' => 'Readiness',
            'description' =>
                'Overall ability to respond, adapt and act on changing business needs.',
            'icon' => 'fa-bolt',
            'value' => $readiness,
        ],
    ];
@endphp


<div class="ag-pulse-workspace">

    {{-- ================================================================
        CLIENT HERO
    ================================================================ --}}

    <section class="ag-pulse-client-hero">

        <div>

            <span class="ag-pulse-kicker">
                AceGuard BOS · Business Pulse™
            </span>

            <h2>
                {{ $client->company_name }}
            </h2>

            <div class="ag-client-meta">

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

                <span>
                    <i class="fas fa-circle"></i>

                    {{
                        ucfirst(
                            $client->status
                            ?? 'active'
                        )
                    }}
                </span>

            </div>

        </div>


        <div class="ag-hero-actions">

            <a
                href="{{ route('security.workspace', $client) }}"
                class="ag-hero-button ag-hero-button--secondary"
            >
                <i class="fas fa-shield-halved"></i>
                Security
            </a>

            <a
                href="{{ route('clients.show', $client) }}"
                class="ag-hero-button ag-hero-button--light"
            >
                <i class="fas fa-building"></i>
                Client Overview
            </a>

        </div>

    </section>


    {{-- ================================================================
        CURRENT INTELLIGENCE
    ================================================================ --}}

    <section class="ag-pulse-summary-grid">

        {{-- Business Pulse --}}

        <article class="ag-summary-card ag-summary-card--primary">

            <div
                class="ag-pulse-ring"
                id="pulse-ring"
                style="
                    --score: {{ $pulseScore }};
                    --score-colour: {{ $pulseColour }};
                "
            >

                <div class="ag-ring-centre">

                    <strong id="pulse-score">
                        {{ $pulseScore }}%
                    </strong>

                    <span id="pulse-rating">
                        {{ $pulseRating }}
                    </span>

                </div>

            </div>


            <div class="ag-summary-copy">

                <span class="ag-section-label">
                    Business Pulse™
                </span>

                <h3 id="pulse-heading">
                    {{ $pulseRating }}
                    business condition
                </h3>

                <p id="pulse-summary">
                    {{
                        $pulse['summary']
                        ?? 'Business Pulse™ has not yet been calculated.'
                    }}
                </p>

                <div class="ag-progress">

                    <div
                        class="ag-progress-value"
                        id="pulse-progress"
                        style="
                            width: {{ $pulseScore }}%;
                            background:
                                {{ $pulseColour }};
                        "
                    ></div>

                </div>

            </div>

        </article>


        {{-- Business Health --}}

        <article class="ag-summary-metric">

            <div class="ag-summary-icon ag-summary-icon--green">
                <i class="fas fa-heart-pulse"></i>
            </div>

            <div>

                <span>
                    Business Health
                </span>

                <strong id="business-health-score">
                    {{ $businessScore }}%
                </strong>

                <small id="business-health-rating">
                    {{ $businessRating }}
                </small>

            </div>

        </article>


        {{-- Security --}}

        <article class="ag-summary-metric">

            <div class="ag-summary-icon ag-summary-icon--blue">
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
                    Cyber resilience
                </small>

            </div>

        </article>


        {{-- Priorities --}}

        <article class="ag-summary-metric">

            <div class="ag-summary-icon ag-summary-icon--amber">
                <i class="fas fa-bullseye"></i>
            </div>

            <div>

                <span>
                    Priorities
                </span>

                <strong id="priority-count">
                    {{ $priorityCount }}
                </strong>

                <small>
                    Items requiring attention
                </small>

            </div>

        </article>

    </section>


    {{-- ================================================================
        ASSESSMENT FORM
    ================================================================ --}}

    <form
        id="business-pulse-form"
        method="POST"
        action="{{ route('business-pulse.update', $client) }}"
    >

        @csrf
        @method('PATCH')

        <input
            type="hidden"
            name="status"
            value="completed"
        >


        <section class="ag-assessment-panel">

            <div class="ag-panel-heading">

                <div>

                    <span class="ag-section-label">
                        Business Assessment
                    </span>

                    <h3>
                        Business Health Domains
                    </h3>

                    <p>
                        Score each area from 0 to 100.
                        AceGuard BOS recalculates Business Health
                        and Business Pulse™ automatically.
                    </p>

                </div>


                <div class="ag-assessment-score">

                    <span>
                        Overall
                    </span>

                    <strong
                        id="overall-preview"
                        style="
                            color:
                                {{ $businessColour }};
                        "
                    >
                        {{ $businessScore }}%
                    </strong>

                </div>

            </div>


            <div class="ag-domain-grid">

                @foreach($domains as $field => $domain)

                    @php
                        $domainValue =
                            (int) $domain['value'];

                        $domainColour = match (true) {
                            $domainValue >= 90 =>
                                '#10b981',

                            $domainValue >= 75 =>
                                '#2563eb',

                            $domainValue >= 60 =>
                                '#f59e0b',

                            $domainValue >= 40 =>
                                '#f97316',

                            default =>
                                '#dc2626',
                        };

                        $domainRating = match (true) {
                            $domainValue >= 90 =>
                                'Excellent',

                            $domainValue >= 75 =>
                                'Healthy',

                            $domainValue >= 60 =>
                                'Watch',

                            $domainValue >= 40 =>
                                'At Risk',

                            default =>
                                'Critical',
                        };
                    @endphp


                    <article
                        class="ag-domain-card"
                        data-domain-card="{{ $field }}"
                    >

                        <div class="ag-domain-header">

                            <div
                                class="ag-domain-icon"
                                data-domain-icon="{{ $field }}"
                                style="
                                    color:
                                        {{ $domainColour }};

                                    background:
                                        {{ $domainColour }}14;
                                "
                            >
                                <i
                                    class="
                                        fas
                                        {{ $domain['icon'] }}
                                    "
                                ></i>
                            </div>


                            <div class="ag-domain-value">

                                <strong
                                    id="{{ $field }}-value"
                                    data-domain-value="{{ $field }}"
                                    style="
                                        color:
                                            {{ $domainColour }};
                                    "
                                >
                                    {{ $domainValue }}%
                                </strong>

                                <span
                                    id="{{ $field }}-rating"
                                    data-domain-rating="{{ $field }}"
                                    style="
                                        color:
                                            {{ $domainColour }};
                                    "
                                >
                                    {{ $domainRating }}
                                </span>

                            </div>

                        </div>


                        <div class="ag-domain-copy">

                            <h4>
                                {{ $domain['label'] }}
                            </h4>

                            <p>
                                {{ $domain['description'] }}
                            </p>

                        </div>


                        <div class="ag-range-row">

                            <input
                                type="range"
                                name="{{ $field }}"
                                id="{{ $field }}"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ $domainValue }}"
                                class="ag-domain-range"
                                data-domain="{{ $field }}"
                            >

                        </div>


                        <div class="ag-range-labels">

                            <span>0</span>

                            <span>50</span>

                            <span>100</span>

                        </div>

                    </article>

                @endforeach

            </div>

        </section>


        {{-- ============================================================
            NOTES & SAVE
        ============================================================ --}}

        <section class="ag-notes-panel">

            <div class="ag-notes-main">

                <label for="notes">
                    Assessment Notes
                </label>

                <p>
                    Record supporting observations,
                    concerns, decisions or evidence relevant
                    to this Business Pulse™ assessment.
                </p>

                <textarea
                    name="notes"
                    id="notes"
                    rows="5"
                    maxlength="5000"
                    placeholder="Add assessment notes..."
                >{{ old('notes', $assessment->notes) }}</textarea>

            </div>


            <div class="ag-save-panel">

                <div>

                    <span>
                        Assessment Status
                    </span>

                    <strong id="assessment-status">
                        {{
                            ucfirst(
                                $assessment->status
                                ?? 'draft'
                            )
                        }}
                    </strong>

                    <small id="assessment-time">

                        @if($assessment->assessed_at)

                            Last reviewed
                            {{
                                $assessment
                                    ->assessed_at
                                    ->format(
                                        'j M Y, H:i'
                                    )
                            }}

                        @else

                            Not yet reviewed

                        @endif

                    </small>

                </div>


                <button
                    type="submit"
                    id="save-assessment-button"
                    class="ag-save-button"
                >
                    <i class="fas fa-floppy-disk"></i>

                    <span>
                        Save Assessment
                    </span>
                </button>

            </div>

        </section>

    </form>


    {{-- ================================================================
        EXECUTIVE PRIORITIES
    ================================================================ --}}

    <section class="ag-priority-panel">

        <div class="ag-panel-heading">

            <div>

                <span class="ag-section-label">
                    Executive Intelligence
                </span>

                <h3>
                    Current Priorities
                </h3>

                <p>
                    Priorities are generated from the
                    Business Pulse™ and Security engines.
                </p>

            </div>

        </div>


        <div
            class="ag-priority-list"
            id="priority-list"
        >

            @forelse($priorities as $priority)

                @php
                    $severity =
                        $priority['severity']
                        ?? 'low';

                    $severityClass =
                        match ($severity) {
                            'critical' =>
                                'critical',

                            'high' =>
                                'high',

                            'medium' =>
                                'medium',

                            default =>
                                'low',
                        };
                @endphp


                <div class="ag-priority-item">

                    <div
                        class="
                            ag-priority-dot
                            ag-priority-dot--{{ $severityClass }}
                        "
                    ></div>


                    <div class="ag-priority-content">

                        <div class="ag-priority-title">

                            <strong>
                                {{ $priority['title'] }}
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

    </section>

</div>


{{-- ====================================================================
    STYLES
==================================================================== --}}

<style>

.ag-pulse-workspace {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/* --------------------------------------------------------------------
   CLIENT HERO
-------------------------------------------------------------------- */

.ag-pulse-client-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
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

.ag-pulse-client-hero h2 {
    margin: 6px 0 12px;
    font-size: 30px;
}

.ag-pulse-kicker,
.ag-section-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.ag-pulse-kicker {
    color: #93c5fd;
}

.ag-section-label {
    color: #64748b;
}

.ag-client-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    color: #cbd5e1;
    font-size: 13px;
}

.ag-client-meta span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.ag-hero-actions {
    display: flex;
    gap: 10px;
}

.ag-hero-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 17px;
    border-radius: 12px;
    font-weight: 750;
    text-decoration: none;
    transition: .2s ease;
}

.ag-hero-button:hover {
    transform: translateY(-2px);
}

.ag-hero-button--light {
    color: #0f172a;
    background: #ffffff;
}

.ag-hero-button--secondary {
    color: #ffffff;
    border: 1px solid rgba(255,255,255,.25);
    background: rgba(255,255,255,.08);
}


/* --------------------------------------------------------------------
   SUMMARY
-------------------------------------------------------------------- */

.ag-pulse-summary-grid {
    display: grid;
    grid-template-columns:
        minmax(0, 1.7fr)
        repeat(3, minmax(180px, .55fr));
    gap: 18px;
}

.ag-summary-card,
.ag-summary-metric,
.ag-assessment-panel,
.ag-notes-panel,
.ag-priority-panel {
    border: 1px solid #e5e7eb;
    background: #ffffff;
    box-shadow:
        0 10px 28px
        rgba(15, 23, 42, .05);
}

.ag-summary-card {
    display: grid;
    grid-template-columns: 145px 1fr;
    align-items: center;
    gap: 24px;
    padding: 22px;
    border-radius: 18px;
}

.ag-summary-metric {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px;
    border-radius: 18px;
}

.ag-summary-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
}

.ag-summary-icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-summary-icon--blue {
    color: #2563eb;
    background: #dbeafe;
}

.ag-summary-icon--amber {
    color: #d97706;
    background: #fef3c7;
}

.ag-summary-metric span,
.ag-summary-metric strong,
.ag-summary-metric small {
    display: block;
}

.ag-summary-metric span {
    color: #64748b;
    font-size: 13px;
}

.ag-summary-metric strong {
    margin: 3px 0;
    color: #0f172a;
    font-size: 25px;
}

.ag-summary-metric small {
    color: #94a3b8;
}


/* --------------------------------------------------------------------
   PULSE RING
-------------------------------------------------------------------- */

.ag-pulse-ring {
    width: 135px;
    height: 135px;
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

.ag-pulse-ring::before {
    content: "";
    position: absolute;
    width: 103px;
    height: 103px;
    border-radius: 50%;
    background: #ffffff;
}

.ag-ring-centre {
    position: relative;
    z-index: 1;
    text-align: center;
}

.ag-ring-centre strong,
.ag-ring-centre span {
    display: block;
}

.ag-ring-centre strong {
    color: #0f172a;
    font-size: 29px;
}

.ag-ring-centre span {
    margin-top: 3px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
}

.ag-summary-copy h3 {
    margin: 5px 0 7px;
    color: #0f172a;
    font-size: 21px;
}

.ag-summary-copy p {
    margin: 0 0 15px;
    color: #64748b;
    line-height: 1.55;
}


/* --------------------------------------------------------------------
   PROGRESS
-------------------------------------------------------------------- */

.ag-progress,
.ag-domain-progress {
    width: 100%;
    overflow: hidden;
    border-radius: 999px;
    background: #e5e7eb;
}

.ag-progress {
    height: 9px;
}

.ag-progress-value {
    height: 100%;
    border-radius: inherit;
    transition:
        width .4s ease,
        background .4s ease;
}


/* --------------------------------------------------------------------
   ASSESSMENT
-------------------------------------------------------------------- */

.ag-assessment-panel,
.ag-priority-panel {
    padding: 25px;
    border-radius: 20px;
}

.ag-panel-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.ag-panel-heading h3 {
    margin: 5px 0 7px;
    color: #0f172a;
    font-size: 22px;
}

.ag-panel-heading p {
    max-width: 720px;
    margin: 0;
    color: #64748b;
    line-height: 1.6;
}

.ag-assessment-score {
    min-width: 130px;
    padding: 13px 16px;
    border-radius: 13px;
    text-align: right;
    background: #f8fafc;
}

.ag-assessment-score span,
.ag-assessment-score strong {
    display: block;
}

.ag-assessment-score span {
    color: #64748b;
    font-size: 12px;
}

.ag-assessment-score strong {
    margin-top: 3px;
    font-size: 25px;
}


/* --------------------------------------------------------------------
   DOMAIN CARDS
-------------------------------------------------------------------- */

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
        box-shadow .2s ease;
}

.ag-domain-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 12px 24px
        rgba(15, 23, 42, .06);
}

.ag-domain-header {
    display: flex;
    align-items: flex-start;
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

.ag-domain-value {
    text-align: right;
}

.ag-domain-value strong,
.ag-domain-value span {
    display: block;
}

.ag-domain-value strong {
    font-size: 24px;
}

.ag-domain-value span {
    margin-top: 2px;
    font-size: 11px;
    font-weight: 750;
}

.ag-domain-copy h4 {
    margin: 15px 0 5px;
    color: #0f172a;
}

.ag-domain-copy p {
    min-height: 59px;
    margin: 0 0 16px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}


/* --------------------------------------------------------------------
   RANGE INPUT
-------------------------------------------------------------------- */

.ag-range-row {
    width: 100%;
}

.ag-domain-range {
    width: 100%;
    cursor: pointer;
}

.ag-range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 4px;
    color: #94a3b8;
    font-size: 10px;
}


/* --------------------------------------------------------------------
   NOTES
-------------------------------------------------------------------- */

.ag-notes-panel {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 270px;
    gap: 24px;
    margin-top: 20px;
    padding: 24px;
    border-radius: 18px;
}

.ag-notes-main label {
    display: block;
    color: #0f172a;
    font-weight: 750;
}

.ag-notes-main p {
    margin: 4px 0 12px;
    color: #64748b;
    font-size: 13px;
}

.ag-notes-main textarea {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    outline: none;
    resize: vertical;
    color: #0f172a;
    background: #ffffff;
}

.ag-notes-main textarea:focus {
    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, .08);
}

.ag-save-panel {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 20px;
    padding: 17px;
    border-radius: 14px;
    background: #f8fafc;
}

.ag-save-panel span,
.ag-save-panel strong,
.ag-save-panel small {
    display: block;
}

.ag-save-panel span {
    color: #64748b;
    font-size: 12px;
}

.ag-save-panel strong {
    margin-top: 3px;
    color: #0f172a;
}

.ag-save-panel small {
    margin-top: 5px;
    color: #94a3b8;
}

.ag-save-button {
    width: 100%;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 13px 16px;

    border: 0;
    border-radius: 12px;

    color: #ffffff;
    background: #2563eb;

    font-weight: 750;

    cursor: pointer;

    transition:
        transform .2s ease,
        opacity .2s ease,
        background .2s ease;
}

.ag-save-button:hover {
    transform: translateY(-2px);
    background: #1d4ed8;
}

.ag-save-button:disabled {
    cursor: wait;
    opacity: .65;
}


/* --------------------------------------------------------------------
   PRIORITIES
-------------------------------------------------------------------- */

.ag-priority-item {
    display: grid;

    grid-template-columns:
        12px
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 14px;

    padding: 17px 0;

    border-top:
        1px solid #eef2f7;
}

.ag-priority-item:first-child {
    border-top: 0;
}

.ag-priority-dot {
    width: 10px;
    height: 10px;
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

.ag-priority-title {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.ag-priority-content strong {
    color: #0f172a;
}

.ag-priority-content p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
}

.ag-priority-badge {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
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
    min-width: 60px;
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
    padding: 18px 0;
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
    margin: 4px 0 0;
    color: #64748b;
}


/* --------------------------------------------------------------------
   LIVE NOTIFICATION
-------------------------------------------------------------------- */

.ag-pulse-notification {
    min-width: 300px;
    max-width: 430px;

    position: fixed;

    right: 24px;
    bottom: 24px;

    z-index: 9999;

    padding: 15px 18px;

    border-radius: 13px;

    color: #ffffff;

    font-size: 14px;
    font-weight: 700;

    background: #047857;

    box-shadow:
        0 18px 38px
        rgba(15, 23, 42, .22);

    opacity: 0;

    pointer-events: none;

    transform: translateY(18px);

    transition:
        opacity .2s ease,
        transform .2s ease;
}

.ag-pulse-notification.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.ag-pulse-notification.is-error {
    background: #b91c1c;
}


/* --------------------------------------------------------------------
   RESPONSIVE
-------------------------------------------------------------------- */

@media (max-width: 1250px) {

    .ag-pulse-summary-grid {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

    .ag-summary-card {
        grid-column: 1 / -1;
    }

}


@media (max-width: 1000px) {

    .ag-domain-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .ag-notes-panel {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .ag-pulse-client-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .ag-hero-actions {
        width: 100%;
        flex-direction: column;
    }

    .ag-pulse-summary-grid,
    .ag-domain-grid,
    .ag-summary-card {
        grid-template-columns: 1fr;
    }

    .ag-summary-card {
        text-align: center;
    }

    .ag-pulse-ring {
        margin: auto;
    }

    .ag-panel-heading {
        flex-direction: column;
    }

    .ag-assessment-score {
        width: 100%;
        text-align: left;
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


{{-- ====================================================================
    LIVE BUSINESS PULSE™ JAVASCRIPT
==================================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.getElementById(
            'business-pulse-form'
        );

    if (!form) {
        return;
    }

    const ranges =
        document.querySelectorAll(
            '.ag-domain-range'
        );

    const saveButton =
        document.getElementById(
            'save-assessment-button'
        );


    /*
    |--------------------------------------------------------------------------
    | Domain Display
    |--------------------------------------------------------------------------
    */

    function ratingForScore(score) {

        if (score >= 90) {
            return 'Excellent';
        }

        if (score >= 75) {
            return 'Healthy';
        }

        if (score >= 60) {
            return 'Watch';
        }

        if (score >= 40) {
            return 'At Risk';
        }

        return 'Critical';
    }


    function colourForScore(score) {

        if (score >= 90) {
            return '#10b981';
        }

        if (score >= 75) {
            return '#2563eb';
        }

        if (score >= 60) {
            return '#f59e0b';
        }

        if (score >= 40) {
            return '#f97316';
        }

        return '#dc2626';
    }


    /*
    |--------------------------------------------------------------------------
    | Preview Domain Changes
    |--------------------------------------------------------------------------
    */

    function updateDomainPreview(range) {

        const field =
            range.dataset.domain;

        const score =
            Number(range.value);

        const valueElement =
            document.querySelector(
                '[data-domain-value="'
                + field
                + '"]'
            );

        const ratingElement =
            document.querySelector(
                '[data-domain-rating="'
                + field
                + '"]'
            );

        const iconElement =
            document.querySelector(
                '[data-domain-icon="'
                + field
                + '"]'
            );

        const colour =
            colourForScore(score);

        if (valueElement) {
            valueElement.textContent =
                score + '%';

            valueElement.style.color =
                colour;
        }

        if (ratingElement) {
            ratingElement.textContent =
                ratingForScore(score);

            ratingElement.style.color =
                colour;
        }

        if (iconElement) {
            iconElement.style.color =
                colour;

            iconElement.style.background =
                colour + '14';
        }

        updateOverallPreview();
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Preview Overall Score
    |--------------------------------------------------------------------------
    */

    function updateOverallPreview() {

        const scores =
            Array.from(ranges)
                .map(function (range) {
                    return Number(
                        range.value
                    );
                });

        if (!scores.length) {
            return;
        }

        const total =
            scores.reduce(
                function (sum, value) {
                    return sum + value;
                },
                0
            );

        const average =
            Math.round(
                total / scores.length
            );

        const preview =
            document.getElementById(
                'overall-preview'
            );

        if (preview) {

            preview.textContent =
                average + '%';

            preview.style.color =
                colourForScore(
                    average
                );
        }
    }


    ranges.forEach(function (range) {

        range.addEventListener(
            'input',
            function () {
                updateDomainPreview(
                    range
                );
            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | AJAX Save
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();

            if (
                form.dataset.saving
                === 'true'
            ) {
                return;
            }

            form.dataset.saving =
                'true';

            saveButton.disabled =
                true;

            const originalText =
                saveButton.innerHTML;

            saveButton.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i>'
                + '<span>Saving...</span>';

            try {

                const response =
                    await fetch(
                        form.action,
                        {
                            method: 'POST',

                            body:
                                new FormData(
                                    form
                                ),

                            credentials:
                                'same-origin',

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );


                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message
                        || 'The assessment could not be saved.'
                    );

                }


                updateSavedAssessment(
                    data
                );


                showNotification(
                    data.message
                    || 'Business Pulse™ assessment updated.'
                );


            } catch (error) {

                console.error(
                    'Business Pulse update failed:',
                    error
                );


                showNotification(
                    error.message
                    || 'An unexpected error occurred.',
                    true
                );

            } finally {

                form.dataset.saving =
                    'false';

                saveButton.disabled =
                    false;

                saveButton.innerHTML =
                    originalText;

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Update Saved Intelligence
    |--------------------------------------------------------------------------
    */

    function updateSavedAssessment(data) {

        const assessment =
            data.assessment || {};

        const pulse =
            data.pulse || {};


        const businessScore =
            Number(
                assessment.overall_score
                || 0
            );


        const businessHealth =
            document.getElementById(
                'business-health-score'
            );

        if (businessHealth) {

            businessHealth.textContent =
                businessScore + '%';

        }


        const businessRating =
            document.getElementById(
                'business-health-rating'
            );

        if (businessRating) {

            businessRating.textContent =
                ratingForScore(
                    businessScore
                );

        }


        const pulseScore =
            Number(
                pulse.score
                || 0
            );


        const pulseScoreElement =
            document.getElementById(
                'pulse-score'
            );

        if (pulseScoreElement) {

            pulseScoreElement.textContent =
                pulseScore + '%';

        }


        const pulseRating =
            document.getElementById(
                'pulse-rating'
            );

        if (pulseRating) {

            pulseRating.textContent =
                pulse.rating
                || 'Not assessed';

        }


        const pulseHeading =
            document.getElementById(
                'pulse-heading'
            );

        if (pulseHeading) {

            pulseHeading.textContent =
                (
                    pulse.rating
                    || 'Not assessed'
                )
                + ' business condition';

        }


        const pulseSummary =
            document.getElementById(
                'pulse-summary'
            );

        if (pulseSummary) {

            pulseSummary.textContent =
                pulse.summary || '';

        }


        const pulseRing =
            document.getElementById(
                'pulse-ring'
            );

        const pulseColour =
            colourForScore(
                pulseScore
            );

        if (pulseRing) {

            pulseRing.style.setProperty(
                '--score',
                pulseScore
            );

            pulseRing.style.setProperty(
                '--score-colour',
                pulseColour
            );

        }


        const pulseProgress =
            document.getElementById(
                'pulse-progress'
            );

        if (pulseProgress) {

            pulseProgress.style.width =
                pulseScore + '%';

            pulseProgress.style.background =
                pulseColour;

        }


        const priorityCount =
            document.getElementById(
                'priority-count'
            );

        if (priorityCount) {

            priorityCount.textContent =
                pulse.priority_count
                ?? 0;

        }


        const status =
            document.getElementById(
                'assessment-status'
            );

        if (status) {

            status.textContent =
                'Completed';

        }


        const assessmentTime =
            document.getElementById(
                'assessment-time'
            );

        if (assessmentTime) {

            assessmentTime.textContent =
                'Saved just now';

        }


        updatePriorities(
            pulse.priorities
            || []
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Priority Rendering
    |--------------------------------------------------------------------------
    */

    function updatePriorities(priorities) {

        const list =
            document.getElementById(
                'priority-list'
            );

        if (!list) {
            return;
        }


        list.replaceChildren();


        if (!priorities.length) {

            const positive =
                document.createElement(
                    'div'
                );

            positive.className =
                'ag-positive-state';


            const icon =
                document.createElement(
                    'i'
                );

            icon.className =
                'fas fa-circle-check';


            const copy =
                document.createElement(
                    'div'
                );


            const strong =
                document.createElement(
                    'strong'
                );

            strong.textContent =
                'No immediate priorities';


            const paragraph =
                document.createElement(
                    'p'
                );

            paragraph.textContent =
                'Business Pulse™ has detected no current issues requiring management attention.';


            copy.append(
                strong,
                paragraph
            );


            positive.append(
                icon,
                copy
            );


            list.appendChild(
                positive
            );


            return;

        }


        priorities.forEach(
            function (priority) {

                const severity =
                    priority.severity
                    || 'low';


                const row =
                    document.createElement(
                        'div'
                    );

                row.className =
                    'ag-priority-item';


                const dot =
                    document.createElement(
                        'div'
                    );

                dot.className =
                    'ag-priority-dot '
                    + 'ag-priority-dot--'
                    + severity;


                const content =
                    document.createElement(
                        'div'
                    );

                content.className =
                    'ag-priority-content';


                const title =
                    document.createElement(
                        'div'
                    );

                title.className =
                    'ag-priority-title';


                const strong =
                    document.createElement(
                        'strong'
                    );

                strong.textContent =
                    priority.title || 'Priority';


                const badge =
                    document.createElement(
                        'span'
                    );

                badge.className =
                    'ag-priority-badge '
                    + 'ag-priority-badge--'
                    + severity;

                badge.textContent =
                    severity.charAt(0)
                        .toUpperCase()
                    + severity.slice(1);


                title.append(
                    strong,
                    badge
                );


                const paragraph =
                    document.createElement(
                        'p'
                    );

                paragraph.textContent =
                    priority.message
                    || '';


                content.append(
                    title,
                    paragraph
                );


                const impact =
                    document.createElement(
                        'div'
                    );

                impact.className =
                    'ag-priority-impact';


                const impactStrong =
                    document.createElement(
                        'strong'
                    );

                impactStrong.textContent =
                    '+'
                    + (
                        priority.impact
                        || 0
                    );


                const impactText =
                    document.createElement(
                        'span'
                    );

                impactText.textContent =
                    'impact';


                impact.append(
                    impactStrong,
                    impactText
                );


                row.append(
                    dot,
                    content,
                    impact
                );


                list.appendChild(
                    row
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    function showNotification(
        message,
        error = false
    ) {

        let notification =
            document.querySelector(
                '.ag-pulse-notification'
            );


        if (!notification) {

            notification =
                document.createElement(
                    'div'
                );

            notification.className =
                'ag-pulse-notification';

            notification.setAttribute(
                'role',
                'status'
            );

            document.body.appendChild(
                notification
            );

        }


        notification.textContent =
            message;


        notification.classList.toggle(
            'is-error',
            error
        );


        notification.classList.add(
            'is-visible'
        );


        window.clearTimeout(
            notification.hideTimer
        );


        notification.hideTimer =
            window.setTimeout(
                function () {

                    notification
                        .classList
                        .remove(
                            'is-visible'
                        );

                },
                3000
            );

    }

});

</script>

@endsection