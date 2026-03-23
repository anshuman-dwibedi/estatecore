<?php
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
Auth::requireRole('admin', 'login.php');

$user = Auth::user();
$db   = Database::getInstance();

$properties = $db->fetchAll(
    "SELECT id, title, type, price, bedrooms, bathrooms, area_sqft, address, city,
            image_url, status, views, scan_count, created_at
     FROM properties ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Properties — EstateCore Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../core/ui/devcore.css">
<link rel="stylesheet" href="../../../core/ui/parts/_icons.css">
<style>
  :root { --dc-accent:#e8a838; --dc-accent-2:#f0c060; --dc-accent-glow:rgba(232,168,56,0.2); }
  .prop-img { width:54px; height:42px; object-fit:cover; border-radius:6px; flex-shrink:0; }
  .prop-img-placeholder {
    width:54px; height:42px; background:var(--dc-bg-3); border-radius:6px;
    flex-shrink:0; display:flex; align-items:center; justify-content:center;
  }
  .prop-img-placeholder .dc-icon { color:var(--dc-text-3); opacity:0.4; }
  .upload-zone {
    border:2px dashed var(--dc-border-2); border-radius:var(--dc-radius-lg);
    padding:28px 20px; text-align:center; cursor:pointer; position:relative;
    transition:border-color var(--dc-t-fast), background var(--dc-t-fast);
  }
  .upload-zone:hover, .upload-zone.drag-over { border-color:var(--dc-accent); background:var(--dc-accent-glow); }
  .upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
  .upload-zone .uz-icon  { margin-bottom:6px; }
  .upload-zone .uz-icon .dc-icon { color:var(--dc-text-3); }
  .upload-zone .uz-label { font-size:0.875rem; color:var(--dc-text-2); }
  .upload-zone .uz-hint  { font-size:0.775rem; color:var(--dc-text-3); margin-top:4px; }
  .hero-preview-wrap { position:relative; border-radius:var(--dc-radius-lg); overflow:hidden; background:var(--dc-bg-3); margin-bottom:8px; display:none; }
  .hero-preview-wrap.has-image { display:block; }
  .hero-preview-wrap img { width:100%; height:180px; object-fit:cover; display:block; }
  .hero-preview-remove {
    position:absolute; top:8px; right:8px;
    background:rgba(0,0,0,0.7); border:none; color:#fff;
    border-radius:50%; width:28px; height:28px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background var(--dc-t-fast);
  }
  .hero-preview-remove:hover { background:var(--dc-danger); }
  .hero-preview-remove .dc-icon { color:#fff; }
  .gallery-strip { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
  .gallery-thumb-wrap { position:relative; width:80px; height:64px; border-radius:8px; overflow:hidden; background:var(--dc-bg-3); flex-shrink:0; }
  .gallery-thumb-wrap img { width:100%; height:100%; object-fit:cover; }
  .gallery-thumb-remove {
    position:absolute; top:3px; right:3px;
    background:rgba(0,0,0,0.75); border:none; color:#fff;
    border-radius:50%; width:20px; height:20px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background var(--dc-t-fast);
  }
  .gallery-thumb-remove:hover { background:var(--dc-danger); }
  .upload-progress { height:4px; border-radius:2px; background:var(--dc-border); overflow:hidden; margin-top:8px; display:none; }
  .upload-progress.active { display:block; }
  .upload-progress-fill { height:100%; background:var(--dc-accent); border-radius:2px; width:0%; transition:width 0.25s ease; }
  .storage-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--dc-accent-glow); border:1px solid rgba(232,168,56,0.3);
    border-radius:var(--dc-radius-full); padding:4px 14px;
    font-size:0.75rem; font-weight:600; color:var(--dc-accent-2);
  }
  .storage-badge .dc-icon { color:var(--dc-accent-2); }
</style>
</head>
<body>

<aside class="dc-sidebar">
  <div class="dc-sidebar__logo">EstateCore</div>
  <div class="dc-sidebar__section">Main</div>
  <a href="dashboard.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-bar-chart dc-icon-sm"></i> Dashboard</a>
  <a href="properties.php"   class="dc-sidebar__link active"><i class="dc-icon dc-icon-home dc-icon-sm"></i> Properties</a>
  <a href="inquiries.php"    class="dc-sidebar__link"><i class="dc-icon dc-icon-inbox dc-icon-sm"></i> Inquiries</a>
  <a href="qr-generator.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-qr-code dc-icon-sm"></i> QR Codes</a>
  <div class="dc-sidebar__section" style="margin-top:auto">Account</div>
  <a href="../index.php" class="dc-sidebar__link"><i class="dc-icon dc-icon-globe dc-icon-sm"></i> View Site</a>
  <a href="logout.php"   class="dc-sidebar__link"><i class="dc-icon dc-icon-log-out dc-icon-sm"></i> Logout</a>
</aside>

<div class="dc-with-sidebar">
  <nav class="dc-nav">
    <div class="dc-nav__brand" style="font-size:1rem;font-weight:600">Properties</div>
    <div class="dc-flex dc-items-center" style="gap:16px">
      <div class="dc-live" id="liveIndicator"><div class="dc-live__dot"></div><span id="liveText">Live</span></div>
            <button class="dc-btn dc-btn-primary dc-btn-sm dc-flex dc-items-center"
              onclick="openAddModal()"
              style="background:var(--dc-accent);border-color:var(--dc-accent);gap:6px">
        <i class="dc-icon dc-icon-plus dc-icon-sm"></i> Add Property
      </button>
    </div>
  </nav>

  <div class="dc-container dc-section">

    <div class="dc-flex-between dc-mb-lg">
      <div>
        <h1 class="dc-h2">Property Management</h1>
        <p class="dc-body"><?= count($properties) ?> listings &middot; images via DevCore Storage</p>
      </div>
      <div class="dc-flex" style="gap:8px">
        <input  type="text" id="searchInput"  class="dc-input"  style="width:220px" placeholder="Search listings...">
        <select              id="filterStatus" class="dc-select" style="width:160px">
          <option value="">All Statuses</option>
          <option value="available">Available</option>
          <option value="under_offer">Under Offer</option>
          <option value="sold">Sold</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <span class="storage-badge">
        <i class="dc-icon dc-icon-cloud dc-icon-sm"></i>
        Storage active &mdash; images stored via Storage::uploadFile()
      </span>
    </div>

    <div class="dc-table-wrap">
      <table class="dc-table">
        <thead>
          <tr>
            <th>Property</th><th>Type</th><th>Price</th>
            <th>Beds / Bath</th><th>Status</th><th>Views</th><th>Scans</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="propTableBody">
          <?php foreach ($properties as $p): ?>
          <tr data-id="<?= $p['id'] ?>"
              data-status="<?= $p['status'] ?>"
              data-title="<?= htmlspecialchars(strtolower($p['title'])) ?>">
            <td>
              <div class="dc-flex dc-items-center" style="gap:10px">
                <?php if ($p['image_url']): ?>
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" class="prop-img" alt="" loading="lazy">
                <?php else: ?>
                  <div class="prop-img-placeholder"><i class="dc-icon dc-icon-home dc-icon-sm"></i></div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;font-size:0.9rem"><?= htmlspecialchars($p['title']) ?></div>
                  <div class="dc-caption dc-text-dim"><?= htmlspecialchars($p['city']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="dc-badge dc-badge-neutral"><?= ucfirst($p['type']) ?></span></td>
            <td style="font-weight:700;color:var(--dc-accent-2)">$<?= number_format($p['price']) ?></td>
            <td class="dc-caption">
              <span class="dc-flex dc-items-center" style="gap:4px">
                <i class="dc-icon dc-icon-bed dc-icon-xs"></i> <?= $p['bedrooms'] ?>
                &nbsp;/&nbsp;
                <i class="dc-icon dc-icon-bath dc-icon-xs"></i> <?= $p['bathrooms'] ?>
              </span>
            </td>
            <td>
              <span class="dc-badge status-badge-<?= $p['id'] ?> <?= $p['status'] === 'available' ? 'dc-badge-success' : ($p['status'] === 'under_offer' ? 'dc-badge-warning' : 'dc-badge-danger') ?>">
                <?= $p['status'] === 'under_offer' ? 'Under Offer' : ucfirst($p['status']) ?>
              </span>
            </td>
            <td><?= number_format($p['views']) ?></td>
            <td><?= number_format($p['scan_count']) ?></td>
            <td>
              <div class="dc-flex" style="gap:5px">
                <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" onclick="openEditModal(<?= $p['id'] ?>)" title="Edit">
                  <i class="dc-icon dc-icon-edit dc-icon-sm"></i>
                </button>
                <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon"
                        onclick="openGalleryModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>')" title="Gallery">
                  <i class="dc-icon dc-icon-image dc-icon-sm"></i>
                </button>
                <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" onclick="openStatusModal(<?= $p['id'] ?>)" title="Change Status">
                  <i class="dc-icon dc-icon-refresh dc-icon-sm"></i>
                </button>
                <a href="../property.php?id=<?= $p['id'] ?>" target="_blank" class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" title="View">
                  <i class="dc-icon dc-icon-eye dc-icon-sm"></i>
                </a>
                <button class="dc-btn dc-btn-danger dc-btn-sm dc-btn-icon"
                        onclick="deleteProperty(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>')" title="Delete">
                  <i class="dc-icon dc-icon-trash dc-icon-sm"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ADD / EDIT MODAL -->
<div class="dc-modal-overlay" id="propertyModal">
  <div class="dc-modal" style="max-width:680px">
      <div class="dc-flex-between" style="margin-bottom:24px">
      <h2 class="dc-h3" id="modalTitle">Add Property</h2>
      <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" data-modal-close="propertyModal">
        <i class="dc-icon dc-icon-x dc-icon-sm"></i>
      </button>
    </div>
    <form id="propertyForm" enctype="multipart/form-data">
      <input type="hidden" id="editId" value="">
      <div class="dc-grid dc-grid-2" style="gap:16px">
        <div class="dc-form-group" style="grid-column:1/-1">
          <label class="dc-label-field">Title *</label>
          <input type="text" name="title" id="fTitle" class="dc-input" placeholder="Beautiful 3BR Family Home..." required>
        </div>
        <div class="dc-form-group" style="grid-column:1/-1">
          <label class="dc-label-field">Description *</label>
          <textarea name="description" id="fDescription" class="dc-textarea" rows="4" placeholder="Describe the property..." required></textarea>
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Type *</label>
          <select name="type" id="fType" class="dc-select" required>
            <option value="house">House</option>
            <option value="apartment">Apartment</option>
            <option value="villa">Villa</option>
            <option value="land">Land</option>
          </select>
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Price ($) *</label>
          <input type="number" name="price" id="fPrice" class="dc-input" placeholder="485000" min="0" step="1000" required>
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Bedrooms</label>
          <input type="number" name="bedrooms" id="fBedrooms" class="dc-input" value="0" min="0" max="20">
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Bathrooms</label>
          <input type="number" name="bathrooms" id="fBathrooms" class="dc-input" value="0" min="0" max="20">
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Area (sq ft)</label>
          <input type="number" name="area_sqft" id="fAreaSqft" class="dc-input" value="0" min="0">
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">Status *</label>
          <select name="status" id="fStatus" class="dc-select" required>
            <option value="available">Available</option>
            <option value="under_offer">Under Offer</option>
            <option value="sold">Sold</option>
          </select>
        </div>
        <div class="dc-form-group" style="grid-column:1/-1">
          <label class="dc-label-field">Address *</label>
          <input type="text" name="address" id="fAddress" class="dc-input" placeholder="123 Main Street" required>
        </div>
        <div class="dc-form-group">
          <label class="dc-label-field">City *</label>
          <input type="text" name="city" id="fCity" class="dc-input" placeholder="Austin" required>
        </div>
        <div class="dc-form-group" style="grid-column:1/-1">
          <label class="dc-label-field">Hero Image</label>
          <div id="heroPreviewWrap" class="hero-preview-wrap">
            <img id="heroPreviewImg" src="" alt="Hero preview">
            <button type="button" class="hero-preview-remove" onclick="clearHeroImage()">
              <i class="dc-icon dc-icon-x dc-icon-xs"></i>
            </button>
          </div>
          <div id="heroDropZone" class="upload-zone">
            <input type="file" name="image" id="heroFileInput" accept="image/jpeg,image/png,image/webp" onchange="handleHeroSelect(this)">
            <div class="uz-icon"><i class="dc-icon dc-icon-upload dc-icon-lg"></i></div>
            <div class="uz-label">Drop hero image or <strong style="color:var(--dc-accent-2)">click to browse</strong></div>
            <div class="uz-hint">JPEG &middot; PNG &middot; WebP &middot; max 5 MB</div>
          </div>
          <div style="margin-top:10px">
            <input type="url" name="image_url" id="fImageUrl" class="dc-input"
                   placeholder="Or paste an external image URL" style="font-size:0.82rem">
          </div>
          <div class="upload-progress" id="heroProgress">
            <div class="upload-progress-fill" id="heroProgressFill"></div>
          </div>
        </div>
      </div>
      <div class="dc-flex" style="gap:12px;margin-top:24px;justify-content:flex-end">
        <button type="button" class="dc-btn dc-btn-ghost" data-modal-close="propertyModal">Cancel</button>
        <button type="submit" id="saveBtn" class="dc-btn dc-btn-primary"
                style="background:var(--dc-accent);border-color:var(--dc-accent)">Save Property</button>
      </div>
    </form>
  </div>
</div>

<!-- GALLERY MODAL -->
<div class="dc-modal-overlay" id="galleryModal">
  <div class="dc-modal" style="max-width:580px">
    <div class="dc-flex-between" style="margin-bottom:20px">
      <div>
        <h2 class="dc-h3">Gallery Images</h2>
        <p class="dc-caption dc-text-dim" id="gallerySubtitle" style="margin-top:2px"></p>
      </div>
      <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" data-modal-close="galleryModal">
        <i class="dc-icon dc-icon-x dc-icon-sm"></i>
      </button>
    </div>
    <input type="hidden" id="galleryPropId" value="">
    <div class="gallery-strip" id="galleryStrip">
      <div class="dc-skeleton" style="width:80px;height:64px;border-radius:8px"></div>
    </div>
    <div style="margin-top:18px">
      <label class="dc-label-field" style="margin-bottom:8px;display:block">Add Image to Gallery</label>
      <div class="upload-zone" style="padding:20px">
        <input type="file" id="galleryFileInput" accept="image/jpeg,image/png,image/webp" onchange="uploadGalleryImage(this)">
        <div class="uz-icon"><i class="dc-icon dc-icon-image dc-icon-lg"></i></div>
        <div class="uz-label">Drop or <strong style="color:var(--dc-accent-2)">click to browse</strong></div>
        <div class="uz-hint">JPEG &middot; PNG &middot; WebP &middot; max 5 MB</div>
      </div>
      <div class="upload-progress" id="galleryProgress">
        <div class="upload-progress-fill" id="galleryProgressFill"></div>
      </div>
    </div>
  </div>
</div>

<!-- STATUS MODAL -->
<div class="dc-modal-overlay" id="statusModal">
  <div class="dc-modal" style="max-width:360px">
    <div class="dc-flex-between" style="margin-bottom:20px">
      <h2 class="dc-h3">Change Status</h2>
      <button class="dc-btn dc-btn-ghost dc-btn-sm dc-btn-icon" data-modal-close="statusModal">
        <i class="dc-icon dc-icon-x dc-icon-sm"></i>
      </button>
    </div>
    <input type="hidden" id="statusPropId" value="">
    <div class="dc-flex-col" style="gap:10px">
      <button class="dc-btn dc-btn-success dc-btn-full" onclick="changeStatus('available')">Mark Available</button>
      <button class="dc-btn dc-btn-full"
              style="background:rgba(245,166,35,0.12);border-color:var(--dc-warning);color:var(--dc-warning)"
              onclick="changeStatus('under_offer')">Mark Under Offer</button>
      <button class="dc-btn dc-btn-danger dc-btn-full" onclick="changeStatus('sold')">Mark Sold</button>
    </div>
    <button class="dc-btn dc-btn-ghost dc-btn-full" style="margin-top:12px" data-modal-close="statusModal">Cancel</button>
  </div>
</div>

<script src="../../../core/ui/devcore.js"></script>
<script src="../../../core/utils/helpers.js"></script>
<script>
const searchInput  = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');
function filterTable() {
  const q = searchInput.value.toLowerCase(), s = filterStatus.value;
  document.querySelectorAll('#propTableBody tr').forEach(row => {
    row.style.display = (row.dataset.title.includes(q) && (!s || row.dataset.status === s)) ? '' : 'none';
  });
}
searchInput.addEventListener('input', filterTable);
filterStatus.addEventListener('change', filterTable);

const heroDropZone    = document.getElementById('heroDropZone');
const heroPreviewWrap = document.getElementById('heroPreviewWrap');
const heroPreviewImg  = document.getElementById('heroPreviewImg');

heroDropZone.addEventListener('dragover',  e => { e.preventDefault(); heroDropZone.classList.add('drag-over'); });
heroDropZone.addEventListener('dragleave', () => heroDropZone.classList.remove('drag-over'));
heroDropZone.addEventListener('drop', e => {
  e.preventDefault(); heroDropZone.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file) previewHero(file);
});
function handleHeroSelect(input) { if (input.files && input.files[0]) previewHero(input.files[0]); }
function previewHero(file) {
  const reader = new FileReader();
  reader.onload = ev => {
    heroPreviewImg.src = ev.target.result;
    heroPreviewWrap.classList.add('has-image');
    heroDropZone.style.display = 'none';
    document.getElementById('fImageUrl').value = '';
  };
  reader.readAsDataURL(file);
}
function clearHeroImage() {
  heroPreviewImg.src = '';
  heroPreviewWrap.classList.remove('has-image');
  heroDropZone.style.display = '';
  document.getElementById('heroFileInput').value = '';
}
document.getElementById('fImageUrl').addEventListener('input', function() {
  if (this.value.startsWith('http')) {
    heroPreviewImg.src = this.value;
    heroPreviewWrap.classList.add('has-image');
    heroDropZone.style.display = 'none';
    document.getElementById('heroFileInput').value = '';
  } else if (!this.value) { clearHeroImage(); }
});

function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Add Property';
  document.getElementById('editId').value = '';
  document.getElementById('propertyForm').reset();
  clearHeroImage();
  Modal.open('propertyModal');
}
async function openEditModal(id) {
  document.getElementById('modalTitle').textContent = 'Edit Property';
  try {
    const res = await DC.get(`../api/properties.php?id=${id}`);
    const p   = res.data;
    document.getElementById('editId').value       = p.id;
    document.getElementById('fTitle').value       = p.title;
    document.getElementById('fDescription').value = p.description;
    document.getElementById('fType').value        = p.type;
    document.getElementById('fPrice').value       = p.price;
    document.getElementById('fBedrooms').value    = p.bedrooms;
    document.getElementById('fBathrooms').value   = p.bathrooms;
    document.getElementById('fAreaSqft').value    = p.area_sqft;
    document.getElementById('fAddress').value     = p.address;
    document.getElementById('fCity').value        = p.city;
    document.getElementById('fStatus').value      = p.status;
    document.getElementById('fImageUrl').value    = p.image_url || '';
    if (p.image_url) {
      heroPreviewImg.src = p.image_url;
      heroPreviewWrap.classList.add('has-image');
      heroDropZone.style.display = 'none';
    } else { clearHeroImage(); }
    Modal.open('propertyModal');
  } catch (err) { Toast.error('Failed to load property: ' + err.message); }
}

document.getElementById('propertyForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn    = document.getElementById('saveBtn');
  const editId = document.getElementById('editId').value;
  const fd     = new FormData(e.target);
  const fi     = document.getElementById('heroFileInput');
  if (!fi.files || !fi.files.length) fd.delete('image');
  DCForm.setLoading(btn, true);
  simulateProgress('heroProgress', 'heroProgressFill');
  try {
    const url  = editId ? `../api/properties.php?id=${editId}` : '../api/properties.php';
    const res  = await fetch(url, { method: editId ? 'PUT' : 'POST', body: fd });
    const data = await res.json();
    if (!res.ok || data.status === 'error') throw new Error(data.message || 'Save failed');
    stopProgress('heroProgress', 'heroProgressFill');
    Toast.success(editId ? 'Property updated!' : 'Property created!');
    Modal.close('propertyModal');
    setTimeout(() => location.reload(), 700);
  } catch (err) {
    stopProgress('heroProgress', 'heroProgressFill');
    Toast.error(err.message);
    DCForm.setLoading(btn, false);
  }
});

function openStatusModal(id) { document.getElementById('statusPropId').value = id; Modal.open('statusModal'); }
async function changeStatus(status) {
  const id = document.getElementById('statusPropId').value;
  try {
    const propRes = await DC.get(`../api/properties.php?id=${id}`);
    const full = propRes.data; full.status = status;
    const fd = new FormData();
    ['title','description','type','price','bedrooms','bathrooms','area_sqft','address','city','status','image_url']
      .forEach(k => fd.append(k, full[k] ?? ''));
    const res  = await fetch(`../api/properties.php?id=${id}`, { method:'PUT', body:fd });
    const data = await res.json();
    if (!res.ok || data.status === 'error') throw new Error(data.message);
    Modal.close('statusModal');
    Toast.success('Status updated');
    setTimeout(() => location.reload(), 600);
  } catch (err) { Toast.error(err.message); }
}

async function deleteProperty(id, title) {
  if (!confirm(`Delete "${title}"?\n\nAll stored images will also be deleted.`)) return;
  try {
    await DC.delete(`../api/properties.php?id=${id}`);
    document.querySelector(`tr[data-id="${id}"]`)?.remove();
    Toast.success('Property deleted');
  } catch (err) { Toast.error(err.message); }
}

async function openGalleryModal(propId, propTitle) {
  document.getElementById('galleryPropId').value         = propId;
  document.getElementById('gallerySubtitle').textContent = propTitle;
  document.getElementById('galleryFileInput').value      = '';
  Modal.open('galleryModal');
  await loadGallery(propId);
}
async function loadGallery(propId) {
  const strip = document.getElementById('galleryStrip');
  strip.innerHTML = '<span class="dc-caption dc-text-dim">Loading...</span>';
  try {
    const res    = await DC.get(`../api/properties.php?id=${propId}`);
    const images = res.data.gallery || [];
    renderGallery(images);
  } catch (err) { strip.innerHTML = `<span class="dc-caption" style="color:var(--dc-danger)">${err.message}</span>`; }
}
function renderGallery(images) {
  const strip = document.getElementById('galleryStrip');
  strip.innerHTML = '';
  if (!images.length) {
    strip.innerHTML = '<span class="dc-caption dc-text-dim">No gallery images yet.</span>';
    return;
  }
  images.forEach(img => {
    const wrap = document.createElement('div');
    wrap.className = 'gallery-thumb-wrap';
    wrap.innerHTML = `<img src="${escHtml(img.image_url)}" alt="" loading="lazy">
      <button class="gallery-thumb-remove" onclick="deleteGalleryImage(${img.id})">
        <i class="dc-icon dc-icon-x" style="width:10px;height:10px"></i>
      </button>`;
    strip.appendChild(wrap);
  });
}
async function uploadGalleryImage(input) {
  if (!input.files || !input.files[0]) return;
  const propId = document.getElementById('galleryPropId').value;
  const fd = new FormData(); fd.append('image', input.files[0]);
  simulateProgress('galleryProgress', 'galleryProgressFill');
  try {
    const res  = await fetch(`../api/images.php?property_id=${propId}`, { method:'POST', body:fd });
    const data = await res.json();
    if (!res.ok || data.status === 'error') throw new Error(data.message || 'Upload failed');
    input.value = '';
    stopProgress('galleryProgress', 'galleryProgressFill');
    Toast.success('Gallery image uploaded');
    await loadGallery(propId);
  } catch (err) { stopProgress('galleryProgress', 'galleryProgressFill'); Toast.error('Upload failed: ' + err.message); }
}
async function deleteGalleryImage(imageId) {
  if (!confirm('Remove this image from storage?')) return;
  const propId = document.getElementById('galleryPropId').value;
  try {
    await DC.delete(`../api/images.php?id=${imageId}`);
    Toast.success('Image deleted');
    await loadGallery(propId);
  } catch (err) { Toast.error(err.message); }
}

const progressTimers = {};
function simulateProgress(barId, fillId) {
  const bar = document.getElementById(barId), fill = document.getElementById(fillId);
  if (!bar || !fill) return;
  bar.classList.add('active'); fill.style.width = '0%';
  let pct = 0;
  progressTimers[barId] = setInterval(() => { pct = Math.min(pct + Math.random() * 14, 85); fill.style.width = pct + '%'; }, 180);
}
function stopProgress(barId, fillId) {
  clearInterval(progressTimers[barId]);
  const fill = document.getElementById(fillId);
  if (fill) fill.style.width = '100%';
  setTimeout(() => document.getElementById(barId)?.classList.remove('active'), 500);
}

const livePoller = new LivePoller('../api/live.php', res => {
  const count = res.data.new_inquiry_count;
  if (count > 0) {
    document.getElementById('liveText').textContent = `${count} new inquiry${count !== 1 ? 's':''}`;
    document.getElementById('liveIndicator').style.color = 'var(--dc-warning)';
  }
  res.data.property_statuses.forEach(ps => {
    const badge = document.querySelector(`.status-badge-${ps.id}`);
    if (!badge) return;
    const map = { available:['dc-badge-success','Available'], under_offer:['dc-badge-warning','Under Offer'], sold:['dc-badge-danger','Sold'] };
    const [cls, label] = map[ps.status] || ['dc-badge-neutral', ps.status];
    badge.className = `dc-badge status-badge-${ps.id} ${cls}`;
    badge.textContent = label;
    const row = document.querySelector(`tr[data-id="${ps.id}"]`);
    if (row) row.dataset.status = ps.status;
  });
}, 4000);
livePoller.start();

function escHtml(s) {
  if (window.DCHelpers && typeof window.DCHelpers.escHtml === 'function') {
    return window.DCHelpers.escHtml(s);
  }
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>