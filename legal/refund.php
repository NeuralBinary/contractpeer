<?php
/**
 * ContractPeer - Refund Policy
 * Consistent with actual refund thresholds configured in the system.
 */
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Policy — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .legal-doc { max-width: 800px; margin: 0 auto; padding: 60px 24px; line-height: 1.7; }
        .legal-doc h1 { margin-bottom: 8px; }
        .legal-doc .updated { color: var(--gray-400); margin-bottom: 32px; }
        .legal-doc h2 { margin-top: 32px; margin-bottom: 8px; }
        .legal-doc p { margin-bottom: 12px; color: var(--gray-700); }
        .legal-doc ul { margin-bottom: 12px; padding-left: 24px; }
        .legal-doc li { margin-bottom: 6px; color: var(--gray-700); }
    </style>
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <div class="nav-links">
            <a href="/login.php">Sign In</a>
            <a href="/register.php" class="nav-cta">Start Free Trial</a>
        </div>
    </div>
</nav>

<div class="legal-doc">
<h1>Refund Policy</h1>
<p class="updated">Last updated: July 22, 2026</p>

<p>We want you to be satisfied with ContractPeer. This policy explains our refund practices and your options.</p>

<h2>1. Free Trial</h2>
<p>Every new account includes a 14-day free trial with 3 contract analyses. No credit card is required to start a trial. Since no payment is collected during the trial, no refund is needed — simply let the trial expire or delete your account.</p>

<h2>2. Monthly Subscriptions</h2>
<p>ContractPeer is billed monthly in advance. Our refund policy for paid subscriptions is:</p>
<ul>
    <li><strong>First payment:</strong> If you are not satisfied within 14 days of your first payment, contact us for a full refund of your first month's payment.</li>
    <li><strong>Subsequent payments:</strong> Monthly subscriptions are non-refundable after the first month. You can cancel at any time to prevent future charges, and you will retain access until the end of your current billing period.</li>
    <li><strong>Cancellation:</strong> Cancel anytime from the Account page or via the Stripe customer portal. Cancellation takes effect at the end of your current billing period.</li>
</ul>

<h2>3. Automated Refund Processing</h2>
<p>For your convenience, refund requests can be processed automatically when they meet our criteria:</p>
<ul>
    <li>Per-transaction refund limit: $<?= REFUND_THRESHOLD_PER_TRANSACTION ?> (based on 2x our blended average revenue per user of $94, hard-capped)</li>
    <li>Daily cumulative refund limit: $<?= REFUND_THRESHOLD_DAILY_CUMULATIVE ?> (3x the per-transaction limit)</li>
</ul>
<p>Refunds within these limits may be processed automatically. Refunds exceeding these limits, or unusual refund patterns, require manual review and may be escalated for approval.</p>

<h2>4. How to Request a Refund</h2>
<p>To request a refund, you can:</p>
<ul>
    <li>Email <?= htmlspecialchars(SUPPORT_EMAIL) ?> with your account email and reason for the refund request</li>
    <li>Use the "Manage Billing" link in your dashboard to access the Stripe customer portal</li>
</ul>
<p>Refund requests are typically processed within 2-3 business days. Approved refunds are returned to your original payment method.</li>

<h2>5. Plan Changes</h2>
<p>If you upgrade or downgrade your plan mid-cycle:</p>
<ul>
    <li><strong>Upgrades:</strong> The prorated difference is charged immediately. The new plan's features are available right away.</li>
    <li><strong>Downgrades:</strong> The new plan takes effect at the next billing cycle. You retain the higher plan's features until then. No refund is issued for the current period.</li>
</ul>

<h2>6. Service Interruptions</h2>
<p>If the Service experiences a significant outage (more than 24 hours of downtime in a billing period), contact us for a prorated credit toward your next billing cycle.</p>

<h2>7. Chargebacks</h2>
<p>We prefer to resolve issues directly. If you have a billing concern, please contact us at <?= htmlspecialchars(SUPPORT_EMAIL) ?> before initiating a chargeback. We will work to resolve the issue promptly. Unwarranted chargebacks may result in account suspension.</p>

<h2>8. Contact</h2>
<p>For refund questions, contact us at <?= htmlspecialchars(SUPPORT_EMAIL) ?>.</p>

<p style="margin-top: 48px; font-size: 0.85rem; color: var(--gray-400); font-style: italic;">
    This Refund Policy was generated for ContractPeer and has not been reviewed by an attorney.
</p>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
