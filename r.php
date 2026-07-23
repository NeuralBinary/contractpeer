<?php
/**
 * ContractPeer - Referral redirect handler
 * /r.php?ref=CODE -> stores cookie -> redirects to register
 */
require_once __DIR__ . '/includes/config.php';

$ref = $_GET['ref'] ?? '';
if ($ref) {
    // Validate the referral code exists
    $stmt = db()->prepare('SELECT id FROM users WHERE referral_code = ?');
    $stmt->execute([$ref]);
    $referrer = $stmt->fetch();
    
    if ($referrer) {
        // Store referral code in cookie (30 days)
        setcookie('cp_ref', $ref, time() + (30 * 86400), '/', '', true, true);
    }
}

// Redirect to registration page
header('Location: /register.php');
exit;
