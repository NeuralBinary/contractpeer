<?php
/**
 * ContractPeer - AI Chat Assistant API
 * Answers customer questions using GPT-4o with a personality prompt
 */
require_once __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$message = trim($input['message'] ?? '');

if (!$message) {
    json_response(['error' => 'Message is required.'], 400);
}
if (strlen($message) > 2000) {
    json_response(['error' => 'Message too long.'], 400);
}

// Rate limit checks
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = STORAGE_PATH . '/chat_ratelimit_' . md5($ip);
$now = time();
$last_request = @file_get_contents($rate_file);
if ($last_request && ($now - intval($last_request)) < 3) {
    json_response(['error' => 'Please wait a moment before sending another message.'], 429);
}
file_put_contents($rate_file, (string)$now);

$system_prompt = "You are ContractPeer's AI assistant on contractpeer.com. Be friendly, concise, and helpful.

ABOUT CONTRACTPEER:
- AI-powered contract review tool for solo attorneys and small law firms (1-10 attorneys)
- Upload PDF/DOCX/TXT, get instant risk analysis across 15+ categories
- GPT-4o powered, ~$0.006 per analysis in AI costs
- Web-based, no MS Word plugin needed
- Secure: data not used for AI training, encrypted, GDPR/CCPA compliant

PRICING: Starter $49/mo (10 contracts), Professional $99/mo (50 contracts), Firm $199/mo (200 contracts). 14-day free trial with 3 free contracts, no credit card required.

FEATURES:
- Clause-by-clause risk analysis with severity ratings (HIGH/MEDIUM/LOW)
- Plain-language explanations of every risk identified
- Suggested edits and recommended actions for each risk
- Contract playbooks (Professional+), version comparison (Professional+), team seats (Firm)
- Analysis history, export annotated contracts

CONTRACT TYPES SUPPORTED: NDAs, MSAs, employment agreements, vendor contracts, SaaS agreements, software licenses, real estate leases, service agreements, independent contractor agreements, partnership agreements, and more.

IMPORTANT RULES:
1. Do NOT provide legal advice — always remind users this is a decision-support tool
2. Do NOT hallucinate features — only mention features that actually exist
3. If asked something you don't know, say \"I don't have that information — contact support@contractpeer.com and we'll get back to you\"
4. Be honest about limitations — AI can make mistakes, attorneys must verify
5. Suggest the free NDA check at /free-nda-check.php and free trial at /register.php
6. Keep responses under 200 words unless more detail is genuinely needed

RESPONSE FORMAT: Plain text, not markdown. Use line breaks between paragraphs. Use a friendly but professional tone.";
$user_prompt = "The visitor asks: {$message}\n\nRespond helpfully, honestly, and concisely.";

if (LLM_PROVIDER === 'openai') {
    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_prompt]
        ],
        'temperature' => 0.5,
        'max_tokens' => 500
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . LLM_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        json_response(['error' => 'Service temporarily unavailable.'], 500);
    }
    
    $decoded = json_decode($response, true);
    if ($http_code >= 400 || !$decoded) {
        json_response(['error' => 'Service temporarily unavailable.'], 500);
    }
    
    $reply = $decoded['choices'][0]['message']['content'] ?? '';
    
    json_response(['reply' => $reply]);
} else {
    json_response(['error' => 'Chat service not configured.'], 500);
}
