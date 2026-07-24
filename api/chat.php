<?php
/**
 * ContractPeer - AI Chat Assistant API
 * Uses cheapest available model. Supports OpenAI or OpenRouter.
 */
require_once __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$message = trim($input['message'] ?? '');

if (!$message) json_response(['error' => 'Message is required.'], 400);
if (strlen($message) > 2000) json_response(['error' => 'Message too long.'], 400);

// Rate limit
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = STORAGE_PATH . '/chat_ratelimit_' . md5($ip);
$now = time();
$last = @file_get_contents($rate_file);
if ($last && ($now - intval($last)) < 3) json_response(['error' => 'Please wait a moment.'], 429);
file_put_contents($rate_file, (string)$now);

// Try OpenRouter first if we have a key
$chat_api_key = getenv('OPENROUTER_API_KEY') ?: '';
$chat_api_url = 'https://openrouter.ai/api/v1/chat/completions';
$chat_model = getenv('CHAT_MODEL') ?: 'deepseek/deepseek-v4-flash';
$chat_headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $chat_api_key,
    'HTTP-Referer: https://contractpeer.com',
    'X-Title: ContractPeer Chat',
];

// Fall back to OpenAI if no OpenRouter key
if (!$chat_api_key) {
    $chat_api_key = LLM_API_KEY;
    $chat_api_url = 'https://api.openai.com/v1/chat/completions';
    $chat_model = 'gpt-4o-mini';
    $chat_headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $chat_api_key,
    ];
}

$system = "You are ContractPeer's AI assistant on contractpeer.com. Be friendly, concise, and helpful.

ABOUT CONTRACTPEER:
- AI-powered contract review for solo attorneys and small law firms (1-10 attorneys)
- Upload PDF/DOCX/TXT, get instant risk analysis across 15+ categories
- GPT-4o powered, ~$0.006 per analysis in AI costs
- Web-based, no MS Word plugin needed
- Secure: data not used for AI training, GDPR/CCPA compliant

PRICING: Starter $49/mo (10 contracts), Professional $99/mo (50 contracts), Firm $199/mo (200 contracts). 14-day free trial with 3 free contracts, no credit card required.

WEBSITE LINKS:
- Free NDA check (no signup): https://contractpeer.com/free-nda-check.php
- Free trial signup: https://contractpeer.com/register.php
- Pricing: https://contractpeer.com/pricing.php
- Contact: https://contractpeer.com/contact.php
- Blog: https://contractpeer.com/blog/

RULES:
1. Do NOT provide legal advice - this is a decision-support tool
2. Do not hallucinate features - only mention what actually exists
3. When mentioning URLs, use the full https:// URL so they become clickable
4. If you don't know something, say so honestly
5. Keep responses under 200 words unless more detail is needed
6. Use plain text only - no markdown formatting";

$data = [
    'model' => $chat_model,
    'messages' => [
        ['role' => 'system', 'content' => $system],
        ['role' => 'user', 'content' => $message]
    ],
    'temperature' => 0.5,
    'max_tokens' => 500,
];

$ch = curl_init($chat_api_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => $chat_headers,
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error || $http >= 400) {
    json_response(['reply' => 'Service temporarily unavailable. Please email support@contractpeer.com for help.'], 200);
}

$decoded = json_decode($response, true);
$reply = $decoded['choices'][0]['message']['content'] ?? '';

json_response(['reply' => $reply]);
