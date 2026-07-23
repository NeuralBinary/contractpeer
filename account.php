<?php
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
$page_title = 'Account & Data';
require __DIR__ . '/templates/header.php';
?>
<div class="bg-light py-4 border-bottom">
    <div class="container"><h1 class="h4 mb-0">Account & Data Management</h1></div>
</div>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="list-group">
                <a href="/dashboard.php" class="list-group-item list-group-item-action">New Analysis</a>
                <a href="/history.php" class="list-group-item list-group-item-action">History</a>
                <a href="/pricing.php" class="list-group-item list-group-item-action">Upgrade Plan</a>
                <a href="/account.php" class="list-group-item list-group-item-action active">Account & Data</a>
                <a href="#" class="list-group-item list-group-item-action" data-action="logout">Sign Out</a>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm"><div class="card-body">
                <h3 class="h5">Your Data Rights</h3>
                <p class="text-muted">Under GDPR (EU/UK) and CCPA (California), you have the right to access and delete your personal data.</p>

                <div class="card border-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-success">Export Your Data</h5>
                        <p class="card-text">Download a copy of all your account data, analysis history, and payment records.</p>
                        <button class="btn btn-outline-primary" onclick="exportData()">Export My Data</button>
                    </div>
                </div>

                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Delete Your Account</h5>
                        <p class="card-text">Permanently delete your account and all associated data. This cannot be undone.</p>
                        <button class="btn btn-danger" onclick="deleteAccount()">Delete My Account</button>
                    </div>
                </div>

                <hr class="my-4">
                <h4 class="h6">Account Information</h4>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Plan:</strong> <?= ucfirst($user['plan']) ?></p>
                <p><strong>Member since:</strong> <?= date('M j, Y', strtotime($user['created_at'])) ?></p>
            </div></div>
        </div>
    </div>
</div>
<script>
async function exportData() {
    if (!confirm('Export all your data?')) return;
    const r = await fetch('/api/dsr.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({request_type:'access'})});
    const d = await r.json();
    const blob = new Blob([JSON.stringify(d,null,2)],{type:'application/json'});
    const a = document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='contractpeer-data-export.json'; a.click();
}
async function deleteAccount() {
    if (!confirm('Permanently delete your account? This cannot be undone.')) return;
    if (prompt('Type DELETE to confirm:') !== 'DELETE') { alert('Cancelled.'); return; }
    const r = await fetch('/api/dsr.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({request_type:'delete'})});
    const d = await r.json();
    if (d.success) { alert('Account deleted.'); location.href='/'; }
    else { alert('Error: '+(d.error||'Unknown')); }
}
</script>
<?php require __DIR__ . '/templates/footer.php'; ?>
