<?php
require_once __DIR__ . '/app_bootstrap.php';

function loadHomePostsForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return [];
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_home_posts WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($posts as &$row) {
            if (!empty($row['icon_image']) && file_exists('uploads/home_posts/' . $row['icon_image'])) {
                $row['icon_image'] = 'uploads/home_posts/' . $row['icon_image'];
            }
            if (!empty($row['background_image']) && file_exists('uploads/home_posts/' . $row['background_image'])) {
                $row['background_image'] = 'uploads/home_posts/' . $row['background_image'];
            }
            if (!empty($row['background_video']) && file_exists('uploads/home_posts/' . $row['background_video'])) {
                $row['background_video'] = 'uploads/home_posts/' . $row['background_video'];
            }
        }
        return $posts;
    } catch (Throwable $e) {
        return [];
    }
}
