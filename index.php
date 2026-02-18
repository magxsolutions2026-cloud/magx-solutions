<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/app_bootstrap.php';
magx_send_security_headers();

if (empty($_SESSION['magx_csrf'])) {
    try {
        $_SESSION['magx_csrf'] = bin2hex(random_bytes(24));
    } catch (Exception $e) {
        $_SESSION['magx_csrf'] = bin2hex(pack('d', microtime(true)));
    }
}

require_once 'home_posts_functions.php';
require_once 'contact_functions.php';
require_once 'footer_contact_functions.php';
require_once 'services_functions.php';
require_once 'portfolio_functions.php';

function checkLoginAttempts($username, $loginType = 'user') {
    $sessionKey = $loginType . '_attempts_' . $username;
    if (!isset($_SESSION[$sessionKey])) {
        return 0;
    }
    $attempts = $_SESSION[$sessionKey];
    $currentTime = time();
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime) {
        return ($currentTime - $timestamp) < 300;
    });
    $_SESSION[$sessionKey] = $attempts;
    return count($attempts);
}

function recordFailedAttempt($username, $loginType = 'user') {
    $sessionKey = $loginType . '_attempts_' . $username;
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = array();
    }
    $_SESSION[$sessionKey][] = time();
}

function clearLoginAttempts($username, $loginType = 'user') {
    $sessionKey = $loginType . '_attempts_' . $username;
    unset($_SESSION[$sessionKey]);
}

function magxSetFlash($message) {
    $_SESSION['magx_flash'] = (string)$message;
}

function magxRedirect($url) {
    header('Location: ' . $url, true, 303);
    exit;
}

function outputHomePostCard(array $post, int $positionIndex) {
    $description = nl2br(htmlspecialchars($post['description'] ?? ''));
    $likeCount = intval($post['like_count'] ?? 0);
    $commentCount = intval($post['comment_count'] ?? 0);
    $shareCount = intval($post['share_count'] ?? 0);
    $iconImage = 'logomagx.png';
    if (!empty($post['icon_image'])) {
        $iconRaw = trim((string)$post['icon_image']);
        if (preg_match('#^(https?:)?//#i', $iconRaw) || strpos($iconRaw, 'data:') === 0 || strpos($iconRaw, 'uploads/home_posts/') === 0) {
            $iconImage = htmlspecialchars($iconRaw);
        } else {
            $iconImage = 'uploads/home_posts/' . ltrim($iconRaw, '/');
        }
    }

    $bgImage = '';
    if (!empty($post['background_image'])) {
        $bgRaw = trim((string)$post['background_image']);
        if (preg_match('#^(https?:)?//#i', $bgRaw) || strpos($bgRaw, 'data:') === 0 || strpos($bgRaw, 'uploads/home_posts/') === 0) {
            $bgImage = htmlspecialchars($bgRaw);
        } else {
            $bgImage = 'uploads/home_posts/' . ltrim($bgRaw, '/');
        }
    }

    $bgVideo = '';
    if (!empty($post['background_video'])) {
        $vidRaw = trim((string)$post['background_video']);
        if (preg_match('#^(https?:)?//#i', $vidRaw) || strpos($vidRaw, 'data:') === 0 || strpos($vidRaw, 'uploads/home_posts/') === 0) {
            $bgVideo = htmlspecialchars($vidRaw);
        } else {
            $bgVideo = 'uploads/home_posts/' . ltrim($vidRaw, '/');
        }
    }
    $title = htmlspecialchars($post['title'] ?? '');
    $subtitle = htmlspecialchars($post['subtitle'] ?? '');
    $fallbackCaption = htmlspecialchars($title);
    $productFallbackSvg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="500" height="300"><rect width="500" height="300" fill="#672222"/><text x="50%" y="50%" font-size="32" fill="white" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-weight="bold">' . $fallbackCaption . '</text></svg>');
    $imageFirst = ($positionIndex % 2 === 0);
    $productId = intval($post['id'] ?? 0);
    ?>
    <div class="product-card <?php echo ($bgVideo || $bgImage) ? 'has-image' : 'no-image'; ?>" id="post-<?php echo $productId; ?>" data-post-id="<?php echo $productId; ?>">
        <div class="product-header">
            <div class="product-header-left">
                <div class="product-icon">
                    <img src="<?php echo $iconImage; ?>" alt="MAGX logo" onerror="if(!this.dataset.error){this.dataset.error='1';this.style.display='none';}">
                </div>
                <div class="product-title">
                    <h3><?php echo $title; ?></h3>
                    <?php if ($subtitle): ?>
                    <p><?php echo $subtitle; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="product-content <?php echo $imageFirst ? 'is-reverse' : ''; ?>">
            <?php if ($bgVideo || $bgImage): ?>
            <div class="product-image">
                <?php if ($bgVideo): ?>
                <video class="post-card-video" src="<?php echo $bgVideo; ?>" poster="<?php echo ($bgImage ?: $iconImage); ?>" controls playsinline preload="metadata" muted onerror="this.style.display='none';"></video>
                <?php else: ?>
                <img src="<?php echo $bgImage; ?>" alt="<?php echo $title; ?>" onerror="if(!this.dataset.error){this.dataset.error='1';this.src='<?php echo $productFallbackSvg; ?>';}">
                <?php endif; ?>
            </div>
	            <?php endif; ?>
	            <div class="product-description">
	                <div class="product-description-text"><?php echo $description; ?></div>
	                <div class="post-social" aria-label="Engagement">
	                    <button type="button" class="social-chip heart" data-kind="like" aria-label="Like post">
	                        <i class="fas fa-heart"></i>
	                        <span class="social-count"><?php echo $likeCount; ?></span>
	                    </button>
                    <button type="button" class="social-chip comment" data-kind="comment" aria-label="Comment on post">
                        <i class="fas fa-comment"></i>
                        <span class="social-count"><?php echo $commentCount; ?></span>
                    </button>
                    <button type="button" class="social-chip share" data-kind="share" aria-label="Share post">
                        <i class="fas fa-share"></i>
                        <span class="social-count"><?php echo $shareCount; ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function renderHomeScrollIndicator() {
    ?>
    <div class="scroll-indicator" id="homeScrollHint">
        <span>Scroll to explore</span>
        <i class="fas fa-chevron-down"></i>
    </div>
    <?php
}

// Avoid browser "Confirm Form Resubmission" by using POST-Redirect-GET.
// Also handle the side-nav Admin login form here.
if (isset($_SERVER['REQUEST_METHOD']) && strtoupper((string)$_SERVER['REQUEST_METHOD']) === 'POST') {
    // Cancel just returns to a clean GET.
    if (isset($_POST['adcancel'])) {
        magxRedirect('index.php');
    }

    if (isset($_POST['adlogin'])) {
        $csrfToken = (string)($_POST['csrf_token'] ?? '');
        if (!magx_valid_csrf($csrfToken)) {
            magxSetFlash('Security validation failed. Please try again.');
            magxRedirect('index.php?admin=1');
        }

        $adminuser = trim((string)($_POST['aduser'] ?? ''));
        $adminpass = (string)($_POST['adpass'] ?? '');
        $bu = (string)($_POST['businessunit'] ?? 'Admin');

        if ($adminuser === '') {
            magxSetFlash('Kindly type your username.');
            magxRedirect('index.php?admin=1');
        }
        if ($adminpass === '') {
            magxSetFlash('Kindly type your password.');
            magxRedirect('index.php?admin=1');
        }

        // Basic lockout (5 attempts / 5 minutes) using the session-based tracker already in this file.
        if (checkLoginAttempts($adminuser, 'admin') >= 5) {
            magxSetFlash('Too many login attempts. Please wait 5 minutes and try again.');
            magxRedirect('index.php?admin=1');
        }

        $db = magx_db_connect();
        if (!$db) {
            magxSetFlash('Database connection failed.');
            magxRedirect('index.php?admin=1');
        }
        $found = false;
        $dbPass = null;
        try {
            $stmt = magx_db_execute($db, 'SELECT adminpass FROM tbl_acc WHERE adminuser = :u LIMIT 1', [
                ':u' => $adminuser
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && array_key_exists('adminpass', $row)) {
                $dbPass = (string)$row['adminpass'];
                $found = true;
            }
        } catch (Throwable $e) {
            magxSetFlash('Login is temporarily unavailable.');
            magxRedirect('index.php?admin=1');
        }

        $ok = false;
        if ($found) {
            $ok = hash_equals((string)$dbPass, (string)$adminpass);
        }

        if (!$ok) {
            recordFailedAttempt($adminuser, 'admin');
            magxSetFlash('Invalid username or password.');
            magxRedirect('index.php?admin=1');
        }

        clearLoginAttempts($adminuser, 'admin');
        $_SESSION['magx_admin_authenticated'] = true;
        $_SESSION['adminuser'] = $adminuser;
        magx_issue_admin_cookie($adminuser);

        // Log the login (same table used by the legacy Main.php flow).
        date_default_timezone_set('Asia/Manila');
        $log_date = date('Y-m-d');
        $log_time = date('h:i:s A');

        try {
            magx_db_execute($db, 'INSERT INTO tbl_logss (nameofuser, oras, petsa, unit) VALUES (:n, :o, :p, :u)', [
                ':n' => $adminuser,
                ':o' => $log_time,
                ':p' => $log_date,
                ':u' => $bu,
            ]);
        } catch (Throwable $e) {
            // Ignore logging failure for successful login.
        }

        magxRedirect('admain.php');
    }

    // Any unexpected POST: bounce to GET to prevent resubmission prompt.
    magxRedirect('index.php');
}

?>




<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MAGX Solutions</title>
	        <link rel="icon" type="png" href="withbacklogo.jpg">
	        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" onerror="this.href='assets/css/bootstrap.min.css';">
	        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600&display=swap">
	        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@600&family=Orbitron:wght@600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap">
	        
	        <style>
	        :root{
	            --magx-primary: #0f55c8;
            --magx-accent: #00aaff;
            --magx-surface: rgba(10, 18, 35, 0.86);
            --magx-panel: rgba(255, 255, 255, 0.07);
            --magx-border: rgba(255, 255, 255, 0.12);
            --magx-text: #e9edf5;
            --magx-shadow: 0 18px 50px rgba(0, 0, 0, 0.35);
        }
           
        
        #mainnav{
            background: linear-gradient(
                90deg,
                #f5f7fa 0%,
                #e9edf3 50%,
                #f5f7fa 100%
            );

            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: fixed;
            width: 100vw;
            height: 40px;
            z-index: 800;

            /* optional premium touch */
            border-bottom: 1px solid rgba(0,0,0,0.05);
            backdrop-filter: blur(6px);
        }

       

        #btnnav{
            top: 40px;
            align-items: center;
        }
        #btnnav .btn {
            transition: 0.3s;
            border: none;
        }
        #srow {
            margin-right: 50px;  
            gap: 50px; 
        }

        #frow {
            margin-left: 50px;  
            gap: 50px; 
        }

        .bt{ 
            width: 150px; 
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }
        /* Only apply the blurred dark backing to the top-nav buttons (not all Bootstrap .btn) */
        #adlogin::before,#adcancel::before,.bt::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            clip-path: inherit;
            background-color: black;
            opacity: 0.3;
            transform: translate(0px, 0px); 
            filter: blur(5px); 
            z-index: -1;
        }
        #adlogin,#adcancel,.bt{
            position: relative;
            overflow: hidden;
        }
        #adlogin:hover,#adcancel:hover,.bt:hover{
            box-shadow: 0 0 20px 4px rgba(0, 170, 255, 0.7),
            0 0 40px 8px rgba(0, 170, 255, 0.4);
            transform: scale(1.05); 
         }

        .bt.active,
        .bt:focus {
            background: linear-gradient(90deg, #0066cc, #00aaff);
            color: #fff;
            box-shadow: 0 0 25px 5px rgba(0, 170, 255, 0.9),
                        0 0 50px 10px rgba(0, 170, 255, 0.6);
            transform: scale(0.98);
        }

        
       


        .sidenav::before {
            color: white;
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(
                circle at var(--x, 50%) var(--y, 50%),
                rgba(0, 170, 255, 0.6) 0%,     
                rgba(0, 102, 204, 0.42) 60%,    
                transparent 80%
            );
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidenav:hover::before {
            color: white;
            opacity: 1;
        }

        .sidenav .span {
            position: relative;
            z-index: 1;
        }
        
          

        .poly-center {
            cursor: pointer;
            color: #1f2937; /* dark gray text, readable on light bg */
            font-size: 50px;

            display: flex;
            justify-content: center;
            align-items: center;

            position: fixed;
            left: 50%;
            transform: translateX(-50%);

            width: 250px;
            height: 90px;

            /* light glass + tech gradient */
            background: linear-gradient(
                90deg,
                #f5f7fa 0%,
                #e9edf3 50%,
                #f5f7fa 100%
            );

            /* soft light shadow (not black) */
            box-shadow: 
                0 8px 20px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);

            backdrop-filter: blur(8px);
            z-index: 1000;

            clip-path: path('M0,0 H250 Q212,90 187,90 H63 Q38,90 0,0 Z');
        }
        .poly-center img {
            width: 100%;
            height: 100%;

            object-fit: contain;
            object-position: center;

            transform: scale(1.50);   /* adjust: 1.15 â€“ 1.4 */
            transform-origin: center;

            pointer-events: none;
        }


	        @media (max-width: 768px) {
            .poly-center img {
                max-height: 70px;
                max-width: 180px;  
            }
        }

        @media (max-width: 480px) {
            .poly-center img {
                max-height: 60px;  
                max-width: 160px;   
            }
        }



       

       
         
	       #bodycontainer {
	            color: white;
	            font-family: 'Poppins', sans-serif;
	            text-align: center;
	            /* Offset the fixed top bar (mainnav is 40px tall) */
	            padding: 40px 20px 0;
	            box-sizing: border-box;
	            overflow: hidden; /* sections manage their own scrolling */
	            overflow-x: hidden;
	            min-height: 100vh;
	            height: 100vh; /* scroll inside bodycontainer, not the whole page */
	            position: relative;
	        }

	        /* Remove the browser/page scrollbar (scroll happens in #bodycontainer instead) */
	        html, body {
	            height: 100%;
	            overflow: hidden;
	        }

	        /* Hide the scrollbar visuals but keep scrolling enabled */
	        #bodycontainer {
	            scrollbar-width: none;      /* Firefox */
	            -ms-overflow-style: none;   /* IE/Edge legacy */
	        }
	        #bodycontainer::-webkit-scrollbar {
	            width: 0;
	            height: 0;
	        }

        /* Services section should behave like other full-height sections */
        #servicescon{
            overflow-y: auto;
            overflow-x: hidden;
            height: calc(100vh - 40px);
            padding: 60px 30px;
            scroll-behavior: smooth;
            background: transparent;
            position: relative;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        #servicescon::before {
            content: "";
            position: absolute;
            inset: 16px;
            border-radius: 40px;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
            filter: blur(40px);
            opacity: 0.65;
        }
        .services-panel {
            background:
                radial-gradient(700px 320px at 85% -10%, rgba(0,170,255,0.25), transparent 62%),
                radial-gradient(520px 260px at 15% 110%, rgba(0,92,190,0.28), transparent 68%),
                linear-gradient(180deg, rgba(6,22,52,0.95), rgba(4,12,30,0.95));
            border-radius: 36px;
            padding: 36px;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow:
                0 34px 70px rgba(0,0,0,0.38),
                inset 0 1px 0 rgba(255,255,255,0.18);
            display: flex;
            flex-direction: column;
            gap: 32px;
            min-height: calc(100vh - 120px);
            position: relative;
            overflow: hidden;
        }
        .services-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(
                circle at var(--x, 50%) var(--y, 50%),
                rgba(0, 170, 255, 0.2) 0%,
                rgba(0, 102, 204, 0.12) 45%,
                transparent 85%
            );
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .services-panel:hover::before {
            opacity: 1;
        }
        .services-hero-card {
            background: rgba(4,12,30,0.8);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid rgba(0,170,255,0.25);
            box-shadow:
                0 20px 48px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.12);
        }
        .services-hero-card .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.35em;
            font-size: 12px;
            color: rgba(0,170,255,0.9);
            margin-bottom: 16px;
        }
        .services-hero-card h2 {
            color: #e9f5ff;
            font-size: clamp(28px, 4vw, 40px);
            margin: 0 0 16px;
            letter-spacing: -0.5px;
        }
        .services-hero-card p {
            color: rgba(233, 245, 255, 0.85);
            font-size: 16px;
            line-height: 1.9;
            margin-bottom: 24px;
        }
        .services-hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }
        .service-cta {
            border-radius: 999px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.08em;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .service-cta.primary {
            background: linear-gradient(135deg, #00aaff, #0066cc);
            color: white;
            border-color: rgba(255,255,255,0.2);
            box-shadow: 0 10px 30px rgba(0,170,255,0.45);
        }
        .service-cta.ghost {
            background: transparent;
            color: #e9f5ff;
            border-color: rgba(0,170,255,0.4);
        }
        .service-cta:hover {
            transform: translateY(-2px) scale(1.005);
            box-shadow: 0 16px 30px rgba(0,170,255,0.35);
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 26px;
        }
        .services-card {
            background: rgba(255,255,255,0.96);
            border-radius: 20px;
            padding: 28px;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow:
                0 20px 50px rgba(0,0,0,0.12),
                inset 0 1px 0 rgba(255,255,255,0.8);
            display: flex;
            gap: 18px;
            align-items: flex-start;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            min-height: 200px;
        }
        .services-card-icon {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 18px;
            background: linear-gradient(145deg, rgba(0,170,255,0.95), rgba(0,102,204,0.9));
            box-shadow: 0 12px 30px rgba(0,170,255,0.4);
            display: grid;
            place-items: center;
            font-size: 20px;
            color: white;
        }
        .services-card-body h3 {
            margin: 0 0 10px;
            font-size: 20px;
            color: #0f1b33;
        }
        .services-card-body p {
            margin: 0;
            color: #2c3b4a;
            line-height: 1.75;
            font-size: 15px;
        }
        .services-card:hover {
            transform: translateY(-6px);
            border-color: rgba(0,170,255,0.4);
            box-shadow:
                0 24px 70px rgba(0,0,0,0.28),
                inset 0 1px 0 rgba(255,255,255,0.8);
            z-index: 2;
        }
        .services-card:hover .services-card-icon {
            box-shadow:
                0 16px 30px rgba(0,170,255,0.5),
                0 0 12px rgba(0,170,255,0.45);
            transform: scale(1.05);
        }
        .empty-card {
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: rgba(255,255,255,0.12);
            color: #e1edff;
            border: 1px dashed rgba(255,255,255,0.45);
        }
        .empty-card .empty-title {
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }
        .empty-card .empty-copy {
            font-size: 15px;
            color: rgba(233,245,255,0.8);
        }
        
        #bodycontainer::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
            pointer-events: none;
        }
        /* Only the background video (direct child of #bodycontainer) should be absolutely positioned. */
        #bodycontainer > video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover; /* like background-size: cover */
            transform: translate(-50%, -50%);
            z-index: -1; /* behind content */
            pointer-events: none;
        }
        
        #bodycontainer > * {
            position: relative;
            z-index: 1;
        }

        /* Bootstrap modals must not inherit bodycontainer stacking/positioning rules */
        #bodycontainer > .modal {
            position: fixed;
            z-index: 1055;
        }

        /* Sections */
        .section {
            padding: 45px 20px;
            min-height: 100vh;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            animation: fadeInUp 1s ease both;
            
        }
        
        .section.home {
            min-height: auto;
            height: auto;
            
        }
        .section.dark {
            background: linear-gradient(145deg, rgba(245, 247, 250, 0.95), rgba(233, 237, 243, 0.98));
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .section.darker {
            background: linear-gradient(145deg, rgba(233, 237, 243, 0.98), rgba(220, 230, 240, 0.95));
        }
        
        /* About Section Styling - Modern Design */
        #aboutcon {
            background: transparent !important;
            backdrop-filter: none;
            box-shadow: none;
            padding: 80px 60px;
            border-radius: 30px;
            margin: 20px;
            border: none;
            overflow-y: auto;
            overflow-x: hidden;
            height: calc(100vh - 40px);
            scroll-behavior: smooth;
            position: relative;
        }
        
        
        #aboutcon::-webkit-scrollbar {
            width: 8px;
        }
        
        #aboutcon::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            margin: 10px 0;
        }
        
        #aboutcon::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #0066cc, #00aaff);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        #aboutcon::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #00aaff, #0066cc);
            box-shadow: 0 0 10px rgba(0, 170, 255, 0.5);
        }
        
        /* Modern Card Container */
        .about-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.08),
                0 2px 8px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 170, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .about-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 170, 255, 0.05), transparent);
            transition: left 0.6s ease;
        }
        
        .about-card:hover::before {
            left: 100%;
        }
        
        .about-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 
                0 20px 60px rgba(0, 170, 255, 0.15),
                0 4px 12px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border-color: rgba(0, 170, 255, 0.3);
        }
        
        /* Modern Heading Styles */
        #aboutcon h2 {
            color: #1a1a2e;
            font-size: 32px;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
            position: relative;
            padding-left: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        #aboutcon h2::before {
            content: "";
            width: 5px;
            height: 40px;
            background: linear-gradient(180deg, #0066cc, #00aaff);
            border-radius: 10px;
            position: absolute;
            left: 0;
            transition: all 0.3s ease;
        }
        
        
        #aboutcon h2:hover {
            color: #0066cc;
            padding-left: 25px;
        }
        
        #aboutcon h2:hover::before {
            width: 8px;
            height: 45px;
            box-shadow: 0 0 20px rgba(0, 170, 255, 0.5);
        }
        
        /* Modern Paragraph Styles */
        #aboutcon p {
            color: #2d3748;
            font-size: 17px;
            line-height: 1.9;
            margin-bottom: 0;
            text-align: left;
            font-weight: 400;
            letter-spacing: 0.2px;
        }
        
        
        /* Icon Integration */
        .about-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0066cc, #00aaff);
            border-radius: 12px;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0, 170, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .about-card:hover .about-icon {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 170, 255, 0.4);
        }
        
        /* Section Spacing */
        #aboutcon > * {
            animation: fadeInUp 0.6s ease backwards;
        }
        
        #aboutcon > *:nth-child(1) { animation-delay: 0.1s; }
        #aboutcon > *:nth-child(2) { animation-delay: 0.2s; }
        #aboutcon > *:nth-child(3) { animation-delay: 0.3s; }
        #aboutcon > *:nth-child(4) { animation-delay: 0.4s; }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

	        /* Home */
	        #title {
	            position: relative; /* anchors the scroll hint overlay */
	            overflow-y: auto;
	            overflow-x: hidden;
	            height: calc(100vh - 40px);
	            padding: 20px;
	            scroll-behavior: smooth;
            
        }

        /* MAGX AI chat widget (fresh layout) */
        #magx-chat-root{
            position: fixed;
            right: 22px;
            bottom: 52px;
            z-index: 1400;
            font-family: 'Inter', 'Poppins', system-ui, sans-serif;
        }
        #magx-chat-root, #magx-chat-root *{
            box-sizing: border-box;
            text-align: left;
        }

	        .magx-chat-button{
	            width: 60px;
	            height: 60px;
	            border-radius: 999px;
	            border: 1px solid rgba(255,255,255,0.12);
	            background: linear-gradient(135deg, rgba(0,102,204,0.95), rgba(0,170,255,0.70));
	            color: #fff;
	            display: grid;
	            place-items: center;
	            cursor: pointer;
	            box-shadow: 0 18px 50px rgba(0,0,0,0.38);
	            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
	            position: relative;
	            overflow: hidden;
	        }
	        .magx-chat-button i{
	            font-size: 22px;
	            line-height: 1;
	            opacity: 0; /* show the bot avatar by default */
	            transition: opacity 0.14s ease;
	        }
	        .magx-chat-button.magx-chat-fallback i{
	            opacity: 1; /* if avatar fails to load */
	        }
	        .magx-chat-toggle-avatar{
	            position: absolute;
	            inset: 7px;
	            border-radius: 999px;
	            overflow: hidden;
	            display: block;
	            background:
	                radial-gradient(18px 18px at 30% 25%, rgba(255,255,255,0.22), transparent 55%),
	                radial-gradient(46px 34px at 60% 85%, rgba(0,170,255,0.18), transparent 62%);
	        }
	        .magx-chat-button.magx-chat-fallback .magx-chat-toggle-avatar{ display: none; }
	        .magx-chat-toggle-avatar img{
	            width: 100%;
	            height: 100%;
	            display: block;
	            object-fit: cover;
	            transform: scale(1.12);
	            transform-origin: 50% 50%;
	        }
        .magx-chat-button:hover{
            transform: translateY(-2px);
            filter: saturate(1.08);
            box-shadow: 0 26px 70px rgba(0,0,0,0.44);
        }
        .magx-chat-button.open{
            box-shadow: 0 20px 56px rgba(0,0,0,0.40);
        }

        .magx-chat-window{
            position: fixed;
            right: 22px;
            bottom: 96px;
            width: 390px;
            height: min(680px, 76vh);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.12);
            background: radial-gradient(1100px 520px at 20% 0%, rgba(0,170,255,0.22), transparent 60%),
                        linear-gradient(180deg, rgba(15,23,42,0.92), rgba(2,6,23,0.92));
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 30px 90px rgba(0,0,0,0.58);
            opacity: 0;
            transform: translateY(10px) scale(0.98);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        .magx-chat-window.open{
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .magx-chat-header{
            flex: 0 0 auto;
            padding: 14px 14px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.10);
            background: radial-gradient(1200px 420px at 10% 0%, rgba(0,170,255,0.30), transparent 60%),
                        radial-gradient(900px 320px at 90% 0%, rgba(0,102,204,0.22), transparent 60%);
        }

        .magx-chat-titleblock{ min-width: 0; }
        .magx-chat-titlemain{
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .magx-chat-title{
            font-weight: 800;
            font-size: 15px;
            letter-spacing: 0.2px;
            color: rgba(255,255,255,0.96);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .magx-chat-presence{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255,255,255,0.80);
            flex: 0 0 auto;
            margin-left: auto;
        }
        .magx-status-dot{
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 0 6px rgba(74,222,128,0.18);
        }
       

        .magx-chat-actions{
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .magx-chat-actions button{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.92);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: transform 0.14s ease, background 0.14s ease, border-color 0.14s ease;
        }
        .magx-chat-actions button:hover{
            transform: translateY(-1px);
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.22);
        }
        .magx-chat-actions i{ font-size: 14px; line-height: 1; }

        .magx-chat-body{
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        .magx-messages{
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 14px 14px 18px 14px;
            display: grid;
            gap: 10px;
        }
        .magx-messages{
            scrollbar-width: thin;
            scrollbar-color: rgba(0,170,255,0.70) rgba(255,255,255,0.06);
        }
        .magx-messages::-webkit-scrollbar{ width: 8px; }
        .magx-messages::-webkit-scrollbar-track{
            background: rgba(255,255,255,0.06);
            border-radius: 10px;
        }
        .magx-messages::-webkit-scrollbar-thumb{
            background: linear-gradient(180deg, rgba(0,102,204,0.95), rgba(0,170,255,0.85));
            border-radius: 10px;
        }

        .magx-message{
            width: 100%;
            display: flex;
            gap: 10px;
            align-items: flex-end;
            justify-content: flex-start;
        }
        .magx-message.user{
            justify-content: flex-end;
            flex-direction: row;
        }
        .magx-message.user .magx-bubble{
            order: 1;
        }
        .magx-message.user .magx-avatar{
            order: 2;
        }

	        .magx-avatar{
	            width: 36px;
	            height: 36px;
	            border-radius: 999px;
	            border: 1px solid rgba(255,255,255,0.14);
	            background: rgba(255,255,255,0.08);
	            overflow: hidden;
	            flex: 0 0 auto;
	            display: grid;
	            place-items: center;
	        }
	        .magx-avatar img{
	            width: 100%;
	            height: 100%;
	            display: block;
	            object-fit: cover; /* default: fill the circle nicely */
	            background: transparent;
	        }
	        .magx-avatar-logo{
	            padding: 4px;
	            background: rgba(0,170,255,0.10);
	            border-color: rgba(0,170,255,0.22);
	        }
	        .magx-avatar-logo img{
	            object-fit: contain; /* keep logo fully visible */
	        }
	        .magx-avatar-bot{
	            padding: 0;
	            background:
	                radial-gradient(18px 18px at 30% 25%, rgba(255,255,255,0.22), transparent 55%),
	                radial-gradient(40px 30px at 60% 85%, rgba(0,170,255,0.18), transparent 60%),
	                rgba(0,170,255,0.10);
	            border-color: rgba(0,170,255,0.30);
	            box-shadow: 0 0 0 1px rgba(0,170,255,0.12), 0 10px 26px rgba(0,0,0,0.36);
	        }
	        .magx-avatar-bot img{
	            transform: scale(1.14);
	            transform-origin: 50% 50%;
	        }
	        .magx-avatar-me{
	            padding: 0;
	            background:
	                radial-gradient(20px 18px at 30% 25%, rgba(255,255,255,0.22), transparent 55%),
	                radial-gradient(52px 32px at 60% 85%, rgba(59,130,246,0.24), transparent 62%),
	                linear-gradient(135deg, rgba(30,64,175,0.92), rgba(2,132,199,0.70));
	            border-color: rgba(255,255,255,0.18);
	            box-shadow: 0 0 0 1px rgba(255,255,255,0.08), 0 10px 26px rgba(0,0,0,0.34);
	        }
	        .magx-avatar-text{
	            display: grid;
	            place-items: center;
	            width: 100%;
	            height: 100%;
	            font-weight: 900;
	            font-size: 11px;
	            letter-spacing: 0.8px;
	            color: rgba(255,255,255,0.96);
	            text-shadow: 0 2px 10px rgba(0,0,0,0.45);
	            user-select: none;
	        }

        .magx-bubble{
            max-width: 78%;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.92);
            font-size: 14px;
            line-height: 1.5;
        }
        .magx-message.user .magx-bubble{
            background: linear-gradient(135deg, rgba(0,102,204,0.92), rgba(0,170,255,0.66));
            border-color: rgba(255,255,255,0.08);
            box-shadow: 0 18px 38px rgba(0,0,0,0.36);
            color: #fff;
        }
        .magx-bubble p{ margin: 0 0 8px 0; }
        .magx-bubble p:last-child{ margin-bottom: 0; }
        .magx-bubble a{ color: rgba(125,211,252,0.95); text-decoration: none; }
        .magx-bubble a:hover{ text-decoration: underline; }
        .magx-time{
            margin-top: 8px;
            font-size: 11px;
            opacity: 0.72;
        }

        .magx-chat-footer{
            flex: 0 0 auto;
            border-top: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.04);
        }
        .magx-quick{
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            padding: 10px 12px 6px 12px;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .magx-quick::-webkit-scrollbar{ height: 0; }
        .magx-quick button{
            flex: 0 0 auto;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.92);
            padding: 8px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 650;
            cursor: pointer;
            transition: transform 0.14s ease, background 0.14s ease, border-color 0.14s ease;
        }
        .magx-quick button:hover{
            transform: translateY(-1px);
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.22);
        }

        .magx-input-bar{
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
        }
        .magx-input{
            flex: 1;
            height: 44px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.92);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .magx-input:focus{
            border-color: rgba(0,170,255,0.78);
            box-shadow: 0 0 0 3px rgba(0,170,255,0.16);
        }
        .magx-send{
            width: 46px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.10);
            background: linear-gradient(135deg, rgba(0,102,204,0.95), rgba(0,170,255,0.72));
            color: #fff;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: transform 0.14s ease, box-shadow 0.14s ease;
        }
        .magx-send:hover{
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.26);
        }

        .magx-message.typing .magx-bubble{
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }
        .magx-message.typing .dot{
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.76);
            animation: magxBounce 1s infinite;
        }
        .magx-message.typing .dot:nth-child(2){ animation-delay: 0.15s; }
        .magx-message.typing .dot:nth-child(3){ animation-delay: 0.30s; }
        @keyframes magxBounce{
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-6px); opacity: 1; }
        }

        @media (max-width: 576px){
            #magx-chat-root{ right: 16px; bottom: 46px; }
            .magx-chat-window{
                right: 16px;
                bottom: 92px;
                width: calc(100% - 32px);
                height: 72vh;
            }
        }
        
		        #contactcontainer{
		            overflow-y: auto;
		            overflow-x: hidden;
		            height: calc(100vh - 40px);
		            scroll-behavior: smooth;
		            padding: 60px 20px;
		            background: rgba(255,255,255,0.08);
		            backdrop-filter: blur(14px);
		            -webkit-backdrop-filter: blur(14px);
	            box-shadow: inset 0 0 80px rgba(0,0,0,0.12);
	        }
        #contactcontainer::-webkit-scrollbar {
            width: 8px;
        }
        
        #contactcontainer::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        
        #contactcontainer::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #0066cc, #00aaff);
            border-radius: 10px;
        }
        
        #contactcontainer::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #00aaff, #0066cc);
        }

        #developer {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 18px 60px rgba(0,0,0,0.25);
            border-radius: 28px;
            padding: 28px;
        }


	        /* body scrollbar styling removed (page scrolling is disabled above) */
















		        /* Hide scrollbar visuals across the app (scrolling still works). */
		        #bodycontainer,
		        #title,
		        #aboutcon,
		        #servicescon,
		        #contactcontainer{
		            scrollbar-width: none;      /* Firefox */
		            -ms-overflow-style: none;   /* IE/Edge legacy */
		        }
		        #bodycontainer::-webkit-scrollbar,
		        #title::-webkit-scrollbar,
		        #aboutcon::-webkit-scrollbar,
		        #servicescon::-webkit-scrollbar,
		        #contactcontainer::-webkit-scrollbar{
		            width: 0;
		            height: 0;
		        }
        
		        /* Home hero (WELCOME TO / title / tagline / CTAs / chips) */
				        #title{
				            --home-shell-width: min(1240px, 100%);
				        }
				        #title .welcome-header{
			            /* One knob to reduce hero height while keeping proportions. */
			            --hero-scale: 0.90;
			            position: relative;
			            /* Use container-relative sizing to avoid horizontal overflow on mobile */
				            width: var(--home-shell-width);
				            max-width: 1240px;
			            margin: calc(clamp(56px, 8vh, 96px) * var(--hero-scale, 1)) auto calc(72px * var(--hero-scale, 1));
			            padding: calc(clamp(20px, 3.8vw, 36px) * var(--hero-scale, 1)) calc(clamp(16px, 2.6vw, 30px) * var(--hero-scale, 1));
			            border-radius: calc(28px * var(--hero-scale, 1));
			            text-align: center;
			            isolation: isolate;
			            animation: heroIn 0.9s cubic-bezier(0.2, 0.9, 0.2, 1) both;
			            box-sizing: border-box;
			
			            /* glass surface so the hero reads clearly over the video */
			            background:
			                radial-gradient(900px 380px at 50% 0%, rgba(0,170,255,0.16), transparent 60%),
			                linear-gradient(180deg, rgba(10,18,35,0.40), rgba(10,18,35,0.22));
			            border: 1px solid rgba(255,255,255,0.10);
			            box-shadow: 0 30px 90px rgba(0,0,0,0.38);
			            backdrop-filter: blur(10px);
			            -webkit-backdrop-filter: blur(10px);
			        }

			        /* Hero layout: left robot card + right copy card */
			        #title .hero-grid{
			            width: 100%;
			            display: grid;
			            gap: calc(clamp(16px, 3vw, 28px) * var(--hero-scale, 1));
			            align-items: center;
			        }
			        #title .hero-media{
			            display: flex;
			            justify-content: center;
			            align-items: center;
			        }
			        #title .hero-media-card{
			            position: relative;
			            border-radius: calc(22px * var(--hero-scale, 1));
			            overflow: hidden;
			            border: 0; /* no static border; neon streaks provide the edge treatment */
			            background: rgba(255,255,255,0.04);
			            box-shadow: 0 22px 70px rgba(0,0,0,0.30);
			            aspect-ratio: 4 / 5;
			            width: clamp(220px, 62vw, 360px);
			            max-width: 100%;
			        }
			        #title .hero-robot-video{
			            width: 100%;
			            height: 100%;
			            display: block;
			            object-fit: cover;
			            object-position: center;
			            filter: saturate(1.06) contrast(1.03);
			        }

			        /* Two neon streaks around the perimeter (same direction, opposite sides) */
			        #title .hero-media-card .hero-neon-frame{
			            position: absolute;
			            inset: 0;
			            width: 100%;
			            height: 100%;
			            z-index: 2;
			            pointer-events: none;
			            border-radius: inherit;
			            overflow: visible;
			        }
			        #title .hero-media-card .hero-neon-glow{
			            stroke: rgba(0,170,255,0.55);
			            stroke-width: 2.4;
			            stroke-linecap: round;
			            stroke-dasharray: 160 840; /* longer segment + long gap (pathLength=1000) */
			        }
			        #title .hero-media-card .hero-neon-core{
			            stroke: rgba(210,250,255,0.95);
			            stroke-width: 1;
			            stroke-linecap: round;
			            stroke-dasharray: 160 840;
			        }
			        #title .hero-media-card .hero-neon-a{ animation: heroNeonRun 3.4s linear infinite; }
			        #title .hero-media-card .hero-neon-b{ animation: heroNeonRunOffset 3.4s linear infinite; }
			        @keyframes heroNeonRun{ from { stroke-dashoffset: 0; } to { stroke-dashoffset: -1000; } }
			        @keyframes heroNeonRunOffset{ from { stroke-dashoffset: -500; } to { stroke-dashoffset: -1500; } }
			        @media (prefers-reduced-motion: reduce){
			            #title .hero-media-card .hero-neon-a,
			            #title .hero-media-card .hero-neon-b{ animation: none; }
			        }

			        #title .hero-copy{
			            min-width: 0;
			            display: flex;
			        }
			        #title .hero-copy-card{
			            width: 100%;
			            height: 100%;
			            display: flex;
			            flex-direction: column;
			            justify-content: center;
			            align-items: center;
			            padding: calc(22px * var(--hero-scale, 1));
			            border-radius: calc(22px * var(--hero-scale, 1));
			            border: 1px solid rgba(255,255,255,0.12);
			            background:
			                radial-gradient(700px 260px at 20% 0%, rgba(0,170,255,0.12), transparent 60%),
			                linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
			            box-shadow: 0 22px 70px rgba(0,0,0,0.26);
			            backdrop-filter: blur(14px);
			            -webkit-backdrop-filter: blur(14px);
			            box-sizing: border-box;
			        }

				        @media (min-width: 992px){
				            #title .hero-grid{
				                grid-template-columns: minmax(260px, 420px) 1fr;
				                align-items: stretch;
				            }
				            #title .hero-media{ justify-content: flex-start; }
				            #title .hero-media-card{ width: min(360px, 100%); }
				            /* Cap the headline size when the split layout is active. */
				            #title .hero-title{ font-size: calc(clamp(30px, 3.2vw, 46px) * var(--hero-scale, 1)); }
				        }
			        #title .welcome-header::before{
			            content:"";
			            position:absolute;
			            inset:-1px;
		            border-radius: inherit;
		            z-index:-1;
		            background:
		                radial-gradient(800px 420px at 20% 0%, rgba(0,170,255,0.34), transparent 60%),
		                radial-gradient(700px 360px at 90% 10%, rgba(0,102,204,0.28), transparent 65%),
		                linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.00));
		            opacity: 0.9;
		        }
		        #title .welcome-header::after{
		            content:"";
		            position:absolute;
		            inset: 0;
		            border-radius: inherit;
		            z-index:-1;
		            pointer-events: none;
		            background:
		                linear-gradient(120deg, rgba(255,255,255,0.06), rgba(255,255,255,0.00) 45%, rgba(255,255,255,0.04));
		            mask-image: radial-gradient(circle at 50% 20%, #000 0%, rgba(0,0,0,0.25) 55%, transparent 78%);
		            opacity: 0.85;
		        }

			        #title h2{
			            font-family: 'Space Grotesk','Poppins',system-ui,sans-serif;
			            font-weight: 700;
			            font-size: calc(14px * var(--hero-scale, 1));
			            letter-spacing: 0.38em;
			            text-transform: uppercase;
			            color: rgba(255,255,255,0.92);
			            margin: 0 0 calc(14px * var(--hero-scale, 1));
			            text-shadow: 0 10px 40px rgba(0,170,255,0.22);
			        }

				        #title .hero-title{
				            position: relative;
				            display: inline-block;
				            font-family: 'Orbitron','Space Grotesk','Poppins',system-ui,sans-serif;
				            font-weight: 800;
				            font-size: calc(clamp(28px, 4.2vw, 54px) * var(--hero-scale, 1));
				            line-height: 1.02;
				            letter-spacing: 0.01em;
				            margin: calc(16px * var(--hero-scale, 1)) 0;
				            white-space: normal;
				            color: rgba(255,255,255,0.98);
				            text-transform: uppercase;
				            -webkit-text-stroke: 1px rgba(0,170,255,0.10);
				            text-shadow:
				                0 18px 70px rgba(0,0,0,0.55),
				                0 0 28px rgba(0,170,255,0.12);
				        }
				        /* Electric-blue streak pass over the title (animated overlay only). */
				        #title .hero-title::after{
				            content: attr(data-text);
				            position: absolute;
				            inset: 0;
				            pointer-events: none;
				            white-space: inherit;
				            color: transparent;
				            -webkit-text-fill-color: transparent;
				            background-image: linear-gradient(
				                90deg,
				                transparent 0%,
				                transparent 40%,
				                rgba(0,170,255,0.00) 44%,
				                rgba(0,170,255,0.80) 47%,
				                rgba(230, 251, 255, 1.00) 50%,
				                rgba(0,170,255,0.55) 53%,
				                rgba(0,170,255,0.00) 57%,
				                transparent 60%,
				                transparent 100%
				            );
				            background-size: 320% 100%;
				            background-position: -160% 50%;
				            -webkit-background-clip: text;
				            background-clip: text;
				            filter: drop-shadow(0 0 16px rgba(0,170,255,0.40)) drop-shadow(0 0 54px rgba(0,170,255,0.18));
				            opacity: 0.95;
				            will-change: background-position, opacity, filter;
				            animation: magxElectricPass 7.2s linear infinite;
				        }
				        @keyframes magxElectricPass{
				            from { background-position: -160% 50%; }
				            to { background-position: 160% 50%; }
				        }
				        @media (prefers-reduced-motion: reduce){
				            #title .hero-title::after{
				                animation: none;
				                opacity: 0.55;
				            }
				        }

			        #title hr{
			            width: min(680px, 72%);
			            height: calc(2px * var(--hero-scale, 1));
			            border: none;
			            margin: calc(14px * var(--hero-scale, 1)) auto;
			            border-radius: 999px;
			            background: linear-gradient(90deg, transparent, rgba(0,170,255,0.55), rgba(255,255,255,0.26), rgba(0,170,255,0.55), transparent);
			            box-shadow: 0 0 18px rgba(0,170,255,0.22);
			        }

			        #title .hero-kicker{
			            display: flex;
			            align-items: center;
			            justify-content: center;
			            gap: 10px;
			            /* Wide pill, centered */
			            width: min(720px, 100%);
			            padding: calc(10px * var(--hero-scale, 1)) calc(14px * var(--hero-scale, 1));
			            margin: calc(8px * var(--hero-scale, 1)) auto 0;
			            border-radius: 999px;
			            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			            font-size: calc(12px * var(--hero-scale, 1));
			            letter-spacing: 0.18em;
			            text-transform: uppercase;
			            color: rgba(255,255,255,0.86);
			            background: rgba(0,0,0,0.18);
		            border: 1px solid rgba(255,255,255,0.10);
		            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10);
		        }

			        #title .hero-tagline{
			            max-width: 760px;
			            margin: calc(14px * var(--hero-scale, 1)) auto 0;
			            color: rgba(255,255,255,0.88);
			            font-size: calc(clamp(14px, 1.35vw, 16px) * var(--hero-scale, 1));
			            line-height: 1.7;
			            letter-spacing: 0.2px;
			            text-shadow: 0 2px 14px rgba(0,0,0,0.55);
			        }

			        #title .hero-cta{
			            margin-top: calc(22px * var(--hero-scale, 1));
			            width: 100%;
			            display: flex;
			            justify-content: center;
			            gap: calc(12px * var(--hero-scale, 1));
			            flex-wrap: wrap;
			        }

			        #title .hero-cta .btn{
			            position: relative;
			            border-radius: 999px;
			            padding: calc(12px * var(--hero-scale, 1)) calc(18px * var(--hero-scale, 1));
			            font-weight: 800;
			            font-size: calc(1rem * var(--hero-scale, 1));
			            letter-spacing: 0.4px;
			            background: rgba(255,255,255,0.06);
			            border: 1px solid rgba(255,255,255,0.12);
			            color: rgba(255,255,255,0.96);
		            backdrop-filter: blur(14px);
		            -webkit-backdrop-filter: blur(14px);
		            box-shadow: 0 14px 40px rgba(0,0,0,0.22);
		            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease, filter 0.18s ease;
		            outline: none;
		        }
		        #title .hero-cta .btn::after{
		            content:"";
		            position:absolute;
		            inset:-1px;
		            border-radius: inherit;
		            pointer-events:none;
		            background: radial-gradient(380px 120px at 30% 0%, rgba(255,255,255,0.14), transparent 55%);
		            opacity: 0.8;
		        }

		        #title .hero-cta .btn-primary{
		            background: linear-gradient(135deg, rgba(0,102,204,0.92), rgba(0,170,255,0.70));
		            border-color: rgba(0,170,255,0.28);
		            box-shadow: 0 18px 54px rgba(0,170,255,0.20), 0 14px 40px rgba(0,0,0,0.24);
		        }

		        #title .hero-cta .btn-outline-light{
		            border-color: rgba(0,170,255,0.20);
		            background: rgba(0,0,0,0.16);
		        }

		        #title .hero-cta .btn:hover{
		            transform: translateY(-1px);
		            border-color: rgba(0,170,255,0.55);
		            box-shadow: 0 22px 70px rgba(0,170,255,0.16), 0 18px 46px rgba(0,0,0,0.28);
		            filter: saturate(1.08);
		        }
		        #title .hero-cta .btn:active{
		            transform: translateY(0px) scale(0.99);
		        }
		        #title .hero-cta .btn:focus-visible{
		            box-shadow: 0 0 0 4px rgba(0,170,255,0.22), 0 18px 46px rgba(0,0,0,0.28);
		        }

		        @keyframes heroIn{
		            from{ opacity: 0; transform: translateY(18px) scale(0.985); }
		            to{ opacity: 1; transform: translateY(0) scale(1); }
		        }

		        @media (max-width: 576px){
		            #title .hero-cta .btn{ width: 100%; max-width: 360px; }
		            #title h2{ letter-spacing: 0.30em; }
		        }
	        
	        /* Home Posts (cards) */
	        .products-container {
	            max-width: 1200px;
	            margin: 42px auto;
	            padding: 18px;
	            transition: opacity 0.6s ease, transform 0.6s ease;
	        }
	        
	        /* Hide home posts on initial load; reveal after user scrolls */
	        .products-container.is-hidden {
	            opacity: 0;
	            transform: translateY(20px);
	            pointer-events: none;
	        }
	        
	        .products-container.is-revealed {
	            opacity: 1;
	            transform: translateY(0);
	            pointer-events: auto;
	        }
        
	        .product-card {
	            position: relative;
	            overflow: hidden;
	            margin-bottom: 34px;
	            padding: 28px;
	            border-radius: 26px;
	            text-align: left;
	            max-height: 640px;
	            display: flex;
	            flex-direction: column;

            /* Gradient border + deep glass surface */
            border: 1px solid transparent;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.06)) padding-box,
                linear-gradient(135deg, rgba(0,170,255,0.35), rgba(0,102,204,0.22), rgba(255,255,255,0.10)) border-box;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);

            box-shadow:
                0 18px 70px rgba(0,0,0,0.35),
                inset 0 1px 0 rgba(255,255,255,0.10);

            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            animation: fadeInUp 0.8s ease backwards;
        }
        
        .product-card::before {
            content: "";
            position: absolute;
            inset: -1px;
            background:
                radial-gradient(900px 380px at 20% 0%, rgba(0,170,255,0.22), transparent 60%),
                radial-gradient(700px 320px at 95% 10%, rgba(0,102,204,0.20), transparent 60%);
            opacity: 0.55;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }

        .product-card::after{
            content: "";
            position: absolute;
            top: -120px;
            right: -140px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.12), transparent 60%);
            opacity: 0.55;
            pointer-events: none;
            transform: rotate(12deg);
        }

        .product-card:hover::before{
            opacity: 0.75;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow:
                0 26px 95px rgba(0,0,0,0.42),
                0 0 0 1px rgba(0,170,255,0.10) inset,
                inset 0 1px 0 rgba(255,255,255,0.12);
        }
        
        .product-card:nth-child(1) {
            animation-delay: 0.2s;
        }
        
        .product-card:nth-child(2) {
            animation-delay: 0.4s;
        }
        
        .product-card:nth-child(3) {
            animation-delay: 0.6s;
        }
        
        .product-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .product-header-left{
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        
        .product-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255,255,255,0.90), rgba(210,210,210,0.86));
            border: 1px solid rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.22);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            flex: 0 0 auto;
        }
        
        .product-card:hover .product-icon {
            transform: rotate(-4deg) scale(1.06);
            box-shadow: 0 16px 44px rgba(0, 0, 0, 0.26);
        }
        
        .product-icon img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }
        
        .product-title {
            flex: 1;
            min-width: 0;
        }
        
        .product-title h3 {
            color: rgba(255,255,255,0.96);
            font-size: 26px;
            margin: 0;
            letter-spacing: 0.2px;
            text-shadow: 0 14px 36px rgba(0,0,0,0.35);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-title p {
            color: rgba(255, 255, 255, 0.75);
            margin: 6px 0 0 0;
            font-size: 13px;
            font-weight: 550;
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
	        .product-content {
	            display: grid;
	            grid-template-columns: 1.05fr 0.95fr;
	            gap: 22px;
	            align-items: stretch;
	            margin-top: 16px;
	            flex: 1 1 auto;
	            min-height: 0;
	        }
        .product-content.is-reverse .product-image{ order: 2; }
        .product-content.is-reverse .product-description{ order: 1; }
        .product-card:not(.has-image) .product-content{
            grid-template-columns: 1fr;
        }
        
	        .product-image {
	            position: relative;
	            border-radius: 20px;
	            overflow: hidden;
	            box-shadow: 0 22px 60px rgba(0,0,0,0.42);
	            transition: transform 0.28s ease, box-shadow 0.28s ease;
	            isolation: isolate;
	            display: flex;
	            align-items: center;
	            justify-content: center;
	            background: rgba(0,0,0,0.18);
	            height: 340px;
	        }
        .product-image::before{
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.12);
            pointer-events: none;
            z-index: 2;
        }
        
        .product-image:hover {
            transform: translateY(-2px);
            box-shadow: 0 28px 82px rgba(0,0,0,0.48);
        }
        
        .product-image img,
        .product-image video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s ease;
            border-radius: 20px;
            filter: saturate(1.05) contrast(1.03);
            background: rgba(0,0,0,0.25);
            display: block;
            position: relative;
            z-index: 1;
        }
        .product-image.is-portrait img,
        .product-image.is-portrait video{ object-fit: contain; }
        
        .product-image:hover img,
        .product-image:hover video { transform: scale(1.04); }
        
        .product-image::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(800px 360px at 20% 0%, rgba(0,170,255,0.22), transparent 55%),
                linear-gradient(180deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0.45) 100%);
            border-radius: 20px;
            pointer-events: none;
            z-index: 2;
        }

        .product-image .video-sound-toggle{
            position: absolute;
            left: 14px;
            bottom: 14px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(0,0,0,0.55);
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.95);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.2px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            cursor: pointer;
        }
        .product-image .video-sound-toggle:hover{ background: rgba(0,0,0,0.65); }
        .product-image .video-sound-toggle:focus-visible{
            outline: 2px solid rgba(0,170,255,0.7);
            outline-offset: 2px;
        }
        
	        .product-description {
	            position: relative;
	            z-index: 1;
	            border-radius: 20px;
	            padding: 18px 18px 14px 18px;
	            background: rgba(255,255,255,0.05);
	            border: 1px solid rgba(255,255,255,0.10);
	            color: rgba(255, 255, 255, 0.90);
	            line-height: 1.75;
	            font-size: 15px;
	            display: flex;
	            flex-direction: column;
	            min-height: 0;
	        }

	        .product-description-text{
	            flex: 1 1 auto;
	            min-height: 0;
	            overflow: auto;
	            padding-right: 6px;
	        }
	        .product-description-text::-webkit-scrollbar{ width: 6px; }
	        .product-description-text::-webkit-scrollbar-thumb{ background: rgba(255,255,255,0.18); border-radius: 999px; }
	        .product-description-text::-webkit-scrollbar-track{ background: rgba(255,255,255,0.06); border-radius: 999px; }

	        .product-description .post-social{
	            margin-top: auto;
	            padding-top: 14px;
	        }
        
        .product-features {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        /* Home posts engagement row (heart/comment/share) */
        .post-social {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .social-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.92);
            font-size: 13px;
            line-height: 1;
            cursor: pointer;
            user-select: none;
        }

        button.social-chip {
            appearance: none;
            -webkit-appearance: none;
            outline: none;
        }

        .social-chip:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }

        .social-chip:active {
            transform: translateY(0);
        }

        .social-chip:focus-visible {
            box-shadow: 0 0 0 3px rgba(0, 170, 255, 0.28);
        }

        .social-chip.heart i { color: #ff5b7a; }
        .social-chip.comment i { color: #00aaff; }
        .social-chip.share i { color: #7dd3fc; }

        /* Heart: one-like-per-device + pro click effect */
        .social-chip.heart.is-liked{
            background: rgba(255, 91, 122, 0.12);
            border-color: rgba(255, 91, 122, 0.28);
        }

        .social-chip.heart.is-liked i{
            color: #ff3b6b;
            filter: drop-shadow(0 0 10px rgba(255, 59, 107, 0.35));
        }

        .social-chip.heart .fa-heart{
            transition: transform 0.18s ease, filter 0.18s ease;
        }

        .social-chip.heart.is-popping .fa-heart{
            animation: heartPop 480ms cubic-bezier(.17,.89,.32,1.49);
        }

        .social-chip.heart.is-popping{
            position: relative;
        }

        .social-chip.heart.is-popping::after{
            content:"";
            position:absolute;
            inset:-6px;
            border-radius: 999px;
            border: 1px solid rgba(255, 59, 107, 0.42);
            animation: heartRing 520ms ease-out;
            pointer-events: none;
        }

        @keyframes heartPop{
            0%{ transform: scale(1); }
            35%{ transform: scale(1.28) rotate(-8deg); }
            70%{ transform: scale(0.98) rotate(2deg); }
            100%{ transform: scale(1) rotate(0deg); }
        }

        @keyframes heartRing{
            0%{ opacity: 0.75; transform: scale(0.86); }
            100%{ opacity: 0; transform: scale(1.15); }
        }

        /* Modern modal sheet styling (comment + share) */
        .modal.modern-sheet .modal-dialog{
            max-width: 720px;
        }

        .modal.modern-sheet .modal-content{
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(180deg, rgba(15,23,42,0.92), rgba(2,6,23,0.92));
            color: rgba(255,255,255,0.92);
            box-shadow: 0 28px 80px rgba(0,0,0,0.55);
        }

        .modal.modern-sheet .modal-header{
            border-bottom: 1px solid rgba(255,255,255,0.10);
            background: radial-gradient(1200px 400px at 10% 0%, rgba(0,170,255,0.35), transparent 55%),
                        radial-gradient(900px 300px at 90% 0%, rgba(0,102,204,0.25), transparent 55%);
        }

        .modal.modern-sheet .modal-title{
            display:flex;
            flex-direction: column;
            gap: 2px;
        }

        .modal.modern-sheet .modal-title .title-main{
            display:flex;
            align-items:center;
            gap:10px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .modal.modern-sheet .modal-title .title-sub{
            font-size: 12px;
            color: rgba(255,255,255,0.70);
            font-weight: 500;
        }

        .modal.modern-sheet .modal-body{
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        }

        .modal.modern-sheet .btn-close{
            filter: invert(1);
            opacity: 0.9;
        }

        /* Share UI */
        .share-url{
            display:flex;
            gap: 10px;
            align-items:center;
            padding: 12px;
            border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .share-url input{
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: rgba(255,255,255,0.92);
            font-size: 13px;
        }

        .share-grid{
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width: 576px){
            .share-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .share-tile{
            text-decoration: none;
            border-radius: 16px;
            padding: 14px 12px;
            display:flex;
            flex-direction: column;
            align-items:flex-start;
            gap: 10px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
            color: rgba(255,255,255,0.92);
        }

        .share-tile:hover{
            transform: translateY(-2px);
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.18);
            box-shadow: 0 18px 38px rgba(0,0,0,0.35);
        }

        .share-ico{
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display:flex;
            align-items:center;
            justify-content:center;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .share-label{
            font-weight: 650;
            font-size: 13px;
        }

        .share-meta{
            font-size: 12px;
            color: rgba(255,255,255,0.70);
            margin-top: -6px;
        }

        .share-tile.is-facebook .share-ico{ background: rgba(24,119,242,0.22); border-color: rgba(24,119,242,0.30); }
        .share-tile.is-whatsapp .share-ico{ background: rgba(37,211,102,0.18); border-color: rgba(37,211,102,0.28); }
        .share-tile.is-x .share-ico{ background: rgba(255,255,255,0.10); border-color: rgba(255,255,255,0.16); }
        .share-tile.is-linkedin .share-ico{ background: rgba(10,102,194,0.20); border-color: rgba(10,102,194,0.30); }
        .share-tile.is-email .share-ico{ background: rgba(148,163,184,0.18); border-color: rgba(148,163,184,0.25); }
        .share-tile.is-telegram .share-ico{ background: rgba(34,158,217,0.18); border-color: rgba(34,158,217,0.28); }
        .share-tile.is-copy .share-ico{ background: rgba(0,170,255,0.18); border-color: rgba(0,170,255,0.28); }

        /* Comments UI */
        .comments-wrap{
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .comments-list{
            border-radius: 16px;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            max-height: 280px;
            overflow: auto;
        }

        .comment-row{
            display:flex;
            gap: 10px;
            padding: 10px 8px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .comment-row:last-child{
            border-bottom: none;
        }

        .comment-avatar{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display:flex;
            align-items:center;
            justify-content:center;
            background: rgba(0,170,255,0.16);
            border: 1px solid rgba(0,170,255,0.22);
            color: rgba(255,255,255,0.92);
            font-weight: 700;
            font-size: 12px;
            flex: 0 0 auto;
        }

        .comment-body{
            flex: 1 1 auto;
            text-align: left;
        }

        .comment-head{
            display:flex;
            gap: 10px;
            align-items: baseline;
            justify-content: space-between;
        }

        .comment-author{
            font-weight: 700;
            font-size: 13px;
        }

        .comment-time{
            font-size: 11px;
            color: rgba(255,255,255,0.55);
            white-space: nowrap;
        }

        .comment-text{
            margin-top: 4px;
            font-size: 13px;
            color: rgba(255,255,255,0.86);
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .comment-compose{
            border-radius: 16px;
            padding: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .comment-compose textarea{
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.92);
        }

        .comment-compose textarea::placeholder{
            color: rgba(255,255,255,0.55);
        }

        .comment-compose .compose-meta{
            display:flex;
            align-items:center;
            justify-content: space-between;
            margin-top: 8px;
            color: rgba(255,255,255,0.65);
            font-size: 12px;
        }

        .comment-compose .btn-primary{
            background: linear-gradient(135deg, rgba(0,102,204,0.95), rgba(0,170,255,0.78));
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
        }
        
        .feature-badge {
            background: rgba(0, 102, 204, 0.6);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            color: #00aaff;
            border: 1px solid rgba(0, 170, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .feature-badge:hover {
            background: rgba(0, 102, 204, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 170, 255, 0.3);
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .product-content {
                grid-template-columns: 1fr;
            }
            .product-content.is-reverse .product-image{ order: 1; }
            .product-content.is-reverse .product-description{ order: 2; }
            
            .product-header {
                text-align: left;
                margin-bottom: 14px;
            }
            .product-header-left{
                width: 100%;
                justify-content: flex-start;
                align-items: flex-start;
                gap: 10px;
            }
            
            .product-title h3 {
                font-size: 22px;
                line-height: 1.2;
                white-space: normal;
                overflow: visible;
                text-overflow: unset;
                word-break: break-word;
                margin: 0;
            }
            .product-title p {
                white-space: normal;
                overflow: visible;
                text-overflow: unset;
                line-height: 1.35;
                margin: 4px 0 0;
                word-break: break-word;
            }
            
	            .product-card{ max-height: 620px; }
	            .product-image{ height: 220px; }
            
            .products-container {
                padding: 10px;
            }
            
            .product-card {
                padding: 20px;
                margin-bottom: 30px;
            }
            
            .product-icon {
                width: 60px;
                height: 60px;
            }
            
            .product-icon img {
                width: 35px;
                height: 35px;
            }

            .post-social {
                flex-wrap: nowrap;
                gap: 8px;
                justify-content: space-between;
            }
            .post-social .social-chip {
                flex: 1 1 0;
                min-width: 0;
                justify-content: center;
                padding: 8px 10px;
            }

            #title .post-social{
                flex-wrap: nowrap;
                gap: 8px;
            }
            #title .social-chip{
                flex: 1 1 0;
                min-width: 0;
                justify-content: center;
                padding: 8px 10px !important;
            }
            #title .product-title h3{
                font-size: 20px !important;
                white-space: normal;
            }
            #title .product-title p{
                white-space: normal !important;
            }
        }
        
        @media (max-width: 480px) {
            .product-card {
                padding: 15px;
            }
            
            .product-title h3 {
                font-size: 18px;
            }
            .product-title p{
                font-size: 12px;
            }
            
            .product-description {
                font-size: 14px;
            }

            .post-social{
                gap: 6px;
            }
            .post-social .social-chip{
                padding: 7px 8px;
                font-size: 12px;
                gap: 6px;
            }
            #title .social-chip{
                padding: 7px 8px !important;
            }
            
            .feature-badge {
                font-size: 11px;
                padding: 6px 12px;
            }
        }

        /* Headings with animated underline */
        .heading {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        .heading::after {
            content: "";
            position: absolute;
            width: 0%;
            height: 3px;
            left: 0;
            bottom: -5px;
            background: #00aaff;
            transition: width 0.4s ease;
        }
        .heading:hover::after {
            width: 100%;
        }

        /* Developer Cards */
        .dev-team {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 30px;


            
        }
        .dev-card {
            background: rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 25px;
            width: 220px;
            transition: 0.4s ease;
            backdrop-filter: blur(6px);
        }
        .dev-card:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        .contact-picture {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #00aaff;
            box-shadow: 0 0 20px rgba(0, 170, 255, 0.6);
            animation: float 3s ease-in-out infinite;
            background: linear-gradient(135deg, #0066cc, #00aaff);
        }
        .contact-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .dev-card:hover .contact-picture img {
            transform: scale(1.1);
        }
        .contact-info {
            font-size: 13px;
            margin: 8px 0;
            color: rgba(255, 255, 255, 0.9);
            text-align: left;
        }
        .contact-info i {
            color: #00aaff;
            margin-right: 8px;
            width: 20px;
        }
        .contact-info .contact-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .contact-info .contact-link:hover {
            color: #00aaff;
            text-decoration: underline;
        }
        .tagline {
            margin-top: 25px;
            font-size: 15px;
            opacity: 0.85;
            font-style: italic;
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Scroll Indicator */
        .scroll-indicator {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* Pull the hint up closer to the hero card */
            margin: -36px auto 16px;
            max-width: min(260px, 90%);
            text-align: center;
            animation: bounce 2s infinite;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }

        .first-post-wrapper {
            position: relative;
        }
        .scroll-indicator.is-off {
            opacity: 0;
            animation: none;
        }

	        .scroll-indicator span {
	            display: block;
	            color: rgba(255,255,255,0.96);
	            font-size: 12px;
	            margin-bottom: 10px;
	            text-transform: uppercase;
	            letter-spacing: 0.28em;
	            text-shadow:
	                0 0 10px rgba(0,170,255,0.55),
	                0 0 26px rgba(0,170,255,0.35),
	                0 18px 60px rgba(0,0,0,0.55);
	        }

	        .scroll-indicator i {
	            font-size: 24px;
	            color: rgba(255,255,255,0.96);
	            animation: scrollDown 1.5s infinite;
	            text-shadow:
	                0 0 12px rgba(0,170,255,0.70),
	                0 0 28px rgba(0,170,255,0.42),
	                0 18px 60px rgba(0,0,0,0.50);
	        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @keyframes scrollDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(10px);
            }
        }
        
        /* Image Loading Animation */
        .product-image img,
        .product-image video {
            opacity: 0;
            animation: fadeInImage 0.8s ease forwards;
        }
        
        @keyframes fadeInImage {
            from {
                opacity: 0;
                transform: scale(1.1);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
         

        

        /* Contact Information Styles */
        .contact-item {
            transition: 0.3s ease;
        }

        .contact-icon {
            font-size: 20px;
            color: #00aaff;
            margin-right: 15px;
            width: 25px;
            text-align: center;
        }

        .contact-link {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: 0.3s ease;
        }

        .contact-link:hover {
            color: #00aaff;
            text-decoration: underline;
            text-shadow: 0 0 5px rgba(0, 170, 255, 0.5);
        }

        .contact-text {
            color: white;
            font-size: 16px;
        }

        .contact-item:hover .contact-icon {
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(0, 170, 255, 0.8);
        }
        
        #locationLink {
            transition: all 0.3s ease;
        }
        
        #locationLink:hover {
            color: #00aaff !important;
            text-shadow: 0 0 5px rgba(0, 170, 255, 0.5);
            transform: translateX(3px);
        }
                

        #copyr{
            bottom: 0; 
            position: fixed;
            display: flex;
            width: 100%;
            height: 40px;
            background: linear-gradient(
                90deg,
                #f5f7fa 0%,
                #e9edf3 50%,
                #f5f7fa 100%
            );
            color: #103287ff;
            align-items: center;
            text-align:center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            z-index: 1000;
            
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* Footer-safe spacing variable for mobile scroll containers */
        :root{
            --magx-footer-safe-space: 76px;
        }
        
        

	        /* Side Navigation Styles */
	        .sidenav-toggle{
	            position: absolute;
	            right: 14px;
	            top: 50%;
	            transform: translateY(-50%);
	            width: 38px;
	            height: 38px;
	            padding: 0;
	            border-radius: 999px;
	            border: 1px solid rgba(0,170,255,0.55);
	            background: linear-gradient(180deg, rgba(2,6,23,0.78), rgba(2,6,23,0.42));
	            color: #eaf6ff;
	            display: grid;
	            place-items: center;
	            cursor: pointer;
	            backdrop-filter: blur(10px);
	            -webkit-backdrop-filter: blur(10px);
	            box-shadow:
	                inset 0 1px 0 rgba(255,255,255,0.10),
	                0 0 0 1px rgba(0,170,255,0.12),
	                0 0 14px rgba(0,170,255,0.18),
	                0 14px 34px rgba(0,0,0,0.32);
	            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
	            overflow: hidden;
	        }
	        .sidenav-toggle::before{
	            content: "";
	            position: absolute;
	            inset: -2px;
	            border-radius: inherit;
	            background: conic-gradient(
	                from 0deg,
	                rgba(0,170,255,0.00) 0deg,
	                rgba(0,170,255,0.95) 55deg,
	                rgba(0,170,255,0.00) 120deg,
	                rgba(0,170,255,0.00) 360deg
	            );
	            opacity: 0.85; /* visible ring even at rest */
	            transition: opacity 0.18s ease;
	            /* ring mask */
	            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 2.4px), #000 0);
	            mask: radial-gradient(farthest-side, transparent calc(100% - 2.4px), #000 0);
	            filter: drop-shadow(0 0 12px rgba(0,170,255,0.62));
	            pointer-events: none;
	        }
	        .sidenav-toggle::after{
	            content: "";
	            position: absolute;
	            inset: -24px;
	            border-radius: inherit;
	            background: linear-gradient(120deg, transparent 35%, rgba(255,255,255,0.22), transparent 65%);
	            opacity: 0;
	            transform: translateX(-55%);
	            pointer-events: none;
	        }
	        .sidenav-toggle i{
	            font-size: 18px;
	            line-height: 1;
	            position: relative;
	            z-index: 1;
	            color: rgba(230,250,255,0.98);
	            filter: drop-shadow(0 0 10px rgba(0,170,255,0.85));
	            transition: transform 0.18s ease, filter 0.18s ease;
	        }
	        .sidenav-toggle:hover{
	            transform: translateY(-50%) scale(1.08);
	            border-color: rgba(0,170,255,0.70);
	            background: linear-gradient(180deg, rgba(2,6,23,0.70), rgba(2,6,23,0.34));
	            box-shadow:
	                inset 0 1px 0 rgba(255,255,255,0.14),
	                0 0 0 1px rgba(0,170,255,0.18),
	                0 0 22px rgba(0,170,255,0.32),
	                0 18px 46px rgba(0,0,0,0.40);
	        }
	        .sidenav-toggle:hover::before{
	            opacity: 1;
	            animation: magxSidenavRing 1.1s linear infinite;
	        }
	        .sidenav-toggle:hover::after{
	            opacity: 0.95;
	            animation: magxSidenavSweep 1.15s ease-in-out infinite;
	        }
	        .sidenav-toggle:hover i{
	            transform: scale(1.07);
	            filter: drop-shadow(0 0 14px rgba(0,170,255,0.95));
	        }
	        .sidenav-toggle:active{
	            transform: translateY(-50%) scale(1.02);
	        }
	        .sidenav-toggle:focus-visible{
	            outline: none;
	            box-shadow:
	                0 0 0 2px rgba(255,255,255,0.20),
	                0 0 0 5px rgba(0,170,255,0.34),
	                0 14px 34px rgba(0,0,0,0.32);
	        }
	        @keyframes magxSidenavRing{
	            to { transform: rotate(360deg); }
	        }
	        @keyframes magxSidenavSweep{
	            from { transform: translateX(-55%); }
	            to { transform: translateX(55%); }
	        }
        .sidenav {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1050;
            top: 0;
            right: 0;
            overflow: hidden;
            padding-top: 18px;
            transition: width 0.34s cubic-bezier(.22,.8,.24,1), box-shadow 0.34s ease;
            border-left: 1px solid rgba(0,170,255,0.24);
            background:
                radial-gradient(500px 240px at 85% -12%, rgba(0,170,255,0.30), transparent 62%),
                radial-gradient(380px 220px at 0% 100%, rgba(0,92,190,0.35), transparent 70%),
                linear-gradient(180deg, rgba(6,22,52,0.97), rgba(4,12,30,0.97));
            box-shadow: -20px 0 46px rgba(0,0,0,0.38);
        }
        .sidenav::after{
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 24px 24px, 24px 24px;
            opacity: 0.22;
        }
        #mySidenav .span{
            position: relative;
            z-index: 2;
            margin: 8px 14px 14px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.10);
            background: linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10), 0 20px 46px rgba(0,0,0,0.26);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateX(16px);
            transition: opacity 0.22s ease, transform 0.30s ease;
        }
        #mySidenav.is-open .span{
            opacity: 1;
            transform: translateX(0);
        }
        #mySidenav #adminlogin{
            margin: 0;
            padding: 12px 4px 14px;
            text-decoration: none;
            text-align: center;
            font-size: 30px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 900;
            color: #ecf8ff;
            display: block;
            text-shadow: 0 8px 34px rgba(0,170,255,0.45);
        }
        #mySidenav #admininput{
            margin-top: 6px;
            padding: 0;
        }
        #mySidenav .input-group{
            margin-bottom: 14px !important;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 8px 24px rgba(0,0,0,0.24);
        }
        #mySidenav .input-group-text{
            width: 52px;
            justify-content: center;
            border: none;
            margin: 0;
            background: linear-gradient(180deg, rgba(8,31,68,0.88), rgba(7,26,58,0.82));
            color: #5fd2ff;
            padding: 0;
        }
        #mySidenav .input-group-text i{
            font-size: 18px;
            color: #46c8ff;
            text-shadow: 0 0 12px rgba(70,200,255,0.45);
        }
        #mySidenav .form-control{
            border: none;
            background: rgba(255,255,255,0.12);
            color: #eef8ff;
            border-radius: 0 !important;
            padding: 10px 12px;
        }
        #mySidenav .form-control::placeholder{ color: transparent; }

        /* Side nav action buttons (Admin login) */
        #mySidenav .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }
        #mySidenav .action-buttons input {
            flex: 1;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            border: 1px solid rgba(255,255,255,0.14);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        }
        #mySidenav #adlogin {
            background: linear-gradient(135deg, rgba(18,132,242,0.98), rgba(0,170,255,0.88));
            color: #fff;
            box-shadow: 0 14px 26px rgba(0,120,255,0.32);
        }
        #mySidenav #adcancel {
            background: linear-gradient(145deg, rgba(243,248,255,0.96), rgba(208,222,238,0.92));
            color: #123054;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
        }
        #mySidenav #adlogin:hover,
        #mySidenav #adcancel:hover{
            transform: translateY(-1px);
            border-color: rgba(0,170,255,0.46);
            box-shadow: 0 16px 30px rgba(0,140,255,0.24);
        }

        #mySidenav #exit{
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(228,246,255,0.96);
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }
        #mySidenav #exit::before{
            content: "\f2f5";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #63d0ff;
            font-size: 14px;
        }
        #mySidenav #exit:hover {
            background: rgba(255, 255, 255, 0.10);
            transform: translateX(4px);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.12) inset, 0 10px 20px rgba(0,0,0,0.20);
        }

        #mySidenav #closeBtn{
            margin-left: 0 !important;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14) !important;
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.16);
            filter: invert(1);
            opacity: 0.92;
            transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
        }
        #mySidenav #closeBtn:hover{
            transform: rotate(90deg) scale(1.06);
            background: rgba(255,255,255,0.20) !important;
            box-shadow: 0 0 0 1px rgba(95,210,255,0.30), 0 10px 20px rgba(0,0,0,0.20);
        }

        #mySidenav .modal-footer{
            position: absolute;
            bottom: 10px;
            left: 0;
            width: 100%;
            border: none;
            background: transparent;
            justify-content: center;
            z-index: 2;
        }
        #mySidenav .warning{
            margin: 0;
            color: rgba(210,237,255,0.78);
            letter-spacing: 0.04em;
            text-shadow: 0 6px 18px rgba(0,0,0,0.35);
        }

	        /* old #sidenav hover removed (now handled by .sidenav-toggle) */

	        /* End Side Navigation Styles */
        
        #droptmc{
            background-color: rgba(255, 255, 255, 0.1);

        }
        .dropdown-item{
            font-family: cursive;
            transition: 0.3s;
            color: white;
        }
        .dropdown-item:hover{
            background-color: #0066cc; 
            color: white;
            box-shadow: 0 0 10px rgba(0, 170, 255, 0.5);
        }
        .nav-link{
            margin-right:20px;
            font-family: cursive;
            transition: 0.3s;
            color: white;
            
        }
        #title{
            text-shadow: 0 0 5px black, 0 0 10px black;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        .nav-link:hover{
            border-bottom: 3px solid #00aaff;
            color: white !important;
            position:none;
            text-shadow: 0 0 5px #00aaff, 0 0 10px #00aaff;
            
        }
        .nav-link:active {
            border-bottom: 3px solid #00aaff;
            color: #00aaff !important;

            
        }
        .nav-link:focus{
            color: #00aaff !important;
            border-bottom: 3px solid #00aaff;
            text-shadow: 0 0 5px white, 0 0 10px #00aaff;
            
        }
        .rotated {
             transform: rotate(180deg);
        }



        #modcon{
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #modcon2{
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #close:hover,#regcancel:hover,#regcreate:hover,#login:hover{
            transform: scale(1.2);
            box-shadow: inset 0 4px 6px rgba(255, 255, 255, 0.47);
        }
        .acclink{
            text-decoration: none;
            cursor: pointer;
        }
        .acclink:hover{
            text-decoration: underline;
            color: #672222 !important;
            text-shadow: 0 0 5px white, 0 0 10px #672222;
        }

        /* loader animation*/
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.68); 
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        
        .circle-loader {
            width: 50px;
            height: 50px;
            border: 6px solid rgba(0, 0, 0, 0.1);
            border-top: 6px solid #a50606ff;
            border-radius: 50%;
            animation: spin 1s infinite;
        }

        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
         /* end ng loader */


         /* for admin float label*/
        #mySidenav .col-form-label {
            position: absolute;
            left: 63px;
            top: 1.5px;
            color: rgba(220, 240, 255, 0.76);
            font-size: 15px;
            pointer-events: none;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            
        }
        
        #mySidenav .form-control:focus {
            border: none;
            box-shadow: inset 0 0 0 1px rgba(95,210,255,0.62), 0 0 0 3px rgba(95,210,255,0.20);
            outline: none ;
            background: rgba(255,255,255,0.16);
            
            
        }

        /* make select mimic input styling */
        #mySidenav .form-select:focus {
            border: none;
            box-shadow: 0 0 0 3px rgba(95,210,255,0.20);
            outline: none ;
        }

        /* Custom checkbox styling to match header colors */
        #showPassword {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #672222;
            border: 2px solid #8c2f2f;
        }

        #showPassword:checked {
            background: linear-gradient(90deg, #672222, #8c2f2f);
            border-color: #672222;
        }

        #showPassword:focus {
            box-shadow: 0 0 8px rgba(103, 34, 34, 0.6);
            outline: none;
        }

        .form-check-label[for="showPassword"] {
            user-select: none;
            transition: color 0.3s ease;
        }

        .form-check-label[for="showPassword"]:hover {
            color: #8c2f2f !important;
        }

        #mySidenav .form-control:focus ~ .col-form-label,
        #mySidenav .form-control:not(:placeholder-shown) ~ .col-form-label {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 13px;
            color: #9fe5ff;
            background: rgba(4, 12, 30, 0.8);
            border-radius: 8px;
            z-index: 20; 
        }

        /* float label for select */
        #mySidenav .form-select:focus ~ .col-form-label,
        #mySidenav .form-select.has-value ~ .col-form-label {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 13px;
            color: #9fe5ff !important;
            background: rgba(4, 12, 30, 0.8);
            border-radius: 8px;
            z-index: 20;
        }



        /* end user float label*/
        #ulabel,#plabel {
            position: absolute;
            left: 63px;
            top: 1.5px;
            color: grey;
            font-size: 15px;
            pointer-events: none;
            transition: all 0.2s ease;
            font-family: 'cursive';
            
        }

        #u:focus,#p:focus {
            border: none;
            box-shadow: 0 0 12px rgba(103, 34, 34, 0.8);
            outline: none ;
            
            
        }
        
        #u:focus ~ #ulabel,
        #p:focus ~ #plabel,
        #u:not(:placeholder-shown) ~ #ulabel,
        #p:not(:placeholder-shown) ~ #plabel {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 15px;
            color: #672222;
            background: none;
            z-index: 20; 
        }
        

        .mb-3 {
            position: relative;
            margin: 20px 0;
        }

        /* Responsive for #btnnav */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.35);
        }
       
        #btnnav .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255, 0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        @media (max-width: 992px) {
            /* When collapsed, let items stack nicely */
            #btnnav .navbar-collapse {
                padding: 10px 0;
            }
            #frow, #srow {
                margin: 10px 0;
                gap: 12px;
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }
            #btnnav .bt {
                width: 100%;
            }
            #btnnav .dropdown,
            #btnnav .dropdown > .btn,
            #btnnav .dropdown-menu {
                width: 100%;
            }
            #btnnav .dropdown-menu .dropdown-item {
                white-space: normal;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            /* Prevent fixed footer from covering the end of section content */
            #title,
            #aboutcon,
            #servicescon,
            #contactcontainer{
                padding-bottom: calc(var(--magx-footer-safe-space) + env(safe-area-inset-bottom, 0px)) !important;
                scroll-padding-bottom: calc(var(--magx-footer-safe-space) + env(safe-area-inset-bottom, 0px));
            }

            /* Use dynamic viewport on modern mobile browsers (Safari iOS included) */
            @supports (height: 100dvh){
                #title,
                #aboutcon,
                #servicescon,
                #contactcontainer{
                    height: calc(100dvh - 40px) !important;
                    min-height: calc(100dvh - 40px) !important;
                }
            }

            /* Tighter spacing on very small screens */
            #btnnav .bt { 
                padding-top: 8px;
                padding-bottom: 8px;
            }
            
	            /* Mobile responsive for About section */
            #aboutcon {
                padding: 40px 20px !important;
                height: calc(100vh - 40px) !important;
                min-height: calc(100vh - 40px);
                overflow-y: auto;
                margin: 10px !important;
            }
            
            .about-card {
                padding: 25px !important;
                margin-bottom: 25px !important;
                border-radius: 20px !important;
            }
            
            #aboutcon h2 {
                font-size: 22px !important;
                margin-bottom: 20px !important;
                padding-left: 15px !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }
            
            #aboutcon h2::before {
                height: 35px !important;
            }
            
            #aboutcon h2:hover {
                padding-left: 18px !important;
            }
            
            .about-icon {
                width: 40px !important;
                height: 40px !important;
                font-size: 20px !important;
            }
            
            #aboutcon p {
                font-size: 15px !important;
                line-height: 1.8 !important;
                text-align: left !important;
            }
            
	            /* Mobile responsive for Contact section */
            #contactcontainer {
                padding: 20px 15px !important;
                height: calc(100vh - 40px) !important;
                min-height: calc(100vh - 40px);
                overflow-y: auto;
            }

	            #servicescon{
	                padding: 40px 20px !important;
	                height: calc(100vh - 40px) !important;
	                overflow-y: auto;
	            }
	            .services-panel {
	                padding: 24px !important;
	                min-height: auto !important;
	            }
	            .services-hero-card {
	                padding: 22px !important;
	            }
	            .services-hero-actions {
	                flex-direction: column;
	                align-items: stretch;
	            }
	            .service-cta {
	                width: 100%;
	                text-align: center;
	            }
	            .services-grid {
	                gap: 18px !important;
	                grid-template-columns: 1fr !important;
	            }
	            .services-card {
	                flex-direction: column;
	                align-items: flex-start;
	                min-height: 0 !important;
	                height: auto !important;
	                padding: 18px !important;
	                gap: 12px !important;
	            }
	            .services-card-icon {
	                width: 48px;
	                height: 48px;
	                flex: 0 0 48px;
	            }
	            #servicescon .services-card-body{
	                width: 100%;
	                min-width: 0;
	            }
	            #servicescon .services-card-body h3{
	                font-size: 22px !important;
	                line-height: 1.2 !important;
	                margin: 0 0 8px !important;
	                word-break: break-word;
	                overflow-wrap: anywhere;
	            }
	            #servicescon .services-card-body p{
	                font-size: 14px !important;
	                line-height: 1.65 !important;
	                margin: 0 !important;
	                word-break: break-word;
	                overflow-wrap: anywhere;
	            }
            
            #contactcontainer h2 {
                font-size: 18px !important;
                margin-bottom: 20px !important;
                text-align: center;
            }
            
            .dev-team {
                flex-direction: column !important;
                align-items: center !important;
                gap: 15px !important;
            }
            
            .dev-card {
                width: 90% !important;
                max-width: 280px !important;
                padding: 20px !important;
                margin: 0 auto !important;
            }
            
            .dev-card h3 {
                font-size: 16px !important;
                margin-bottom: 8px !important;
            }
            
            .dev-card .role {
                font-size: 14px !important;
            }
            
            .contact-picture {
                width: 100px !important;
                height: 100px !important;
            }
            
            .contact-info {
                font-size: 12px !important;
            }
            
            .tagline {
                font-size: 13px !important;
                text-align: center !important;
                padding: 0 15px !important;
            }
            
        }

        /* iOS Safari footer/home-indicator needs a bit more bottom breathing room */
        @supports (-webkit-touch-callout: none) {
            :root{
                --magx-footer-safe-space: 104px;
            }
        }
        
        /* Additional mobile optimizations for very small screens */
        @media (max-width: 480px) {
            #aboutcon {
                padding: 30px 15px !important;
                margin: 5px !important;
            }
            
            .about-card {
                padding: 20px !important;
                margin-bottom: 20px !important;
            }
            
            #aboutcon h2 {
                font-size: 20px !important;
                padding-left: 12px !important;
            }
            
            #aboutcon h2::before {
                height: 30px !important;
            }
            
            .about-icon {
                width: 35px !important;
                height: 35px !important;
                font-size: 18px !important;
            }
            
            #aboutcon p {
                font-size: 14px !important;
            }
            
            #contactcontainer h2 {
                font-size: 16px !important;
            }
            
            .dev-card {
                width: 95% !important;
                padding: 15px !important;
            }
            
            .dev-card h3 {
                font-size: 14px !important;
            }
            
            .contact-picture {
                width: 80px !important;
                height: 80px !important;
            }
            
            .contact-info {
                font-size: 11px !important;
            }
            
            
            /* Contact section mobile responsive */
            .contact-icon {
                font-size: 18px !important;
                margin-right: 12px !important;
                width: 20px !important;
            }
            
            .contact-link, .contact-text {
                font-size: 14px !important;
            }
        }

	        /* Portfolio — rebuilt */
			        #contactcontainer {
			            color: #0f172a;
			            position: relative;
			            overflow-y: auto;
			            overflow-x: hidden;
			            padding: 60px 20px;
			            background: rgba(255,255,255,0.10);
			            font-family: 'Inter','Poppins',system-ui,sans-serif;
			            backdrop-filter: blur(18px) saturate(1.25);
			            -webkit-backdrop-filter: blur(18px) saturate(1.25);
			            box-shadow: inset 0 0 90px rgba(0,0,0,0.14);
			        }
		        #contactcontainer::before{
		            content:"";
		            position:absolute;
		            inset:-2px;
		            background:
		                radial-gradient(circle at 18% 12%, rgba(0,122,255,0.18), transparent 48%),
		                radial-gradient(circle at 82% 0%, rgba(0,180,255,0.14), transparent 44%),
		                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
		                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
		            background-size: auto, auto, 46px 46px, 46px 46px;
		            opacity: 0.55;
		            mask-image: radial-gradient(circle at 30% 20%, rgba(0,0,0,1), rgba(0,0,0,0) 62%);
		            pointer-events:none;
		            z-index:0;
		        }
	        .folio-shell{
	            max-width: 1200px;
	            margin: 12px auto 40px;
	            padding: 12px;
	            position: relative;
	            z-index: 1;
	        }
		        .folio-hero{
		            display:grid;
		            grid-template-columns: 1.05fr 0.95fr;
		            gap:26px;
		            padding:30px;
		            background: rgba(255,255,255,0.86);
		            border:1px solid rgba(15,27,50,0.08);
		            border-radius:24px;
		            box-shadow: 0 26px 70px rgba(10,30,80,0.16);
		            position: relative;
		            overflow: hidden;
		        }
		        .folio-hero::before{
		            content:"";
		            position:absolute;
		            inset:-1px;
		            background:
		                radial-gradient(circle at 12% 18%, rgba(0,122,255,0.18), transparent 40%),
		                radial-gradient(circle at 72% 0%, rgba(0,180,255,0.14), transparent 38%),
		                linear-gradient(135deg, rgba(255,255,255,0.65), rgba(255,255,255,0.20));
		            opacity:1;
		            border-radius: 24px;
		            z-index:0;
		        }
        .folio-hero > *{ position: relative; z-index: 1; }
        /* Safety override: prevent any accidental dark block styles on the left column */
        .folio-headline{
            background: transparent;
            box-shadow: none;
            filter: none;
        }
        .folio-headline::before,
        .folio-headline::after{
            content: none !important;
        }
	        .folio-headline h2{
	            margin:8px 0 8px;
	            font-size:40px;
	            font-weight:900;
	            letter-spacing:-0.02em;
	            color: #0b1220;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .folio-headline .eyebrow{letter-spacing:0.22em;text-transform:uppercase;font-size:11px;color:#446;}
	        .folio-headline .role{font-size:16px;color:#102a52;font-weight:800;margin-bottom:10px;}
	        .folio-headline .bio{color:#26334f;line-height:1.7;font-size:15px;margin:0 0 14px; max-width: 58ch;}
	        .folio-meta{display:flex;flex-wrap:wrap;gap:10px;margin:12px 0;}
	        .folio-meta .pill{padding:7px 12px;border-radius:12px;background:rgba(238,242,255,0.90);color:#0f1a2c;font-weight:800;font-size:12px;border:1px solid rgba(15,26,44,0.08);}
	        .folio-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;}
	        .folio-chip{padding:9px 13px;border-radius:999px;background:rgba(241,245,255,0.92);color:#0f1a2c;font-weight:800;font-size:12px;border:1px solid rgba(15,26,44,0.07);}
        .folio-cta{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;}
        .folio-cta .btn-primary{background:#1d7cff;border:none;font-weight:800;padding:11px 18px; color:#fff; box-shadow:0 14px 34px rgba(29,124,255,0.35);}
        .folio-cta .btn-light{background:#eef2ff;border:1px solid rgba(15,26,44,0.1);color:#0f1a2c;font-weight:800;}
        .folio-visual{
            display:grid;
            gap:14px;
            justify-items:end;
            align-items:center;
        }
	        .folio-portrait{
	            width:100%;
	            max-width:340px;
	            border-radius:20px;
	            overflow:hidden;
	            box-shadow:0 18px 40px rgba(0,0,0,0.18);
	            border:1px solid rgba(15,27,50,0.08);
	        }
	        .folio-portrait img{width:100%;height:100%;object-fit:cover;}

	        .folio-kpis{
	            display:grid;
	            grid-template-columns: repeat(3, minmax(0, 1fr));
	            gap:10px;
	            margin-top:14px;
	        }
	        .kpi{
	            background: rgba(255,255,255,0.72);
	            border:1px solid rgba(15,27,50,0.08);
	            border-radius:16px;
	            padding:12px 12px;
	            box-shadow: 0 14px 40px rgba(15,27,50,0.10);
	        }
	        .kpi .num{ font-weight: 950; font-size: 20px; letter-spacing:-0.02em; color:#0b1220; font-family:'Poppins','Inter',system-ui,sans-serif; }
	        .kpi .cap{ font-size: 12px; font-weight: 800; color:#0d2a52; margin-top:2px; }
	        .kpi .sub{ font-size: 12px; color:#28405f; margin-top:4px; line-height:1.4; }

	        .resume-intro{
	            display: grid;
	            gap: 12px;
	            align-content: center;
	        }
	        .resume-intro h2{
	            margin: 0;
	            font-size: 44px;
	            font-weight: 900;
	            line-height: 1.02;
	            letter-spacing: -0.02em;
	            color: #1a2338;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .resume-intro .title{
	            margin: 0;
	            font-size: 22px;
	            color: #23395d;
	            font-weight: 700;
	        }
	        .resume-intro .summary{
	            margin: 0;
	            color: #2a3c59;
	            line-height: 1.7;
	            font-size: 15px;
	        }
	        .resume-contact{
	            display: grid;
	            grid-template-columns: repeat(2, minmax(0, 1fr));
	            gap: 8px 12px;
	            margin-top: 4px;
	        }
	        .resume-contact .item{
	            display: inline-flex;
	            align-items: center;
	            gap: 8px;
	            font-size: 13px;
	            font-weight: 600;
	            color: #1e2c45;
	            padding: 8px 10px;
	            border-radius: 12px;
	            background: rgba(255,255,255,0.74);
	            border: 1px solid rgba(15,27,50,0.08);
	            word-break: break-word;
	        }
	        .resume-contact .item i{
	            color: #1d7cff;
	        }
	        .resume-pills{
	            display:flex;
	            flex-wrap: wrap;
	            gap: 8px;
	            margin-top: 6px;
	        }
	        .resume-pill{
	            padding: 8px 11px;
	            border-radius: 999px;
	            background: rgba(236,243,255,0.92);
	            border: 1px solid rgba(15,26,44,0.08);
	            color: #163257;
	            font-size: 12px;
	            font-weight: 800;
	            letter-spacing: 0.03em;
	        }
	        .resume-category-grid{
	            margin-top: 18px;
	            display: grid;
	            grid-template-columns: repeat(2, minmax(0, 1fr));
	            gap: 14px;
	        }
	        .resume-category-card{
	            border-radius: 20px;
	            border: 1px solid rgba(15,27,50,0.08);
	            background: rgba(255,255,255,0.88);
	            box-shadow: 0 20px 50px rgba(15,27,50,0.12);
	            overflow: hidden;
	        }
	        .resume-category-card .head{
	            padding: 14px 16px;
	            background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(245,248,255,0.86));
	            border-bottom: 1px solid rgba(15,27,50,0.08);
	            display:flex;
	            align-items:center;
	            justify-content:space-between;
	            gap: 10px;
	        }
	        .resume-category-card .head .label{
	            font-size: 11px;
	            font-weight: 900;
	            letter-spacing: 0.18em;
	            text-transform: uppercase;
	            color: #1f4a88;
	        }
	        .resume-category-card .head h3{
	            margin: 6px 0 0;
	            font-size: 23px;
	            line-height: 1.2;
	            font-weight: 950;
	            color: #0b1220;
	            letter-spacing: -0.02em;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .resume-category-card .head i{
	            width: 40px;
	            height: 40px;
	            border-radius: 12px;
	            display: grid;
	            place-items: center;
	            background: linear-gradient(135deg, rgba(29,124,255,0.20), rgba(0,178,255,0.15));
	            border: 1px solid rgba(29,124,255,0.26);
	            color: #1d7cff;
	        }
	        .resume-category-card .body{
	            padding: 16px;
	        }
	        .resume-list{
	            margin: 0;
	            padding: 0;
	            list-style: none;
	            display: grid;
	            gap: 12px;
	        }
	        .resume-item{
	            border: 1px solid rgba(15,27,50,0.08);
	            background: rgba(255,255,255,0.75);
	            border-radius: 14px;
	            padding: 12px;
	        }
	        .resume-item .top{
	            display:flex;
	            justify-content: space-between;
	            align-items: baseline;
	            gap: 10px;
	        }
	        .resume-item .title{
	            margin: 0;
	            font-size: 18px;
	            font-weight: 900;
	            color: #0d1628;
	            line-height: 1.2;
	        }
	        .resume-item .year{
	            font-size: 12px;
	            font-weight: 900;
	            color: #1c4887;
	            letter-spacing: 0.14em;
	            text-transform: uppercase;
	            white-space: nowrap;
	        }
	        .resume-item .sub{
	            margin: 3px 0 0;
	            font-size: 14px;
	            color: #22324f;
	            font-style: italic;
	        }
	        .resume-item .desc{
	            margin: 8px 0 0;
	            font-size: 13px;
	            line-height: 1.58;
	            color: #2a3c59;
	        }
	        .resume-bullets{
	            margin: 0;
	            padding-left: 18px;
	            display: grid;
	            gap: 7px;
	        }
	        .resume-bullets li{
	            color: #273a57;
	            font-size: 14px;
	            line-height: 1.5;
	        }
	        .resume-reference{
	            margin: 0;
	            color: #1b2c47;
	            font-size: 16px;
	            line-height: 1.6;
	        }
	        .resume-reference strong{
	            font-size: 24px;
	            color: #0d1628;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }

	        .folio-block{
	            margin-top: 18px;
	            background: rgba(255,255,255,0.86);
	            border:1px solid rgba(15,27,50,0.08);
	            border-radius:22px;
	            box-shadow: 0 22px 60px rgba(15,27,50,0.12);
	            overflow:hidden;
	        }
	        .folio-block-head{
	            padding:18px 18px 10px;
	            border-bottom: 1px solid rgba(15,27,50,0.06);
	            background: linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,255,255,0.76));
	        }
	        .folio-block-head .kicker{
	            font-size:11px;
	            font-weight:900;
	            letter-spacing:0.22em;
	            text-transform:uppercase;
	            color:#204a86;
	        }
	        .folio-block-head h3{
	            margin:8px 0 6px;
	            font-size: 22px;
	            font-weight: 950;
	            letter-spacing:-0.02em;
	            color:#0b1220;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .folio-block-head p{ margin:0; color:#23314d; font-size:14px; line-height:1.6; max-width: 74ch; }
	        .folio-block-body{ padding: 16px 18px 18px; }
	        .folio-block-body p{ margin: 0; }

	        .folio-showcase{
	            margin-top: 18px;
	        }
	        .showcase-card{
	            border-radius: 22px;
	            overflow:hidden;
	            border: 1px solid rgba(15,27,50,0.10);
	            background:
	                radial-gradient(circle at 20% 0%, rgba(0,140,255,0.10), transparent 45%),
	                linear-gradient(180deg, rgba(255,255,255,0.92), rgba(245,248,255,0.86));
	            box-shadow: 0 22px 60px rgba(15,27,50,0.14);
	            position: relative;
	        }
	        .showcase-media{
	            position: relative;
	            aspect-ratio: 16 / 9;
	            background: #0b1220;
	        }
	        .showcase-media img{
	            position:absolute;
	            inset:0;
	            width:100%;
	            height:100%;
	            object-fit: cover;
	            opacity: 1;
	            transform: scale(1);
	            transition: transform 0.45s ease, box-shadow 0.45s ease;
	        }
	        .showcase-card{
	            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
	        }
	        .showcase-card:hover{
	            transform: translateY(-6px);
	            box-shadow: 0 28px 80px rgba(0,140,255,0.16);
	            border-color: rgba(0,140,255,0.18);
	        }
	        .showcase-card:hover .showcase-media img{
	            transform: scale(1.03);
	        }

	        .work-grid{
	            display:grid;
	            grid-template-columns: 1.25fr 0.75fr;
	            gap:14px;
	        }
	        .work-card{
	            border-radius:18px;
	            border:1px solid rgba(15,27,50,0.08);
	            background: rgba(255,255,255,0.86);
	            overflow:hidden;
	            box-shadow: 0 18px 46px rgba(15,27,50,0.12);
	            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
	        }
	        .work-card:hover{
	            transform: translateY(-6px);
	            box-shadow: 0 26px 70px rgba(0,140,255,0.18);
	            border-color: rgba(0,140,255,0.18);
	        }
	        .work-top{
	            padding:14px 14px 0;
	            display:flex;
	            align-items:flex-start;
	            justify-content:space-between;
	            gap:12px;
	        }
	        .work-name{ font-weight: 950; font-size: 16px; color:#0b1220; font-family:'Poppins','Inter',system-ui,sans-serif; letter-spacing:-0.01em; }
	        .work-meta{ font-size: 12px; font-weight: 900; color:#18407a; letter-spacing:0.16em; text-transform:uppercase; }
	        .work-desc{ padding:10px 14px 12px; color:#24324d; font-size:14px; line-height:1.6; }
	        .work-desc strong{ font-weight: 950; }
	        .work-points{ padding: 0 14px 14px; margin: 0; list-style:none; display:grid; gap:8px; }
	        .work-points li{
	            display:flex;
	            gap:10px;
	            align-items:flex-start;
	            color:#22324f;
	            font-size:13px;
	            line-height:1.5;
	        }
	        .work-points li::before{
	            content:"";
	            width:10px;height:10px; border-radius:999px;
	            margin-top:5px;
	            background: linear-gradient(180deg, #1d7cff, #00b2ff);
	            box-shadow: 0 8px 18px rgba(0,140,255,0.28);
	            flex: 0 0 auto;
	        }
	        .work-tags{
	            padding: 0 14px 14px;
	            display:flex;
	            flex-wrap:wrap;
	            gap:8px;
	        }
	        .folio-tag{
	            padding:7px 10px;
	            border-radius:999px;
	            background: rgba(238,242,255,0.90);
	            border:1px solid rgba(15,26,44,0.08);
	            font-size:12px;
	            font-weight:800;
	            color:#0f1a2c;
	        }
	        .work-actions{
	            padding: 0 14px 16px;
	            display:flex;
	            gap:10px;
	            flex-wrap:wrap;
	        }
	        .work-actions .btn{
	            border-radius: 999px;
	            font-weight: 900;
	        }
	        .folio-db-grid{
	            display:grid;
	            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
	            gap:16px;
	        }
	        .folio-db-card{
	            border-radius:16px;
	            padding:16px;
	            background: rgba(255,255,255,0.9);
	            border:1px solid rgba(15,27,50,0.08);
	            box-shadow: 0 18px 42px rgba(15,27,50,0.12);
	            display:flex;
	            flex-direction:column;
	            gap:10px;
	            min-height: 220px;
	            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
	        }
	        .folio-db-card:hover{
	            transform: translateY(-6px);
	            box-shadow: 0 26px 70px rgba(0,140,255,0.18);
	            border-color: rgba(0,140,255,0.18);
	        }
	        .folio-db-card .meta{
	            font-size:12px;
	            font-weight:800;
	            letter-spacing:0.12em;
	            text-transform:uppercase;
	            color:#18407a;
	        }
	        .folio-db-card h4{
	            font-size:16px;
	            margin:0;
	            color:#0b1220;
	            font-weight:900;
	        }
	        .folio-db-card p{
	            margin:0;
	            color:#22324f;
	            font-size:13px;
	            line-height:1.6;
	        }
	        .folio-db-card .tags{
	            display:flex;
	            flex-wrap:wrap;
	            gap:8px;
	        }
	        .folio-db-card .tags span{
	            padding:6px 10px;
	            border-radius:999px;
	            background: rgba(238,242,255,0.9);
	            border:1px solid rgba(15,26,44,0.08);
	            font-size:11px;
	            font-weight:800;
	            color:#0f1a2c;
	        }

	        .folio-split{
	            display:grid;
	            grid-template-columns: 0.9fr 1.1fr;
	            gap:16px;
	            margin-top:18px;
	        }
	        .skills{ display:grid; gap:10px; }
	        .skill{
	            display:grid;
	            grid-template-columns: 140px 1fr;
	            gap:12px;
	            align-items:center;
	        }
	        .skill .name{ font-size: 13px; font-weight: 900; color:#0b1220; }
	        .folio-bar{
	            height: 10px;
	            border-radius: 999px;
	            background: rgba(15,27,50,0.08);
	            overflow:hidden;
	            border:1px solid rgba(15,27,50,0.06);
	        }
	        .folio-bar > span{
	            display:block;
	            height:100%;
	            width: var(--w, 60%);
	            background: linear-gradient(90deg, #1d7cff, #00b2ff);
	            border-radius: 999px;
	            box-shadow: 0 10px 26px rgba(0,140,255,0.25);
	        }
	        .timeline{ display:grid; gap:12px; }
	        .titem{
	            display:grid;
	            grid-template-columns: 90px 1fr;
	            gap:12px;
	            padding:12px 12px;
	            border-radius:16px;
	            border:1px solid rgba(15,27,50,0.08);
	            background: rgba(255,255,255,0.72);
	        }
	        .titem .when{ font-size: 12px; font-weight: 950; letter-spacing:0.16em; text-transform:uppercase; color:#18407a; }
	        .titem .what{ font-weight: 950; font-size: 14px; color:#0b1220; font-family:'Poppins','Inter',system-ui,sans-serif; }
	        .titem .where{ font-size: 13px; color:#22324f; margin-top:2px; }
	        .titem .detail{ font-size: 13px; color:#2a3b59; margin-top:6px; line-height:1.5; }

	        .folio-band{
	            margin-top:18px;
	            border-radius:24px;
	            background:
	                radial-gradient(circle at 20% 40%, rgba(255,255,255,0.45), transparent 55%),
	                linear-gradient(135deg, rgba(29,124,255,0.92), rgba(0,178,255,0.88));
	            color:#fff;
	            border:1px solid rgba(255,255,255,0.20);
	            box-shadow: 0 26px 70px rgba(0,90,200,0.28);
	            overflow:hidden;
	        }
	        .folio-band .inner{
	            padding:18px 18px;
	            display:flex;
	            align-items:center;
	            justify-content:space-between;
	            gap:14px;
	            flex-wrap:wrap;
	        }
	        .folio-band h4{
	            margin:0;
	            font-weight: 950;
	            letter-spacing:-0.02em;
	            font-size: 18px;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .folio-band p{ margin:6px 0 0; color: rgba(255,255,255,0.92); font-size: 13px; line-height:1.5; max-width: 70ch; }
	        .folio-band .actions{ display:flex; gap:10px; flex-wrap:wrap; }
	        .folio-band .actions .btn{
	            border-radius: 999px;
	            font-weight: 950;
	            padding: 10px 14px;
	        }
	        .folio-band .actions .btn-light{
	            background: rgba(255,255,255,0.92);
	            border: none;
	            color:#08325f;
	        }
	        .folio-band .actions .btn-outline-light{
	            border-color: rgba(255,255,255,0.70);
	            color:#fff;
	        }

		        /* Portfolio nav (non-sticky) */
		        .folio-subnav{
		            position: relative;
		            top: auto;
		            z-index: 1;
		            display:flex;
		            gap:10px;
		            flex-wrap:wrap;
		            align-items:center;
		            justify-content:space-between;
		            padding:12px 12px;
		            margin-top: 18px;
		            border-radius: 18px;
		            background: rgba(255,255,255,0.72);
		            border:1px solid rgba(15,27,50,0.10);
		            box-shadow: 0 18px 50px rgba(15,27,50,0.12);
		            backdrop-filter: blur(10px);
		            -webkit-backdrop-filter: blur(10px);
		        }
	        .folio-subnav .left{
	            display:flex;
	            gap:10px;
	            flex-wrap:wrap;
	            align-items:center;
	        }
	        .folio-subnav .right{
	            display:flex;
	            gap:8px;
	            flex-wrap:wrap;
	            align-items:center;
	        }
		        .folio-subnav .navbtn{
		            display:inline-flex;
		            gap:10px;
		            align-items:center;
		            padding:11px 14px;
		            border-radius:999px;
		            border:1px solid rgba(15,27,50,0.10);
		            background: rgba(238,242,255,0.88);
		            color:#0b1220;
		            font-weight: 950;
		            font-size: 12px;
		            letter-spacing: 0.10em;
		            text-transform: uppercase;
		            font-family:'Poppins','Inter',system-ui,sans-serif;
		            cursor:pointer;
		            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease, border-color 0.25s ease;
		        }
	        .folio-subnav .navbtn:hover{
	            transform: translateY(-2px);
	            box-shadow: 0 18px 44px rgba(0,140,255,0.16);
	            border-color: rgba(0,140,255,0.18);
	        }
	        .folio-subnav .navbtn.is-active{
	            background: linear-gradient(135deg, rgba(29,124,255,0.95), rgba(0,178,255,0.90));
	            border-color: rgba(255,255,255,0.20);
	            color:#fff;
	            box-shadow: 0 22px 60px rgba(0,90,200,0.22);
	        }

	        .folio-filters{
	            margin-top: 10px;
	            display:flex;
	            gap:8px;
	            flex-wrap:wrap;
	            align-items:center;
	        }
		        .filterbtn{
		            padding:11px 14px;
		            border-radius:999px;
		            background: rgba(255,255,255,0.80);
		            border:1px solid rgba(15,27,50,0.10);
		            font-weight: 950;
		            font-size: 12px;
		            cursor:pointer;
		            color:#0b1220;
		            font-family:'Poppins','Inter',system-ui,sans-serif;
		            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease;
		        }
	        .filterbtn:hover{ transform: translateY(-2px); box-shadow: 0 18px 44px rgba(15,27,50,0.12); }
	        .filterbtn.is-active{
	            background: rgba(238,242,255,0.92);
	            border-color: rgba(0,140,255,0.18);
	            box-shadow: 0 18px 44px rgba(0,140,255,0.12);
	        }
	        .work-card.is-off{
	            opacity: 0.25;
	            transform: scale(0.985);
	            pointer-events:none;
	            filter: grayscale(0.6);
	        }
	        .work-card.is-clickable{ cursor:pointer; }
	        .work-card.is-clickable:active{ transform: translateY(-2px); }

	        /* Project modal */
		        .folio-modal .modal-content{
	            border-radius: 20px;
	            overflow:hidden;
	            border: 1px solid rgba(255,255,255,0.12);
	            box-shadow: 0 30px 100px rgba(0,0,0,0.35);
	        }
	        .folio-modal .modal-header{
	            background: linear-gradient(135deg, rgba(29,124,255,0.95), rgba(0,178,255,0.88));
	            color:#fff;
	            border-bottom: 1px solid rgba(255,255,255,0.16);
	        }
	        .folio-modal .modal-title{
	            display:flex;
	            align-items:center;
	            gap:12px;
	            font-weight: 950;
	            letter-spacing:-0.01em;
	            font-family:'Poppins','Inter',system-ui,sans-serif;
	        }
	        .folio-modal .modal-title .logo{
	            width:34px; height:34px;
	            border-radius: 12px;
	            background: rgba(255,255,255,0.18);
	            border: 1px solid rgba(255,255,255,0.22);
	            display:grid;
	            place-items:center;
	            overflow:hidden;
	        }
	        .folio-modal .modal-title .logo img{ width:100%; height:100%; object-fit:cover; }
	        .folio-modal .modal-body{
	            background:
	                radial-gradient(circle at 20% 0%, rgba(0,140,255,0.10), transparent 45%),
	                linear-gradient(180deg, rgba(255,255,255,0.96), rgba(245,248,255,0.94));
	        }
	        .pgrid{
	            display:grid;
	            grid-template-columns: 1.05fr 0.95fr;
	            gap:14px;
	        }
	        .pbox{
	            border-radius: 16px;
	            background: rgba(255,255,255,0.86);
	            border: 1px solid rgba(15,27,50,0.10);
	            box-shadow: 0 18px 50px rgba(15,27,50,0.10);
	            padding: 14px;
	        }
	        .pbox h5{
	            margin:0 0 10px;
	            font-weight: 950;
	            font-size: 14px;
	            color:#0b1220;
	            letter-spacing: 0.12em;
	            text-transform: uppercase;
	        }
	        .plist{ margin:0; padding-left: 18px; color:#23314d; font-size: 14px; line-height:1.6; }
	        .pcover{
	            border-radius: 16px;
	            overflow:hidden;
	            background: #0b1220;
	            border: 1px solid rgba(255,255,255,0.10);
	            box-shadow: 0 26px 70px rgba(0,0,0,0.22);
	            min-height: 240px;
	            position:relative;
	        }
	        .pcover .carousel,
	        .pcover .carousel-inner,
	        .pcover .carousel-item{ height: 100%; }
	        .pcover .carousel-item{ min-height: 240px; }
	        .pcover img{
	            width:100%;
	            height:100%;
	            object-fit: cover;
	            opacity: 0.92;
	        }
	        .pcover::after{
	            content:"";
	            position:absolute;
	            inset:0;
	            background:
	                radial-gradient(circle at 70% 10%, rgba(0,178,255,0.30), transparent 45%),
	                linear-gradient(135deg, rgba(8,23,45,0.35), rgba(8,23,45,0.10));
	        }
	        .pcover .overlay{
	            position:absolute;
	            inset:auto 14px 14px 14px;
	            z-index:1;
	            color:#eaf2ff;
	        }
	        .pcover .overlay .k{ font-weight: 950; font-size: 16px; font-family:'Poppins','Inter',system-ui,sans-serif; }
	        .pcover .overlay .s{ margin-top:4px; font-size: 13px; color: rgba(234,242,255,0.88); line-height:1.4; }
	        .pcover .ph{
	            position:absolute;
	            inset:0;
	            display:grid;
	            place-items:center;
	            z-index:0;
	            color: rgba(234,242,255,0.92);
	            text-align:center;
	            padding: 18px;
	        }
	        .pcover .ph .icon{
	            width: 54px;
	            height: 54px;
	            border-radius: 18px;
	            display:grid;
	            place-items:center;
	            background: rgba(255,255,255,0.14);
	            border:1px solid rgba(255,255,255,0.18);
	            box-shadow: 0 18px 44px rgba(0,0,0,0.30);
	            margin: 0 auto 10px;
	        }
	        .pcover .ph .t{ font-weight: 950; font-family:'Poppins','Inter',system-ui,sans-serif; font-size: 16px; letter-spacing:-0.01em; }
	        .pcover .ph .d{ margin-top: 6px; font-size: 13px; color: rgba(234,242,255,0.82); line-height:1.45; max-width: 48ch; }
	        .pcover .carousel-control-prev,
	        .pcover .carousel-control-next{
	            z-index: 2;
	            width: 52px;
	            opacity: 0.92;
	        }
	        .pcover .carousel-control-prev-icon,
	        .pcover .carousel-control-next-icon{
	            filter: drop-shadow(0 10px 22px rgba(0,0,0,0.55));
	        }
	        @media (max-width: 992px){
	            .pgrid{ grid-template-columns: 1fr; }
	        }
        .folio-grid{
            display:grid;
            gap:16px;
            grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
            margin-top:18px;
        }
	        .folio-card{
	            background: rgba(255,255,255,0.92);
	            border:1px solid rgba(15,27,50,0.08);
	            border-radius:16px;
	            padding:18px;
	            box-shadow:0 12px 30px rgba(15,27,50,0.12);
	        }
	        .folio-card .label{font-weight:800;font-size:12px;letter-spacing:0.05em;text-transform:uppercase;color:#0f172a;margin-bottom:8px;}
	        .folio-card p{margin:0;color:#23314d;font-size:14px;line-height:1.55;}

	        .folio-shell.is-playing .folio-card.reveal,
	        .folio-shell.is-playing .folio-block.reveal,
	        .folio-shell.is-playing .folio-hero.reveal{
	            will-change: transform, opacity;
	        }
 	
	        /* Funnel mockup removed */

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(28px) scale(0.98); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0) scale(1); }
        .delay-1 { transition-delay: 0.08s; }
        .delay-2 { transition-delay: 0.16s; }
        .delay-3 { transition-delay: 0.24s; }
        .delay-4 { transition-delay: 0.32s; }
        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .portfolio-grid {
            margin-top: 28px;
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
        .portfolio-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(15,27,50,0.14);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
            animation: riseIn 0.7s ease backwards;
            color: #0f172a;
            text-align: left;
        }
        .portfolio-card:nth-child(2) { animation-delay: 0.08s; }
        .portfolio-card:nth-child(3) { animation-delay: 0.16s; }
        .portfolio-card:nth-child(4) { animation-delay: 0.24s; }
        .portfolio-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 16px 40px rgba(0,170,255,0.25);
            border-color: rgba(0,170,255,0.25);
        }
        .portfolio-card .label {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 12px;
        }
        .portfolio-card p {
            color: #24324d;
            line-height: 1.5;
            font-size: 14px;
        }
        .portfolio-card img.photo-preview {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.16);
            margin-top: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .portfolio-card img.photo-preview:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 38px rgba(0,170,255,0.25);
        }
        .tagline {
            color: #0b3d6f;
            font-weight: 600;
        }
	        @media (max-width: 992px) {
	            .folio-headline h2{ font-size: 32px; }
	            .folio-kpis{ grid-template-columns: 1fr; }
	            .work-grid{ grid-template-columns: 1fr; }
	            .folio-split{ grid-template-columns: 1fr; }
	            .skill{ grid-template-columns: 1fr; }
	            .folio-hero{ grid-template-columns: 1fr; }
	            .resume-intro h2{ font-size: 34px; }
	            .resume-contact{ grid-template-columns: 1fr; }
	            .resume-category-grid{ grid-template-columns: 1fr; }
	            .portfolio-hero {
	                grid-template-columns: 1fr;
	                text-align: center;
	            }
            .portfolio-hero .portrait img {
                aspect-ratio: 1 / 1;
            }
            .portfolio-hero .intro {
                justify-items: center;
            }
            .cta-row {
                justify-content: center;
            }
        }
	        @media (max-width: 576px){
	            .folio-shell{ padding: 8px; }
	            .folio-hero{ padding: 20px; }
	            .resume-intro h2{ font-size: 28px; }
	            .resume-intro .title{ font-size: 18px; }
	            .resume-category-card .head h3{ font-size: 20px; }
	            .resume-item .title{ font-size: 16px; }
	            .resume-reference strong{ font-size: 20px; }
	        }
        @keyframes floatGlow {
            0%, 100% { transform: translateY(0); box-shadow: 0 20px 40px rgba(0,0,0,0.25); }
            50% { transform: translateY(-8px); box-shadow: 0 28px 50px rgba(0,102,204,0.28); }
        }
        @keyframes riseIn {
            from { opacity: 0; transform: translateY(18px) scale(0.99); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes shine {
            from { transform: translateX(-6%); opacity: 0.9; }
            to { transform: translateX(6%); opacity: 1; }
        }

        /* Portfolio 2026 redesign */
        #contactcontainer{
            --px-bg-1: #f5f9ff;
            --px-bg-2: #eaf2ff;
            --px-bg-3: #deebff;
            --px-ink: #081427;
            --px-sub: #3f526f;
            --px-primary: #0a5aff;
            --px-cyan: #00b5d1;
            --px-deep: #042b78;
            --px-border: rgba(10, 32, 72, 0.14);
            --px-shadow: 0 24px 70px rgba(7, 29, 75, 0.16);
            --px-inner: inset 0 1px 0 rgba(255,255,255,0.7);
            --px-radius-xl: 28px;
            --px-radius-lg: 20px;
            --px-radius-md: 14px;
            background:
                radial-gradient(circle at 2% 8%, rgba(10,90,255,0.14), transparent 34%),
                radial-gradient(circle at 92% 1%, rgba(0,181,209,0.12), transparent 28%),
                linear-gradient(145deg, var(--px-bg-1), var(--px-bg-2) 48%, var(--px-bg-3));
            color: var(--px-ink);
            font-family: 'Inter','Poppins',system-ui,sans-serif;
        }
        #contactcontainer::before{
            background:
                radial-gradient(circle at 18% 12%, rgba(0,122,255,0.2), transparent 44%),
                radial-gradient(circle at 82% 0%, rgba(0,180,255,0.14), transparent 34%),
                linear-gradient(rgba(12,91,255,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(12,91,255,0.05) 1px, transparent 1px);
            background-size: auto, auto, 42px 42px, 42px 42px;
            opacity: 0.75;
        }
        .folio-shell{
            max-width: 1220px;
            margin: 0 auto 46px;
            padding: 0 4px 40px;
        }
        .px-block{
            margin-top: 20px;
            background: linear-gradient(150deg, rgba(255,255,255,0.84), rgba(243,249,255,0.68));
            backdrop-filter: blur(16px) saturate(1.08);
            -webkit-backdrop-filter: blur(16px) saturate(1.08);
            border: 1px solid var(--px-border);
            border-radius: var(--px-radius-xl);
            box-shadow: var(--px-shadow);
            box-shadow: var(--px-shadow), var(--px-inner);
            overflow: hidden;
            position: relative;
            isolation: isolate;
        }
        .px-block::before{
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255,255,255,0.48), rgba(255,255,255,0));
            pointer-events: none;
            z-index: 0;
        }
        .px-block > *{
            position: relative;
            z-index: 1;
        }
        .px-block-head{
            padding: 26px 28px 0;
        }
        .px-kicker{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.16em;
            color: #0e417f;
            text-transform: uppercase;
            font-family: 'Orbitron','Space Grotesk','Poppins',sans-serif;
        }
        .px-kicker::before{
            content: "";
            width: 18px;
            height: 1px;
            background: linear-gradient(90deg, #0c5bff, #00b8d9);
        }
        .px-block-head h3{
            margin: 10px 0 8px;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            font-size: clamp(1.45rem, 2.8vw, 2.2rem);
            line-height: 1.08;
            letter-spacing: -0.02em;
            color: #071327;
        }
        .px-block-head p{
            margin: 0;
            font-size: 15px;
            line-height: 1.62;
            color: var(--px-sub);
            max-width: 75ch;
        }
        .px-hero{
            position: relative;
            padding: 34px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 22px;
            align-items: center;
            isolation: isolate;
            overflow: hidden;
        }
        .px-hero > div:first-child{
            text-align: left;
        }
        .px-hero .px-kicker{
            justify-content: flex-start;
        }
        .px-hero .px-cta-row{
            justify-content: flex-start;
        }
        .px-hero::before{
            content: "";
            position: absolute;
            inset: -30px;
            background:
                radial-gradient(circle at 20% 15%, rgba(12,91,255,0.22), transparent 36%),
                radial-gradient(circle at 88% 10%, rgba(0,184,217,0.22), transparent 28%);
            z-index: -1;
            animation: pxAmbientShift 16s ease-in-out infinite alternate;
        }
        .px-hero::after{
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            right: -80px;
            bottom: -120px;
            background: radial-gradient(circle, rgba(10,90,255,0.22), rgba(10,90,255,0));
            z-index: -1;
            filter: blur(2px);
        }
        .px-hero-title{
            margin: 12px 0;
            font-size: clamp(2rem, 6vw, 4rem);
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            font-weight: 700;
            line-height: 0.95;
            letter-spacing: -0.03em;
            color: #071327;
            max-width: 12ch;
            text-wrap: balance;
        }
        .px-hero-role{
            display: block;
            margin-top: 8px;
            font-size: clamp(0.92rem, 1.6vw, 1.2rem);
            font-family: 'Sora','Space Grotesk','Poppins',sans-serif;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #3d5f89;
            line-height: 1.2;
        }
        .px-hero-sub{
            margin: 0;
            color: #344866;
            font-size: 15px;
            line-height: 1.7;
            max-width: 62ch;
        }
        .px-hero-stats{
            margin-top: 22px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .px-stat{
            background: rgba(255,255,255,0.82);
            border: 1px solid var(--px-border);
            border-radius: 14px;
            padding: 12px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .px-stat:hover{
            transform: translateY(-3px);
            border-color: rgba(10,90,255,0.28);
            box-shadow: 0 14px 30px rgba(10,90,255,0.16);
        }
        .px-stat strong{
            display: block;
            font-size: 24px;
            font-family: 'Orbitron','Space Grotesk','Poppins',sans-serif;
            letter-spacing: -0.02em;
        }
        .px-stat span{
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #355173;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .px-hero-glass{
            width: min(100%, 400px);
            justify-self: end;
            border-radius: 0;
            border: none;
            background: transparent;
            box-shadow: none;
            padding: 0;
            align-self: center;
            display: grid;
            gap: 0;
            position: relative;
            overflow: visible;
        }
        .px-hero-glass::after{
            content: none;
        }
        .px-hero-media{
            width: 100%;
            max-width: 350px;
            margin: 0 0 0 auto;
            aspect-ratio: 3 / 4;
            border-radius: 18px;
            border: 1px solid rgba(95,210,255,0.24);
            background: linear-gradient(145deg, rgba(12,36,70,0.48), rgba(8,25,50,0.40));
            display: grid;
            place-items: stretch;
            padding: 0;
            position: relative;
            overflow: hidden;
            box-shadow:
                inset 6px 6px 12px rgba(0, 0, 0, 0.28),
                inset -6px -6px 12px rgba(255, 255, 255, 0.04),
                0 14px 30px rgba(0, 0, 0, 0.26);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .px-hero-media::before{
            content: none;
        }
        .px-hero-photo{
            position: relative;
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center 12%;
            border-radius: inherit;
            z-index: 1;
            opacity: 1;
            box-shadow: none;
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        .px-hero-media:hover{
            transform: translateY(-6px);
            border-color: rgba(95,210,255,0.42);
            box-shadow:
                inset 6px 6px 12px rgba(0, 0, 0, 0.30),
                inset -6px -6px 12px rgba(255, 255, 255, 0.05),
                0 20px 44px rgba(0, 0, 0, 0.34),
                0 0 28px rgba(0, 145, 255, 0.22);
        }
        .px-hero-media-caption{
            position: relative;
            z-index: 1;
            padding: 10px 12px;
            margin-bottom: 6px;
            border-radius: 12px;
            background: rgba(6, 27, 59, 0.58);
            border: 1px solid rgba(255,255,255,0.24);
            color: rgba(246,250,255,0.95);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }
        .px-hero-badges{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .px-chip{
            padding: 8px 11px;
            border-radius: 999px;
            background: linear-gradient(140deg, rgba(238,246,255,0.96), rgba(226,239,255,0.92));
            border: 1px solid rgba(12,91,255,0.14);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #15335b;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .px-chip:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(10,90,255,0.12);
        }
        .px-cta-row{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        .px-btn{
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 11px 16px;
            text-decoration: none;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, background 0.25s ease, color 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .px-btn:hover{
            transform: translateY(-2px);
        }
        .px-btn::before{
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, rgba(255,255,255,0), rgba(255,255,255,0.34), rgba(255,255,255,0));
            transform: translateX(-130%);
            transition: transform 0.6s ease;
        }
        .px-btn:hover::before{
            transform: translateX(130%);
        }
        .px-btn-primary{
            background: linear-gradient(135deg, #0a5aff, #00a7d7);
            color: #fff;
            box-shadow: 0 16px 34px rgba(12,91,255,0.32);
        }
        .px-btn-light{
            background: rgba(255,255,255,0.88);
            color: #0b2a53;
            border-color: rgba(12,91,255,0.2);
        }
        .px-btn:focus-visible{
            outline: none;
            box-shadow: 0 0 0 3px rgba(10,90,255,0.18), 0 14px 28px rgba(10,90,255,0.22);
        }
        .px-featured-stack{
            padding: 12px 20px 20px;
            display: grid;
            gap: 12px;
            max-width: 1020px;
            margin: 0 auto;
        }
        .px-featured-hero{
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(220px, 0.8fr);
            gap: 12px;
            align-items: center;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid rgba(95,210,255,0.18);
            background: linear-gradient(150deg, rgba(255,255,255,0.09), rgba(255,255,255,0.04));
        }
        .px-featured-label{
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 10px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 800;
            color: #205090;
            padding: 7px 11px;
            border-radius: 999px;
            border: 1px solid rgba(12,91,255,0.16);
            background: rgba(236,246,255,0.9);
        }
        .px-featured-label i{
            color: #0c5bff;
        }
        .px-featured-hero-copy h3{
            margin: 0;
            font-size: clamp(1.12rem, 1.9vw, 1.56rem);
            line-height: 1.16;
            letter-spacing: -0.02em;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            max-width: 30ch;
        }
        .px-featured-hero-copy p{
            margin: 8px 0 0;
            font-size: 13px;
            line-height: 1.55;
            max-width: 56ch;
        }
        .px-featured-points{
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .px-featured-point{
            border: 1px solid rgba(12,91,255,0.12);
            border-radius: 12px;
            padding: 10px 11px;
            background: rgba(237,246,255,0.7);
        }
        .px-featured-point span{
            display: block;
            font-size: 10px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 800;
            color: #28588f;
        }
        .px-featured-point strong{
            display: block;
            margin-top: 3px;
            font-size: 13px;
            color: #0f2f5a;
            line-height: 1.35;
        }
        .px-featured-links{
            margin-top: 11px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .px-featured-link{
            border: 1px solid rgba(12,91,255,0.18);
            background: rgba(255,255,255,0.92);
            color: #11345f;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .px-featured-link:hover{
            transform: translateY(-2px);
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(12,91,255,0.14);
        }
        .px-featured-hero-media{
            justify-self: end;
            width: min(100%, 250px);
        }
        .px-featured-hero-media img{
            width: 100%;
            height: auto;
            max-height: 320px;
            display: block;
            object-fit: contain;
            object-position: center;
            border-radius: 16px;
            border: 1px solid var(--px-border);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24);
        }
        .px-carousel{
            position: relative;
            border-radius: 16px;
            padding: 0 34px;
        }
        .px-carousel-viewport{
            overflow: hidden;
            border-radius: inherit;
        }
        .px-carousel-track{
            display: flex;
            transition: transform 0.5s ease;
            will-change: transform;
        }
        .px-carousel-slide{
            min-width: 100%;
            max-width: 100%;
        }
        .px-carousel .px-project{
            max-width: 930px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
            align-items: stretch;
            border-radius: 16px;
        }
        .px-carousel .px-project-top{
            padding: 14px 16px 8px;
        }
        .px-carousel .px-project-bottom{
            padding: 8px 16px 14px;
        }
        .px-carousel .px-project-media.px-project-photo{
            min-height: 0;
            aspect-ratio: auto;
            border-right: 0;
            border-bottom: 1px solid rgba(10, 32, 72, 0.12);
            border-top: 1px solid rgba(10, 32, 72, 0.12);
            display: grid;
            place-items: center;
            padding: 0 14px;
        }
        .px-carousel .px-project-media.px-project-photo img{
            width: min(100%, 520px);
            height: auto;
            max-height: 230px;
            object-fit: contain;
            object-position: center;
            border-radius: 10px;
            margin: 0 auto;
        }
        .px-carousel .px-project-body{
            padding: 14px 16px;
            display: grid;
            align-content: center;
        }
        .px-carousel .px-project h4{
            font-size: clamp(1rem, 1.45vw, 1.22rem);
            line-height: 1.24;
        }
        .px-carousel .px-project p{
            margin-top: 7px;
            font-size: 13px;
            line-height: 1.52;
            max-width: 48ch;
        }
        .px-carousel .px-project-top p{
            text-align: center;
            width: 100%;
            max-width: none;
            margin-left: auto;
            margin-right: auto;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            min-height: calc(1.52em * 2);
            max-height: calc(1.52em * 2);
        }
        .px-carousel .px-cta-row{
            margin-top: 11px;
            justify-content: center;
        }
        .px-carousel .px-btn{
            padding: 9px 14px;
            font-size: 11px;
        }
        .px-project{
            border-radius: 20px;
            border: 1px solid var(--px-border);
            background: rgba(255,255,255,0.86);
            overflow: hidden;
            position: relative;
            transform: translateY(0);
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }
        .px-project::before{
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(130deg, rgba(10,90,255,0.06), rgba(10,90,255,0));
            pointer-events: none;
        }
        .px-project:hover{
            transform: translateY(-6px);
            box-shadow: 0 24px 60px rgba(12,91,255,0.18);
            border-color: rgba(12,91,255,0.24);
        }
        .px-project-media{
            min-height: 236px;
            border-bottom: 1px solid rgba(10, 32, 72, 0.1);
            background:
                linear-gradient(135deg, rgba(12,91,255,0.14), rgba(0,184,217,0.13));
            position: relative;
            display: grid;
            place-items: center;
            color: #184a82;
            font-weight: 700;
            font-size: 13px;
            text-align: center;
            padding: 20px;
            overflow: hidden;
        }
        .px-project-media.px-project-photo{
            min-height: 240px;
            padding: 0;
            border-bottom: 1px solid rgba(10, 32, 72, 0.12);
            background: linear-gradient(140deg, rgba(8,28,58,0.35), rgba(8,28,58,0.16));
        }
        .px-project-media::after{
            content: "";
            position: absolute;
            inset: 12px;
            border-radius: 14px;
            border: 1px dashed rgba(12,91,255,0.26);
            pointer-events: none;
        }
        .px-project-media.px-project-photo::after,
        .px-project-media.px-project-photo::before{
            content: none;
        }
        .px-project-media.px-project-photo img{
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center 35%;
        }
        .px-project-media::before{
            content: "";
            position: absolute;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            top: -55px;
            right: -35px;
            background: radial-gradient(circle, rgba(255,255,255,0.44), rgba(255,255,255,0));
        }
        .px-project-body{
            padding: 16px;
        }
        .px-project-meta{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-bottom: 10px;
        }
        .px-project-meta-grid{
            margin: 11px 0 10px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .px-project-meta-item{
            border: 1px solid rgba(12,91,255,0.14);
            border-radius: 11px;
            padding: 8px 9px;
            background: rgba(235,246,255,0.7);
        }
        .px-project-meta-item span{
            display: block;
            font-size: 9px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 800;
            color: #2a5b92;
        }
        .px-project-meta-item strong{
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #0f2e58;
            line-height: 1.35;
        }
        .px-project-year{
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #1f4a86;
        }
        .px-project h4{
            margin: 0;
            font-size: 23px;
            line-height: 1.2;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: -0.01em;
            color: #071327;
            text-wrap: balance;
        }
        .px-project p{
            margin: 10px 0 0;
            font-size: 14px;
            line-height: 1.64;
            color: #384c68;
        }
        .px-project-stats{
            margin: 12px 0;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid rgba(12,91,255,0.12);
            background: rgba(238,246,255,0.84);
            color: #183a69;
            font-size: 13px;
            font-weight: 700;
        }
        .px-project-outcomes{
            margin: 0;
            padding-left: 0;
            list-style: none;
            font-size: 13px;
            line-height: 1.55;
            color: #355375;
        }
        .px-project-outcomes li{
            margin-bottom: 4px;
        }
        .px-project-outcomes li:last-child{
            margin-bottom: 0;
        }
        .px-carousel-arrow{
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 1px solid rgba(12,91,255,0.2);
            background: rgba(255,255,255,0.92);
            color: #0b2a53;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
            z-index: 2;
        }
        .px-carousel-arrow:hover{
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 10px 20px rgba(12,91,255,0.2);
            background: rgba(255,255,255,0.98);
        }
        .px-carousel-arrow:focus-visible{
            outline: none;
            box-shadow: 0 0 0 3px rgba(10,90,255,0.24);
        }
        .px-carousel-arrow.prev{
            left: -32px;
        }
        .px-carousel-arrow.next{
            right: -32px;
        }
        .px-case-grid{
            padding: 18px 28px 28px;
            display: grid;
            gap: 12px;
        }
        #px-case-studies .px-block-head p{
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        .px-case{
            border-radius: 18px;
            border: 1px solid var(--px-border);
            background: rgba(255,255,255,0.85);
            overflow: hidden;
            transition: box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .px-case:hover{
            border-color: rgba(10,90,255,0.24);
            box-shadow: 0 18px 42px rgba(10,90,255,0.12);
        }
        .px-case .accordion-button,
        .px-case .accordion-button:not(.collapsed){
            background: transparent;
            color: #071327;
            box-shadow: none;
        }
        .px-case .accordion-button::after{
            display: none;
        }
        .px-case-toggle{
            width: 100%;
            border: 0;
            background: transparent;
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            color: #071327;
            text-align: left;
        }
        .px-case-toggle strong{
            display: block;
            font-size: 18px;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: -0.01em;
        }
        .px-case-toggle span{
            display: block;
            margin-top: 2px;
            color: #426086;
            font-size: 13px;
        }
        .px-case-toggle i{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            border: 1px solid rgba(12,91,255,0.18);
            display: grid;
            place-items: center;
            color: #0c5bff;
            transition: transform 0.25s ease;
        }
        .px-case-toggle[aria-expanded="true"] i{
            transform: rotate(45deg);
        }
        .px-case-content{
            padding: 0 18px 18px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .px-case-box{
            border-radius: 14px;
            border: 1px solid rgba(10, 32, 72, 0.1);
            background: rgba(245,249,255,0.9);
            padding: 12px;
            position: relative;
            overflow: hidden;
        }
        .px-case-box::before{
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #0a5aff, #00b5d1);
            opacity: 0.75;
        }
        .px-case-box h5{
            margin: 0 0 6px;
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #205090;
        }
        .px-case-box p, .px-case-box ul{
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
            color: #304c6d;
        }
        .px-case-box ul{
            padding-left: 18px;
        }
        .px-case-media{
            min-height: 130px;
            border-radius: 12px;
            border: 1px dashed rgba(12,91,255,0.24);
            background: rgba(230,241,255,0.8);
            display: grid;
            place-items: center;
            text-align: center;
            color: #275487;
            font-size: 12px;
            font-weight: 700;
            padding: 14px;
        }
        .px-skills{
            padding: 18px 28px 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .px-skill-card{
            border: 1px solid var(--px-border);
            border-radius: 18px;
            background: rgba(255,255,255,0.85);
            padding: 16px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .px-skill-card:hover{
            transform: translateY(-3px);
            border-color: rgba(10,90,255,0.24);
            box-shadow: 0 18px 42px rgba(10,90,255,0.14);
        }
        .px-skill-card h4{
            margin: 0 0 12px;
            font-size: 15px;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .px-skill{
            margin-bottom: 10px;
        }
        .px-skill:last-child{
            margin-bottom: 0;
        }
        .px-skill-top{
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 6px;
            font-size: 13px;
            color: #223a5b;
            font-weight: 700;
        }
        .px-skill-track{
            height: 8px;
            border-radius: 999px;
            background: rgba(15,27,50,0.1);
            overflow: hidden;
        }
        .px-skill-fill{
            display: block;
            width: 0;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0c5bff, #00b8d9);
            box-shadow: 0 8px 16px rgba(12,91,255,0.3);
            transition: width 1.2s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        .px-skill-fill.is-on{
            width: var(--skill-level, 60%);
        }
        .px-process{
            padding: 18px 28px 28px;
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            position: relative;
        }
        .px-step{
            border-radius: 16px;
            border: 1px solid var(--px-border);
            background: rgba(255,255,255,0.88);
            padding: 14px 12px;
            min-height: 166px;
            display: grid;
            align-content: start;
            gap: 8px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .px-step:hover{
            transform: translateY(-3px);
            border-color: rgba(10,90,255,0.24);
            box-shadow: 0 16px 34px rgba(10,90,255,0.12);
        }
        .px-step strong{
            font-size: 12px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #1d4f94;
        }
        .px-step h4{
            margin: 0;
            font-size: 18px;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            line-height: 1.2;
            color: #08182e;
        }
        .px-step p{
            margin: 0;
            font-size: 13px;
            color: #405876;
            line-height: 1.55;
        }
        .px-credentials{
            --px-cred-surface: linear-gradient(155deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
            --px-cred-border: rgba(95,210,255,0.20);
            --px-cred-muted: rgba(218,238,255,0.82);
            --px-cred-line: rgba(95,210,255,0.26);
            --px-cred-chip: linear-gradient(145deg, rgba(0,170,255,0.22), rgba(7,26,58,0.58));
            --px-cred-icon-bg: linear-gradient(145deg, rgba(95,210,255,0.22), rgba(7,26,58,0.56));
            --px-cred-ring: rgba(95,210,255,0.42);
        }
        .px-cred-head h3{
            max-width: 26ch;
        }
        .px-cred-head p{
            max-width: 62ch;
            margin: 10px auto 0;
            text-align: center;
        }
        .px-cred-grid{
            padding: 18px 28px 28px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            align-items: stretch;
        }
        .px-cred-card{
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--px-cred-border);
            background: var(--px-cred-surface);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 18px 40px rgba(0,0,0,0.28);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 100%;
            transition: transform 0.28s ease, border-color 0.28s ease, box-shadow 0.28s ease;
            animation: pxCredIn 0.62s ease both;
        }
        .px-cred-card::before{
            content: "";
            position: absolute;
            inset: -2px;
            z-index: -1;
            pointer-events: none;
            background: radial-gradient(420px 140px at 10% 0%, rgba(95,210,255,0.18), transparent 60%);
            opacity: 0.9;
        }
        .px-cred-card:nth-child(2){
            animation-delay: 0.08s;
        }
        .px-cred-card:nth-child(3){
            animation-delay: 0.16s;
        }
        .px-cred-card:hover{
            transform: translateY(-5px);
            border-color: var(--px-cred-ring);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.14), 0 24px 40px rgba(0,0,0,0.34), 0 0 0 1px rgba(95,210,255,0.22);
        }
        .px-cred-card:focus-visible{
            outline: none;
            border-color: var(--px-cred-ring);
            box-shadow: 0 0 0 3px rgba(95,210,255,0.24), inset 0 1px 0 rgba(255,255,255,0.14), 0 24px 40px rgba(0,0,0,0.34);
        }
        .px-cred-top{
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .px-cred-icon{
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(95,210,255,0.32);
            background: var(--px-cred-icon-bg);
            display: grid;
            place-items: center;
            color: #d8f1ff;
            font-size: 16px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.16);
        }
        .px-cred-title{
            min-width: 0;
        }
        .px-cred-title h4{
            margin: 0;
            font-size: 18px;
            line-height: 1.15;
            color: #eef9ff;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: 0.01em;
        }
        .px-cred-title p{
            margin: 2px 0 0;
            font-size: 12px;
            color: #8fd8ff;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: 'JetBrains Mono','Inter',monospace;
        }
        .px-cred-badge{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid rgba(95,210,255,0.30);
            background: var(--px-cred-chip);
            color: #d8f1ff;
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .px-cred-list{
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }
        .px-cred-item{
            margin: 0;
            padding: 10px 12px 10px 14px;
            border: 1px solid rgba(95,210,255,0.14);
            border-radius: 12px;
            background: linear-gradient(145deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
            position: relative;
        }
        .px-cred-item::before{
            content: "";
            position: absolute;
            left: 0;
            top: 10px;
            bottom: 10px;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(180deg, #37cdff, #0066cc);
            opacity: 0.9;
        }
        .px-cred-item-head{
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }
        .px-cred-item h5{
            margin: 0;
            font-size: 14px;
            color: #f0f9ff;
            font-weight: 600;
            line-height: 1.35;
            font-family: 'Inter','Poppins',sans-serif;
        }
        .px-cred-item time{
            font-size: 11px;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #8fd8ff;
            font-family: 'JetBrains Mono','Inter',monospace;
            white-space: nowrap;
        }
        .px-cred-item p{
            margin: 0;
            font-size: 13px;
            line-height: 1.58;
            color: var(--px-cred-muted);
        }
        .px-cred-footer{
            padding-top: 10px;
            border-top: 1px solid var(--px-cred-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .px-cred-label{
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8fd8ff;
            font-family: 'JetBrains Mono','Inter',monospace;
        }
        .px-cred-flag{
            font-size: 13px;
            color: #eaf8ff;
            font-weight: 600;
        }
        .px-cred-ref{
            margin: 0;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(95,210,255,0.18);
            background: linear-gradient(145deg, rgba(0,170,255,0.12), rgba(7,26,58,0.46));
            color: #d7efff;
            font-size: 13px;
            line-height: 1.55;
        }
        .px-cred-ref strong{
            color: #f0f9ff;
        }
        @keyframes pxCredIn{
            from{
                opacity: 0;
                transform: translateY(16px);
            }
            to{
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (prefers-reduced-motion: reduce){
            .px-cred-card{
                animation: none;
            }
        }
        .px-final-cta{
            margin-top: 20px;
            border-radius: var(--px-radius-xl);
            padding: clamp(20px, 2.4vw, 30px);
            color: #fff;
            background:
                radial-gradient(620px 320px at -8% 110%, rgba(15,150,255,0.20), transparent 64%),
                radial-gradient(420px 300px at 102% -12%, rgba(126,218,255,0.24), transparent 66%),
                linear-gradient(120deg, #071d47, #0a4d9f 52%, #1690da);
            border: 1px solid rgba(255,255,255,0.22);
            box-shadow: 0 30px 76px rgba(6, 33, 88, 0.42), inset 0 1px 0 rgba(255,255,255,0.10);
            display: grid;
            grid-template-columns: 1.12fr 0.88fr;
            align-items: center;
            gap: clamp(8px, 1.1vw, 14px);
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }
        .px-final-cta::after{
            content: "";
            position: absolute;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            top: -170px;
            right: -110px;
            background: radial-gradient(circle, rgba(255,255,255,0.20), rgba(255,255,255,0));
            pointer-events: none;
            z-index: 0;
        }
        .px-final-cta::before{
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 34px 34px, 34px 34px;
            opacity: 0.14;
            pointer-events: none;
            z-index: 0;
        }
        .px-final-copy{
            min-width: 0;
            text-align: left;
            position: relative;
            z-index: 2;
        }
        .px-final-cta h3{
            margin: 0;
            font-size: clamp(1.55rem, 2.9vw, 2.75rem);
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            line-height: 0.94;
            letter-spacing: -0.03em;
            max-width: none;
            text-wrap: balance;
        }
        .px-final-cta h3 .px-final-line{
            display: block;
            white-space: nowrap;
        }
        .px-final-cta p{
            margin: 16px 0 0;
            font-size: clamp(0.95rem, 1.05vw, 1.1rem);
            color: rgba(237,249,255,0.86);
            line-height: 1.55;
            max-width: 60ch;
        }
        .px-final-actions{
            margin-top: 14px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .px-final-actions .px-btn-primary{
            background: linear-gradient(135deg, #ecf8ff, #c9eaff);
            color: #063f7a;
            box-shadow: 0 18px 36px rgba(3,20,54,0.34), 0 0 0 0 rgba(95,210,255,0.42);
            transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease;
        }
        .px-final-actions .px-btn-primary:hover{
            transform: translateY(-4px);
            filter: saturate(1.04);
            box-shadow: 0 24px 46px rgba(3,20,54,0.42), 0 0 0 10px rgba(95,210,255,0.16);
        }
        .px-final-visual{
            position: relative;
            min-height: 220px;
            display: grid;
            place-items: center end;
            justify-self: end;
            z-index: 2;
        }
        .px-final-halo{
            position: absolute;
            width: min(380px, 92%);
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 52%, rgba(100,216,255,0.52), rgba(25,137,236,0.12) 48%, transparent 72%);
            filter: blur(11px);
            bottom: 14px;
            right: -8%;
            pointer-events: none;
        }
        .px-final-glass{
            position: absolute;
            width: min(228px, 70%);
            height: 100%;
            right: 0;
            top: 0;
            border-radius: 24px;
            border: 1px solid rgba(195,233,255,0.24);
            background: linear-gradient(160deg, rgba(255,255,255,0.14), rgba(255,255,255,0.03));
            backdrop-filter: blur(9px) saturate(1.05);
            -webkit-backdrop-filter: blur(9px) saturate(1.05);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.16),
                inset 0 -24px 40px rgba(0,0,0,0.12),
                0 20px 48px rgba(0,0,0,0.30);
        }
        .px-final-portrait{
            width: min(228px, 70%);
            height: 100%;
            max-width: none;
            max-height: none;
            object-fit: cover;
            object-position: center center;
            display: block;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 22px 30px rgba(0,0,0,0.34));
            transition: transform 0.35s ease;
        }
        .px-final-visual:hover .px-final-portrait{
            transform: translateY(-5px) scale(1.015);
        }
        #contactcontainer::-webkit-scrollbar{
            width: 10px;
        }
        #contactcontainer::-webkit-scrollbar-track{
            background: rgba(6, 31, 70, 0.08);
        }
        #contactcontainer::-webkit-scrollbar-thumb{
            background: linear-gradient(180deg, #0a5aff, #00b5d1);
            border-radius: 999px;
        }
        .px-reveal{
            opacity: 0;
            transform: translateY(26px) scale(0.98);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .px-reveal.visible{
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        @media (max-width: 1100px){
            .px-process{
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .px-cred-grid{
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .px-cred-grid .px-cred-card:last-child{
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 992px){
            .px-hero{
                grid-template-columns: 1fr;
                padding: 28px 22px;
            }
            .px-hero-glass{
                width: min(100%, 380px);
            }
            .px-hero-title{
                max-width: 16ch;
            }
            .px-skills{
                grid-template-columns: 1fr;
            }
            .px-case-content{
                grid-template-columns: 1fr;
            }
            .px-featured-hero{
                grid-template-columns: 1fr;
                padding: 12px;
            }
            .px-featured-hero-media{
                justify-self: start;
                width: min(100%, 220px);
            }
            .px-featured-points{
                grid-template-columns: 1fr;
            }
            .px-carousel{
                padding: 0 28px;
            }
            .px-carousel .px-project{
                grid-template-columns: 1fr;
            }
            .px-carousel .px-project-top,
            .px-carousel .px-project-bottom{
                height: auto;
                min-height: 0;
                overflow: visible;
            }
            .px-carousel .px-project h4,
            .px-carousel .px-project p{
                max-width: 100%;
                white-space: normal;
                overflow: visible;
                word-break: break-word;
                overflow-wrap: anywhere;
            }
            .px-carousel .px-project h4{
                text-wrap: wrap;
            }
            .px-carousel .px-project-media.px-project-photo{
                min-height: 190px;
                border-right: 0;
                border-bottom: 1px solid rgba(10, 32, 72, 0.12);
            }
            .px-project-meta-grid{
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px){
            #contactcontainer .px-hero{
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            #contactcontainer .px-hero > div:first-child{
                order: 2;
                width: 100%;
            }
            #contactcontainer .px-hero-glass{
                order: 1;
                margin: 0 auto 12px;
                width: min(100%, 250px);
                justify-self: center;
                position: relative;
                padding-top: 26px;
            }
            #contactcontainer .px-hero-glass::before{
                content: "PORTFOLIO";
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #92dbff;
                font-family: 'Orbitron','Space Grotesk','Poppins',sans-serif;
                white-space: nowrap;
            }
            #contactcontainer .px-hero-media{
                margin: 0 auto;
            }
            #contactcontainer .px-hero > div:first-child .px-kicker{
                display: none;
            }
            #contactcontainer .px-hero > div:first-child{
                text-align: center;
            }
            #contactcontainer .px-hero .px-kicker{
                justify-content: center;
            }
            #contactcontainer .px-hero .px-cta-row{
                justify-content: center;
            }
            #contactcontainer .px-hero-title{
                font-size: clamp(2.2rem, 9vw, 3rem) !important;
                line-height: 1.02;
                letter-spacing: -0.02em;
                font-weight: 800;
                max-width: 18ch;
                margin-left: auto;
                margin-right: auto;
            }
            #contactcontainer .px-hero-role{
                margin-top: 6px;
                font-size: clamp(0.78rem, 3.2vw, 0.95rem);
                letter-spacing: 0.08em;
                line-height: 1.2;
            }
            #contactcontainer .px-hero-sub{
                text-align: center;
                margin-left: auto;
                margin-right: auto;
            }
            #px-featured-projects .px-carousel-arrow{
                display: none;
            }
            #px-featured-projects .px-carousel{
                padding: 0 8px;
            }
            #px-featured-projects .px-carousel .px-project-top,
            #px-featured-projects .px-carousel .px-project-bottom{
                min-width: 0;
                max-width: 100%;
            }
            #px-featured-projects .px-carousel .px-carousel-slide{
                min-width: 100%;
                max-width: 100%;
            }
            #px-featured-projects .px-carousel .px-project-top{
                padding: 10px 10px 8px;
                overflow: visible;
            }
            #px-featured-projects .px-carousel .px-project-bottom{
                padding: 8px 10px 10px;
                overflow: visible;
            }
            #px-featured-projects .px-carousel .px-project h4{
                font-size: 0.95rem;
                line-height: 1.3;
                letter-spacing: 0;
                margin: 0;
                text-wrap: wrap;
                word-break: break-word;
                overflow-wrap: anywhere;
                white-space: normal;
                overflow: visible;
            }
            #px-featured-projects .px-carousel .px-project p{
                margin-top: 8px;
                font-size: 12px;
                line-height: 1.5;
                max-width: 100%;
                text-wrap: wrap;
                word-break: break-word;
                overflow-wrap: anywhere;
                white-space: normal;
                overflow: visible;
            }
            #px-featured-projects .px-carousel .px-project-media.px-project-photo img{
                transform: translateX(-4px);
            }
            #px-featured-projects .px-project-year{
                font-size: 10px;
                letter-spacing: 0.1em;
                white-space: normal;
            }
        }
        @media (max-width: 640px){
            .px-block-head,
            .px-featured-stack,
            .px-case-grid,
            .px-skills,
            .px-process,
            .px-cred-grid{
                padding-left: 16px;
                padding-right: 16px;
            }
            .px-carousel-arrow{
                width: 32px;
                height: 32px;
            }
            .px-carousel-arrow.prev{
                left: -20px;
            }
            .px-carousel-arrow.next{
                right: -20px;
            }
            .px-carousel{
                padding: 0 24px;
            }
            #px-featured-projects .px-carousel-viewport > .px-kicker{
                display: flex;
                width: 100%;
                max-width: 100%;
                align-items: center;
                gap: 6px;
                margin-bottom: 10px;
                font-size: 10px;
                letter-spacing: 0.1em;
                line-height: 1.3;
                white-space: normal;
                overflow-wrap: anywhere;
            }
            #px-featured-projects .px-carousel-viewport > br{
                display: none;
            }
            .px-carousel .px-project-media.px-project-photo{
                min-height: 160px;
                aspect-ratio: 16 / 10;
            }
            .px-carousel .px-project-top{
                padding: 12px 12px 8px;
            }
            .px-carousel .px-project-bottom{
                padding: 8px 12px 12px;
            }
            .px-carousel .px-project h4{
                font-size: 1rem;
                line-height: 1.28;
                overflow-wrap: anywhere;
            }
            .px-carousel .px-project-top p{
                display: block;
                -webkit-line-clamp: unset;
                min-height: 0;
                max-height: none;
                overflow: visible;
                line-height: 1.5;
            }
            .px-project-stats{
                margin: 10px 0;
                line-height: 1.45;
                overflow-wrap: anywhere;
            }
            .px-carousel .px-cta-row{
                margin-top: 10px;
                flex-wrap: wrap;
            }
            .px-carousel .px-btn{
                width: 100%;
                justify-content: center;
                white-space: normal;
                text-align: center;
            }
            .px-hero-stats{
                grid-template-columns: 1fr;
            }
            .px-process{
                grid-template-columns: 1fr;
            }
            .px-cred-grid{
                grid-template-columns: 1fr;
            }
            .px-cred-grid .px-cred-card:last-child{
                grid-column: auto;
            }
            .px-cred-card{
                padding: 14px;
            }
            .px-cred-top{
                grid-template-columns: auto 1fr;
            }
            .px-cred-badge{
                grid-column: 1 / -1;
                justify-self: start;
            }
            .px-cred-item-head{
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .px-final-cta{
                padding: 18px 14px;
                grid-template-columns: 1fr;
            }
            .px-final-copy{
                text-align: center;
            }
            .px-final-cta h3 .px-final-line{
                white-space: normal;
            }
            .px-final-actions{
                justify-content: center;
            }
            .px-final-visual{
                min-height: 220px;
                width: 100%;
                justify-self: center;
                place-items: center;
            }
            .px-final-glass,
            .px-final-portrait{
                width: min(188px, 54vw);
            }
            .px-final-glass{
                right: auto;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
            }
            .px-final-portrait{
                width: min(188px, 54vw);
                height: 100%;
                max-width: none;
            }
        }
        @media (max-width: 480px){
            #px-featured-projects .px-carousel-viewport > .px-kicker{
                font-size: 9px;
                letter-spacing: 0.08em;
            }
            .px-carousel{
                padding: 0 20px;
            }
            .px-carousel .px-project-media.px-project-photo{
                min-height: 150px;
            }
            .px-carousel .px-project-top,
            .px-carousel .px-project-bottom{
                padding-left: 10px;
                padding-right: 10px;
            }
        }
        @keyframes pxAmbientShift{
            0%{ transform: translate3d(0,0,0) scale(1); }
            100%{ transform: translate3d(8px,-6px,0) scale(1.04); }
        }

        /* Portfolio palette sync with side nav */
        #contactcontainer{
            --px-bg-1: #041124;
            --px-bg-2: #071a39;
            --px-bg-3: #06152d;
            --px-ink: #ecf7ff;
            --px-sub: rgba(218, 238, 255, 0.82);
            --px-primary: #0066cc;
            --px-cyan: #00aaff;
            --px-deep: #03102a;
            --px-border: rgba(95, 210, 255, 0.22);
            --px-shadow: 0 30px 80px rgba(0,0,0,0.42);
            --px-inner: inset 0 1px 0 rgba(255,255,255,0.08);
            background:
                radial-gradient(700px 340px at 86% -8%, rgba(0,170,255,0.22), transparent 62%),
                radial-gradient(560px 280px at 10% 105%, rgba(0,92,190,0.22), transparent 68%),
                linear-gradient(180deg, var(--px-bg-1), var(--px-bg-2) 48%, var(--px-bg-3));
            color: var(--px-ink);
        }
        #contactcontainer::before{
            background:
                linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px),
                radial-gradient(circle at 15% 10%, rgba(0,170,255,0.16), transparent 42%);
            background-size: 26px 26px, 26px 26px, auto;
            opacity: 0.32;
        }
        .px-block{
            background: linear-gradient(180deg, rgba(255,255,255,0.09), rgba(255,255,255,0.04));
            border: 1px solid var(--px-border);
            box-shadow: var(--px-shadow), var(--px-inner);
        }
        .px-block::before{
            background: linear-gradient(120deg, rgba(95,210,255,0.10), rgba(255,255,255,0));
        }
        .px-kicker{
            color: #92dbff;
            text-shadow: 0 0 18px rgba(95,210,255,0.24);
        }
        .px-block-head h3,
        .px-hero-title,
        .px-project h4,
        .px-case-toggle strong,
        .px-step h4,
        .px-final-cta h3{
            color: #f0f9ff;
        }
        .px-block-head p,
        .px-hero-sub,
        .px-project p,
        .px-case-box p,
        .px-case-box ul,
        .px-step p,
        .px-quote p{
            color: rgba(218,238,255,0.82);
        }
        .px-stat,
        .px-project,
        .px-case,
        .px-skill-card,
        .px-step,
        .px-quote{
            background: linear-gradient(160deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
            border-color: rgba(95,210,255,0.24);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 18px 44px rgba(0,0,0,0.30);
        }
        .px-stat strong{
            color: #cbecff;
            text-shadow: 0 0 18px rgba(95,210,255,0.22);
        }
        .px-stat span,
        .px-project-year,
        .px-case-toggle span,
        .px-step strong,
        .px-person span{
            color: #92dbff;
        }
        .px-hero-role{
            color: #92dbff;
            text-shadow: 0 0 18px rgba(95,210,255,0.22);
        }
        .px-hero-glass{
            background: transparent;
            border-color: transparent;
            box-shadow: none;
        }
        .px-hero-media{
            border-color: rgba(95,210,255,0.24);
            color: #d7efff;
            background: linear-gradient(145deg, rgba(7,28,58,0.60), rgba(7,22,46,0.48));
            box-shadow:
                inset 6px 6px 12px rgba(0,0,0,0.32),
                inset -6px -6px 12px rgba(255,255,255,0.04),
                0 14px 30px rgba(0,0,0,0.32);
        }
        .px-hero-media-caption{
            background: rgba(3,16,42,0.64);
            border-color: rgba(95,210,255,0.28);
            color: #e9f7ff;
        }
        .px-chip{
            background: linear-gradient(145deg, rgba(95,210,255,0.16), rgba(8,31,68,0.58));
            color: #ccedff;
            border-color: rgba(95,210,255,0.30);
        }
        .px-btn-primary{
            background: linear-gradient(135deg, #0066cc, #00aaff);
            border-color: rgba(255,255,255,0.16);
            box-shadow: 0 16px 34px rgba(0,120,255,0.36);
        }
        .px-btn-light{
            background: linear-gradient(145deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08));
            color: #d8efff;
            border-color: rgba(95,210,255,0.36);
        }
        .px-project-stats{
            background: linear-gradient(145deg, rgba(0,170,255,0.12), rgba(6,27,59,0.52));
            border-color: rgba(95,210,255,0.30);
            color: #d5edff;
        }
        .px-featured-hero-copy h3{
            color: #f0f9ff;
        }
        .px-featured-hero-copy p{
            color: rgba(218,238,255,0.82);
        }
        .px-featured-label{
            color: #bfe8ff;
            border-color: rgba(95,210,255,0.30);
            background: linear-gradient(145deg, rgba(95,210,255,0.14), rgba(8,31,68,0.58));
        }
        .px-featured-label i{
            color: #9cdeff;
        }
        .px-featured-point{
            border-color: rgba(95,210,255,0.24);
            background: linear-gradient(145deg, rgba(0,170,255,0.10), rgba(6,27,59,0.48));
        }
        .px-featured-point span{
            color: #8fd9ff;
        }
        .px-featured-point strong{
            color: #d6edff;
        }
        .px-featured-link{
            border-color: rgba(95,210,255,0.34);
            background: linear-gradient(145deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08));
            color: #d6edff;
        }
        .px-featured-link:hover{
            background: linear-gradient(145deg, rgba(255,255,255,0.22), rgba(255,255,255,0.12));
            box-shadow: 0 10px 20px rgba(0,170,255,0.22);
        }
        .px-project-meta-item{
            border-color: rgba(95,210,255,0.24);
            background: linear-gradient(145deg, rgba(0,170,255,0.10), rgba(6,27,59,0.46));
        }
        .px-project-meta-item span{
            color: #8fd9ff;
        }
        .px-project-meta-item strong{
            color: #d6edff;
        }
        .px-featured-hero-media img{
            border-color: rgba(95,210,255,0.24);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.34);
        }
        .px-carousel-arrow{
            background: rgba(6,22,52,0.9);
            border-color: rgba(95,210,255,0.36);
            color: #d8efff;
        }
        .px-carousel-arrow:hover{
            background: rgba(10,30,66,0.96);
            box-shadow: 0 14px 26px rgba(0,170,255,0.24);
        }
        .px-case-box{
            background: linear-gradient(150deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04));
            border-color: rgba(95,210,255,0.24);
        }
        .px-case-box h5{
            color: #8cd8ff;
        }
        .px-case-media{
            border-color: rgba(95,210,255,0.30);
            background: linear-gradient(140deg, rgba(0,170,255,0.16), rgba(7,26,58,0.68));
            color: #d7efff;
        }
        .px-project-outcomes{
            color: rgba(218,238,255,0.84);
        }
        .px-skill-top{
            color: #caebff;
        }
        .px-skill-track{
            background: rgba(255,255,255,0.14);
        }
        .px-skill-fill{
            background: linear-gradient(90deg, #0066cc, #00aaff);
            box-shadow: 0 8px 16px rgba(0,140,255,0.38);
        }
        .px-credentials{
            --px-cred-border: rgba(95,210,255,0.24);
            --px-cred-line: rgba(95,210,255,0.24);
            --px-cred-chip: linear-gradient(145deg, rgba(95,210,255,0.20), rgba(8,31,68,0.62));
            --px-cred-icon-bg: linear-gradient(140deg, rgba(0,170,255,0.22), rgba(8,31,68,0.66));
            --px-cred-ring: rgba(95,210,255,0.44);
        }
        .px-cred-item p{
            color: rgba(218,238,255,0.82);
        }
        .px-final-cta{
            background:
                radial-gradient(circle at 10% 10%, rgba(95,210,255,0.26), transparent 34%),
                linear-gradient(135deg, rgba(6,22,52,0.96), rgba(0,102,204,0.92), rgba(0,170,255,0.85));
            border-color: rgba(95,210,255,0.28);
        }
        .px-final-cta p{
            color: rgba(232,247,255,0.84);
        }
        .px-final-glass{
            border-color: rgba(95,210,255,0.30);
            background: linear-gradient(160deg, rgba(95,210,255,0.16), rgba(8,31,68,0.30));
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.10),
                inset 0 -24px 40px rgba(0,0,0,0.16),
                0 20px 44px rgba(0,0,0,0.34);
        }
        #contactcontainer::-webkit-scrollbar-track{
            background: rgba(255,255,255,0.10);
        }
        #contactcontainer::-webkit-scrollbar-thumb{
            background: linear-gradient(180deg, #0066cc, #00aaff);
        }

        /* Unified design system: About + Services */
        #aboutcon,
        #servicescon{
            background:
                radial-gradient(700px 340px at 86% -8%, rgba(0,170,255,0.20), transparent 62%),
                radial-gradient(560px 280px at 10% 105%, rgba(0,92,190,0.20), transparent 68%),
                linear-gradient(180deg, #041124, #071a39 48%, #06152d) !important;
            box-shadow: inset 0 0 80px rgba(0,0,0,0.30);
            border: 1px solid rgba(95,210,255,0.14);
            border-radius: 28px;
            position: relative;
            isolation: isolate;
        }
        #aboutcon{
            background: linear-gradient(160deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)) !important;
            border: 1px solid rgba(95,210,255,0.22);
            backdrop-filter: blur(18px) saturate(1.05);
            -webkit-backdrop-filter: blur(18px) saturate(1.05);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.10),
                0 24px 64px rgba(0,0,0,0.34);
        }
        #aboutcon::before,
        #servicescon::before{
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 24px 24px, 24px 24px;
            opacity: 0.18;
            z-index: 0;
        }
        #aboutcon > *,
        #servicescon > *{
            position: relative;
            z-index: 1;
        }
        #aboutcon{
            padding: 64px 44px;
            margin: 16px;
        }
        #aboutcon::-webkit-scrollbar-track,
        #servicescon::-webkit-scrollbar-track{
            background: rgba(255,255,255,0.10);
            border-radius: 999px;
        }
        #aboutcon::-webkit-scrollbar,
        #servicescon::-webkit-scrollbar{
            width: 10px;
        }
        #aboutcon::-webkit-scrollbar-thumb,
        #servicescon::-webkit-scrollbar-thumb{
            background: linear-gradient(180deg, #0066cc, #00aaff);
            border-radius: 999px;
        }
        #aboutcon .about-card{
            background: linear-gradient(160deg, rgba(255,255,255,0.14), rgba(255,255,255,0.06));
            border: 1px solid rgba(95,210,255,0.24);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 20px 50px rgba(0,0,0,0.32);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        #aboutcon .about-card::before{
            background: linear-gradient(100deg, transparent, rgba(95,210,255,0.12), transparent);
        }
        #aboutcon .about-card:hover{
            border-color: rgba(95,210,255,0.42);
            box-shadow: 0 24px 62px rgba(0,0,0,0.36), 0 0 0 1px rgba(95,210,255,0.18) inset;
            transform: translateY(-8px) scale(1.01);
        }
        #aboutcon h2{
            color: #ecf7ff;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: -0.02em;
        }
        #aboutcon h2::before{
            background: linear-gradient(180deg, #0066cc, #00aaff);
            box-shadow: 0 0 14px rgba(95,210,255,0.28);
        }
        #aboutcon h2:hover{
            color: #cceeff;
        }
        #aboutcon p{
            color: rgba(218,238,255,0.84);
        }
        #aboutcon .about-icon{
            background: linear-gradient(135deg, #0066cc, #00aaff);
            box-shadow: 0 8px 22px rgba(0,120,255,0.38);
            border: 1px solid rgba(255,255,255,0.16);
        }

        #servicescon{
            padding: 48px 26px;
            background: linear-gradient(160deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03)) !important;
            border: 1px solid rgba(95,210,255,0.20);
            backdrop-filter: blur(18px) saturate(1.04);
            -webkit-backdrop-filter: blur(18px) saturate(1.04);
        }
        #servicescon .services-panel{
            background:
                radial-gradient(700px 320px at 88% -8%, rgba(0,170,255,0.24), transparent 64%),
                radial-gradient(560px 260px at 8% 108%, rgba(0,92,190,0.24), transparent 68%),
                linear-gradient(180deg, rgba(5,18,44,0.95), rgba(4,12,30,0.95));
            border: 1px solid rgba(95,210,255,0.24);
            border-radius: 30px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 28px 72px rgba(0,0,0,0.42);
        }
        #servicescon .services-hero-card{
            background: linear-gradient(160deg, rgba(255,255,255,0.10), rgba(255,255,255,0.03));
            border-color: rgba(95,210,255,0.28);
        }
        #servicescon .services-hero-card .eyebrow{
            color: #8edaff;
            font-family: 'Orbitron','Space Grotesk','Poppins',sans-serif;
            letter-spacing: 0.24em;
        }
        #servicescon .services-hero-card h2{
            color: #eff8ff;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: -0.02em;
        }
        #servicescon .services-hero-card p{
            color: rgba(218,238,255,0.82);
        }
        #servicescon .service-cta{
            border-radius: 999px;
            border-color: rgba(95,210,255,0.26);
            letter-spacing: 0.10em;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px 18px;
        }
        #servicescon .service-cta.primary{
            background: linear-gradient(135deg, #0066cc, #00aaff);
            box-shadow: 0 12px 30px rgba(0,120,255,0.38);
        }
        #servicescon .service-cta.ghost{
            background: linear-gradient(145deg, rgba(255,255,255,0.14), rgba(255,255,255,0.06));
            color: #d8efff;
            border-color: rgba(95,210,255,0.34);
        }
        #servicescon .service-cta:hover{
            box-shadow: 0 16px 34px rgba(0,140,255,0.32);
        }
        #servicescon .services-card{
            background: linear-gradient(160deg, rgba(255,255,255,0.12), rgba(255,255,255,0.05));
            border: 1px solid rgba(95,210,255,0.24);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10), 0 18px 44px rgba(0,0,0,0.32);
        }
        #servicescon .services-card:hover{
            border-color: rgba(95,210,255,0.42);
            box-shadow: 0 24px 64px rgba(0,0,0,0.40), 0 0 0 1px rgba(95,210,255,0.18) inset;
        }
        #servicescon .services-card-icon{
            background: linear-gradient(145deg, #0066cc, #00aaff);
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: 0 12px 30px rgba(0,120,255,0.36);
        }
        #servicescon .services-card-body h3{
            color: #eef8ff;
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
        }
        #servicescon .services-card-body p{
            color: rgba(218,238,255,0.84);
        }
        #servicescon .empty-card{
            border-color: rgba(95,210,255,0.34);
            color: #d9eeff;
            background: linear-gradient(160deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03));
        }

        @media (max-width: 992px){
            #aboutcon{
                padding: 44px 20px;
                margin: 10px;
            }
            #servicescon{
                padding: 34px 14px;
            }
            #servicescon .services-panel{
                padding: 22px;
                border-radius: 24px;
            }
        }

        /* Unified design system: Home posting feature only */
        #title .products-container{
            max-width: none;
            width: var(--home-shell-width);
            box-sizing: border-box;
            padding: 0;
            margin: 34px auto 42px;
        }
        #title .first-post-wrapper,
        #title #homePosts{
            max-width: none;
            width: var(--home-shell-width);
            box-sizing: border-box;
            padding: 0;
            margin-left: auto;
            margin-right: auto;
        }
        #title .product-card{
            width: 100%;
            border-radius: 28px;
            border: 1px solid rgba(95,210,255,0.34) !important;
            background:
                linear-gradient(165deg, rgba(4,17,38,0.94), rgba(7,26,58,0.90)) padding-box,
                linear-gradient(135deg, rgba(0,170,255,0.55), rgba(0,102,204,0.44), rgba(255,255,255,0.18)) border-box !important;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.10),
                0 24px 70px rgba(0,0,0,0.36) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        #title .product-card::before{
            background:
                radial-gradient(820px 360px at 15% 0%, rgba(0,170,255,0.24), transparent 58%),
                radial-gradient(700px 300px at 94% 12%, rgba(0,102,204,0.22), transparent 62%);
            opacity: 0.62;
        }
        #title .product-card::after{
            background: radial-gradient(circle at 30% 30%, rgba(95,210,255,0.16), transparent 62%);
        }
        #title .product-card:hover{
            border-color: rgba(95,210,255,0.40);
            transform: translateY(-7px);
            box-shadow:
                0 30px 90px rgba(0,0,0,0.42),
                0 0 0 1px rgba(95,210,255,0.16) inset,
                inset 0 1px 0 rgba(255,255,255,0.12),
                0 0 36px rgba(0,170,255,0.18) !important;
        }
        #title .product-title h3{
            font-family: 'Space Grotesk','Poppins','Inter',sans-serif;
            letter-spacing: -0.01em;
            color: #f3fbff !important;
            font-size: clamp(1.65rem, 2.6vw, 2.15rem) !important;
        }
        #title .product-title p{
            color: rgba(168,222,255,0.92) !important;
            font-size: 14px !important;
        }
        #title .product-icon{
            background: linear-gradient(145deg, rgba(222,242,255,0.96), rgba(157,206,240,0.90)) !important;
            border: 1px solid rgba(95,210,255,0.24);
            box-shadow: 0 12px 30px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.85);
        }
        #title .product-image{
            border-radius: 22px;
            border: 1px solid rgba(95,210,255,0.22);
            background: rgba(3,16,42,0.58);
            box-shadow: 0 24px 64px rgba(0,0,0,0.38);
        }
        #title .product-image::before{
            border-color: rgba(95,210,255,0.24);
            border-radius: 22px;
        }
        #title .product-image::after{
            border-radius: 22px;
            background:
                radial-gradient(720px 320px at 18% 0%, rgba(0,170,255,0.24), transparent 56%),
                linear-gradient(180deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0.48) 100%);
        }
        #title .product-description{
            border-radius: 22px;
            background: linear-gradient(155deg, rgba(3,16,42,0.72), rgba(7,26,58,0.56)) !important;
            border: 1px solid rgba(95,210,255,0.26) !important;
            color: rgba(230,246,255,0.90);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }
        #title .product-description-text{
            line-height: 1.8;
            color: rgba(227,244,255,0.95) !important;
            font-size: 16px !important;
        }
        #title .product-description-text::-webkit-scrollbar-thumb{
            background: linear-gradient(180deg, rgba(0,170,255,0.78), rgba(0,102,204,0.82));
        }
        #title .product-description-text::-webkit-scrollbar-track{
            background: rgba(255,255,255,0.08);
        }
        #title .social-chip{
            background: linear-gradient(145deg, rgba(0,170,255,0.24), rgba(8,31,68,0.74)) !important;
            border: 1px solid rgba(95,210,255,0.42) !important;
            color: #e1f2ff !important;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
            padding: 10px 16px !important;
        }
        #title .social-chip:hover{
            background: linear-gradient(145deg, rgba(95,210,255,0.30), rgba(8,31,68,0.82)) !important;
            border-color: rgba(95,210,255,0.62) !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0,120,255,0.34);
        }
        #title .social-chip:focus-visible{
            box-shadow: 0 0 0 3px rgba(95,210,255,0.24), 0 12px 24px rgba(0,120,255,0.22);
        }
        #title .social-chip.comment i{ color: #46c8ff; }
        #title .social-chip.share i{ color: #8edaff; }
        #title .social-chip.heart.is-liked{
            background: linear-gradient(145deg, rgba(255,91,122,0.18), rgba(8,31,68,0.60));
            border-color: rgba(255,91,122,0.35);
        }
        #title .social-chip .social-count{
            font-family: 'JetBrains Mono','Inter',monospace;
            font-size: 12px;
        }

        @media (max-width: 992px){
            #title .products-container{
                padding: 0;
                margin: 24px auto 34px;
            }
            #title .product-card{
                border-radius: 22px;
                padding: 20px;
            }
            #title .product-description,
            #title .product-image{
                border-radius: 18px;
            }
        }

	        </style>

	        <?php if (!empty($_SESSION['magx_flash'])): ?>
	            <script>
	            window.addEventListener("DOMContentLoaded", function(){
	                try { alert(<?php echo json_encode((string)$_SESSION['magx_flash']); ?>); } catch(e) {}
	            });
	            </script>
	            <?php unset($_SESSION['magx_flash']); ?>
	        <?php endif; ?>
	    </head>
    <body>
        
        <div class="loader-overlay">
          <div class="circle-loader"></div>
        </div>

        <div id="copyr" class="text-center">
            
            <p >
            <h6 style="margin-top: 10px; font-size: smaller;">MAGX Copyright © <span class="year"></span> Terms of Service | Data Privacy Policy</h6>
             </p>
        </div>
         <!-- Side Nav -->
        <div id="mySidenav" class="sidenav">
            <div class="span">
                <button type="button" id="closeBtn" class="btn-close" aria-label="Close"></button>
                
            </br>
            </br>
                <form autocomplete="off" method="POST" action="index.php">
                    <a href="#" id="adminlogin">Admin Login</a>
                    <div id="admininput">
                    </br>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['magx_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        

                        <div class="input-group mb-4">
                            <span class="input-group-text">
                                <i class="fas fa-user" id="userico" aria-hidden="true"></i>
                            </span>
                            <input class="form-control" type="textbox" placeholder="" aria-label="Username" id="admin-user" name="aduser"/>
                            <label for="admin-user" id="lbl1" class="col-form-label">Username</label>
                                    
                        </div>
                        
                        <div class="input-group mb-4">
                            <span class="input-group-text">
                                <i class="fas fa-lock" id="lockico" aria-hidden="true"></i>
                            </span>
                            <input class="form-control" type="password" placeholder="" aria-label="Password" id="admin-pass" name="adpass"/>
                            <label for="admin-pass" id="lbl2" class="col-form-label">Password</label>
                            <input type="hidden" name="businessunit" class="businessunit" value="Admin">
                                    
                        </div>
                        
                        
                        <div class="action-buttons">
                            <input type="submit" id="adlogin" name="adlogin" class="btn btn-primary" value="LOG IN">
                            <input type="submit" id="adcancel" name="adcancel" class="btn btn-secondary" value="CANCEL">
                        </div>
                    </div>
                    <a href="#" id="exit">Exit</a>
                    
                </form>
            </div>

            <div class="modal-footer">
                <div class="text-center">
                   
                        <h6 class="warning"> MAGX Copyright © <span class="year"></span></h6>
                  
                </div>
            </div>
        </div>


       
        <div class="poly-center">
            <img src="logomagx.png" alt="MAGX Logo" />
        </div>

        <nav id="mainnav" class="navbar navbar-expand-lg">
          
	                <button id="sidenav" type="button" class="sidenav-toggle" aria-label="Open side navigation" aria-expanded="false">
	                    <i class="fas fa-bars" aria-hidden="true"></i>
	                </button>

            
        </nav>
        
         <nav id="btnnav" class="navbar navbar-expand-lg fixed-top  px-3">
            <div class="container-fluid">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarButtons" aria-controls="navbarButtons" aria-expanded="false" aria-label="Toggle navigation" id="navbar-toggler">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-between" id="navbarButtons">
        
                    <div id="frow" class="d-flex">
	                        <button id="home" type="button" class="btn btn-secondary text-nowrap bt">HOME</button>
	                        <button id="about" type="button" class="btn btn-secondary text-nowrap bt">ABOUT</button>
                    </div>

             
                    <div id="srow" class="d-flex">
	                        <button id="contact" type="button" class="btn btn-secondary text-nowrap bt">PORTFOLIO</button>
	                        <button id="services" type="button" class="btn btn-secondary text-nowrap bt">SERVICES</button>
                    </div>
                </div>
            </div>
        </nav>
       



	        <div id="bodycontainer">
	            <video class="magx-bg-video magx-lock-video" autoplay muted loop playsinline webkit-playsinline preload="auto" disablepictureinpicture disableremoteplayback controlslist="nofullscreen noremoteplayback nodownload" oncontextmenu="return false;">
	                <source src="vidbg.mp4" type="video/mp4">
	            </video>

            <!-- Home -->
		            <div id="title" class="section home" >
		                <div class="welcome-header">
		                    <div class="hero-grid">
		                        <div class="hero-media" aria-label="MAGX robot video">
		                            <div class="hero-media-card">
		                                <video class="hero-robot-video smooth-loop-video magx-lock-video" autoplay muted playsinline webkit-playsinline preload="auto" disablepictureinpicture disableremoteplayback controlslist="nofullscreen noremoteplayback nodownload" oncontextmenu="return false;" data-overlap="0.8" poster="logomagx.png">
		                                    <?php
		                                    $robotVideoSrc = '';
		                                    if (file_exists(__DIR__ . '/robotwhole.MP4')) {
		                                        $robotVideoSrc = 'robotwhole.MP4';
		                                    } elseif (file_exists(__DIR__ . '/robotwhole.mp4')) {
		                                        $robotVideoSrc = 'robotwhole.mp4';
		                                    }
		                                    if ($robotVideoSrc !== ''):
		                                    ?>
		                                    <source src="<?php echo htmlspecialchars($robotVideoSrc, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
		                                    <?php endif; ?>
		                                </video>
		                                <svg class="hero-neon-frame" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
		                                    <defs>
		                                        <filter id="heroNeonBlur" x="-50%" y="-50%" width="200%" height="200%">
		                                            <feGaussianBlur stdDeviation="1.2" />
		                                        </filter>
		                                    </defs>
		                                    <!-- Use pathLength to make dash units consistent regardless of element size -->
		                                    <!-- Inset = 1.2 (half of glow stroke-width=2.4) so the stroke outer edge aligns with the card edge -->
		                                    <rect x="1.2" y="1.2" width="97.6" height="97.6" rx="10" ry="10" pathLength="1000"
		                                        fill="none" class="hero-neon-glow hero-neon-a" filter="url(#heroNeonBlur)"/>
		                                    <rect x="1.2" y="1.2" width="97.6" height="97.6" rx="10" ry="10" pathLength="1000"
		                                        fill="none" class="hero-neon-glow hero-neon-b" filter="url(#heroNeonBlur)"/>
		                                    <rect x="1.2" y="1.2" width="97.6" height="97.6" rx="10" ry="10" pathLength="1000"
		                                        fill="none" class="hero-neon-core hero-neon-a"/>
		                                    <rect x="1.2" y="1.2" width="97.6" height="97.6" rx="10" ry="10" pathLength="1000"
		                                        fill="none" class="hero-neon-core hero-neon-b"/>
		                                </svg>
		                            </div>
		                        </div>
		                        <div class="hero-copy">
		                            <div class="hero-copy-card">
		                                <h2>WELCOME TO</h2>
		                                <hr>
		                                <h1 class="hero-title" data-text="MAGX CODE-POWERED SOLUTIONS">MAGX CODE-POWERED SOLUTIONS</h1>
		                                <hr>
		                                <p class="hero-kicker">EST 2026</p>
		                                <p class="hero-tagline">Magx build secure web systems and workflow automations that replace manual work with fast, trackable processes.</p>
		                                <div class="hero-cta">
		                                    <button id="heroConsult" type="button" class="btn btn-primary">Free Consultation</button>
		                                    <button id="heroPortfolio" type="button" class="btn btn-outline-light">View Portfolio</button>
		                                    <button id="heroExplore" type="button" class="btn btn-outline-light">Explore Services</button>
		                                </div>
		                            </div>
		                        </div>
			                    </div>
			                    
			                </div>
			                <?php renderHomeScrollIndicator(); ?>
		                <div id="homePostsSentinel" aria-hidden="true"></div>
		                <?php
		                $posts = loadHomePostsForDisplay();
		                $firstPost = null;
	                if (!empty($posts)) {
	                    $firstPost = array_shift($posts);
	                }
	                ?>
	                <div class="products-container first-post-wrapper is-hidden">
	                    <?php
	                    if ($firstPost) {
	                        outputHomePostCard($firstPost, 1);
	                    } else {
	                        echo '<div class="product-card"><div class="product-description"><p>No content available at this time.</p></div></div>';
		                    }
		                    ?>
		                </div>
	                <div id="homePosts" class="products-container is-hidden">
	                    <?php
	                    $postIndex = 2;
	                    foreach ($posts as $post) {
	                        outputHomePostCard($post, $postIndex);
	                        $postIndex++;
	                    }
	                    ?>
	                </div>
            </div>

            <!-- About -->
	        <div id="aboutcon" class="section dark" style="min-height: 100%; display: none;">
                <div class="about-card">
                    <h2>
                        <i class="fas fa-building about-icon"></i>
                        What is MAGX?
                    </h2>
                    <p>
                        MAGX Solutions is a technology initiative that develops digital tools and systems to help people and organizations navigate the ever-changing digital world. We focus on creating user-friendly solutions that simplify tasks, improve efficiency, and make technology easier to use. By combining creativity, technology, and problem-solving, MAGX Solutions turns ideas into tools that support learning, growth, and digital adaptation.
                    </p>
                </div>

                <div class="about-card">
                    <h2>
                        <i class="fas fa-eye about-icon"></i>
                        VISION
                    </h2>
                    <p>
                        MAGX vision is to make adapting to the digital world easier and more accessible for everyone. We strive to create user-friendly solutions that help people explore technology confidently, enhance their workflows, and embrace new digital opportunities. MAGX Solutions is a place where ideas become tools that make technology approachable and helpful.
                    </p>
                </div>

                <div class="about-card">
                    <h2>
                        <i class="fas fa-bullseye about-icon"></i>
                        MISSION
                    </h2>
                    <p>
                        MAGX mission is to design and develop digital systems and applications that are user-friendly, effective, and meaningful. We focus on addressing real challenges and helping people adjust to technological changes in their daily lives. Through MAGX Solutions, we are committed to continuous learning, experimenting, and delivering solutions that empower users to make the most of the digital world.
                    </p>
                </div>
            </div>

            <!-- Services -->
            <div id="servicescon" class="section dark" style="display: none;">
                <div class="services-panel">
                    <section class="services-hero-card">
                        <p class="eyebrow">What we build</p>
                        <h2>SYSTEMS THAT WORK FOR YOUR TEAM</h2>
                        <p>MAGX Solution’s service stack turns manual workflows, spreadsheets, and paperwork into automated, data-rich applications. We design secure web portals, dashboard-driven operations, integrations, and database-backed tools that keep every stakeholder aligned.</p>
                        <div class="services-hero-actions">
                            <button type="button" class="service-cta primary">Book a discovery call</button>
                            <button type="button" class="service-cta ghost">Share your process challenge</button>
                        </div>
                    </section>
                    <div class="services-grid">
                        <?php
                            $services = loadServicesForDisplay();
                            if (count($services) === 0) {
                                echo '<article class="services-card empty-card">';
                                echo '<p class="empty-title"><i class="fas fa-layer-group"></i> Services coming soon</p>';
                                echo '<p class="empty-copy">Add service entries from the admin panel to introduce offerings to visitors.</p>';
                                echo '</article>';
                            } else {
                                foreach ($services as $service) {
                                    $icon = !empty($service['icon_class']) ? $service['icon_class'] : 'fas fa-layer-group';
                                    $title = htmlspecialchars($service['title']);
                                    $description = nl2br(htmlspecialchars($service['description']));
                                    echo '<article class="services-card">';
                                    echo '<span class="services-card-icon"><i class="' . htmlspecialchars($icon) . '"></i></span>';
                                    echo '<div class="services-card-body">';
                                    echo '<h3>' . $title . '</h3>';
                                    echo '<p>' . $description . '</p>';
                                    echo '</div>';
                                    echo '</article>';
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>


	            <!-- Portfolio / Contact -->
	            <div id="contactcontainer" class="section home" style="min-height: 100%; display: none;">
	                <div class="folio-shell">
	                    <section class="px-block px-hero px-reveal reveal">
	                        <div>
	                            <span class="px-kicker">Portfolio</span>
	                            <h2 class="px-hero-title">Guillermo, Mark Angelo H.<span class="px-hero-role">Web Developer</span></h2>
	                            <p class="px-hero-sub">I build secure and responsive web systems that replace manual workflows with structured digital processes. My focus is practical business automation using PHP, MySQL, and modern UI implementation.</p>
	                            <div class="px-hero-stats">
	                                <div class="px-stat"><strong>2021-2025</strong><span>BSIT Degree</span></div>
	                                <div class="px-stat"><strong>2</strong><span>Work Experiences</span></div>
	                                <div class="px-stat"><strong>2</strong><span>Certifications</span></div>
	                            </div>
	                            <div class="px-cta-row">
	                                <a class="px-btn px-btn-primary" href="#px-featured-projects"><i class="fas fa-layer-group" aria-hidden="true"></i>View Featured Work</a>
	                                <a class="px-btn px-btn-light" href="mailto:magxsolutios2026@gmail.com"><i class="fas fa-envelope" aria-hidden="true"></i>Start a Project</a>
	                            </div>
	                        </div>
	                        <div class="px-hero-glass">
	                            <div class="px-hero-media">
	                                <!-- Replace with project image -->
	                                <img class="px-hero-photo" src="pic2x2.jpg" alt="Portrait of Guillermo, Mark Angelo H." onerror="this.style.display='none';">
	                               
	                            </div>
	                        </div>
	                    </section>

	                    <section id="px-credentials" class="px-block px-credentials px-reveal reveal" aria-labelledby="px-credentials-title">
	                        <header class="px-block-head px-cred-head">
	                            <span class="px-kicker">Credentials</span>
	                           
	                        </header>
	                        <div class="px-cred-grid" role="list" aria-label="Professional credentials categories">
	                            <article class="px-cred-card" role="listitem" tabindex="0" aria-labelledby="px-cred-education-title">
	                                <div class="px-cred-top">
	                                    <span class="px-cred-icon" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
	                                    <div class="px-cred-title">
	                                        <h4 id="px-cred-education-title">Education</h4>
	                                        <p>Academic Foundation</p>
	                                    </div>
	                                    <span class="px-cred-badge">2021-2025</span>
	                                </div>
	                                <ul class="px-cred-list">
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>Saint Ferdinand College</h5>
	                                            <time datetime="2021">2021-2025</time>
	                                        </div>
	                                        <p>Bachelor of Science in Information Technology.</p>
	                                    </li>
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>Isabela National Highschool</h5>
	                                            <time datetime="2019">2019-2021</time>
	                                        </div>
	                                        <p>ABM, Senior High.</p>
	                                    </li>
	                                </ul>
	                                <div class="px-cred-footer">
	                                    <span class="px-cred-label">Academic Background</span>
	                                    <strong class="px-cred-flag">BSIT Graduate</strong>
	                                </div>
	                            </article>
	                            <article class="px-cred-card" role="listitem" tabindex="0" aria-labelledby="px-cred-work-title">
	                                <div class="px-cred-top">
	                                    <span class="px-cred-icon" aria-hidden="true"><i class="fas fa-briefcase"></i></span>
	                                    <div class="px-cred-title">
	                                        <h4 id="px-cred-work-title">Work Experience</h4>
	                                        <p>Practical Delivery</p>
	                                    </div>
	                                    <span class="px-cred-badge">2 Roles</span>
	                                </div>
	                                <ul class="px-cred-list">
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>Graphic Designer, Firstgate</h5>
	                                            <time datetime="2025">2025</time>
	                                        </div>
	                                        <p>Created campaign-ready visual assets for digital and print materials.</p>
	                                    </li>
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>Technical Support &amp; Office Staff, City Hall of City of Ilagan (GIP)</h5>
	                                            <time datetime="2023">2023</time>
	                                        </div>
	                                        <p>Supported technical operations and frontline administrative workflows.</p>
	                                    </li>
	                                </ul>
	                                <div class="px-cred-footer">
	                                    <span class="px-cred-label">Professional Experience</span>
	                                    <strong class="px-cred-flag">Design + Technical Support</strong>
	                                </div>
	                            </article>
	                            <article class="px-cred-card" role="listitem" tabindex="0" aria-labelledby="px-cred-cert-title">
	                                <div class="px-cred-top">
	                                    <span class="px-cred-icon" aria-hidden="true"><i class="fas fa-certificate"></i></span>
	                                    <div class="px-cred-title">
	                                        <h4 id="px-cred-cert-title">Certifications</h4>
	                                        <p>Trust &amp; Verification</p>
	                                    </div>
	                                    <span class="px-cred-badge">Verified</span>
	                                </div>
	                                <ul class="px-cred-list">
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>Certificate of Implementation - TMC Multi-Industry</h5>
	                                            <time datetime="2025">2025</time>
	                                        </div>
	                                        <p>Awarded for practical implementation and deployment impact.</p>
	                                    </li>
	                                    <li class="px-cred-item">
	                                        <div class="px-cred-item-head">
	                                            <h5>National Certificate II - Computer Systems Servicing</h5>
	                                            <time datetime="2025">2025</time>
	                                        </div>
	                                        <p>Validated competencies in computer systems servicing and support.</p>
	                                    </li>
	                                </ul>
	                                <p class="px-cred-ref"><strong>Professional Reference:</strong> Ramon Z. Marayag, LPT, MBA, MIT (Dean, BSIT).</p>
	                                <div class="px-cred-footer">
	                                    <span class="px-cred-label">Reference Status</span>
	                                    <strong class="px-cred-flag">Reference Available</strong>
	                                </div>
	                            </article>
	                        </div>
	                    </section>

	                    <section class="px-block px-reveal reveal">
	                        <header class="px-block-head">
	                            <span class="px-kicker">Skills &amp; Capabilities</span>
	                            <h3>Cross-functional delivery across product, engineering, and systems</h3>
	                            <p style="text-align:center;margin-left:auto;margin-right:auto;">A modern capability map grouped by discipline to show both depth and practical shipping power.</p>
	                        </header>
	                        <div class="px-skills">
	                            <article class="px-skill-card">
	                                <h4>Frontend Engineering</h4>
	                                <div class="px-skill"><div class="px-skill-top"><span>HTML/CSS Systems</span><span>95%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:95%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>JavaScript UX Interactions</span><span>90%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:90%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>Responsive Interface Architecture</span><span>94%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:94%"></span></div></div>
	                            </article>
	                            <article class="px-skill-card">
	                                <h4>Backend &amp; Data Systems</h4>
	                                <div class="px-skill"><div class="px-skill-top"><span>PHP Application Logic</span><span>92%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:92%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>MySQL Data Modeling</span><span>90%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:90%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>Security &amp; Validation Patterns</span><span>88%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:88%"></span></div></div>
	                            </article>
	                            <article class="px-skill-card">
	                                <h4>Design &amp; Product Strategy</h4>
	                                <div class="px-skill"><div class="px-skill-top"><span>UI/UX Hierarchy and Layout</span><span>89%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:89%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>Design System Thinking</span><span>86%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:86%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>Visual Branding Execution</span><span>91%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:91%"></span></div></div>
	                            </article>
	                            <article class="px-skill-card">
	                                <h4>Systems &amp; Delivery</h4>
	                                <div class="px-skill"><div class="px-skill-top"><span>Workflow Automation</span><span>90%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:90%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>QA and Release Reliability</span><span>87%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:87%"></span></div></div>
	                                <div class="px-skill"><div class="px-skill-top"><span>Stakeholder Communication</span><span>93%</span></div><div class="px-skill-track"><span class="px-skill-fill" style="--skill-level:93%"></span></div></div>
	                            </article>
	                        </div>
	                    </section>

	                    <section class="px-block px-reveal reveal">
	                        <header class="px-block-head">
	                            <span class="px-kicker">Process</span>
	                            <h3>How I work from first discovery to launch stability</h3>
	                        </header>
	                        <div class="px-process">
	                            <article class="px-step"><strong>Step 01</strong><h4>Discovery</h4><p>Clarify business model, user flow bottlenecks, and success metrics before writing implementation plans.</p></article>
	                            <article class="px-step"><strong>Step 02</strong><h4>Planning</h4><p>Build scope architecture, define technical strategy, and align milestones for predictable delivery.</p></article>
	                            <article class="px-step"><strong>Step 03</strong><h4>Design</h4><p>Create premium hierarchy, interaction states, and responsive behavior with product-level consistency.</p></article>
	                            <article class="px-step"><strong>Step 04</strong><h4>Development</h4><p>Implement scalable frontend/backend components with clean structure and maintainable standards.</p></article>
	                            <article class="px-step"><strong>Step 05</strong><h4>Testing</h4><p>Run functional, usability, and performance validation to reduce regressions and protect trust.</p></article>
	                            <article class="px-step"><strong>Step 06</strong><h4>Launch</h4><p>Deploy confidently, monitor outcomes, and iterate based on user behavior and business KPIs.</p></article>
	                        </div>
	                    </section>

	                    <section id="px-featured-projects" class="px-block px-reveal reveal">
	                        <div class="px-featured-stack">
	                            <section class="px-carousel" id="pxFeaturedCarousel" aria-label="Projects Carousel">
	                                <button type="button" class="px-carousel-arrow prev" id="pxFeaturedPrev" aria-label="Previous project">
	                                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
	                                </button>
	                                <div class="px-carousel-viewport">
	                                        <span class="px-kicker">FEATURED PROJECTS</span>
	                                        <br>
	                                        <br>
	                                    <div class="px-carousel-track">
	                                        <article class="px-project px-carousel-slide">
	                                            <div class="px-project-top">
	                                                <div class="px-project-meta"><span class="px-project-year">Flagship Build • 2025</span></div>
	                                                <h4>Integrated Record Management System (TMC)</h4>
	                                                <p>A multi-industry platform that centralized operations across gasoline stations, leasing, and food services into one secure and searchable system.</p>
	                                            </div>
	                                            <div class="px-project-media px-project-photo">
	                                                <img src="tmcgrid.png" alt="Integrated Record Management System preview image" onerror="this.style.display='none';">
	                                            </div>
	                                            <div class="px-project-bottom">
	                                                <div class="px-project-stats">Key highlight: Unified operations across multiple branches with role-based control.</div>
	                                                <div class="px-cta-row">
	                                                    <button type="button" class="px-btn px-btn-primary px-open-case" data-case-target="#px-case-tmc"><i class="fas fa-book-open" aria-hidden="true"></i>View Case Study</button>
	                                                </div>
	                                            </div>
	                                        </article>

	                                        <article class="px-project px-carousel-slide">
	                                            <div class="px-project-top">
	                                                <div class="px-project-meta"><span class="px-project-year">Growth Build • 2026</span></div>
	                                                <h4>MAGX High-Converting Landing Experience</h4>
	                                                <p>A conversion-oriented website rebuild designed to improve trust, content clarity, and lead action through stronger visual hierarchy and interaction design.</p>
	                                            </div>
	                                            <div class="px-project-media px-project-photo">
	                                                <img src="magxss.png" alt="MAGX high-converting landing experience preview image" onerror="this.style.display='none';">
	                                            </div>
	                                            <div class="px-project-bottom">
	                                                <div class="px-project-stats">Key highlight: Stronger conversion flow with clearer hierarchy and engagement modules.</div>
	                                                <div class="px-cta-row">
	                                                    <button type="button" class="px-btn px-btn-primary px-open-case" data-case-target="#px-case-magx"><i class="fas fa-book-open" aria-hidden="true"></i>View Case Study</button>
	                                                </div>
	                                            </div>
	                                        </article>

	                                        <article class="px-project px-carousel-slide">
	                                            <div class="px-project-top">
	                                                <div class="px-project-meta"><span class="px-project-year">Design Build • 2025</span></div>
	                                                <h4>Firstgate Campaign Asset System</h4>
	                                                <p>A brand asset production system that standardized campaign visuals across social and print channels for faster and more consistent rollout.</p>
	                                            </div>
	                                            <div class="px-project-media px-project-photo">
	                                                <img src="firstgate.png" alt="Firstgate campaign asset system preview image" onerror="this.style.display='none';">
	                                            </div>
	                                            <div class="px-project-bottom">
	                                                <div class="px-project-stats">Key highlight: Reusable templates improved consistency and output speed.</div>
	                                                <div class="px-cta-row">
	                                                    <button type="button" class="px-btn px-btn-primary px-open-case" data-case-target="#px-case-firstgate"><i class="fas fa-book-open" aria-hidden="true"></i>View Case Study</button>
	                                                </div>
	                                            </div>
	                                        </article>
	                                    </div>
	                                </div>
	                                <button type="button" class="px-carousel-arrow next" id="pxFeaturedNext" aria-label="Next project">
	                                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
	                                </button>
	                            </section>
	                        </div>
	                    </section>

	                    <section id="px-case-studies" class="px-block px-reveal reveal">
	                        <header class="px-block-head">
	                            <span class="px-kicker">Case Studies</span>
	                            <h3>Problem to impact breakdowns with professional delivery logic</h3>
	                            <p>Each project includes challenge framing, strategic decisions, process, tools, and measurable outcomes. Expand each case to review details.</p>
	                        </header>
	                        <div class="px-case-grid accordion" id="pxCaseAccordion">
	                            <article class="px-case accordion-item">
	                                <h4 class="accordion-header">
	                                    <button id="px-case-tmc" class="px-case-toggle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pxCollapseTmc" aria-expanded="false" aria-controls="pxCollapseTmc">
	                                        <span>
	                                            <strong>TMC Integrated Record Management System</strong>
	                                            <span>TMC Multi-Industry • 2025</span>
	                                        </span>
	                                        <i class="fas fa-plus" aria-hidden="true"></i>
	                                    </button>
	                                </h4>
	                                <div id="pxCollapseTmc" class="accordion-collapse collapse" data-bs-parent="#pxCaseAccordion">
	                                    <div class="px-case-content accordion-body">
	                                        <div class="px-case-box"><h5>Problem</h5><p>Operations depended on fragmented manual records, causing reporting delays, low visibility, and inconsistent data quality.</p></div>
	                                        <div class="px-case-box"><h5>Solution</h5><p>Delivered a centralized role-based platform with structured forms, validation layers, and searchable monitoring dashboards.</p></div>
	                                        <div class="px-case-box"><h5>Process</h5><p>Discovery workshops, domain mapping, modular database modeling, pilot rollout, QA hardening, and phased deployment.</p></div>
	                                        <div class="px-case-box"><h5>Tools Used</h5><ul><li>PHP, MySQL, Bootstrap, JavaScript</li><li>Role-based authentication design</li><li>Operational reporting architecture</li></ul></div>
	                                        <div class="px-case-box"><h5>Key Results</h5><ul><li>Successfully deployed across three gasoline station branches</li><li>Supported commercial leasing and fast-food chain operations</li><li>Automated manual and semi-manual processes from capstone recommendations</li></ul></div>
	                                        <div class="px-case-box"><h5>Impact Highlight</h5><p>Received Certificate of Implementation in 2025 for delivering a practical and scalable business system.</p></div>
	                                        <div class="px-case-box"><h5>Screenshots</h5><div class="px-case-media"><!-- Replace with project image -->Project Screenshots Placeholder</div></div>
	                                        <div class="px-case-box"><h5>Before / After</h5><div class="px-case-media"><!-- Replace with project image -->Before / After Comparison Placeholder</div></div>
	                                    </div>
	                                </div>
	                            </article>
	                            <article class="px-case accordion-item">
	                                <h4 class="accordion-header">
	                                    <button id="px-case-magx" class="px-case-toggle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pxCollapseMagx" aria-expanded="false" aria-controls="pxCollapseMagx">
	                                        <span>
	                                            <strong>MAGX Conversion-Focused Website Experience</strong>
	                                            <span>Web Portfolio Platform • 2026</span>
	                                        </span>
	                                        <i class="fas fa-plus" aria-hidden="true"></i>
	                                    </button>
	                                </h4>
	                                <div id="pxCollapseMagx" class="accordion-collapse collapse" data-bs-parent="#pxCaseAccordion">
	                                    <div class="px-case-content accordion-body">
	                                        <div class="px-case-box"><h5>Problem</h5><p>The earlier site lacked strong hierarchy and persuasive structure, limiting conversion confidence from new prospects.</p></div>
	                                        <div class="px-case-box"><h5>Solution</h5><p>Introduced premium visual language, strategic section sequencing, stronger copy hierarchy, and micro-interactions.</p></div>
	                                        <div class="px-case-box"><h5>Process</h5><p>Analytics baseline, UX mapping, interaction prototyping, UI system build, performance tuning, and post-launch iteration.</p></div>
	                                        <div class="px-case-box"><h5>Tools Used</h5><ul><li>HTML, CSS, JavaScript, Bootstrap</li><li>Component-based section architecture</li><li>Motion and interaction choreography</li></ul></div>
	                                        <div class="px-case-box"><h5>Key Results</h5><ul><li>Established clear portfolio hierarchy and project storytelling</li><li>Added interaction modules for comments, sharing, and engagement</li><li>Improved presentation quality for client-facing credibility</li></ul></div>
	                                        <div class="px-case-box"><h5>Impact Highlight</h5><p>Strengthened digital presence with a polished and professional product-style presentation.</p></div>
	                                        <div class="px-case-box"><h5>Screenshots</h5><div class="px-case-media"><!-- Replace with project image -->Project Screenshots Placeholder</div></div>
	                                    </div>
	                                </div>
	                            </article>
	                            <article class="px-case accordion-item">
	                                <h4 class="accordion-header">
	                                    <button id="px-case-firstgate" class="px-case-toggle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pxCollapseFirstgate" aria-expanded="false" aria-controls="pxCollapseFirstgate">
	                                        <span>
	                                            <strong>Firstgate Brand Asset and Campaign System</strong>
	                                            <span>Design Operations • 2025</span>
	                                        </span>
	                                        <i class="fas fa-plus" aria-hidden="true"></i>
	                                    </button>
	                                </h4>
	                                <div id="pxCollapseFirstgate" class="accordion-collapse collapse" data-bs-parent="#pxCaseAccordion">
	                                    <div class="px-case-content accordion-body">
	                                        <div class="px-case-box"><h5>Problem</h5><p>Marketing assets were inconsistent across channels, reducing campaign quality and slowing production turnaround.</p></div>
	                                        <div class="px-case-box"><h5>Solution</h5><p>Built a modular visual system with reusable social/print templates and brand-safe composition rules.</p></div>
	                                        <div class="px-case-box"><h5>Process</h5><p>Brand audit, style system definition, production templates, review loop optimization, and rollout playbook.</p></div>
	                                        <div class="px-case-box"><h5>Tools Used</h5><ul><li>Graphic Design Workflows</li><li>Template System Thinking</li><li>Content-Ready Layout Structures</li></ul></div>
	                                        <div class="px-case-box"><h5>Key Results</h5><ul><li>Delivered multi-format marketing materials for active campaigns</li><li>Maintained consistent branding across social and print collateral</li><li>Supported stronger visual communication for promotions</li></ul></div>
	                                        <div class="px-case-box"><h5>Screenshots</h5><div class="px-case-media"><!-- Replace with project image -->Campaign Asset Placeholder</div></div>
	                                    </div>
	                                </div>
	                            </article>
	                        </div>
	                    </section>

	                    <section class="px-final-cta px-reveal reveal">
	                        <div class="px-final-copy">
	                            <h3><span class="px-final-line">Let’s build a better website</span><span class="px-final-line">for your business.</span></h3>
	                            <p>I can help you create a clean, reliable website that is easy to use and helps your business grow.</p>
	                            <div class="px-final-actions">
	                                <a class="px-btn px-btn-primary" href="mailto:magxsolutios2026@gmail.com"><i class="fas fa-paper-plane" aria-hidden="true"></i>Let's Work Together</a>
	                            </div>
	                        </div>
	                        <div class="px-final-visual">
	                            <span class="px-final-halo" aria-hidden="true"></span>
	                            <span class="px-final-glass" aria-hidden="true"></span>
	                            <img class="px-final-portrait" src="picwithipad.png" alt="Guillermo holding an iPad" onerror="this.style.display='none';">
	                        </div>
	                    </section>
	                </div>
	            </div>



        <!-- MAGX Solutions AI Chat Widget -->
        <div id="magx-chat-root" aria-live="polite">
	            <button class="magx-chat-button" id="magxChatToggle" aria-label="Open chat">
	                <span class="magx-chat-toggle-avatar" aria-hidden="true">
	                    <img src="bot2x2.png" alt="" onerror="try{this.closest('button').classList.add('magx-chat-fallback'); this.style.display='none';}catch(e){}">
	                </span>
	                <i class="fas fa-comments" aria-hidden="true"></i>
	            </button>
            <div class="magx-chat-window" id="magxChatWindow" role="dialog" aria-label="MAGX Solutions AI chat">
                <div class="magx-chat-header">
                    <div class="magx-chat-titleblock">
                        <div class="magx-chat-titlemain">
	                            <span class="magx-avatar magx-avatar-bot" aria-hidden="true">
	                                <img src="bot2x2.png" alt="" onerror="this.style.display='none';">
	                            </span>
                            <span class="magx-chat-title">MAGX Solutions</span>
                            <span class="magx-chat-presence"><span class="magx-status-dot"></span>Online</span>
                        </div>
                       
                    </div>
                    <div class="magx-chat-actions">
                        <button class="magx-clear" id="magxChatClear" aria-label="Clear chat">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="magx-close" id="magxChatClose" aria-label="Close chat">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="magx-chat-body">
                    <div class="magx-messages" id="magxMessages"></div>
                </div>
                <div class="magx-chat-footer">
                    <div class="magx-quick" id="magxQuick" aria-label="Quick actions"></div>
                    <div class="magx-input-bar">
                        <input type="text" id="magxUserInput" class="magx-input" placeholder="Ask about services, automation, or consultations..." autocomplete="off">
                        <button class="magx-send" id="magxSendBtn" aria-label="Send message">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>



        


        
        



        






        <!-- Google Map Modal -->
        <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                        <h1 class="modal-title fs-5" id="mapModalLabel" style="color:white;">
                            <i class="fas fa-map-marker-alt me-2"></i>Our Location
                        </h1>
                        <button type="button" class="btn-close" style="background-color:white;" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 0;">
                        <iframe 
                            src="https://www.google.com/maps?q=Ilagan+City,+Isabela,+Philippines&output=embed" 
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            id="googleMapIframe">
                        </iframe>
                    </div>
                    <div class="modal-footer" style="background-color: #f8f9fa;">
                        <p class="mb-0 text-muted" style="font-size: 14px;">
                            <i class="fas fa-map-marker-alt me-2" style="color: #672222;"></i>
                            Ilagan City, Isabela
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home Post Comment Modal -->
        <div class="modal fade modern-sheet" id="postCommentModal" tabindex="-1" aria-labelledby="postCommentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title fs-5" id="postCommentModalLabel">
                            <div class="title-main">
                                <i class="fas fa-comment"></i>
                                <span>Comments</span>
                            </div>
                            <div class="title-sub" id="commentPostTitle">Post</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="commentPostId" value="">
                        <div class="comments-wrap">
                            <div class="comments-list" id="commentsList" aria-label="Comments list">
                                <div class="text-muted" style="font-size: 13px;">Loading...</div>
                            </div>
                            <div class="comment-compose">
                                <label for="commentText" class="form-label" style="font-weight:700;">Add a comment</label>
                                <textarea id="commentText" class="form-control" rows="3" maxlength="1000" placeholder="Write something helpful..."></textarea>
                                <div class="compose-meta">
                                    <span id="commentCharCount">0/1000</span>
                                    <button type="button" class="btn btn-primary" id="submitCommentBtn">
                                        Post Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home Post Share Modal -->
        <div class="modal fade modern-sheet" id="postShareModal" tabindex="-1" aria-labelledby="postShareModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title fs-5" id="postShareModalLabel">
                            <div class="title-main">
                                <i class="fas fa-share"></i>
                                <span>Share</span>
                            </div>
                            <div class="title-sub">Choose a platform or copy the link</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="sharePostId" value="">
                        <div class="share-url">
                            <i class="fas fa-link" style="color: rgba(255,255,255,0.75);"></i>
                            <input type="text" id="shareUrlInput" value="" readonly>
                            <button type="button" class="btn btn-sm btn-primary" id="shareCopyBtn" style="border-radius:999px; background-color:#0066cc; border:none; font-weight:700;">
                                Copy
                            </button>
                        </div>
                        <div id="shareCopyStatus" class="share-meta" style="display:none;">Copied to clipboard.</div>

                        <div class="share-grid" aria-label="Share options">
                            <a class="share-tile is-facebook share-action" id="shareFacebook" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fab fa-facebook-f"></i></span>
                                <span class="share-label">Facebook</span>
                                <span class="share-meta">Post to feed</span>
                            </a>
                            <a class="share-tile is-whatsapp share-action" id="shareWhatsapp" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fab fa-whatsapp"></i></span>
                                <span class="share-label">WhatsApp</span>
                                <span class="share-meta">Send a message</span>
                            </a>
                            <a class="share-tile is-x share-action" id="shareX" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fab fa-x-twitter"></i></span>
                                <span class="share-label">X</span>
                                <span class="share-meta">Tweet link</span>
                            </a>
                            <a class="share-tile is-linkedin share-action" id="shareLinkedIn" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fab fa-linkedin-in"></i></span>
                                <span class="share-label">LinkedIn</span>
                                <span class="share-meta">Share professionally</span>
                            </a>
                            <a class="share-tile is-telegram share-action" id="shareTelegram" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fab fa-telegram"></i></span>
                                <span class="share-label">Telegram</span>
                                <span class="share-meta">Send to chat</span>
                            </a>
                            <a class="share-tile is-email share-action" id="shareEmail" target="_blank" rel="noopener">
                                <span class="share-ico"><i class="fas fa-envelope"></i></span>
                                <span class="share-label">Email</span>
                                <span class="share-meta">Compose email</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Project Modal -->
        <div class="modal fade folio-modal" id="portfolioProjectModal" tabindex="-1" aria-labelledby="portfolioProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title fs-5" id="portfolioProjectModalLabel">
                            <span class="logo" aria-hidden="true"><img src="logomagx.png" alt="" onerror="this.style.display='none';"></span>
                            <span id="ppTitle">Project</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="pgrid">
                            <div class="pcover" aria-hidden="true">
                                <div id="ppCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
                                    <div class="carousel-inner" id="ppCarouselInner"></div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#ppCarousel" data-bs-slide="prev" id="ppPrevBtn" aria-label="Previous">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#ppCarousel" data-bs-slide="next" id="ppNextBtn" aria-label="Next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div class="overlay">
                                    <div class="k" id="ppKicker">Overview</div>
                                    <div class="s" id="ppSubtitle">Details</div>
                                </div>
                            </div>
                            <div class="pbox">
                                <h5>Impact</h5>
                                <ul class="plist" id="ppImpact"></ul>
                            </div>
                            <div class="pbox">
                                <h5>Key Features</h5>
                                <ul class="plist" id="ppFeatures"></ul>
                            </div>
                            <div class="pbox">
                                <h5>Tech Stack</h5>
                                <div id="ppStack" class="work-tags"></div>
                                <div class="work-actions" style="margin-top:12px;">
                                    <a class="btn btn-primary btn-sm" id="ppCTA" href="mailto:magxsolutios2026@gmail.com">Ask about this</a>
                                    <button type="button" class="btn btn-light btn-sm js-go-home">Back to Home</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>if (typeof window.jQuery === 'undefined') document.write('<script src=\"assets/js/jquery-3.7.1.min.js\"><\\/script>');</script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script>if (typeof window.bootstrap === 'undefined') document.write('<script src=\"assets/js/bootstrap.bundle.min.js\"><\\/script>');</script>
        <script>
        // Bootstrap Modal hotfix: ensure a focus trap object always exists.
        // Fixes: "Cannot read properties of undefined (reading 'activate')" from modal.js.
        (function(){
            if(!window.bootstrap || !bootstrap.Modal){ return; }

            const Modal = bootstrap.Modal;
            const noopTrap = { activate: function(){}, deactivate: function(){} };

            const origInitFocusTrap = Modal.prototype._initializeFocusTrap;
            Modal.prototype._initializeFocusTrap = function(){
                try {
                    if (origInitFocusTrap) {
                        origInitFocusTrap.call(this);
                    }
                } catch (e) {
                    // fall through to noop trap
                }

                if (!this._focustrap) {
                    this._focustrap = noopTrap;
                }
            };

            const origShow = Modal.prototype.show;
            Modal.prototype.show = function(){
                if (!this._focustrap) {
                    this._focustrap = noopTrap;
                }
                return origShow.apply(this, arguments);
            };
        })();
        </script>

		        <script>
			        $(document).ready(function(){
			            $('.year').text(new Date().getFullYear());

			            // Ensure the neon title overlay always matches the visible title text.
				            $(".hero-title").each(function(){
				                const t = String($(this).text() || "").trim();
				                if(t){ this.setAttribute("data-text", t); }
				            });

				            function enforceLockedAutoplay(videoEl){
				                if (!videoEl || videoEl.dataset.lockAutoplayInit === "1") { return; }
				                videoEl.dataset.lockAutoplayInit = "1";
				                videoEl.muted = true;
				                videoEl.defaultMuted = true;
				                videoEl.playsInline = true;
				                videoEl.setAttribute("muted", "");
				                videoEl.setAttribute("playsinline", "");
				                videoEl.setAttribute("webkit-playsinline", "");
				                videoEl.setAttribute("disablePictureInPicture", "");
				                videoEl.setAttribute("disableRemotePlayback", "");
				                videoEl.controls = false;

				                function replay(){
				                    try {
				                        const p = videoEl.play();
				                        if (p && typeof p.catch === "function") { p.catch(function(){}); }
				                    } catch (e) {}
				                }

				                videoEl.addEventListener("pause", function(e){
				                    if (e && e.isTrusted) {
				                        e.preventDefault();
				                    }
				                    replay();
				                });
				                videoEl.addEventListener("ended", function(e){
				                    if (e && e.isTrusted) {
				                        e.preventDefault();
				                    }
				                    try { videoEl.currentTime = 0; } catch (err) {}
				                    replay();
				                });
				                videoEl.addEventListener("loadedmetadata", replay);
				                videoEl.addEventListener("canplay", replay);
				                videoEl.addEventListener("suspend", replay);
				                videoEl.addEventListener("stalled", replay);
				                videoEl.addEventListener("waiting", replay);

				                ["touchstart", "pointerdown", "click"].forEach(function(evt){
				                    videoEl.addEventListener(evt, function(e){
				                        if (e && typeof e.preventDefault === "function") {
				                            e.preventDefault();
				                        }
				                        replay();
				                    }, { passive: false });
				                });

				                replay();
				            }

				            function initSmoothLoopVideo(videoEl){
				                if(!videoEl || videoEl.dataset.smoothLoopInit === "1"){ return; }
				                videoEl.dataset.smoothLoopInit = "1";
				                videoEl.removeAttribute("loop");

			                const overlapRaw = parseFloat(videoEl.dataset.overlap || "0.8");
			                const overlap = Math.min(1.5, Math.max(0.4, isFinite(overlapRaw) ? overlapRaw : 0.8));
			                const enableAudioCrossfade = String(videoEl.dataset.audioCrossfade || "").toLowerCase() === "true";
			                const sourceVolume = Number.isFinite(videoEl.volume) ? videoEl.volume : 1;

				                enforceLockedAutoplay(videoEl);
				                const parent = videoEl.parentElement;
				                const blendEl = videoEl.cloneNode(true);
				                enforceLockedAutoplay(blendEl);
				                blendEl.removeAttribute("loop");
				                blendEl.removeAttribute("controls");
			                blendEl.setAttribute("aria-hidden", "true");
			                blendEl.style.opacity = "0";
			                blendEl.style.pointerEvents = "none";
			                blendEl.style.zIndex = (videoEl.classList.contains("hero-robot-video")) ? "1" : "-1";
			                blendEl.dataset.smoothLoopClone = "1";

			                if (videoEl.classList.contains("hero-robot-video") && parent) {
			                    if (getComputedStyle(parent).position === "static") {
			                        parent.style.position = "relative";
			                    }
			                    videoEl.style.position = "absolute";
			                    videoEl.style.inset = "0";
			                    videoEl.style.zIndex = "1";
			                    blendEl.style.position = "absolute";
			                    blendEl.style.inset = "0";
			                }

			                videoEl.insertAdjacentElement("afterend", blendEl);

			                let active = videoEl;
			                let inactive = blendEl;
			                let isBlending = false;
			                let blendStartTs = 0;
			                let rafId = 0;
			                let started = false;

			                function safePlay(el){
			                    try {
			                        const p = el.play();
			                        if (p && typeof p.catch === "function") { p.catch(function(){}); }
			                    } catch (e) {}
			                }

			                function startBlend(nowTs){
			                    if (isBlending) { return; }
			                    isBlending = true;
			                    blendStartTs = nowTs;

			                    try { inactive.currentTime = 0; } catch (e) {}
			                    inactive.muted = active.muted;
			                    inactive.defaultMuted = active.defaultMuted;
			                    if (!active.muted && enableAudioCrossfade) {
			                        inactive.volume = 0;
			                    } else {
			                        inactive.volume = sourceVolume;
			                    }
			                    safePlay(inactive);
			                }

			                function finishBlend(){
			                    active.pause();
			                    try { active.currentTime = 0; } catch (e) {}
			                    active.style.opacity = "0";
			                    inactive.style.opacity = "1";

			                    const prev = active;
			                    active = inactive;
			                    inactive = prev;

			                    if (!active.muted) {
			                        active.volume = sourceVolume;
			                    }
			                    isBlending = false;
			                }

			                function recoverActiveLoop(){
			                    if (!active) { return; }
			                    try {
			                        if (isFinite(active.duration) && active.currentTime >= (active.duration - 0.02)) {
			                            active.currentTime = 0;
			                        }
			                    } catch (e) {}
			                    safePlay(active);
			                }

			                function tick(nowTs){
			                    rafId = requestAnimationFrame(tick);
			                    if (!started || !active) { return; }
			                    if (!isFinite(active.duration) || active.duration <= 0) {
			                        safePlay(active);
			                        return;
			                    }
			                    if (active.paused) {
			                        recoverActiveLoop();
			                        return;
			                    }

			                    const blendTriggerTime = active.duration - overlap;
			                    if (!isBlending && active.currentTime >= blendTriggerTime) {
			                        startBlend(nowTs);
			                    }

			                    if (!isBlending) { return; }

				                    const elapsed = (nowTs - blendStartTs) / 1000;
				                    const linear = Math.max(0, Math.min(1, elapsed / overlap));
				                    // Keep bg blending subtle: most of the time stays on the current frame,
				                    // then transitions quickly near the loop boundary.
				                    const progress = videoEl.classList.contains("smooth-loop-bg")
				                        ? Math.pow(linear, 1.8)
				                        : linear;
				                    active.style.opacity = String(1 - progress);
				                    inactive.style.opacity = String(progress);

			                    if (!active.muted && enableAudioCrossfade) {
			                        active.volume = sourceVolume * (1 - progress);
			                        inactive.volume = sourceVolume * progress;
			                    }

			                    if (progress >= 1) {
			                        finishBlend();
			                    }
			                }

			                function startIfReady(){
			                    if (started) { return; }
			                    if (!isFinite(videoEl.duration) || videoEl.duration <= overlap) { return; }
			                    started = true;
			                    videoEl.style.opacity = "1";
			                    blendEl.style.opacity = "0";
			                    safePlay(videoEl);
			                    if (!rafId) { rafId = requestAnimationFrame(tick); }
			                }

			                videoEl.addEventListener("loadedmetadata", startIfReady);
			                videoEl.addEventListener("canplay", startIfReady);
			                [videoEl, blendEl].forEach(function(el){
			                    el.addEventListener("ended", function(e){
			                        e.preventDefault();
			                        recoverActiveLoop();
			                    });
			                    el.addEventListener("pause", function(){
			                        if (!started) { return; }
			                        if (el === active && !isBlending) {
			                            recoverActiveLoop();
			                        }
			                    });
			                });
			                document.addEventListener("visibilitychange", function(){
			                    if (document.visibilityState === "visible") {
			                        recoverActiveLoop();
			                    }
			                });

			                if (videoEl.readyState >= 1) {
			                    startIfReady();
			                }
			            }

				            document.querySelectorAll("video.smooth-loop-video").forEach(initSmoothLoopVideo);
				            document.querySelectorAll("video.magx-lock-video").forEach(enforceLockedAutoplay);
				            ["touchstart", "click", "visibilitychange", "pageshow"].forEach(function(evt){
				                document.addEventListener(evt, function(){
				                    if (evt === "visibilitychange" && document.visibilityState !== "visible") { return; }
				                    document.querySelectorAll("video.magx-lock-video").forEach(function(v){
				                        try {
				                            const p = v.play();
				                            if (p && typeof p.catch === "function") { p.catch(function(){}); }
				                        } catch (e) {}
				                    });
				                }, { passive: true });
				            });

			            // Keep the neon frame path radius aligned with the card border-radius.
			            function syncHeroNeonRadius(){
			                const card = document.querySelector("#title .hero-media-card");
			                if(!card){ return; }
			                const svg = card.querySelector("svg.hero-neon-frame");
			                if(!svg){ return; }

			                const rect = card.getBoundingClientRect();
			                if(!rect.width || !rect.height){ return; }

			                const brRaw = getComputedStyle(card).borderTopLeftRadius || "0px";
			                const br = Math.max(0, parseFloat(brRaw) || 0);

			                const vb = (svg.getAttribute("viewBox") || "0 0 100 100").trim().split(/\s+/).map(Number);
			                const vbW = (vb.length === 4 && isFinite(vb[2])) ? vb[2] : 100;
			                const vbH = (vb.length === 4 && isFinite(vb[3])) ? vb[3] : 100;
			                const unitX = rect.width / vbW;
			                const unitY = rect.height / vbH;

			                const rectEl = svg.querySelector("rect");
			                if(!rectEl){ return; }
			                const xU = parseFloat(rectEl.getAttribute("x") || "0") || 0;
			                const yU = parseFloat(rectEl.getAttribute("y") || "0") || 0;

			                const insetPxX = xU * unitX;
			                const insetPxY = yU * unitY;
			                const innerRxPx = Math.max(0, br - insetPxX);
			                const innerRyPx = Math.max(0, br - insetPxY);
			                const rxU = innerRxPx / unitX;
			                const ryU = innerRyPx / unitY;

			                svg.querySelectorAll("rect").forEach(r => {
			                    r.setAttribute("rx", String(rxU));
			                    r.setAttribute("ry", String(ryU));
			                });
			            }
			            syncHeroNeonRadius();
			            let heroNeonT = null;
			            window.addEventListener("resize", function(){
			                clearTimeout(heroNeonT);
			                heroNeonT = setTimeout(syncHeroNeonRadius, 80);
			            });

			            function playPortfolioIntro(){
		                const $root = $("#contactcontainer");
		                const $shell = $root.find(".folio-shell").first();
	                if(!$root.length || !$shell.length){ return; }

	                // Reset + replay reveal transitions whenever Portfolio is opened.
	                $shell.removeClass("is-playing");
	                const $revealEls = $root.find(".reveal");
	                $revealEls.removeClass("visible");
	                // Force reflow so transitions reliably restart.
	                if($root[0]){ void $root[0].offsetHeight; }

	                $shell.addClass("is-playing");
	                requestAnimationFrame(() => {
	                    $revealEls.addClass("visible");
	                });

	                // Keep the portfolio at the top on each open.
	                try { $root.scrollTop(0); } catch(e) {}
		            }
		            
			            const $mainSections = $("#title, #aboutcon, #servicescon, #contactcontainer");
			            const $homeScroller = $("#title");
			            const $homePosts = $("#homePosts");
			            const $homeFirstPost = $("#title .first-post-wrapper");
			            const $homePostsSentinel = $("#homePostsSentinel");
			            const $homeScrollHint = $("#homeScrollHint");
			            let homePostsVisible = false;
                        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

		            function setHomePostsVisible(visible){
		                if(homePostsVisible === visible){ return; }
		                homePostsVisible = visible;
		                const method = visible ? "removeClass" : "addClass";
		                const inverse = visible ? "addClass" : "removeClass";
		                $homeFirstPost[method]("is-hidden")[inverse]("is-revealed");
		                $homePosts[method]("is-hidden")[inverse]("is-revealed");
		                if($homeScrollHint.length){
		                    $homeScrollHint.toggleClass("is-off", visible);
		                }
		            }

			            function shouldShowHomePosts(){
			                if(!$homeScroller.length){ return false; }
                            if(isIOS){ return true; }
                            const node = $homeScroller.get(0);
                            if (node && node.scrollHeight <= (node.clientHeight + 8)) {
                                return true;
                            }
			                const st = $homeScroller.scrollTop();
			                if(!$homePostsSentinel.length){ return st > 160; }
			                const triggerTop = Math.max(120, $homePostsSentinel.position().top - ($homeScroller.innerHeight() * 0.25));
			                return st >= triggerTop;
			            }

		            function syncHomePostsVisibility(){
		                setHomePostsVisible(shouldShowHomePosts());
		            }

		            function resetHomePosts(){
		                homePostsVisible = false;
		                $homeFirstPost.addClass("is-hidden").removeClass("is-revealed");
		                $homePosts.addClass("is-hidden").removeClass("is-revealed");
		                if($homeScrollHint.length){
		                    $homeScrollHint.removeClass("is-off");
		                }
		            }

		            $homeScroller.on("scroll", syncHomePostsVisibility);
		            $(window).on("resize", syncHomePostsVisibility);

		            function stopAndHideAll(){
		                $mainSections.stop(true, true).hide();
		                // Reset portfolio reveal state when leaving portfolio.
		                $("#contactcontainer").find(".folio-shell").removeClass("is-playing");
		                $("#contactcontainer").find(".reveal").removeClass("visible");
		            }

		            function setActiveNav(btnId){
		                $(".bt").removeClass("active");
		                $(btnId).addClass("active");
		            }

		            function showMain(which){
		                stopAndHideAll();

		                if(which === "home"){
		                    $("#title").hide().fadeIn(450);
		                    $("#copyr").css("position", "fixed").show();
		                    resetHomePosts();
		                    setTimeout(function(){
		                        $homeScroller.stop(true).animate({ scrollTop: 0 }, 250);
		                    }, 50);
		                    setActiveNav("#home");
		                    return;
		                }

		                if(which === "about"){
		                    $("#aboutcon").hide().fadeIn(450);
		                    $("#copyr").hide();
		                    setActiveNav("#about");
		                    try { $("#aboutcon").scrollTop(0); } catch(e) {}
		                    return;
		                }

		                if(which === "services"){
		                    $("#servicescon").hide().fadeIn(450);
		                    $("#copyr").hide();
		                    setActiveNav("#services");
		                    try { $("#servicescon").scrollTop(0); } catch(e) {}
		                    return;
		                }

		                // portfolio (button id is #contact)
		                $("#contactcontainer").hide().fadeIn(420);
		                $("#copyr").css("position", "fixed").show();
		                setActiveNav("#contact");
		                playPortfolioIntro();
		            }

		            // Default view: Home only
		            showMain("home");
	            
	            $(".bt").on("click", function() {
	                $(".bt").removeClass("active");
	                $(this).addClass("active").focus();
	            });
	
	            // Primary nav buttons
	            $("#home").on("click", function(){ showMain("home"); });
	
	            $("#about").on("click", function(){ showMain("about"); });
	
	            $("#services").on("click", function(){ showMain("services"); });
	
	            $("#contact").on("click", function(){ showMain("portfolio"); });

	            // Hero CTAs
	            $("#heroPortfolio").on("click", function(){ $("#contact").trigger("click"); });
	            $("#heroExplore").on("click", function(){ $("#services").trigger("click"); });
	            $("#heroConsult").on("click", function(){
	                window.location.href = "mailto:magxsolutios2026@gmail.com";
	            });
	
	            // Scroll reveal animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if(entry.isIntersecting){
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });
	            document.querySelectorAll(".reveal").forEach(el => observer.observe(el));

	            // Portfolio utility actions
	            $(document).on("click", ".js-go-home", function(){
	                $("#home").trigger("click");
	            });

	            // Portfolio: sticky nav scroll + progress + filters + project modal
	            (function(){
	                const $folioRoot = $("#contactcontainer");
	                const $navBtns = $folioRoot.find(".folio-subnav .navbtn");
	                const $filterBtns = $folioRoot.find(".folio-subnav .filterbtn");
	                const $workCards = $folioRoot.find(".work-card[data-project]");

	                function folioScrollTo(sel){
	                    const $t = $folioRoot.find(sel);
	                    if(!$t.length){ return; }
	                    const top = $t.position().top + $folioRoot.scrollTop() - 12;
	                    $folioRoot.stop(true).animate({ scrollTop: Math.max(0, top) }, 520);
	                }

	                // (progress bar removed)

	                $navBtns.on("click", function(){
	                    const target = $(this).data("target");
	                    if(!target){ return; }
	                    $navBtns.removeClass("is-active");
	                    $(this).addClass("is-active");
	                    folioScrollTo(String(target));
	                });

	                function applyFilter(kind){
	                    $filterBtns.removeClass("is-active");
	                    $filterBtns.filter(`[data-filter="${kind}"]`).addClass("is-active");
	                    $workCards.each(function(){
	                        const cats = String($(this).data("cat") || "").toLowerCase();
	                        const on = (kind === "all") || cats.split(/\s+/).includes(String(kind).toLowerCase());
	                        $(this).toggleClass("is-off", !on);
	                    });
	                }
	                $filterBtns.on("click", function(){
	                    applyFilter(String($(this).data("filter") || "all"));
	                });
	                applyFilter("all");

	                const projectData = {
	                    tmc: {
	                        title: "Integrated Record Management and Monitoring System (TMC)",
	                        kicker: "2025 Deployment",
	                        subtitle: "Multi-industry operations: gasoline stations, leasing, and fast-food.",
	                        // Put your exported Canva images here (recommended path under uploads/portfolio/...)
	                        images: ["uploads/portfolio/tmc/tmc-mockup-01.jpg"],
	                        impact: [
	                            "Reduced manual and semi-manual processes through structured digital records",
	                            "Improved monitoring visibility across branches and business units",
	                            "More consistent reporting and easier tracking of operational history"
	                        ],
	                        features: [
	                            "Role-based access and secure forms/logins",
	                            "Searchable record history and monitoring views",
	                            "Reporting-ready data structure for operational summaries"
	                        ],
	                        stack: ["PHP", "MySQL", "Bootstrap", "JavaScript"]
	                    },
	                    magx: {
	                        title: "MAGX Solutions Landing + Engagement",
	                        kicker: "Web and UI Motion",
	                        subtitle: "Modern portfolio site with interactive posts, reactions, comments, sharing, and chat.",
	                        images: [],
	                        impact: [
	                            "Stronger conversion flow: clear sections, CTAs, and modern UI",
	                            "Engagement components to keep visitors interacting with content",
	                            "Mobile-friendly layout and polished motion"
	                        ],
	                        features: [
	                            "Responsive UI with section transitions and reveal animations",
	                            "Post engagement: heart (per device), comments modal, share modal",
	                            "Chat widget layout with quick actions and persistent history"
	                        ],
	                        stack: ["HTML/CSS", "JavaScript", "Bootstrap", "PHP"]
	                    },
	                    firstgate: {
	                        title: "Graphic Design and Brand Assets (Firstgate)",
	                        kicker: "2025 Design Role",
	                        subtitle: "Marketing visuals with consistent branding for digital and print.",
	                        images: [],
	                        impact: [
	                            "Improved brand consistency across marketing materials",
	                            "Faster creation of promotional assets with a clean visual system",
	                            "Better social media presentation for campaigns"
	                        ],
	                        features: [
	                            "Posters, menus, and promotional graphics",
	                            "Social media templates and brand-aligned layouts",
	                            "Design iteration based on content needs and consistency"
	                        ],
	                        stack: ["Branding", "Social Media", "Print Design"]
	                    }
	                };

	                function setList($ul, items){
	                    $ul.empty();
	                    (items || []).forEach(t => $ul.append(`<li>${String(t).replace(/</g,"&lt;").replace(/>/g,"&gt;")}</li>`));
	                }
	                function setTags($wrap, tags){
	                    $wrap.empty();
	                    (tags || []).forEach(t => $wrap.append(`<span class="folio-tag">${String(t).replace(/</g,"&lt;").replace(/>/g,"&gt;")}</span>`));
	                }

	                function openProject(key){
	                    const data = projectData[key];
	                    if(!data || typeof bootstrap === "undefined"){ return; }
	                    $("#ppTitle").text(data.title || "Project");
	                    $("#ppKicker").text(data.kicker || "Overview");
	                    $("#ppSubtitle").text(data.subtitle || "");

	                    // Gallery (blank placeholder for now; you can add images later)
	                    const imgs = Array.isArray(data.images) ? data.images : [];
	                    const $inner = $("#ppCarouselInner");
	                    $inner.empty();
	                    if(imgs.length){
	                        imgs.forEach((src, idx) => {
	                            const safe = String(src).replace(/\"/g, "&quot;");
	                            $inner.append(
	                                `<div class="carousel-item ${idx === 0 ? "active" : ""}">
	                                    <img src="${safe}" alt="" onerror="try{this.closest('.carousel-item').remove();}catch(e){}">
	                                </div>`
	                            );
	                        });
	                    }

	                    // If all images failed to load (removed), fall back to placeholder.
	                    setTimeout(() => {
	                        if(!$inner.children().length){
	                            $inner.append(
	                                `<div class="carousel-item active">
	                                    <div class="ph">
	                                        <div>
	                                            <div class="icon"><i class="fas fa-images" aria-hidden="true"></i></div>
	                                            <div class="t">Screenshots coming soon</div>
	                                            <div class="d">Upload your project screenshots, then tell me the filenames. I will plug them into this gallery slider.</div>
	                                        </div>
	                                    </div>
	                                </div>`
	                            );
	                        } else {
	                            // Ensure exactly one active slide
	                            $inner.children().removeClass("active").first().addClass("active");
	                        }

	                        const count = $inner.children().length;
	                        $("#ppPrevBtn, #ppNextBtn").toggle(count > 1);
	                    }, 0);

	                    const cEl = document.getElementById("ppCarousel");
	                    if(cEl){
	                        const old = bootstrap.Carousel.getInstance(cEl);
	                        if(old){ old.dispose(); }
	                        bootstrap.Carousel.getOrCreateInstance(cEl, { interval: false, ride: false, wrap: true });
	                    }

	                    setList($("#ppImpact"), data.impact);
	                    setList($("#ppFeatures"), data.features);
	                    setTags($("#ppStack"), data.stack);
	                    const inst = bootstrap.Modal.getOrCreateInstance(document.getElementById("portfolioProjectModal"), { backdrop: true, keyboard: true });
	                    inst.show();
	                }

	                $folioRoot.on("click", ".work-card.is-clickable", function(){
	                    const key = $(this).data("project");
	                    if(!key){ return; }
	                    openProject(String(key));
	                });
	            })();

	            (function(){
	                const $root = $("#contactcontainer");
	                if(!$root.length){ return; }

	                (function initFeaturedCarousel(){
	                    const carousel = document.getElementById("pxFeaturedCarousel");
	                    if(!carousel){ return; }
	                    const track = carousel.querySelector(".px-carousel-track");
	                    const slides = track ? track.querySelectorAll(".px-carousel-slide") : [];
	                    const prevBtn = document.getElementById("pxFeaturedPrev");
	                    const nextBtn = document.getElementById("pxFeaturedNext");
	                    if(!track || slides.length <= 1 || !prevBtn || !nextBtn){ return; }

	                    let index = 0;
	                    const last = slides.length - 1;

	                    function render(){
	                        track.style.transform = "translateX(-" + (index * 100) + "%)";
	                    }
	                    render();

	                    prevBtn.addEventListener("click", function(){
	                        index = (index === 0) ? last : index - 1;
	                        render();
	                    });

	                    nextBtn.addEventListener("click", function(){
	                        index = (index === last) ? 0 : index + 1;
	                        render();
	                    });

	                    carousel.setAttribute("tabindex", "0");
	                    carousel.addEventListener("keydown", function(e){
	                        if(e.key === "ArrowLeft"){
	                            e.preventDefault();
	                            prevBtn.click();
	                        } else if(e.key === "ArrowRight"){
	                            e.preventDefault();
	                            nextBtn.click();
	                        }
	                    });

	                    let touchStartX = 0;
	                    let touchEndX = 0;
	                    carousel.addEventListener("touchstart", function(e){
	                        if(!e.touches || !e.touches.length){ return; }
	                        touchStartX = e.touches[0].clientX;
	                    }, { passive: true });
	                    carousel.addEventListener("touchend", function(e){
	                        if(!e.changedTouches || !e.changedTouches.length){ return; }
	                        touchEndX = e.changedTouches[0].clientX;
	                        const delta = touchEndX - touchStartX;
	                        if(Math.abs(delta) < 40){ return; }
	                        if(delta > 0){
	                            prevBtn.click();
	                        } else {
	                            nextBtn.click();
	                        }
	                    }, { passive: true });
	                })();

	                $root.on("click", ".px-open-case", function(){
	                    const target = String($(this).data("case-target") || "");
	                    if(!target){ return; }
	                    const $trigger = $root.find(target).first();
	                    if(!$trigger.length){ return; }

	                    const collapseId = $trigger.attr("data-bs-target");
	                    if(!collapseId){ return; }
	                    const collapseEl = document.querySelector(collapseId);
	                    if(!collapseEl || typeof bootstrap === "undefined"){ return; }

	                    const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
	                    collapse.show();

	                    setTimeout(function(){
	                        const top = $trigger.position().top + $root.scrollTop() - 14;
	                        $root.stop(true).animate({ scrollTop: Math.max(0, top) }, 520);
	                    }, 120);
	                });

	                const fills = $root.find(".px-skill-fill").toArray();
	                if(!fills.length){ return; }

	                const observerRoot = $root.get(0);
	                const skillObserver = new IntersectionObserver((entries) => {
	                    entries.forEach((entry) => {
	                        if(entry.isIntersecting){
	                            entry.target.classList.add("is-on");
	                            skillObserver.unobserve(entry.target);
	                        }
	                    });
	                }, { threshold: 0.35, root: observerRoot });

	                fills.forEach((el) => skillObserver.observe(el));
	            })();

	            // Home post engagement: like, comment, share
	            (function(){
	                const API_URL = "home_posts_api.php";
	                const DEVICE_ID_KEY = "magx_home_post_device_id_v1";
	                const LIKED_MAP_KEY = "magx_home_post_liked_map_v1";

	                const commentModalEl = document.getElementById("postCommentModal");
	                const shareModalEl = document.getElementById("postShareModal");
	                const commentModal = (window.bootstrap && commentModalEl) ? bootstrap.Modal.getOrCreateInstance(commentModalEl) : null;
	                const shareModal = (window.bootstrap && shareModalEl) ? bootstrap.Modal.getOrCreateInstance(shareModalEl) : null;

	                const $commentPostId = $("#commentPostId");
	                const $commentPostTitle = $("#commentPostTitle");
	                const $commentsList = $("#commentsList");
	                const $commentText = $("#commentText");
	                const $commentCharCount = $("#commentCharCount");
	                const $submitCommentBtn = $("#submitCommentBtn");

	                const $sharePostId = $("#sharePostId");
	                const $shareUrlInput = $("#shareUrlInput");
	                const $shareCopyBtn = $("#shareCopyBtn");
	                const $shareCopyStatus = $("#shareCopyStatus");

	                function escapeHtml(text){
	                    return String(text || "")
	                        .replace(/&/g, "&amp;")
	                        .replace(/</g, "&lt;")
	                        .replace(/>/g, "&gt;")
	                        .replace(/"/g, "&quot;")
	                        .replace(/'/g, "&#39;");
	                }

	                function parseJsonSafe(raw){
	                    if (raw && typeof raw === "object") { return raw; }
	                    if (typeof raw !== "string") { return null; }
	                    try { return JSON.parse(raw); } catch (e) { return null; }
	                }

	                function randomToken(length){
	                    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
	                    let out = "";
	                    for(let i = 0; i < length; i++){
	                        out += chars.charAt(Math.floor(Math.random() * chars.length));
	                    }
	                    return out;
	                }

	                function getOrCreateDeviceId(){
	                    const valid = /^[A-Za-z0-9_-]{8,128}$/;
	                    let v = "";
	                    try { v = String(localStorage.getItem(DEVICE_ID_KEY) || ""); } catch (e) {}
	                    if (valid.test(v)) { return v; }

	                    if (window.crypto && typeof window.crypto.getRandomValues === "function") {
	                        const bytes = new Uint8Array(16);
	                        window.crypto.getRandomValues(bytes);
	                        v = Array.from(bytes).map(b => b.toString(16).padStart(2, "0")).join("");
	                    } else {
	                        v = randomToken(32);
	                    }

	                    if (!valid.test(v)) {
	                        v = randomToken(32);
	                    }
	                    try { localStorage.setItem(DEVICE_ID_KEY, v); } catch (e) {}
	                    return v;
	                }

	                function loadLikedMap(){
	                    try {
	                        const raw = localStorage.getItem(LIKED_MAP_KEY);
	                        const parsed = raw ? JSON.parse(raw) : {};
	                        if (parsed && typeof parsed === "object") {
	                            return parsed;
	                        }
	                    } catch (e) {}
	                    return {};
	                }

	                function saveLikedMap(map){
	                    try { localStorage.setItem(LIKED_MAP_KEY, JSON.stringify(map || {})); } catch (e) {}
	                }

	                const likedMap = loadLikedMap();

	                function setLiked(postId, liked){
	                    if (!postId) { return; }
	                    const key = String(postId);
	                    if (liked) {
	                        likedMap[key] = 1;
	                    } else {
	                        delete likedMap[key];
	                    }
	                    saveLikedMap(likedMap);
	                }

	                function isLiked(postId){
	                    return !!likedMap[String(postId)];
	                }

	                function getPostMetaFromCard($card){
	                    const postId = parseInt($card.attr("data-post-id"), 10) || 0;
	                    const title = String($card.find(".product-title h3").first().text() || "Post").trim();
	                    return { postId, title };
	                }

	                function getCardByPostId(postId){
	                    return $('.product-card[data-post-id="' + String(postId) + '"]').first();
	                }

	                function getCountEl($card, kind){
	                    return $card.find('.social-chip[data-kind="' + kind + '"] .social-count').first();
	                }

	                function readCount($card, kind){
	                    const txt = String(getCountEl($card, kind).text() || "0").trim();
	                    const n = parseInt(txt, 10);
	                    return Number.isFinite(n) ? n : 0;
	                }

	                function setCount($card, kind, count){
	                    const n = parseInt(count, 10);
	                    if (!Number.isFinite(n)) { return; }
	                    getCountEl($card, kind).text(Math.max(0, n));
	                }

	                function bumpCount($card, kind, delta){
	                    const current = readCount($card, kind);
	                    setCount($card, kind, current + (parseInt(delta, 10) || 1));
	                }

	                function setHeartVisual($card, liked){
	                    const $heart = $card.find('.social-chip[data-kind="like"]').first();
	                    if (!$heart.length) { return; }
	                    $heart.toggleClass("is-liked", !!liked);
	                    $heart.attr("aria-pressed", liked ? "true" : "false");
	                }

	                function syncLikedVisuals(){
	                    $(".product-card[data-post-id]").each(function(){
	                        const id = parseInt($(this).attr("data-post-id"), 10) || 0;
	                        if (id > 0 && isLiked(id)) {
	                            setHeartVisual($(this), true);
	                        }
	                    });
	                }

	                function postApi(payload){
	                    const dfd = $.Deferred();
	                    $.ajax({
	                        url: API_URL,
	                        method: "POST",
	                        data: payload,
	                        dataType: "json"
	                    }).done(function(res){
	                        dfd.resolve(parseJsonSafe(res) || {});
	                    }).fail(function(xhr){
	                        const parsed = parseJsonSafe(xhr && xhr.responseText ? xhr.responseText : "");
	                        dfd.reject(parsed || { success: false, message: "Request failed." });
	                    });
	                    return dfd.promise();
	                }

	                function formatCommentTime(raw){
	                    const d = new Date(String(raw || "").replace(" ", "T"));
	                    if (Number.isNaN(d.getTime())) { return String(raw || ""); }
	                    return d.toLocaleString([], {
	                        year: "numeric",
	                        month: "short",
	                        day: "numeric",
	                        hour: "numeric",
	                        minute: "2-digit"
	                    });
	                }

	                function renderComments(items){
	                    const rows = Array.isArray(items) ? items : [];
	                    if (!rows.length) {
	                        $commentsList.html('<div class="text-muted" style="font-size:13px;">No comments yet. Be the first to comment.</div>');
	                        return;
	                    }

	                    const html = rows.map(function(c){
	                        const authorRaw = String(c && c.author_name ? c.author_name : "Guest").trim();
	                        const author = authorRaw ? authorRaw : "Guest";
	                        const initial = author.charAt(0).toUpperCase();
	                        const text = String(c && c.comment_text ? c.comment_text : "");
	                        const time = formatCommentTime(c && c.created_at ? c.created_at : "");
	                        return (
	                            '<div class="comment-row">' +
	                                '<div class="comment-avatar">' + escapeHtml(initial) + '</div>' +
	                                '<div class="comment-body">' +
	                                    '<div class="comment-head">' +
	                                        '<span class="comment-author">' + escapeHtml(author) + '</span>' +
	                                        '<span class="comment-time">' + escapeHtml(time) + '</span>' +
	                                    "</div>" +
	                                    '<div class="comment-text">' + escapeHtml(text).replace(/\n/g, "<br>") + "</div>" +
	                                "</div>" +
	                            "</div>"
	                        );
	                    }).join("");

	                    $commentsList.html(html);
	                }

	                function loadComments(postId){
	                    if (!postId) { return; }
	                    $commentsList.html('<div class="text-muted" style="font-size:13px;">Loading...</div>');
	                    postApi({ action: "LIST_COMMENTS", id: postId, limit: 50 }).done(function(res){
	                        if (!res || !res.success) {
	                            const msg = res && res.message ? String(res.message) : "Could not load comments.";
	                            $commentsList.html('<div class="text-danger" style="font-size:13px;">' + escapeHtml(msg) + "</div>");
	                            return;
	                        }
	                        renderComments(res.data || []);
	                    }).fail(function(err){
	                        const msg = err && err.message ? String(err.message) : "Could not load comments.";
	                        $commentsList.html('<div class="text-danger" style="font-size:13px;">' + escapeHtml(msg) + "</div>");
	                    });
	                }

	                function buildShareUrl(postId){
	                    const u = new URL(window.location.href);
	                    u.hash = "post-" + String(postId);
	                    return u.toString();
	                }

	                function setShareLinks(shareUrl, title){
	                    const urlEnc = encodeURIComponent(shareUrl);
	                    const text = title ? (title + " - MAGX Solutions") : "MAGX Solutions";
	                    const textEnc = encodeURIComponent(text);
	                    const bodyEnc = encodeURIComponent(text + "\n" + shareUrl);

	                    $("#shareFacebook").attr("href", "https://www.facebook.com/sharer/sharer.php?u=" + urlEnc);
	                    $("#shareWhatsapp").attr("href", "https://wa.me/?text=" + textEnc + "%20" + urlEnc);
	                    $("#shareX").attr("href", "https://twitter.com/intent/tweet?text=" + textEnc + "&url=" + urlEnc);
	                    $("#shareLinkedIn").attr("href", "https://www.linkedin.com/sharing/share-offsite/?url=" + urlEnc);
	                    $("#shareTelegram").attr("href", "https://t.me/share/url?url=" + urlEnc + "&text=" + textEnc);
	                    $("#shareEmail").attr("href", "mailto:?subject=" + textEnc + "&body=" + bodyEnc);
	                }

	                function incrementShare(postId){
	                    if (!postId) { return; }
	                    const $card = getCardByPostId(postId);
	                    postApi({ action: "ENGAGE", id: postId, kind: "share" }).done(function(res){
	                        if (!$card.length) { return; }
	                        if (res && res.success && Number.isFinite(parseInt(res.count, 10))) {
	                            setCount($card, "share", res.count);
	                        } else {
	                            bumpCount($card, "share", 1);
	                        }
	                    });
	                }

	                function openCommentModal($card){
	                    const meta = getPostMetaFromCard($card);
	                    if (!meta.postId) { return; }
	                    $commentPostId.val(String(meta.postId));
	                    $commentPostTitle.text(meta.title || "Post");
	                    $commentText.val("");
	                    $commentCharCount.text("0/1000");
	                    loadComments(meta.postId);
	                    if (commentModal) { commentModal.show(); }
	                }

	                function openShareModal($card){
	                    const meta = getPostMetaFromCard($card);
	                    if (!meta.postId) { return; }
	                    const shareUrl = buildShareUrl(meta.postId);
	                    $sharePostId.val(String(meta.postId));
	                    $shareUrlInput.val(shareUrl);
	                    $shareCopyStatus.hide();
	                    setShareLinks(shareUrl, meta.title);
	                    if (shareModal) { shareModal.show(); }
	                    incrementShare(meta.postId);
	                }

	                function copyShareUrl(){
	                    const text = String($shareUrlInput.val() || "").trim();
	                    if (!text) { return; }
	                    function showStatus(msg){
	                        $shareCopyStatus.text(msg).show();
	                        clearTimeout(copyShareUrl._timer);
	                        copyShareUrl._timer = setTimeout(function(){ $shareCopyStatus.fadeOut(150); }, 1400);
	                    }
	                    if (navigator.clipboard && navigator.clipboard.writeText) {
	                        navigator.clipboard.writeText(text).then(function(){
	                            showStatus("Copied to clipboard.");
	                        }).catch(function(){
	                            showStatus("Copy failed. Select and copy manually.");
	                        });
	                        return;
	                    }
	                    try {
	                        $shareUrlInput.trigger("focus").trigger("select");
	                        const ok = document.execCommand("copy");
	                        showStatus(ok ? "Copied to clipboard." : "Copy failed. Select and copy manually.");
	                    } catch (e) {
	                        showStatus("Copy failed. Select and copy manually.");
	                    }
	                }

	                function handleLike($chip, $card){
	                    const meta = getPostMetaFromCard($card);
	                    if (!meta.postId) { return; }
	                    if ($chip.data("busy")) { return; }
	                    $chip.data("busy", 1).addClass("is-popping");
	                    setTimeout(function(){ $chip.removeClass("is-popping"); }, 320);

	                    postApi({
	                        action: "ENGAGE",
	                        id: meta.postId,
	                        kind: "like",
	                        device_id: getOrCreateDeviceId()
	                    }).done(function(res){
	                        if (!res || !res.success) { return; }
	                        if (Number.isFinite(parseInt(res.count, 10))) {
	                            setCount($card, "like", res.count);
	                        }
	                        if (res.liked || res.already_liked) {
	                            setLiked(meta.postId, true);
	                            setHeartVisual($card, true);
	                        }
	                    }).always(function(){
	                        $chip.data("busy", 0);
	                    });
	                }

	                function submitComment(){
	                    const postId = parseInt($commentPostId.val(), 10) || 0;
	                    const comment = String($commentText.val() || "").trim();
	                    if (!postId) { return; }
	                    if (!comment) {
	                        $commentText.trigger("focus");
	                        return;
	                    }
	                    if ($submitCommentBtn.data("busy")) { return; }

	                    $submitCommentBtn.data("busy", 1).prop("disabled", true).text("Posting...");
	                    postApi({
	                        action: "ADD_COMMENT",
	                        id: postId,
	                        comment: comment
	                    }).done(function(res){
	                        if (!res || !res.success) {
	                            const msg = res && res.message ? String(res.message) : "Failed to add comment.";
	                            alert(msg);
	                            return;
	                        }
	                        const $card = getCardByPostId(postId);
	                        if ($card.length) {
	                            if (Number.isFinite(parseInt(res.count, 10))) {
	                                setCount($card, "comment", res.count);
	                            } else {
	                                bumpCount($card, "comment", 1);
	                            }
	                        }
	                        $commentText.val("");
	                        $commentCharCount.text("0/1000");
	                        loadComments(postId);
	                    }).fail(function(err){
	                        alert((err && err.message) ? String(err.message) : "Failed to add comment.");
	                    }).always(function(){
	                        $submitCommentBtn.data("busy", 0).prop("disabled", false).text("Post Comment");
	                    });
	                }

	                $(document).on("click", ".product-card .social-chip", function(){
	                    const $chip = $(this);
	                    const $card = $chip.closest(".product-card");
	                    const kind = String($chip.attr("data-kind") || "").toLowerCase();
	                    if (!$card.length || !kind) { return; }

	                    if (kind === "like") {
	                        handleLike($chip, $card);
	                        return;
	                    }
	                    if (kind === "comment") {
	                        openCommentModal($card);
	                        return;
	                    }
	                    if (kind === "share") {
	                        openShareModal($card);
	                    }
	                });

	                $commentText.on("input", function(){
	                    const len = String($(this).val() || "").length;
	                    $commentCharCount.text(len + "/1000");
	                });

	                $submitCommentBtn.on("click", submitComment);
	                $commentText.on("keydown", function(e){
	                    if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
	                        e.preventDefault();
	                        submitComment();
	                    }
	                });

	                $shareCopyBtn.on("click", copyShareUrl);

	                syncLikedVisuals();
	            })();

            
            // MAGX Solutions AI chat behavior
            const magxSystemPrompt = `You are MAGX Solutions AI, the official website assistant. Company profile: MAGX Solutions was founded on January 5, 2026 and helps businesses transition from manual processes to structured digital systems. Services: Business System Development, Workflow Automation, Web-Based Systems, Database Management, Custom Software Solutions. Vision: To be a trusted digital solutions provider for growing businesses. Mission: Help businesses transition to reliable, user-friendly digital systems that improve workflow and productivity. Target market: SMEs, retail, gas stations, service providers, startups, and businesses moving from manual processes. Competitive edge: workflow-focused, automation-first, custom-built, user-friendly, logical, scalable. Founder: Mark Angelo H. Guillermo (System Developer & Workflow Automation Specialist). Contact: magxsolutios2026@gmail.com, +63 997-369-3779, https://www.facebook.com/magxsolutions2026, Alibagu City of Ilagan Isabela. Behavior: understand user intent, ask one clarifying question only if intent is unclear, keep responses concise/accurate/helpful, do not invent facts, and keep a professional, friendly tone.`;
            window.magxSystemPrompt = magxSystemPrompt;

            const $magxChatWindow = $("#magxChatWindow");
            const $magxChatToggle = $("#magxChatToggle");
            const $magxChatClose = $("#magxChatClose");
            const $magxChatClear = $("#magxChatClear");
            const $magxMessages = $("#magxMessages");
            const $magxQuick = $("#magxQuick");
            const $magxUserInput = $("#magxUserInput");
            const $magxSendBtn = $("#magxSendBtn");
            let magxGreeted = false;
            let magxLastReplyKey = null;
            let magxContext = {
                detailsRequested: false,
                lastNeed: ""
            };
            const magxQuickActions = [
                { label: "Services", text: "What services do you offer?" },
                { label: "Pricing", text: "How much does a system cost?" },
                { label: "Timeline", text: "How long does a project take?" },
                { label: "Portfolio", text: "Show portfolio / sample projects." },
                { label: "Contact", text: "How can I contact MAGX Solutions?" }
            ];

            function magxNowTime(ts){
                const d = new Date(ts || Date.now());
                return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
            }

            function magxLoadState(){
                // Always start fresh on page load/refresh.
                return { v: 1, messages: [] };
            }

            function magxSaveState(state){
                // Keep state only in-memory for current page session.
                return state;
            }

            let magxState = magxLoadState();

            function magxRenderQuickActions(){
                if(!$magxQuick.length){ return; }
                const html = magxQuickActions.map(a => (
                    `<button type="button" class="magx-quick-btn" data-text="${String(a.text).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;")}">${a.label}</button>`
                )).join("");
                $magxQuick.html(html);
            }

            function magxScrollMessages(){
                $magxMessages.scrollTop($magxMessages[0].scrollHeight);
            }

            function magxAppendTime($bubble, ts){
                const $t = $('<div class="magx-time"></div>');
                $t.text(magxNowTime(ts));
                $bubble.append($t);
            }

	            function magxAddUserMessage(text, opts = {}){
	                const ts = opts.ts || Date.now();
	                const bubble = $('<div class="magx-message user"><div class="magx-avatar magx-avatar-me" aria-hidden="true"><span class="magx-avatar-text">ME</span></div><div class="magx-bubble"></div></div>');
	                const $b = bubble.find('.magx-bubble');
	                $b.text(text);
	                magxAppendTime($b, ts);
	                $magxMessages.append(bubble);
                magxScrollMessages();
                if(!opts.restore){
                    magxState.messages.push({ role: "user", text: String(text), ts: ts });
                    magxSaveState(magxState);
                }
            }

	            function magxAddAssistantMessage(html, opts = {}){
	                const ts = opts.ts || Date.now();
	                // Use \' (not \\') inside a single-quoted JS string, otherwise the quote terminates early.
		                const bubble = $('<div class="magx-message assistant"><div class="magx-avatar magx-avatar-bot" aria-hidden="true"><img src="bot2x2.png" alt="" onerror="this.style.display=\'none\';" /></div><div class="magx-bubble"></div></div>');
	                const $b = bubble.find('.magx-bubble');
	                $b.html(html);
	                magxAppendTime($b, ts);
	                $magxMessages.append(bubble);
                magxScrollMessages();
                if(!opts.restore){
                    magxState.messages.push({ role: "assistant", html: String(html), ts: ts });
                    magxSaveState(magxState);
                }
            }

            function magxSendAssistant(key, html){
                if(key && key === magxLastReplyKey){
                    // avoid exact same response twice in a row
                    key = key + "-alt";
                }
                magxLastReplyKey = key;
                magxAddAssistantMessage(html);
            }

	            function magxShowTyping(){
				                const typing = $('<div class="magx-message assistant typing"><div class="magx-avatar magx-avatar-bot" aria-hidden="true"><img src="bot2x2.png" alt="" onerror="this.style.display=\'none\';" /></div><div class="magx-bubble"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div></div>');
	                $magxMessages.append(typing);
	                magxScrollMessages();
	                return typing;
	            }

            function magxCraftReply(input){
                const text = String(input || "").trim();
                const q = text.toLowerCase();

                const hasAny = (arr) => arr.some(k => q.includes(k));
                const toReply = (key, html) => ({ key, html });
                const safe = (s) => String(s || "")
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");

                if(!text){
                    return toReply("empty", "<p>Please share your question so I can help.</p>");
                }

                if(q.includes("user inquiry:") && q.includes("original bot response:")){
                    const userMatch = text.match(/user inquiry:\s*([\s\S]*?)(?:\n\s*original bot response:|$)/i);
                    const draftMatch = text.match(/original bot response:\s*([\s\S]*?)$/i);
                    const userInquiry = userMatch ? userMatch[1].trim() : "";
                    const originalDraft = draftMatch ? draftMatch[1].trim() : "";
                    if(!userInquiry || !originalDraft){
                        return toReply(
                            "improve-missing",
                            "<p>Please include both sections so I can improve it correctly:</p><ul><li><strong>User Inquiry:</strong> ...</li><li><strong>Original Bot Response:</strong> ...</li></ul>"
                        );
                    }
                    const improved = "Thank you for your message. Based on your needs, MAGX Solutions can help through workflow-focused and user-friendly digital solutions. For more info or any questions, email us at magxsolutios2026@gmail.com or message our Facebook page: facebook.com/magxsolutions2026.";
                    return toReply(
                        "improve-format",
                        "<p><strong>User Inquiry:</strong> " + safe(userInquiry) + "</p>" +
                        "<p><strong>Original Bot Response:</strong> " + safe(originalDraft) + "</p>" +
                        "<p><strong>Improved Response:</strong> " + safe(improved) + "</p>"
                    );
                }

                if(hasAny(["hello", "hi", "good morning", "good afternoon", "good evening"])){
                    return toReply(
                        "greet",
                        "<p>Hello. Welcome to <strong>MAGX Solutions</strong>.</p><p>How can we help your business today: services, pricing, timeline, portfolio, or consultation?</p>"
                    );
                }

                if(hasAny(["founder", "owner", "who made magx", "who started", "who found magx", "who founded magx", "found magx"])){
                    return toReply(
                        "founder",
                        "<p>MAGX Solutions was founded by <strong>Mark Angelo H. Guillermo</strong>, a System Developer and Workflow Automation Specialist.</p>"
                    );
                }

                if(hasAny(["vision", "mission", "goal"])){
                    return toReply(
                        "vision-mission",
                        "<p><strong>Vision:</strong> To be a trusted digital solutions provider for growing businesses.</p><p><strong>Mission:</strong> Help businesses transition to reliable, user-friendly digital systems that improve workflow and productivity.</p>"
                    );
                }

                if(hasAny(["price", "pricing", "cost", "budget", "how much"])){
                    magxContext.detailsRequested = true;
                    magxContext.lastNeed = "pricing";
                    return toReply(
                        "pricing",
                        "<p>Pricing depends on the features, scope, users, integrations, and target timeline.</p><p>For more info or any questions, email us at <a href=\"mailto:magxsolutios2026@gmail.com\">magxsolutios2026@gmail.com</a> or message our Facebook page: <a href=\"https://www.facebook.com/magxsolutions2026\" target=\"_blank\" rel=\"noopener\">facebook.com/magxsolutions2026</a>.</p>"
                    );
                }

                if(hasAny(["timeline", "how long", "duration", "deadline"])){
                    return toReply(
                        "timeline",
                        "<p>Typical project flow:</p><ul><li>Discovery and scope: 1-3 days</li><li>Design and planning: 3-7 days</li><li>Development and testing: 2-8 weeks (depends on complexity)</li></ul><p>For more info or any questions, email us at <a href=\"mailto:magxsolutios2026@gmail.com\">magxsolutios2026@gmail.com</a> or message our Facebook page: <a href=\"https://www.facebook.com/magxsolutions2026\" target=\"_blank\" rel=\"noopener\">facebook.com/magxsolutions2026</a>.</p>"
                    );
                }

                if(hasAny(["portfolio", "sample", "project", "case study", "demo"])){
                    return toReply(
                        "portfolio",
                        "<p>You can view featured work in the <strong>Portfolio</strong> section, including system goals, solutions, tools, and outcomes.</p><p>If you want, I can also recommend the most relevant project type for your business.</p>"
                    );
                }

                if(hasAny(["market", "target client", "who do you help", "industries"])){
                    return toReply(
                        "target-market",
                        "<p>MAGX Solutions mainly supports SMEs, retail businesses, gas stations, service providers, startups, and teams transitioning from manual processes to digital workflows.</p>"
                    );
                }

                if(hasAny(["advantage", "why magx", "difference", "why choose"])){
                    return toReply(
                        "competitive-advantage",
                        "<p>Our approach is workflow-focused and automation-first. We build custom, user-friendly systems that are logical, scalable, and aligned with real business operations.</p>"
                    );
                }

                if(hasAny(["contact", "consult", "email", "call", "reach", "inquiry"])){
                    return toReply(
                        "contact",
                        "<p>You can contact MAGX Solutions via:</p><ul><li>Email: <a href=\"mailto:magxsolutios2026@gmail.com\">magxsolutios2026@gmail.com</a></li><li>Phone: +63 997-369-3779</li><li>Facebook: <a href=\"https://www.facebook.com/magxsolutions2026\" target=\"_blank\" rel=\"noopener\">facebook.com/magxsolutions2026</a></li><li>Address: Alibagu City of Ilagan Isabela</li></ul>"
                    );
                }

                if(hasAny(["service", "offer", "what do you do", "solutions"])){
                    return toReply(
                        "services",
                        "<p>We provide digital solutions designed for business operations:</p><ul><li>Business System Development</li><li>Workflow Automation</li><li>Web-Based Systems</li><li>Database Management</li><li>Custom Software Solutions</li></ul><p>For more info or any questions, email us at <a href=\"mailto:magxsolutios2026@gmail.com\">magxsolutios2026@gmail.com</a> or message our Facebook page: <a href=\"https://www.facebook.com/magxsolutions2026\" target=\"_blank\" rel=\"noopener\">facebook.com/magxsolutions2026</a>.</p>"
                    );
                }

                if(hasAny(["thank", "thanks", "salamat"])){
                    return toReply("thanks", "<p>You are welcome. If you want, I can help you with services, pricing, timeline, or consultation next.</p>");
                }

                return toReply(
                    "fallback",
                    "<p>To make sure I guide you correctly, what do you want help with right now: services, pricing, timeline, portfolio, or contact details?</p>"
                );
            }


            function magxOpenChat(){
                $magxChatWindow.addClass("open");
                $magxChatToggle.addClass("open");
                magxRenderQuickActions();
                if(!magxState.messages.length){
                    // Fresh chat: greet + quick actions visible.
                    if(!magxGreeted){
                        magxSendAssistant("greet-initial","<p>Welcome to <strong>MAGX Solutions</strong>. How can we help optimize your business today?</p><p>You can ask about services, pricing, timeline, or request a consultation.</p>");
                        magxGreeted = true;
                    }
                } else if(!magxGreeted) {
                    magxGreeted = true;
                }
                setTimeout(() => $magxUserInput.trigger("focus"), 120);
            }

            function magxCloseChat(){
                $magxChatWindow.removeClass("open");
                $magxChatToggle.removeClass("open");
            }

            function magxSend(){
                const text = $magxUserInput.val().trim();
                if(!text){ return; }
                if(text === "/clear"){
                    magxClearChat(true);
                    return;
                }
                if(text.length > 800){
                    magxAddAssistantMessage("<p>Your message is a bit long. Please shorten it (max 800 characters) or split it into parts.</p>");
                    return;
                }
                magxAddUserMessage(text);
                $magxUserInput.val("");
                const typingRow = magxShowTyping();
                setTimeout(() => {
                    typingRow.remove();
                    const reply = magxCraftReply(text);
                    magxSendAssistant(reply.key, reply.html);
                }, Math.min(1400, 500 + text.length * 20));
            }

            function magxRestoreChat(){
                $magxMessages.empty();
                if(!magxState || !Array.isArray(magxState.messages)){ return; }
                magxState.messages.forEach(m => {
                    if(m.role === "user"){
                        magxAddUserMessage(m.text || "", { restore: true, ts: m.ts || Date.now() });
                    } else if(m.role === "assistant"){
                        magxAddAssistantMessage(m.html || "", { restore: true, ts: m.ts || Date.now() });
                    }
                });
                if(magxState.messages.length){
                    magxGreeted = true;
                }
            }

            function magxClearChat(reopen){
                magxState = { v: 1, messages: [] };
                magxSaveState(magxState);
                magxContext = { detailsRequested: false, lastNeed: "" };
                magxLastReplyKey = null;
                magxGreeted = false;
                $magxMessages.empty();
                if(reopen){
                    magxOpenChat();
                }
            }

            $magxChatToggle.on("click", function(){
                if($magxChatWindow.hasClass("open")){
                    magxCloseChat();
                } else {
                    magxOpenChat();
                }
            });

            $magxChatClose.on("click", magxCloseChat);
            $magxChatClear.on("click", function(){ magxClearChat(true); });
            // If a dedicated CTA exists, wire it (safe even if absent)
            $("#openMagxChat").on("click", function(){
                magxOpenChat();
            });
            $magxSendBtn.on("click", magxSend);
            $magxUserInput.on("keydown", function(e){
                if(e.key === "Enter"){
                    e.preventDefault();
                    magxSend();
                }
            });
            $(document).on("click", "#magxQuick button", function(){
                const txt = $(this).data("text");
                if(!txt){ return; }
                $magxUserInput.val(String(txt));
                magxSend();
            });

            // Restore any previous chat session.
            magxRestoreChat();


            
            function hideLoaderAfter3Sec() {
                setTimeout(function(){
                    $(".loader-overlay").css("display", "none");
                }, 3000); // 3000ms = 3 seconds
            }

            // Make post cards compatible with portrait/landscape media (images and videos).
            function initHomePostMediaFits(){
                function applyClass(container, w, h){
                    if(!container){ return; }
                    container.classList.remove("is-portrait", "is-landscape", "is-square");
                    w = Number(w) || 0;
                    h = Number(h) || 0;
                    if(w <= 0 || h <= 0){ return; }
                    const r = h / w;
                    if(Math.abs(r - 1) < 0.08){
                        container.classList.add("is-square");
                    } else if(r > 1.15){
                        container.classList.add("is-portrait");
                    } else {
                        container.classList.add("is-landscape");
                    }
                }

                document.querySelectorAll(".product-card .product-image img").forEach(img => {
                    const run = () => applyClass(img.closest(".product-image"), img.naturalWidth, img.naturalHeight);
                    if(img.complete){ run(); }
                    else { img.addEventListener("load", run, { once: true }); }
                });

                document.querySelectorAll(".product-card .product-image video").forEach(v => {
                    const run = () => applyClass(v.closest(".product-image"), v.videoWidth, v.videoHeight);
                    if(v.readyState >= 1){ run(); }
                    else { v.addEventListener("loadedmetadata", run, { once: true }); }
                });
            }
            initHomePostMediaFits();

            // Autoplay post-card videos when they scroll into view.
            // Note: browsers typically block autoplay WITH sound until the user interacts.
            function initHomePostVideoAutoplay(){
                const root = document.getElementById("bodycontainer") || null;
                const videos = Array.from(document.querySelectorAll("video.post-card-video"));
                if(!videos.length || typeof IntersectionObserver === "undefined"){ return; }

                let userInteracted = false;
                const markInteracted = () => { userInteracted = true; };
                window.addEventListener("pointerdown", markInteracted, { once: true, passive: true });
                window.addEventListener("keydown", markInteracted, { once: true });

                function ensureSoundButton(v){
                    const wrap = v.closest(".product-image");
                    if(!wrap){ return null; }
                    let btn = wrap.querySelector(".video-sound-toggle");
                    if(btn){ return btn; }
                    btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "video-sound-toggle";
                    btn.innerHTML = '<i class="fas fa-volume-up" aria-hidden="true"></i><span>Enable sound</span>';
                    btn.addEventListener("click", async () => {
                        userInteracted = true;
                        try{
                            v.muted = false;
                            v.volume = 1;
                            await v.play();
                            btn.style.display = "none";
                        } catch(e) {
                            // If it still fails, leave the button visible.
                        }
                    });
                    wrap.appendChild(btn);
                    return btn;
                }

                async function tryPlay(v){
                    // Pause other visible videos to avoid multiple playing with audio.
                    videos.forEach(o => { if(o !== v){ try{ o.pause(); } catch(e){} } });

                    // Start muted to maximize autoplay success; if user interacted, try with sound.
                    if(userInteracted){
                        v.muted = false;
                        v.volume = 1;
                    } else {
                        v.muted = true;
                    }

                    try{
                        await v.play();
                        if(v.muted){
                            // Offer a one-click sound enable.
                            const btn = ensureSoundButton(v);
                            if(btn){ btn.style.display = ""; }
                        } else {
                            const wrap = v.closest(".product-image");
                            const btn = wrap ? wrap.querySelector(".video-sound-toggle") : null;
                            if(btn){ btn.style.display = "none"; }
                        }
                    } catch(e){
                        // Autoplay with sound failed; fall back to muted autoplay.
                        try{
                            v.muted = true;
                            await v.play();
                            const btn = ensureSoundButton(v);
                            if(btn){ btn.style.display = ""; }
                        } catch(e2) {}
                    }
                }

                function pauseVideo(v){
                    try{ v.pause(); } catch(e){}
                }

                const obs = new IntersectionObserver((entries) => {
                    entries.forEach(ent => {
                        const v = ent.target;
                        if(ent.isIntersecting && ent.intersectionRatio >= 0.65){
                            tryPlay(v);
                        } else {
                            pauseVideo(v);
                        }
                    });
                }, { root: root, threshold: [0, 0.25, 0.65, 0.9] });

                videos.forEach(v => {
                    // Hint to browser we intend to autoplay.
                    v.playsInline = true;
                    v.preload = "metadata";
                    obs.observe(v);
                });
            }
            initHomePostVideoAutoplay();

            const btn = document.querySelector('.sidenav');

            if (btn) {
                btn.addEventListener('mousemove', (e) => {
                    const rect = btn.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    btn.style.setProperty('--x', x + 'px');
                    btn.style.setProperty('--y', y + 'px');
                });
            }

            const servicesPanel = document.querySelector('.services-panel');
            if (servicesPanel) {
                const updatePanelGradient = (event) => {
                    const rect = servicesPanel.getBoundingClientRect();
                    const x = event.clientX - rect.left;
                    const y = event.clientY - rect.top;
                    servicesPanel.style.setProperty('--x', x + 'px');
                    servicesPanel.style.setProperty('--y', y + 'px');
                };
                servicesPanel.addEventListener('mousemove', updatePanelGradient);
                servicesPanel.addEventListener('mouseleave', () => {
                    servicesPanel.style.setProperty('--x', '50%');
                    servicesPanel.style.setProperty('--y', '50%');
                });
            }

            
	            // side nav
	            function sideNavOpenWidth(){
	                const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
	                const preferred = 360;
	                const safe = Math.max(280, Math.min(preferred, vw - 14));
	                return safe + "px";
	            }
	            function openSideNav(){
	                $("#mySidenav")
	                    .css("width", sideNavOpenWidth())
	                    .css("border-radius", "16px 0 0 16px")
	                    .addClass("is-open");
	                $("#sidenav").hide().attr("aria-expanded", "true");
	            }
	            function closeSideNav(){
	                $("#mySidenav")
	                    .css("width", "0")
	                    .removeClass("is-open");
	                $("#sidenav").show().attr("aria-expanded", "false");
	            }

	            // If we redirected back from a POST (admin login error), reopen the side-nav.
	            try {
	                const qs = new URLSearchParams(window.location.search);
	                if (qs.get("admin") === "1") {
	                    openSideNav();
	                    setTimeout(function(){ $("#admin-user").trigger("focus"); }, 150);
	                }
	            } catch(e) {}

	            $("#sidenav").click(function(){
	                openSideNav();
	            });

            $("#closeBtn").click(function(){
                closeSideNav();
            });
            $("#exit").click(function(e){
                e.preventDefault();
                closeSideNav();
            });

            // Function to check if user is locked out
            function checkLockoutStatus(username, loginType) {
                return $.ajax({
                    url: 'check_lockout.php',
                    method: 'POST',
                    data: {
                        username: username,
                        login_type: loginType
                    },
                    dataType: 'json'
                });
            }

        });
        </script>
    </body>
</html>

