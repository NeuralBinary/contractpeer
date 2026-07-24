<?php
/**
 * ContractPeer - Lightweight Page View Tracking
 * Anonymous, no cookies, no personal data. Just counts and paths.
 */
require_once __DIR__ . '/../includes/config.php';

$page = $_SERVER['HTTP_REFERER'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ip_hash = substr(md5($ip . 'cp-salt-2026'), 0, 16);

// Generate or reuse session ID from cookie
$session_id = $_COOKIE['cp_visitor'] ?? bin2hex(random_bytes(8));
if (!isset($_COOKIE['cp_visitor'])) {
    setcookie('cp_visitor', $session_id, time() + 86400 * 30, '/', '', true, true);
}

// Only log if not a bot (check for common bot user agents)
$is_bot = preg_match('/bot|crawl|spider|scraper|curl|wget|python/i', $ua);
if (!$is_bot && $page) {
    $stmt = db()->prepare('INSERT INTO page_views (page, referrer, user_agent, ip_hash, session_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([substr($page, 0, 500), substr($referrer, 0, 500), $ua, $ip_hash, $session_id]);
}

// Return 1x1 transparent GIF
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
