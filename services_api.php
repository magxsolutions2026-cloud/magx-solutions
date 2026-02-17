<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/app_bootstrap.php';
magx_send_security_headers();
magx_require_post_request();
header('Content-Type: application/json; charset=utf-8');

$db = magx_db_connect();
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = strtoupper(trim((string)($_POST['action'] ?? '')));
magx_require_admin_for_action($action, ['ADD', 'EDIT', 'DELETE', 'TOGGLE']);

try {
    if ($action === 'LOAD') {
        $rows = magx_db_execute($db, 'SELECT * FROM tbl_services ORDER BY display_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    if ($action === 'GET') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT * FROM tbl_services WHERE id = :id LIMIT 1', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Service not found']);
        exit;
    }

    if ($action === 'ADD' || $action === 'EDIT') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $icon_class = trim((string)($_POST['icon_class'] ?? ''));
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 1);

        if ($title === '' || $description === '') {
            echo json_encode(['success' => false, 'message' => 'Title and description are required']);
            exit;
        }

        if ($action === 'ADD') {
            magx_db_execute($db, 'INSERT INTO tbl_services (title, description, icon_class, display_order, is_active, date_created, date_updated) VALUES (:t, :d, :i, :o, :a, NOW(), NOW())', [
                ':t' => $title, ':d' => $description, ':i' => $icon_class, ':o' => $display_order, ':a' => $is_active
            ]);
            echo json_encode(['success' => true, 'message' => 'Service added']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        magx_db_execute($db, 'UPDATE tbl_services SET title=:t, description=:d, icon_class=:i, display_order=:o, is_active=:a, date_updated=NOW() WHERE id=:id', [
            ':t' => $title, ':d' => $description, ':i' => $icon_class, ':o' => $display_order, ':a' => $is_active, ':id' => $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Service updated']);
        exit;
    }

    if ($action === 'DELETE') {
        $id = (int)($_POST['id'] ?? 0);
        magx_db_execute($db, 'DELETE FROM tbl_services WHERE id = :id', [':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Service deleted']);
        exit;
    }

    if ($action === 'TOGGLE') {
        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);
        magx_db_execute($db, 'UPDATE tbl_services SET is_active = :a, date_updated=NOW() WHERE id = :id', [':a' => $is_active, ':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Request failed']);
}
