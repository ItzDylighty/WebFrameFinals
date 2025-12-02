<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EseePark - Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #fdf2f2, #f3f4f6); margin: 0; padding: 0; color: #111; }
        .app-header { background-color: #800000; color: #fff; padding: 12px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
        .app-brand { font-size: 20px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .app-nav { display: flex; gap: 16px; align-items: center; }
        .nav-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; padding: 6px 10px; border-radius: 4px; }
        .nav-link:hover, .nav-link-active { background-color: rgba(255,255,255,0.2); color: #fff; }
        .nav-button { background: transparent; color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.4); border-radius: 4px; padding: 6px 12px; cursor: pointer; }
        .main { padding: 24px 32px; }
        .container { width: 100%; max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .filters { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .filters select, .filters input[type="date"] { padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .filters select:focus, .filters input[type="date"]:focus { outline: none; border-color: #800000; box-shadow: 0 0 4px rgba(128,0,0,0.3); }
        .btn { border: none; border-radius: 4px; padding: 8px 12px; font-size: 14px; cursor: pointer; }
        .btn-primary { background-color: #800000; color: #fff; }
        .btn-primary:hover { background-color: #a00000; }
        .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
        .kpi { padding: 14px; border-radius: 6px; background: #f9fafb; border: 1px solid #ececec; }
        .kpi-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.08em; }
        .kpi-value { font-size: 24px; font-weight: 700; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        @media (min-width: 980px) { .grid { grid-template-columns: 1fr 1fr; } }
        .card { padding: 14px; border-radius: 8px; background: #fff; border: 1px solid #ececec; }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="app-brand">EseePark Admin</div>
        <nav class="app-nav">
            <a href="{{ url('/admin/reservations') }}" class="nav-link">Admin Reservations</a>
            <a href="{{ url('/admin/walk-in') }}" class="nav-link">Walk-In Entry</a>
            <a href="{{ url('/admin/analytics') }}" class="nav-link nav-link-active">Analytics</a>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="nav-button">Logout</button>
            </form>
        </nav>
    </header>

    <main class="main">
        <div class="container">
            <div class="filters">
                <label>Range
                    <select id="range">
                        <option value="14d" selected>Last 14 days</option>
                        <option value="7d">Last 7 days</option>
                        <option value="30d">Last 30 days</option>
                    </select>
                </label>
                <label>Date
                    <input type="date" id="date" value="{{ now()->toDateString() }}">
                </label>
                <button id="apply" class="btn btn-primary">Apply</button>
            </div>

            <section class="kpis" id="kpis">
                <div class="kpi"><div class="kpi-label">Total</div><div class="kpi-value" id="kpiTotal">0</div></div>
                <div class="kpi"><div class="kpi-label">Pending</div><div class="kpi-value" id="kpiPending">0</div></div>
                <div class="kpi"><div class="kpi-label">Approved</div><div class="kpi-value" id="kpiApproved">0</div></div>
                <div class="kpi"><div class="kpi-label">Rejected</div><div class="kpi-value" id="kpiRejected">0</div></div>
                <div class="kpi"><div class="kpi-label">Completed</div><div class="kpi-value" id="kpiCompleted">0</div></div>
                <div class="kpi"><div class="kpi-label">Approval Rate</div><div class="kpi-value" id="kpiApprovalRate">0%</div></div>
                <div class="kpi"><div class="kpi-label">Avg Stay (min)</div><div class="kpi-value" id="kpiAvgStay">0</div></div>
            </section>

            <section class="grid">
                <div class="card">
                    <h3>Reservations (last N days)</h3>
                    <canvas id="chartByDay" height="180"></canvas>
                </div>
                <div class="card">
                    <h3>Occupancy by Hour (selected date)</h3>
                    <canvas id="chartOccHour" height="180"></canvas>
                </div>
                <div class="card" style="grid-column: 1 / -1;">
                    <h3>Utilization by Area (selected date)</h3>
                    <canvas id="chartUtilArea" height="180"></canvas>
                </div>
            </section>
        </div>
    </main>

    <script>
        function buildUrl(base, params={}) {
            const usp = new URLSearchParams(params);
            usp.set('noCache', '1');
            usp.set('_ts', Date.now());
            return `${base}?${usp.toString()}`;
        }

        async function fetchJSON(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
            return data.data || data;
        }

        const els = {
            range: document.getElementById('range'),
            date: document.getElementById('date'),
            kpiTotal: document.getElementById('kpiTotal'),
            kpiPending: document.getElementById('kpiPending'),
            kpiApproved: document.getElementById('kpiApproved'),
            kpiRejected: document.getElementById('kpiRejected'),
            kpiCompleted: document.getElementById('kpiCompleted'),
            kpiApprovalRate: document.getElementById('kpiApprovalRate'),
            kpiAvgStay: document.getElementById('kpiAvgStay'),
        };

        let chartByDay, chartOccHour, chartUtilArea;

        function ensureChart(ctx, type, config) {
            if (ctx._chart) ctx._chart.destroy();
            ctx._chart = new Chart(ctx, { type, data: config.data, options: config.options || {} });
            return ctx._chart;
        }

        async function loadOverview() {
            const range = els.range.value;
            const d = await fetchJSON(buildUrl('/admin/api/analytics/overview', { range }));
            els.kpiTotal.textContent = d.totals.total;
            els.kpiPending.textContent = d.totals.pending;
            els.kpiApproved.textContent = d.totals.approved;
            els.kpiRejected.textContent = d.totals.rejected;
            els.kpiCompleted.textContent = d.totals.completed;
            els.kpiApprovalRate.textContent = d.approvalRate + '%';
            els.kpiAvgStay.textContent = Math.round(d.avgStayMinutes || 0);
        }

        async function loadByDay() {
            const days = parseInt(els.range.value) || 14;
            const d = await fetchJSON(buildUrl('/admin/api/analytics/reservations-by-day', { days }));
            const labels = d.map(x => x.date);
            const ds = (key, color) => ({ label: key, data: d.map(x => x[key]), borderColor: color, backgroundColor: color + '33', fill: true, tension: 0.2 });
            const ctx = document.getElementById('chartByDay').getContext('2d');
            ensureChart(ctx, 'line', {
                data: { labels, datasets: [
                    ds('pending', '#f59e0b'),
                    ds('approved', '#22c55e'),
                    ds('rejected', '#ef4444'),
                    ds('completed', '#3b82f6'),
                ]}
            });
        }

        async function loadOccHour() {
            const date = els.date.value;
            const d = await fetchJSON(buildUrl('/admin/api/analytics/occupancy-by-hour', { date }));
            const labels = Array.from({length:24}, (_,i)=> i + ':00');
            const ctx = document.getElementById('chartOccHour').getContext('2d');
            ensureChart(ctx, 'bar', {
                data: { labels, datasets: [{ label: 'Occupancy', data: d.hours, backgroundColor: '#22c55e' }] },
                options: { scales: { y: { beginAtZero: true } } }
            });
        }

        async function loadUtilArea() {
            const date = els.date.value;
            const d = await fetchJSON(buildUrl('/admin/api/analytics/utilization-by-area', { date }));
            const labels = d.map(x => x.area_code);
            const ctx = document.getElementById('chartUtilArea').getContext('2d');
            const approvedVals = d.map(x => x.approved);
            const utilRaw = d.map(x => Number(x.utilization_pct) || 0);
            const utilVals = utilRaw.every(v => v <= 1) ? utilRaw.map(v => v * 100) : utilRaw; // fallback if server returned 0..1
            ensureChart(ctx, 'bar', {
                data: { labels, datasets: [
                    { label: 'Approved', data: approvedVals, backgroundColor: '#3b82f6' },
                    { label: 'Utilization %', data: utilVals, backgroundColor: '#a78bfa', yAxisID: 'y1' },
                ] },
                options: { responsive: true, scales: { 
                    y: { beginAtZero: true, title: { display: true, text: 'Approved' } }, 
                    y1: { type: 'linear', position: 'right', beginAtZero: true, min: 0, max: 100, ticks: { callback: v => v + '%' }, title: { display: true, text: '%' } } 
                } }
            });
        }

        async function loadAll() {
            await Promise.all([
                loadOverview(),
                loadByDay(),
                loadOccHour(),
                loadUtilArea(),
            ]);
        }

        document.getElementById('apply').addEventListener('click', () => loadAll());

        document.addEventListener('DOMContentLoaded', () => {
            loadAll();
        });
    </script>
</body>
</html>
