<?php
require_once __DIR__ . '/app_bootstrap.php';

function loadServicesForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return [];
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_services WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}
