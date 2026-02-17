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
magx_require_admin_for_action($action, ['ADD', 'EDIT', 'DELETE', 'GET_FOOTER_CONTACT', 'LOAD']);

function uploadFooterFile($file, $uploadDir = 'uploads/footer_contacts/') {
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
        $row = magx_db_execute($db, 'SELECT * FROM tbl_footer_contacts WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $row = magx_db_execute($db, 'SELECT * FROM tbl_footer_contacts ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        }
        if ($row) {
            if (!empty($row['logo']) && file_exists('uploads/footer_contacts/' . $row['logo'])) {
                $row['logo'] = 'uploads/footer_contacts/' . $row['logo'];
            }
            if (!empty($row['qr_code']) && file_exists('uploads/footer_contacts/' . $row['qr_code'])) {
                $row['qr_code'] = 'uploads/footer_contacts/' . $row['qr_code'];
            }
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No footer contact found']);
        }
        exit;
    }

    if ($action === 'GET_FOOTER_CONTACT') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT * FROM tbl_footer_contacts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['logo']) && file_exists('uploads/footer_contacts/' . $row['logo'])) {
            $row['logo'] = 'uploads/footer_contacts/' . $row['logo'];
        }
        if ($row && !empty($row['qr_code']) && file_exists('uploads/footer_contacts/' . $row['qr_code'])) {
            $row['qr_code'] = 'uploads/footer_contacts/' . $row['qr_code'];
        }
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Footer contact not found']);
        exit;
    }

    if ($action === 'ADD') {
        $facebook_link = trim((string)($_POST['facebook_link'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $is_active = (int)($_POST['is_active'] ?? 1);
        $logo = uploadFooterFile($_FILES['logo'] ?? null) ?: '';
        $qr = uploadFooterFile($_FILES['qr_code'] ?? null) ?: '';

        magx_db_execute($db, 'UPDATE tbl_footer_contacts SET is_active = 0');
        magx_db_execute($db, 'INSERT INTO tbl_footer_contacts (logo, facebook_link, phone, location, qr_code, is_active, date_created, date_updated) VALUES (:l,:f,:p,:loc,:q,:a,NOW(),NOW())', [
            ':l'=>$logo, ':f'=>$facebook_link, ':p'=>$phone, ':loc'=>$location, ':q'=>$qr, ':a'=>$is_active
        ]);
        echo json_encode(['success' => true, 'message' => 'Footer contact added successfully']);
        exit;
    }

    if ($action === 'EDIT') {
        $id = (int)($_POST['id'] ?? 0);
        $facebook_link = trim((string)($_POST['facebook_link'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $is_active = (int)($_POST['is_active'] ?? 1);

        $current = magx_db_execute($db, 'SELECT logo, qr_code FROM tbl_footer_contacts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC) ?: [];
        $logo = $current['logo'] ?? '';
        $qr = $current['qr_code'] ?? '';

        $newLogo = uploadFooterFile($_FILES['logo'] ?? null);
        if ($newLogo) {
            if ($logo && file_exists('uploads/footer_contacts/' . $logo)) { @unlink('uploads/footer_contacts/' . $logo); }
            $logo = $newLogo;
        } elseif (isset($_POST['existing_logo'])) {
            $logo = (string)$_POST['existing_logo'];
        }

        $newQr = uploadFooterFile($_FILES['qr_code'] ?? null);
        if ($newQr) {
            if ($qr && file_exists('uploads/footer_contacts/' . $qr)) { @unlink('uploads/footer_contacts/' . $qr); }
            $qr = $newQr;
        } elseif (isset($_POST['existing_qr_code'])) {
            $qr = (string)$_POST['existing_qr_code'];
        }

        magx_db_execute($db, 'UPDATE tbl_footer_contacts SET logo=:l, facebook_link=:f, phone=:p, location=:loc, qr_code=:q, is_active=:a, date_updated=NOW() WHERE id=:id', [
            ':l'=>$logo, ':f'=>$facebook_link, ':p'=>$phone, ':loc'=>$location, ':q'=>$qr, ':a'=>$is_active, ':id'=>$id
        ]);
        echo json_encode(['success' => true, 'message' => 'Footer contact updated successfully']);
        exit;
    }

    if ($action === 'DELETE') {
        $id = (int)($_POST['id'] ?? 0);
        $row = magx_db_execute($db, 'SELECT logo, qr_code FROM tbl_footer_contacts WHERE id = :id', [':id'=>$id])->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (!empty($row['logo']) && file_exists('uploads/footer_contacts/' . $row['logo'])) { @unlink('uploads/footer_contacts/' . $row['logo']); }
            if (!empty($row['qr_code']) && file_exists('uploads/footer_contacts/' . $row['qr_code'])) { @unlink('uploads/footer_contacts/' . $row['qr_code']); }
        }
        magx_db_execute($db, 'DELETE FROM tbl_footer_contacts WHERE id = :id', [':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Footer contact deleted successfully']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Request failed']);
}
