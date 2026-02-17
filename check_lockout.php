<?php
header('Content-Type: application/json');
session_start();

// Function to check login attempts using sessions
function checkLoginAttempts($username, $loginType = 'user') {
    $sessionKey = $loginType . '_attempts_' . $username;
    
    if (!isset($_SESSION[$sessionKey])) {
        return 0;
    }
    
    $attempts = $_SESSION[$sessionKey];
    $currentTime = time();
    
    // Remove attempts older than 5 minutes
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime) {
        return ($currentTime - $timestamp) < 300; // 300 seconds = 5 minutes
    });
    
    // Update session with cleaned attempts
    $_SESSION[$sessionKey] = $attempts;
    
    return count($attempts);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $loginType = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';
    
    if ($username) {
        $attempts = checkLoginAttempts($username, $loginType);
        $locked = $attempts >= 3;
        
        echo json_encode([
            'locked' => $locked,
            'attempts' => $attempts,
            'remaining' => max(0, 3 - $attempts)
        ]);
    } else {
        echo json_encode([
            'locked' => false,
            'attempts' => 0,
            'remaining' => 3
        ]);
    }
} else {
    echo json_encode([
        'locked' => false,
        'attempts' => 0,
        'remaining' => 3
    ]);
}
?>
