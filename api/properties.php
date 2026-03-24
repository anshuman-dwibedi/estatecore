<?php
/**
 * API — Properties
 * GET    /api/properties.php          list with filters / single lookup
 * POST   /api/properties.php          create property  (multipart/form-data with optional image file)
 * PUT    /api/properties.php?id=X     update property  (multipart/form-data with optional image file)
 * DELETE /api/properties.php?id=X     delete property  (also removes stored image)
 *
 * Image upload:
 *   Send as multipart/form-data with field name "image" (JPEG/PNG/WebP, max 5 MB).
 *   Uploaded file is stored via Storage::uploadFile() and its public URL is saved
 *   to properties.image_url.  If no file is sent the existing URL is kept as-is.
 *   You may also pass image_url as a plain text field to set an external URL directly.
 */
require_once dirname(__DIR__) . '/core/bootstrap.php';

$db     = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── Helper: is this URL one we own in Storage? ───────────────
function isStoredUrl(string $url): bool {
    // Exclude seeded Unsplash URLs and blank strings; treat everything else as ours
    return $url !== ''
        && !str_contains($url, 'unsplash.com')
        && !str_contains($url, 'images.unsplash');
}

// ─── GET ─────────────────────────────────────────────────────
if ($method === 'GET') {
    $where  = ['1=1'];
    $params = [];

    if (!empty($_GET['type']) && in_array($_GET['type'], ['house','apartment','villa','land'])) {
        $where[]  = 'type = ?';
        $params[] = $_GET['type'];
    }
    if (!empty($_GET['city'])) {
        $where[]  = 'city LIKE ?';
        $params[] = '%' . $_GET['city'] . '%';
    }
    if (!empty($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $where[]  = 'price >= ?';
        $params[] = (float)$_GET['min_price'];
    }
    if (!empty($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $where[]  = 'price <= ?';
        $params[] = (float)$_GET['max_price'];
    }
    if (!empty($_GET['bedrooms']) && is_numeric($_GET['bedrooms'])) {
        $where[]  = 'bedrooms >= ?';
        $params[] = (int)$_GET['bedrooms'];
    }
    if (!empty($_GET['status']) && in_array($_GET['status'], ['available','under_offer','sold'])) {
        $where[]  = 'status = ?';
        $params[] = $_GET['status'];
    }

    // Single property lookup
    if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
        $prop = $db->fetchOne('SELECT * FROM properties WHERE id = ?', [(int)$_GET['id']]);
        if (!$prop) Api::error('Property not found', 404);
        $images = $db->fetchAll(
            'SELECT id, image_url, sort_order FROM property_images WHERE property_id = ? ORDER BY sort_order ASC',
            [(int)$_GET['id']]
        );
        $prop['gallery'] = $images;
        Api::success($prop);
    }

    $sort = 'created_at DESC';
    if (!empty($_GET['sort'])) {
        $sortMap = [
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
            'newest'     => 'created_at DESC',
            'views'      => 'views DESC',
        ];
        $sort = $sortMap[$_GET['sort']] ?? $sort;
    }

    $whereStr = implode(' AND ', $where);
    $total    = (int)($db->fetchOne("SELECT COUNT(*) as c FROM properties WHERE $whereStr", $params)['c'] ?? 0);
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $perPage  = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
    $offset   = ($page - 1) * $perPage;

    $properties = $db->fetchAll(
        "SELECT id, title, type, price, bedrooms, bathrooms, area_sqft, address, city,
                image_url, status, views, scan_count, created_at
         FROM properties WHERE $whereStr ORDER BY $sort LIMIT $perPage OFFSET $offset",
        $params
    );

    Api::paginated($properties, $total, $page, $perPage);
}

// ─── POST: create ─────────────────────────────────────────────
if ($method === 'POST') {
    Auth::requireRole('admin', '/admin/login.php');

    // Accept multipart form-data (file upload) or JSON body
    $data = !empty($_POST) ? $_POST : Api::body();

    $v = Validator::make($data, [
        'title'       => 'required|min:5|max:200',
        'description' => 'required|min:20',
        'type'        => 'required|in:house,apartment,villa,land',
        'price'       => 'required|numeric',
        'bedrooms'    => 'numeric',
        'bathrooms'   => 'numeric',
        'area_sqft'   => 'numeric',
        'address'     => 'required|min:5',
        'city'        => 'required|min:2',
        'status'      => 'required|in:available,under_offer,sold',
    ]);

    if ($v->fails()) {
        Api::error('Validation failed', 422, $v->errors());
    }

    // ── Hero image: uploaded file takes priority over URL field ─
    $imageUrl = trim($data['image_url'] ?? '');

    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imageUrl = Storage::uploadFile(
                $_FILES['image'],
                'properties/hero',
                ['image/jpeg', 'image/png', 'image/webp'],
                5 * 1024 * 1024
            );
        } catch (RuntimeException $e) {
            Api::error('Image upload failed: ' . $e->getMessage(), 422);
        }
    }

    $id = $db->insert('properties', [
        'title'       => trim($data['title']),
        'description' => trim($data['description']),
        'type'        => $data['type'],
        'price'       => (float)$data['price'],
        'bedrooms'    => (int)($data['bedrooms'] ?? 0),
        'bathrooms'   => (int)($data['bathrooms'] ?? 0),
        'area_sqft'   => (int)($data['area_sqft'] ?? 0),
        'address'     => trim($data['address']),
        'city'        => trim($data['city']),
        'image_url'   => $imageUrl,
        'status'      => $data['status'],
        'views'       => 0,
        'scan_count'  => 0,
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    $prop = $db->fetchOne('SELECT * FROM properties WHERE id = ?', [(int)$id]);
    Api::success($prop, 'Property created', 201);
}

// ─── PUT: update ──────────────────────────────────────────────
if ($method === 'PUT') {
    Auth::requireRole('admin', '/admin/login.php');

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Api::error('Missing property ID', 400);

    $existing = $db->fetchOne('SELECT * FROM properties WHERE id = ?', [$id]);
    if (!$existing) Api::error('Property not found', 404);

    $data = !empty($_POST) ? $_POST : Api::body();

    $v = Validator::make($data, [
        'title'       => 'required|min:5|max:200',
        'description' => 'required|min:20',
        'type'        => 'required|in:house,apartment,villa,land',
        'price'       => 'required|numeric',
        'bedrooms'    => 'numeric',
        'bathrooms'   => 'numeric',
        'area_sqft'   => 'numeric',
        'address'     => 'required|min:5',
        'city'        => 'required|min:2',
        'status'      => 'required|in:available,under_offer,sold',
    ]);

    if ($v->fails()) {
        Api::error('Validation failed', 422, $v->errors());
    }

    // ── Hero image: new upload replaces old stored file ─────────
    $imageUrl = $existing['image_url']; // keep existing by default

    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $newUrl = Storage::uploadFile(
                $_FILES['image'],
                'properties/hero',
                ['image/jpeg', 'image/png', 'image/webp'],
                5 * 1024 * 1024
            );
            // Remove the old stored file (skip seed/external URLs)
            if (isStoredUrl($existing['image_url'])) {
                try { Storage::delete($existing['image_url']); } catch (RuntimeException) {}
            }
            $imageUrl = $newUrl;
        } catch (RuntimeException $e) {
            Api::error('Image upload failed: ' . $e->getMessage(), 422);
        }
    } elseif (isset($data['image_url'])) {
        // Allow explicit URL update (e.g. clearing or setting an external URL)
        $imageUrl = trim($data['image_url']);
    }

    $db->update('properties', [
        'title'       => trim($data['title']),
        'description' => trim($data['description']),
        'type'        => $data['type'],
        'price'       => (float)$data['price'],
        'bedrooms'    => (int)($data['bedrooms'] ?? 0),
        'bathrooms'   => (int)($data['bathrooms'] ?? 0),
        'area_sqft'   => (int)($data['area_sqft'] ?? 0),
        'address'     => trim($data['address']),
        'city'        => trim($data['city']),
        'image_url'   => $imageUrl,
        'status'      => $data['status'],
    ], 'id = ?', [$id]);

    $updated = $db->fetchOne('SELECT * FROM properties WHERE id = ?', [$id]);
    Api::success($updated, 'Property updated');
}

// ─── DELETE: remove property + all stored images ─────────────
if ($method === 'DELETE') {
    Auth::requireRole('admin', '/admin/login.php');

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Api::error('Missing property ID', 400);

    $prop = $db->fetchOne('SELECT * FROM properties WHERE id = ?', [$id]);
    if (!$prop) Api::error('Property not found', 404);

    // Delete hero image from storage (skip Unsplash seed images)
    if (isStoredUrl($prop['image_url'])) {
        try { Storage::delete($prop['image_url']); } catch (RuntimeException) {}
    }

    // Delete all gallery images from storage
    $gallery = $db->fetchAll('SELECT image_url FROM property_images WHERE property_id = ?', [$id]);
    foreach ($gallery as $img) {
        if (isStoredUrl($img['image_url'])) {
            try { Storage::delete($img['image_url']); } catch (RuntimeException) {}
        }
    }

    $db->delete('property_images', 'property_id = ?', [$id]);
    $db->delete('properties', 'id = ?', [$id]);

    Api::success(null, 'Property deleted');
}

Api::error('Method not allowed', 405);
