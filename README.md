# 🏡 EstateCore — Real Estate Property Listing Platform

> **A production-grade real estate platform** built on the DevCore shared library.
> Dark card grid aesthetic, live property availability, QR signboard system, and a full analytics dashboard.
> Part of the **DevCore Portfolio Suite** — 4 industry projects, 1 shared core.

---

## ✨ Features

### Public-Facing
- 🏠 **Property Listings** — Filterable grid of houses, apartments, villas & land
- 🔍 **Dynamic Search** — Filter by type, city, price range, bedrooms, status — no page reload
- 📄 **Property Detail Pages** — Hero image, gallery thumbnails, full description, two-column layout
- 📬 **Inquiry Form** — Visitors submit inquiries directly from any listing page
- ✅ **Confirmation Page** — Friendly thank-you page with next-steps guide
- 🖼️ **Storage-backed Image Uploads** — Hero images and gallery photos upload via `Storage::uploadFile()`. Swap between local disk, AWS S3, or Cloudflare R2 by changing one config line — zero code changes required
- 🗑️ **Automatic Image Cleanup** — Deleting a property also deletes its stored images from whatever provider is active
- 🖼️ **Gallery Management** — Upload, preview, and remove multiple gallery images per property from the admin panel
- 📤 **Drag-and-drop Upload UI** — Hero image field supports drag-and-drop with live preview and XHR progress bar
- 🔴 **SOLD Overlay** — Sold properties display a diagonal ribbon and overlay on their card image

### Real-Time
- ⚡ **Live Availability Updates** — Property status badges refresh every 4 seconds via `LivePoller`
- 🆕 **New Inquiry Notifications** — Admin sidebar shows live count of new inquiries
- 🔄 **Status Propagation** — When admin marks a property Sold, the SOLD ribbon appears on the public listing instantly

### Admin Portal
- 📊 **Analytics Dashboard** — KPI stat cards, line/bar/doughnut charts, live inquiry feed
- 🏠 **Property Management** — Add, edit, delete, toggle availability for all listings
- 📬 **Inquiry Management** — View, filter, update status, and reply to all inquiries
- 📱 **QR Code Generator** — Print-optimized grid of QR codes for all properties
- 🔐 **Secure Auth** — Session-based login with bcrypt password hashing

### Analytics
- 👁️ **View Tracking** — Every property page visit increments the views counter
- 📱 **QR Scan Tracking** — `?ref=qr` in the URL increments the `scan_count` column
- 📈 **Inquiry Trends** — Chart of inquiries per day for the last 30 days
- 🏆 **Top Properties** — Bar chart of the 10 most-viewed listings
- 🍩 **Type Distribution** — Doughnut chart: Houses / Apartments / Villas / Land

---

## ☁️ Storage System — Image Uploads

EstateCore uses the **DevCore Storage** abstraction for all property image uploads. Switch between local disk, AWS S3, and Cloudflare R2 by changing **one line** in `config.php`.

### How it works

| Feature | Implementation |
|---------|---------------|
| **Hero image upload** | Admin drag-and-drops or selects a file → `POST /api/properties.php` → `Storage::uploadFile()` → URL saved to `properties.image_url` |
| **Gallery image upload** | Separate 🖼️ modal per property → `POST /api/images.php?property_id=X` → `Storage::uploadFile()` → URL saved to `property_images` |
| **Image deletion** | On property delete or gallery image remove → `Storage::delete()` removes the file from whichever driver is active |
| **Seed / external URLs** | Unsplash seed images are never passed to `Storage::delete()` — detected via `isStoredUrl()` helper |

### Configure your storage driver

Open `devcore/config.php` and set `storage.driver`:

```php
'storage' => [
    'driver' => 'local',   // ← swap to 's3' or 'r2' to go cloud-native

    'local' => [
        'root'     => __DIR__ . '/uploads',
        'base_url' => 'http://localhost/uploads',
    ],

    's3' => [
        'key'    => 'YOUR_AWS_KEY',
        'secret' => 'YOUR_AWS_SECRET',
        'bucket' => 'my-estate-bucket',
        'region' => 'us-east-1',
    ],

    'r2' => [
        'account_id' => 'YOUR_CF_ACCOUNT_ID',
        'key'        => 'YOUR_R2_KEY',
        'secret'     => 'YOUR_R2_SECRET',
        'bucket'     => 'my-estate-bucket',
        'base_url'   => 'https://pub-xxxx.r2.dev',
    ],
],
```

No code changes required — the `Storage` facade picks the correct driver automatically.

### Supported drivers

| Driver | Class | Best for |
|--------|-------|---------|
| `local` | `LocalStorage` | Development, self-hosted |
| `s3` | `S3Storage` | AWS deployments (no SDK needed — raw HTTP + HMAC-SHA1) |
| `r2` | `R2Storage` | Cloudflare deployments (AWS Signature V4 compatible) |

---

## 🛠 Tech Stack

![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![DevCore](https://img.shields.io/badge/DevCore-Shared_Library-e8a838?style=flat)
![Storage](https://img.shields.io/badge/Storage-Local%20%7C%20S3%20%7C%20R2-6c63ff?style=flat)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=flat&logo=chartdotjs&logoColor=white)
![Vanilla JS](https://img.shields.io/badge/JavaScript-Vanilla_ES2022-F7DF1E?style=flat&logo=javascript&logoColor=black)
![QR Server](https://img.shields.io/badge/QR_API-goqr.me-22d3a0?style=flat)
![Storage](https://img.shields.io/badge/Storage-Local_|_S3_|_R2-38bdf8?style=flat)

---

## 🚀 Setup Instructions

### 1. Clone DevCore Shared Library
```bash
git clone https://github.com/your-org/devcore.git
# Result: devcore/core/bootstrap.php, devcore/core/ui/, etc.
```

### 2. Clone This Project
```bash
git clone https://github.com/your-org/estatecore.git
# Place so that: devcore/ and estatecore/ are siblings
```

Expected folder structure:
```
your-project/
├── devcore/
│   ├── core/
│   │   ├── bootstrap.php
│   │   ├── backend/
│   │   └── ui/
│   └── config.example.php
└── estatecore/
    ├── index.php
    ├── property.php
    ├── admin/
    └── api/
```

### 3. Configure Database
```bash
cp devcore/config.example.php devcore/config.php
# Edit config.php with your DB credentials:
```

```php
return [
    'db_host'    => 'localhost',
    'db_name'    => 'real_estate',
    'db_user'    => 'root',
    'db_pass'    => 'your_password',
    'debug'      => true,
    'api_secret' => 'your-secret-key',
];
```

### 4. Import Database Schema
```bash
mysql -u root -p -e "CREATE DATABASE real_estate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p real_estate < estatecore/database.sql
```

### 5. Configure Storage (for image uploads)

The platform uses the DevCore `Storage` facade — swap providers by changing **one line** in `config.php`.

#### Option A — Local filesystem (default, works immediately)
```php
'storage' => [
    'driver' => 'local',
    'local'  => [
        'root'     => __DIR__ . '/uploads',           // absolute path on server
        'base_url' => 'http://localhost/uploads',     // public URL prefix
    ],
],
```
Create the uploads directory and make it writable:
```bash
mkdir -p uploads && chmod 755 uploads
```

#### Option B — AWS S3
```php
'storage' => [
    'driver' => 's3',
    's3' => [
        'key'      => 'AKIAIOSFODNN7EXAMPLE',
        'secret'   => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        'bucket'   => 'my-estate-bucket',
        'region'   => 'us-east-1',
        'base_url' => '',          // optional CloudFront URL; blank = S3 default
        'acl'      => 'public-read',
    ],
],
```

#### Option C — Cloudflare R2 (S3-compatible, no egress fees)
```php
'storage' => [
    'driver' => 'r2',
    'r2' => [
        'account_id' => 'your-cloudflare-account-id',
        'key'        => 'r2-access-key-id',
        'secret'     => 'r2-secret-access-key',
        'bucket'     => 'estate-assets',
        'base_url'   => 'https://pub-xxxx.r2.dev',   // or your custom domain
    ],
],
```

> **No code changes needed** — the `Storage` facade auto-selects the correct driver from `config.php`. The same `Storage::uploadFile()` / `Storage::delete()` calls work identically across all three providers.

### 6. Run the Project
```bash
# Using PHP built-in server (from project root, one level above devcore/)
php -S localhost:8000 -t .

# Or configure Apache/Nginx to point to your project root
```

Then visit:
- **Public Listings:** `http://localhost:8000/estatecore/index.php`
- **Admin Login:** `http://localhost:8000/estatecore/admin/login.php`

### Default Admin Credentials
| Field | Value |
|-------|-------|
| Email | `admin@realestate.com` |
| Password | `admin123` |

> ⚠️ Change this password immediately in production.

---

## 📱 How the QR Signboard System Works

```
Agent prints QR code  →  Sticks it on a physical FOR SALE signboard
         ↓
Buyer drives past, scans QR with their phone camera
         ↓
Buyer lands on: /property.php?id=12&ref=qr
         ↓
System increments scan_count in the database
         ↓
Buyer sees "Scanned from signboard" banner
         ↓
Buyer fills in the inquiry form → Agent gets notification
```

**Where to access QR codes:**
1. Log in to admin panel
2. Go to **QR Generator** (`/admin/qr-generator.php`)
3. See a grid of all properties with their QR codes
4. Click **Print All QR Codes** — the page enters print mode
5. Print on label paper or cardstock and stick on signboards

Each QR code encodes a URL like:
```
https://yourdomain.com/estatecore/property.php?id=7&ref=qr
```

The `?ref=qr` parameter is what triggers scan tracking. The admin QR Generator also shows per-property scan counts so you can see which signboards are generating the most interest.

---

## ⚡ How Real-Time Availability Works

Every **4 seconds**, a `LivePoller` instance on the public listings page calls `/api/live.php`, which returns the current status of every property in a single lightweight query.

```javascript
const livePoller = new LivePoller('api/live.php', (res) => {
  res.data.property_statuses.forEach(ps => {
    const badge = document.querySelector(`.prop-status-${ps.id}`);
    // Update badge text and CSS class
    // If status === 'sold', inject the SOLD ribbon into the card image
  });
}, 4000);
```

When an admin changes a property from **Available → Sold** in the admin panel:
1. `PUT /api/properties.php?id=X` is called with `status: 'sold'`
2. Database is updated immediately
3. Within 4 seconds, all open browser tabs on the public listings page see the status badge change
4. The diagonal **SOLD** ribbon overlay is dynamically injected into the card image

The admin panel also polls for **new inquiry notifications**, showing a live count in the navigation bar.

---

## 📊 How Analytics Tracks Views vs Scans vs Inquiries

| Metric | How Tracked | Where Stored |
|--------|-------------|--------------|
| **Page Views** | Incremented on every `property.php` visit | `properties.views` |
| **QR Scans** | Incremented only when `?ref=qr` is in URL | `properties.scan_count` |
| **Inquiries** | Inserted to `inquiries` table on form submit | `inquiries` table |
| **Inquiries Today** | `COUNT(*) WHERE DATE(created_at) = CURDATE()` | Computed live |
| **Inquiries by Day** | `Analytics::countByDay()` — last 30 days | Computed live |

The dashboard fetches all analytics in a single call to `/api/analytics.php`, which uses the shared `Analytics` class methods:

```php
$analytics->kpi('inquiries')           // Total, today, this week, this month
$analytics->countByDay('inquiries')    // Day-by-day for line chart
$db->fetchAll('SELECT ... top 10 ...')  // Top properties by views
$db->fetchAll('SELECT type, COUNT(*) ...') // Doughnut chart data
```

All charts render using `DCChart.line()`, `DCChart.bar()`, and `DCChart.doughnut()` from the DevCore JS library, which wraps Chart.js with the dark design theme.

---

## 📁 Project Structure

```
estatecore/
├── index.php              ← Public listings: search, filter, live status
├── property.php           ← Property detail: gallery, inquiry form, QR widget
├── inquiry-success.php    ← Confirmation after inquiry submitted
├── admin/
│   ├── login.php          ← Secure admin login
│   ├── dashboard.php      ← Analytics: KPIs, charts, live feed
│   ├── properties.php     ← Manage listings: add/edit/delete/status
│   ├── inquiries.php      ← View & manage all inquiries
│   ├── qr-generator.php   ← Print QR codes for signboards
│   └── logout.php
├── api/
│   ├── properties.php     ← GET/POST/PUT/DELETE properties
│   ├── inquiries.php      ← GET/POST/PUT/DELETE inquiries
│   ├── analytics.php      ← GET dashboard stats
│   └── live.php           ← GET live polling data (statuses + inquiry count)
└── database.sql           ← Complete schema + 20 properties + 30 inquiries
```

---

## 🔗 DevCore Shared Library

This project is built on the **DevCore Shared Library** — a reusable backend + UI kit designed for rapid development of production-grade PHP applications.

> 📦 **Repository:** [github.com/your-org/devcore](https://github.com/your-org/devcore)

The shared library provides:
- `Database` — Singleton PDO wrapper with query helpers
- `Api` — Standardised JSON responses (`Api::success()`, `Api::error()`, `Api::paginated()`)
- `Auth` — Session-based authentication with role support
- `Analytics` — Reusable analytics query methods (`kpi()`, `countByDay()`, `topItems()`)
- `QrCode` — QR code image URL generator (no library dependency)
- `Storage` — Pluggable file storage facade with Local, S3, and R2 drivers (`Storage::uploadFile()`, `Storage::delete()`, `Storage::url()`)
- `devcore.css` — Dark design system with 60+ utility components
- `devcore.js` — `DC`, `Toast`, `Modal`, `LivePoller`, `DCChart`, `DCForm`, `DCFormat`

---

## 🏗 Part of the DevCore Portfolio Suite

> **4 industry projects · 1 shared core · Production-ready PHP**

| Project | Description |
|---------|-------------|
| 🏡 **EstateCore** *(this project)* | Real Estate Property Listing Platform |
| 🍽 **Restrodesk** | Restaurant Menu & Ordering System |
| 🏥 **MediCore** | Medical Appointment Booking Platform |
| 📦 **Livestore** | E-Commerce Product & Order Management |

All four projects share the same `devcore/core/` library — update the core once, all projects benefit.

---

## 📄 License

MIT — free to use, modify, and build upon.
