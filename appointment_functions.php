<?php

require_once __DIR__ . '/app_bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if (!function_exists('magx_json_response')) {
    function magx_json_response(array $payload, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload);
        exit;
    }
}

if (!function_exists('magx_appointment_config')) {
    function magx_appointment_config(): array
    {
        $start = trim((string)(getenv('APPOINTMENT_BUSINESS_HOUR_START') ?: '09:00'));
        $end = trim((string)(getenv('APPOINTMENT_BUSINESS_HOUR_END') ?: '17:00'));
        $slotMinutes = (int)(getenv('APPOINTMENT_SLOT_MINUTES') ?: 30);
        $slotMinutes = in_array($slotMinutes, [15, 30, 45, 60], true) ? $slotMinutes : 30;
        $capacity = max(1, (int)(getenv('APPOINTMENT_SLOT_CAPACITY') ?: 1));
        $timezone = trim((string)(getenv('APPOINTMENT_TIMEZONE') ?: 'UTC'));
        if ($timezone === '') {
            $timezone = 'UTC';
        }

        return [
            'start' => $start,
            'end' => $end,
            'slot_minutes' => $slotMinutes,
            'slot_capacity' => $capacity,
            'timezone' => $timezone,
        ];
    }
}

if (!function_exists('magx_appointment_time_valid')) {
    function magx_appointment_time_valid(string $time): bool
    {
        return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time);
    }
}

if (!function_exists('magx_appointment_date_valid')) {
    function magx_appointment_date_valid(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        return checkdate($m, $d, $y);
    }
}

if (!function_exists('magx_appointment_slots')) {
    function magx_appointment_slots(array $config): array
    {
        if (!magx_appointment_time_valid((string)$config['start']) || !magx_appointment_time_valid((string)$config['end'])) {
            return [];
        }

        [$sh, $sm] = array_map('intval', explode(':', (string)$config['start']));
        [$eh, $em] = array_map('intval', explode(':', (string)$config['end']));
        $startMin = ($sh * 60) + $sm;
        $endMin = ($eh * 60) + $em;

        $slot = max(15, (int)$config['slot_minutes']);
        if ($endMin <= $startMin) {
            return [];
        }

        $out = [];
        for ($m = $startMin; ($m + $slot) <= $endMin; $m += $slot) {
            $h = str_pad((string)intdiv($m, 60), 2, '0', STR_PAD_LEFT);
            $mi = str_pad((string)($m % 60), 2, '0', STR_PAD_LEFT);
            $time = $h . ':' . $mi;
            $out[] = [
                'value' => $time,
                'label' => magx_appointment_human_time($time),
            ];
        }
        return $out;
    }
}

if (!function_exists('magx_appointment_human_time')) {
    function magx_appointment_human_time(string $hhmm): string
    {
        if (!magx_appointment_time_valid($hhmm)) {
            return $hhmm;
        }
        [$h, $m] = array_map('intval', explode(':', $hhmm));
        $ampm = $h >= 12 ? 'PM' : 'AM';
        $h12 = $h % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }
        return sprintf('%d:%02d %s', $h12, $m, $ampm);
    }
}

if (!function_exists('magx_appointment_datetime_iso')) {
    function magx_appointment_datetime_iso(string $date, string $time, string $timezone): ?string
    {
        try {
            $tz = new DateTimeZone($timezone ?: 'UTC');
            $dt = new DateTime($date . ' ' . $time . ':00', $tz);
            return $dt->format(DateTimeInterface::ATOM);
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('magx_http_json')) {
    function magx_http_json(string $method, string $url, array $headers = [], ?array $body = null): array
    {
        $ch = curl_init();
        if ($ch === false) {
            return ['ok' => false, 'status' => 0, 'body' => null, 'raw' => 'Failed to initialize cURL'];
        }

        $headerList = [];
        foreach ($headers as $k => $v) {
            $headerList[] = $k . ': ' . $v;
        }

        if ($body !== null) {
            $headerList[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headerList,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'status' => $status, 'body' => null, 'raw' => $err];
        }

        $decoded = json_decode((string)$raw, true);
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'body' => is_array($decoded) ? $decoded : null,
            'raw' => (string)$raw,
        ];
    }
}

if (!function_exists('magx_supabase_base_url')) {
    function magx_supabase_base_url(): string
    {
        return rtrim((string)(getenv('SUPABASE_URL') ?: ''), '/');
    }
}

if (!function_exists('magx_supabase_anon_key')) {
    function magx_supabase_anon_key(): string
    {
        return (string)(getenv('SUPABASE_ANON_KEY') ?: '');
    }
}

if (!function_exists('magx_supabase_signin_with_password')) {
    function magx_supabase_signin_with_password(string $email, string $password): array
    {
        $base = magx_supabase_base_url();
        $anon = magx_supabase_anon_key();
        if ($base === '' || $anon === '') {
            return ['success' => false, 'message' => 'Supabase Auth is not configured.'];
        }

        $resp = magx_http_json('POST', $base . '/auth/v1/token?grant_type=password', [
            'apikey' => $anon,
        ], [
            'email' => $email,
            'password' => $password,
        ]);

        if (!$resp['ok'] || !is_array($resp['body'])) {
            return ['success' => false, 'message' => 'Invalid credentials or auth request failed.'];
        }

        $token = (string)($resp['body']['access_token'] ?? '');
        if ($token === '') {
            return ['success' => false, 'message' => 'Authentication token missing.'];
        }

        $userResp = magx_supabase_user_from_token($token);
        if (!$userResp['success']) {
            return $userResp;
        }

        if (!magx_supabase_is_admin_role($userResp['user'])) {
            return ['success' => false, 'message' => 'Supabase user is not authorized as admin.'];
        }

        return [
            'success' => true,
            'access_token' => $token,
            'user' => $userResp['user'],
        ];
    }
}

if (!function_exists('magx_supabase_user_from_token')) {
    function magx_supabase_user_from_token(string $token): array
    {
        $base = magx_supabase_base_url();
        $anon = magx_supabase_anon_key();
        if ($base === '' || $anon === '') {
            return ['success' => false, 'message' => 'Supabase Auth is not configured.'];
        }

        $resp = magx_http_json('GET', $base . '/auth/v1/user', [
            'apikey' => $anon,
            'Authorization' => 'Bearer ' . $token,
        ]);

        if (!$resp['ok'] || !is_array($resp['body'])) {
            return ['success' => false, 'message' => 'Failed to validate Supabase token.'];
        }

        return ['success' => true, 'user' => $resp['body']];
    }
}

if (!function_exists('magx_supabase_is_admin_role')) {
    function magx_supabase_is_admin_role(array $user): bool
    {
        $roles = [];
        $appMeta = isset($user['app_metadata']) && is_array($user['app_metadata']) ? $user['app_metadata'] : [];
        $userMeta = isset($user['user_metadata']) && is_array($user['user_metadata']) ? $user['user_metadata'] : [];

        foreach (['role', 'roles'] as $key) {
            if (!empty($appMeta[$key])) {
                $roles = array_merge($roles, is_array($appMeta[$key]) ? $appMeta[$key] : [(string)$appMeta[$key]]);
            }
            if (!empty($userMeta[$key])) {
                $roles = array_merge($roles, is_array($userMeta[$key]) ? $userMeta[$key] : [(string)$userMeta[$key]]);
            }
        }

        $roles = array_map(static function ($v) {
            return strtolower(trim((string)$v));
        }, $roles);

        if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true)) {
            return true;
        }

        $allowlist = trim((string)(getenv('APPOINTMENTS_ADMIN_EMAILS') ?: ''));
        if ($allowlist !== '') {
            $emails = array_filter(array_map(static function ($v) {
                return strtolower(trim($v));
            }, explode(',', $allowlist)));
            $email = strtolower(trim((string)($user['email'] ?? '')));
            if ($email !== '' && in_array($email, $emails, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('magx_google_access_token')) {
    function magx_google_access_token(): array
    {
        $clientId = (string)(getenv('GOOGLE_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '');
        $refreshToken = (string)(getenv('GOOGLE_REFRESH_TOKEN') ?: '');

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            return ['success' => false, 'message' => 'Google OAuth credentials are not configured.'];
        }

        $ch = curl_init();
        if ($ch === false) {
            return ['success' => false, 'message' => 'Failed to initialize Google OAuth request.'];
        }

        $payload = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = is_string($raw) ? json_decode($raw, true) : null;
        if ($status < 200 || $status >= 300 || !is_array($data) || empty($data['access_token'])) {
            return ['success' => false, 'message' => 'Failed to refresh Google access token.'];
        }

        return ['success' => true, 'access_token' => (string)$data['access_token']];
    }
}

if (!function_exists('magx_create_google_calendar_event')) {
    function magx_create_google_calendar_event(array $appointment, string $zoomLink, string $timezone): array
    {
        $tokenRes = magx_google_access_token();
        if (!$tokenRes['success']) {
            return $tokenRes;
        }

        $calendarId = rawurlencode((string)(getenv('GOOGLE_CALENDAR_ID') ?: 'primary'));
        $startIso = magx_appointment_datetime_iso((string)$appointment['preferred_date'], (string)$appointment['preferred_time'], $timezone);
        if ($startIso === null) {
            return ['success' => false, 'message' => 'Invalid appointment datetime for Google calendar.'];
        }

        $duration = max(15, (int)(getenv('APPOINTMENT_SLOT_MINUTES') ?: 30));
        try {
            $startDt = new DateTime($startIso);
            $endDt = (clone $startDt)->modify('+' . $duration . ' minutes');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Unable to build Google event time range.'];
        }

        $summary = 'Appointment: ' . (string)$appointment['full_name'];
        $description = trim((string)($appointment['notes'] ?? ''));
        $service = trim((string)($appointment['service_type'] ?? ''));
        if ($service !== '') {
            $description = 'Service: ' . $service . "\n" . $description;
        }

        $payload = [
            'summary' => $summary,
            'description' => trim($description . "\nZoom: " . $zoomLink),
            'location' => $zoomLink,
            'start' => [
                'dateTime' => $startDt->format(DateTimeInterface::ATOM),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $endDt->format(DateTimeInterface::ATOM),
                'timeZone' => $timezone,
            ],
            'attendees' => [
                ['email' => (string)$appointment['email']],
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 1440],
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ];

        $resp = magx_http_json(
            'POST',
            'https://www.googleapis.com/calendar/v3/calendars/' . $calendarId . '/events',
            ['Authorization' => 'Bearer ' . $tokenRes['access_token']],
            $payload
        );

        if (!$resp['ok'] || !is_array($resp['body']) || empty($resp['body']['id'])) {
            return ['success' => false, 'message' => 'Google calendar event creation failed.'];
        }

        return [
            'success' => true,
            'event_id' => (string)$resp['body']['id'],
            'html_link' => (string)($resp['body']['htmlLink'] ?? ''),
        ];
    }
}

if (!function_exists('magx_outlook_access_token')) {
    function magx_outlook_access_token(): array
    {
        $tenant = (string)(getenv('MS_TENANT_ID') ?: '');
        $clientId = (string)(getenv('MS_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('MS_CLIENT_SECRET') ?: '');

        if ($tenant === '' || $clientId === '' || $clientSecret === '') {
            return ['success' => false, 'message' => 'Microsoft Graph credentials are not configured.'];
        }

        $ch = curl_init();
        if ($ch === false) {
            return ['success' => false, 'message' => 'Failed to initialize Microsoft token request.'];
        }

        $payload = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials',
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = is_string($raw) ? json_decode($raw, true) : null;
        if ($status < 200 || $status >= 300 || !is_array($data) || empty($data['access_token'])) {
            return ['success' => false, 'message' => 'Failed to fetch Microsoft Graph token.'];
        }

        return ['success' => true, 'access_token' => (string)$data['access_token']];
    }
}

if (!function_exists('magx_create_outlook_calendar_event')) {
    function magx_create_outlook_calendar_event(array $appointment, string $zoomLink, string $timezone): array
    {
        $tokenRes = magx_outlook_access_token();
        if (!$tokenRes['success']) {
            return $tokenRes;
        }

        $userId = trim((string)(getenv('MS_USER_ID') ?: getenv('MS_USER_EMAIL') ?: ''));
        if ($userId === '') {
            return ['success' => false, 'message' => 'MS_USER_ID or MS_USER_EMAIL is not configured.'];
        }

        $calendarId = trim((string)(getenv('MS_CALENDAR_ID') ?: ''));
        $duration = max(15, (int)(getenv('APPOINTMENT_SLOT_MINUTES') ?: 30));

        try {
            $start = new DateTime((string)$appointment['preferred_date'] . ' ' . (string)$appointment['preferred_time'] . ':00', new DateTimeZone($timezone));
            $end = (clone $start)->modify('+' . $duration . ' minutes');
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Invalid appointment datetime for Outlook calendar.'];
        }

        $notes = trim((string)($appointment['notes'] ?? ''));
        $service = trim((string)($appointment['service_type'] ?? ''));
        $body = '<p>Appointment request approved.</p>';
        if ($service !== '') {
            $body .= '<p><strong>Service:</strong> ' . htmlspecialchars($service, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        if ($notes !== '') {
            $body .= '<p><strong>Notes:</strong> ' . nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        $body .= '<p><strong>Zoom:</strong> <a href="' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '</a></p>';

        $payload = [
            'subject' => 'Appointment: ' . (string)$appointment['full_name'],
            'body' => [
                'contentType' => 'HTML',
                'content' => $body,
            ],
            'start' => [
                'dateTime' => $start->format('Y-m-d\\TH:i:s'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->format('Y-m-d\\TH:i:s'),
                'timeZone' => $timezone,
            ],
            'location' => ['displayName' => $zoomLink],
            'attendees' => [
                [
                    'emailAddress' => ['address' => (string)$appointment['email']],
                    'type' => 'required',
                ],
            ],
            'isReminderOn' => true,
            'reminderMinutesBeforeStart' => 30,
        ];

        $path = $calendarId !== ''
            ? '/users/' . rawurlencode($userId) . '/calendars/' . rawurlencode($calendarId) . '/events'
            : '/users/' . rawurlencode($userId) . '/events';

        $resp = magx_http_json(
            'POST',
            'https://graph.microsoft.com/v1.0' . $path,
            ['Authorization' => 'Bearer ' . $tokenRes['access_token']],
            $payload
        );

        if (!$resp['ok'] || !is_array($resp['body']) || empty($resp['body']['id'])) {
            return ['success' => false, 'message' => 'Outlook calendar event creation failed.'];
        }

        return [
            'success' => true,
            'event_id' => (string)$resp['body']['id'],
            'web_link' => (string)($resp['body']['webLink'] ?? ''),
        ];
    }
}

if (!function_exists('magx_build_add_to_calendar_links')) {
    function magx_build_add_to_calendar_links(array $appointment, string $zoomLink, string $timezone): array
    {
        $duration = max(15, (int)(getenv('APPOINTMENT_SLOT_MINUTES') ?: 30));
        $start = new DateTime((string)$appointment['preferred_date'] . ' ' . (string)$appointment['preferred_time'] . ':00', new DateTimeZone($timezone));
        $end = (clone $start)->modify('+' . $duration . ' minutes');

        $title = 'Appointment - ' . (string)$appointment['full_name'];
        $desc = trim((string)($appointment['notes'] ?? ''));
        if (!empty($appointment['service_type'])) {
            $desc = 'Service: ' . (string)$appointment['service_type'] . "\n" . $desc;
        }
        $desc = trim($desc . "\nZoom: " . $zoomLink);
        $startUtc = (clone $start)->setTimezone(new DateTimeZone('UTC'));
        $endUtc = (clone $end)->setTimezone(new DateTimeZone('UTC'));

        $googleParams = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $startUtc->format('Ymd\\THis\\Z') . '/' . $endUtc->format('Ymd\\THis\\Z'),
            'details' => $desc,
            'location' => $zoomLink,
        ]);

        $outlookParams = http_build_query([
            'path' => '/calendar/action/compose',
            'rru' => 'addevent',
            'subject' => $title,
            'startdt' => $start->format(DateTimeInterface::ATOM),
            'enddt' => $end->format(DateTimeInterface::ATOM),
            'body' => $desc,
            'location' => $zoomLink,
        ]);

        return [
            'google' => 'https://calendar.google.com/calendar/render?' . $googleParams,
            'outlook' => 'https://outlook.office.com/calendar/0/deeplink/compose?' . $outlookParams,
        ];
    }
}

if (!function_exists('magx_send_appointment_approval_emails')) {
    function magx_send_appointment_approval_emails(array $appointment, array $calendarLinks, string $zoomLink, string $timezone): array
    {
        $smtpHost = (string)(getenv('SMTP_HOST') ?: '');
        $smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
        $smtpUser = (string)(getenv('SMTP_USER') ?: '');
        $smtpPass = (string)(getenv('SMTP_PASSWORD') ?: '');
        $fromEmail = (string)(getenv('SMTP_FROM_EMAIL') ?: '');
        $fromName = (string)(getenv('SMTP_FROM_NAME') ?: 'MAGX Solutions');
        $adminEmail = (string)(getenv('ADMIN_NOTIFICATION_EMAIL') ?: $fromEmail);

        if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $fromEmail === '') {
            return ['success' => false, 'message' => 'SMTP credentials are not configured.'];
        }

        $when = (string)$appointment['preferred_date'] . ' at ' . magx_appointment_human_time((string)$appointment['preferred_time']) . ' (' . $timezone . ')';

        try {
            $client = new PHPMailer(true);
            $client->isSMTP();
            $client->Host = $smtpHost;
            $client->SMTPAuth = true;
            $client->Username = $smtpUser;
            $client->Password = $smtpPass;
            $client->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $client->Port = $smtpPort;
            $client->setFrom($fromEmail, $fromName);
            $client->addAddress((string)$appointment['email'], (string)$appointment['full_name']);
            $client->isHTML(true);
            $client->Subject = 'Your Appointment Has Been Approved';
            $client->Body =
                '<p>Hello ' . htmlspecialchars((string)$appointment['full_name'], ENT_QUOTES, 'UTF-8') . ',</p>' .
                '<p>Your appointment has been approved.</p>' .
                '<p><strong>Date and Time:</strong> ' . htmlspecialchars($when, ENT_QUOTES, 'UTF-8') . '</p>' .
                '<p><strong>Zoom Link:</strong> <a href="' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '</a></p>' .
                '<p><a href="' . htmlspecialchars((string)$calendarLinks['google'], ENT_QUOTES, 'UTF-8') . '">Add to Google Calendar</a><br>' .
                '<a href="' . htmlspecialchars((string)$calendarLinks['outlook'], ENT_QUOTES, 'UTF-8') . '">Add to Outlook Calendar</a></p>' .
                '<p>Thank you.</p>';
            $client->send();

            if ($adminEmail !== '') {
                $admin = new PHPMailer(true);
                $admin->isSMTP();
                $admin->Host = $smtpHost;
                $admin->SMTPAuth = true;
                $admin->Username = $smtpUser;
                $admin->Password = $smtpPass;
                $admin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $admin->Port = $smtpPort;
                $admin->setFrom($fromEmail, $fromName);
                $admin->addAddress($adminEmail, 'Admin');
                $admin->isHTML(true);
                $admin->Subject = 'New Appointment Approved';
                $admin->Body =
                    '<p>An appointment was approved.</p>' .
                    '<p><strong>Name:</strong> ' . htmlspecialchars((string)$appointment['full_name'], ENT_QUOTES, 'UTF-8') . '<br>' .
                    '<strong>Gmail:</strong> ' . htmlspecialchars((string)$appointment['email'], ENT_QUOTES, 'UTF-8') . '<br>' .
                    '<strong>Phone:</strong> ' . htmlspecialchars((string)($appointment['phone'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br>' .
                    '<strong>Date and Time:</strong> ' . htmlspecialchars($when, ENT_QUOTES, 'UTF-8') . '<br>' .
                    '<strong>Service:</strong> ' . htmlspecialchars((string)($appointment['service_type'] ?? ''), ENT_QUOTES, 'UTF-8') . '<br>' .
                    '<strong>Notes:</strong> ' . nl2br(htmlspecialchars((string)($appointment['notes'] ?? ''), ENT_QUOTES, 'UTF-8')) . '<br>' .
                    '<strong>Zoom Link:</strong> <a href="' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($zoomLink, ENT_QUOTES, 'UTF-8') . '</a></p>';
                $admin->send();
            }

            return ['success' => true];
        } catch (PHPMailerException $e) {
            return ['success' => false, 'message' => 'Gmail delivery failed.'];
        }
    }
}
