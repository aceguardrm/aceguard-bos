<aside class="ag-sidebar">

    <div class="ag-logo">

        <h2>🛡️ AceGuard</h2>

        <span>Secure CRM Enterprise</span>

    </div>

    <nav class="ag-nav">

        <a href="{{ route('dashboard') }}" class="active">
            <i class="fas fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('clients.index') }}">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </a>

        <a href="#">
            <i class="fas fa-folder-open"></i>
            <span>Matters</span>
        </a>

        <a href="#">
            <i class="fas fa-shield-halved"></i>
            <span>Security Centre</span>
        </a>

        <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Business Pulse™</span>
        </a>

        <a href="#">
            <i class="fas fa-calendar"></i>
            <span>Calendar</span>
        </a>

        <a href="#">
            <i class="fas fa-file-lines"></i>
            <span>Documents</span>
        </a>

        <a href="#">
            <i class="fas fa-gear"></i>
            <span>Settings</span>
        </a>

    </nav>

</aside>

<style>

.ag-sidebar{
    width:260px;
    background:#0f172a;
    color:#fff;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

.ag-logo{
    padding:28px;
    border-bottom:1px solid rgba(255,255,255,.08);
}

.ag-logo h2{
    margin:0;
    font-size:24px;
}

.ag-logo span{
    color:#94a3b8;
    font-size:13px;
}

.ag-nav{
    display:flex;
    flex-direction:column;
    padding:18px;
    gap:6px;
}

.ag-nav a{
    color:#cbd5e1;
    text-decoration:none;
    padding:12px 16px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:12px;
    transition:.2s;
}

.ag-nav a:hover{
    background:#1e293b;
    color:#fff;
}

.ag-nav a.active{
    background:#2563eb;
    color:#fff;
}

</style>