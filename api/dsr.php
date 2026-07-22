<?php
/**
 * ContractPeer - API: Data Subject Request (GDPR/CCPA)
 * Handles access and deletion requests.
 */
require_once __DIR__ . '/../includes/config.php';

$user = require_auth();
$input = get_input();
$request_type = $input['request_type'] ?? '';

switch ($request_type) {
    case 'access':
        // Export all user data
        $stmt = db()->prepare('SELECT id, email, name, plan, created_at FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $user_data = $stmt->fetch();
        
        $stmt = db()->prepare('SELECT id, filename, risk_count_high, risk_count_medium, risk_count_low, created_at FROM analyses WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $analyses = $stmt->fetchAll();
        
        $stmt = db()->prepare('SELECT id, amount, currency, status, description, created_at FROM payments WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $payments = $stmt->fetchAll();
        
        $export = [
            'user' => $user_data,
            'analyses' => $analyses,
            'payments' => $payments,
            'exported_at' => date('c')
        ];
        
        // Log the DSR
        $stmt = db()->prepare('INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user['id'], 'access', json_encode(['exported_at' => date('c')]), 'completed']);
        
        json_response(['data' => $export]);
        break;
        
    case 'delete':
        // Delete all user data
        $stmt = db()->prepare('DELETE FROM analyses WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        
        $stmt = db()->prepare('DELETE FROM payments WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        
        $stmt = db()->prepare('DELETE FROM subscriptions WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        
        $stmt = db()->prepare('DELETE FROM dsr_log WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        
        $stmt = db()->prepare('INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([null, 'deletion', json_encode(['email' => $user['email'], 'deleted_at' => date('c')]), 'completed']);
        
        logout_user();
        json_response(['success' => true, 'message' => 'All data deleted.']);
        break;
        
    default:
        json_response(['error' => 'Invalid request type. Use "access" or "delete".'], 400);
}
