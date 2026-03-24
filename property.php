<?php
/**
 * Public — Property Detail Page
 */
require_once __DIR__ . '/core/bootstrap.php';

$db = Database::getInstance();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) { header('Location: index.php'); exit; }

$property = $db->fetchOne("SELECT * FROM properties WHERE id = ?", [$id]);
if (!$property) { header('Location: index.php'); exit; }

$isQrScan = isset($_GET['ref']) && $_GET['ref'] === 'qr';

if ($isQrScan) {
    $db->update('properties', ['scan_count' => $property['scan_count'] + 1], 'id = ?', [$id]);
    $property['scan_count']++;
} else {
    $db->update('properties', ['views' => $property['views'] + 1], 'id = ?', [$id]);
    $property['views']++;
}

$gallery = $db->fetchAll(
    "SELECT image_url FROM property_images WHERE property_id = ? ORDER BY sort_order ASC",
    [$id]
);

$related = $db->fetchAll(
    "SELECT id, title, price, image_url, status, bedrooms, bathrooms, city
     FROM properties
     WHERE id != ? AND (type = ? OR city = ?) AND status != 'sold'
     ORDER BY RAND() LIMIT 3",
    [$id, $property['type'], $property['city']]
);

$protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir         = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/property.php'), '/');
$propertyUrl = $protocol . '://' . $host . $dir . '/property.php?id=' . $id . '&ref=qr';
$qrSrc       = QrCode::url($propertyUrl, 160);
$apiBase     = $dir . "/api";

$statusLabel = match($property['status']) {
    'available'   => 'Available',
    'under_offer' => 'Under Offer',
    'sold'        => 'Sold',
    default       => ucfirst($property['status']),
};
$statusClass = match($property['status']) {
    'available'   => 'dc-badge-success',
    'under_offer' => 'dc-badge-warning',
    'sold'        => 'dc-badge-danger',
    default       => 'dc-badge-neutral',
};
$typeLabel = match($property['type']) {
    'house'     => 'House',
    'apartment' => 'Apartment',
    'villa'     => 'Villa',
    'land'      => 'Land',
    default     => ucfirst($property['type']),
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($property['title']) ?> — EstateCore</title>
<meta name="description" content="<?= htmlspecialchars(substr($property['description'], 0, 160)) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../core/ui/devcore.css">
<link rel="stylesheet" href="../../core/ui/parts/_icons.css">
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }

  .hero-img {
    width:100%; height:460px; object-fit:cover;
    border-radius:var(--dc-radius-xl); display:block;
  }
  .hero-img-placeholder {
    width:100%; height:460px; background:var(--dc-bg-3);
    border-radius:var(--dc-radius-xl);
    display:flex; align-items:center; justify-content:center;
  }
  .hero-img-placeholder .dc-icon { width:80px; height:80px; color:var(--dc-text-3); opacity:0.2; }
  .hero-wrap { position:relative; margin-bottom:32px; }

  .sold-overlay {
    position:absolute; inset:0;
    background:rgba(0,0,0,0.45);
    border-radius:var(--dc-radius-xl);
    display:flex; align-items:center; justify-content:center;
    pointer-events:none;
  }
  .sold-overlay-text {
    font-family:var(--dc-font-display);
    font-size:4rem; font-weight:800;
    color:var(--dc-danger); letter-spacing:0.15em;
    text-shadow:0 2px 20px rgba(255,92,106,0.6);
    border:4px solid var(--dc-danger);
    padding:12px 40px; border-radius:var(--dc-radius);
    transform:rotate(-12deg);
  }

  .thumb-strip {
    display:flex; gap:10px; overflow-x:auto;
    padding-bottom:4px; margin-bottom:24px; scrollbar-width:thin;
  }
  .thumb-img {
    width:88px; height:64px; object-fit:cover; flex-shrink:0;
    border-radius:8px; cursor:pointer;
    border:2px solid transparent;
    transition:border-color var(--dc-t-fast), transform var(--dc-t-fast);
  }
  .thumb-img:hover, .thumb-img.active { border-color:var(--dc-accent); transform:scale(1.04); }

  .detail-layout {
    display:grid; grid-template-columns:1fr 380px; gap:32px; align-items:start;
  }
  @media (max-width:900px) { .detail-layout { grid-template-columns:1fr; } }

  .detail-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin:20px 0; }
  .detail-item {
    background:var(--dc-bg-glass); border:1px solid var(--dc-border);
    border-radius:var(--dc-radius); padding:12px 16px;
    display:flex; align-items:center; gap:10px;
  }
  .detail-item .dc-icon { color:var(--dc-accent-2); flex-shrink:0; }
  .detail-item .di-value { font-weight:700; font-size:0.95rem; }
  .detail-item .di-label { font-size:0.75rem; color:var(--dc-text-3); }

  .qr-widget {
    background:var(--dc-bg-2); border:1px solid var(--dc-border);
    border-radius:var(--dc-radius-lg); padding:20px;
    text-align:center; margin-top:16px;
  }

  .qr-banner {
    background:linear-gradient(135deg,rgba(232,168,56,0.15),rgba(232,168,56,0.05));
    border:1px solid rgba(232,168,56,0.35);
    border-radius:var(--dc-radius-lg);
    padding:14px 20px;
    display:flex; align-items:center; gap:12px;
    margin-bottom:24px;
  }
  .qr-banner .dc-icon { color:var(--dc-accent-2); flex-shrink:0; }

  .related-card {
    display:flex; gap:14px; align-items:center;
    background:var(--dc-bg-glass); border:1px solid var(--dc-border);
    border-radius:var(--dc-radius-lg); padding:14px;
    transition:border-color var(--dc-t-fast);
  }
  .related-card:hover { border-color:var(--dc-border-2); }
  .related-card img { width:72px; height:56px; object-fit:cover; border-radius:8px; flex-shrink:0; }
  .related-card-placeholder {
    width:72px; height:56px; background:var(--dc-bg-3);
    border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
  }
  .related-card-placeholder .dc-icon { color:var(--dc-text-3); opacity:0.4; }

  .breadcrumb {
    display:flex; align-items:center; gap:6px;
    font-size:0.85rem; color:var(--dc-text-3);
    margin-bottom:16px; flex-wrap:wrap;
  }
  .breadcrumb a { color:var(--dc-text-3); }
  .breadcrumb a:hover { color:var(--dc-accent-2); }
  .breadcrumb .dc-icon { opacity:0.5; }
</style>
</head>
<body>

<nav class="dc-nav">
  <div class="dc-nav__brand">EstateCore</div>
  <div class="dc-nav__links">
    <a href="index.php" class="dc-nav__link">
      <i class="dc-icon dc-icon-arrow-right dc-icon-sm" style="transform:rotate(180deg)"></i> All Listings
    </a>
    <a href="admin/login.php" class="dc-nav__link">Admin</a>
  </div>
</nav>

<div class="dc-container" style="padding-top:28px;padding-bottom:60px">

  <!-- QR SCAN BANNER -->
  <?php if ($isQrScan): ?>
  <div class="qr-banner">
    <i class="dc-icon dc-icon-qr-code dc-icon-lg"></i>
    <div>
      <div style="font-weight:700;color:var(--dc-accent-2)">You scanned from a signboard</div>
      <p class="dc-caption" style="color:var(--dc-text-2);margin-top:2px">
        Welcome — you landed on this listing via QR code. All details are live and up to date.
      </p>
    </div>
  </div>
  <?php endif; ?>

  <!-- BREADCRUMB -->
  <div class="breadcrumb">
    <a href="index.php">Listings</a>
    <i class="dc-icon dc-icon-arrow-right dc-icon-xs"></i>
    <span><?= htmlspecialchars($property['city']) ?></span>
    <i class="dc-icon dc-icon-arrow-right dc-icon-xs"></i>
    <span><?= $typeLabel ?></span>
    <i class="dc-icon dc-icon-arrow-right dc-icon-xs"></i>
    <span style="color:var(--dc-text-2)"><?= htmlspecialchars($property['title']) ?></span>
  </div>

  <!-- HERO IMAGE -->
  <div class="hero-wrap">
    <?php if ($property['image_url']): ?>
    <img src="<?= htmlspecialchars($property['image_url']) ?>"
         alt="<?= htmlspecialchars($property['title']) ?>"
         class="hero-img" id="heroImg" loading="eager">
    <?php else: ?>
    <div class="hero-img-placeholder" id="heroImg">
      <i class="dc-icon dc-icon-home dc-icon-2xl"></i>
    </div>
    <?php endif; ?>
    <?php if ($property['status'] === 'sold'): ?>
    <div class="sold-overlay"><div class="sold-overlay-text">SOLD</div></div>
    <?php endif; ?>
  </div>

  <!-- THUMBNAIL STRIP -->
  <?php if (!empty($gallery)): ?>
  <div class="thumb-strip">
    <?php if ($property['image_url']): ?>
    <img src="<?= htmlspecialchars($property['image_url']) ?>"
         class="thumb-img active"
         onclick="swapHero(this, '<?= htmlspecialchars($property['image_url']) ?>')"
         alt="Main" loading="lazy">
    <?php endif; ?>
    <?php foreach ($gallery as $img): ?>
    <img src="<?= htmlspecialchars($img['image_url']) ?>"
         class="thumb-img"
         onclick="swapHero(this, '<?= htmlspecialchars($img['image_url']) ?>')"
         alt="Gallery" loading="lazy">
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- TWO-COLUMN LAYOUT -->
  <div class="detail-layout">

    <!-- LEFT: Details -->
    <div>
      <div class="dc-flex-between" style="flex-wrap:wrap;gap:12px;margin-bottom:8px">
        <div>
          <h1 class="dc-h2" style="margin-bottom:4px"><?= htmlspecialchars($property['title']) ?></h1>
          <p style="color:var(--dc-text-3);font-size:0.9rem;display:flex;align-items:center;gap:4px">
            <i class="dc-icon dc-icon-map-pin dc-icon-sm"></i>
            <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?>
          </p>
        </div>
        <div style="text-align:right">
          <div style="font-family:var(--dc-font-display);font-size:2rem;font-weight:800;color:var(--dc-accent-2)">
            $<?= number_format($property['price']) ?>
          </div>
          <span class="dc-badge <?= $statusClass ?>" id="main-status-badge" data-prop-id="<?= $property['id'] ?>"><?= $statusLabel ?></span>
        </div>
      </div>

      <div class="dc-flex" style="gap:8px;margin-bottom:20px;align-items:center">
        <span class="dc-badge dc-badge-neutral"><?= $typeLabel ?></span>
        <span class="dc-caption" style="color:var(--dc-text-3);display:flex;align-items:center;gap:6px">
          <i class="dc-icon dc-icon-eye dc-icon-xs"></i> <?= number_format($property['views']) ?> views
          &nbsp;&middot;&nbsp;
          <i class="dc-icon dc-icon-qr-code dc-icon-xs"></i> <?= $property['scan_count'] ?> QR scans
        </span>
      </div>

      <!-- Detail items -->
      <div class="detail-grid">
        <?php if ($property['bedrooms'] > 0): ?>
        <div class="detail-item">
          <i class="dc-icon dc-icon-bed dc-icon-md"></i>
          <div><div class="di-value"><?= $property['bedrooms'] ?></div><div class="di-label">Bedrooms</div></div>
        </div>
        <?php endif; ?>
        <?php if ($property['bathrooms'] > 0): ?>
        <div class="detail-item">
          <i class="dc-icon dc-icon-bath dc-icon-md"></i>
          <div><div class="di-value"><?= $property['bathrooms'] ?></div><div class="di-label">Bathrooms</div></div>
        </div>
        <?php endif; ?>
        <?php if ($property['area_sqft'] > 0): ?>
        <div class="detail-item">
          <i class="dc-icon dc-icon-ruler dc-icon-md"></i>
          <div><div class="di-value"><?= number_format($property['area_sqft']) ?></div><div class="di-label">Square Feet</div></div>
        </div>
        <?php endif; ?>
        <div class="detail-item">
          <i class="dc-icon dc-icon-building dc-icon-md"></i>
          <div><div class="di-value"><?= $typeLabel ?></div><div class="di-label">Property Type</div></div>
        </div>
      </div>

      <!-- Description -->
      <div class="dc-card" style="padding:20px;margin-bottom:24px">
        <h3 class="dc-h4" style="margin-bottom:12px">About this property</h3>
        <p style="line-height:1.8;color:var(--dc-text-2);font-size:0.9375rem">
          <?= nl2br(htmlspecialchars($property['description'])) ?>
        </p>
      </div>

      <!-- Related listings -->
      <?php if (!empty($related)): ?>
      <div>
        <h3 class="dc-h4" style="margin-bottom:16px">You may also like</h3>
        <div class="dc-flex-col" style="gap:12px">
          <?php foreach ($related as $r): ?>
          <a href="property.php?id=<?= $r['id'] ?>" class="related-card" style="text-decoration:none">
            <?php if ($r['image_url']): ?>
            <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>">
            <?php else: ?>
            <div class="related-card-placeholder">
              <i class="dc-icon dc-icon-home dc-icon-md"></i>
            </div>
            <?php endif; ?>
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:0.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($r['title']) ?></div>
              <div class="dc-caption" style="color:var(--dc-text-3)"><?= htmlspecialchars($r['city']) ?></div>
              <div style="color:var(--dc-accent-2);font-weight:700;font-size:0.9rem">$<?= number_format($r['price']) ?></div>
            </div>
            <span class="dc-badge related-status-<?= $r['id'] ?> <?= $r['status'] === 'available' ? 'dc-badge-success' : ($r['status'] === 'sold' ? 'dc-badge-danger' : 'dc-badge-warning') ?>" style="flex-shrink:0">
              <?= $r['status'] === 'available' ? 'Available' : ($r['status'] === 'sold' ? 'Sold' : 'Under Offer') ?>
            </span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Inquiry Form + QR Widget -->
    <div>
      <div class="dc-card-solid" style="position:sticky;top:140px">
        <h3 class="dc-h3" style="margin-bottom:4px">Request Information</h3>
        <p class="dc-caption" style="color:var(--dc-text-3);margin-bottom:20px">
          Enquire about this property — we will be in touch soon.
        </p>

        <?php if ($property['status'] === 'sold'): ?>
        <div class="dc-card" style="background:rgba(255,92,106,0.08);border-color:rgba(255,92,106,0.3);padding:16px;text-align:center;margin-bottom:16px">
          <i class="dc-icon dc-icon-x dc-icon-lg" style="color:var(--dc-danger);margin-bottom:6px"></i>
          <div style="font-weight:600;color:var(--dc-danger)">This property has been sold</div>
          <p class="dc-caption" style="margin-top:4px;color:var(--dc-text-3)">
            <a href="index.php" style="color:var(--dc-accent-2)">Browse available listings</a>
          </p>
        </div>
        <?php else: ?>

        <form id="inquiryForm">
          <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
          <div class="dc-form-group">
            <label class="dc-label-field">Full Name *</label>
            <input type="text" name="name" class="dc-input" placeholder="Jane Smith" required>
          </div>
          <div class="dc-form-group" style="margin-top:12px">
            <label class="dc-label-field">Email Address *</label>
            <input type="email" name="email" class="dc-input" placeholder="jane@example.com" required>
          </div>
          <div class="dc-form-group" style="margin-top:12px">
            <label class="dc-label-field">Phone Number</label>
            <input type="tel" name="phone" class="dc-input" placeholder="+1 555 000 0000">
          </div>
          <div class="dc-form-group" style="margin-top:12px">
            <label class="dc-label-field">Message *</label>
            <textarea name="message" class="dc-textarea" rows="4"
              placeholder="I am interested in this property. Could we arrange a viewing?"
              required></textarea>
          </div>
          <button type="submit" id="inquirySubmitBtn"
            class="dc-btn dc-btn-primary dc-btn-full"
            style="margin-top:16px;background:var(--dc-accent);border-color:var(--dc-accent);font-size:1rem;padding:12px">
            <i class="dc-icon dc-icon-mail dc-icon-sm" style="margin-right:6px"></i>
            Send Inquiry
          </button>
          <p class="dc-caption" style="text-align:center;margin-top:8px;color:var(--dc-text-3)">
            We respond within 24 hours
          </p>
        </form>
        <?php endif; ?>

        <!-- QR Share Widget -->
        <div class="qr-widget">
          <div class="dc-label" style="margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:6px">
            <i class="dc-icon dc-icon-qr-code dc-icon-sm"></i> Share via QR Code
          </div>
          <div class="dc-qr-card" style="margin:0 auto 10px">
            <img src="<?= htmlspecialchars($qrSrc) ?>"
                 alt="QR Code for this listing"
                 width="160" height="160" loading="lazy">
            <p class="dc-qr-label">Scan to share this listing</p>
          </div>
          <p class="dc-caption" style="color:var(--dc-text-3);font-size:0.72rem;margin-top:4px">
            Scans recorded: <?= number_format($property['scan_count']) ?>
          </p>
          <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-full" style="margin-top:8px" onclick="copyLink()">
            <i class="dc-icon dc-icon-clipboard dc-icon-sm" style="margin-right:4px"></i> Copy Link
          </button>
        </div>

      </div>
    </div>

  </div><!-- /detail-layout -->
</div><!-- /dc-container -->

<footer style="border-top:1px solid var(--dc-border);padding:24px 0;text-align:center">
  <div class="dc-caption" style="color:var(--dc-text-3)">
    EstateCore &middot; Part of the <strong>DevCore Portfolio Suite</strong>
  </div>
</footer>

<script src="../../core/ui/devcore.js"></script>
<script>
function swapHero(thumbEl, url) {
  const hero = document.getElementById('heroImg');
  if (hero && hero.tagName === 'IMG') hero.src = url;
  document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
  thumbEl.classList.add('active');
}

const inquiryForm = document.getElementById('inquiryForm');
if (inquiryForm) {
  inquiryForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn  = document.getElementById('inquirySubmitBtn');
    const data = {
      property_id: <?= $property['id'] ?>,
      name:    inquiryForm.querySelector('[name=name]').value,
      email:   inquiryForm.querySelector('[name=email]').value,
      phone:   inquiryForm.querySelector('[name=phone]').value,
      message: inquiryForm.querySelector('[name=message]').value,
    };
    DCForm.setLoading(btn, true);
    DCForm.clearErrors(inquiryForm);
    try {
      await DC.post(`<?= $apiBase ?>/inquiries.php`, data);
      window.location.href = 'inquiry-success.php?property=' + encodeURIComponent(<?= json_encode($property['title']) ?>);
    } catch (err) {
      Toast.error(err.message || 'Failed to send inquiry. Please try again.');
      DCForm.setLoading(btn, false);
    }
  });
}

function copyLink() {
  const url = window.location.href.split('?')[0] + '?id=<?= $property['id'] ?>';
  navigator.clipboard.writeText(url).then(() => Toast.success('Link copied to clipboard!'));
}

// Live polling — update this property status + related listings
const statusMap = {
  available:   ['dc-badge-success', 'Available'],
  under_offer: ['dc-badge-warning', 'Under Offer'],
  sold:        ['dc-badge-danger',  'Sold'],
};

const livePoller = new LivePoller('<?= $apiBase ?>/live.php', (res) => {
  res.data.property_statuses.forEach(ps => {

    // Update main status badge
    if (ps.id === <?= $property['id'] ?>) {
      const badge = document.getElementById('main-status-badge');
      if (badge) {
        const [cls, label] = statusMap[ps.status] || ['dc-badge-neutral', ps.status];
        badge.className = `dc-badge ${cls}`;
        badge.textContent = label;
      }
      if (ps.status === 'sold') {
        const heroWrap = document.querySelector('.hero-wrap');
        if (heroWrap && !heroWrap.querySelector('.sold-overlay')) {
          const overlay = document.createElement('div');
          overlay.className = 'sold-overlay';
          overlay.innerHTML = '<div class="sold-overlay-text">SOLD</div>';
          heroWrap.appendChild(overlay);
        }
        const form = document.getElementById('inquiryForm');
        if (form) {
          form.innerHTML = `<div class="dc-card" style="background:rgba(255,92,106,0.08);border-color:rgba(255,92,106,0.3);padding:16px;text-align:center">
            <div style="font-weight:600;color:var(--dc-danger);margin-bottom:6px">This property has just been sold</div>
            <p class="dc-caption" style="color:var(--dc-text-3)">
              <a href="index.php" style="color:var(--dc-accent-2)">Browse available listings</a>
            </p>
          </div>`;
        }
      }
    }

    // Update related listing badges
    const relBadge = document.querySelector(`.related-status-${ps.id}`);
    if (relBadge) {
      const [cls, label] = statusMap[ps.status] || ['dc-badge-neutral', ps.status];
      relBadge.className = `dc-badge related-status-${ps.id} ${cls}`;
      relBadge.style.flexShrink = '0';
      relBadge.textContent = label;
      const card = relBadge.closest('.related-card');
      if (card) {
        card.style.opacity      = ps.status === 'sold' ? '0.55' : '';
        card.style.pointerEvents = ps.status === 'sold' ? 'none'  : '';
      }
    }
  });
}, 4000);

livePoller.start();
</script>
</body>
</html>