# EstateCore - Real Estate Property Listing Platform

A production-grade real estate platform built on the DevCore Shared Library. Features filterable property listings, dynamic search, QR signboard system, live availability updates, and a comprehensive analytics dashboard for agents and property managers.

Browse listings, submit inquiries, scan QR codes on signboards, and track property interest metrics in real-time.

**Part of the DevCore Suite** â€” a collection of business-ready web applications sharing a common core library.

---

## Features

| Feature | Description |
|---------|-------------|
| Property Listings | Filterable grid view with search, sort, and pagination |
| Dynamic Search | Filter by property type, city, price range, bedrooms, and availability status without page reload |
| Property Details | Hero image, gallery thumbnails, full description, two-column layout with inquiry form |
| Inquiry Management | Public inquiry submission form on every listing â†’ admin inbox with status tracking |
| Image Storage | Upload hero and gallery images to local filesystem, AWS S3, or Cloudflare R2 â€” change one config line |
| QR Signboard System | Generate printable QR codes linking to property pages with scan tracking |
| Live Availability | Property status badges (Available/Pending/Sold) refresh every 4 seconds via polling |
| SOLD Badge | Sold properties display visual indicator on listing cards and detail pages |
| Analytics Dashboard | KPI stat cards, inquiry trends, top properties, property type distribution, live feed |
| Secure Admin Panel | Session-based authentication with bcrypt password hashing |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.1+ with DevCore framework |
| Database | MySQL 8 / MariaDB 10.6+ |
| Frontend | Vanilla JavaScript ES2022 + DevCore UI library |
| Charts | Chart.js via DevCore wrapper |
| QR Codes | qrserver.com API (no library dependencies) |
| Image Storage | Local filesystem (swap to S3 or R2 in config) |
| Sessions | PHP native sessions for auth |
| Shared Core | DevCore Shared Library (git submodule at ./core/) |

---

## Project Structure

```
estatecore/
â”œâ”€â”€ index.php                   Public property listings page
â”œâ”€â”€ property.php                Single property detail + inquiry form
â”œâ”€â”€ inquiry-success.php         Confirmation page after submitting inquiry
â”œâ”€â”€ config.example.php          Configuration template
â”œâ”€â”€ database.sql                Schema + sample data
â”œâ”€â”€ .env.example                Environment variables template
â”‚
â”œâ”€â”€ api/
â”‚   â”œâ”€â”€ properties.php          GET list/filter, POST create, PUT update, DELETE remove (admin)
â”‚   â”œâ”€â”€ inquiries.php           POST submit, GET list/search, PUT update status, DELETE (admin)
â”‚   â”œâ”€â”€ images.php              POST upload gallery image, DELETE remove image (admin)
â”‚   â”œâ”€â”€ live.php                GET real-time status updates (public polling)
â”‚   â””â”€â”€ analytics.php           GET dashboard statistics (admin only)
â”‚
â”œâ”€â”€ admin/
â”‚   â”œâ”€â”€ login.php               Admin authentication
â”‚   â”œâ”€â”€ dashboard.php           Analytics dashboard + inquiry feed
â”‚   â”œâ”€â”€ properties.php          Property management (add/edit/delete)
â”‚   â”œâ”€â”€ inquiries.php           Inquiry management + reply system
â”‚   â”œâ”€â”€ qr-generator.php        Printable QR codes for all properties
â”‚   â””â”€â”€ logout.php              Session logout
â”‚
â””â”€â”€ core/                       DevCore shared library (git submodule)
    â”œâ”€â”€ bootstrap.php           Autoloader + config loader
    â”œâ”€â”€ backend/                PHP classes (Database, Api, Auth, Storage, etc.)
    â””â”€â”€ ui/                     CSS framework + JavaScript utilities
```

---

## Setup Instructions

### 1. Clone DevCore Shared Library

```bash
git clone https://github.com/anshuman-dwibedi/devcore-shared.git core
```

Or if using this as a git submodule, it's automatically initialized when you clone:
```bash
git clone --recursive https://github.com/anshuman-dwibedi/estatecore.git
```

### 2. Create Database

```bash
mysql -u root -p -e "CREATE DATABASE real_estate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p real_estate < database.sql
```

Replace `real_estate` with your desired database name. Update config.php accordingly.

### 3. Configure Application

```bash
cp config.example.php config.php
```

Edit `config.php` with your environment:

```php
return [
    'db_host'    => 'localhost',
    'db_name'    => 'real_estate',
    'db_user'    => 'root',
    'db_pass'    => 'your_password',
    'app_name'   => 'EstateCore',
    'app_url'    => 'http://localhost/estatecore',
    'debug'      => true,  // set false in production
    'api_secret' => 'your-secure-random-string',
];
```

### 4. Configure Storage

Choose where to store property images (local disk, AWS S3, or Cloudflare R2):

**Local filesystem (default):**
```php
'storage' => [
    'driver' => 'local',
    'local' => [
        'root'     => __DIR__ . '/uploads',
        'base_url' => 'http://localhost/estatecore/uploads',
    ],
],
```

**AWS S3:**
```php
'storage' => [
    'driver' => 's3',
    's3' => [
        'key'      => 'YOUR_AWS_KEY',
        'secret'   => 'YOUR_AWS_SECRET',
        'bucket'   => 'estatecore-bucket',
        'region'   => 'us-east-1',
        'base_url' => '',  // optional CloudFront URL
        'acl'      => 'public-read',
    ],
],
```

**Cloudflare R2:**
```php
'storage' => [
    'driver' => 'r2',
    'r2' => [
        'account_id' => 'YOUR_CF_ACCOUNT_ID',
        'key'        => 'YOUR_R2_KEY',
        'secret'     => 'YOUR_R2_SECRET',
        'bucket'     => 'estatecore',
        'base_url'   => 'https://pub-xxxx.r2.dev',
    ],
],
```

Create uploads folder and set permissions:
```bash
mkdir -p uploads
chmod 755 uploads
```

### 5. Start Web Server

Using PHP built-in server (from project root):
```bash
php -S localhost:8000
```

Or configure Apache/Nginx to point to the project root.

### 6. Access Application

- **Public Listings:** http://localhost:8000/estatecore/index.php
- **Admin Panel:** http://localhost:8000/estatecore/admin/login.php

**Default Admin Credentials:**
```
Email: admin@estatecore.com
Password: admin123
```

> Change these credentials immediately in production.

---

## Configuration

### config.example.php

**Database:**
```php
'db_host'   => 'localhost',
'db_name'   => 'real_estate',
'db_user'   => 'root',
'db_pass'   => '',
```

**App Settings:**
```php
'app_name'   => 'EstateCore',
'app_url'    => 'http://localhost/estatecore',
'debug'      => true,
'api_secret' => 'change-this-to-a-random-secret',
```

**Storage Driver:**
Supports 'local', 's3', or 'r2'. Changing this one line swaps all image uploads:
```php
'storage' => ['driver' => 'local'],  // â† change this
```

---

## How It Works

### Property Listings & Search

1. Admin adds properties in the admin panel
2. Properties are stored in `properties` table with: id, type, city, price, bedrooms, description, image_url, status, views, scan_count
3. Public `index.php` renders filterable grid via `GET /api/properties.php`
4. Filters (type, city, price range) are applied via JavaScript to matching properties
5. Clicking a property navigates to `property.php?id=X`

### QR Signboard System

```
1. Admin generates QR codes in /admin/qr-generator.php
2. Print QR codes and stick on physical FOR SALE signboards
3. Buyer scans QR with phone camera
4. QR encodes URL: https://yourdomain.com/estatecore/property.php?id=12&ref=qr
5. ?ref=qr parameter increments scan_count in database
6. Buyer sees "Scanned from signboard" banner on property page
7. Buyer can submit inquiry directly from that page
8. Agent views scan counts in admin dashboard to see which signboards generate interest
```

**Access QR Generator:**
- Admin Panel â†’ QR Generator â†’ Print All QR Codes (print-friendly page)

### Live Availability Updates

Every **4 seconds**, the public listings page polls `/api/live.php` which returns real-time property statuses:

```javascript
const livePoller = new LivePoller('api/live.php', (res) => {
  // Update property status badges in real-time
  // If status === 'sold', inject SOLD ribbon visual indicator
}, 4000);
```

When admin marks a property as Sold:
1. Status is updated in database
2. Within 4 seconds, all open browser tabs see the change
3. SOLD ribbon appears on listing cards

### Analytics Dashboarding

Dashboard tracks three distinct metrics:

| Metric | How It's Incremented | Stored In |
|--------|-------------------|-----------|
| Page Views | Every time property.php loads (any URL) | properties.views |
| QR Scans | Only when property.php loads with ?ref=qr | properties.scan_count |
| Inquiries | When visitor submits inquiry form | inquiries.created_at |

Charts render daily trends for the last 30 days and top-performing properties.

### Storage Abstraction

The DevCore `Storage` facade handles all image uploads, delegating to the configured driver:

```php
// Admin uploads hero image for property
Storage::uploadFile($_FILES['image'], 'properties');  // Works with any driver

// When deleting property, image is automatically removed
Storage::delete($image_url);  // Auto-detects if stored vs external
```

No code changes needed â€” the same API works identically for local, S3, and R2.

---

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | /api/properties.php | No | List all properties with optional filters (type, city, price, bedrooms) |
| POST | /api/properties.php | Admin | Create new property with image upload |
| PUT | /api/properties.php?id=X | Admin | Update property details (image, status, etc.) |
| DELETE | /api/properties.php?id=X | Admin | Delete property and remove uploaded images |
| GET | /api/inquiries.php | No/Admin | Submit or list inquiries (public submission, admin view) |
| POST | /api/inquiries.php | No | Submit inquiry from property page |
| PUT | /api/inquiries.php?id=X | Admin | Update inquiry status (new/contacted/converted/lost) |
| DELETE | /api/inquiries.php?id=X | Admin | Delete inquiry |
| POST | /api/images.php?property_id=X | Admin | Upload gallery image for property |
| DELETE | /api/images.php?id=X | Admin | Delete gallery image |
| GET | /api/live.php | No | Real-time property statuses and new inquiry counts (polling) |
| GET | /api/analytics.php | Admin | Dashboard statistics (views, scans, inquiries, charts) |

---

## Troubleshooting

**Database not found error**
- Ensure you created the database: `mysql -u root -e "CREATE DATABASE real_estate;"`
- Verify database name in config.php matches
- Check MySQL is running: `mysql -u root -p -e "SELECT 1;"`

**"Cannot include core/bootstrap.php" error**
- Ensure DevCore is cloned at `./core/` relative to project root
- Run: `git clone https://github.com/anshuman-dwibedi/devcore-shared.git core`
- Or if using submodule: `git submodule update --init`

**Images not uploading**
- Check `uploads/` folder exists and is writable: `chmod 755 uploads/`
- Verify storage config in config.php is correct
- Check file permissions and disk space available

**QR codes not scanning**
- QR Generator uses qrserver.com API (requires internet)
- Ensure `?ref=qr` parameter appears in generated URLs
- Test: Scan a generated QR and check URL contains `&ref=qr`

**Live updates not working**
- Check browser console for JavaScript errors
- Verify `/api/live.php` is accessible and returns JSON
- Ensure polling interval is not too aggressive (default: 4 seconds)

**Admin login not working**
- Verify database imported correctly: `SELECT COUNT(*) FROM users;`
- Try password reset in database: `UPDATE users SET password = '$2y$10$...' WHERE email = 'admin@estatecore.com';`
- Check session handler: ensure `php.ini` has proper session settings

---

## Environment Variables

Create `.env` file or configure in config.php:

| Variable | Purpose |
|----------|---------|
| DB_HOST | MySQL server hostname (default: localhost) |
| DB_NAME | Database name (default: real_estate) |
| DB_USER | Database username (default: root) |
| DB_PASS | Database password |
| APP_NAME | Application title displayed in UI |
| APP_URL | Base URL for public access (used in links, QR codes) |
| DEBUG | Enable/disable debug mode (true/false) |
| API_SECRET | Secret key for API bearer token validation |
| STORAGE_DRIVER | Image storage backend: 'local', 's3', or 'r2' |
| UPLOADS_PATH | Path to uploads folder (default: ./uploads) |

---

## License

MIT License â€” see LICENSE file for details.

---

**Questions or issues?** Visit the [DevCore Shared Library](https://github.com/anshuman-dwibedi/devcore-shared) repository.

