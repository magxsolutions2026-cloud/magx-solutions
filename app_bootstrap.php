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

if (!function_exists('magx_signing_secret')) {
    function magx_signing_secret(): string
    {
        $secret = (string)(getenv('APP_KEY') ?: getenv('MAGX_APP_KEY') ?: getenv('DB_PASS') ?: getenv('SUPABASE_DB_PASSWORD') ?: '');
        if ($secret === '') {
            // Dev fallback only; set APP_KEY in production for stronger signing.
            $secret = 'magx-default-signing-key-change-me';
        }
        return $secret;
    }
}

if (!function_exists('magx_issue_admin_cookie')) {
    function magx_issue_admin_cookie(string $username): void
    {
        if ($username === '') {
            return;
        }
        $exp = time() + (60 * 60 * 12); // 12 hours
        $payload = base64_encode(json_encode(['u' => $username, 'e' => $exp], JSON_UNESCAPED_SLASHES));
        $sig = hash_hmac('sha256', $payload, magx_signing_secret());
        $value = $payload . '.' . $sig;
        setcookie('magx_admin_auth', $value, [
            'expires' => $exp,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (!function_exists('magx_read_admin_cookie')) {
    function magx_read_admin_cookie(): ?string
    {
        $raw = (string)($_COOKIE['magx_admin_auth'] ?? '');
        if ($raw === '' || strpos($raw, '.') === false) {
            return null;
        }
        [$payload, $sig] = explode('.', $raw, 2);
        $expected = hash_hmac('sha256', $payload, magx_signing_secret());
        if (!hash_equals($expected, (string)$sig)) {
            return null;
        }
        $decoded = json_decode((string)base64_decode($payload, true), true);
        if (!is_array($decoded)) {
            return null;
        }
        $exp = (int)($decoded['e'] ?? 0);
        $username = trim((string)($decoded['u'] ?? ''));
        if ($username === '' || $exp <= time()) {
            return null;
        }
        return $username;
    }
}

if (!function_exists('magx_clear_admin_cookie')) {
    function magx_clear_admin_cookie(): void
    {
        setcookie('magx_admin_auth', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (!function_exists('magx_db_connect')) {
    function magx_db_connect(?string $dbName = null)
    {
        $databaseUrl = getenv('DATABASE_URL') ?: getenv('SUPABASE_DB_URL') ?: '';

        $host = getenv('DB_HOST') ?: getenv('SUPABASE_DB_HOST') ?: getenv('PGHOST') ?: '';
        $port = getenv('DB_PORT') ?: getenv('SUPABASE_DB_PORT') ?: getenv('PGPORT') ?: '5432';
        $user = getenv('DB_USER') ?: getenv('SUPABASE_DB_USER') ?: getenv('PGUSER') ?: '';
        $pass = getenv('DB_PASS') ?: getenv('SUPABASE_DB_PASSWORD') ?: getenv('PGPASSWORD') ?: '';
        $name = $dbName ?: (getenv('DB_NAME') ?: getenv('SUPABASE_DB_NAME') ?: getenv('PGDATABASE') ?: 'postgres');
        $sslmode = getenv('DB_SSLMODE') ?: getenv('SUPABASE_DB_SSLMODE') ?: getenv('PGSSLMODE') ?: 'require';

        if ($databaseUrl !== '') {
            $parsed = @parse_url($databaseUrl);
            if (is_array($parsed)) {
                $host = (string)($parsed['host'] ?? $host);
                $port = (string)($parsed['port'] ?? $port);
                $user = isset($parsed['user']) ? rawurldecode((string)$parsed['user']) : $user;
                $pass = isset($parsed['pass']) ? rawurldecode((string)$parsed['pass']) : $pass;
                if (!empty($parsed['path'])) {
                    $urlDb = ltrim((string)$parsed['path'], '/');
                    if ($urlDb !== '') {
                        $name = $dbName ?: $urlDb;
                    }
                }
                if (!empty($parsed['query'])) {
                    parse_str((string)$parsed['query'], $query);
                    if (!empty($query['sslmode'])) {
                        $sslmode = (string)$query['sslmode'];
                    }
                }
            }
        }

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
            error_log('MAGX DB connect failed: ' . $e->getMessage());
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
        if (!empty($_SESSION['magx_admin_authenticated']) || !empty($_SESSION['adminuser']) || !empty($_SESSION['username'])) {
            return true;
        }
        $cookieUser = magx_read_admin_cookie();
        if ($cookieUser !== null) {
            $_SESSION['magx_admin_authenticated'] = true;
            $_SESSION['adminuser'] = $cookieUser;
            return true;
        }
        return false;
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
        if ($sessionToken !== '' && hash_equals($sessionToken, $token)) {
            return true;
        }
        $cookieToken = isset($_COOKIE['magx_csrf']) ? (string)$_COOKIE['magx_csrf'] : '';
        if ($cookieToken !== '' && hash_equals($cookieToken, $token)) {
            return true;
        }
        return false;
    }
}
