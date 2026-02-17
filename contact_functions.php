<?php
require_once __DIR__ . '/app_bootstrap.php';

function loadContactsForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return [];
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_contacts WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($contacts as &$row) {
            if (!empty($row['picture']) && file_exists('uploads/contacts/' . $row['picture'])) {
                $row['picture'] = 'uploads/contacts/' . $row['picture'];
            }
        }
        return $contacts;
    } catch (Throwable $e) {
        return [];
    }
}
