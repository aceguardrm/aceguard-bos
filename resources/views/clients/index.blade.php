@extends('layouts.aceguard')

@section('title', 'Workspaces')

@section('page-title', 'Workspaces')

@section(
    'page-subtitle',
    'Manage organisations and open their Business Pulse™ and cyber security workspaces.'
)

@section('content')

<div class="ag-workspaces">

    {{-- ================================================================
        HERO
    ================================================================ --}}

    <section class="ag-workspaces-hero">

        <div>

            <span class="ag-kicker">
                AceGuard BOS
            </span>

            <h2>
                Organisation Workspaces
            </h2>

            <p>
                Each workspace brings together business health,
                cyber security and executive intelligence for one organisation.
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
        SUMMARY
    ================================================================ --}}

    <section class="ag-workspace-summary">

        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--blue">
                <i class="fas fa-building"></i>
            </div>

            <div>

                <span>
                    Total Workspaces
                </span>

                <strong>
                    {{ $clients->count() }}
                </strong>

                <small>
                    Organisations currently registered
                </small>

            </div>

        </article>


        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--green">
                <i class="fas fa-circle-check"></i>
            </div>

            <div>

                <span>
                    Active
                </span>

                <strong>
                    {{
                        $clients->filter(
                            fn ($client) =>
                                strtolower($client->status ?? '')
                                === 'active'
                        )->count()
                    }}
                </strong>

                <small>
                    Active organisation workspaces
                </small>

            </div>

        </article>


        <article class="ag-summary-card">

            <div class="ag-summary-icon ag-summary-icon--amber">
                <i class="fas fa-user-clock"></i>
            </div>

            <div>

                <span>
                    Leads
                </span>

                <strong>
                    {{
                        $clients->filter(
                            fn ($client) =>
                                strtolower($client->status ?? '')
                                === 'lead'
                        )->count()
                    }}
                </strong>

                <small>
                    Prospective organisations
                </small>

            </div>

        </article>

    </section>


    {{-- ================================================================
        WORKSPACE PORTFOLIO
    ================================================================ --}}

    <section class="ag-workspace-panel">

        <div class="ag-panel-heading">

            <div>

                <span class="ag-kicker ag-kicker--dark">
                    Organisation Portfolio
                </span>

                <h3>
                    Your Workspaces
                </h3>

                <p>
                    Open a workspace, review its Business Pulse™,
                    or manage its cyber security posture.
                </p>

            </div>

        </div>


        @forelse($clients as $client)

            @php

                $status =
                    strtolower(
                        $client->status
                        ?? 'inactive'
                    );

                $statusClass =
                    match ($status) {
                        'active' => 'ag-status--active',
                        'lead' => 'ag-status--lead',
                        default => 'ag-status--inactive',
                    };

                $statusLabel =
                    ucfirst($status);

            @endphp


            <article class="ag-workspace-card">

                {{-- ====================================================
                    IDENTITY
                ==================================================== --}}

                <div class="ag-workspace-card__identity">

                    <div class="ag-workspace-avatar">

                        {{
                            strtoupper(
                                substr(
                                    $client->company_name
                                        ?? 'W',
                                    0,
                                    1
                                )
                            )
                        }}

                    </div>


                    <div>

                        <div class="ag-workspace-title-row">

                            <h4>
                                {{ $client->company_name }}
                            </h4>

                            <span
                                class="
                                    ag-status
                                    {{ $statusClass }}
                                "
                            >
                                {{ $statusLabel }}
                            </span>

                        </div>


                        <div class="ag-workspace-meta">

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

                </div>


                {{-- ====================================================
                    BOS MODULES
                ==================================================== --}}

                <div class="ag-workspace-modules">

                    {{-- Workspace --}}

                    <a
                        href="{{ route('clients.show', $client) }}"
                        class="
                            ag-module-button
                            ag-module-button--overview
                        "
                    >

                        <div class="ag-module-icon">
                            <i class="fas fa-building"></i>
                        </div>

                        <div>

                            <strong>
                                Workspace
                            </strong>

                            <span>
                                Client overview
                            </span>

                        </div>

                        <i
                            class="
                                fas
                                fa-arrow-right
                                ag-module-arrow
                            "
                        ></i>

                    </a>


                    {{-- Business Pulse™ --}}

                    <a
                        href="{{ route('business-pulse.workspace', $client) }}"
                        class="
                            ag-module-button
                            ag-module-button--pulse
                        "
                    >

                        <div class="ag-module-icon">
                            <i class="fas fa-heart-pulse"></i>
                        </div>

                        <div>

                            <strong>
                                Business Pulse™
                            </strong>

                            <span>
                                Business health
                            </span>

                        </div>

                        <i
                            class="
                                fas
                                fa-arrow-right
                                ag-module-arrow
                            "
                        ></i>

                    </a>


                    {{-- Cyber Centre --}}

                    <a
                        href="{{ route('security.workspace', $client) }}"
                        class="
                            ag-module-button
                            ag-module-button--security
                        "
                    >

                        <div class="ag-module-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>

                        <div>

                            <strong>
                                Cyber Centre
                            </strong>

                            <span>
                                Security posture
                            </span>

                        </div>

                        <i
                            class="
                                fas
                                fa-arrow-right
                                ag-module-arrow
                            "
                        ></i>

                    </a>

                </div>


                {{-- ====================================================
                    ADMINISTRATION
                ==================================================== --}}

                <div class="ag-workspace-actions">

                    <a
                        href="{{ route('clients.edit', $client) }}"
                        class="ag-action-button"
                    >
                        <i class="fas fa-pen"></i>
                        Edit
                    </a>


                    <form
                        action="{{ route('clients.destroy', $client) }}"
                        method="POST"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to delete this workspace?'
                            );
                        "
                    >

                        @csrf
                        @method('DELETE')


                        <button
                            type="submit"
                            class="
                                ag-action-button
                                ag-action-button--danger
                            "
                        >
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>

                    </form>

                </div>

            </article>


        @empty

            <div class="ag-empty-state">

                <div class="ag-empty-icon">
                    <i class="fas fa-building-circle-exclamation"></i>
                </div>

                <h3>
                    No workspaces yet
                </h3>

                <p>
                    Create your first organisation workspace
                    to begin using Business Pulse™
                    and the Cyber Centre.
                </p>

                <a
                    href="{{ route('clients.create') }}"
                    class="ag-button ag-button--primary"
                >
                    <i class="fas fa-plus"></i>
                    Create First Workspace
                </a>

            </div>

        @endforelse

    </section>

</div>


<style>

/* ================================================================
   WORKSPACES
================================================================ */

.ag-workspaces {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/* ================================================================
   HERO
================================================================ */

.ag-workspaces-hero {
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

.ag-workspaces-hero h2 {
    margin: 5px 0 9px;
    font-size: 29px;
}

.ag-workspaces-hero p {
    max-width: 700px;

    margin: 0;

    color: #cbd5e1;

    line-height: 1.6;
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

    font-size: 13px;
    font-weight: 650;
}

.ag-alert--success {
    color: #047857;

    border: 1px solid #a7f3d0;

    background: #ecfdf5;
}


/* ================================================================
   SUMMARY
================================================================ */

.ag-workspace-summary {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 18px;
}

.ag-summary-card {
    display: flex;
    align-items: center;

    gap: 15px;

    padding: 20px;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    background: #ffffff;

    box-shadow:
        0 8px 22px
        rgba(15, 23, 42, .04);
}

.ag-summary-icon {
    width: 49px;
    height: 49px;

    flex: 0 0 49px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    font-size: 18px;
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

.ag-summary-card span,
.ag-summary-card strong,
.ag-summary-card small {
    display: block;
}

.ag-summary-card span {
    color: #64748b;

    font-size: 13px;
}

.ag-summary-card strong {
    margin: 2px 0;

    color: #0f172a;

    font-size: 25px;
}

.ag-summary-card small {
    color: #94a3b8;
}


/* ================================================================
   MAIN PANEL
================================================================ */

.ag-workspace-panel {
    padding: 25px;

    border: 1px solid #e5e7eb;

    border-radius: 20px;

    background: #ffffff;

    box-shadow:
        0 12px 30px
        rgba(15, 23, 42, .05);
}

.ag-panel-heading {
    margin-bottom: 22px;
}

.ag-panel-heading h3 {
    margin: 5px 0 7px;

    color: #0f172a;

    font-size: 22px;
}

.ag-panel-heading p {
    margin: 0;

    color: #64748b;

    line-height: 1.6;
}


/* ================================================================
   WORKSPACE CARD
================================================================ */

.ag-workspace-card {
    display: grid;

    grid-template-columns:
        minmax(270px, 1fr)
        minmax(560px, 1.4fr)
        auto;

    align-items: center;

    gap: 24px;

    padding: 22px 0;

    border-top:
        1px solid #eef2f7;
}

.ag-workspace-card:first-of-type {
    border-top: 0;
}

.ag-workspace-card__identity {
    display: flex;
    align-items: center;

    gap: 14px;

    min-width: 0;
}

.ag-workspace-avatar {
    width: 52px;
    height: 52px;

    flex: 0 0 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );

    font-size: 20px;
    font-weight: 850;
}

.ag-workspace-title-row {
    display: flex;
    align-items: center;

    flex-wrap: wrap;

    gap: 8px;
}

.ag-workspace-title-row h4 {
    margin: 0;

    color: #0f172a;

    font-size: 17px;
}

.ag-workspace-meta {
    display: flex;
    flex-wrap: wrap;

    gap: 8px 14px;

    margin-top: 7px;

    color: #64748b;

    font-size: 12px;
}

.ag-workspace-meta span {
    display: inline-flex;
    align-items: center;

    gap: 6px;
}

.ag-workspace-meta i {
    color: #94a3b8;
}


/* ================================================================
   STATUS
================================================================ */

.ag-status {
    display: inline-flex;

    padding: 4px 8px;

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
   MODULE BUTTONS
================================================================ */

.ag-workspace-modules {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 10px;

    position: relative;
    z-index: 5;
}

.ag-module-button {
    min-width: 0;

    position: relative;
    z-index: 6;

    display: grid;

    grid-template-columns:
        38px
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 10px;

    padding: 12px;

    border: 1px solid #e5e7eb;

    border-radius: 13px;

    color: inherit;

    background: #ffffff;

    text-decoration: none;

    cursor: pointer;

    pointer-events: auto;

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
}

.ag-module-button:hover {
    color: inherit;

    text-decoration: none;

    transform: translateY(-2px);

    border-color: #cbd5e1;

    box-shadow:
        0 10px 22px
        rgba(15, 23, 42, .06);
}

.ag-module-button * {
    pointer-events: none;
}

.ag-module-icon {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;
}

.ag-module-button strong,
.ag-module-button span {
    display: block;
}

.ag-module-button strong {
    color: #0f172a;

    font-size: 12px;
}

.ag-module-button span {
    margin-top: 2px;

    color: #94a3b8;

    font-size: 10px;
}

.ag-module-arrow {
    color: #94a3b8;

    font-size: 10px;
}

.ag-module-button--overview
.ag-module-icon {
    color: #2563eb;

    background: #dbeafe;
}

.ag-module-button--pulse
.ag-module-icon {
    color: #059669;

    background: #d1fae5;
}

.ag-module-button--security
.ag-module-icon {
    color: #7c3aed;

    background: #ede9fe;
}


/* ================================================================
   ADMIN ACTIONS
================================================================ */

.ag-workspace-actions {
    display: flex;
    align-items: center;

    gap: 7px;
}

.ag-action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;

    padding: 9px 11px;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    color: #475569;

    background: #ffffff;

    font-size: 11px;
    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition: .18s ease;
}

.ag-action-button:hover {
    color: #0f172a;

    border-color: #cbd5e1;

    background: #f8fafc;

    text-decoration: none;
}

.ag-action-button--danger {
    color: #b91c1c;
}

.ag-action-button--danger:hover {
    color: #991b1b;

    border-color: #fecaca;

    background: #fef2f2;
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

    margin: 0 auto 16px;

    border-radius: 18px;

    color: #2563eb;

    background: #dbeafe;

    font-size: 24px;
}

.ag-empty-state h3 {
    margin: 0 0 7px;

    color: #0f172a;
}

.ag-empty-state p {
    max-width: 520px;

    margin:
        0
        auto
        20px;

    color: #64748b;

    line-height: 1.6;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1300px) {

    .ag-workspace-card {
        grid-template-columns: 1fr;
    }

    .ag-workspace-actions {
        justify-content: flex-start;
    }

}


@media (max-width: 900px) {

    .ag-workspace-summary {
        grid-template-columns: 1fr;
    }

    .ag-workspace-modules {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 700px) {

    .ag-workspaces-hero {
        align-items: flex-start;

        flex-direction: column;
    }

    .ag-workspace-panel {
        padding: 18px;
    }

    .ag-workspace-card__identity {
        align-items: flex-start;
    }

    .ag-workspace-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .ag-workspace-actions form {
        width: 100%;
    }

    .ag-action-button {
        width: 100%;
    }

}

</style>

@endsection