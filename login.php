<?php
/**
 * ContractPeer - Login Page
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
    <title>Sign In — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <div class="nav-links">
            <a href="/register.php">Sign Up</a>
        </div>
    </div>
</nav>
<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your ContractPeer account</p>
        <form class="auth-form" id="login-form">
            <div class="auth-error"></div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="you@lawfirm.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Your password">
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
        <p class="auth-switch">Don't have an account? <a href="/register.php">Sign up free</a></p>
    </div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
