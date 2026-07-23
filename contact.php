<?php
$page_title = 'Contact Us';
$page_description = 'Get in touch with the ContractPeer team. We typically respond within 24 hours.';
require __DIR__ . '/templates/header.php';
?>
<div class="container" style="max-width:600px;padding:60px 0;">
    <h1 class="fw-bold mb-2">Contact Us</h1>
    <p class="text-muted mb-4">Have a question, feedback, or need help? Send us a message and we'll get back to you within 24 hours.</p>
    
    <form id="contactForm">
        <div id="contactStatus" class="alert" style="display:none;"></div>
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="contactName" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="contactEmail" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" class="form-control" id="contactSubject" placeholder="e.g., Billing question, Feature request, Bug report">
        </div>
        <div class="mb-3">
            <label class="form-label">Message <span class="text-danger">*</span></label>
            <textarea class="form-control" id="contactMessage" rows="5" required placeholder="How can we help you?"></textarea>
        </div>
        <button type="submit" class="btn btn-cp w-100">Send Message</button>
        <p class="text-center mt-3 small text-muted">You can also email us directly at <a href="mailto:support@contractpeer.com">support@contractpeer.com</a></p>
    </form>
</div>
<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const status = document.getElementById('contactStatus');
    btn.disabled = true; btn.textContent = 'Sending...';
    const r = await fetch('/api/contact.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({
        name: document.getElementById('contactName').value,
        email: document.getElementById('contactEmail').value,
        subject: document.getElementById('contactSubject').value,
        message: document.getElementById('contactMessage').value
    })});
    const d = await r.json();
    btn.disabled = false; btn.textContent = 'Send Message';
    status.style.display = 'block';
    if (d.success) { status.className = 'alert alert-success'; status.textContent = d.message; this.reset(); }
    else { status.className = 'alert alert-danger'; status.textContent = d.error || 'Something went wrong.'; }
});
</script>
<?php require __DIR__ . '/templates/footer.php'; ?>
