<?php
require_once __DIR__ . '/app_bootstrap.php';

function loadFooterContactForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return null;
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_footer_contacts WHERE is_active = 1 ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $stmt = magx_db_execute($db, 'SELECT * FROM tbl_footer_contacts ORDER BY id DESC LIMIT 1');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$row) {
            return null;
        }
        if (!empty($row['logo']) && file_exists('uploads/footer_contacts/' . $row['logo'])) {
            $row['logo'] = 'uploads/footer_contacts/' . $row['logo'];
        }
        if (!empty($row['qr_code']) && file_exists('uploads/footer_contacts/' . $row['qr_code'])) {
            $row['qr_code'] = 'uploads/footer_contacts/' . $row['qr_code'];
        }
        return $row;
    } catch (Throwable $e) {
        return null;
    }
}
