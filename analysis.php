<?php
require_once __DIR__ . '/includes/config.php';
$user = require_auth();
$page_title = 'Analysis';
require __DIR__ . '/templates/header.php';
?>
<div class="bg-light py-4 border-bottom">
    <div class="container"><h1 class="h4 mb-0">Contract Analysis</h1></div>
</div>
<div class="container py-4">
    <div class="card shadow-sm" style="max-width:900px;margin:0 auto;">
        <div class="card-body">
            <div id="analysis-detail"><div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Loading analysis...</p></div></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
