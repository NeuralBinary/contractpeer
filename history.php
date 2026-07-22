<?php
/**
 * ContractPeer - Analysis History
 */
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <div class="nav-links">
            <a href="/dashboard.php">Dashboard</a>
            <a href="#" data-action="logout">Sign Out</a>
        </div>
    </div>
</nav>

<div class="dash-header">
    <div class="container">
        <h1>Analysis History</h1>
    </div>
</div>

<div class="container">
    <div class="dash-layout">
        <div class="dash-sidebar">
            <a href="/dashboard.php">New Analysis</a>
            <a href="/history.php" class="active">History</a>
            <a href="/pricing.php">Upgrade Plan</a>
            <a href="/account.php">Account & Data</a>
            <a href="#" data-action="logout">Sign Out</a>
        </div>
        <div class="dash-main">
            <div id="history-list">
                <div class="loading"><div class="spinner"></div><p>Loading...</p></div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
