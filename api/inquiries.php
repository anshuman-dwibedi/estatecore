<?php
/**
 * API — Inquiries
 * POST   /api/inquiries.php           submit inquiry (public)
 * GET    /api/inquiries.php           list inquiries (admin)
 * PUT    /api/inquiries.php?id=X      update inquiry status (admin)
 * DELETE /api/inquiries.php?id=X      delete inquiry (admin)
 */
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';

$db     = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── POST: submit inquiry ────────────────────────────────────
if ($method === 'POST') {
    $data = Api::body();

    $v = Validator::make($data, [
        'property_id' => 'required|numeric',
        'name'        => 'required|min:2|max:100',
        'email'       => 'required|email',
        'phone'       => 'max:30',
        'message'     => 'required|min:10|max:2000',
    ]);

    if ($v->fails()) {
        Api::error('Validation failed', 422, $v->errors());
    }

    $propId = (int)$data['property_id'];
    $prop   = $db->fetchOne('SELECT id, title FROM properties WHERE id = ?', [$propId]);
    if (!$prop) {
        Api::error('Property not found', 404);
    }

    $id = $db->insert('inquiries', [
        'property_id' => $propId,
        'name'        => trim($data['name']),
        'email'       => strtolower(trim($data['email'])),
        'phone'       => trim($data['phone'] ?? ''),
        'message'     => trim($data['message']),
        'status'      => 'new',
        'created_at'  => date('Y-m-d H:i:s'),
    ]);

    Api::success(['id' => (int)$id, 'property' => $prop['title']], 'Inquiry submitted successfully', 201);
}

// ─── GET: list inquiries ─────────────────────────────────────
if ($method === 'GET') {
    Auth::requireRole('admin', '/admin/login.php');

    $where  = ['1=1'];
    $params = [];

    if (!empty($_GET['property_id']) && is_numeric($_GET['property_id'])) {
        $where[]  = 'i.property_id = ?';
        $params[] = (int)$_GET['property_id'];
    }
    if (!empty($_GET['status']) && in_array($_GET['status'], ['new','contacted','closed'])) {
        $where[]  = 'i.status = ?';
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['search'])) {
        $where[]  = '(i.name LIKE ? OR i.email LIKE ? OR p.title LIKE ?)';
        $s        = '%' . $_GET['search'] . '%';
        $params   = array_merge($params, [$s, $s, $s]);
    }

    $whereStr = implode(' AND ', $where);
    $total    = (int)($db->fetchOne(
        "SELECT COUNT(*) as c FROM inquiries i
         JOIN properties p ON p.id = i.property_id
         WHERE $whereStr",
        $params
    )['c'] ?? 0);

    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
    $offset  = ($page - 1) * $perPage;

    $inquiries = $db->fetchAll(
        "SELECT i.id, i.property_id, i.name, i.email, i.phone, i.message,
                i.status, i.created_at,
                p.title as property_title, p.address as property_address
         FROM inquiries i
         JOIN properties p ON p.id = i.property_id
         WHERE $whereStr
         ORDER BY i.created_at DESC
         LIMIT $perPage OFFSET $offset",
        $params
    );

    Api::paginated($inquiries, $total, $page, $perPage);
}

// ─── PUT: update inquiry status ──────────────────────────────
if ($method === 'PUT') {
    Auth::requireRole('admin', '/admin/login.php');

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Api::error('Missing inquiry ID', 400);

    $data = Api::body();
    $v    = Validator::make($data, [
        'status' => 'required|in:new,contacted,closed',
    ]);

    if ($v->fails()) {
        Api::error('Validation failed', 422, $v->errors());
    }

    $inq = $db->fetchOne('SELECT id FROM inquiries WHERE id = ?', [$id]);
    if (!$inq) Api::error('Inquiry not found', 404);

    $db->update('inquiries', ['status' => $data['status']], 'id = ?', [$id]);
    Api::success(['id' => $id, 'status' => $data['status']], 'Status updated');
}

// ─── DELETE: remove inquiry ──────────────────────────────────
if ($method === 'DELETE') {
    Auth::requireRole('admin', '/admin/login.php');

    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Api::error('Missing inquiry ID', 400);

    $inq = $db->fetchOne('SELECT id FROM inquiries WHERE id = ?', [$id]);
    if (!$inq) Api::error('Inquiry not found', 404);

    $db->delete('inquiries', 'id = ?', [$id]);
    Api::success(null, 'Inquiry deleted');
}

Api::error('Method not allowed', 405);
