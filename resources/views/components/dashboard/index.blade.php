@extends('layouts.aceguard')

@section('title', 'Home')

@section('page-title', 'Home')

@section(
    'page-subtitle',
    'Your business overview, security posture and priorities.'
)

@section('content')

@php
    $clientCount = \App\Models\Client::count();

    $businessScore = $health['overall'] ?? 0;
    $securityScore = $security['score'] ?? 0;

    $controls = collect($security['controls'] ?? []);

    $incompleteControls = $controls
        ->filter(fn ($control) => !($control['enabled'] ?? false))
        ->take(3);
@endphp

<div class="ag-dashboard">

    {{-- Welcome panel --}}
    <section class="ag-welcome-panel">

        <div>
            <span class="ag-welcome-label">Executive workspace</span>

            <h2>
                Good evening, {{ Auth::user()->name ?? 'Founder' }}.
            </h2>

            <p>
                Here is what requires your attention across the business today.
            </p>
        </div>

        <a href="{{ route('clients.create') }}" class="ag-primary-button">
            <i class="fas fa-plus"></i>
            New Client
        </a>

    </section>

    {{-- Main scores --}}
    <div class="ag-dashboard-grid ag-dashboard-grid--scores">

        <section class="ag-pulse-card">

            <div class="ag-card-heading">
                <div>
                    <span class="ag-eyebrow">Business Pulse™</span>
                    <h3>Overall Business Health</h3>
                </div>

                <span class="ag-status-pill ag-status-pill--success">
                    Healthy
                </span>
            </div>

            <div class="ag-pulse-content">

                <div class="ag-pulse-score">
                    {{ $businessScore }}%
                </div>

                <div class="ag-pulse-summary">
                    <strong>
                        Your business is performing well.
                    </strong>

                    <p>
                        This score combines security, finance, compliance,
                        documents and operational readiness.
                    </p>

                    <div class="ag-progress-track">
                        <div
                            class="ag-progress-value ag-progress-value--success"
                            style="width: {{ $businessScore }}%;">
                        </div>
                    </div>
                </div>

            </div>

        </section>

        @if($security)
            @include(
                'components.dashboard.security-score',
                ['security' => $security]
            )
        @endif

    </div>

    {{-- Statistics --}}
    <div class="ag-stat-grid">

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

        <div class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--purple">
                <i class="fas fa-folder-open"></i>
            </div>

            <div>
                <span>Open Matters</span>
                <strong>0</strong>
                <small>No matters requiring action</small>
            </div>

        </div>

        <div class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--amber">
                <i class="fas fa-list-check"></i>
            </div>

            <div>
                <span>Outstanding Tasks</span>
                <strong>0</strong>
                <small>Everything is currently clear</small>
            </div>

        </div>

        <div class="ag-stat-card">

            <div class="ag-stat-icon ag-stat-icon--green">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <span>Security Score</span>
                <strong>{{ $securityScore }}%</strong>
                <small>{{ $security['rating'] ?? 'Not assessed' }}</small>
            </div>

        </div>

    </div>

    {{-- Priorities and activity --}}
    <div class="ag-dashboard-grid ag-dashboard-grid--lower">

        <section class="ag-panel">

            <div class="ag-card-heading">
                <div>
                    <span class="ag-eyebrow">Action centre</span>
                    <h3>Today's Priorities</h3>
                </div>

                <span class="ag-priority-count">
                    {{ $incompleteControls->count() }}
                </span>
            </div>

            <div class="ag-priority-list">

                @forelse($incompleteControls as $name => $control)

                    <div class="ag-priority-item">

                        <span class="ag-priority-dot ag-priority-dot--danger">
                        </span>

                        <div>
                            <strong>{{ $name }}</strong>

                            <p>
                                Complete this security control to improve the
                                organisation's protection.
                            </p>
                        </div>

                        <span class="ag-priority-points">
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

        </section>

        <section class="ag-panel">

            <div class="ag-card-heading">
                <div>
                    <span class="ag-eyebrow">Latest updates</span>
                    <h3>Recent Activity</h3>
                </div>
            </div>

            <div class="ag-activity-list">

                <div class="ag-activity-item">

                    <div class="ag-activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>

                    <div>
                        <strong>Client workspace available</strong>
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
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <div>
                        <strong>Business Pulse calculated</strong>
                        <p>
                            Current overall health score:
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
                            Current security score:
                            {{ $securityScore }}%.
                        </p>
                    </div>

                </div>

            </div>

        </section>

    </div>

</div>

<style>
.ag-dashboard{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.ag-welcome-panel{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:24px;
    padding:30px;
    border-radius:22px;
    color:#ffffff;
    background:
        radial-gradient(
            circle at top right,
            rgba(37,99,235,.55),
            transparent 38%
        ),
        linear-gradient(135deg,#0f172a,#172554);
    box-shadow:0 18px 40px rgba(15,23,42,.16);
}

.ag-welcome-label,
.ag-eyebrow{
    display:block;
    margin-bottom:7px;
    color:#64748b;
    font-size:12px;
    font-weight:700;
    letter-spacing:.09em;
    text-transform:uppercase;
}

.ag-welcome-label{
    color:#93c5fd;
}

.ag-welcome-panel h2{
    margin:0;
    font-size:28px;
}

.ag-welcome-panel p{
    margin:9px 0 0;
    color:#cbd5e1;
}

.ag-primary-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:9px;
    min-width:138px;
    padding:13px 18px;
    border-radius:12px;
    color:#0f172a;
    background:#ffffff;
    font-weight:700;
    text-decoration:none;
    transition:.2s ease;
}

.ag-primary-button:hover{
    color:#0f172a;
    transform:translateY(-2px);
}

.ag-dashboard-grid{
    display:grid;
    gap:24px;
}

.ag-dashboard-grid--scores{
    grid-template-columns:1fr 1fr;
}

.ag-dashboard-grid--lower{
    grid-template-columns:1.35fr .85fr;
}

.ag-pulse-card,
.ag-panel{
    padding:24px;
    border:1px solid #e5e7eb;
    border-radius:18px;
    background:#ffffff;
    box-shadow:0 12px 30px rgba(15,23,42,.05);
}

.ag-card-heading{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    margin-bottom:24px;
}

.ag-card-heading h3{
    margin:0;
    color:#0f172a;
    font-size:21px;
}

.ag-status-pill{
    padding:7px 11px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.ag-status-pill--success{
    color:#047857;
    background:#d1fae5;
}

.ag-pulse-content{
    display:grid;
    grid-template-columns:150px 1fr;
    align-items:center;
    gap:24px;
}

.ag-pulse-score{
    font-size:54px;
    font-weight:800;
    color:#0f172a;
}

.ag-pulse-summary strong{
    color:#0f172a;
}

.ag-pulse-summary p{
    margin:7px 0 18px;
    color:#64748b;
    line-height:1.6;
}

.ag-progress-track{
    width:100%;
    height:10px;
    overflow:hidden;
    border-radius:999px;
    background:#e5e7eb;
}

.ag-progress-value{
    height:100%;
    border-radius:inherit;
}

.ag-progress-value--success{
    background:#10b981;
}

.ag-stat-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
}

.ag-stat-card{
    display:flex;
    align-items:center;
    gap:16px;
    padding:20px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    color:inherit;
    background:#ffffff;
    text-decoration:none;
    box-shadow:0 8px 22px rgba(15,23,42,.04);
    transition:.2s ease;
}

.ag-stat-card:hover{
    color:inherit;
    transform:translateY(-3px);
    box-shadow:0 14px 28px rgba(15,23,42,.08);
}

.ag-stat-icon{
    width:48px;
    height:48px;
    flex:0 0 48px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:14px;
    font-size:18px;
}

.ag-stat-icon--blue{
    color:#2563eb;
    background:#dbeafe;
}

.ag-stat-icon--purple{
    color:#7c3aed;
    background:#ede9fe;
}

.ag-stat-icon--amber{
    color:#d97706;
    background:#fef3c7;
}

.ag-stat-icon--green{
    color:#059669;
    background:#d1fae5;
}

.ag-stat-card span,
.ag-stat-card strong,
.ag-stat-card small{
    display:block;
}

.ag-stat-card span{
    color:#64748b;
    font-size:13px;
}

.ag-stat-card strong{
    margin:3px 0;
    color:#0f172a;
    font-size:25px;
}

.ag-stat-card small{
    color:#94a3b8;
}

.ag-priority-count{
    min-width:31px;
    height:31px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    color:#ffffff;
    background:#dc2626;
    font-size:13px;
    font-weight:800;
}

.ag-priority-list,
.ag-activity-list{
    display:flex;
    flex-direction:column;
}

.ag-priority-item{
    display:grid;
    grid-template-columns:13px 1fr auto;
    align-items:center;
    gap:14px;
    padding:17px 0;
    border-top:1px solid #eef2f7;
}

.ag-priority-item:first-child{
    border-top:0;
}

.ag-priority-dot{
    width:11px;
    height:11px;
    border-radius:50%;
}

.ag-priority-dot--danger{
    background:#ef4444;
    box-shadow:0 0 0 5px #fee2e2;
}

.ag-priority-item strong{
    color:#0f172a;
}

.ag-priority-item p{
    margin:4px 0 0;
    color:#64748b;
    font-size:13px;
}

.ag-priority-points{
    white-space:nowrap;
    color:#2563eb;
    font-size:12px;
    font-weight:700;
}

.ag-empty-state{
    display:flex;
    align-items:center;
    gap:14px;
    padding:24px 0;
    color:#059669;
}

.ag-empty-state i{
    font-size:24px;
}

.ag-empty-state p{
    margin:4px 0 0;
    color:#64748b;
}

.ag-activity-item{
    display:flex;
    align-items:flex-start;
    gap:14px;
    padding:17px 0;
    border-top:1px solid #eef2f7;
}

.ag-activity-item:first-child{
    border-top:0;
}

.ag-activity-icon{
    width:38px;
    height:38px;
    flex:0 0 38px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:11px;
    color:#2563eb;
    background:#eff6ff;
}

.ag-activity-item strong{
    color:#0f172a;
}

.ag-activity-item p{
    margin:4px 0 0;
    color:#64748b;
    font-size:13px;
}

@media (max-width:1100px){
    .ag-dashboard-grid--scores,
    .ag-dashboard-grid--lower{
        grid-template-columns:1fr;
    }

    .ag-stat-grid{
        grid-template-columns:repeat(2,1fr);
    }
}

@media (max-width:700px){
    .ag-welcome-panel{
        align-items:flex-start;
        flex-direction:column;
    }

    .ag-pulse-content,
    .ag-stat-grid{
        grid-template-columns:1fr;
    }

    .ag-priority-item{
        grid-template-columns:13px 1fr;
    }

    .ag-priority-points{
        grid-column:2;
    }
}
</style>

@endsection