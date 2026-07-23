<?php
require_once __DIR__ . '/includes/config.php';
$user = current_user();
$checkout = $_GET['checkout'] ?? '';
$page_title = 'Pricing';
$page_description = 'Simple, transparent pricing for AI contract review. Starting at $49/month. 14-day free trial, no credit card required.';
require __DIR__ . '/templates/header.php';
?>
<section class="container py-5" style="max-width:900px;">
    <?php if ($checkout === 'cancelled'): ?>
        <div class="alert alert-info">Checkout was cancelled. You can try again anytime.</div>
    <?php endif; ?>
    
    <div class="text-center mb-5">
        <h1 class="fw-bold">Choose Your Plan</h1>
        <p class="text-muted">14-day free trial on every plan. No credit card required. Cancel anytime.</p>
    </div>
    
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center p-4">
                <h5 class="fw-bold">Starter</h5>
                <div class="display-6 fw-bold">$49<span class="fs-6 text-muted">/mo</span></div>
                <p class="text-muted">10 contracts per month</p>
                <ul class="list-unstyled text-start">
                    <li class="mb-2">✓ AI risk identification</li>
                    <li class="mb-2">✓ Plain-language summaries</li>
                    <li class="mb-2">✓ Clause search</li>
                    <li class="mb-2">✓ PDF/DOCX upload</li>
                    <li class="mb-2">✓ Analysis history</li>
                </ul>
                <?php if ($user && $user['plan'] === 'starter'): ?>
                    <span class="badge bg-success w-100 py-2">Current Plan</span>
                <?php elseif ($user): ?>
                    <button class="btn btn-outline-primary w-100 mt-3" data-plan="starter">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-outline-primary w-100 mt-3">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-primary border-2 shadow text-center p-4 position-relative">
                <span class="badge bg-primary position-absolute top-0 start-50 translate-middle-x">Most Popular</span>
                <h5 class="fw-bold">Professional</h5>
                <div class="display-6 fw-bold">$99<span class="fs-6 text-muted">/mo</span></div>
                <p class="text-muted">50 contracts per month</p>
                <ul class="list-unstyled text-start">
                    <li class="mb-2">✓ Everything in Starter</li>
                    <li class="mb-2">✓ Version comparison</li>
                    <li class="mb-2">✓ Contract playbooks</li>
                    <li class="mb-2">✓ Redlined edit suggestions</li>
                    <li class="mb-2">✓ Multi-party contracts</li>
                </ul>
                <?php if ($user && $user['plan'] === 'professional'): ?>
                    <span class="badge bg-success w-100 py-2">Current Plan</span>
                <?php elseif ($user): ?>
                    <button class="btn btn-cp w-100 mt-3" data-plan="professional">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-cp w-100 mt-3">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center p-4">
                <h5 class="fw-bold">Firm</h5>
                <div class="display-6 fw-bold">$199<span class="fs-6 text-muted">/mo</span></div>
                <p class="text-muted">200 contracts per month</p>
                <ul class="list-unstyled text-start">
                    <li class="mb-2">✓ Everything in Professional</li>
                    <li class="mb-2">✓ Team seats (up to 5)</li>
                    <li class="mb-2">✓ Shared playbooks</li>
                    <li class="mb-2">✓ Analytics dashboard</li>
                    <li class="mb-2">✓ Priority support</li>
                </ul>
                <?php if ($user && $user['plan'] === 'firm'): ?>
                    <span class="badge bg-success w-100 py-2">Current Plan</span>
                <?php elseif ($user): ?>
                    <button class="btn btn-outline-primary w-100 mt-3" data-plan="firm">Subscribe</button>
                <?php else: ?>
                    <a href="/register.php" class="btn btn-outline-primary w-100 mt-3">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <p class="text-center mt-4 text-muted">All plans include a 14-day free trial with 3 free contracts. No credit card required to start. Sales tax/VAT calculated automatically at checkout.</p>
</section>
<?php require __DIR__ . '/templates/footer.php'; ?>
