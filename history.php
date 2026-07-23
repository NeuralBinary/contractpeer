<?php
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
$page_title = 'History';
require __DIR__ . '/templates/header.php';
?>
<div class="bg-light py-4 border-bottom">
    <div class="container"><h1 class="h4 mb-0">Analysis History</h1></div>
</div>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="list-group">
                <a href="/dashboard.php" class="list-group-item list-group-item-action">New Analysis</a>
                <a href="/history.php" class="list-group-item list-group-item-action active">History</a>
                <a href="/pricing.php" class="list-group-item list-group-item-action">Upgrade Plan</a>
                <a href="/account.php" class="list-group-item list-group-item-action">Account & Data</a>
                <a href="#" class="list-group-item list-group-item-action" data-action="logout">Sign Out</a>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm"><div class="card-body">
                <div id="history-list"><div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading...</p></div></div>
            </div></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
