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

$uploadDir = __DIR__ . '/uploads/portfolio_items/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$action = strtoupper(trim((string)($_POST['action'] ?? '')));
magx_require_admin_for_action($action, ['ADD', 'EDIT', 'DELETE', 'TOGGLE', 'GET', 'LOAD']);

function uploadPortfolioFile($fieldName, $uploadDir) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if (!isset($_FILES[$fieldName]['tmp_name']) || !is_uploaded_file($_FILES[$fieldName]['tmp_name'])) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($_FILES[$fieldName]['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    if (!isset($allowed[$mime])) { return null; }
    $seed = bin2hex(random_bytes(10));
    $safe = $seed . '_' . time() . '.' . $allowed[$mime];
    $target = $uploadDir . $safe;
    return move_uploaded_file($_FILES[$fieldName]['tmp_name'], $target) ? $safe : null;
}

try {
    if ($action === 'LOAD') {
        $rows = magx_db_execute($db, 'SELECT * FROM tbl_portfolio_items ORDER BY display_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            if (!empty($row['image']) && file_exists($uploadDir . $row['image'])) {
                $row['image'] = 'uploads/portfolio_items/' . $row['image'];
            }
        }
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    if ($action === 'GET') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT * FROM tbl_portfolio_items WHERE id = :id LIMIT 1', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['image']) && file_exists($uploadDir . $row['image'])) {
            $row['image'] = 'uploads/portfolio_items/' . $row['image'];
        }
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Portfolio item not found']);
        exit;
    }

    if ($action === 'ADD' || $action === 'EDIT') {
        $title = trim((string)($_POST['title'] ?? ''));
        $subtitle = trim((string)($_POST['subtitle'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $tags = trim((string)($_POST['tags'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $year = trim((string)($_POST['year'] ?? ''));
        $link = trim((string)($_POST['link'] ?? ''));
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 1);

        if ($title === '' || $description === '') {
            echo json_encode(['success' => false, 'message' => 'Title and description are required']);
            exit;
        }

        $newImage = uploadPortfolioFile('image', $uploadDir);

        if ($action === 'ADD') {
            magx_db_execute($db, 'INSERT INTO tbl_portfolio_items (title, subtitle, description, tags, category, year, link, image, display_order, is_active, date_created, date_updated) VALUES (:t,:s,:d,:tags,:c,:y,:l,:img,:o,:a,NOW(),NOW())', [
                ':t'=>$title, ':s'=>$subtitle, ':d'=>$description, ':tags'=>$tags, ':c'=>$category, ':y'=>$year, ':l'=>$link, ':img'=>$newImage, ':o'=>$display_order, ':a'=>$is_active
            ]);
            echo json_encode(['success' => true, 'message' => 'Portfolio item added']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $existingImage = trim((string)($_POST['existing_image'] ?? ''));
        $finalImage = $existingImage;
        if ($newImage) {
            if ($existingImage && file_exists($uploadDir . $existingImage)) { @unlink($uploadDir . $existingImage); }
            $finalImage = $newImage;
        }

        magx_db_execute($db, 'UPDATE tbl_portfolio_items SET title=:t, subtitle=:s, description=:d, tags=:tags, category=:c, year=:y, link=:l, image=:img, display_order=:o, is_active=:a, date_updated=NOW() WHERE id=:id', [
            ':t'=>$title, ':s'=>$subtitle, ':d'=>$description, ':tags'=>$tags, ':c'=>$category, ':y'=>$year, ':l'=>$link, ':img'=>$finalImage !== '' ? $finalImage : null, ':o'=>$display_order, ':a'=>$is_active, ':id'=>$id
        ]);
        echo json_encode(['success' => true, 'message' => 'Portfolio item updated']);
        exit;
    }

    if ($action === 'DELETE') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT image FROM tbl_portfolio_items WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['image']) && file_exists($uploadDir . $row['image'])) {
            @unlink($uploadDir . $row['image']);
        }
        magx_db_execute($db, 'DELETE FROM tbl_portfolio_items WHERE id = :id', [':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Portfolio item deleted']);
        exit;
    }

    if ($action === 'TOGGLE') {
        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);
        magx_db_execute($db, 'UPDATE tbl_portfolio_items SET is_active = :a, date_updated=NOW() WHERE id = :id', [':a' => $is_active, ':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Request failed']);
}
