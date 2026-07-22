<?php
/**
 * ContractPeer - Dashboard
 */
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
$checkout = $_GET['checkout'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <button class="mobile-menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">☰</button>
        <div class="nav-links">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/pricing.php">Pricing</a>
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
        <h1>Welcome, <?= htmlspecialchars($user['name'] ?: explode('@', $user['email'])[0]) ?></h1>
        <p style="color: var(--gray-500);">
            Plan: <strong><?= ucfirst($user['plan']) ?></strong> • 
            Contracts used: <strong><?= $user['contracts_used'] ?>/<?= $user['contracts_limit'] ?></strong>
            <?php if ($user['plan'] === 'free' && $user['trial_ends_at']): ?>
                • Trial ends: <strong><?= date('M j, Y', strtotime($user['trial_ends_at'])) ?></strong>
            <?php endif; ?>
        </p>
        <?php if ($checkout === 'success'): ?>
            <p style="color: var(--success); margin-top: 8px;">✅ Payment successful! Your subscription is now active.</p>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="dash-layout">
        <div class="dash-sidebar">
            <a href="/dashboard.php" class="active">New Analysis</a>
            <a href="/history.php">History</a>
            <a href="/pricing.php">Upgrade Plan</a>
            <?php if ($user['stripe_customer_id']): ?>
            <a href="#" data-action="portal">Manage Billing</a>
            <?php endif; ?>
            <a href="/account.php">Account & Data</a>
            <a href="#" data-action="logout">Sign Out</a>
        </div>
        <div class="dash-main">
            <h3>Analyze a New Contract</h3>
            <p style="color: var(--gray-500); margin-bottom: 24px;">Upload a contract (PDF, DOCX, or TXT) to get instant AI-powered risk analysis.</p>
            
            <form id="analyze-form">
                <div class="upload-zone" id="upload-zone">
                    <div class="icon">📁</div>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <p style="font-size: 0.85rem;">PDF, DOCX, or TXT — up to 10MB</p>
                    <p class="filename" style="font-weight: 600; color: var(--primary); margin-top: 8px;"></p>
                    <input type="file" id="file-input" accept=".pdf,.docx,.doc,.txt">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
                    <div>
                        <label style="font-weight: 500; font-size: 0.9rem; color: var(--gray-700);">Focus Areas (optional)</label>
                        <input type="text" id="focus-areas" placeholder="e.g., liability, termination, IP" style="width: 100%; padding: 10px; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); margin-top: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: 500; font-size: 0.9rem; color: var(--gray-700);">Review Perspective (optional)</label>
                        <select id="perspective" style="width: 100%; padding: 10px; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); margin-top: 4px;">
                            <option value="">— Select —</option>
                            <option value="receiving">Receiving party</option>
                            <option value="providing">Providing party</option>
                            <option value="neutral">Neutral / both</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="analyze-btn" style="margin-top: 16px;">Analyze Contract</button>
            </form>
            
            <div id="results" style="margin-top: 32px;"></div>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
