<?php
/**
 * ContractPeer - Pricing Page
 */
require_once __DIR__ . '/includes/config.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing — ContractPeer</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <div class="nav-links">
            <?php if ($user): ?>
                <a href="/dashboard.php">Dashboard</a>
                <a href="#" data-action="logout">Sign Out</a>
            <?php else: ?>
                <a href="/login.php">Sign In</a>
                <a href="/register.php" class="nav-cta">Start Free Trial</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="pricing" style="padding-top: 60px;">
    <div class="container">
        <div class="section-title">
            <h2>Choose Your Plan</h2>
            <p>14-day free trial on every plan. No credit card required. Cancel anytime.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>Starter</h3>
                <div class="price">$49<span>/mo</span></div>
                <div class="desc">10 contracts per month</div>
                <ul>
                    <li>AI risk identification</li>
                    <li>Plain-language summaries</li>
                    <li>Clause search</li>
                    <li>PDF/DOCX upload</li>
                    <li>Analysis history</li>
                </ul>
                <?php if ($user && $user['plan'] === 'starter'): ?>
                    <p style="text-align: center; color: var(--success); font-weight: 600;">Current Plan</p>
                <?php elseif ($user): ?>
                    <button class="btn btn-secondary" style="width: 100%;" data-plan="starter">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-secondary" style="width: 100%;">Start Free Trial</a>
                <?php endif; ?>
            </div>
            <div class="price-card popular">
                <div class="badge">Most Popular</div>
                <h3>Professional</h3>
                <div class="price">$99<span>/mo</span></div>
                <div class="desc">50 contracts per month</div>
                <ul>
                    <li>Everything in Starter</li>
                    <li>Version comparison</li>
                    <li>Contract playbooks</li>
                    <li>Redlined edit suggestions</li>
                    <li>Multi-party contracts</li>
                </ul>
                <?php if ($user && $user['plan'] === 'professional'): ?>
                    <p style="text-align: center; color: var(--success); font-weight: 600;">Current Plan</p>
                <?php elseif ($user): ?>
                    <button class="btn btn-primary" style="width: 100%;" data-plan="professional">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-primary" style="width: 100%;">Start Free Trial</a>
                <?php endif; ?>
            </div>
            <div class="price-card">
                <h3>Firm</h3>
                <div class="price">$199<span>/mo</span></div>
                <div class="desc">200 contracts per month</div>
                <ul>
                    <li>Everything in Professional</li>
                    <li>Team seats (up to 5)</li>
                    <li>Shared playbooks</li>
                    <li>Analytics dashboard</li>
                    <li>Priority support</li>
                </ul>
                <?php if ($user && $user['plan'] === 'firm'): ?>
                    <p style="text-align: center; color: var(--success); font-weight: 600;">Current Plan</p>
                <?php elseif ($user): ?>
                    <button class="btn btn-secondary" style="width: 100%;" data-plan="firm">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-secondary" style="width: 100%;">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($checkout === 'cancelled'): ?>
            <p style="text-align: center; margin-top: 24px; color: var(--gray-500);">Checkout was cancelled. You can try again anytime.</p>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 32px; color: var(--gray-500);">
            All plans include a 14-day free trial with 3 free contracts. No credit card required to start.
            Sales tax/VAT calculated automatically at checkout where applicable.
        </p>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>ContractPeer</h3>
                <p>AI-powered contract review built for solo and small-firm attorneys.</p>
            </div>
            <div class="footer-links">
                <h4>Product</h4>
                <a href="/#features">Features</a>
                <a href="/pricing.php">Pricing</a>
            </div>
            <div class="footer-links">
                <h4>Legal</h4>
                <a href="/legal/terms.php">Terms of Service</a>
                <a href="/legal/privacy.php">Privacy Policy</a>
                <a href="/legal/refund.php">Refund Policy</a>
            </div>
            <div class="footer-links">
                <h4>Account</h4>
                <a href="/login.php">Sign In</a>
                <a href="/register.php">Sign Up</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 Contract Peer (contractpeer.com). Operated by Contract Peer.</p>
            <p style="margin-top: 8px; font-size: 0.8rem; opacity: 0.6;">Legal documents were generated for this business and have not been reviewed by an attorney.</p>
        </div>
    </div>
</footer>

<script src="/assets/js/app.js"></script>
</body>
</html>
