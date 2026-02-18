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
magx_require_admin_for_action($action, ['LOAD', 'ADD', 'EDIT', 'DELETE', 'GET_POST', 'TOGGLE_STATUS']);

function hp_upload_media($file, string $kind, &$error = null, string $uploadDir = 'uploads/home_posts/') {
    $error = null;

    if (!isset($file) || !is_array($file) || !isset($file['error'])) {
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed (error code ' . (int)$file['error'] . ').';
        return null;
    }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload source.';
        return null;
    }

    // Vercel serverless request body limits are much smaller than local PHP limits.
    $isVercel = !empty($_SERVER['VERCEL']) || getenv('VERCEL');
    $maxBytes = ($kind === 'video')
        ? ($isVercel ? (4 * 1024 * 1024) : (50 * 1024 * 1024))
        : ($isVercel ? (4 * 1024 * 1024) : (5 * 1024 * 1024));
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        $error = 'Uploaded file is empty.';
        return null;
    }
    if ($size > $maxBytes) {
        $error = ($kind === 'video')
            ? ('Video too large (max ' . ($isVercel ? '4MB on Vercel' : '50MB') . ').')
            : ('Image too large (max ' . ($isVercel ? '4MB on Vercel' : '5MB') . ').');
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    $allowed = $kind === 'video'
        ? ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogv']
        : ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    if (!isset($allowed[$mime])) {
        $error = ($kind === 'video')
            ? 'Unsupported video type. Allowed: MP4, WebM, OGG.'
            : 'Unsupported image type. Allowed: JPG, PNG, WebP, GIF.';
        return null;
    }

    $uploadRoot = __DIR__ . '/' . trim($uploadDir, '/\\') . '/';
    if (!is_dir($uploadRoot)) {
        @mkdir($uploadRoot, 0755, true);
    }
    if (!is_dir($uploadRoot) || !is_writable($uploadRoot)) {
        // Vercel/serverless deployments usually have read-only app filesystem.
        $error = 'MEDIA_STORAGE_UNAVAILABLE';
        return null;
    }

    try {
        $rand = bin2hex(random_bytes(16));
    } catch (Throwable $e) {
        $rand = uniqid('hp_', true);
    }

    $filename = $rand . '_' . time() . '.' . $allowed[$mime];
    $path = $uploadRoot . $filename;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        $error = 'MEDIA_STORAGE_UNAVAILABLE';
        return null;
    }

    return $filename;
}

function hp_media_public_url(?string $value): string {
    $v = trim((string)$value);
    if ($v === '') { return ''; }
    if (preg_match('#^(https?:)?//#i', $v) || strpos($v, 'data:') === 0) { return $v; }
    if (strpos($v, 'uploads/home_posts/') === 0) { return $v; }
    return 'uploads/home_posts/' . ltrim($v, '/');
}

function hp_is_valid_external_url(?string $value): bool {
    $v = trim((string)$value);
    if ($v === '') { return false; }
    return (bool)filter_var($v, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $v);
}

function hp_remove_file(?string $filename): void {
    if (!$filename) { return; }
    $base = basename((string)$filename);
    $path = __DIR__ . '/uploads/home_posts/' . $base;
    if (file_exists($path)) {
        @unlink($path);
    }
}

try {
    switch ($action) {
        case 'LOAD': {
            $rows = magx_db_execute($db, 'SELECT * FROM tbl_home_posts ORDER BY display_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows ?: []]);
            break;
        }

        case 'GET_POST': {
            $id = (int)($_POST['id'] ?? 0);
            $row = magx_db_execute($db, 'SELECT * FROM tbl_home_posts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['icon_image'] = hp_media_public_url($row['icon_image'] ?? '');
                $row['background_image'] = hp_media_public_url($row['background_image'] ?? '');
                $row['background_video'] = hp_media_public_url($row['background_video'] ?? '');
                echo json_encode(['success' => true, 'data' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Post not found']);
            }
            break;
        }

        case 'ADD': {
            $title = trim((string)($_POST['title'] ?? ''));
            $subtitle = trim((string)($_POST['subtitle'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $like = max(0, (int)($_POST['like_count'] ?? 0));
            $comment = max(0, (int)($_POST['comment_count'] ?? 0));
            $share = max(0, (int)($_POST['share_count'] ?? 0));
            $order = (int)($_POST['display_order'] ?? 0);
            $active = (int)($_POST['is_active'] ?? 1);

            $iconErr = null; $bgErr = null; $vidErr = null;
            $warnings = [];
            $icon = hp_upload_media($_FILES['icon_image'] ?? null, 'image', $iconErr) ?: '';
            if ($iconErr && $iconErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$iconErr]); break; }
            if ($iconErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Icon upload skipped (server storage unavailable).'; }
            $bg = hp_upload_media($_FILES['background_image'] ?? null, 'image', $bgErr) ?: '';
            if ($bgErr && $bgErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$bgErr]); break; }
            if ($bgErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Background image upload skipped (server storage unavailable).'; }
            $vid = hp_upload_media($_FILES['background_video'] ?? null, 'video', $vidErr) ?: '';
            if ($vidErr && $vidErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$vidErr]); break; }
            if ($vidErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Background video upload skipped (server storage unavailable).'; }
            $videoUrl = trim((string)($_POST['background_video_url'] ?? ''));
            if ($videoUrl !== '') {
                if (!hp_is_valid_external_url($videoUrl)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid background video URL']);
                    break;
                }
                $vid = $videoUrl;
            }

            magx_db_execute($db, 'INSERT INTO tbl_home_posts (title, subtitle, description, icon_image, background_image, background_video, like_count, comment_count, share_count, display_order, is_active, date_created, date_updated) VALUES (:t,:s,:d,:i,:b,:v,:l,:c,:sh,:o,:a,NOW(),NOW())', [
                ':t'=>$title, ':s'=>$subtitle, ':d'=>$description, ':i'=>$icon, ':b'=>$bg, ':v'=>$vid,
                ':l'=>$like, ':c'=>$comment, ':sh'=>$share, ':o'=>$order, ':a'=>$active
            ]);
            $msg = 'Post added successfully';
            if (!empty($warnings)) { $msg .= ' ' . implode(' ', $warnings); }
            echo json_encode(['success' => true, 'message' => $msg]);
            break;
        }

        case 'EDIT': {
            $id = (int)($_POST['id'] ?? 0);
            $title = trim((string)($_POST['title'] ?? ''));
            $subtitle = trim((string)($_POST['subtitle'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $like = max(0, (int)($_POST['like_count'] ?? 0));
            $comment = max(0, (int)($_POST['comment_count'] ?? 0));
            $share = max(0, (int)($_POST['share_count'] ?? 0));
            $order = (int)($_POST['display_order'] ?? 0);
            $active = (int)($_POST['is_active'] ?? 1);

            $current = magx_db_execute($db, 'SELECT icon_image, background_image, background_video FROM tbl_home_posts WHERE id = :id', [':id'=>$id])->fetch(PDO::FETCH_ASSOC) ?: [];
            $icon = (string)($current['icon_image'] ?? '');
            $bg = (string)($current['background_image'] ?? '');
            $vid = (string)($current['background_video'] ?? '');

            $iconErr = null; $bgErr = null; $vidErr = null;
            $warnings = [];
            $newIcon = hp_upload_media($_FILES['icon_image'] ?? null, 'image', $iconErr);
            if ($iconErr && $iconErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$iconErr]); break; }
            if ($iconErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Icon upload skipped (server storage unavailable).'; }
            if ($newIcon) { hp_remove_file($icon); $icon = $newIcon; }
            elseif (isset($_POST['existing_icon_image'])) { $icon = (string)$_POST['existing_icon_image']; }

            $newBg = hp_upload_media($_FILES['background_image'] ?? null, 'image', $bgErr);
            if ($bgErr && $bgErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$bgErr]); break; }
            if ($bgErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Background image upload skipped (server storage unavailable).'; }
            if ($newBg) { hp_remove_file($bg); $bg = $newBg; }
            elseif (isset($_POST['existing_background_image'])) { $bg = (string)$_POST['existing_background_image']; }

            $newVid = hp_upload_media($_FILES['background_video'] ?? null, 'video', $vidErr);
            if ($vidErr && $vidErr !== 'MEDIA_STORAGE_UNAVAILABLE') { echo json_encode(['success'=>false,'message'=>$vidErr]); break; }
            if ($vidErr === 'MEDIA_STORAGE_UNAVAILABLE') { $warnings[] = 'Background video upload skipped (server storage unavailable).'; }
            $videoUrl = trim((string)($_POST['background_video_url'] ?? ''));
            if ($newVid) { hp_remove_file($vid); $vid = $newVid; }
            elseif ($videoUrl !== '') {
                if (!hp_is_valid_external_url($videoUrl)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid background video URL']);
                    break;
                }
                hp_remove_file($vid);
                $vid = $videoUrl;
            }
            elseif (isset($_POST['existing_background_video'])) { $vid = (string)$_POST['existing_background_video']; }

            magx_db_execute($db, 'UPDATE tbl_home_posts SET title=:t, subtitle=:s, description=:d, icon_image=:i, background_image=:b, background_video=:v, like_count=:l, comment_count=:c, share_count=:sh, display_order=:o, is_active=:a, date_updated=NOW() WHERE id=:id', [
                ':t'=>$title, ':s'=>$subtitle, ':d'=>$description, ':i'=>$icon, ':b'=>$bg, ':v'=>$vid,
                ':l'=>$like, ':c'=>$comment, ':sh'=>$share, ':o'=>$order, ':a'=>$active, ':id'=>$id
            ]);
            $msg = 'Post updated successfully';
            if (!empty($warnings)) { $msg .= ' ' . implode(' ', $warnings); }
            echo json_encode(['success' => true, 'message' => $msg]);
            break;
        }

        case 'DELETE': {
            $id = (int)($_POST['id'] ?? 0);
            $row = magx_db_execute($db, 'SELECT icon_image, background_image, background_video FROM tbl_home_posts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                hp_remove_file($row['icon_image'] ?? null);
                hp_remove_file($row['background_image'] ?? null);
                hp_remove_file($row['background_video'] ?? null);
            }
            magx_db_execute($db, 'DELETE FROM tbl_home_posts WHERE id = :id', [':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
            break;
        }

        case 'TOGGLE_STATUS': {
            $id = (int)($_POST['id'] ?? 0);
            $active = (int)($_POST['is_active'] ?? 0);
            magx_db_execute($db, 'UPDATE tbl_home_posts SET is_active = :a, date_updated = NOW() WHERE id = :id', [':a' => $active, ':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            break;
        }

        case 'ENGAGE': {
            $id = (int)($_POST['id'] ?? 0);
            $kind = strtolower(trim((string)($_POST['kind'] ?? '')));
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid post id']);
                break;
            }

            if ($kind === 'like') {
                $device = trim((string)($_POST['device_id'] ?? ''));
                if ($device === '' || strlen($device) > 128 || !preg_match('/^[A-Za-z0-9_-]{8,128}$/', $device)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid device id']);
                    break;
                }

                magx_db_execute($db, 'INSERT INTO tbl_home_post_likes (post_id, device_id, created_at) VALUES (:id, :d, NOW()) ON CONFLICT (post_id, device_id) DO NOTHING', [
                    ':id' => $id, ':d' => $device
                ]);

                $liked = false;
                $chk = magx_db_execute($db, 'SELECT EXISTS (SELECT 1 FROM tbl_home_post_likes WHERE post_id = :id AND device_id = :d) AS ex', [':id'=>$id, ':d'=>$device])->fetch(PDO::FETCH_ASSOC);
                $liked = !empty($chk['ex']);

                if ($liked) {
                    $cnt = magx_db_execute($db, 'SELECT COUNT(*)::int AS c FROM tbl_home_post_likes WHERE post_id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
                    $count = (int)($cnt['c'] ?? 0);
                    magx_db_execute($db, 'UPDATE tbl_home_posts SET like_count = :c, date_updated = NOW() WHERE id = :id', [':c'=>$count, ':id'=>$id]);
                    echo json_encode(['success'=>true,'count'=>$count,'liked'=>true,'already_liked'=>false]);
                } else {
                    $row = magx_db_execute($db, 'SELECT like_count AS c FROM tbl_home_posts WHERE id = :id', [':id'=>$id])->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(['success'=>true,'count'=>(int)($row['c'] ?? 0),'liked'=>false,'already_liked'=>true]);
                }
                break;
            }

            if ($kind !== 'share' && $kind !== 'comment') {
                echo json_encode(['success' => false, 'message' => 'Invalid kind']);
                break;
            }
            $col = $kind === 'share' ? 'share_count' : 'comment_count';
            magx_db_execute($db, "UPDATE tbl_home_posts SET {$col} = {$col} + 1, date_updated = NOW() WHERE id = :id", [':id' => $id]);
            $row = magx_db_execute($db, "SELECT {$col} AS c FROM tbl_home_posts WHERE id = :id", [':id' => $id])->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)($row['c'] ?? 0)]);
            break;
        }

        case 'ADD_COMMENT': {
            $id = (int)($_POST['id'] ?? 0);
            $comment = trim((string)($_POST['comment'] ?? ''));
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid post id']);
                break;
            }
            if ($comment === '') {
                echo json_encode(['success' => false, 'message' => 'Comment is required']);
                break;
            }
            if (strlen($comment) > 1000) {
                $comment = substr($comment, 0, 1000);
            }
            $author = !empty($_SESSION['username']) ? (string)$_SESSION['username'] : 'Guest';

            magx_db_execute($db, 'INSERT INTO tbl_home_post_comments (post_id, author_name, comment_text, created_at) VALUES (:id,:a,:c,NOW())', [
                ':id' => $id, ':a' => $author, ':c' => $comment
            ]);
            magx_db_execute($db, 'UPDATE tbl_home_posts SET comment_count = comment_count + 1, date_updated = NOW() WHERE id = :id', [':id' => $id]);
            $row = magx_db_execute($db, 'SELECT comment_count AS c FROM tbl_home_posts WHERE id = :id', [':id' => $id])->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)($row['c'] ?? 0)]);
            break;
        }

        case 'LIST_COMMENTS': {
            $id = (int)($_POST['id'] ?? 0);
            $limit = (int)($_POST['limit'] ?? 20);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid post id']);
                break;
            }
            if ($limit <= 0 || $limit > 100) {
                $limit = 20;
            }
            $rows = magx_db_execute($db, 'SELECT author_name, comment_text, created_at FROM tbl_home_post_comments WHERE post_id = :id ORDER BY created_at DESC, id DESC LIMIT ' . (int)$limit, [':id' => $id])->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows ?: []]);
            break;
        }

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Request failed']);
}
