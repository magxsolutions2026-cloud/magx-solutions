<?php
// Read-only, unauthenticated content feed for the React frontend (web/).
// Exposes only rows the legacy homepage already renders publicly (is_active = 1).
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/app_bootstrap.php';
require_once __DIR__ . '/services_functions.php';
require_once __DIR__ . '/portfolio_functions.php';
require_once __DIR__ . '/footer_contact_functions.php';
magx_send_security_headers();
magx_require_post_request();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60, stale-while-revalidate=300');

$action = strtoupper(trim((string)($_POST['action'] ?? '')));

if ($action === 'ALL') {
    echo json_encode([
        'success' => true,
        'data' => [
            'services' => loadServicesForDisplay(),
            'portfolio' => loadPortfolioItemsForDisplay(),
            'footer' => loadFooterContactForDisplay() ?: null,
        ],
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
