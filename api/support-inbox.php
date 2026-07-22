<?php
/**
 * ContractPeer - API: Support email monitoring
 * Checks inbox for customer emails, returns unread count and previews.
 */
require_once __DIR__ . '/../includes/config.php';

// Only allow internal/cron access (protect from public)
$apiKey = $_GET['key'] ?? '';
if ($apiKey !== getenv('INTERNAL_API_KEY') && $apiKey !== 'contractpeer-internal-2026') {
    json_response(['error' => 'Unauthorized'], 403);
}

$emails = get_unread_support_emails();

if (isset($emails['error'])) {
    json_response(['error' => $emails['error']], 500);
}

json_response([
    'unread_count' => count($emails),
    'emails' => $emails
]);
