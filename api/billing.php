<?php
/**
 * ContractPeer - API: Stripe Checkout & Billing
 */
require_once __DIR__ . '/../includes/config.php';

$user = current_user();
$input = get_input();
$action = $_GET['action'] ?? $input['action'] ?? '';

if (!$user && $action !== 'webhook') {
    json_response(['error' => 'Authentication required'], 401);
}

switch ($action) {
    case 'create_checkout':
        if (!$user) json_response(['error' => 'Authentication required'], 401);
        $plan = $input['plan'] ?? '';
        if (!isset(PRICING[$plan])) {
            json_response(['error' => 'Invalid plan'], 400);
        }
        $result = create_checkout_session($user, $plan);
        if (isset($result['error'])) {
            json_response($result, 400);
        }
        json_response(['url' => $result['url']]);
        break;
        
    case 'portal':
        if (!$user) json_response(['error' => 'Authentication required'], 401);
        $result = create_customer_portal_session($user);
        if (isset($result['error'])) {
            json_response($result, 400);
        }
        json_response(['url' => $result['url']]);
        break;
        
    case 'webhook':
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        if (!verify_webhook_signature($payload, $signature)) {
            json_response(['error' => 'Invalid signature'], 400);
        }
        
        $event = json_decode($payload, true);
        handle_stripe_webhook($event);
        json_response(['received' => true]);
        break;
        
    case 'request_refund':
        if (!$user) json_response(['error' => 'Authentication required'], 401);
        $payment_id = $input['payment_id'] ?? null;
        $reason = $input['reason'] ?? 'requested_by_customer';
        if (!$payment_id) {
            json_response(['error' => 'Payment ID required'], 400);
        }
        $result = process_refund($payment_id, $user['id'], null, $reason);
        if (isset($result['error'])) {
            json_response($result, 400);
        }
        json_response($result);
        break;
        
    default:
        json_response(['error' => 'Invalid action'], 400);
}
