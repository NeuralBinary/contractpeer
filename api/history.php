<?php
/**
 * ContractPeer - API: Get analysis history
 */
require_once __DIR__ . '/../includes/config.php';

$user = require_auth();

$stmt = db()->prepare('SELECT id, filename, risk_count_high, risk_count_medium, risk_count_low, created_at FROM analyses WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$analyses = $stmt->fetchAll();

json_response(['analyses' => $analyses]);
