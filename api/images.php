<?php
/**
 * API — Property Gallery Images
 *
 * POST   /api/images.php?property_id=X   Upload a gallery image for a property
 *                                          Expects multipart/form-data, field: "image"
 *                                          Returns: { id, image_url, sort_order }
 *
 * DELETE /api/images.php?id=X             Delete a gallery image by property_images.id
 *                                          Also removes the file from Storage
 *
 * Admin-only. Both endpoints require an active admin session.
 */
require_once dirname(__DIR__) . '/core/bootstrap.php';

$db     = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

Auth::requireRole('admin', '/admin/login.php');

// ─── POST: upload gallery image ───────────────────────────────
if ($method === 'POST') {
    $propertyId = (int)($_GET['property_id'] ?? 0);
    if (!$propertyId) Api::error('Missing property_id', 400);

    $prop = $db->fetchOne('SELECT id FROM properties WHERE id = ?', [$propertyId]);
    if (!$prop) Api::error('Property not found', 404);

    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        Api::error('No image file received or upload error: ' . ($_FILES['image']['error'] ?? 'no file'), 422);
    }

    try {
        $url = Storage::uploadFile(
            $_FILES['image'],
            'properties/gallery',
            ['image/jpeg', 'image/png', 'image/webp'],
            5 * 1024 * 1024
        );
    } catch (RuntimeException $e) {
        Api::error('Image upload failed: ' . $e->getMessage(), 422);
    }

    // Determine next sort order for this property
    $maxOrder = $db->fetchOne(
        'SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM property_images WHERE property_id = ?',
        [$propertyId]
    );
    $sortOrder = (int)($maxOrder['max_order'] ?? 0) + 1;

    $imageId = $db->insert('property_images', [
        'property_id' => $propertyId,
        'image_url'   => $url,
        'sort_order'  => $sortOrder,
    ]);

    Api::success([
        'id'         => (int)$imageId,
        'image_url'  => $url,
        'sort_order' => $sortOrder,
    ], 'Gallery image uploaded', 201);
}

// ─── DELETE: remove gallery image ────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Api::error('Missing image id', 400);

    $image = $db->fetchOne('SELECT * FROM property_images WHERE id = ?', [$id]);
    if (!$image) Api::error('Image not found', 404);

    // Remove from Storage (skip seed/external URLs such as Unsplash)
    if (!empty($image['image_url']) && !str_contains($image['image_url'], 'unsplash.com')) {
        try { Storage::delete($image['image_url']); } catch (RuntimeException) {}
    }

    $db->delete('property_images', 'id = ?', [$id]);

    Api::success(null, 'Gallery image deleted');
}

Api::error('Method not allowed', 405);
