<?php
/**
 * ContractPeer - API: Get single analysis result
 */
require_once __DIR__ . '/../includes/config.php';

$user = require_auth();
$analysis_id = $_GET['id'] ?? null;

if (!$analysis_id) {
    json_response(['error' => 'Analysis ID required'], 400);
}

$stmt = db()->prepare('SELECT * FROM analyses WHERE id = ? AND user_id = ?');
$stmt->execute([$analysis_id, $user['id']]);
$analysis = $stmt->fetch();

if (!$analysis) {
    json_response(['error' => 'Analysis not found'], 404);
}

$analysis['analysis_result'] = json_decode($analysis['analysis_result'], true);
// Don't return full contract text
unset($analysis['contract_text']);

json_response(['analysis' => $analysis]);
