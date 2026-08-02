@php
    $score = $security['score'] ?? 0;
    $rating = $security['rating'] ?? 'Not assessed';

    $scoreClass = match (true) {
        $score >= 90 => 'ag-score-excellent',
        $score >= 75 => 'ag-score-good',
        $score >= 60 => 'ag-score-fair',
        $score >= 40 => 'ag-score-poor',
        default => 'ag-score-critical',
    };
@endphp

<section class="ag-security-card">

    <div class="ag-security-card__header">
        <div>
            <span class="ag-eyebrow">Cyber resilience</span>
            <h2>Business Security Score</h2>
        </div>

        <div class="ag-security-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
    </div>

    <div class="ag-security-card__body">

        <div class="ag-score-ring {{ $scoreClass }}">
            <div class="ag-score-ring__inner">
                <strong>{{ $score }}%</strong>
                <span>{{ $rating }}</span>
            </div>
        </div>

        <div class="ag-security-summary">
            <p>
                Your current score is based on
                <strong>{{ count($security['controls'] ?? []) }}</strong>
                security controls.
            </p>

            <div class="ag-score-progress">
                <div
                    class="ag-score-progress__bar"
                    style="width: {{ $score }}%;">
                </div>
            </div>

            <div class="ag-score-meta">
                <span>
                    {{ $security['earned'] ?? 0 }}
                    of
                    {{ $security['maximum'] ?? 100 }}
                    points achieved
                </span>

                <span>{{ $rating }}</span>
            </div>
        </div>

    </div>

</section>

<style>
.ag-security-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:24px;
    box-shadow:0 12px 30px rgba(15,23,42,.06);
}

.ag-security-card__header{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    margin-bottom:24px;
}

.ag-security-card__header h2{
    margin:4px 0 0;
    font-size:22px;
}

.ag-eyebrow{
    color:#64748b;
    font-size:12px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.ag-security-icon{
    width:48px;
    height:48px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:14px;
    background:#eff6ff;
    color:#2563eb;
    font-size:20px;
}

.ag-security-card__body{
    display:grid;
    grid-template-columns:180px 1fr;
    gap:28px;
    align-items:center;
}

.ag-score-ring{
    width:160px;
    height:160px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:conic-gradient(
        currentColor calc(var(--score, 0) * 1%),
        #e5e7eb 0
    );
    position:relative;
}

.ag-score-ring::before{
    content:"";
    position:absolute;
    width:126px;
    height:126px;
    border-radius:50%;
    background:#ffffff;
}

.ag-score-ring__inner{
    position:relative;
    z-index:1;
    text-align:center;
}

.ag-score-ring__inner strong{
    display:block;
    font-size:36px;
    line-height:1;
    color:#0f172a;
}

.ag-score-ring__inner span{
    display:block;
    margin-top:8px;
    color:#64748b;
    font-size:13px;
    font-weight:600;
}

.ag-score-excellent{
    color:#10b981;
    --score:{{ $score }};
}

.ag-score-good{
    color:#2563eb;
    --score:{{ $score }};
}

.ag-score-fair{
    color:#f59e0b;
    --score:{{ $score }};
}

.ag-score-poor{
    color:#f97316;
    --score:{{ $score }};
}

.ag-score-critical{
    color:#dc2626;
    --score:{{ $score }};
}

.ag-security-summary p{
    margin:0 0 18px;
    color:#475569;
}

.ag-score-progress{
    height:10px;
    border-radius:999px;
    overflow:hidden;
    background:#e5e7eb;
}

.ag-score-progress__bar{
    height:100%;
    border-radius:999px;
    background:#2563eb;
}

.ag-score-meta{
    display:flex;
    justify-content:space-between;
    margin-top:10px;
    color:#64748b;
    font-size:13px;
}

@media (max-width: 768px){
    .ag-security-card__body{
        grid-template-columns:1fr;
    }

    .ag-score-ring{
        margin:auto;
    }
}
</style>