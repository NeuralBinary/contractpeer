<?php
/**
 * ContractPeer - Shared Footer Template
 */
?>
<footer class="footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <h3 class="h5">ContractPeer</h3>
                <p>AI-powered contract review built for solo and small-firm attorneys. Enterprise-grade analysis without enterprise pricing.</p>
            </div>
            <div class="col-6 col-md-2">
                <h4 class="h6 text-uppercase">Product</h4>
                <div class="d-flex flex-column gap-2">
                    <a href="/#features">Features</a>
                    <a href="/free-nda-check.php">Free NDA Check</a>
                    <a href="/pricing.php">Pricing</a>
                    <a href="/blog/">Blog</a>
                    <a href="/contact.php">Contact</a>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <h4 class="h6 text-uppercase">Legal</h4>
                <div class="d-flex flex-column gap-2">
                    <a href="/legal/terms.php">Terms of Service</a>
                    <a href="/legal/privacy.php">Privacy Policy</a>
                    <a href="/legal/refund.php">Refund Policy</a>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <h4 class="h6 text-uppercase">Account</h4>
                <div class="d-flex flex-column gap-2">
                    <a href="/login.php">Sign In</a>
                    <a href="/register.php">Sign Up</a>
                </div>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="text-center small">
            <p>&copy; 2026 Contract Peer (contractpeer.com). Operated by Contract Peer.</p>
            <p class="text-muted small">Legal documents were generated for this business and have not been reviewed by an attorney.</p>
        </div>
    </div>
</footer>
<!-- Tracking -->
<img src="/api/track.php" alt="" width="1" height="1" style="display:none;">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/chat.js"></script>
<?= isset($extra_scripts) ? $extra_scripts : '' ?>
</body>
</html>
