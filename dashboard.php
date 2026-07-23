<?php
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
$checkout = $_GET['checkout'] ?? '';
$page_title = 'Dashboard';
require __DIR__ . '/templates/header.php';
$is_logged_in = true;
?>
<div class="bg-light py-4 border-bottom">
    <div class="container">
        <h1 class="h4 mb-1">Welcome, <?= htmlspecialchars($user['name'] ?: explode('@', $user['email'])[0]) ?></h1>
        <p class="text-muted mb-0 small">
            Plan: <strong><?= ucfirst($user['plan']) ?></strong> •
            Contracts used: <strong><?= $user['contracts_used'] ?>/<?= $user['contracts_limit'] ?></strong>
            <?php if ($user['plan'] === 'free' && $user['trial_ends_at']): ?>
                • Trial ends: <strong><?= date('M j, Y', strtotime($user['trial_ends_at'])) ?></strong>
            <?php endif; ?>
        </p>
        <?php if ($checkout === 'success'): ?>
            <div class="alert alert-success mt-2 mb-0 py-2">✅ Payment successful! Your subscription is now active.</div>
        <?php endif; ?>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="list-group">
                <a href="/dashboard.php" class="list-group-item list-group-item-action active">New Analysis</a>
                <a href="/history.php" class="list-group-item list-group-item-action">History</a>
                <a href="/pricing.php" class="list-group-item list-group-item-action">Upgrade Plan</a>
                <?php if ($user['stripe_customer_id']): ?>
                <a href="#" class="list-group-item list-group-item-action" data-action="portal">Manage Billing</a>
                <?php endif; ?>
                <a href="/account.php" class="list-group-item list-group-item-action">Account & Data</a>
                <a href="#" class="list-group-item list-group-item-action" data-action="logout">Sign Out</a>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="h5">Analyze a New Contract</h3>
                    <p class="text-muted">Upload a contract (PDF, DOCX, or TXT) to get instant AI-powered risk analysis.</p>
                    
                    <form id="analyze-form">
                        <div class="border border-2 border-dashed rounded p-5 text-center" id="upload-zone" style="cursor:pointer;border-color:#d1d5db !important;">
                            <div style="font-size:3rem;">📁</div>
                            <p class="mb-1"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted small">PDF, DOCX, or TXT — up to 10MB</p>
                            <p class="filename fw-semibold text-primary mb-0"></p>
                            <input type="file" id="file-input" accept=".pdf,.docx,.doc,.txt" class="d-none">
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label small">Focus Areas (optional)</label>
                                <input type="text" class="form-control form-control-sm" id="focus-areas" placeholder="e.g., liability, termination, IP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Review Perspective (optional)</label>
                                <select class="form-select form-select-sm" id="perspective">
                                    <option value="">— Select —</option>
                                    <option value="receiving">Receiving party</option>
                                    <option value="providing">Providing party</option>
                                    <option value="neutral">Neutral / both</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-cp mt-3" id="analyze-btn">Analyze Contract</button>
                    </form>
                    
                    <div id="results" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
