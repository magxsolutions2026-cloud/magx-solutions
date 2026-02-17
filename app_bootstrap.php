<?php

if (!function_exists('magx_send_security_headers')) {
    function magx_send_security_headers(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}

if (!function_exists('magx_db_connect')) {
    function magx_db_connect(?string $dbName = null)
    {
        $host = getenv('DB_HOST') ?: getenv('SUPABASE_DB_HOST') ?: '';
        $port = getenv('DB_PORT') ?: getenv('SUPABASE_DB_PORT') ?: '5432';
        $user = getenv('DB_USER') ?: getenv('SUPABASE_DB_USER') ?: '';
        $pass = getenv('DB_PASS') ?: getenv('SUPABASE_DB_PASSWORD') ?: '';
        $name = $dbName ?: (getenv('DB_NAME') ?: getenv('SUPABASE_DB_NAME') ?: 'postgres');
        $sslmode = getenv('DB_SSLMODE') ?: getenv('SUPABASE_DB_SSLMODE') ?: 'require';

        if ($host === '' || $user === '') {
            return null;
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$sslmode}";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('magx_db_execute')) {
    function magx_db_execute(PDO $db, string $sql, array $params = []): PDOStatement
    {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

if (!function_exists('magx_require_post_request')) {
    function magx_require_post_request(): void
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'POST') {
            if (!headers_sent()) {
                http_response_code(405);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
    }
}

if (!function_exists('magx_is_admin_authenticated')) {
    function magx_is_admin_authenticated(): bool
    {
        return !empty($_SESSION['magx_admin_authenticated'])
            || !empty($_SESSION['adminuser'])
            || !empty($_SESSION['username']);
    }
}

if (!function_exists('magx_require_admin_for_action')) {
    function magx_require_admin_for_action(string $action, array $restrictedActions): void
    {
        if (in_array($action, $restrictedActions, true) && !magx_is_admin_authenticated()) {
            if (!headers_sent()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }
    }
}

if (!function_exists('magx_valid_csrf')) {
    function magx_valid_csrf(string $token): bool
    {
        $sessionToken = isset($_SESSION['magx_csrf']) ? (string)$_SESSION['magx_csrf'] : '';
        return ($sessionToken !== '') && hash_equals($sessionToken, $token);
    }
}
