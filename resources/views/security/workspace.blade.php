@extends('layouts.aceguard')

@section('title', $client->company_name . ' Security Workspace')

@section('page-title', 'Security Workspace')

@section(
    'page-subtitle',
    'Review controls, evidence and cyber-risk priorities for '
    . $client->company_name . '.'
)

@section('content')

@php
    $score = $security['score'] ?? 0;
    $rating = $security['rating'] ?? 'Not assessed';
    $completed = $security['completed'] ?? 0;
    $outstanding = $security['outstanding'] ?? 0;
    $maximum = $security['maximum'] ?? 0;
    $earned = $security['earned'] ?? 0;

    $scoreColour = match (true) {
        $score >= 90 => '#10b981',
        $score >= 75 => '#2563eb',
        $score >= 60 => '#f59e0b',
        $score >= 40 => '#f97316',
        default => '#dc2626',
    };
@endphp

<div class="ag-security-workspace">

    @if(session('success'))
        <div class="ag-alert ag-alert--success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="ag-alert ag-alert--danger">
            <i class="fas fa-triangle-exclamation"></i>

            <div>
                <strong>The update could not be saved.</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <section class="ag-client-hero">

        <div>
            <span class="ag-kicker">AceGuard BOS workspace</span>

            <h2>{{ $client->company_name }}</h2>

            <div class="ag-client-meta">
                <span>
                    <i class="fas fa-user"></i>
                    {{ $client->contact_name ?: 'No contact recorded' }}
                </span>

                <span>
                    <i class="fas fa-envelope"></i>
                    {{ $client->email ?: 'No email recorded' }}
                </span>

                <span>
                    <i class="fas fa-circle"></i>
                    {{ ucfirst($client->status ?? 'active') }}
                </span>
            </div>
        </div>

        <a
            href="{{ route('clients.show', $client) }}"
            class="ag-button ag-button--light"
        >
            <i class="fas fa-arrow-left"></i>
            Client Overview
        </a>

    </section>

    <section class="ag-summary-grid">

        <article class="ag-score-card">

            <div
                class="ag-score-ring"
                style="
                    --score: {{ $score }};
                    --score-colour: {{ $scoreColour }};
                "
            >
                <div class="ag-score-ring__centre">
                    <strong>{{ $score }}%</strong>
                    <span>{{ $rating }}</span>
                </div>
            </div>

            <div class="ag-score-copy">
                <span class="ag-section-label">
                    Business Security Score
                </span>

                <h3>{{ $rating }} cyber posture</h3>

                <p>
                    {{ $earned }} of {{ $maximum }} available security
                    points have been achieved.
                </p>

                <div class="ag-progress">
                    <div
                        class="ag-progress__value"
                        style="
                            width: {{ $score }}%;
                            background: {{ $scoreColour }};
                        "
                    ></div>
                </div>
            </div>

        </article>

        <article class="ag-metric-card">
            <div class="ag-metric-icon ag-metric-icon--green">
                <i class="fas fa-circle-check"></i>
            </div>

            <div>
                <span>Completed controls</span>
                <strong>{{ $completed }}</strong>
                <small>Controls currently satisfied</small>
            </div>
        </article>

        <article class="ag-metric-card">
            <div class="ag-metric-icon ag-metric-icon--red">
                <i class="fas fa-triangle-exclamation"></i>
            </div>

            <div>
                <span>Outstanding controls</span>
                <strong>{{ $outstanding }}</strong>
                <small>Controls requiring attention</small>
            </div>
        </article>

    </section>

    <div class="ag-workspace-grid">

        <main class="ag-controls-column">

            @forelse($controlsByCategory as $category => $controls)

                <section class="ag-category-card">

                    <div class="ag-category-header">
                        <div>
                            <span class="ag-section-label">
                                Security domain
                            </span>

                            <h3>{{ $category }}</h3>
                        </div>

                        <span class="ag-category-count">
                            {{ $controls->where('enabled', true)->count() }}
                            /
                            {{ $controls->count() }}
                            complete
                        </span>
                    </div>

                    <div class="ag-control-list">

                        @foreach($controls as $control)

                            <form
                                method="POST"
                                action="{{ route(
                                    'security.controls.update',
                                    [$client, $control]
                                ) }}"
                                class="ag-control-item"
                            >
                                @csrf
                                @method('PATCH')

                                <input
                                    type="hidden"
                                    name="enabled"
                                    value="{{ $control->enabled ? 0 : 1 }}"
                                >

                                <div class="ag-control-main">

                                    <button
                                        type="submit"
                                        class="ag-toggle-button
                                            {{ $control->enabled
                                                ? 'ag-toggle-button--active'
                                                : ''
                                            }}"
                                        aria-label="Toggle {{ $control->control }}"
                                        aria-pressed="{{ $control->enabled
                                            ? 'true'
                                            : 'false'
                                        }}"
                                    >
                                        <span class="ag-toggle-button__thumb">
                                        </span>
                                    </button>

                                    <div class="ag-control-copy">

                                        <div class="ag-control-title-row">
                                            <strong>
                                                {{ $control->control }}
                                            </strong>

                                            <span
                                                class="ag-control-status
                                                    {{ $control->enabled
                                                        ? 'ag-control-status--complete'
                                                        : 'ag-control-status--outstanding'
                                                    }}"
                                            >
                                                {{ $control->enabled
                                                    ? 'Complete'
                                                    : 'Outstanding'
                                                }}
                                            </span>
                                        </div>

                                        <p>
                                            {{ $control->enabled
                                                ? 'This security control is currently satisfied.'
                                                : 'Complete this control to improve the client’s security posture.'
                                            }}
                                        </p>

                                        @if($control->notes)
                                            <small class="ag-control-note">
                                                <i class="fas fa-note-sticky"></i>
                                                {{ $control->notes }}
                                            </small>
                                        @endif

                                        @if($control->evidence)
                                            <small class="ag-control-note">
                                                <i class="fas fa-paperclip"></i>
                                                Evidence:
                                                {{ $control->evidence }}
                                            </small>
                                        @endif

                                    </div>

                                </div>

                                <div class="ag-control-points">
                                    <strong>
                                        {{ $control->maximum_points }}
                                    </strong>

                                    <span>points</span>
                                </div>

                            </form>

                        @endforeach

                    </div>

                </section>

            @empty

                <section class="ag-empty-card">
                    <i class="fas fa-shield-halved"></i>

                    <h3>No security controls found</h3>

                    <p>
                        Run the security-control seeder or create the first
                        assessment for this workspace.
                    </p>
                </section>

            @endforelse

        </main>

        <aside class="ag-insights-column">

            <section class="ag-side-card">

                <div class="ag-side-card__header">
                    <span class="ag-section-label">
                        Recommended actions
                    </span>

                    <h3>Next Best Actions</h3>
                </div>

                <div class="ag-recommendation-list">

                    @forelse(
                        $security['recommendations'] ?? []
                        as $recommendation
                    )

                        <div class="ag-recommendation">
                            <div class="ag-recommendation__icon">
                                <i class="fas fa-arrow-trend-up"></i>
                            </div>

                            <p>{{ $recommendation }}</p>
                        </div>

                    @empty

                        <div class="ag-positive-state">
                            <i class="fas fa-circle-check"></i>

                            <p>
                                All assessed security controls are complete.
                            </p>
                        </div>

                    @endforelse

                </div>

            </section>

            <section class="ag-side-card">

                <div class="ag-side-card__header">
                    <span class="ag-section-label">
                        Assessment details
                    </span>

                    <h3>Workspace Summary</h3>
                </div>

                <dl class="ag-summary-list">
                    <div>
                        <dt>Workspace</dt>
                        <dd>{{ $client->company_name }}</dd>
                    </div>

                    <div>
                        <dt>Controls assessed</dt>
                        <dd>{{ $completed + $outstanding }}</dd>
                    </div>

                    <div>
                        <dt>Points achieved</dt>
                        <dd>{{ $earned }} / {{ $maximum }}</dd>
                    </div>

                    <div>
                        <dt>Current rating</dt>
                        <dd>{{ $rating }}</dd>
                    </div>
                </dl>

            </section>

        </aside>

    </div>

</div>

<style>
.ag-security-workspace {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.ag-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 18px;
    border-radius: 14px;
    font-size: 14px;
}

.ag-alert ul {
    margin: 8px 0 0;
    padding-left: 19px;
}

.ag-alert--success {
    color: #047857;
    background: #d1fae5;
    border: 1px solid #a7f3d0;
}

.ag-alert--danger {
    color: #b91c1c;
    background: #fee2e2;
    border: 1px solid #fecaca;
}

.ag-client-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 30px;
    color: #ffffff;
    border-radius: 22px;
    background:
        radial-gradient(
            circle at top right,
            rgba(37, 99, 235, .58),
            transparent 38%
        ),
        linear-gradient(135deg, #0f172a, #172554);
    box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
}

.ag-client-hero h2 {
    margin: 5px 0 12px;
    font-size: 30px;
}

.ag-kicker,
.ag-section-label {
    display: block;
    color: #93c5fd;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.ag-section-label {
    color: #64748b;
}

.ag-client-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    color: #cbd5e1;
    font-size: 13px;
}

.ag-client-meta span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
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

.ag-summary-grid {
    display: grid;
    grid-template-columns: 1.6fr .7fr .7fr;
    gap: 18px;
}

.ag-score-card,
.ag-metric-card,
.ag-category-card,
.ag-side-card,
.ag-empty-card {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
}

.ag-score-card {
    display: grid;
    grid-template-columns: 150px 1fr;
    align-items: center;
    gap: 24px;
    padding: 24px;
}

.ag-score-ring {
    width: 140px;
    height: 140px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: conic-gradient(
        var(--score-colour) calc(var(--score) * 1%),
        #e5e7eb 0
    );
}

.ag-score-ring::before {
    content: "";
    position: absolute;
    width: 108px;
    height: 108px;
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
    font-size: 31px;
}

.ag-score-ring__centre span {
    margin-top: 4px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.ag-score-copy h3 {
    margin: 5px 0 7px;
    color: #0f172a;
}

.ag-score-copy p {
    margin: 0 0 17px;
    color: #64748b;
    line-height: 1.6;
}

.ag-progress {
    height: 9px;
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

.ag-metric-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 22px;
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

.ag-metric-icon--green {
    color: #059669;
    background: #d1fae5;
}

.ag-metric-icon--red {
    color: #dc2626;
    background: #fee2e2;
}

.ag-metric-card span,
.ag-metric-card strong,
.ag-metric-card small {
    display: block;
}

.ag-metric-card span {
    color: #64748b;
    font-size: 13px;
}

.ag-metric-card strong {
    margin: 3px 0;
    color: #0f172a;
    font-size: 27px;
}

.ag-metric-card small {
    color: #94a3b8;
}

.ag-workspace-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    align-items: start;
    gap: 24px;
}

.ag-controls-column,
.ag-insights-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ag-category-card,
.ag-side-card {
    padding: 23px;
}

.ag-category-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 15px;
}

.ag-category-header h3,
.ag-side-card__header h3 {
    margin: 5px 0 0;
    color: #0f172a;
    font-size: 20px;
}

.ag-category-count {
    padding: 7px 10px;
    border-radius: 999px;
    color: #475569;
    background: #f1f5f9;
    font-size: 12px;
    font-weight: 700;
}

.ag-control-item {
    width: 100%;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 18px;
    padding: 18px 0;
    border: 0;
    border-top: 1px solid #eef2f7;
    background: transparent;
}

.ag-control-item:first-child {
    border-top: 0;
}

.ag-control-main {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.ag-toggle-button {
    width: 45px;
    height: 25px;
    flex: 0 0 45px;
    position: relative;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: #cbd5e1;
    cursor: pointer;
    transition:
        background .2s ease,
        opacity .2s ease,
        transform .2s ease;
}

.ag-toggle-button:hover {
    transform: scale(1.03);
}

.ag-toggle-button:disabled {
    cursor: wait;
    opacity: .65;
}

.ag-toggle-button.is-loading {
    animation: ag-toggle-pulse .75s infinite alternate;
}

.ag-toggle-button__thumb {
    width: 19px;
    height: 19px;
    position: absolute;
    top: 3px;
    left: 3px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 2px 5px rgba(15, 23, 42, .18);
    transition: transform .2s ease;
}

.ag-toggle-button--active {
    background: #10b981;
}

.ag-toggle-button--active .ag-toggle-button__thumb {
    transform: translateX(20px);
}

.ag-control-copy {
    min-width: 0;
    flex: 1;
}

.ag-control-title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 9px;
}

.ag-control-title-row strong {
    color: #0f172a;
}

.ag-control-status {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.ag-control-status--complete {
    color: #047857;
    background: #d1fae5;
}

.ag-control-status--outstanding {
    color: #b91c1c;
    background: #fee2e2;
}

.ag-control-copy p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}

.ag-control-note {
    display: block;
    margin-top: 7px;
    color: #64748b;
}

.ag-control-points {
    min-width: 58px;
    text-align: right;
}

.ag-control-points strong,
.ag-control-points span {
    display: block;
}

.ag-control-points strong {
    color: #2563eb;
    font-size: 19px;
}

.ag-control-points span {
    color: #94a3b8;
    font-size: 11px;
}

.ag-side-card__header {
    margin-bottom: 17px;
}

.ag-recommendation {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 14px 0;
    border-top: 1px solid #eef2f7;
}

.ag-recommendation:first-child {
    border-top: 0;
}

.ag-recommendation__icon {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    color: #2563eb;
    background: #eff6ff;
}

.ag-recommendation p {
    margin: 3px 0 0;
    color: #475569;
    font-size: 13px;
    line-height: 1.5;
}

.ag-positive-state {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 0;
    color: #059669;
}

.ag-positive-state p {
    margin: 0;
}

.ag-summary-list {
    margin: 0;
}

.ag-summary-list > div {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 13px 0;
    border-top: 1px solid #eef2f7;
}

.ag-summary-list > div:first-child {
    border-top: 0;
}

.ag-summary-list dt {
    color: #64748b;
    font-weight: 500;
}

.ag-summary-list dd {
    margin: 0;
    color: #0f172a;
    font-weight: 750;
    text-align: right;
}

.ag-empty-card {
    padding: 45px;
    text-align: center;
}

.ag-empty-card i {
    color: #2563eb;
    font-size: 36px;
}

.ag-empty-card h3 {
    margin: 15px 0 7px;
    color: #0f172a;
}

.ag-empty-card p {
    margin: 0;
    color: #64748b;
}

.ag-live-notification {
    min-width: 280px;
    max-width: 420px;
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9999;
    padding: 15px 18px;
    border-radius: 13px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 18px 38px rgba(15, 23, 42, .2);
    opacity: 0;
    pointer-events: none;
    transform: translateY(18px);
    transition:
        opacity .2s ease,
        transform .2s ease;
}

.ag-live-notification.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.ag-live-notification--success {
    background: #047857;
}

.ag-live-notification--error {
    background: #b91c1c;
}

@keyframes ag-toggle-pulse {
    from {
        box-shadow: 0 0 0 0 rgba(37, 99, 235, .15);
    }

    to {
        box-shadow: 0 0 0 7px rgba(37, 99, 235, .08);
    }
}

@media (max-width: 1150px) {
    .ag-summary-grid {
        grid-template-columns: 1fr 1fr;
    }

    .ag-score-card {
        grid-column: 1 / -1;
    }

    .ag-workspace-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .ag-client-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .ag-summary-grid,
    .ag-score-card {
        grid-template-columns: 1fr;
    }

    .ag-score-ring {
        margin: auto;
    }

    .ag-control-item {
        grid-template-columns: 1fr;
    }

    .ag-control-points {
        padding-left: 59px;
        text-align: left;
    }

    .ag-live-notification {
        right: 16px;
        bottom: 16px;
        left: 16px;
        min-width: 0;
    }
}
</style>

@endsection

@push('scripts')
    <script
        src="{{ asset('js/security-workspace.js') }}"
        defer
    ></script>
@endpush