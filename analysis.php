<?php
/**
 * ContractPeer - Single Analysis View
 */
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <button class="mobile-menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">☰</button>
        <div class="nav-links">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/history.php">History</a>
            <a href="#" data-action="logout">Sign Out</a>
        </div>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <a href="/dashboard.php">Dashboard</a>
    <a href="/free-nda-check.php">Free NDA Check</a>
    <a href="/history.php">History</a>
    <a href="/pricing.php">Upgrade</a>
    <a href="/account.php">Account</a>
    <a href="#" data-action="logout" class="nav-cta">Sign Out</a>
</div>

<div class="dash-header">
    <div class="container">
        <h1>Contract Analysis</h1>
    </div>
</div>

<div class="container" style="padding: 32px 24px;">
    <div class="dash-main" style="max-width: 900px; margin: 0 auto;">
        <div id="analysis-detail">
            <div class="loading"><div class="spinner"></div><p>Loading analysis...</p></div>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
