<aside class="ag-sidebar">

    <div class="ag-brand">
        <a href="{{ route('dashboard') }}" class="ag-brand__link">

            <div class="ag-brand__mark">
                <i class="fas fa-shield-halved"></i>
            </div>

            <div>
                <strong>AceGuard BOS</strong>
                <span>Business Operating System</span>
            </div>

        </a>
    </div>

    <nav class="ag-navigation">

        <span class="ag-navigation__label">
            Business
        </span>

        <a
            href="{{ route('dashboard') }}"
            class="ag-navigation__item
                {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
        >
            <i class="fas fa-chart-pie"></i>
            <span>Executive Dashboard</span>
        </a>

        <a
            href="{{ route('clients.index') }}"
            class="ag-navigation__item
                {{ request()->routeIs('clients.*')
                    || request()->routeIs('security.*')
                        ? 'is-active'
                        : '' }}"
        >
            <i class="fas fa-building"></i>
            <span>Workspaces</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-diagram-project"></i>
            <span>Projects</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-heart-pulse"></i>
            <span>Business Pulse™</span>
        </a>

        <span class="ag-navigation__label">
            Protection
        </span>

        <a
            href="{{ request()->route('client')
                ? route('security.workspace', request()->route('client'))
                : '#' }}"
            class="ag-navigation__item
                {{ request()->routeIs('security.*') ? 'is-active' : '' }}"
        >
            <i class="fas fa-shield-halved"></i>
            <span>Cyber Centre</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fab fa-microsoft"></i>
            <span>Microsoft 365</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-clipboard-check"></i>
            <span>Compliance</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Risk Centre</span>
        </a>

        <span class="ag-navigation__label">
            Operations
        </span>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-folder-open"></i>
            <span>Knowledge Hub</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-calendar-days"></i>
            <span>Planner</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-chart-line"></i>
            <span>Reports</span>
        </a>

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-robot"></i>
            <span>AI Executive</span>

            <span class="ag-navigation__badge">
                Soon
            </span>
        </a>

    </nav>

    <div class="ag-sidebar__footer">

        <a href="#" class="ag-navigation__item">
            <i class="fas fa-gear"></i>
            <span>Organisation</span>
        </a>

        <div class="ag-version">
            <span>AceGuard BOS</span>
            <strong>v0.3</strong>
        </div>

    </div>

</aside>

<style>
.ag-sidebar {
    width: 260px;
    min-height: 100vh;
    position: sticky;
    top: 0;
    display: flex;
    flex: 0 0 260px;
    flex-direction: column;
    color: #ffffff;
    background: #0b1428;
    border-right: 1px solid rgba(255, 255, 255, .06);
}

.ag-brand {
    padding: 25px 21px;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.ag-brand__link {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #ffffff;
    text-decoration: none;
}

.ag-brand__link:hover {
    color: #ffffff;
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
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 8px 20px rgba(37, 99, 235, .3);
}

.ag-brand strong,
.ag-brand span {
    display: block;
}

.ag-brand strong {
    font-size: 19px;
    line-height: 1.2;
}

.ag-brand span {
    margin-top: 4px;
    color: #93a4bf;
    font-size: 11px;
}

.ag-navigation {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
    padding: 19px 14px;
}

.ag-navigation__label {
    margin: 17px 12px 7px;
    color: #64748b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.ag-navigation__label:first-child {
    margin-top: 2px;
}

.ag-navigation__item {
    min-height: 46px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 11px 13px;
    border-radius: 11px;
    color: #cbd5e1;
    font-size: 14px;
    font-weight: 550;
    text-decoration: none;
    transition:
        color .18s ease,
        background .18s ease,
        transform .18s ease;
}

.ag-navigation__item i {
    width: 19px;
    color: #8292ad;
    text-align: center;
    transition: color .18s ease;
}

.ag-navigation__item:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, .06);
    transform: translateX(2px);
}

.ag-navigation__item:hover i {
    color: #93c5fd;
}

.ag-navigation__item.is-active {
    color: #ffffff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 8px 18px rgba(37, 99, 235, .22);
}

.ag-navigation__item.is-active i {
    color: #ffffff;
}

.ag-navigation__badge {
    margin-left: auto;
    padding: 3px 7px;
    border-radius: 999px;
    color: #bfdbfe;
    background: rgba(37, 99, 235, .2);
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
}

.ag-sidebar__footer {
    padding: 12px 14px 18px;
    border-top: 1px solid rgba(255, 255, 255, .07);
}

.ag-version {
    display: flex;
    justify-content: space-between;
    margin: 12px 12px 0;
    color: #64748b;
    font-size: 10px;
}

.ag-version strong {
    color: #94a3b8;
}

@media (max-width: 900px) {
    .ag-sidebar {
        width: 84px;
        flex-basis: 84px;
    }

    .ag-brand {
        padding: 21px;
    }

    .ag-brand__link > div:last-child,
    .ag-navigation__item span,
    .ag-navigation__label,
    .ag-version {
        display: none;
    }

    .ag-navigation__item {
        justify-content: center;
    }

    .ag-navigation__item i {
        width: auto;
        font-size: 17px;
    }
}
</style>