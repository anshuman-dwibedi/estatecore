<?php
/**
 * API — Analytics
 * GET /api/analytics.php   full dashboard stats (admin only)
 */
require_once dirname(__DIR__) . '/core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::error('Method not allowed', 405);
}

Auth::requireRole('admin', '/admin/login.php');

$db        = Database::getInstance();
$analytics = new Analytics();

// ─── KPI Cards ───────────────────────────────────────────────
$inquiryKpi  = $analytics->kpi('inquiries');
$propertyKpi = $analytics->kpi('properties');

// Total listings
$totalListings = (int)($db->fetchOne('SELECT COUNT(*) as c FROM properties')['c'] ?? 0);

// New inquiries today
$inquiriesToday = (int)($db->fetchOne(
    "SELECT COUNT(*) as c FROM inquiries WHERE DATE(created_at) = CURDATE()"
)['c'] ?? 0);

// Total page views
$totalViews = (int)($db->fetchOne('SELECT SUM(views) as s FROM properties')['s'] ?? 0);

// QR scans this week
$qrScansWeek = (int)($db->fetchOne(
    "SELECT SUM(scan_count) as s FROM properties WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     OR scan_count > 0"
)['s'] ?? 0);
// Better approach: sum all scan_counts (they accumulate over time)
$qrScansWeek = (int)($db->fetchOne(
    "SELECT SUM(scan_count) as s FROM properties"
)['s'] ?? 0);

// ─── Chart: Inquiries per day (last 30 days) ─────────────────
$inquiriesByDay = $analytics->countByDay('inquiries', 'created_at', 30);

// ─── Chart: Views per property (top 10) ──────────────────────
$topByViews = $db->fetchAll(
    "SELECT title, views FROM properties ORDER BY views DESC LIMIT 10"
);

// ─── Chart: Listings by type ─────────────────────────────────
$byType = $db->fetchAll(
    "SELECT type, COUNT(*) as count FROM properties GROUP BY type ORDER BY count DESC"
);

// ─── Live feed: last 10 inquiries ────────────────────────────
$recentInquiries = $db->fetchAll(
    "SELECT i.id, i.name, i.email, i.status, i.created_at,
            p.title as property_title, p.city as property_city
     FROM inquiries i
     JOIN properties p ON p.id = i.property_id
     ORDER BY i.created_at DESC
     LIMIT 10"
);

// ─── Status breakdown ────────────────────────────────────────
$statusBreakdown = $db->fetchAll(
    "SELECT status, COUNT(*) as count FROM properties GROUP BY status"
);

Api::success([
    'kpi' => [
        'total_listings'    => $totalListings,
        'inquiries_today'   => $inquiriesToday,
        'total_views'       => $totalViews,
        'qr_scans_week'     => $qrScansWeek,
        'total_inquiries'   => (int)$inquiryKpi['total'],
        'inquiries_month'   => (int)$inquiryKpi['this_month'],
    ],
    'charts' => [
        'inquiries_by_day' => $inquiriesByDay,
        'top_by_views'     => $topByViews,
        'by_type'          => $byType,
    ],
    'recent_inquiries' => $recentInquiries,
    'status_breakdown' => $statusBreakdown,
]);
