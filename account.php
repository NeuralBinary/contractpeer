<?php
/**
 * ContractPeer - Account & Data Management (GDPR/CCPA)
 */
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account & Data — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <button class="mobile-menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">☰</button>
        <div class="nav-links">
            <a href="/dashboard.php">Dashboard</a>
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
        <h1>Account & Data Management</h1>
    </div>
</div>

<div class="container">
    <div class="dash-layout">
        <div class="dash-sidebar">
            <a href="/dashboard.php">New Analysis</a>
            <a href="/history.php">History</a>
            <a href="/pricing.php">Upgrade Plan</a>
            <a href="/account.php" class="active">Account & Data</a>
            <a href="#" data-action="logout">Sign Out</a>
        </div>
        <div class="dash-main">
            <h3>Your Data Rights</h3>
            <p style="color: var(--gray-500); margin-bottom: 24px;">
                Under GDPR (EU/UK) and CCPA (California), you have the right to access and delete your personal data.
                Use the options below to export or delete your data.
            </p>

            <div class="risk-card low" style="margin-bottom: 16px;">
                <h4>Export Your Data</h4>
                <p>Download a copy of all your account data, including analysis history and payment records.</p>
                <button class="btn btn-secondary" style="margin-top: 12px;" onclick="exportData()">Export My Data</button>
            </div>

            <div class="risk-card high">
                <h4>Delete Your Account</h4>
                <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                <button class="btn btn-primary" style="margin-top: 12px; background: var(--danger);" onclick="deleteAccount()">Delete My Account</button>
            </div>

            <div style="margin-top: 32px;">
                <h4>Account Information</h4>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Plan:</strong> <?= ucfirst($user['plan']) ?></p>
                <p><strong>Member since:</strong> <?= date('M j, Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<script>
async function exportData() {
    if (!confirm('Export all your data? This will generate a download.')) return;
    const result = await fetch('/api/dsr.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({request_type: 'access'}) });
    const data = await result.json();
    const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'contractpeer-data-export.json'; a.click();
    URL.revokeObjectURL(url);
}

async function deleteAccount() {
    if (!confirm('Are you absolutely sure? This will permanently delete your account and all data. This cannot be undone.')) return;
    if (!confirm('Last confirmation: Type DELETE in the next prompt to proceed.')) return;
    const text = prompt('Type DELETE to confirm:');
    if (text !== 'DELETE') { alert('Deletion cancelled.'); return; }
    const result = await fetch('/api/dsr.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({request_type: 'delete'}) });
    const data = await result.json();
    if (data.success) { alert('Your account has been deleted.'); window.location.href = '/'; }
    else { alert('Error: ' + (data.error || 'Unknown error')); }
}
</script>
</body>
</html>
