<?php
require_once __DIR__ . '/app_bootstrap.php';

function loadPortfolioItemsForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return [];
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_portfolio_items WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($items as &$row) {
            if (!empty($row['image']) && file_exists('uploads/portfolio_items/' . $row['image'])) {
                $row['image'] = 'uploads/portfolio_items/' . $row['image'];
            }
        }
        return $items;
    } catch (Throwable $e) {
        return [];
    }
}
