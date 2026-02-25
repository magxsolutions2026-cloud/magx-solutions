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

function magx_outlook_env_ready(): bool {
    return trim((string)(getenv('MS_TENANT_ID') ?: '')) !== '' &&
        trim((string)(getenv('MS_CLIENT_ID') ?: '')) !== '' &&
        trim((string)(getenv('MS_CLIENT_SECRET') ?: '')) !== '' &&
        trim((string)(getenv('MS_USER_ID') ?: getenv('MS_USER_EMAIL') ?: '')) !== '';
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

if ($action === 'CANCEL_APPROVED') {
    $id = trim((string)($_POST['id'] ?? ''));
    if ($id === '' || strlen($id) > 80) {
        magx_json_response(['success' => false, 'message' => 'Invalid appointment id.'], 422);
    }

    $row = magx_db_execute(
        $db,
        "SELECT status FROM appointments WHERE id = :id LIMIT 1",
        [':id' => $id]
    )->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        magx_json_response(['success' => false, 'message' => 'Appointment not found.'], 404);
    }

    if ((string)$row['status'] !== 'approved') {
        if ((string)$row['status'] === 'rejected') {
            magx_json_response(['success' => true, 'message' => 'Appointment is already cancelled.']);
        }
        magx_json_response(['success' => true, 'message' => 'Appointment is not in approved status.']);
    }

    magx_db_execute(
        $db,
        "UPDATE appointments
         SET status = 'rejected'
         WHERE id = :id AND status = 'approved'",
        [':id' => $id]
    );

    magx_json_response(['success' => true, 'message' => 'Approved appointment schedule cancelled.']);
}

if ($action === 'DECIDE') {
    $id = trim((string)($_POST['id'] ?? ''));
    $decision = strtoupper(trim((string)($_POST['decision'] ?? '')));
    $zoomLink = trim((string)($_POST['zoom_link'] ?? ''));

    if ($id === '' || strlen($id) > 80) {
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
        if ((string)$appointment['status'] === 'approved') {
            magx_json_response(['success' => true, 'message' => 'Appointment is already approved.']);
        }
        if ((string)$appointment['status'] === 'rejected') {
            magx_json_response(['success' => true, 'message' => 'Appointment is already rejected.']);
        }
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
    $warnings = [];

    $google = magx_create_google_calendar_event($appointment, $zoomLink, $timezone);
    if (!$google['success']) {
        $warnings[] = 'Google calendar sync failed: ' . (string)$google['message'];
        $google = ['success' => false, 'event_id' => null];
    }

    $outlook = ['success' => false, 'event_id' => null, 'message' => 'Outlook calendar is not configured.'];
    $outlookWarning = '';
    if (magx_outlook_env_ready()) {
        $outlook = magx_create_outlook_calendar_event($appointment, $zoomLink, $timezone);
        if (!$outlook['success']) {
            $outlookWarning = 'Outlook calendar sync failed: ' . (string)$outlook['message'];
        }
    } else {
        $outlookWarning = 'Outlook calendar is not configured.';
    }

    $calendarLinks = magx_build_add_to_calendar_links($appointment, $zoomLink, $timezone);
    $emailRes = magx_send_appointment_approval_emails($appointment, $calendarLinks, $zoomLink, $timezone);
    if (!$emailRes['success']) {
        $warnings[] = 'Notification send failed: ' . (string)$emailRes['message'];
    }

    magx_db_execute(
        $db,
        "UPDATE appointments
         SET google_event_id = :g,
             outlook_event_id = :o
         WHERE id = :id AND status = 'approved'",
        [
            ':g' => $google['success'] ? (string)$google['event_id'] : null,
            ':o' => $outlook['success'] ? (string)$outlook['event_id'] : null,
            ':id' => $id,
        ]
    );

    $message = 'Appointment approved and notifications sent.';
    if ($outlookWarning !== '') {
        $warnings[] = $outlookWarning;
    }
    if (!empty($warnings)) {
        $message = 'Appointment approved. ' . implode(' ', $warnings);
    }

    magx_json_response([
        'success' => true,
        'message' => $message,
    ]);
}

magx_json_response(['success' => false, 'message' => 'Invalid action.'], 400);
