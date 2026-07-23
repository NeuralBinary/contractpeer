<?php
/**
 * ContractPeer - Authentication
 */

function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

function current_user() {
    start_session();
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        unset($user['password_hash']);
    }
    return $user;
}

function require_auth() {
    $user = current_user();
    if (!$user) {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            json_response(['error' => 'Authentication required'], 401);
        }
        header('Location: /#login');
        exit;
    }
    return $user;
}

function require_subscription() {
    $user = require_auth();
    if ($user['plan'] === 'free') {
        $trial_end = $user['trial_ends_at'];
        if ($trial_end && strtotime($trial_end) < time()) {
            json_response(['error' => 'Free trial expired. Please upgrade to continue.', 'upgrade_required' => true], 403);
        }
        if ($user['contracts_used'] >= $user['contracts_limit']) {
            json_response(['error' => 'Contract limit reached. Please upgrade to continue.', 'upgrade_required' => true], 403);
        }
    }
    return $user;
}

function register_user($email, $password, $name = '') {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Invalid email address'];
    }
    if (strlen($password) < 8) {
        return ['error' => 'Password must be at least 8 characters'];
    }
    
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['error' => 'An account with this email already exists'];
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $trial_end = date('Y-m-d H:i:s', time() + (14 * 86400)); // 14-day trial
    
    $stmt = db()->prepare('INSERT INTO users (email, password_hash, name, plan, trial_ends_at, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
    
    // Generate unique referral code
    $ref_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    $referred_by = null;
    
    // Check for referral cookie
    start_session();
    if (isset($_COOKIE['cp_ref'])) {
        $stmt_check = db()->prepare('SELECT id FROM users WHERE referral_code = ?');
        $stmt_check->execute([$_COOKIE['cp_ref']]);
        $referrer = $stmt_check->fetch();
        if ($referrer) {
            $referred_by = $referrer['id'];
        }
    }
    
    $stmt->execute([$email, $hash, $name, 'free', $trial_end, $ref_code, $referred_by]);
    $user_id = db()->lastInsertId();
    
    // Record referral relationship
    if ($referred_by) {
        $stmt = db()->prepare('INSERT INTO referrals (referrer_user_id, referred_user_id, status) VALUES (?, ?, "pending")');
        $stmt->execute([$referred_by, $user_id]);
    }
    
    $_SESSION['user_id'] = $user_id;
    
    // Send welcome email (async - don't block registration)
    @send_welcome_email($email, $name);
    
    return ['success' => true, 'user_id' => $user_id];
}

function login_user($email, $password) {
    $email = strtolower(trim($email));
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['error' => 'Invalid email or password'];
    }
    
    start_session();
    $_SESSION['user_id'] = $user['id'];
    
    return ['success' => true];
}

function logout_user() {
    start_session();
    session_destroy();
    return ['success' => true];
}
