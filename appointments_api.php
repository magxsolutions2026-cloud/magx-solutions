<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/appointment_functions.php';

magx_send_security_headers();
magx_require_post_request();

$db = magx_db_connect();
if (!$db) {
    magx_json_response(['success' => false, 'message' => 'Database connection failed'], 500);
}

$action = strtoupper(trim((string)($_POST['action'] ?? '')));
$config = magx_appointment_config();
$slots = magx_appointment_slots($config);
$slotMap = [];
foreach ($slots as $slot) {
    $slotMap[(string)$slot['value']] = (string)$slot['label'];
}

if ($action === 'AVAILABLE_SLOTS') {
    $date = trim((string)($_POST['date'] ?? ''));
    if (!magx_appointment_date_valid($date)) {
        magx_json_response(['success' => false, 'message' => 'Invalid date'], 422);
    }

    $stmt = magx_db_execute(
        $db,
        "SELECT to_char(preferred_time, 'HH24:MI') AS preferred_time, COUNT(*) AS c
         FROM appointments
         WHERE preferred_date = :d
           AND status IN ('pending','approved')
         GROUP BY preferred_time",
        [':d' => $date]
    );

    $booked = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $booked[(string)$row['preferred_time']] = (int)$row['c'];
    }

    $result = [];
    foreach ($slots as $slot) {
        $value = (string)$slot['value'];
        $count = (int)($booked[$value] ?? 0);
        $result[] = [
            'value' => $value,
            'label' => (string)$slot['label'],
            'is_available' => $count < (int)$config['slot_capacity'],
            'remaining' => max(0, (int)$config['slot_capacity'] - $count),
        ];
    }

    magx_json_response([
        'success' => true,
        'date' => $date,
        'timezone' => (string)$config['timezone'],
        'slot_minutes' => (int)$config['slot_minutes'],
        'slots' => $result,
    ]);
}

if ($action === 'SUBMIT') {
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $date = trim((string)($_POST['preferred_date'] ?? ''));
    $time = trim((string)($_POST['preferred_time'] ?? ''));
    $serviceType = trim((string)($_POST['service_type'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));

    $errors = [];
    if ($fullName === '') {
        $errors['full_name'] = 'Full name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid Gmail address is required.';
    }
    if (!magx_appointment_date_valid($date)) {
        $errors['preferred_date'] = 'Preferred date is invalid.';
    }
    if (!isset($slotMap[$time])) {
        $errors['preferred_time'] = 'Preferred time slot is not valid.';
    }

    if (empty($errors) && magx_appointment_date_valid($date)) {
        $tz = new DateTimeZone((string)$config['timezone']);
        $today = new DateTime('today', $tz);
        $selected = new DateTime($date, $tz);
        if ($selected < $today) {
            $errors['preferred_date'] = 'Preferred date cannot be in the past.';
        }
    }

    if (!empty($errors)) {
        magx_json_response(['success' => false, 'message' => 'Please fix validation errors.', 'errors' => $errors], 422);
    }

    try {
        $db->beginTransaction();

        // Advisory lock prevents race conditions for this exact slot.
        magx_db_execute($db, 'SELECT pg_advisory_xact_lock(hashtext(:k))', [
            ':k' => $date . '|' . $time,
        ]);

        $countStmt = magx_db_execute(
            $db,
            "SELECT COUNT(*) AS c
             FROM appointments
             WHERE preferred_date = :d
               AND to_char(preferred_time, 'HH24:MI') = :t
               AND status IN ('pending','approved')",
            [':d' => $date, ':t' => $time]
        );
        $count = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        if ($count >= (int)$config['slot_capacity']) {
            $db->rollBack();
            magx_json_response(['success' => false, 'message' => 'Selected time slot is no longer available.'], 409);
        }

        magx_db_execute(
            $db,
            "INSERT INTO appointments
            (full_name, email, phone, preferred_date, preferred_time, service_type, notes, status, created_at)
            VALUES
            (:fn, :em, :ph, :pd, :pt, :st, :nt, 'pending', NOW())",
            [
                ':fn' => $fullName,
                ':em' => $email,
                ':ph' => $phone,
                ':pd' => $date,
                ':pt' => $time,
                ':st' => $serviceType,
                ':nt' => $notes,
            ]
        );

        $db->commit();
        magx_json_response([
            'success' => true,
            'message' => 'Your appointment request has been submitted and is pending admin approval.',
        ]);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        magx_json_response(['success' => false, 'message' => 'Could not submit appointment. Please try again.'], 500);
    }
}

magx_json_response(['success' => false, 'message' => 'Invalid action.'], 400);
