<?php
/**
 * ContractPeer - Privacy Policy
 * Accurately describes actual data practices.
 */
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — ContractPeer</title>
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
        <button class="mobile-menu-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')">☰</button>
        <div class="nav-links">
            <a href="/login.php">Sign In</a>
            <a href="/register.php" class="nav-cta">Start Free Trial</a>
        </div>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <a href="/#features">Features</a>
    <a href="/free-nda-check.php">Free NDA Check</a>
    <a href="/pricing.php">Pricing</a>
    <a href="/blog/">Blog</a>
    <a href="/login.php">Sign In</a>
    <a href="/register.php" class="nav-cta">Start Free Trial</a>
</div>

<div class="legal-doc">
<h1>Privacy Policy</h1>
<p class="updated">Last updated: July 22, 2026</p>

<p>This Privacy Policy describes how Contract Peer ("we", "us") collects, uses, and protects your data when you use ContractPeer (contractpeer.com). We are committed to transparency about our data practices.</p>

<h2>1. Data We Collect</h2>

<h3>Account Data</h3>
<ul>
    <li><strong>Email address</strong> — used for login, notifications, and account management</li>
    <li><strong>Name</strong> (optional) — used to personalize your experience</li>
    <li><strong>Password</strong> — stored as a one-way hash (never stored in plaintext)</li>
</ul>

<h3>Contract Data</h3>
<ul>
    <li><strong>Uploaded contract files</strong> (PDF, DOCX, TXT) — processed to extract text, then the original file is deleted</li>
    <li><strong>Extracted contract text</strong> — sent to a third-party LLM API (OpenAI or Anthropic) for analysis, then stored truncated (first 50,000 characters) in your analysis history</li>
    <li><strong>Analysis results</strong> — stored in your account for your review history</li>
</ul>

<h3>Payment Data</h3>
<ul>
    <li><strong>Billing information</strong> — processed by Stripe. We never see or store your credit card number. Stripe provides us with your customer ID, subscription status, and payment amounts for record-keeping.</li>
    <li><strong>Billing email</strong> — used for receipts and subscription management</li>
</ul>

<h3>Usage Data</h3>
<ul>
    <li><strong>Number of contracts analyzed</strong> — tracked for usage limits and billing</li>
    <li><strong>Account creation date, plan, and subscription status</strong></li>
    <li><strong>IP address</strong> — logged for security purposes</li>
</ul>

<h2>2. How We Use Your Data</h2>
<ul>
    <li>To provide the contract analysis service</li>
    <li>To manage your account, subscription, and billing</li>
    <li>To send transactional emails (receipts, trial notifications, support responses)</li>
    <li>To maintain analysis history for your reference</li>
    <li>To monitor and improve the Service (using aggregate, de-identified metrics only)</li>
    <li>To prevent fraud and abuse</li>
</ul>

<h2>3. How We Do NOT Use Your Data</h2>
<ul>
    <li><strong>We do not use your contract data to train AI models.</strong> The LLM API we use (OpenAI/Anthropic) processes your contract text to generate analysis but does not retain it for training (per their API terms).</li>
    <li><strong>We do not sell your data</strong> to third parties.</li>
    <li><strong>We do not share your contract data</strong> with third parties except the LLM provider necessary to generate the analysis.</li>
    <li><strong>We do not use your data for marketing</strong> without your separate consent.</li>
</ul>

<h2>4. Third-Party Services</h2>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
    <tr style="border-bottom: 1px solid var(--gray-200);">
        <th style="text-align: left; padding: 8px;">Service</th>
        <th style="text-align: left; padding: 8px;">Purpose</th>
        <th style="text-align: left; padding: 8px;">Data Shared</th>
    </tr>
    <tr style="border-bottom: 1px solid var(--gray-200);">
        <td style="padding: 8px;">Stripe</td>
        <td style="padding: 8px;">Payment processing</td>
        <td style="padding: 8px;">Email, billing details (card data stays with Stripe)</td>
    </tr>
    <tr style="border-bottom: 1px solid var(--gray-200);">
        <td style="padding: 8px;">OpenAI / Anthropic</td>
        <td style="padding: 8px;">Contract analysis (LLM)</td>
        <td style="padding: 8px;">Extracted contract text for analysis</td>
    </tr>
    <tr style="border-bottom: 1px solid var(--gray-200);">
        <td style="padding: 8px;">Hosting Provider</td>
        <td style="padding: 8px;">Website hosting</td>
        <td style="padding: 8px;">Account data, analysis results (stored on server)</td>
    </tr>
</table>

<h2>5. Data Storage and Security</h2>
<ul>
    <li><strong>Storage:</strong> Your data is stored on our hosting provider's servers. Analysis results are stored in a database on the server. Uploaded contract files are deleted after text extraction.</li>
    <li><strong>Encryption:</strong> All data in transit is encrypted via TLS/HTTPS. Passwords are hashed using bcrypt.</li>
    <li><strong>Access control:</strong> Only you can access your analysis history. Admin access is restricted and logged.</li>
    <li><strong>Data retention:</strong> Your data is retained as long as your account is active. When you delete your account, all data (including analysis history and payment records) is permanently deleted within 30 days.</li>
</ul>

<h2>6. Your Rights (GDPR / CCPA)</h2>
<p>If you are in the EU, UK, or California, you have the right to:</p>
<ul>
    <li><strong>Access:</strong> Request a copy of all your personal data (available self-service from your Account page)</li>
    <li><strong>Deletion:</strong> Request deletion of all your personal data (available self-service from your Account page)</li>
    <li><strong>Rectification:</strong> Request correction of inaccurate data</li>
    <li><strong>Portability:</strong> Receive your data in a machine-readable format (JSON export available)</li>
    <li><strong>Objection:</strong> Object to certain processing of your data</li>
    <li><strong>Withdraw consent:</strong> Withdraw consent for any processing based on consent</li>
</ul>
<p>To exercise these rights, use the self-service tools on your Account page, or contact us at <?= htmlspecialchars(SUPPORT_EMAIL) ?>. We will respond within 30 days.</p>

<h2>7. Data Subject Request Handling</h2>
<p>When you submit a data access or deletion request through the Account page, the system:</p>
<ul>
    <li>For access requests: compiles your account data, analysis history, and payment records into a downloadable JSON file</li>
    <li>For deletion requests: permanently deletes your analyses, payment records, subscriptions, and account. This action is irreversible.</li>
    <li>All requests are logged with timestamp and action taken</li>
</ul>

<h2>8. Children's Privacy</h2>
<p>The Service is not directed to children under 18. We do not knowingly collect data from children.</p>

<h2>9. International Data Transfers</h2>
<p>Your data may be processed in countries other than your own, including the United States (hosting, Stripe) and potentially the LLM provider's infrastructure. We rely on standard contractual clauses and provider compliance programs for international transfers.</p>

<h2>10. Cookie Policy</h2>
<p>We use a single session cookie (PHPSESSID) to maintain your login session. This cookie is essential for the Service to function and is not used for tracking. We do not use third-party analytics cookies, advertising cookies, or tracking pixels.</p>

<h2>11. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Material changes will be notified by email. Continued use after changes take effect constitutes acceptance.</p>

<h2>12. Contact</h2>
<p>For privacy questions or data requests, contact us at <?= htmlspecialchars(SUPPORT_EMAIL) ?>.</p>

<p style="margin-top: 48px; font-size: 0.85rem; color: var(--gray-400); font-style: italic;">
    This Privacy Policy was generated for ContractPeer and has not been reviewed by an attorney. It accurately describes our actual data practices.
</p>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
