/**
 * ContractPeer - Chat Assistant Widget
 */
(function() {
    if (document.getElementById('chatWidget')) return;

    // Build widget HTML
    var widget = document.createElement('div');
    widget.id = 'chatWidget';
    widget.innerHTML = 
        '<div id="chatBox" style="display:none;width:360px;max-width:85vw;background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.18);overflow:hidden;margin-bottom:12px;animation:slideUp 0.3s ease;">' +
        '  <div style="background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;padding:16px 18px;">' +
        '    <div style="display:flex;justify-content:space-between;align-items:flex-start;">' +
        '      <div><div style="font-weight:700;font-size:1rem;">ContractPeer AI</div><div style="font-size:0.78rem;opacity:0.9;margin-top:2px;">Ask me anything about the platform</div></div>' +
        '      <button onclick="cpToggleChat()" style="background:rgba(255,255,255,0.15);border:none;color:white;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">&#10005;</button>' +
        '    </div>' +
        '  </div>' +
        '  <div id="cpMessages" style="height:320px;overflow-y:auto;padding:14px 16px;font-size:0.9rem;line-height:1.5;background:#f8f9fb;scroll-behavior:smooth;"></div>' +
        '  <div style="display:flex;gap:8px;border-top:1px solid #e5e7eb;padding:10px 14px;background:white;">' +
        '    <input id="cpInput" type="text" placeholder="Ask a question..." style="flex:1;border:1px solid #d1d5db;border-radius:24px;padding:10px 16px;font-size:0.9rem;outline:none;" onkeydown="if(event.key===\'Enter\')cpSendChat()">' +
        '    <button onclick="cpSendChat()" style="background:#2563eb;color:white;border:none;border-radius:24px;padding:10px 20px;cursor:pointer;font-weight:600;font-size:0.9rem;">Send</button>' +
        '  </div>' +
        '</div>' +
        '<button id="cpButton" onclick="cpToggleChat()" style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;border:none;cursor:pointer;box-shadow:0 4px 15px rgba(37,99,235,0.35);font-size:1.5rem;display:flex;align-items:center;justify-content:center;margin:0 0 0 auto;">&#128172;</button>';

    document.body.appendChild(widget);

    // Add styles
    var style = document.createElement('style');
    style.textContent = 
        '#chatWidget{position:fixed;bottom:24px;right:24px;z-index:9999;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;}' +
        '#chatWidget a{color:#2563eb;text-decoration:underline;word-break:break-word;}' +
        '@keyframes cpSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}' +
        '@keyframes cpPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}' +
        '@media(max-width:480px){#chatBox{width:85vw;bottom:12px;right:5vw;}#chatWidget{bottom:12px;right:12px;}}';
    document.head.appendChild(style);

    // Chat logic
    var cpOpen = false;
    var cpMessages, cpInput;

    window.cpToggleChat = function() {
        var box = document.getElementById('chatBox');
        var btn = document.getElementById('cpButton');
        cpOpen = !cpOpen;
        box.style.display = cpOpen ? 'block' : 'none';
        btn.style.display = cpOpen ? 'none' : 'flex';
        cpMessages = document.getElementById('cpMessages');
        cpInput = document.getElementById('cpInput');
        if (cpOpen && cpMessages.children.length === 0) {
            cpAddMsg('Hi there! I can answer questions about ContractPeer pricing, features, how the tool works, and more. How can I help you?', false);
        }
        if (cpOpen && cpInput) cpInput.focus();
    };

    window.cpLinkify = function(text) {
        var urlRegex = /https?:\/\/[^\s)]+/g;
        return text.replace(urlRegex, function(url) {
            return '<a href="' + url + '" target="_blank" rel="noopener">' + url + '</a>';
        }).replace(/\n/g, '<br>');
    };

    window.cpAddMsg = function(text, isUser) {
        cpMessages = document.getElementById('cpMessages');
        var div = document.createElement('div');
        div.style.cssText = 'margin-bottom:12px;display:flex;justify-content:' + (isUser ? 'flex-end' : 'flex-start') + ';';
        var bubble = document.createElement('div');
        bubble.style.cssText = 'display:inline-block;padding:10px 16px;border-radius:18px;max-width:85%;font-size:0.9rem;line-height:1.5;text-align:left;' + (isUser ? 'background:#2563eb;color:white;border-bottom-right-radius:4px;' : 'background:white;border:1px solid #e5e7eb;border-bottom-left-radius:4px;color:#1f2937;');
        if (isUser) {
            bubble.textContent = text;
        } else {
            bubble.innerHTML = window.cpLinkify(text);
        }
        div.appendChild(bubble);
        cpMessages.appendChild(div);
        cpMessages.scrollTop = cpMessages.scrollHeight;
    };

    window.cpSendChat = function() {
        cpInput = document.getElementById('cpInput');
        cpMessages = document.getElementById('cpMessages');
        var msg = cpInput.value.trim();
        if (!msg) return;
        window.cpAddMsg(msg, true);
        cpInput.value = '';
        var loading = document.createElement('div');
        loading.id = 'cpLoading';
        loading.style.cssText = 'margin-bottom:12px;text-align:left;';
        var dots = document.createElement('div');
        dots.style.cssText = 'display:inline-block;padding:10px 16px;border-radius:18px;background:white;border:1px solid #e5e7eb;border-bottom-left-radius:4px;';
        dots.innerHTML = '<span style="animation:cpPulse 1.4s infinite">.</span><span style="animation:cpPulse 1.4s infinite 0.2s">.</span><span style="animation:cpPulse 1.4s infinite 0.4s">.</span>';
        loading.appendChild(dots);
        cpMessages.appendChild(loading);
        cpMessages.scrollTop = cpMessages.scrollHeight;

        fetch('/api/chat.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:msg})})
        .then(function(r){return r.json()})
        .then(function(d){
            var el = document.getElementById('cpLoading');
            if (el) el.remove();
            if (d.reply) window.cpAddMsg(d.reply, false);
            else window.cpAddMsg('Sorry, I had trouble. Please email support@contractpeer.com for help.', false);
        })
        .catch(function(){
            var el = document.getElementById('cpLoading');
            if (el) el.remove();
            window.cpAddMsg('Sorry, I had trouble. Please email support@contractpeer.com for help.', false);
        });
    };
})();
