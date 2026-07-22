<?php
/**
 * ContractPeer - Free NDA Risk Check (Lead Magnet)
 * Gives a basic 3-risk analysis without signup, then prompts registration for full report.
 */
require_once __DIR__ . '/includes/config.php';

// If POST with contract text, run a limited analysis
$result = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['contract_text'])) {
    $text = trim($_POST['contract_text']);
    if (strlen($text) < 100) {
        $error = 'Please paste at least 100 characters of contract text.';
    } elseif (strlen($text) > 50000) {
        $text = substr($text, 0, 50000);
        $analysis_result = analyze_contract($text);
    } else {
        $analysis_result = analyze_contract($text);
    }
    
    if (isset($analysis_result['error'])) {
        $error = $analysis_result['error'];
    } elseif (isset($analysis_result['analysis'])) {
        $a = $analysis_result['analysis'];
        // Only show top 3 risks (limited - lead magnet)
        $limited_risks = array_slice($a['risks'] ?? [], 0, 3);
        $result = [
            'summary' => $a['summary'] ?? '',
            'risk_level' => $a['risk_level'] ?? 'unknown',
            'risks' => $limited_risks,
            'total_risks_found' => count($a['risks'] ?? []),
            'hidden_count' => max(0, count($a['risks'] ?? []) - 3),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free NDA Risk Check — Instant AI Contract Analysis | ContractPeer</title>
    <meta name="description" content="Paste your NDA or contract and get an instant AI risk analysis for free. See your top 3 risks immediately, no signup required. Full report with 15+ categories available with free trial.">
    <meta name="keywords" content="free NDA review, contract risk check, AI contract analysis, NDA risk assessment, free contract review tool">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Free NDA Risk Check — Instant AI Contract Analysis">
    <meta property="og:description" content="Paste your contract and get instant AI risk analysis. See top 3 risks free. No signup required.">
    <meta property="og:url" content="https://contractpeer.com/free-nda-check.php">
    <meta property="og:type" content="website">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "ContractPeer Free NDA Risk Check",
        "description": "Free AI-powered contract risk analysis tool. Paste your NDA or contract to get instant risk assessment.",
        "url": "https://contractpeer.com/free-nda-check.php",
        "applicationCategory": "LegalApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "publisher": {
            "@type": "Organization",
            "name": "ContractPeer",
            "url": "https://contractpeer.com"
        }
    }
    </script>
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .lead-tool { max-width: 800px; margin: 0 auto; padding: 40px 24px; }
        .lead-tool h1 { font-size: 2rem; margin-bottom: 8px; }
        .lead-tool .subtitle { color: var(--gray-500); margin-bottom: 32px; font-size: 1.1rem; }
        .lead-tool textarea { width: 100%; min-height: 250px; padding: 16px; border: 1px solid var(--gray-300); border-radius: var(--radius); font-family: 'Courier New', monospace; font-size: 0.9rem; resize: vertical; }
        .lead-tool textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
        .lead-tool .btn { margin-top: 16px; }
        .upsell-box { background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: var(--radius); padding: 32px; margin: 32px 0; text-align: center; color: white; }
        .upsell-box h3 { font-size: 1.5rem; margin-bottom: 8px; color: white; }
        .upsell-box p { opacity: 0.95; margin-bottom: 16px; }
        .upsell-box .btn { background: white; color: var(--primary); font-weight: 700; }
        .upsell-box .btn:hover { background: var(--gray-100); }
        .result-box { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 24px; margin: 24px 0; }
        .blur-preview { filter: blur(4px); pointer-events: none; user-select: none; opacity: 0.6; }
        .blur-overlay { position: relative; }
        .blur-overlay::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(37, 99, 235, 0.05); border-radius: var(--radius); }
    </style>
</head>
<body>
<nav class="nav">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">Contract<span>Peer</span></a>
        <div class="nav-links">
            <a href="/free-nda-check.php" class="active">Free NDA Check</a>
            <a href="/pricing.php">Pricing</a>
            <a href="/blog/">Blog</a>
            <a href="/register.php" class="nav-cta">Start Free Trial</a>
        </div>
    </div>
</nav>

<div class="lead-tool">
    <h1>Free NDA Risk Check</h1>
    <p class="subtitle">Paste your non-disclosure agreement or any contract below. Get an instant AI risk analysis — see your top 3 risks immediately. No signup required.</p>
    
    <?php if ($error): ?>
        <div class="risk-card high" style="margin-bottom: 16px;">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!$result): ?>
        <form method="POST" action="/free-nda-check.php">
            <textarea name="contract_text" placeholder="Paste your contract text here... (e.g., your NDA, MSA, employment agreement, vendor contract)

Example:
MUTUAL NON-DISCLOSURE AGREEMENT
This Agreement is entered into by and between Party A and Party B.
1. CONFIDENTIAL INFORMATION
Each party may disclose confidential information...
2. INDEMNIFICATION
Party A shall indemnify Party B from all claims..." required></textarea>
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Analyze My Contract — Free</button>
        </form>
        <p style="text-align: center; margin-top: 16px; font-size: 0.85rem; color: var(--gray-400);">
            🔒 Your text is processed for analysis and not stored. We do not use your data for AI training.
        </p>
    <?php else: ?>
        <div class="result-box">
            <h2 style="margin-bottom: 8px;">Analysis Complete</h2>
            <p style="color: var(--gray-600); margin-bottom: 16px;"><?= htmlspecialchars($result['summary']) ?></p>
            
            <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                <div class="risk-card high" style="flex:1; text-align:center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?= $result['total_risks_found'] ?></div>
                    <div>Total Risks Found</div>
                </div>
                <div class="risk-card low" style="flex:1; text-align:center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);">3</div>
                    <div>Shown Free</div>
                </div>
            </div>
            
            <?php if ($result['risks']): ?>
                <h3 style="margin: 20px 0 12px;">Top Risks Identified</h3>
                <?php foreach ($result['risks'] as $risk): 
                    $sev = strtolower($risk['severity'] ?? 'low'); ?>
                    <div class="risk-card <?= $sev ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <h4><?= htmlspecialchars($risk['category'] ?? 'General') ?></h4>
                            <span class="risk-badge <?= $sev ?>"><?= htmlspecialchars($risk['severity'] ?? 'LOW') ?></span>
                        </div>
                        <p><?= htmlspecialchars($risk['explanation'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="risk-card low"><p>No major risks found in the top categories. Full analysis may reveal additional considerations.</p></div>
            <?php endif; ?>
            
            <?php if ($result['hidden_count'] > 0): ?>
                <div class="blur-overlay">
                    <div class="blur-preview">
                        <h3 style="margin: 20px 0 12px;">+ <?= $result['hidden_count'] ?> More Risks Detected</h3>
                        <div class="risk-card medium"><p>Additional risks identified in categories including limitation of liability, assignment, amendment, and more...</p></div>
                        <div class="risk-card low"><p>Missing clauses detected, positive aspects identified, key terms extracted...</p></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="upsell-box">
                <h3>Unlock Your Full Risk Report</h3>
                <p>Get all <?= $result['total_risks_found'] ?> risks with clause references, severity ratings, plain-language explanations, recommended actions, missing clauses, and key terms extracted.</p>
                <a href="/register.php" class="btn btn-lg">Get Full Report — Free Trial</a>
                <p style="font-size: 0.85rem; margin-top: 8px; opacity: 0.8;">14-day free trial · 3 free contracts · No credit card required</p>
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 24px;">
            <a href="/free-nda-check.php">Check another contract</a>
        </p>
    <?php endif; ?>
</div>

<!-- FAQ Section for SEO -->
<div style="max-width: 760px; margin: 0 auto; padding: 40px 24px;">
    <h2 style="margin-bottom: 16px;">Frequently Asked Questions</h2>
    
    <h3>Is the NDA risk check really free?</h3>
    <p>Yes. You can paste your contract and see the top 3 risks immediately without creating an account or providing a credit card. To see the full report with all risks, missing clauses, and key terms, sign up for a free 14-day trial.</p>
    
    <h3>What types of contracts can I check?</h3>
    <p>Any type: NDAs, master services agreements, employment contracts, vendor agreements, software licenses, real estate leases, and more. The AI analyzes across 15+ risk categories regardless of contract type.</p>
    
    <h3>Is my contract data secure?</h3>
    <p>Yes. Your contract text is processed to generate the analysis and is not stored permanently. We do not use your data to train AI models. The full privacy policy is available on our <a href="/legal/privacy.php">privacy page</a>.</p>
    
    <h3>How accurate is the AI analysis?</h3>
    <p>The AI uses GPT-4o to analyze contracts against established legal risk patterns. It's highly effective at identifying common risks like one-sided indemnification, disproportionate liability caps, and missing clauses. However, it's a decision-support tool — not legal advice. Always have a qualified attorney review contracts before making decisions.</p>
    
    <h3>What's the difference between the free check and the full report?</h3>
    <p>The free check shows your top 3 risks with brief explanations. The full report (available with a free trial) includes: all identified risks, the exact clause text for each risk, severity ratings, confidence levels, recommended actions for each risk, positive aspects of the contract, missing clauses that should be included, and key terms extracted (parties, dates, governing law, payment terms).</p>
</div>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type": "Question", "name": "Is the NDA risk check really free?", "acceptedAnswer": {"@type": "Answer", "text": "Yes. You can paste your contract and see the top 3 risks immediately without creating an account or providing a credit card."}},
        {"@type": "Question", "name": "What types of contracts can I check?", "acceptedAnswer": {"@type": "Answer", "text": "Any type: NDAs, master services agreements, employment contracts, vendor agreements, software licenses, and more."}},
        {"@type": "Question", "name": "Is my contract data secure?", "acceptedAnswer": {"@type": "Answer", "text": "Your contract text is processed for analysis and is not stored permanently. We do not use your data to train AI models."}},
        {"@type": "Question", "name": "How accurate is the AI analysis?", "acceptedAnswer": {"@type": "Answer", "text": "The AI uses GPT-4o to analyze contracts against established legal risk patterns. It's highly effective but is a decision-support tool, not legal advice."}},
        {"@type": "Question", "name": "What's the difference between the free check and the full report?", "acceptedAnswer": {"@type": "Answer", "text": "The free check shows top 3 risks. The full report includes all risks, clause references, recommendations, missing clauses, and key terms."}}
    ]
}
</script>

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
                <a href="/free-nda-check.php">Free NDA Check</a>
                <a href="/pricing.php">Pricing</a>
                <a href="/blog/">Blog</a>
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
            <p>&copy; 2026 Contract Peer (contractpeer.com). Operated by Contract Peer.</p>
            <p style="margin-top: 8px; font-size: 0.8rem; opacity: 0.6;">ContractPeer is a decision-support tool, not legal advice. Always have a qualified attorney review contracts.</p>
        </div>
    </div>
</footer>

<script src="/assets/js/app.js"></script>
</body>
</html>
