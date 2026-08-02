@extends('layouts.aceguard')

@section('title', 'Executive Dashboard')
@section('page-title', 'Executive Dashboard')
@section('page-subtitle', 'Your business health, cyber posture and priorities.')

@section('content')

@php
    $clientCount = \App\Models\Client::count();
    $businessScore = $health['overall'] ?? 0;
    $securityScore = $security['score'] ?? 0;
    $securityRating = $security['rating'] ?? 'Not assessed';

    $controls = collect($security['controls'] ?? []);

    $incompleteControls = $controls
        ->filter(fn ($control) => !($control['enabled'] ?? false))
        ->take(4);
@endphp

<div class="ag-dashboard">

    <section class="ag-hero">
        <div>
            <span class="ag-kicker">AceGuard intelligence</span>

            <h2>
                Welcome back, {{ Auth::user()->name ?? 'Founder' }}.
            </h2>

            <p>
                Here is what needs your attention across the business today.
            </p>
        </div>

        <a href="{{ route('clients.create') }}" class="ag-button ag-button--light">
            <i class="fas fa-plus"></i>
            New Client
        </a>
    </section>

    <section class="ag-score-grid">

        <article class="ag-card ag-pulse-card">
            <div class="ag-card-header">
                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Business Pulse™
                    </span>

                    <h3>Overall Business Health</h3>
                </div>

                <span class="ag-badge ag-badge--success">
                    Healthy
                </span>
            </div>

            <div class="ag-pulse-body">
                <div class="ag-large-score">
                    {{ $businessScore }}%
                </div>

                <div class="ag-pulse-details">
                    <strong>Your business is performing well.</strong>

                    <p>
                        This combines security, compliance, operations,
                        documents and business readiness.
                    </p>

                    <div class="ag-progress">
                        <div
                            class="ag-progress-value ag-progress-value--green"
                            style="width: {{ $businessScore }}%;">
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <article class="ag-card ag-security-card">
            <div class="ag-card-header">
                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Cyber resilience
                    </span>

                    <h3>Business Security Score</h3>
                </div>

                <div class="ag-shield-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>
            </div>

            <div class="ag-security-body">
                <div
                    class="ag-score-ring"
                    style="--ag-score: {{ $securityScore }};">
                    <div class="ag-score-ring-centre">
                        <strong>{{ $securityScore }}%</strong>
                        <span>{{ $securityRating }}</span>
                    </div>
                </div>

                <div class="ag-security-details">
                    <strong>
                        {{ $security['earned'] ?? 0 }}
                        of
                        {{ $security['maximum'] ?? 100 }}
                        security points achieved
                    </strong>

                    <p>
                        Your score is based on
                        {{ count($security['controls'] ?? []) }}
                        assessed security controls.
                    </p>

                    <div class="ag-progress">
                        <div
                            class="ag-progress-value ag-progress-value--blue"
                            style="width: {{ $securityScore }}%;">
                        </div>
                    </div>
                </div>
            </div>
        </article>

    </section>

    <section class="ag-stat-grid">

        <a href="{{ route('clients.index') }}" class="ag-stat-card">
            <div class="ag-stat-icon ag-stat-icon--blue">
                <i class="fas fa-users"></i>
            </div>

            <div>
                <span>Active Clients</span>
                <strong>{{ $clientCount }}</strong>
                <small>View client workspaces</small>
            </div>
        </a>

        <article class="ag-stat-card">
            <div class="ag-stat-icon ag-stat-icon--purple">
                <i class="fas fa-folder-open"></i>
            </div>

            <div>
                <span>Open Matters</span>
                <strong>0</strong>
                <small>No matters requiring action</small>
            </div>
        </article>

        <article class="ag-stat-card">
            <div class="ag-stat-icon ag-stat-icon--amber">
                <i class="fas fa-list-check"></i>
            </div>

            <div>
                <span>Outstanding Tasks</span>
                <strong>0</strong>
                <small>Everything is currently clear</small>
            </div>
        </article>

        <article class="ag-stat-card">
            <div class="ag-stat-icon ag-stat-icon--green">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <span>Security Score</span>
                <strong>{{ $securityScore }}%</strong>
                <small>{{ $securityRating }}</small>
            </div>
        </article>

    </section>

    <section class="ag-lower-grid">

        <article class="ag-card">
            <div class="ag-card-header">
                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Action centre
                    </span>

                    <h3>Today’s Priorities</h3>
                </div>

                <span class="ag-priority-number">
                    {{ $incompleteControls->count() }}
                </span>
            </div>

            <div class="ag-priority-list">

                @forelse($incompleteControls as $name => $control)

                    <div class="ag-priority-item">
                        <span class="ag-priority-dot"></span>

                        <div>
                            <strong>{{ $name }}</strong>

                            <p>
                                Complete this control to improve the
                                organisation’s security posture.
                            </p>
                        </div>

                        <span class="ag-points">
                            +{{ $control['points'] ?? 0 }} points
                        </span>
                    </div>

                @empty

                    <div class="ag-empty-state">
                        <i class="fas fa-circle-check"></i>

                        <div>
                            <strong>No urgent security priorities</strong>

                            <p>
                                All currently assessed controls are complete.
                            </p>
                        </div>
                    </div>

                @endforelse

            </div>
        </article>

        <article class="ag-card">
            <div class="ag-card-header">
                <div>
                    <span class="ag-kicker ag-kicker--dark">
                        Latest updates
                    </span>

                    <h3>Recent Activity</h3>
                </div>
            </div>

            <div class="ag-activity-list">

                <div class="ag-activity-item">
                    <div class="ag-activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>

                    <div>
                        <strong>Client workspaces available</strong>

                        <p>
                            {{ $clientCount }}
                            {{ \Illuminate\Support\Str::plural(
                                'client',
                                $clientCount
                            ) }}
                            currently registered.
                        </p>
                    </div>
                </div>

                <div class="ag-activity-item">
                    <div class="ag-activity-icon">
                        <i class="fas fa-heart-pulse"></i>
                    </div>

                    <div>
                        <strong>Business Pulse calculated</strong>

                        <p>
                            Current business health is
                            {{ $businessScore }}%.
                        </p>
                    </div>
                </div>

                <div class="ag-activity-item">
                    <div class="ag-activity-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>

                    <div>
                        <strong>Security posture reviewed</strong>

                        <p>
                            Current security score is
                            {{ $securityScore }}%.
                        </p>
                    </div>
                </div>

            </div>
        </article>

    </section>

</div>

<style>
.ag-dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

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
        linear-gradient(135deg, #0f172a, #172554);
    box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
}

.ag-hero h2 {
    margin: 5px 0 0;
    font-size: 29px;
}

.ag-hero p {
    margin: 10px 0 0;
    color: #cbd5e1;
}

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

.ag-score-grid,
.ag-lower-grid {
    display: grid;
    gap: 24px;
}

.ag-score-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.ag-lower-grid {
    grid-template-columns: 1.35fr .85fr;
}

.ag-card {
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
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

.ag-badge {
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.ag-badge--success {
    color: #047857;
    background: #d1fae5;
}

.ag-pulse-body {
    display: grid;
    grid-template-columns: 150px 1fr;
    align-items: center;
    gap: 24px;
}

.ag-large-score {
    color: #0f172a;
    font-size: 54px;
    font-weight: 850;
}

.ag-pulse-details strong,
.ag-security-details strong {
    color: #0f172a;
}

.ag-pulse-details p,
.ag-security-details p {
    margin: 8px 0 18px;
    color: #64748b;
    line-height: 1.6;
}

.ag-progress {
    width: 100%;
    height: 10px;
    overflow: hidden;
    border-radius: 999px;
    background: #e5e7eb;
}

.ag-progress-value {
    height: 100%;
    border-radius: inherit;
}

.ag-progress-value--green {
    background: #10b981;
}

.ag-progress-value--blue {
    background: #2563eb;
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

.ag-security-body {
    display: grid;
    grid-template-columns: 165px 1fr;
    align-items: center;
    gap: 25px;
}

.ag-score-ring {
    --ring-colour: #2563eb;
    width: 155px;
    height: 155px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: conic-gradient(
        var(--ring-colour) calc(var(--ag-score) * 1%),
        #e5e7eb 0
    );
}

.ag-score-ring::before {
    content: "";
    position: absolute;
    width: 122px;
    height: 122px;
    border-radius: 50%;
    background: #ffffff;
}

.ag-score-ring-centre {
    position: relative;
    z-index: 1;
    text-align: center;
}

.ag-score-ring-centre strong,
.ag-score-ring-centre span {
    display: block;
}

.ag-score-ring-centre strong {
    color: #0f172a;
    font-size: 34px;
}

.ag-score-ring-centre span {
    margin-top: 5px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.ag-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
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
    box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
    transition: .2s ease;
}

.ag-stat-card:hover {
    color: inherit;
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
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

.ag-stat-icon--purple {
    color: #7c3aed;
    background: #ede9fe;
}

.ag-stat-icon--amber {
    color: #d97706;
    background: #fef3c7;
}

.ag-stat-icon--green {
    color: #059669;
    background: #d1fae5;
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

.ag-priority-number {
    width: 31px;
    height: 31px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #ffffff;
    background: #dc2626;
    font-size: 13px;
    font-weight: 800;
}

.ag-priority-item {
    display: grid;
    grid-template-columns: 13px 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 17px 0;
    border-top: 1px solid #eef2f7;
}

.ag-priority-item:first-child {
    border-top: 0;
}

.ag-priority-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 0 5px #fee2e2;
}

.ag-priority-item strong,
.ag-activity-item strong {
    color: #0f172a;
}

.ag-priority-item p,
.ag-activity-item p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
}

.ag-points {
    white-space: nowrap;
    color: #2563eb;
    font-size: 12px;
    font-weight: 800;
}

.ag-empty-state {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 24px 0;
    color: #059669;
}

.ag-empty-state i {
    font-size: 24px;
}

.ag-empty-state p {
    margin: 4px 0 0;
    color: #64748b;
}

.ag-activity-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 17px 0;
    border-top: 1px solid #eef2f7;
}

.ag-activity-item:first-child {
    border-top: 0;
}

.ag-activity-icon {
    width: 39px;
    height: 39px;
    flex: 0 0 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    color: #2563eb;
    background: #eff6ff;
}

@media (max-width: 1100px) {
    .ag-score-grid,
    .ag-lower-grid {
        grid-template-columns: 1fr;
    }

    .ag-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .ag-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .ag-pulse-body,
    .ag-security-body,
    .ag-stat-grid {
        grid-template-columns: 1fr;
    }

    .ag-score-ring {
        margin: auto;
    }

    .ag-priority-item {
        grid-template-columns: 13px 1fr;
    }

    .ag-points {
        grid-column: 2;
    }
}
</style>

@endsection