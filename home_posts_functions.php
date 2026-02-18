<?php
require_once __DIR__ . '/app_bootstrap.php';

function hp_public_media_url(?string $value): string {
    $v = trim((string)$value);
    if ($v === '') { return ''; }
    if (preg_match('#^(https?:)?//#i', $v) || strpos($v, 'data:') === 0) { return $v; }
    if (strpos($v, 'uploads/home_posts/') === 0) { return $v; }
    return 'uploads/home_posts/' . ltrim($v, '/');
}

function loadHomePostsForDisplay() {
    $db = magx_db_connect();
    if (!$db) {
        return [];
    }

    try {
        $stmt = magx_db_execute($db, 'SELECT * FROM tbl_home_posts WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($posts as &$row) {
            $row['icon_image'] = hp_public_media_url($row['icon_image'] ?? '');
            $row['background_image'] = hp_public_media_url($row['background_image'] ?? '');
            $row['background_video'] = hp_public_media_url($row['background_video'] ?? '');
        }
        return $posts;
    } catch (Throwable $e) {
        return [];
    }
}
