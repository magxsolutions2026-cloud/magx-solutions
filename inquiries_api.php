<?php
// Public "Let's Work Together" form endpoint for the React frontend (web/).
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/inquiry_functions.php';

magx_send_security_headers();
magx_require_post_request();

$action = strtoupper(trim((string)($_POST['action'] ?? '')));
if ($action !== 'SUBMIT') {
    magx_json_response(['success' => false, 'message' => 'Invalid action.'], 400);
}

// Honeypot: real users never fill this hidden field. Pretend success so bots move on.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    magx_json_response(['success' => true, 'message' => 'Thanks! Your message is on its way.']);
}

if (magx_inquiry_rate_limited()) {
    magx_json_response(['success' => false, 'message' => 'Too many messages. Please try again in a few minutes.'], 429);
}

$inquiry = [
    'full_name' => mb_substr(trim((string)($_POST['full_name'] ?? '')), 0, 120),
    'email' => mb_substr(trim((string)($_POST['email'] ?? '')), 0, 200),
    'company' => mb_substr(trim((string)($_POST['company'] ?? '')), 0, 160),
    'budget' => mb_substr(trim((string)($_POST['budget'] ?? '')), 0, 60),
    'message' => mb_substr(trim((string)($_POST['message'] ?? '')), 0, 5000),
];

$errors = [];
if ($inquiry['full_name'] === '') {
    $errors['full_name'] = 'Your name is required.';
}
if ($inquiry['email'] === '' || !filter_var($inquiry['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'A valid email address is required.';
}
if (mb_strlen($inquiry['message']) < 10) {
    $errors['message'] = 'Tell me a little more about your project (at least 10 characters).';
}
if (!empty($errors)) {
    magx_json_response(['success' => false, 'message' => 'Please fix validation errors.', 'errors' => $errors], 422);
}

$db = magx_db_connect();
if (!$db) {
    magx_json_response(['success' => false, 'message' => 'Database connection failed'], 500);
}

try {
    magx_db_execute($db, 'INSERT INTO inquiries (full_name, email, company, budget, message) VALUES (:n, :e, :c, :b, :m)', [
        ':n' => $inquiry['full_name'],
        ':e' => $inquiry['email'],
        ':c' => $inquiry['company'],
        ':b' => $inquiry['budget'],
        ':m' => $inquiry['message'],
    ]);
} catch (Throwable $e) {
    error_log('MAGX inquiry insert failed: ' . $e->getMessage());
    magx_json_response(['success' => false, 'message' => 'Could not save your message. Please try again.'], 500);
}

magx_inquiry_record_hit();

$mailResult = magx_send_inquiry_alert_email($inquiry);
if (empty($mailResult['success'])) {
    error_log('MAGX inquiry alert: ' . ($mailResult['message'] ?? 'unknown error'));
}

magx_json_response(['success' => true, 'message' => "Thanks! Your message is in — I'll get back to you by email."]);
