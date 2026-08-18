<aside class="ag-sidebar">

    <div class="ag-brand">

        <a
            href="{{ route('dashboard') }}"
            class="ag-brand__link"
        >

            <div class="ag-brand__mark">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <strong>AceGuard BOS</strong>
                <span>Business Operating System</span>
            </div>

        </a>

    </div>


    @php

        /*
        |--------------------------------------------------------------------------
        | Current Workspace Context
        |--------------------------------------------------------------------------
        */

        $currentClient = request()->route('client');

        /*
        |--------------------------------------------------------------------------
        | Business Pulse™ Destination
        |--------------------------------------------------------------------------
        */

        $businessPulseUrl = $currentClient
            ? route(
                'business-pulse.workspace',
                $currentClient
            )
            : route('clients.index');

        /*
        |--------------------------------------------------------------------------
        | Cyber Centre Destination
        |--------------------------------------------------------------------------
        */

        $cyberCentreUrl = $currentClient
            ? route(
                'security.workspace',
                $currentClient
            )
            : route('clients.index');

    @endphp


    <nav class="ag-navigation">

        {{-- ============================================================
            BUSINESS
        ============================================================ --}}

        <span class="ag-navigation__label">
            Business
        </span>


        <a
            href="{{ route('dashboard') }}"
            class="
                ag-navigation__item
                {{
                    request()->routeIs('dashboard')
                        ? 'is-active'
                        : ''
                }}
            "
        >
            <i class="fas fa-chart-pie"></i>

            <span>
                Executive Dashboard
            </span>
        </a>


        <a
            href="{{ route('clients.index') }}"
            class="
                ag-navigation__item
                {{
                    request()->routeIs('clients.*')
                        ? 'is-active'
                        : ''
                }}
            "
        >
            <i class="fas fa-building"></i>

            <span>
                Workspaces
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-diagram-project"></i>

            <span>
                Projects
            </span>

            <span class="ag-navigation__badge">
                Soon
            </span>
        </a>


        <a
            href="{{ $businessPulseUrl }}"
            class="
                ag-navigation__item
                {{
                    request()->routeIs('business-pulse.*')
                        ? 'is-active'
                        : ''
                }}
            "
            title="{{
                $currentClient
                    ? 'Open Business Pulse™ assessment'
                    : 'Select a workspace to open Business Pulse™'
            }}"
        >
            <i class="fas fa-heart-pulse"></i>

            <span>
                Business Pulse™
            </span>

            @if(request()->routeIs('business-pulse.*'))

                <span class="ag-navigation__status">
                    Live
                </span>

            @endif
        </a>


        {{-- ============================================================
            PROTECTION
        ============================================================ --}}

        <span class="ag-navigation__label">
            Protection
        </span>


        <a
            href="{{ $cyberCentreUrl }}"
            class="
                ag-navigation__item
                {{
                    request()->routeIs('security.*')
                        ? 'is-active'
                        : ''
                }}
            "
            title="{{
                $currentClient
                    ? 'Open Cyber Centre'
                    : 'Select a workspace to open Cyber Centre'
            }}"
        >
            <i class="fas fa-shield-halved"></i>

            <span>
                Cyber Centre
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fab fa-microsoft"></i>

            <span>
                Microsoft 365
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-clipboard-check"></i>

            <span>
                Compliance
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-triangle-exclamation"></i>

            <span>
                Risk Centre
            </span>
        </a>


        {{-- ============================================================
            OPERATIONS
        ============================================================ --}}

        <span class="ag-navigation__label">
            Operations
        </span>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-folder-open"></i>

            <span>
                Knowledge Hub
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-calendar-days"></i>

            <span>
                Planner
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-chart-line"></i>

            <span>
                Reports
            </span>
        </a>


        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-robot"></i>

            <span>
                AI Executive
            </span>

            <span class="ag-navigation__badge">
                Soon
            </span>
        </a>

    </nav>


    <div class="ag-sidebar__footer">

        <a
            href="#"
            class="ag-navigation__item"
        >
            <i class="fas fa-gear"></i>

            <span>
                Organisation
            </span>
        </a>


        <div class="ag-version">

            <span>
                AceGuard BOS
            </span>

            <strong>
                v0.3
            </strong>

        </div>

    </div>

</aside>


<style>

/* ================================================================
   SIDEBAR CONTAINER
================================================================ */

.ag-sidebar {
    width: 250px;
    min-width: 250px;
    max-width: 250px;

    flex: 0 0 250px;

    min-height: 100vh;
    height: 100vh;

    position: sticky;
    top: 0;

    display: flex;
    flex-direction: column;

    overflow: hidden;

    color: #e2e8f0;

    background:
        linear-gradient(
            180deg,
            #0f172a 0%,
            #0b162b 100%
        );

    border-right:
        1px solid rgba(148, 163, 184, .12);

    z-index: 100;
}


/* ================================================================
   BRAND
================================================================ */

.ag-brand {
    flex: 0 0 auto;

    padding: 22px 20px;

    border-bottom:
        1px solid rgba(148, 163, 184, .12);
}

.ag-brand__link {
    display: flex;
    align-items: center;

    gap: 13px;

    color: #ffffff;

    text-decoration: none;
}

.ag-brand__link:hover {
    color: #ffffff;
    text-decoration: none;
}

.ag-brand__mark {
    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #1d4ed8
        );

    box-shadow:
        0 10px 24px
        rgba(37, 99, 235, .28);
}

.ag-brand__mark i {
    font-size: 18px;
}

.ag-brand__link > div:last-child {
    min-width: 0;
}

.ag-brand__link strong,
.ag-brand__link span {
    display: block;
}

.ag-brand__link strong {
    color: #ffffff;

    font-size: 17px;
    font-weight: 800;

    line-height: 1.2;

    white-space: nowrap;
}

.ag-brand__link span {
    margin-top: 4px;

    color: #93a4bd;

    font-size: 10px;

    line-height: 1.2;

    white-space: nowrap;
}


/* ================================================================
   NAVIGATION
================================================================ */

.ag-navigation {
    flex: 1 1 auto;

    min-height: 0;

    display: flex;
    flex-direction: column;

    padding: 18px 14px 24px;

    overflow-x: hidden;
    overflow-y: auto;
}

.ag-navigation__label {
    display: block;

    margin:
        11px
        11px
        8px;

    color: #647fa8;

    font-size: 9px;
    font-weight: 800;

    letter-spacing: .14em;

    text-transform: uppercase;
}

.ag-navigation__label:not(:first-child) {
    margin-top: 22px;
}


/* ================================================================
   ITEMS
================================================================ */

.ag-navigation__item {
    width: 100%;

    min-height: 45px;

    position: relative;

    display: flex;
    align-items: center;

    gap: 12px;

    margin: 2px 0;

    padding: 11px 13px;

    border-radius: 11px;

    color: #d8e1ef;

    font-size: 13px;
    font-weight: 650;

    text-decoration: none;

    box-sizing: border-box;

    transition:
        color .18s ease,
        background .18s ease,
        transform .18s ease,
        box-shadow .18s ease;
}

.ag-navigation__item:hover {
    color: #ffffff;

    background:
        rgba(59, 130, 246, .09);

    text-decoration: none;

    transform: translateX(2px);
}

.ag-navigation__item i {
    width: 20px;

    flex: 0 0 20px;

    color: #8da2c2;

    font-size: 15px;

    text-align: center;
}

.ag-navigation__item:hover i {
    color: #bfdbfe;
}

.ag-navigation__item
> span:not(.ag-navigation__badge):not(.ag-navigation__status) {
    min-width: 0;
    flex: 1;
}


/* ================================================================
   ACTIVE STATE
================================================================ */

.ag-navigation__item.is-active {
    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #2f6dea
        );

    box-shadow:
        0 8px 20px
        rgba(37, 99, 235, .19);
}

.ag-navigation__item.is-active i {
    color: #ffffff;
}

.ag-navigation__item.is-active:hover {
    transform: none;
}


/* ================================================================
   BADGES
================================================================ */

.ag-navigation__badge,
.ag-navigation__status {
    flex: 0 0 auto;

    margin-left: auto;

    padding: 4px 7px;

    border-radius: 999px;

    font-size: 8px;
    font-weight: 800;

    letter-spacing: .04em;

    text-transform: uppercase;
}

.ag-navigation__badge {
    color: #bfdbfe;

    background:
        rgba(37, 99, 235, .24);
}

.ag-navigation__status {
    color: #6ee7b7;

    background:
        rgba(16, 185, 129, .15);
}

.ag-navigation__item.is-active
.ag-navigation__status {
    color: #ffffff;

    background:
        rgba(255, 255, 255, .18);
}


/* ================================================================
   FOOTER
================================================================ */

.ag-sidebar__footer {
    flex: 0 0 auto;

    padding: 13px 14px 17px;

    border-top:
        1px solid rgba(148, 163, 184, .12);

    background:
        rgba(15, 23, 42, .36);
}

.ag-sidebar__footer
.ag-navigation__item {
    margin-bottom: 10px;
}

.ag-version {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 4px 13px;

    color: #64748b;

    font-size: 9px;
}

.ag-version strong {
    color: #93c5fd;

    font-weight: 800;
}


/* ================================================================
   SCROLLBAR
================================================================ */

.ag-navigation {
    scrollbar-width: thin;

    scrollbar-color:
        rgba(148, 163, 184, .18)
        transparent;
}

.ag-navigation::-webkit-scrollbar {
    width: 5px;
}

.ag-navigation::-webkit-scrollbar-track {
    background: transparent;
}

.ag-navigation::-webkit-scrollbar-thumb {
    border-radius: 999px;

    background:
        rgba(148, 163, 184, .18);
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 900px) {

    .ag-sidebar {
        width: 74px;
        min-width: 74px;
        max-width: 74px;

        flex-basis: 74px;
    }

    .ag-brand {
        padding: 18px 10px;
    }

    .ag-brand__link {
        justify-content: center;
    }

    .ag-brand__link > div:last-child {
        display: none;
    }

    .ag-navigation {
        padding: 15px 8px;
    }

    .ag-navigation__label {
        display: none;
    }

    .ag-navigation__item {
        justify-content: center;

        padding: 12px 9px;
    }

    .ag-navigation__item i {
        width: auto;

        flex: 0 0 auto;

        font-size: 17px;
    }

    .ag-navigation__item
    > span:not(.ag-navigation__badge):not(.ag-navigation__status) {
        display: none;
    }

    .ag-navigation__badge,
    .ag-navigation__status {
        display: none;
    }

    .ag-sidebar__footer {
        padding: 12px 8px;
    }

    .ag-version {
        justify-content: center;

        padding: 6px 0;
    }

    .ag-version span {
        display: none;
    }

}

</style>