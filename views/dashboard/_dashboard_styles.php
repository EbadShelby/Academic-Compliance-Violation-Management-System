<style>
/* ── Dashboard shared styles — Phase 13 ──────────────────────────────────────── */

/* ── Welcome banner ─────────────────────────────────────────────────────────── */
.dash-banner {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: linear-gradient(135deg, rgba(79,70,229,.22), rgba(124,58,237,.15));
    border: 1px solid rgba(79,70,229,.3);
    border-radius: 1rem;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}
.dash-banner-icon {
    width: 52px; height: 52px;
    border-radius: .875rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #fff; flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(79,70,229,.35);
}
.dash-banner-title { font-size: 1.125rem; font-weight: 700; color: #f8fafc; }
.dash-banner-sub   { font-size: .875rem; color: #94a3b8; margin-top: .15rem; }
.dash-banner-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

/* ── Quick action buttons ───────────────────────────────────────────────────── */
.btn-dash-action {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem .95rem;
    border-radius: .5rem;
    font-size: .8125rem; font-weight: 600;
    text-decoration: none;
    transition: filter .15s, transform .1s;
    white-space: nowrap;
}
.btn-dash-action:hover { filter: brightness(1.12); transform: translateY(-1px); }
.btn-primary-dash { background: linear-gradient(135deg,#4f46e5,#7c3aed); color: #fff; }
.btn-warning-dash { background: linear-gradient(135deg,#d97706,#f59e0b); color: #1c1917; }
.btn-neutral-dash { background: rgba(255,255,255,.08); color: #cbd5e1; border: 1px solid rgba(255,255,255,.1); }

/* ── KPI cards ──────────────────────────────────────────────────────────────── */
.kpi-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .875rem;
    padding: 1.25rem;
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(0,0,0,.35);
}
.kpi-icon {
    width: 40px; height: 40px;
    border-radius: .625rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.0625rem; color: #fff;
    margin-bottom: .75rem;
}
.kpi-value {
    font-size: 1.75rem; font-weight: 800; color: #f8fafc; line-height: 1;
    margin-bottom: .3rem;
}
.kpi-label {
    font-size: .75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: #64748b;
}
.kpi-link {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .8rem; color: #818cf8; text-decoration: none;
    margin-top: .6rem;
}
.kpi-link:hover { color: #a5b4fc; }

/* ── Secondary KPI (compact) ────────────────────────────────────────────────── */
.kpi-card-sm {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .75rem;
    padding: .875rem 1rem;
    display: flex; align-items: center; gap: .875rem;
    font-size: 1.25rem;
}
.kpi-sm-val   { font-size: 1.1rem; font-weight: 700; color: #f8fafc; line-height: 1.2; }
.kpi-sm-label { font-size: .7rem; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }

/* ── Chart cards ────────────────────────────────────────────────────────────── */
.chart-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .875rem;
    padding: 1.25rem;
}
.chart-card-title {
    font-size: .875rem; font-weight: 700; color: #e2e8f0;
    margin-bottom: 1rem;
    display: flex; align-items: center;
}

/* ── Table cards ────────────────────────────────────────────────────────────── */
.dash-table-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: .875rem;
    overflow: hidden;
}
.dash-table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-size: .875rem; font-weight: 700; color: #e2e8f0;
}
.dash-table-link {
    font-size: .8125rem; color: #818cf8; text-decoration: none;
}
.dash-table-link:hover { color: #a5b4fc; }
.dash-table {
    width: 100%; border-collapse: collapse;
    font-size: .8125rem; color: #cbd5e1;
}
.dash-table thead th {
    padding: .65rem 1rem;
    font-size: .6875rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: #64748b; border-bottom: 1px solid rgba(255,255,255,.06);
    white-space: nowrap;
}
.dash-table tbody td {
    padding: .65rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    vertical-align: middle;
}
.dash-table tbody tr:last-child td { border-bottom: none; }
.dash-table tbody tr:hover { background: rgba(255,255,255,.03); }
.text-muted-sm { color: #64748b; font-size: .8rem; }

/* ── Badges ─────────────────────────────────────────────────────────────────── */
.sev-badge {
    display: inline-block; padding: 2px 8px;
    border-radius: 9px; font-size: .7rem; font-weight: 700;
}
.sev-minor    { background: rgba(52,211,153,.15);  color: #34d399; }
.sev-moderate { background: rgba(251,191,36,.15);  color: #fbbf24; }
.sev-major    { background: rgba(249,115,22,.15);  color: #f97316; }
.sev-critical { background: rgba(248,113,113,.15); color: #f87171; }

.status-badge {
    display: inline-block; padding: 2px 8px;
    border-radius: 9px; font-size: .7rem; font-weight: 700;
}
.status-pending      { background: rgba(251,191,36,.15);  color: #fbbf24; }
.status-under_review { background: rgba(129,140,248,.15); color: #818cf8; }
.status-resolved     { background: rgba(52,211,153,.15);  color: #34d399; }
.status-rejected     { background: rgba(248,113,113,.15); color: #f87171; }
.status-closed       { background: rgba(148,163,184,.12); color: #94a3b8; }

/* repeat-offender count badge */
.badge-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 24px; height: 24px;
    padding: 0 6px;
    border-radius: 9px;
    background: rgba(248,113,113,.18);
    color: #f87171;
    font-size: .75rem; font-weight: 700;
}

/* ── Empty states ────────────────────────────────────────────────────────────── */
.dash-empty {
    display: flex; flex-direction: column; align-items: center;
    padding: 2.5rem 1rem; text-align: center;
    color: #475569; font-size: .875rem; gap: .5rem;
}
.dash-empty i { font-size: 2rem; }

/* ── Info card ───────────────────────────────────────────────────────────────── */
.dash-info-card {
    display: flex; align-items: flex-start; gap: .875rem;
    background: rgba(129,140,248,.08);
    border: 1px solid rgba(129,140,248,.2);
    border-radius: .75rem;
    padding: 1rem 1.25rem;
    font-size: .875rem; color: #a5b4fc; line-height: 1.55;
}
</style>
