<?php
/**
 * ContractPeer - Shared Header Template
 * All pages include this for consistent navigation and styling.
 */
require_once __DIR__ . '/../includes/config.php';
$is_logged_in = isset($is_logged_in) ? $is_logged_in : (current_user() !== null);
$user = $is_logged_in ? current_user() : null;
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>ContractPeer</title>
    <?php if (isset($page_description)): ?>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= isset($page_title) ? htmlspecialchars($page_title) : 'ContractPeer' ?>">
    <meta property="og:description" content="<?= isset($page_description) ? htmlspecialchars($page_description) : 'AI-powered contract review for solo and small-firm attorneys.' ?>">
    <meta property="og:url" content="https://contractpeer.com<?= $_SERVER['REQUEST_URI'] ?? '' ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --cp-primary: #2563eb; --cp-accent: #7c3aed; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
        .navbar { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #e5e7eb; }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #111827; text-decoration: none; }
        .navbar-brand span { color: var(--cp-primary); }
        .btn-cp { background: var(--cp-primary); border-color: var(--cp-primary); color: white; font-weight: 600; }
        .btn-cp:hover { background: #1d4ed8; border-color: #1d4ed8; color: white; }
        .btn-accent { background: var(--cp-accent); border-color: var(--cp-accent); color: white; font-weight: 600; }
        .btn-accent:hover { background: #6d28d9; border-color: #6d28d9; color: white; }
        .footer { background: #111827; color: #9ca3af; padding: 48px 0 24px; margin-top: 80px; }
        .footer a { color: #9ca3af; text-decoration: none; font-size: 0.9rem; }
        .footer a:hover { color: white; }
        .footer h3, .footer h4 { color: white; }
    </style>
    <?= isset($extra_head) ? $extra_head : '' ?>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top navbar-light">
    <div class="container">
        <a class="navbar-brand" href="/">Contract<span>Peer</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if ($is_logged_in && $user): ?>
                    <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/free-nda-check.php">Free NDA Check</a></li>
                    <li class="nav-item"><a class="nav-link" href="/history.php">History</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pricing.php">Upgrade</a></li>
                    <li class="nav-item"><a class="nav-link" href="/account.php">Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-action="logout">Sign Out</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="/free-nda-check.php">Free NDA Check</a></li>
                    <li class="nav-item"><a class="nav-link" href="/pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="/blog/">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login.php">Sign In</a></li>
                    <li class="nav-item"><a class="btn btn-cp px-3" href="/register.php">Start Free Trial</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
