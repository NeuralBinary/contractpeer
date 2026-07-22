<?php
/**
 * ContractPeer - API: Contract Analysis
 * Upload a contract file, extract text, analyze with LLM, return results.
 */
require_once __DIR__ . '/../includes/config.php';

$user = require_subscription();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Handle file upload
if (!isset($_FILES['contract'])) {
    json_response(['error' => 'No file uploaded'], 400);
}

$save_result = save_uploaded_file($_FILES['contract']);
if (isset($save_result['error'])) {
    json_response($save_result, 400);
}

// Extract text
$extract_result = extract_text_from_file($save_result['path'], $save_result['filename']);
cleanup_uploaded_file($save_result['path']);

if (isset($extract_result['error'])) {
    json_response($extract_result, 400);
}

$contract_text = $extract_result['text'];

// Get user preferences
$preferences = [];
if (!empty($_POST['focus_areas'])) {
    $preferences['focus_areas'] = array_filter(array_map('trim', explode(',', $_POST['focus_areas'])));
}
if (!empty($_POST['perspective'])) {
    $preferences['perspective'] = $_POST['perspective'];
}

// Analyze
$analysis_result = analyze_contract($contract_text, $preferences);

if (isset($analysis_result['error'])) {
    json_response($analysis_result, 500);
}

$analysis = $analysis_result['analysis'];

// Count risks by severity
$high_count = $medium_count = $low_count = 0;
if (!empty($analysis['risks'])) {
    foreach ($analysis['risks'] as $risk) {
        switch (strtolower($risk['severity'] ?? '')) {
            case 'high': $high_count++; break;
            case 'medium': $medium_count++; break;
            case 'low': $low_count++; break;
        }
    }
}

// Save analysis to database
$stmt = db()->prepare('INSERT INTO analyses (user_id, filename, contract_text, analysis_result, risk_count_high, risk_count_medium, risk_count_low) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $user['id'],
    $save_result['filename'],
    mb_substr($contract_text, 0, 50000), // Store truncated text
    json_encode($analysis),
    $high_count,
    $medium_count,
    $low_count
]);
$analysis_id = db()->lastInsertId();

// Increment usage counter
$stmt = db()->prepare('UPDATE users SET contracts_used = contracts_used + 1 WHERE id = ?');
$stmt->execute([$user['id']]);

// Return result (without the full contract text to keep response small)
unset($analysis['contract_text']);
json_response([
    'success' => true,
    'analysis_id' => $analysis_id,
    'analysis' => $analysis,
    'risks_summary' => [
        'high' => $high_count,
        'medium' => $medium_count,
        'low' => $low_count,
        'total' => $high_count + $medium_count + $low_count
    ],
    'usage' => [
        'used' => $user['contracts_used'] + 1,
        'limit' => $user['contracts_limit']
    ]
]);
