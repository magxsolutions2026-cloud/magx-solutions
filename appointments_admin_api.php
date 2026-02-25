<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/appointment_functions.php';

magx_send_security_headers();
magx_require_post_request();

if (!magx_is_admin_authenticated()) {
    magx_json_response(['success' => false, 'message' => 'Authentication required.'], 403);
}

$db = magx_db_connect();
if (!$db) {
    magx_json_response(['success' => false, 'message' => 'Database connection failed'], 500);
}

$action = strtoupper(trim((string)($_POST['action'] ?? '')));

if ($action === 'SUPABASE_LOGIN') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        magx_json_response(['success' => false, 'message' => 'Email and password are required.'], 422);
    }

    $login = magx_supabase_signin_with_password($email, $password);
    if (!$login['success']) {
        magx_json_response(['success' => false, 'message' => (string)$login['message']], 403);
    }

    magx_json_response([
        'success' => true,
        'token' => (string)$login['access_token'],
        'user' => [
            'id' => (string)($login['user']['id'] ?? ''),
            'email' => (string)($login['user']['email'] ?? ''),
        ],
    ]);
}

$supabaseToken = trim((string)($_POST['supabase_token'] ?? ''));
if ($supabaseToken === '') {
    magx_json_response(['success' => false, 'message' => 'Supabase admin token is required.'], 403);
}
$userRes = magx_supabase_user_from_token($supabaseToken);
if (!$userRes['success'] || !magx_supabase_is_admin_role((array)($userRes['user'] ?? []))) {
    magx_json_response(['success' => false, 'message' => 'Supabase admin authorization failed.'], 403);
}

if ($action === 'LOAD_PENDING') {
    $rows = magx_db_execute(
        $db,
        "SELECT id, full_name, email, phone, preferred_date, preferred_time, service_type, notes, status, zoom_link, created_at
         FROM appointments
         WHERE status = 'pending'
         ORDER BY preferred_date ASC, preferred_time ASC, created_at ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    magx_json_response(['success' => true, 'data' => $rows ?: []]);
}

if ($action === 'LOAD_APPROVED') {
    $rows = magx_db_execute(
        $db,
        "SELECT id, full_name, email, phone, preferred_date, preferred_time, service_type, notes, status, zoom_link, google_event_id, outlook_event_id, created_at
         FROM appointments
         WHERE status = 'approved'
         ORDER BY preferred_date DESC, preferred_time DESC, created_at DESC
         LIMIT 200"
    )->fetchAll(PDO::FETCH_ASSOC);

    magx_json_response(['success' => true, 'data' => $rows ?: []]);
}

if ($action === 'DECIDE') {
    $id = trim((string)($_POST['id'] ?? ''));
    $decision = strtoupper(trim((string)($_POST['decision'] ?? '')));
    $zoomLink = trim((string)($_POST['zoom_link'] ?? ''));

    if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
        magx_json_response(['success' => false, 'message' => 'Invalid appointment id.'], 422);
    }
    if (!in_array($decision, ['APPROVE', 'REJECT'], true)) {
        magx_json_response(['success' => false, 'message' => 'Invalid decision.'], 422);
    }

    $appointment = magx_db_execute(
        $db,
        "SELECT * FROM appointments WHERE id = :id LIMIT 1",
        [':id' => $id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        magx_json_response(['success' => false, 'message' => 'Appointment not found.'], 404);
    }

    if ((string)$appointment['status'] !== 'pending') {
        magx_json_response(['success' => false, 'message' => 'Appointment is no longer pending.'], 409);
    }

    if ($decision === 'REJECT') {
        magx_db_execute($db, "UPDATE appointments SET status = 'rejected' WHERE id = :id", [':id' => $id]);
        magx_json_response(['success' => true, 'message' => 'Appointment rejected.']);
    }

    if ($zoomLink === '') {
        $zoomLink = trim((string)(getenv('DEFAULT_ZOOM_MEETING_LINK') ?: ''));
        if ($zoomLink === '') {
            $zoomLink = 'https://zoom.us/j/' . random_int(1000000000, 9999999999);
        }
    }

    if (!preg_match('#^https?://#i', $zoomLink)) {
        magx_json_response(['success' => false, 'message' => 'Zoom link must be a valid URL.'], 422);
    }

    $updated = magx_db_execute(
        $db,
        "UPDATE appointments
         SET status = 'approved',
             zoom_link = :z
         WHERE id = :id AND status = 'pending'",
        [':z' => $zoomLink, ':id' => $id]
    );
    if ($updated->rowCount() < 1) {
        magx_json_response(['success' => false, 'message' => 'Appointment is no longer pending.'], 409);
    }

    $timezone = (string)(magx_appointment_config()['timezone'] ?? 'UTC');

    $google = magx_create_google_calendar_event($appointment, $zoomLink, $timezone);
    if (!$google['success']) {
        magx_db_execute($db, "UPDATE appointments SET status = 'pending', zoom_link = NULL WHERE id = :id", [':id' => $id]);
        magx_json_response(['success' => false, 'message' => (string)$google['message']], 500);
    }

    $outlook = magx_create_outlook_calendar_event($appointment, $zoomLink, $timezone);
    if (!$outlook['success']) {
        magx_db_execute($db, "UPDATE appointments SET status = 'pending', zoom_link = NULL WHERE id = :id", [':id' => $id]);
        magx_json_response(['success' => false, 'message' => (string)$outlook['message']], 500);
    }

    $calendarLinks = magx_build_add_to_calendar_links($appointment, $zoomLink, $timezone);
    $emailRes = magx_send_appointment_approval_emails($appointment, $calendarLinks, $zoomLink, $timezone);
    if (!$emailRes['success']) {
        magx_db_execute($db, "UPDATE appointments SET status = 'pending', zoom_link = NULL WHERE id = :id", [':id' => $id]);
        magx_json_response(['success' => false, 'message' => (string)$emailRes['message']], 500);
    }

    magx_db_execute(
        $db,
        "UPDATE appointments
         SET google_event_id = :g,
             outlook_event_id = :o
         WHERE id = :id AND status = 'approved'",
        [
            ':g' => (string)$google['event_id'],
            ':o' => (string)$outlook['event_id'],
            ':id' => $id,
        ]
    );

    magx_json_response([
        'success' => true,
        'message' => 'Appointment approved and notifications sent.',
    ]);
}

magx_json_response(['success' => false, 'message' => 'Invalid action.'], 400);
