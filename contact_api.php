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
magx_require_admin_for_action($action, ['ADD', 'EDIT', 'DELETE', 'GET_CONTACT', 'TOGGLE_STATUS', 'LOAD']);

function uploadContactFile($file, $uploadDir = 'uploads/contacts/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) { return null; }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) { return null; }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    if (!isset($allowed[$mime])) { return null; }
    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
    $seed = bin2hex(random_bytes(12));
    $filename = $seed . '_' . time() . '.' . $allowed[$mime];
    $path = $uploadDir . $filename;
    return move_uploaded_file($file['tmp_name'], $path) ? $filename : null;
}

try {
    if ($action === 'LOAD') {
        $rows = magx_db_execute($db, 'SELECT * FROM tbl_contacts ORDER BY display_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    if ($action === 'GET_CONTACT') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT * FROM tbl_contacts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['picture']) && file_exists('uploads/contacts/' . $row['picture'])) {
            $row['picture'] = 'uploads/contacts/' . $row['picture'];
        }
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Contact not found']);
        exit;
    }

    if ($action === 'ADD') {
        $name = trim((string)($_POST['name'] ?? ''));
        $position = trim((string)($_POST['position'] ?? ''));
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 1);
        $picture = uploadContactFile($_FILES['picture'] ?? null) ?: '';

        magx_db_execute($db, 'INSERT INTO tbl_contacts (name, position, picture, display_order, is_active, date_created, date_updated) VALUES (:n,:p,:pic,:o,:a,NOW(),NOW())', [
            ':n'=>$name, ':p'=>$position, ':pic'=>$picture, ':o'=>$display_order, ':a'=>$is_active
        ]);
        echo json_encode(['success' => true, 'message' => 'Contact added successfully']);
        exit;
    }

    if ($action === 'EDIT') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $position = trim((string)($_POST['position'] ?? ''));
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 1);

        $current = magx_db_execute($db, 'SELECT picture FROM tbl_contacts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        $picture = $current['picture'] ?? '';

        $newPicture = uploadContactFile($_FILES['picture'] ?? null);
        if ($newPicture) {
            if ($picture && file_exists('uploads/contacts/' . $picture)) { @unlink('uploads/contacts/' . $picture); }
            $picture = $newPicture;
        } elseif (isset($_POST['existing_picture'])) {
            $picture = (string)$_POST['existing_picture'];
        }

        magx_db_execute($db, 'UPDATE tbl_contacts SET name=:n, position=:p, picture=:pic, display_order=:o, is_active=:a, date_updated=NOW() WHERE id=:id', [
            ':n'=>$name, ':p'=>$position, ':pic'=>$picture, ':o'=>$display_order, ':a'=>$is_active, ':id'=>$id
        ]);
        echo json_encode(['success' => true, 'message' => 'Contact updated successfully']);
        exit;
    }

    if ($action === 'DELETE') {
        $id = (int)($_POST['id'] ?? 0);
        $current = magx_db_execute($db, 'SELECT picture FROM tbl_contacts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        if ($current && !empty($current['picture']) && file_exists('uploads/contacts/' . $current['picture'])) {
            @unlink('uploads/contacts/' . $current['picture']);
        }
        magx_db_execute($db, 'DELETE FROM tbl_contacts WHERE id = :id', [':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Contact deleted successfully']);
        exit;
    }

    if ($action === 'TOGGLE_STATUS') {
        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);
        magx_db_execute($db, 'UPDATE tbl_contacts SET is_active = :a, date_updated = NOW() WHERE id = :id', [':a' => $is_active, ':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Request failed']);
}
