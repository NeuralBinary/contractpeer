/* ContractPeer - Frontend JavaScript */

// API helper
async function api(endpoint, options = {}) {
    const defaults = { headers: {}, credentials: 'same-origin' };
    if (options.body && !(options.body instanceof FormData)) {
        defaults.headers['Content-Type'] = 'application/json';
        options.body = typeof options.body === 'string' ? options.body : JSON.stringify(options.body);
    }
    const opts = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };
    const res = await fetch(endpoint, opts);
    const data = await res.json().catch(() => ({}));
    if (!res.ok && !data.error) data.error = `HTTP ${res.status}`;
    return data;
}

// Toast notifications
function toast(msg, type = '') {
    let t = document.querySelector('.toast');
    if (!t) { t = document.createElement('div'); t.className = 'toast'; document.body.appendChild(t); }
    t.textContent = msg;
    t.className = 'toast show ' + type;
    setTimeout(() => t.classList.remove('show'), 4000);
}

// Auth forms
document.addEventListener('DOMContentLoaded', () => {
    // Register form
    const regForm = document.getElementById('register-form');
    if (regForm) {
        regForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errEl = regForm.querySelector('.auth-error');
            const btn = regForm.querySelector('button[type="submit"]');
            btn.disabled = true; btn.textContent = 'Creating account...';
            const result = await api('/api/auth.php', { method: 'POST', body: { action: 'register', email: regForm.email.value, password: regForm.password.value, name: regForm.name.value } });
            btn.disabled = false; btn.textContent = 'Create Free Account';
            if (result.error) { errEl.textContent = result.error; errEl.style.display = 'block'; return; }
            window.location.href = '/dashboard.php';
        });
    }

    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errEl = loginForm.querySelector('.auth-error');
            const btn = loginForm.querySelector('button[type="submit"]');
            btn.disabled = true; btn.textContent = 'Signing in...';
            const result = await api('/api/auth.php', { method: 'POST', body: { action: 'login', email: loginForm.email.value, password: loginForm.password.value } });
            btn.disabled = false; btn.textContent = 'Sign In';
            if (result.error) { errEl.textContent = result.error; errEl.style.display = 'block'; return; }
            window.location.href = '/dashboard.php';
        });
    }

    // Logout
    document.querySelectorAll('[data-action="logout"]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            await api('/api/auth.php', { method: 'POST', body: { action: 'logout' } });
            window.location.href = '/';
        });
    });

    // File upload + analysis
    const uploadZone = document.getElementById('upload-zone');
    if (uploadZone) {
        const fileInput = document.getElementById('file-input');
        const analyzeForm = document.getElementById('analyze-form');

        uploadZone.addEventListener('click', () => fileInput.click());
        uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
        uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault(); uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; updateFileLabel(e.dataTransfer.files[0]); }
        });
        fileInput.addEventListener('change', () => { if (fileInput.files.length) updateFileLabel(fileInput.files[0]); });

        function updateFileLabel(file) {
            uploadZone.querySelector('.filename').textContent = file.name;
            uploadZone.querySelector('.icon').textContent = '📄';
        }

        analyzeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!fileInput.files.length) { toast('Please select a file first', 'error'); return; }

            const submitBtn = document.getElementById('analyze-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Analyzing...';

            // Show loading
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Analyzing your contract...</p>
                    <p style="font-size: 0.85rem; color: var(--gray-400); margin-top: 8px;">This typically takes 15-30 seconds</p>
                </div>`;

            const formData = new FormData();
            formData.append('contract', fileInput.files[0]);
            if (document.getElementById('focus-areas').value) formData.append('focus_areas', document.getElementById('focus-areas').value);
            if (document.getElementById('perspective').value) formData.append('perspective', document.getElementById('perspective').value);

            const result = await api('/api/analyze.php', { method: 'POST', body: formData });
            submitBtn.disabled = false;
            submitBtn.textContent = 'Analyze Contract';

            if (result.error) {
                resultsDiv.innerHTML = `<div class="risk-card high"><h4>Error</h4><p>${result.error}</p></div>`;
                if (result.upgrade_required) {
                    resultsDiv.innerHTML += `<p style="margin-top:16px;"><a href="/pricing.php" class="btn btn-primary">Upgrade Now</a></p>`;
                }
                return;
            }

            displayAnalysis(result);
        });
    }

    // Load analysis history
    const historyDiv = document.getElementById('history-list');
    if (historyDiv) loadHistory();

    // Load single analysis
    const analysisDiv = document.getElementById('analysis-detail');
    if (analysisDiv && window.location.search.includes('id=')) loadAnalysisDetail();

    // Checkout buttons
    document.querySelectorAll('[data-plan]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            btn.disabled = true; btn.textContent = 'Redirecting to checkout...';
            const result = await api('/api/billing.php', { method: 'POST', body: { action: 'create_checkout', plan: btn.dataset.plan } });
            if (result.error) { toast(result.error, 'error'); btn.disabled = false; btn.textContent = 'Subscribe'; return; }
            window.location.href = result.url;
        });
    });

    // Customer portal button
    document.querySelectorAll('[data-action="portal"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            btn.disabled = true; btn.textContent = 'Loading...';
            const result = await api('/api/billing.php', { method: 'POST', body: { action: 'portal' } });
            if (result.error) { toast(result.error, 'error'); btn.disabled = false; btn.textContent = 'Manage Billing'; return; }
            window.location.href = result.url;
        });
    });
});

// Display analysis results
function displayAnalysis(data) {
    const a = data.analysis;
    const s = data.risks_summary;
    const resultsDiv = document.getElementById('results');

    let html = `
        <div style="margin-bottom: 24px;">
            <h3>Analysis Complete</h3>
            <p style="color: var(--gray-500);">${a.summary || ''}</p>
        </div>

        <div style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
            <div class="risk-card high" style="flex: 1; min-width: 120px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 800;">${s.high}</div>
                <div>High Risk</div>
            </div>
            <div class="risk-card medium" style="flex: 1; min-width: 120px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 800;">${s.medium}</div>
                <div>Medium Risk</div>
            </div>
            <div class="risk-card low" style="flex: 1; min-width: 120px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 800;">${s.low}</div>
                <div>Low Risk</div>
            </div>
        </div>`;

    if (a.risks && a.risks.length) {
        html += '<h4 style="margin: 24px 0 12px;">Identified Risks</h4>';
        a.risks.forEach(risk => {
            const sev = (risk.severity || 'low').toLowerCase();
            html += `
                <div class="risk-card ${sev}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <h4>${escapeHtml(risk.category || 'General')}</h4>
                        <span class="risk-badge ${sev}">${escapeHtml(risk.severity || 'LOW')}</span>
                    </div>
                    ${risk.clause ? `<div class="clause">${escapeHtml(risk.clause)}</div>` : ''}
                    <p>${escapeHtml(risk.explanation || '')}</p>
                    ${risk.recommendation ? `<p class="recommendation"><strong>Recommendation:</strong> ${escapeHtml(risk.recommendation)}</p>` : ''}
                    ${risk.confidence ? `<p style="font-size: 0.8rem; color: var(--gray-400); margin-top: 4px;">Confidence: ${escapeHtml(risk.confidence)}</p>` : ''}
                </div>`;
        });
    }

    if (a.positive_aspects && a.positive_aspects.length) {
        html += '<h4 style="margin: 24px 0 12px;">Positive Aspects</h4><ul>';
        a.positive_aspects.forEach(p => { html += `<li style="margin-bottom: 4px;">${escapeHtml(p)}</li>`; });
        html += '</ul>';
    }

    if (a.missing_clauses && a.missing_clauses.length) {
        html += '<h4 style="margin: 24px 0 12px;">Missing Clauses</h4><ul>';
        a.missing_clauses.forEach(m => { html += `<li style="margin-bottom: 4px;">${escapeHtml(m)}</li>`; });
        html += '</ul>';
    }

    if (a.key_terms) {
        html += '<h4 style="margin: 24px 0 12px;">Key Terms</h4><div class="risk-card low">';
        for (const [key, val] of Object.entries(a.key_terms)) {
            if (val) html += `<p><strong>${escapeHtml(key.replace(/_/g, ' '))}:</strong> ${escapeHtml(String(val))}</p>`;
        }
        html += '</div>';
    }

    html += `<p style="margin-top: 24px; font-size: 0.85rem; color: var(--gray-400);">
        ⚠️ This analysis is for informational purposes only and does not constitute legal advice. 
        Always have a qualified attorney review contracts before making decisions.
    </p>`;

    resultsDiv.innerHTML = html;
}

// Load history
async function loadHistory() {
    const div = document.getElementById('history-list');
    const result = await api('/api/history.php');
    if (result.error || !result.analyses || !result.analyses.length) {
        div.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 40px;">No analyses yet. Upload a contract to get started.</p>';
        return;
    }
    div.innerHTML = result.analyses.map(a => `
        <div class="risk-card low" style="cursor: pointer;" onclick="window.location.href='/analysis.php?id=${a.id}'">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4>${escapeHtml(a.filename)}</h4>
                    <p style="font-size: 0.85rem; color: var(--gray-400);">${new Date(a.created_at).toLocaleDateString()}</p>
                </div>
                <div style="display: flex; gap: 8px;">
                    ${a.risk_count_high ? `<span class="risk-badge high">${a.risk_count_high} High</span>` : ''}
                    ${a.risk_count_medium ? `<span class="risk-badge medium">${a.risk_count_medium} Med</span>` : ''}
                    ${a.risk_count_low ? `<span class="risk-badge low">${a.risk_count_low} Low</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

// Load single analysis
async function loadAnalysisDetail() {
    const div = document.getElementById('analysis-detail');
    const id = new URLSearchParams(window.location.search).get('id');
    const result = await api(`/api/get-analysis.php?id=${id}`);
    if (result.error) { div.innerHTML = `<div class="risk-card high"><p>${result.error}</p></div>`; return; }

    const a = result.analysis;
    const analysis = a.analysis_result || {};
    displayAnalysis({
        analysis: analysis,
        risks_summary: {
            high: a.risk_count_high,
            medium: a.risk_count_medium,
            low: a.risk_count_low,
            total: a.risk_count_high + a.risk_count_medium + a.risk_count_low
        }
    });
}

// Utility
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}
