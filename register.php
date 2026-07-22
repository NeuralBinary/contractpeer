<?php
/**
 * ContractPeer - Register Page
 */
require_once __DIR__ . '/includes/config.php';
$user = current_user();
if ($user) { header('Location: /dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <button class="mobile-menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">☰</button>
        <div class="nav-links">
            <a href="/login.php">Sign In</a>
        </div>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <a href="/#features">Features</a>
    <a href="/free-nda-check.php">Free NDA Check</a>
    <a href="/pricing.php">Pricing</a>
    <a href="/blog/">Blog</a>
    <a href="/login.php">Sign In</a>
    <a href="/register.php" class="nav-cta">Start Free Trial</a>
</div>
<div class="auth-page">
    <div class="auth-card">
        <h2>Create Your Account</h2>
        <p class="subtitle">14-day free trial • 3 free contracts • No credit card</p>
        <form class="auth-form" id="register-form">
            <div class="auth-error"></div>
            <div class="form-group">
                <label>Name (optional)</label>
                <input type="text" name="name" placeholder="Your name">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="you@lawfirm.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="8" placeholder="At least 8 characters">
            </div>
            <button type="submit" class="btn btn-primary">Create Free Account</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="/login.php">Sign in</a></p>
        <p style="text-align: center; margin-top: 16px; font-size: 0.8rem; color: var(--gray-400);">
            By signing up, you agree to our <a href="/legal/terms.php">Terms</a> and <a href="/legal/privacy.php">Privacy Policy</a>.
        </p>
    </div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
