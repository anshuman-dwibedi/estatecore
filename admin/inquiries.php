<?php
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
Auth::requireRole('admin', 'login.php');

$user = Auth::user();
$db   = Database::getInstance();

$counts = $db->fetchOne(
    "SELECT COUNT(*) as total,
            SUM(status='new') as new_count,
            SUM(status='contacted') as contacted_count,
            SUM(status='closed') as closed_count
     FROM inquiries"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inquiries — EstateCore Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../core/ui/devcore.css">
<link rel="stylesheet" href="../../../core/ui/parts/_icons.css">
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }
  .inq-message { max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.85rem; color:var(--dc-text-3); }
</style>
</head>
<body>

<aside class="dc-sidebar">
  <div class="dc-sidebar__logo">EstateCore</div>
  <div class="dc-sidebar__section">Main</div>
  <a href="dashboard.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-bar-chart dc-icon-sm"></i> Dashboard</a>
  <a href="properties.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-home dc-icon-sm"></i> Properties</a>
  <a href="inquiries.php"    class="dc-sidebar__link active"><i class="dc-icon dc-icon-inbox dc-icon-sm"></i> Inquiries</a>
  <a href="qr-generator.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-qr-code dc-icon-sm"></i> QR Codes</a>
  <div class="dc-sidebar__section" style="margin-top:auto">Account</div>
  <a href="../index.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-globe dc-icon-sm"></i> View Site</a>
  <a href="logout.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-log-out dc-icon-sm"></i> Logout</a>
</aside>

<div class="dc-with-sidebar">
  <nav class="dc-nav">
    <div class="dc-nav__brand" style="font-size:1rem;font-weight:600">Inquiries</div>
    <div class="dc-flex dc-items-center" style="gap:16px">
      <div class="dc-live" id="liveIndicator"><div class="dc-live__dot"></div><span id="liveText">Live</span></div>
    </div>
  </nav>

  <div class="dc-container dc-section">

    <!-- KPI Row -->
    <div class="dc-grid dc-grid-4 dc-mb-lg">
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-inbox dc-icon-md"></i></div>
        <div class="dc-stat__value"><?= number_format($counts['total']) ?></div>
        <div class="dc-stat__label">Total Inquiries</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon" style="background:rgba(34,211,160,0.15)"><i class="dc-icon dc-icon-mail dc-icon-md" style="color:var(--dc-success)"></i></div>
        <div class="dc-stat__value" style="color:var(--dc-success)"><?= number_format($counts['new_count']) ?></div>
        <div class="dc-stat__label">New / Unread</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon" style="background:rgba(245,166,35,0.15)"><i class="dc-icon dc-icon-phone dc-icon-md" style="color:var(--dc-warning)"></i></div>
        <div class="dc-stat__value" style="color:var(--dc-warning)"><?= number_format($counts['contacted_count']) ?></div>
        <div class="dc-stat__label">Contacted</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon" style="background:rgba(255,92,106,0.1)"><i class="dc-icon dc-icon-check dc-icon-md" style="color:var(--dc-text-2)"></i></div>
        <div class="dc-stat__value" style="color:var(--dc-text-2)"><?= number_format($counts['closed_count']) ?></div>
        <div class="dc-stat__label">Closed</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="dc-flex dc-mb-lg" style="gap:10px;flex-wrap:wrap">
      <input type="text" id="searchInput" class="dc-input" style="width:240px" placeholder="Search name, email, property...">
      <select id="filterStatus" class="dc-select" style="width:160px">
        <option value="">All Statuses</option>
        <option value="new">New</option>
        <option value="contacted">Contacted</option>
        <option value="closed">Closed</option>
      </select>
      <button class="dc-btn dc-btn-ghost dc-btn-sm dc-flex dc-items-center" onclick="loadInquiries()" style="gap:6px">
        <i class="dc-icon dc-icon-refresh dc-icon-sm"></i> Refresh
      </button>
    </div>

    <!-- Table -->
    <div class="dc-table-wrap">
      <table class="dc-table">
        <thead>
          <tr>
            <th>Contact</th><th>Property</th><th>Message</th>
            <th>Phone</th><th>Date</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="inquiryTableBody">
          <tr><td colspan="7" style="text-align:center;padding:32px">
            <div class="dc-skeleton" style="height:40px;border-radius:8px;max-width:400px;margin:0 auto"></div>
          </td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="dc-flex-between" style="margin-top:16px">
      <span class="dc-caption dc-text-dim" id="pageInfo"></span>
      <div class="dc-flex" style="gap:8px" id="pageBtns"></div>
    </div>
  </div>
</div>

<!-- INQUIRY DETAIL MODAL -->
<div class="dc-modal-overlay" id="inqModal">
  <div class="dc-modal" style="max-width:560px">
    <div class="dc-flex-between" style="margin-bottom:20px">
      <h2 class="dc-h3">Inquiry Detail</h2>
      <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" data-modal-close="inqModal">
        <i class="dc-icon dc-icon-x dc-icon-sm"></i>
      </button>
    </div>
    <div id="inqModalContent"></div>
    <div class="dc-flex" style="gap:10px;margin-top:20px;flex-wrap:wrap" id="inqModalActions"></div>
  </div>
</div>

<script src="../../../core/ui/devcore.js"></script>
<script src="../../../core/utils/helpers.js"></script>
<script>
let currentPage = 1;

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

async function loadInquiries(page = 1) {
  currentPage = page;
  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('filterStatus').value;
  const params = new URLSearchParams({ page, per_page:20 });
  if (search) params.set('search', search);
  if (status) params.set('status', status);
  try {
    const res  = await DC.get(`../api/inquiries.php?${params}`);
    const data = res.data;
    const meta = res.meta;
    const tbody = document.getElementById('inquiryTableBody');
    if (!data.length) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:32px"><div class="dc-empty">No inquiries found</div></td></tr>`;
    } else {
      tbody.innerHTML = data.map(i => `
        <tr data-id="${i.id}">
          <td>
            <div style="font-weight:600;font-size:0.9rem">${escHtml(i.name)}</div>
            <div class="dc-caption dc-text-dim">${escHtml(i.email)}</div>
          </td>
          <td>
            <div style="font-size:0.875rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(i.property_title)}</div>
            <div class="dc-caption dc-text-dim">${escHtml(i.property_address)}</div>
          </td>
          <td><div class="inq-message">${escHtml(i.message)}</div></td>
          <td class="dc-caption">${escHtml(i.phone) || '—'}</td>
          <td class="dc-caption">${DCFormat.date(i.created_at)}<br><span class="dc-text-dim">${DCFormat.ago(i.created_at)}</span></td>
          <td><span class="dc-badge ${statusConfig[i.status]?.cls}">${statusConfig[i.status]?.label}</span></td>
          <td>
            <div class="dc-flex" style="gap:6px">
              <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" onclick="viewInquiry(${i.id})" title="View">
                <i class="dc-icon dc-icon-eye dc-icon-sm"></i>
              </button>
              <button class="dc-btn dc-btn-danger dc-btn-sm dc-btn-icon" onclick="deleteInquiry(${i.id})" title="Delete">
                <i class="dc-icon dc-icon-trash dc-icon-sm"></i>
              </button>
            </div>
          </td>
        </tr>`).join('');
    }
    document.getElementById('pageInfo').textContent = `Page ${meta.page} of ${meta.total_pages} (${meta.total} total)`;
    const btns = document.getElementById('pageBtns');
    btns.innerHTML = '';
    for (let p = 1; p <= Math.min(meta.total_pages, 7); p++) {
      const btn = document.createElement('button');
      btn.className = `dc-btn dc-btn-sm ${p === meta.page ? 'dc-btn-primary' : 'dc-btn-ghost'}`;
      if (p === meta.page) btn.style.cssText = 'background:var(--dc-accent);border-color:var(--dc-accent)';
      btn.textContent = p;
      btn.onclick = () => loadInquiries(p);
      btns.appendChild(btn);
    }
  } catch (err) { Toast.error('Failed to load inquiries: ' + err.message); }
}

async function viewInquiry(id) {
  Modal.open('inqModal');
  try {
    const res = await DC.get(`../api/inquiries.php?per_page=100`);
    const inq = res.data.find(i => i.id === id);
    if (!inq) return;
    document.getElementById('inqModalContent').innerHTML = `
      <div class="dc-grid" style="gap:12px;margin-bottom:16px">
        <div class="dc-card-solid" style="padding:16px">
          <div class="dc-label" style="margin-bottom:8px">Contact Info</div>
          <div style="font-weight:600">${escHtml(inq.name)}</div>
          <div class="dc-body">${escHtml(inq.email)}</div>
          <div class="dc-body">${escHtml(inq.phone) || 'No phone provided'}</div>
        </div>
        <div class="dc-card-solid" style="padding:16px">
          <div class="dc-label" style="margin-bottom:8px">Property</div>
          <div style="font-weight:600">${escHtml(inq.property_title)}</div>
          <div class="dc-body">${escHtml(inq.property_address)}</div>
          <a href="../property.php?id=${inq.property_id}" target="_blank" class="dc-caption" style="color:var(--dc-accent-2)">View listing</a>
        </div>
      </div>
      <div class="dc-card-solid" style="padding:16px;margin-bottom:16px">
        <div class="dc-label" style="margin-bottom:8px">Message</div>
        <p style="line-height:1.7;font-size:0.9rem">${escHtml(inq.message)}</p>
      </div>
      <div class="dc-flex-between">
        <span class="dc-caption dc-text-dim">Received ${DCFormat.datetime(inq.created_at)}</span>
        <span class="dc-badge ${statusConfig[inq.status]?.cls}">${statusConfig[inq.status]?.label}</span>
      </div>`;
    document.getElementById('inqModalActions').innerHTML = `
      <button class="dc-btn dc-btn-success dc-btn-sm" onclick="updateStatus(${inq.id},'new')">Mark New</button>
      <button class="dc-btn dc-btn-sm" style="background:rgba(245,166,35,0.12);border-color:var(--dc-warning);color:var(--dc-warning)" onclick="updateStatus(${inq.id},'contacted')">Mark Contacted</button>
      <button class="dc-btn dc-btn-ghost dc-btn-sm" onclick="updateStatus(${inq.id},'closed')">Close</button>
      <a href="mailto:${escHtml(inq.email)}?subject=Re: ${encodeURIComponent(inq.property_title)}"
         class="dc-btn dc-btn-primary dc-btn-sm" style="background:var(--dc-accent);border-color:var(--dc-accent);display:flex;align-items:center;gap:6px">
        <i class="dc-icon dc-icon-mail dc-icon-sm"></i> Reply by Email
      </a>`;
  } catch (err) { Toast.error(err.message); }
}

async function updateStatus(id, status) {
  try {
    await DC.put(`../api/inquiries.php?id=${id}`, { status });
    Toast.success('Status updated');
    Modal.close('inqModal');
    loadInquiries(currentPage);
  } catch (err) { Toast.error(err.message); }
}

async function deleteInquiry(id) {
  if (!confirm('Delete this inquiry? This cannot be undone.')) return;
  try {
    await DC.delete(`../api/inquiries.php?id=${id}`);
    document.querySelector(`tr[data-id="${id}"]`)?.remove();
    Toast.success('Inquiry deleted');
  } catch (err) { Toast.error(err.message); }
}

document.getElementById('searchInput').addEventListener('input', () => loadInquiries(1));
document.getElementById('filterStatus').addEventListener('change', () => loadInquiries(1));

const livePoller = new LivePoller('../api/live.php', (res) => {
  const count = res.data.new_inquiry_count;
  if (count > 0) {
    document.getElementById('liveText').textContent = `${count} new`;
    document.getElementById('liveIndicator').style.color = 'var(--dc-warning)';
  }
}, 4000);
livePoller.start();
loadInquiries();
</script>
</body>
</html>