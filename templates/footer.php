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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<?= isset($extra_scripts) ? $extra_scripts : '' ?>

<!-- AI Chat Widget -->
<div id="chatWidget" style="position:fixed;bottom:24px;right:24px;z-index:9999;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <div id="chatBox" style="display:none;width:340px;max-width:90vw;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.2);overflow:hidden;margin-bottom:12px;">
        <div style="background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;padding:16px;display:flex;justify-content:space-between;align-items:center;">
            <div><strong>Ask ContractPeer AI</strong><br><span style="font-size:0.8rem;opacity:0.9;">I can answer questions about pricing, features, and how the tool works.</span></div>
            <button onclick="toggleChat()" style="background:none;border:none;color:white;font-size:1.3rem;cursor:pointer;">✕</button>
        </div>
        <div id="chatMessages" style="height:300px;overflow-y:auto;padding:16px;font-size:0.9rem;line-height:1.5;background:#f9fafb;"></div>
        <div style="display:flex;border-top:1px solid #e5e7eb;padding:8px;background:white;">
            <input id="chatInput" type="text" placeholder="Ask a question..." style="flex:1;border:1px solid #d1d5db;border-radius:8px;padding:10px 14px;font-size:0.9rem;outline:none;" onkeydown="if(event.key==='Enter')sendChat()">
            <button onclick="sendChat()" style="margin-left:8px;background:#2563eb;color:white;border:none;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600;">Send</button>
        </div>
    </div>
    <button id="chatButton" onclick="toggleChat()" style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;border:none;cursor:pointer;box-shadow:0 4px 15px rgba(37,99,235,0.4);font-size:1.5rem;display:flex;align-items:center;justify-content:center;margin-left:auto;">💬</button>
</div>
<script>
let chatMessages = document.getElementById('chatMessages');
let chatInput = document.getElementById('chatInput');

function toggleChat() {
    let box = document.getElementById('chatBox');
    let btn = document.getElementById('chatButton');
    let open = box.style.display !== 'none';
    box.style.display = open ? 'none' : 'block';
    btn.style.display = open ? 'flex' : 'none';
    if (!open && chatMessages.children.length === 0) {
        addChatMsg('Hi! I\'m the ContractPeer assistant. Ask me about pricing, features, how the tool works, or anything else about the platform!', false);
    }
}

function addChatMsg(text, isUser) {
    let div = document.createElement('div');
    div.style.cssText = 'margin-bottom:10px;text-align:' + (isUser ? 'right' : 'left');
    let bubble = document.createElement('div');
    bubble.style.cssText = 'display:inline-block;padding:10px 14px;border-radius:16px;max-width:85%;font-size:0.9rem;line-height:1.4;text-align:left;' + (isUser ? 'background:#2563eb;color:white;border-bottom-right-radius:4px;' : 'background:white;border:1px solid #e5e7eb;border-bottom-left-radius:4px;');
    bubble.textContent = text;
    div.appendChild(bubble);
    chatMessages.appendChild(div);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sendChat() {
    let msg = chatInput.value.trim();
    if (!msg) return;
    addChatMsg(msg, true);
    chatInput.value = '';
    let loading = document.createElement('div');
    loading.id = 'chatLoading';
    loading.style.cssText = 'text-align:left;margin-bottom:10px;color:#9ca3af;font-size:0.85rem;';
    loading.textContent = 'Thinking...';
    chatMessages.appendChild(loading);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    fetch('/api/chat.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:msg})})
    .then(r=>r.json()).then(d=>{
        document.getElementById('chatLoading')?.remove();
        if (d.reply) addChatMsg(d.reply, false);
        else addChatMsg('Sorry, I had trouble with that. Please email support@contractpeer.com for help.', false);
    }).catch(()=>{
        document.getElementById('chatLoading')?.remove();
        addChatMsg('Sorry, I had trouble with that. Please email support@contractpeer.com for help.', false);
    });
}
</script>
</body>
</html>
