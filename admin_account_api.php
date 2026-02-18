<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/app_bootstrap.php';
magx_send_security_headers();

if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Method not allowed';
    exit;
}

if (!magx_is_admin_authenticated()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Authentication required.';
    exit;
}

$db = magx_db_connect();
if (!$db) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Database connection failed.';
    exit;
}

$action = strtoupper(trim((string)($_POST['action'] ?? '')));

if ($action === 'CHANGE_PASS_USER') {
    $currentUsername = trim((string)($_POST['current_username'] ?? ''));
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newUsername = trim((string)($_POST['new_username'] ?? ''));
    $newPassword = (string)($_POST['new_password'] ?? '');

    if ($currentUsername === '' || $currentPassword === '' || $newUsername === '' || $newPassword === '') {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'All fields are required.';
        exit;
    }

    try {
        $row = magx_db_execute(
            $db,
            'SELECT id, adminpass FROM tbl_acc WHERE adminuser = :u LIMIT 1',
            [':u' => $currentUsername]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Current username or password is incorrect.';
            exit;
        }

        $stored = (string)($row['adminpass'] ?? '');
        if (!hash_equals($stored, $currentPassword)) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Current username or password is incorrect.';
            exit;
        }

        magx_db_execute(
            $db,
            'UPDATE tbl_acc SET adminuser = :nu, adminpass = :np WHERE id = :id',
            [
                ':nu' => $newUsername,
                ':np' => $newPassword,
                ':id' => (int)$row['id'],
            ]
        );

        // Keep active session aligned with updated username.
        $_SESSION['adminuser'] = $newUsername;

        header('Content-Type: text/plain; charset=utf-8');
        echo 'Username and password updated successfully!';
        exit;
    } catch (Throwable $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Error updating username and password!';
        exit;
    }
}

if ($action === 'ADD_ADMIN_ACCOUNT') {
    $adminUsername = trim((string)($_POST['admin_username'] ?? ''));
    $adminPassword = (string)($_POST['admin_password'] ?? '');

    if ($adminUsername === '' || $adminPassword === '') {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Username and password are required.';
        exit;
    }

    try {
        $exists = magx_db_execute(
            $db,
            'SELECT 1 FROM tbl_acc WHERE adminuser = :u LIMIT 1',
            [':u' => $adminUsername]
        )->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Admin username already exists.';
            exit;
        }

        magx_db_execute(
            $db,
            'INSERT INTO tbl_acc (adminuser, adminpass, created_at) VALUES (:u, :p, NOW())',
            [
                ':u' => $adminUsername,
                ':p' => $adminPassword,
            ]
        );

        header('Content-Type: text/plain; charset=utf-8');
        echo 'Admin account added successfully.';
        exit;
    } catch (Throwable $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Failed to add admin account.';
        exit;
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo 'Invalid action';
