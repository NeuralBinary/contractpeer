<?php
require_once __DIR__ . '/includes/config.php';

$result = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['contract_text'])) {
    $text = trim($_POST['contract_text']);
    if (strlen($text) < 100) {
        $error = 'Please paste at least 100 characters of contract text.';
    } else {
        if (strlen($text) > 50000) $text = substr($text, 0, 50000);
        $analysis_result = analyze_contract($text);
        if (isset($analysis_result['error'])) {
            $error = $analysis_result['error'];
        } elseif (isset($analysis_result['analysis'])) {
            $a = $analysis_result['analysis'];
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
}

$page_title = 'Free NDA Risk Check';
$page_description = 'Paste your NDA or contract and get an instant AI risk analysis for free. See your top 3 risks immediately, no signup required.';
$extra_head = '<style>
.lead-tool{max-width:800px;margin:0 auto;padding:40px 0;}
.lead-tool textarea{width:100%;min-height:250px;padding:16px;border:1px solid #d1d5db;border-radius:12px;font-family:Courier New,monospace;font-size:0.9rem;resize:vertical;}
.result-box{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin:24px 0;}
.upsell-box{background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:12px;padding:32px;margin:32px 0;text-align:center;color:white;}
.upsell-box .btn{background:white;color:#2563eb;font-weight:700;}
.upsell-box a{color:white;}
.blur-preview{filter:blur(4px);pointer-events:none;user-select:none;opacity:0.6;}
</style>';

require __DIR__ . '/templates/header.php';
?>
<div class="lead-tool">
    <h1 class="fw-bold mb-2">Free NDA Risk Check</h1>
    <p class="text-muted mb-4">Paste your non-disclosure agreement or any contract below. Get an instant AI risk analysis — see your top 3 risks immediately. No signup required.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$result): ?>
        <form method="POST" action="/free-nda-check.php">
            <textarea name="contract_text" class="form-control" placeholder="Paste your contract text here...&#10;&#10;Example:&#10;MUTUAL NON-DISCLOSURE AGREEMENT&#10;This Agreement is entered into by and between Party A and Party B.&#10;1. CONFIDENTIAL INFORMATION&#10;Each party may disclose confidential information...&#10;2. INDEMNIFICATION&#10;Party A shall indemnify Party B from all claims..." required></textarea>
            <button type="submit" class="btn btn-cp btn-lg w-100 mt-3">Analyze My Contract — Free</button>
        </form>
        <p class="text-center mt-3 small text-muted">🔒 Your text is processed for analysis and not stored. We do not use your data for AI training.</p>
    <?php else: ?>
        <div class="result-box">
            <h2 class="h5">Analysis Complete</h2>
            <p class="text-muted mb-3"><?= htmlspecialchars($result['summary']) ?></p>
            
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div class="card text-center p-3 border-danger">
                        <div class="fs-3 fw-bold text-danger"><?= $result['total_risks_found'] ?></div>
                        <div class="small">Total Risks Found</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center p-3 border-success">
                        <div class="fs-3 fw-bold text-success">3</div>
                        <div class="small">Shown Free</div>
                    </div>
                </div>
            </div>
            
            <?php if ($result['risks']): ?>
                <h3 class="h6 mt-4 mb-2">Top Risks Identified</h3>
                <?php foreach ($result['risks'] as $risk):
                    $sev = strtolower($risk['severity'] ?? 'low'); ?>
                    <div class="card mb-2 p-3 <?= $sev === 'high' ? 'border-danger bg-danger-subtle' : ($sev === 'medium' ? 'border-warning bg-warning-subtle' : 'border-success bg-success-subtle') ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong><?= htmlspecialchars($risk['category'] ?? 'General') ?></strong>
                            <span class="badge <?= $sev === 'high' ? 'bg-danger' : ($sev === 'medium' ? 'bg-warning' : 'bg-success') ?>"><?= htmlspecialchars($risk['severity'] ?? 'LOW') ?></span>
                        </div>
                        <p class="mb-0 mt-1 small"><?= htmlspecialchars($risk['explanation'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($result['hidden_count'] > 0): ?>
                <div style="position:relative;">
                    <div class="blur-preview mt-3">
                        <h3 class="h6">+ <?= $result['hidden_count'] ?> More Risks Detected</h3>
                        <div class="card p-3 mb-2"><p class="mb-0 text-muted">Additional risks identified in categories including limitation of liability, assignment, amendment, and more...</p></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="upsell-box">
                <h3 class="h4 text-white">Unlock Your Full Risk Report</h3>
                <p>Get all <?= $result['total_risks_found'] ?> risks with clause references, severity ratings, explanations, recommended actions, missing clauses, and key terms.</p>
                <a href="/register.php" class="btn btn-lg">Get Full Report — Free Trial</a>
                <p class="small mt-2 mb-0" style="opacity:0.8">14-day free trial · 3 free contracts · No credit card</p>
            </div>
        </div>
        <p class="text-center mt-3"><a href="/free-nda-check.php">Check another contract</a></p>
    <?php endif; ?>
</div>

<div class="container" style="max-width:760px;padding:40px 0;">
    <h2 class="h4 mb-3">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Is the NDA risk check really free?</button></h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted">Yes. You can paste your contract and see the top 3 risks immediately without creating an account or providing a credit card.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">What types of contracts can I check?</button></h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted">Any type: NDAs, MSAs, employment contracts, vendor agreements, software licenses, and more.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Is my contract data secure?</button></h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted">Your contract text is processed for analysis and is not stored permanently. We do not use your data to train AI models.</div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">How accurate is the AI analysis?</button></h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body text-muted">The AI uses GPT-4o to analyze contracts against established legal risk patterns. It's a decision-support tool, not legal advice.</div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
