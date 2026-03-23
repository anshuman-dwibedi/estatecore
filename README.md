# ðŸ¡ EstateCore â€” Real Estate Property Listing Platform

> **A production-grade real estate platform** built on the DevCore shared library.
> Dark card grid aesthetic, live property availability, QR signboard system, and a full analytics dashboard.
> Part of the **DevCore Portfolio Suite** â€” 4 industry projects, 1 shared core.

---

## âœ¨ Features

### Public-Facing
- ðŸ  **Property Listings** â€” Filterable grid of houses, apartments, villas & land
- ðŸ” **Dynamic Search** â€” Filter by type, city, price range, bedrooms, status â€” no page reload
- ðŸ“„ **Property Detail Pages** â€” Hero image, gallery thumbnails, full description, two-column layout
- ðŸ“¬ **Inquiry Form** â€” Visitors submit inquiries directly from any listing page
- âœ… **Confirmation Page** â€” Friendly thank-you page with next-steps guide
- ðŸ–¼ï¸ **Storage-backed Image Uploads** â€” Hero images and gallery photos upload via `Storage::uploadFile()`. Swap between local disk, AWS S3, or Cloudflare R2 by changing one config line â€” zero code changes required
- ðŸ—‘ï¸ **Automatic Image Cleanup** â€” Deleting a property also deletes its stored images from whatever provider is active
- ðŸ–¼ï¸ **Gallery Management** â€” Upload, preview, and remove multiple gallery images per property from the admin panel
- ðŸ“¤ **Drag-and-drop Upload UI** â€” Hero image field supports drag-and-drop with live preview and XHR progress bar
- ðŸ”´ **SOLD Overlay** â€” Sold properties display a diagonal ribbon and overlay on their card image

### Real-Time
- âš¡ **Live Availability Updates** â€” Property status badges refresh every 4 seconds via `LivePoller`
- ðŸ†• **New Inquiry Notifications** â€” Admin sidebar shows live count of new inquiries
- ðŸ”„ **Status Propagation** â€” When admin marks a property Sold, the SOLD ribbon appears on the public listing instantly

### Admin Portal
- ðŸ“Š **Analytics Dashboard** â€” KPI stat cards, line/bar/doughnut charts, live inquiry feed
- ðŸ  **Property Management** â€” Add, edit, delete, toggle availability for all listings
- ðŸ“¬ **Inquiry Management** â€” View, filter, update status, and reply to all inquiries
- ðŸ“± **QR Code Generator** â€” Print-optimized grid of QR codes for all properties
- ðŸ” **Secure Auth** â€” Session-based login with bcrypt password hashing

### Analytics
- ðŸ‘ï¸ **View Tracking** â€” Every property page visit increments the views counter
- ðŸ“± **QR Scan Tracking** â€” `?ref=qr` in the URL increments the `scan_count` column
- ðŸ“ˆ **Inquiry Trends** â€” Chart of inquiries per day for the last 30 days
- ðŸ† **Top Properties** â€” Bar chart of the 10 most-viewed listings
- ðŸ© **Type Distribution** â€” Doughnut chart: Houses / Apartments / Villas / Land

---

## â˜ï¸ Storage System â€” Image Uploads

EstateCore uses the **DevCore Storage** abstraction for all property image uploads. Switch between local disk, AWS S3, and Cloudflare R2 by changing **one line** in `config.php`.

### How it works

| Feature | Implementation |
|---------|---------------|
| **Hero image upload** | Admin drag-and-drops or selects a file â†’ `POST /api/properties.php` â†’ `Storage::uploadFile()` â†’ URL saved to `properties.image_url` |
| **Gallery image upload** | Separate ðŸ–¼ï¸ modal per property â†’ `POST /api/images.php?property_id=X` â†’ `Storage::uploadFile()` â†’ URL saved to `property_images` |
| **Image deletion** | On property delete or gallery image remove â†’ `Storage::delete()` removes the file from whichever driver is active |
| **Seed / external URLs** | Unsplash seed images are never passed to `Storage::delete()` â€” detected via `isStoredUrl()` helper |

### Configure your storage driver

Open `devcore/config.php` and set `storage.driver`:

```php
'storage' => [
    'driver' => 'local',   // â† swap to 's3' or 'r2' to go cloud-native

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

No code changes required â€” the `Storage` facade picks the correct driver automatically.

### Supported drivers

| Driver | Class | Best for |
|--------|-------|---------|
| `local` | `LocalStorage` | Development, self-hosted |
| `s3` | `S3Storage` | AWS deployments (no SDK needed â€” raw HTTP + HMAC-SHA1) |
| `r2` | `R2Storage` | Cloudflare deployments (AWS Signature V4 compatible) |

---

## ðŸ›  Tech Stack

![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![DevCore](https://img.shields.io/badge/DevCore-Shared_Library-e8a838?style=flat)
![Storage](https://img.shields.io/badge/Storage-Local%20%7C%20S3%20%7C%20R2-6c63ff?style=flat)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=flat&logo=chartdotjs&logoColor=white)
![Vanilla JS](https://img.shields.io/badge/JavaScript-Vanilla_ES2022-F7DF1E?style=flat&logo=javascript&logoColor=black)
![QR Server](https://img.shields.io/badge/QR_API-goqr.me-22d3a0?style=flat)
![Storage](https://img.shields.io/badge/Storage-Local_|_S3_|_R2-38bdf8?style=flat)

---

## ðŸš€ Setup Instructions

### 1. Clone DevCore Shared Library
```bash
git clone https://github.com/anshuman-dwibedi/devcore.git
# Result: devcore/core/bootstrap.php, devcore/core/ui/, etc.
```

### 2. Clone This Project
```bash
git clone https://github.com/anshuman-dwibedi/estatecore.git
# Place so that: devcore/ and estatecore/ are siblings
```

Expected folder structure:
```
your-project/
â”œâ”€â”€ devcore/
â”‚   â”œâ”€â”€ core/
â”‚   â”‚   â”œâ”€â”€ bootstrap.php
â”‚   â”‚   â”œâ”€â”€ backend/
â”‚   â”‚   â””â”€â”€ ui/
â”‚   â””â”€â”€ config.example.php
â””â”€â”€ estatecore/
    â”œâ”€â”€ index.php
    â”œâ”€â”€ property.php
    â”œâ”€â”€ admin/
    â””â”€â”€ api/
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

The platform uses the DevCore `Storage` facade â€” swap providers by changing **one line** in `config.php`.

#### Option A â€” Local filesystem (default, works immediately)
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

#### Option B â€” AWS S3
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

#### Option C â€” Cloudflare R2 (S3-compatible, no egress fees)
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

> **No code changes needed** â€” the `Storage` facade auto-selects the correct driver from `config.php`. The same `Storage::uploadFile()` / `Storage::delete()` calls work identically across all three providers.

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

> âš ï¸ Change this password immediately in production.

---

## ðŸ“± How the QR Signboard System Works

```
Agent prints QR code  â†’  Sticks it on a physical FOR SALE signboard
         â†“
Buyer drives past, scans QR with their phone camera
         â†“
Buyer lands on: /property.php?id=12&ref=qr
         â†“
System increments scan_count in the database
         â†“
Buyer sees "Scanned from signboard" banner
         â†“
Buyer fills in the inquiry form â†’ Agent gets notification
```

**Where to access QR codes:**
1. Log in to admin panel
2. Go to **QR Generator** (`/admin/qr-generator.php`)
3. See a grid of all properties with their QR codes
4. Click **Print All QR Codes** â€” the page enters print mode
5. Print on label paper or cardstock and stick on signboards

Each QR code encodes a URL like:
```
https://yourdomain.com/estatecore/property.php?id=7&ref=qr
```

The `?ref=qr` parameter is what triggers scan tracking. The admin QR Generator also shows per-property scan counts so you can see which signboards are generating the most interest.

---

## âš¡ How Real-Time Availability Works

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

When an admin changes a property from **Available â†’ Sold** in the admin panel:
1. `PUT /api/properties.php?id=X` is called with `status: 'sold'`
2. Database is updated immediately
3. Within 4 seconds, all open browser tabs on the public listings page see the status badge change
4. The diagonal **SOLD** ribbon overlay is dynamically injected into the card image

The admin panel also polls for **new inquiry notifications**, showing a live count in the navigation bar.

---

## ðŸ“Š How Analytics Tracks Views vs Scans vs Inquiries

| Metric | How Tracked | Where Stored |
|--------|-------------|--------------|
| **Page Views** | Incremented on every `property.php` visit | `properties.views` |
| **QR Scans** | Incremented only when `?ref=qr` is in URL | `properties.scan_count` |
| **Inquiries** | Inserted to `inquiries` table on form submit | `inquiries` table |
| **Inquiries Today** | `COUNT(*) WHERE DATE(created_at) = CURDATE()` | Computed live |
| **Inquiries by Day** | `Analytics::countByDay()` â€” last 30 days | Computed live |

The dashboard fetches all analytics in a single call to `/api/analytics.php`, which uses the shared `Analytics` class methods:

```php
$analytics->kpi('inquiries')           // Total, today, this week, this month
$analytics->countByDay('inquiries')    // Day-by-day for line chart
$db->fetchAll('SELECT ... top 10 ...')  // Top properties by views
$db->fetchAll('SELECT type, COUNT(*) ...') // Doughnut chart data
```

All charts render using `DCChart.line()`, `DCChart.bar()`, and `DCChart.doughnut()` from the DevCore JS library, which wraps Chart.js with the dark design theme.

---

## ðŸ“ Project Structure

```
estatecore/
â”œâ”€â”€ index.php              â† Public listings: search, filter, live status
â”œâ”€â”€ property.php           â† Property detail: gallery, inquiry form, QR widget
â”œâ”€â”€ inquiry-success.php    â† Confirmation after inquiry submitted
â”œâ”€â”€ admin/
â”‚   â”œâ”€â”€ login.php          â† Secure admin login
â”‚   â”œâ”€â”€ dashboard.php      â† Analytics: KPIs, charts, live feed
â”‚   â”œâ”€â”€ properties.php     â† Manage listings: add/edit/delete/status
â”‚   â”œâ”€â”€ inquiries.php      â† View & manage all inquiries
â”‚   â”œâ”€â”€ qr-generator.php   â† Print QR codes for signboards
â”‚   â””â”€â”€ logout.php
â”œâ”€â”€ api/
â”‚   â”œâ”€â”€ properties.php     â† GET/POST/PUT/DELETE properties
â”‚   â”œâ”€â”€ inquiries.php      â† GET/POST/PUT/DELETE inquiries
â”‚   â”œâ”€â”€ analytics.php      â† GET dashboard stats
â”‚   â””â”€â”€ live.php           â† GET live polling data (statuses + inquiry count)
â””â”€â”€ database.sql           â† Complete schema + 20 properties + 30 inquiries
```

---

## ðŸ”— DevCore Shared Library

This project is built on the **DevCore Shared Library** â€” a reusable backend + UI kit designed for rapid development of production-grade PHP applications.

> 📦 **Repository:** [github.com/anshuman-dwibedi/devcore](https://github.com/anshuman-dwibedi/devcore)\r\n> ðŸ“¦ **Repository:** [github.com/your-org/devcore](https://github.com/your-org/devcore)

The shared library provides:
- `Database` â€” Singleton PDO wrapper with query helpers
- `Api` â€” Standardised JSON responses (`Api::success()`, `Api::error()`, `Api::paginated()`)
- `Auth` â€” Session-based authentication with role support
- `Analytics` â€” Reusable analytics query methods (`kpi()`, `countByDay()`, `topItems()`)
- `QrCode` â€” QR code image URL generator (no library dependency)
- `Storage` â€” Pluggable file storage facade with Local, S3, and R2 drivers (`Storage::uploadFile()`, `Storage::delete()`, `Storage::url()`)
- `devcore.css` â€” Dark design system with 60+ utility components
- `devcore.js` â€” `DC`, `Toast`, `Modal`, `LivePoller`, `DCChart`, `DCForm`, `DCFormat`

---

## ðŸ— Part of the DevCore Portfolio Suite

> **4 industry projects Â· 1 shared core Â· Production-ready PHP**

| Project | Description |
|---------|-------------|
| ðŸ¡ **EstateCore** *(this project)* | Real Estate Property Listing Platform |
| ðŸ½ **Restrodesk** | Restaurant Menu & Ordering System |
| ðŸ¥ **MediCore** | Medical Appointment Booking Platform |
| ðŸ“¦ **Livestore** | E-Commerce Product & Order Management |

All four projects share the same `devcore/core/` library â€” update the core once, all projects benefit.

---

## ðŸ“„ License

MIT â€” free to use, modify, and build upon.


