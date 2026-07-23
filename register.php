<?php
require_once __DIR__ . '/includes/config.php';
$user = current_user();
if ($user) { header('Location: /dashboard.php'); exit; }
$page_title = 'Sign Up';
$page_description = 'Create your free ContractPeer account. 14-day trial, 3 free contracts, no credit card required.';
require __DIR__ . '/templates/header.php';
?>
<div class="container" style="max-width: 440px; padding: 60px 0;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="text-center mb-1">Create Your Account</h2>
            <p class="text-center text-muted mb-4">14-day free trial • 3 free contracts • No credit card</p>
            <form id="register-form">
                <div class="alert alert-danger auth-error" style="display:none;"></div>
                <div class="mb-3">
                    <label class="form-label">Name (optional)</label>
                    <input type="text" class="form-control" name="name" placeholder="Your name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required placeholder="you@lawfirm.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required minlength="8" placeholder="At least 8 characters">
                </div>
                <button type="submit" class="btn btn-cp w-100">Create Free Account</button>
            </form>
            <p class="text-center mt-3 mb-0 small text-muted">
                Already have an account? <a href="/login.php">Sign in</a>
            </p>
            <p class="text-center mt-2 small text-muted">
                By signing up, you agree to our <a href="/legal/terms.php">Terms</a> and <a href="/legal/privacy.php">Privacy Policy</a>.
            </p>
        </div>
    </div>
</div>
<script src="/assets/js/app.js"></script>
<?php require __DIR__ . '/templates/footer.php'; ?>
