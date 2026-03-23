<?php
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
Auth::requireRole('admin', 'login.php');
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — EstateCore Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../core/ui/devcore.css">
<link rel="stylesheet" href="../../../core/ui/parts/_icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }
  .chart-wrap    { height:260px; position:relative; }
  .chart-wrap-sm { height:220px; position:relative; display:flex; align-items:center; justify-content:center; }
  .feed-item { display:flex; align-items:flex-start; gap:12px; padding:14px 0; border-bottom:1px solid var(--dc-border); }
  .feed-item:last-child { border-bottom:none; }
  .feed-avatar {
    width:36px; height:36px; border-radius:50%;
    background:var(--dc-accent-glow);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
  }
  .feed-avatar .dc-icon { color:var(--dc-accent); }
</style>
</head>
<body>

<aside class="dc-sidebar">
  <div class="dc-sidebar__logo">EstateCore</div>
  <div class="dc-sidebar__section">Main</div>
  <a href="dashboard.php"    class="dc-sidebar__link active"><i class="dc-icon dc-icon-bar-chart dc-icon-sm"></i> Dashboard</a>
  <a href="properties.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-home dc-icon-sm"></i> Properties</a>
  <a href="inquiries.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-inbox dc-icon-sm"></i> Inquiries</a>
  <a href="qr-generator.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-qr-code dc-icon-sm"></i> QR Codes</a>
  <div class="dc-sidebar__section" style="margin-top:auto">Account</div>
  <a href="../index.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-globe dc-icon-sm"></i> View Site</a>
  <a href="logout.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-log-out dc-icon-sm"></i> Logout</a>
</aside>

<div class="dc-with-sidebar">
  <nav class="dc-nav">
    <div class="dc-nav__brand" style="font-size:1rem;font-weight:600">Dashboard</div>
    <div class="dc-flex dc-items-center" style="gap:16px">
      <div class="dc-live" id="liveIndicator">
        <div class="dc-live__dot"></div>
        <span id="liveText">Live</span>
      </div>
      <div class="dc-caption dc-text-dim dc-flex dc-items-center" style="gap:6px">
        <i class="dc-icon dc-icon-user dc-icon-sm"></i> <?= htmlspecialchars($user['name']) ?>
      </div>
    </div>
  </nav>

  <div class="dc-container dc-section">

    <div class="dc-flex-between dc-mb-lg">
      <div>
        <h1 class="dc-h2">Analytics Dashboard</h1>
        <p class="dc-body">Real-time performance overview for your listings</p>
      </div>
      <div id="lastUpdated" class="dc-caption dc-text-dim"></div>
    </div>

    <!-- KPI CARDS -->
    <div class="dc-grid dc-grid-4 dc-mb-lg">
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-home dc-icon-md"></i></div>
        <div class="dc-stat__value" id="kpi-listings" data-count="0">—</div>
        <div class="dc-stat__label">Total Listings</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-inbox dc-icon-md"></i></div>
        <div class="dc-stat__value" id="kpi-inquiries" data-count="0">—</div>
        <div class="dc-stat__label">New Inquiries Today</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-eye dc-icon-md"></i></div>
        <div class="dc-stat__value" id="kpi-views" data-count="0">—</div>
        <div class="dc-stat__label">Total Page Views</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-qr-code dc-icon-md"></i></div>
        <div class="dc-stat__value" id="kpi-qr" data-count="0">—</div>
        <div class="dc-stat__label">QR Scans (All Time)</div>
      </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="dc-grid dc-grid-2 dc-mb-lg">
      <div class="dc-card">
        <div class="dc-flex-between dc-mb">
          <h3 class="dc-h4">Inquiries per Day</h3>
          <span class="dc-badge dc-badge-accent">Last 30 Days</span>
        </div>
        <div class="chart-wrap"><canvas id="chartInquiries"></canvas></div>
      </div>
      <div class="dc-card">
        <div class="dc-flex-between dc-mb">
          <h3 class="dc-h4">Listings by Type</h3>
          <span class="dc-badge dc-badge-neutral">Distribution</span>
        </div>
        <div class="chart-wrap-sm">
          <canvas id="chartByType" style="max-width:220px;max-height:220px"></canvas>
        </div>
      </div>
    </div>

    <!-- CHART: Top Properties by Views -->
    <div class="dc-card dc-mb-lg">
      <div class="dc-flex-between dc-mb">
        <h3 class="dc-h4">Top Properties by Views</h3>
        <span class="dc-badge dc-badge-accent">Top 10</span>
      </div>
      <div class="chart-wrap"><canvas id="chartTopViews"></canvas></div>
    </div>

    <!-- LIVE FEED + STATUS BREAKDOWN -->
    <div class="dc-grid dc-grid-2">
      <div class="dc-card">
        <div class="dc-flex-between dc-mb">
          <h3 class="dc-h4">Recent Inquiries</h3>
          <div class="dc-live"><div class="dc-live__dot"></div><span>Live</span></div>
        </div>
        <div id="inquiryFeed">
          <div class="dc-skeleton" style="height:60px;border-radius:8px;margin-bottom:8px"></div>
          <div class="dc-skeleton" style="height:60px;border-radius:8px;margin-bottom:8px"></div>
          <div class="dc-skeleton" style="height:60px;border-radius:8px"></div>
        </div>
      </div>
      <div class="dc-card">
        <div class="dc-flex-between dc-mb">
          <h3 class="dc-h4">Status Breakdown</h3>
          <span class="dc-badge dc-badge-neutral">All Listings</span>
        </div>
        <div id="statusBreakdown" style="display:flex;flex-direction:column;gap:12px">
          <div class="dc-skeleton" style="height:48px;border-radius:8px"></div>
          <div class="dc-skeleton" style="height:48px;border-radius:8px"></div>
          <div class="dc-skeleton" style="height:48px;border-radius:8px"></div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="../../../core/ui/devcore.js"></script>
<script src="../../../core/utils/helpers.js"></script>
<script>
let chartInquiries = null, chartByType = null, chartTopViews = null;

const statusConfig = {
  new:       { label:'New',       cls:'dc-badge-success' },
  contacted: { label:'Contacted', cls:'dc-badge-warning' },
  closed:    { label:'Closed',    cls:'dc-badge-neutral' },
};

function escHtml(s) {
  if (window.DCHelpers && typeof window.DCHelpers.escHtml === 'function') {
    return window.DCHelpers.escHtml(s);
  }
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function loadDashboard() {
  try {
    const res = await DC.get('../api/analytics.php');
    const d   = res.data;

    document.getElementById('kpi-listings').textContent  = DCFormat.number(d.kpi.total_listings);
    document.getElementById('kpi-inquiries').textContent = DCFormat.number(d.kpi.inquiries_today);
    document.getElementById('kpi-views').textContent     = DCFormat.number(d.kpi.total_views);
    document.getElementById('kpi-qr').textContent        = DCFormat.number(d.kpi.qr_scans_week);

    const dayLabels = d.charts.inquiries_by_day.map(r => DCFormat.date(r.date));
    const dayCounts = d.charts.inquiries_by_day.map(r => parseInt(r.count));
    if (chartInquiries) chartInquiries.destroy();
    chartInquiries = DCChart.line('chartInquiries', dayLabels, [{ label:'Inquiries', data:dayCounts }]);

    const topLabels = d.charts.top_by_views.map(r => r.title.substring(0,28) + (r.title.length > 28 ? '...' : ''));
    const topViews  = d.charts.top_by_views.map(r => parseInt(r.views));
    if (chartTopViews) chartTopViews.destroy();
    chartTopViews = DCChart.bar('chartTopViews', topLabels, [{ label:'Views', data:topViews }]);

    const typeLabels = d.charts.by_type.map(r => r.type.charAt(0).toUpperCase() + r.type.slice(1));
    const typeCounts = d.charts.by_type.map(r => parseInt(r.count));
    if (chartByType) chartByType.destroy();
    chartByType = DCChart.doughnut('chartByType', typeLabels, typeCounts);

    renderFeed(d.recent_inquiries);
    renderStatusBreakdown(d.status_breakdown, d.kpi.total_listings);
    document.getElementById('lastUpdated').textContent = 'Updated ' + DCFormat.time(new Date());
  } catch (err) {
    Toast.error('Failed to load dashboard: ' + err.message);
  }
}

function renderFeed(inquiries) {
  const feed = document.getElementById('inquiryFeed');
  if (!inquiries.length) { feed.innerHTML = '<div class="dc-empty">No inquiries yet</div>'; return; }
  feed.innerHTML = inquiries.map(i => `
    <div class="feed-item">
      <div class="feed-avatar"><i class="dc-icon dc-icon-mail dc-icon-sm"></i></div>
      <div style="flex:1;min-width:0">
        <div class="dc-flex-between">
          <span style="font-weight:600;font-size:0.9rem">${escHtml(i.name)}</span>
          <span class="dc-badge ${statusConfig[i.status]?.cls || 'dc-badge-neutral'}" style="font-size:0.7rem">
            ${statusConfig[i.status]?.label || i.status}
          </span>
        </div>
        <div class="dc-caption dc-text-dim dc-truncate" style="margin-top:2px">${escHtml(i.property_title)}</div>
        <div class="dc-caption dc-text-dim" style="margin-top:2px">${DCFormat.ago(i.created_at)}</div>
      </div>
    </div>
  `).join('');
}

function renderStatusBreakdown(breakdown, total) {
  const map = {
    available:   { label:'Available',   icon:'dc-icon-check' },
    under_offer: { label:'Under Offer', icon:'dc-icon-clock' },
    sold:        { label:'Sold',        icon:'dc-icon-tag'   },
  };
  document.getElementById('statusBreakdown').innerHTML = breakdown.map(b => {
    const pct = total > 0 ? Math.round((b.count / total) * 100) : 0;
    const cfg = map[b.status] || { label: b.status, icon: 'dc-icon-info' };
    return `
      <div>
        <div class="dc-flex-between" style="margin-bottom:6px">
          <span style="font-size:0.9rem;display:flex;align-items:center;gap:6px">
            <i class="dc-icon ${cfg.icon} dc-icon-sm"></i> ${cfg.label}
          </span>
          <span style="font-weight:700">${b.count} <span class="dc-caption">(${pct}%)</span></span>
        </div>
        <div style="height:6px;background:var(--dc-border);border-radius:3px;overflow:hidden">
          <div style="height:100%;width:${pct}%;background:var(--dc-accent);border-radius:3px;transition:width 0.6s var(--dc-ease)"></div>
        </div>
      </div>`;
  }).join('');
}

const livePoller = new LivePoller('../api/live.php', (res) => {
  const count = res.data.new_inquiry_count;
  if (count > 0) {
    document.getElementById('liveText').textContent = `${count} new inquiry${count !== 1 ? 's' : ''}`;
    document.getElementById('liveIndicator').style.color = 'var(--dc-warning)';
  }
}, 4000);

loadDashboard();
livePoller.start();
setInterval(loadDashboard, 30000);
</script>
</body>
</html>