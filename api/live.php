<?php
/**
 * API — Live Polling
 * GET /api/live.php   real-time inquiry count + property statuses
 * Called every 4 seconds by LivePoller on index.php and admin pages
 */
require_once dirname(__DIR__) . '/core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::error('Method not allowed', 405);
}

$db = Database::getInstance();

// ─── New inquiries count (since a given timestamp) ───────────
$since = $_GET['since'] ?? null;
$newInquiryCount = 0;
$recentInquiries = [];

if ($since && strtotime($since)) {
    $newInquiryCount = (int)($db->fetchOne(
        "SELECT COUNT(*) as c FROM inquiries WHERE created_at > ?",
        [$since]
    )['c'] ?? 0);

    $recentInquiries = $db->fetchAll(
        "SELECT i.id, i.name, i.created_at, i.status,
                p.title as property_title
         FROM inquiries i
         JOIN properties p ON p.id = i.property_id
         WHERE i.created_at > ?
         ORDER BY i.created_at DESC
         LIMIT 5",
        [$since]
    );
} else {
    // First call — return count of today's new inquiries
    $newInquiryCount = (int)($db->fetchOne(
        "SELECT COUNT(*) as c FROM inquiries WHERE DATE(created_at) = CURDATE() AND status = 'new'"
    )['c'] ?? 0);

    $recentInquiries = $db->fetchAll(
        "SELECT i.id, i.name, i.created_at, i.status,
                p.title as property_title
         FROM inquiries i
         JOIN properties p ON p.id = i.property_id
         ORDER BY i.created_at DESC
         LIMIT 5"
    );
}

// ─── All property statuses (for live badge updates on index.php) ─
$propertyStatuses = $db->fetchAll(
    "SELECT id, status, views FROM properties ORDER BY id ASC"
);

// ─── Current server timestamp (client uses as next 'since') ──
$serverTime = date('Y-m-d H:i:s');

Api::success([
    'new_inquiry_count' => $newInquiryCount,
    'recent_inquiries'  => $recentInquiries,
    'property_statuses' => $propertyStatuses,
    'server_time'       => $serverTime,
]);
