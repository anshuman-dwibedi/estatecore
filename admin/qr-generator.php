<?php
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
Auth::requireRole('admin', 'login.php');

$db         = Database::getInstance();
$properties = $db->fetchAll(
    "SELECT id, title, address, city, status, scan_count, price
     FROM properties ORDER BY status ASC, title ASC"
);

$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/qr-generator.php');
$basePath  = rtrim(dirname($scriptDir), '/');
$baseUrl   = $protocol . '://' . $host . $basePath;

$totalScans = array_sum(array_column($properties, 'scan_count'));
$topScan    = array_reduce($properties, fn($c, $p) => $p['scan_count'] > ($c['scan_count'] ?? 0) ? $p : $c, ['scan_count' => 0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Generator — EstateCore Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../core/ui/devcore.css">
<link rel="stylesheet" href="../../../core/ui/parts/_icons.css">
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }
  .qr-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:20px; }
  .qr-card-wrap {
    background:var(--dc-bg-2); border:1px solid var(--dc-border);
    border-radius:var(--dc-radius-lg); padding:20px; text-align:center;
    transition:border-color var(--dc-t-fast);
  }
  .qr-card-wrap:hover { border-color:var(--dc-border-2); }
  .qr-card-wrap.sold-prop { opacity:0.55; }
  .qr-img-wrap { background:#fff; border-radius:10px; padding:12px; display:inline-block; margin-bottom:12px; }
  .qr-prop-title {
    font-weight:600; font-size:0.85rem; line-height:1.3; margin-bottom:4px;
    overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
  }
  .qr-prop-address { font-size:0.775rem; color:var(--dc-text-3); margin-bottom:8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .scan-count-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:0.72rem; color:var(--dc-text-3);
    background:var(--dc-bg-glass); border:1px solid var(--dc-border);
    border-radius:var(--dc-radius-full); padding:2px 8px;
  }
  .scan-count-badge .dc-icon { color:var(--dc-text-3); }
  @media print {
    body { background:#fff !important; color:#000 !important; }
    .dc-sidebar, .dc-nav, .no-print { display:none !important; }
    .dc-with-sidebar { margin-left:0 !important; }
    .qr-grid { grid-template-columns:repeat(3,1fr) !important; gap:12px !important; }
    .qr-card-wrap { background:#fff !important; border-color:#ddd !important; page-break-inside:avoid; }
    .dc-container { padding:8px !important; }
    h1, .dc-body { display:none; }
  }
</style>
</head>
<body>

<aside class="dc-sidebar no-print">
  <div class="dc-sidebar__logo">EstateCore</div>
  <div class="dc-sidebar__section">Main</div>
  <a href="dashboard.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-bar-chart dc-icon-sm"></i> Dashboard</a>
  <a href="properties.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-home dc-icon-sm"></i> Properties</a>
  <a href="inquiries.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-inbox dc-icon-sm"></i> Inquiries</a>
  <a href="qr-generator.php" class="dc-sidebar__link active"><i class="dc-icon dc-icon-qr-code dc-icon-sm"></i> QR Codes</a>
  <div class="dc-sidebar__section" style="margin-top:auto">Account</div>
  <a href="../index.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-globe dc-icon-sm"></i> View Site</a>
  <a href="logout.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-log-out dc-icon-sm"></i> Logout</a>
</aside>

<div class="dc-with-sidebar">
  <nav class="dc-nav no-print">
    <div class="dc-nav__brand" style="font-size:1rem;font-weight:600">QR Generator</div>
  </nav>

  <div class="dc-container dc-section">

    <div class="dc-flex-between dc-mb-lg no-print">
      <div>
        <h1 class="dc-h2">QR Signboard Generator</h1>
        <p class="dc-body">Print and attach QR codes to physical signboards. Buyers scan to view the listing instantly.</p>
      </div>
      <div class="dc-flex" style="gap:10px">
        <button class="dc-btn dc-btn-ghost dc-flex dc-items-center" onclick="toggleSold()" style="gap:6px">
          <i class="dc-icon dc-icon-eye dc-icon-sm"></i>
          <span id="soldBtnLabel">Hide Sold</span>
        </button>
        <button class="dc-btn dc-btn-primary dc-flex dc-items-center"
          style="background:var(--dc-accent);border-color:var(--dc-accent);gap:6px"
                onclick="printAll()">
          <i class="dc-icon dc-icon-printer dc-icon-sm"></i> Print All QR Codes
        </button>
      </div>
    </div>

    <!-- Info Banner -->
    <div class="dc-card-accent dc-mb-lg no-print" style="padding:16px 20px">
      <div class="dc-flex dc-items-center" style="gap:12px">
        <i class="dc-icon dc-icon-info dc-icon-lg" style="color:var(--dc-accent-2);flex-shrink:0"></i>
        <div>
          <div style="font-weight:600;margin-bottom:2px">How QR Signboards Work</div>
          <p class="dc-caption" style="color:var(--dc-text-2)">
            Each QR code encodes a unique URL with <code style="background:var(--dc-bg-3);padding:1px 6px;border-radius:4px">?ref=qr</code>.
            When a buyer scans, the scan is recorded and they see a signboard banner.
            Base URL: <span style="color:var(--dc-accent-2)"><?= htmlspecialchars($baseUrl) ?>/property.php?id=X&amp;ref=qr</span>
          </p>
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="dc-grid dc-grid-4 dc-mb-lg no-print">
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-qr-code dc-icon-md"></i></div>
        <div class="dc-stat__value"><?= number_format($totalScans) ?></div>
        <div class="dc-stat__label">Total QR Scans</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-home dc-icon-md"></i></div>
        <div class="dc-stat__value"><?= count($properties) ?></div>
        <div class="dc-stat__label">Properties with QR</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-check dc-icon-md"></i></div>
        <div class="dc-stat__value"><?= count(array_filter($properties, fn($p) => $p['status'] === 'available')) ?></div>
        <div class="dc-stat__label">Available Listings</div>
      </div>
      <div class="dc-stat">
        <div class="dc-stat__icon"><i class="dc-icon dc-icon-trophy dc-icon-md"></i></div>
        <div class="dc-stat__value"><?= number_format($topScan['scan_count']) ?></div>
        <div class="dc-stat__label">Most Scanned (single)</div>
      </div>
    </div>

    <!-- QR Grid -->
    <div class="qr-grid" id="qrGrid">
      <?php foreach ($properties as $p):
        $propertyUrl = $baseUrl . '/property.php?id=' . $p['id'] . '&ref=qr';
        $qrSrc       = QrCode::url($propertyUrl, 180);
        $isSold      = $p['status'] === 'sold';
      ?>
      <div class="qr-card-wrap <?= $isSold ? 'sold-prop' : '' ?>" data-status="<?= $p['status'] ?>">
        <div class="qr-img-wrap">
          <img src="<?= htmlspecialchars($qrSrc) ?>"
               alt="QR for <?= htmlspecialchars($p['title']) ?>"
               width="160" height="160" loading="lazy">
        </div>
        <div class="qr-prop-title"><?= htmlspecialchars($p['title']) ?></div>
        <div class="qr-prop-address"><?= htmlspecialchars($p['address']) ?>, <?= htmlspecialchars($p['city']) ?></div>
        <div class="dc-flex" style="justify-content:center;gap:6px;flex-wrap:wrap;margin-bottom:10px">
          <span class="dc-badge <?= $p['status'] === 'available' ? 'dc-badge-success' : ($p['status'] === 'under_offer' ? 'dc-badge-warning' : 'dc-badge-danger') ?>">
            <?= $p['status'] === 'under_offer' ? 'Under Offer' : ucfirst($p['status']) ?>
          </span>
          <?php if ($p['scan_count'] > 0): ?>
          <span class="scan-count-badge">
            <i class="dc-icon dc-icon-qr-code dc-icon-xs"></i> <?= number_format($p['scan_count']) ?> scans
          </span>
          <?php endif; ?>
        </div>
        <a href="<?= htmlspecialchars($propertyUrl) ?>" target="_blank"
           class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-full no-print"
           style="justify-content:center;gap:6px">
          <i class="dc-icon dc-icon-eye dc-icon-sm"></i> Preview
        </a>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<script src="../../../core/ui/devcore.js"></script>
<script>
let showSold = true;
function toggleSold() {
  showSold = !showSold;
  document.getElementById('soldBtnLabel').textContent = showSold ? 'Hide Sold' : 'Show Sold';
  document.querySelectorAll('.qr-card-wrap[data-status="sold"]').forEach(el => {
    el.style.display = showSold ? '' : 'none';
  });
}
function printAll() { window.print(); }
</script>
</body>
</html>