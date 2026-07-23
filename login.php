<?php
require_once __DIR__ . '/includes/config.php';
$user = current_user();
if ($user) { header('Location: /dashboard.php'); exit; }
$page_title = 'Sign In';
$page_description = 'Sign in to your ContractPeer account.';
require __DIR__ . '/templates/header.php';
?>
<div class="container" style="max-width: 440px; padding: 60px 0;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="text-center mb-1">Welcome Back</h2>
            <p class="text-center text-muted mb-4">Sign in to your ContractPeer account</p>
            <form id="login-form">
                <div class="alert alert-danger auth-error" style="display:none;"></div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required placeholder="you@lawfirm.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required placeholder="Your password">
                </div>
                <button type="submit" class="btn btn-cp w-100">Sign In</button>
            </form>
            <p class="text-center mt-3 mb-0 small text-muted">
                Don't have an account? <a href="/register.php">Sign up free</a>
            </p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/templates/footer.php'; ?>
