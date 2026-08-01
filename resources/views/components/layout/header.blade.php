<header class="ag-header">

    <div>
        <h1 class="ag-page-title">
            @yield('page-title', 'Dashboard')
        </h1>

        <p class="ag-page-subtitle">
            @yield('page-subtitle', 'Welcome back to AceGuard.')
        </p>
    </div>

    <div class="ag-header-actions">

        <button class="ag-icon-button" type="button" aria-label="Notifications">
            🔔
        </button>

        <div class="ag-user">
            <div class="ag-user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>

            <div>
                <strong>{{ Auth::user()->name ?? 'User' }}</strong>
                <span>Administrator</span>
            </div>
        </div>

    </div>

</header>

<style>
.ag-header{
    min-height:88px;
    background:#ffffff;
    border-bottom:1px solid var(--ag-border);
    padding:20px 32px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.ag-page-title{
    margin:0;
    font-size:24px;
    font-weight:700;
}

.ag-page-subtitle{
    margin:4px 0 0;
    color:#64748b;
    font-size:14px;
}

.ag-header-actions{
    display:flex;
    align-items:center;
    gap:18px;
}

.ag-icon-button{
    border:1px solid var(--ag-border);
    background:#ffffff;
    width:42px;
    height:42px;
    border-radius:12px;
    cursor:pointer;
}

.ag-user{
    display:flex;
    align-items:center;
    gap:10px;
}

.ag-user-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    background:var(--ag-primary);
    color:#ffffff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
}

.ag-user strong,
.ag-user span{
    display:block;
}

.ag-user span{
    font-size:12px;
    color:#64748b;
}
</style>