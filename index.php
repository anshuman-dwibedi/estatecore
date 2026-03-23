<?php
/**
 * Public — Property Listings
 */
require_once '../../core/bootstrap.php';

$db         = Database::getInstance();
$cities     = $db->fetchAll("SELECT DISTINCT city FROM properties ORDER BY city ASC");
$totalCount = (int)($db->fetchOne("SELECT COUNT(*) as c FROM properties WHERE status != 'sold'")['c'] ?? 0);
$apiBase    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/') . '/api';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EstateCore — Premium Property Listings</title>
<meta name="description" content="Browse <?= $totalCount ?> premium properties. Houses, apartments, villas and land. Real-time availability.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../core/ui/devcore.css">
<link rel="stylesheet" href="../../core/ui/parts/_icons.css">
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }

  .filter-bar {
    position: sticky; top: 64px; z-index: 90;
    background: rgba(10,10,15,0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--dc-border);
    padding: 12px 0;
  }
  .filter-inner { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

  .prop-card {
    background: var(--dc-bg-glass);
    border: 1px solid var(--dc-border);
    border-radius: var(--dc-radius-lg);
    overflow: hidden;
    backdrop-filter: blur(12px);
    transition: border-color var(--dc-t-med), box-shadow var(--dc-t-med), transform var(--dc-t-med);
    display: flex; flex-direction: column;
  }
  .prop-card:hover {
    border-color: var(--dc-border-2);
    box-shadow: var(--dc-shadow);
    transform: translateY(-3px);
  }
  .prop-card-img {
    position: relative; height: 200px; overflow: hidden;
    background: var(--dc-bg-3); flex-shrink: 0;
  }
  .prop-card-img img {
    width:100%; height:100%; object-fit:cover;
    transition: transform 0.5s var(--dc-ease);
  }
  .prop-card:hover .prop-card-img img { transform: scale(1.04); }
  .prop-card-body { padding:18px; display:flex; flex-direction:column; flex:1; }
  .prop-price {
    font-family: var(--dc-font-display);
    font-size:1.35rem; font-weight:800;
    color: var(--dc-accent-2);
    letter-spacing:-0.02em; margin-bottom:4px;
  }
  .prop-title {
    font-weight:600; font-size:0.95rem; line-height:1.35; margin-bottom:4px;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }
  .prop-address {
    font-size:0.8rem; color:var(--dc-text-3); margin-bottom:12px;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
  }
  .prop-meta {
    display:flex; align-items:center; gap:12px;
    font-size:0.82rem; color:var(--dc-text-2);
    margin-bottom:12px; flex-wrap:wrap;
  }
  .prop-meta-item { display:flex; align-items:center; gap:5px; }
  .prop-meta-item .dc-icon { color: var(--dc-text-3); }
  .prop-footer {
    display:flex; align-items:center; justify-content:space-between;
    margin-top:auto; padding-top:12px;
    border-top:1px solid var(--dc-border); gap:8px;
  }

  /* SOLD ribbon */
  .sold-ribbon {
    position:absolute; top:16px; left:-28px;
    background: var(--dc-danger);
    color:#fff; font-size:0.72rem; font-weight:800;
    letter-spacing:0.1em; text-transform:uppercase;
    padding:5px 40px;
    transform:rotate(-45deg);
    box-shadow:0 2px 8px rgba(0,0,0,0.4);
    z-index:5; pointer-events:none;
  }
  .under-offer-ribbon {
    position:absolute; top:12px; right:12px;
    background: var(--dc-warning);
    color:#000; font-size:0.68rem; font-weight:700;
    letter-spacing:0.06em; text-transform:uppercase;
    padding:4px 10px; border-radius:var(--dc-radius-full); z-index:5;
  }

  .hero { padding:64px 0 40px; text-align:center; }
  .hero-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:var(--dc-accent-glow); border:1px solid rgba(232,168,56,0.3);
    border-radius:var(--dc-radius-full);
    padding:6px 16px; font-size:0.8rem; font-weight:600;
    color:var(--dc-accent-2); margin-bottom:20px;
  }
  .results-bar {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 0 8px; flex-wrap:wrap; gap:8px;
  }
  .prop-placeholder {
    height:200px; background:var(--dc-bg-3);
    display:flex; align-items:center; justify-content:center;
  }
  .prop-placeholder .dc-icon { width:48px; height:48px; color:var(--dc-text-3); opacity:0.3; }
  .pagination { display:flex; align-items:center; justify-content:center; gap:8px; padding:32px 0; }
</style>
</head>
<body>

<nav class="dc-nav">
  <div class="dc-nav__brand">EstateCore</div>
  <div class="dc-nav__links">
    <a href="index.php" class="dc-nav__link active">Listings</a>
    <a href="admin/login.php" class="dc-nav__link">
      Admin <i class="dc-icon dc-icon-arrow-right dc-icon-sm" style="margin-left:2px"></i>
    </a>
    <div class="dc-live" style="margin-left:8px" id="liveStatusNav">
      <div class="dc-live__dot"></div>
      <span>Live</span>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="dc-container">
    <div class="hero-badge">
      <i class="dc-icon dc-icon-building dc-icon-sm"></i>
      <?= $totalCount ?> Active Listings Available
    </div>
    <h1 class="dc-h1">Find Your <span style="color:var(--dc-accent)">Dream</span> Property</h1>
    <p class="dc-body" style="max-width:520px;margin:12px auto 0">
      Browse premium houses, apartments, villas, and land. Real-time availability — statuses update live.
    </p>
  </div>
</div>

<!-- FILTER BAR -->
<div class="filter-bar">
  <div class="dc-container">
    <div class="filter-inner">
      <select id="fType" class="dc-select" style="width:130px">
        <option value="">All Types</option>
        <option value="house">House</option>
        <option value="apartment">Apartment</option>
        <option value="villa">Villa</option>
        <option value="land">Land</option>
      </select>
      <select id="fCity" class="dc-select" style="width:140px">
        <option value="">All Cities</option>
        <?php foreach ($cities as $c): ?>
        <option value="<?= htmlspecialchars($c['city']) ?>"><?= htmlspecialchars($c['city']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" id="fMinPrice" class="dc-input" style="width:130px" placeholder="Min Price $" min="0" step="50000">
      <input type="number" id="fMaxPrice" class="dc-input" style="width:130px" placeholder="Max Price $" min="0" step="50000">
      <select id="fBeds" class="dc-select" style="width:120px">
        <option value="">Any Beds</option>
        <option value="1">1+ Beds</option>
        <option value="2">2+ Beds</option>
        <option value="3">3+ Beds</option>
        <option value="4">4+ Beds</option>
        <option value="5">5+ Beds</option>
      </select>
      <select id="fStatus" class="dc-select" style="width:150px">
        <option value="">All Statuses</option>
        <option value="available">Available</option>
        <option value="under_offer">Under Offer</option>
        <option value="sold">Sold</option>
      </select>
      <select id="fSort" class="dc-select" style="width:160px">
        <option value="newest">Newest First</option>
        <option value="price_asc">Price: Low to High</option>
        <option value="price_desc">Price: High to Low</option>
        <option value="views">Most Viewed</option>
      </select>
      <button class="dc-btn dc-btn-ghost dc-btn-sm" onclick="clearFilters()">
        <i class="dc-icon dc-icon-x dc-icon-sm"></i> Clear
      </button>
    </div>
  </div>
</div>

<!-- LISTINGS -->
<div class="dc-container" style="padding-top:24px;padding-bottom:48px">

  <div class="results-bar">
    <div>
      <span class="dc-h4" id="resultsCount">Loading...</span>
      <span class="dc-body" id="resultsLabel"> properties</span>
    </div>
    <span id="loadingSpinner" class="dc-caption" style="color:var(--dc-accent-2);display:none">Loading...</span>
  </div>

  <div class="dc-grid dc-grid-3" id="propGrid" style="margin-top:8px">
    <?php for ($i = 0; $i < 6; $i++): ?>
    <div class="dc-card" style="padding:0;overflow:hidden">
      <div class="dc-skeleton" style="height:200px;border-radius:0"></div>
      <div style="padding:18px">
        <div class="dc-skeleton" style="height:20px;margin-bottom:8px;border-radius:4px"></div>
        <div class="dc-skeleton" style="height:16px;width:60%;border-radius:4px"></div>
      </div>
    </div>
    <?php endfor; ?>
  </div>

  <div class="pagination" id="paginationBar"></div>

  <div id="emptyState" style="display:none;text-align:center;padding:64px 0">
    <div class="dc-empty">
      <i class="dc-icon dc-icon-search dc-icon-2xl dc-empty__icon"></i>
      <div class="dc-empty__title" style="margin-top:16px">No properties found</div>
      <p class="dc-empty__text" style="margin-top:8px">Try adjusting your filters</p>
      <button class="dc-btn dc-btn-primary dc-btn-sm"
              style="margin-top:16px;background:var(--dc-accent);border-color:var(--dc-accent)"
              onclick="clearFilters()">Clear All Filters</button>
    </div>
  </div>

</div>

<footer style="border-top:1px solid var(--dc-border);padding:24px 0;text-align:center">
  <div class="dc-caption" style="color:var(--dc-text-3)">
    EstateCore &middot; Part of the <strong>DevCore Portfolio Suite</strong>
  </div>
</footer>

<script src="../../core/ui/devcore.js"></script>
<script src="../../core/utils/helpers.js"></script>
<script>
let currentPage = 1;
let totalPages  = 1;

function statusBadgeHtml(status, propId) {
  const map = {
    available:   ['dc-badge-success', 'Available'],
    under_offer: ['dc-badge-warning', 'Under Offer'],
    sold:        ['dc-badge-danger',  'Sold'],
  };
  const [cls, label] = map[status] || ['dc-badge-neutral', status];
  return `<span class="dc-badge ${cls} prop-status-${propId}">${label}</span>`;
}

function formatPrice(p) {
  return '$' + Number(p).toLocaleString('en-US', { minimumFractionDigits: 0 });
}

function renderCard(p) {
  const isSold       = p.status === 'sold';
  const isUnderOffer = p.status === 'under_offer';
  const ribbon       = isSold
    ? `<div class="sold-ribbon">SOLD</div>`
    : (isUnderOffer ? `<div class="under-offer-ribbon">Under Offer</div>` : '');

  const metaItems = [];
  if (p.bedrooms > 0)  metaItems.push(`<span class="prop-meta-item"><i class="dc-icon dc-icon-bed dc-icon-sm"></i> ${p.bedrooms}</span>`);
  if (p.bathrooms > 0) metaItems.push(`<span class="prop-meta-item"><i class="dc-icon dc-icon-bath dc-icon-sm"></i> ${p.bathrooms}</span>`);
  if (p.area_sqft > 0) metaItems.push(`<span class="prop-meta-item"><i class="dc-icon dc-icon-ruler dc-icon-sm"></i> ${Number(p.area_sqft).toLocaleString()} sqft</span>`);
  if (!metaItems.length) metaItems.push(`<span class="prop-meta-item"><i class="dc-icon dc-icon-map-pin dc-icon-sm"></i> Land</span>`);

  const imgHtml = p.image_url
    ? `<img src="${escHtml(p.image_url)}" alt="${escHtml(p.title)}" loading="lazy">`
    : `<div class="prop-placeholder"><i class="dc-icon dc-icon-home dc-icon-2xl"></i></div>`;

  return `
  <div class="prop-card" id="prop-card-${p.id}">
    <div class="prop-card-img">
      ${ribbon}
      ${imgHtml}
    </div>
    <div class="prop-card-body">
      <div class="prop-price">${formatPrice(p.price)}</div>
      <div class="prop-title">${escHtml(p.title)}</div>
      <div class="prop-address">
        <i class="dc-icon dc-icon-map-pin dc-icon-xs" style="display:inline-block;vertical-align:middle;margin-right:3px"></i>
        ${escHtml(p.address)}, ${escHtml(p.city)}
      </div>
      <div class="prop-meta">${metaItems.join('')}</div>
      <div class="prop-footer">
        ${statusBadgeHtml(p.status, p.id)}
        <a href="property.php?id=${p.id}" class="dc-btn dc-btn-primary dc-btn-sm"
           style="background:var(--dc-accent);border-color:var(--dc-accent)">
          View Details <i class="dc-icon dc-icon-arrow-right dc-icon-sm"></i>
        </a>
      </div>
    </div>
  </div>`;
}

function escHtml(s) {
  if (window.DCHelpers && typeof window.DCHelpers.escHtml === 'function') {
    return window.DCHelpers.escHtml(s);
  }
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function loadListings(page = 1) {
  currentPage = page;
  document.getElementById('loadingSpinner').style.display = 'inline';

  const params = new URLSearchParams({ page, per_page: 9, sort: document.getElementById('fSort').value });
  const type   = document.getElementById('fType').value;
  const city   = document.getElementById('fCity').value;
  const min    = document.getElementById('fMinPrice').value;
  const max    = document.getElementById('fMaxPrice').value;
  const beds   = document.getElementById('fBeds').value;
  const status = document.getElementById('fStatus').value;

  if (type)   params.set('type', type);
  if (city)   params.set('city', city);
  if (min)    params.set('min_price', min);
  if (max)    params.set('max_price', max);
  if (beds)   params.set('bedrooms', beds);
  if (status) params.set('status', status);

  try {
    const res  = await DC.get(`<?= $apiBase ?>/properties.php?${params}`);
    const data = res.data;
    const meta = res.meta;
    totalPages  = meta.total_pages;

    const grid  = document.getElementById('propGrid');
    const empty = document.getElementById('emptyState');

    if (!data.length) {
      grid.innerHTML = '';
      empty.style.display = 'block';
      document.getElementById('resultsCount').textContent = '0';
    } else {
      empty.style.display = 'none';
      grid.innerHTML = data.map(renderCard).join('');
      document.getElementById('resultsCount').textContent = meta.total;
    }

    renderPagination(meta.page, meta.total_pages);
  } catch (err) {
    Toast.error('Failed to load listings: ' + err.message);
  }
  document.getElementById('loadingSpinner').style.display = 'none';
}

function renderPagination(page, total) {
  const bar = document.getElementById('paginationBar');
  if (total <= 1) { bar.innerHTML = ''; return; }
  let html = '';
  if (page > 1) html += `<button class="dc-btn dc-btn-ghost dc-btn-sm" onclick="loadListings(${page - 1})">Prev</button>`;
  for (let p = Math.max(1, page - 2); p <= Math.min(total, page + 2); p++) {
    html += `<button class="dc-btn dc-btn-sm ${p === page ? 'dc-btn-primary' : 'dc-btn-ghost'}"
      style="${p === page ? 'background:var(--dc-accent);border-color:var(--dc-accent)' : ''}"
      onclick="loadListings(${p})">${p}</button>`;
  }
  if (page < total) html += `<button class="dc-btn dc-btn-ghost dc-btn-sm" onclick="loadListings(${page + 1})">Next</button>`;
  bar.innerHTML = html;
}

function clearFilters() {
  ['fType','fCity','fBeds','fStatus'].forEach(id => document.getElementById(id).value = '');
  ['fMinPrice','fMaxPrice'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('fSort').value = 'newest';
  loadListings(1);
}

let filterTimer;
function debouncedLoad() {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => loadListings(1), 350);
}
['fType','fCity','fBeds','fStatus','fSort'].forEach(id =>
  document.getElementById(id).addEventListener('change', debouncedLoad));
['fMinPrice','fMaxPrice'].forEach(id =>
  document.getElementById(id).addEventListener('input', debouncedLoad));

// Live polling — update status badges and SOLD ribbons without page reload
const livePoller = new LivePoller('<?= $apiBase ?>/live.php', (res) => {
  res.data.property_statuses.forEach(ps => {
    const badge = document.querySelector(`.prop-status-${ps.id}`);
    if (badge) {
      const map = {
        available:   ['dc-badge-success', 'Available'],
        under_offer: ['dc-badge-warning', 'Under Offer'],
        sold:        ['dc-badge-danger',  'Sold'],
      };
      const [cls, label] = map[ps.status] || ['dc-badge-neutral', ps.status];
      badge.className = `dc-badge ${cls} prop-status-${ps.id}`;
      badge.textContent = label;

      const card = document.getElementById(`prop-card-${ps.id}`);
      if (card && ps.status === 'sold') {
        const imgWrap = card.querySelector('.prop-card-img');
        if (imgWrap && !imgWrap.querySelector('.sold-ribbon')) {
          imgWrap.querySelector('.under-offer-ribbon')?.remove();
          const ribbon = document.createElement('div');
          ribbon.className = 'sold-ribbon';
          ribbon.textContent = 'SOLD';
          imgWrap.prepend(ribbon);
        }
      }
    }
  });
}, 4000);

loadListings(1);
livePoller.start();
</script>
</body>
</html>