<?php
/**
 * ContractPeer - Stripe Integration (via REST API / curl)
 * Uses Stripe Checkout for payments (no card data on server).
 * Uses restricted API key only.
 */

function stripe_request($endpoint, $method = 'GET', $data = []) {
    $url = "https://api.stripe.com/v1" . $endpoint;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }
    
    $decoded = json_decode($response, true);
    if ($http_code >= 400) {
        return ['error' => $decoded['error']['message'] ?? 'Stripe API error', 'http_code' => $http_code, 'details' => $decoded];
    }
    
    return $decoded;
}

function create_checkout_session($user, $plan) {
    $pricing = PRICING[$plan] ?? null;
    if (!$pricing) return ['error' => 'Invalid plan'];
    
    // Create or get Stripe customer
    $customer_id = $user['stripe_customer_id'];
    if (!$customer_id) {
        $result = stripe_request('/customers', 'POST', [
            'email' => $user['email'],
            'name' => $user['name'] ?? '',
            'metadata' => ['user_id' => $user['id'], 'app' => APP_NAME]
        ]);
        if (isset($result['error'])) return $result;
        $customer_id = $result['id'];
        
        // Save customer ID
        $stmt = db()->prepare('UPDATE users SET stripe_customer_id = ? WHERE id = ?');
        $stmt->execute([$customer_id, $user['id']]);
    }
    
    // Create checkout session with line items
    // Using price_data (inline pricing) so we don't need pre-created Stripe products
    $line_items = [[
        'price_data' => [
            'currency' => 'usd',
            'unit_amount' => $pricing['price'] * 100, // Stripe uses cents
            'recurring' => ['interval' => 'month'],
            'product_data' => [
                'name' => APP_NAME . ' - ' . $pricing['name'],
                'description' => "{$pricing['contracts']} contracts per month"
            ]
        ],
        'quantity' => 1
    ]];
    
    $params = [
        'customer' => $customer_id,
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'subscription',
        'success_url' => APP_URL . '/dashboard.php?checkout=success',
        'cancel_url' => APP_URL . '/pricing.php?checkout=cancelled',
        'metadata' => ['user_id' => $user['id'], 'plan' => $plan],
        'subscription_data' => [
            'metadata' => ['user_id' => $user['id'], 'plan' => $plan],
            'trial_period_days' => 14
        ],
        'allow_promotion_codes' => true,
        'automatic_tax' => ['enabled' => true],
    ];
    
    return stripe_request('/checkout/sessions', 'POST', $params);
}

function create_customer_portal_session($user) {
    if (!$user['stripe_customer_id']) {
        return ['error' => 'No Stripe customer account found'];
    }
    return stripe_request('/billing_portal/sessions', 'POST', [
        'customer' => $user['stripe_customer_id'],
        'return_url' => APP_URL . '/dashboard.php'
    ]);
}

function process_refund($payment_id, $user_id, $amount = null, $reason = 'requested_by_customer') {
    // Check refund thresholds
    $today = date('Y-m-d');
    $stmt = db()->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM refund_log WHERE date(created_at) = ? AND status != 'reversed'");
    $stmt->execute([$today]);
    $daily_total = $stmt->fetch()['total'] / 100; // Convert cents to dollars
    
    if ($daily_total >= REFUND_THRESHOLD_DAILY_CUMULATIVE) {
        // Flag to human - over daily threshold
        log_event('refund_threshold_breached', [
            'user_id' => $user_id,
            'daily_total' => $daily_total,
            'threshold' => REFUND_THRESHOLD_DAILY_CUMULATIVE
        ]);
        return ['error' => 'Daily refund threshold reached. This refund requires manual approval.'];
    }
    
    $refund_amount = $amount;
    if ($refund_amount && ($refund_amount / 100) > REFUND_THRESHOLD_PER_TRANSACTION) {
        return ['error' => 'Refund amount exceeds per-transaction threshold. Manual approval required.'];
    }
    
    // Get payment intent from payment record
    $stmt = db()->prepare('SELECT * FROM payments WHERE id = ? AND user_id = ?');
    $stmt->execute([$payment_id, $user_id]);
    $payment = $stmt->fetch();
    
    if (!$payment || !$payment['stripe_payment_intent_id']) {
        return ['error' => 'Payment not found'];
    }
    
    $refund_params = [
        'payment_intent' => $payment['stripe_payment_intent_id'],
        'reason' => $reason,
    ];
    if ($amount) {
        $refund_params['amount'] = $amount;
    }
    
    $result = stripe_request('/refunds', 'POST', $refund_params);
    if (isset($result['error'])) return $result;
    
    // Log refund
    $stmt = db()->prepare('INSERT INTO refund_log (payment_id, user_id, stripe_refund_id, amount, reason) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$payment_id, $user_id, $result['id'], $result['amount'], $reason]);
    
    return ['success' => true, 'refund_id' => $result['id']];
}

function verify_webhook_signature($payload, $signature) {
    if (!STRIPE_WEBHOOK_SECRET) return true; // Skip if not configured (dev mode)
    
    $elements = explode(',', $signature);
    $header_data = [];
    foreach ($elements as $element) {
        $parts = explode('=', $element, 2);
        if (count($parts) === 2) {
            $header_data[trim($parts[0])] = trim($parts[1]);
        }
    }
    
    if (!isset($header_data['t']) || !isset($header_data['v1'])) {
        return false;
    }
    
    $signed_payload = $header_data['t'] . '.' . $payload;
    $expected_signature = hash_hmac('sha256', $signed_payload, STRIPE_WEBHOOK_SECRET);
    
    if (!hash_equals($expected_signature, $header_data['v1'])) {
        return false;
    }
    
    // Check timestamp (prevent replay attacks - 5 minute window)
    if (abs(time() - intval($header_data['t'])) > 300) {
        return false;
    }
    
    return true;
}

function handle_stripe_webhook($event) {
    $db = db();
    
    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];
            $user_id = $session['metadata']['user_id'] ?? null;
            $plan = $session['metadata']['plan'] ?? null;
            $customer_id = $session['customer'];
            
            if ($user_id && $plan) {
                $pricing = PRICING[$plan] ?? null;
                if ($pricing) {
                    $stmt = $db->prepare("UPDATE users SET plan = ?, contracts_limit = ? WHERE id = ?");
                    $stmt->execute([$plan, $pricing['contracts'], $user_id]);
                }
                
                $stmt = $db->prepare("INSERT INTO subscriptions (user_id, stripe_subscription_id, stripe_customer_id, plan, status, current_period_end) 
                    VALUES (?, ?, ?, ?, 'active', ?)
                    ON CONFLICT(stripe_subscription_id) DO UPDATE SET status = 'active', current_period_end = ?");
                $sub_id = $session['subscription'] ?? '';
                $stmt->execute([$user_id, $sub_id, $customer_id, $plan, date('Y-m-d H:i:s', time() + 86400 * 30), date('Y-m-d H:i:s', time() + 86400 * 30)]);
            }
            break;
            
        case 'invoice.paid':
            $invoice = $event['data']['object'];
            $user_id = $invoice['metadata']['user_id'] ?? null;
            if (!$user_id) {
                // Try to find user by customer ID
                $stmt = $db->prepare("SELECT id FROM users WHERE stripe_customer_id = ?");
                $stmt->execute([$invoice['customer']]);
                $user = $stmt->fetch();
                $user_id = $user['id'] ?? null;
            }
            if ($user_id) {
                $stmt = $db->prepare("INSERT INTO payments (user_id, stripe_payment_intent_id, stripe_invoice_id, amount, currency, status, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $invoice['payment_intent'] ?? '', $invoice['id'], $invoice['amount_paid'], $invoice['currency'] ?? 'usd', 'paid', $invoice['description'] ?? 'Subscription payment']);
            }
            break;
            
        case 'customer.subscription.deleted':
            $sub = $event['data']['object'];
            $stmt = $db->prepare("UPDATE subscriptions SET status = 'canceled' WHERE stripe_subscription_id = ?");
            $stmt->execute([$sub['id']]);
            // Downgrade user to free
            $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ?");
            $stmt->execute([$sub['id']]);
            $record = $stmt->fetch();
            if ($record) {
                $stmt = $db->prepare("UPDATE users SET plan = 'free', contracts_limit = 0 WHERE id = ?");
                $stmt->execute([$record['user_id']]);
            }
            break;
            
        case 'customer.subscription.updated':
            $sub = $event['data']['object'];
            $stmt = $db->prepare("UPDATE subscriptions SET status = ?, current_period_end = ? WHERE stripe_subscription_id = ?");
            $period_end = date('Y-m-d H:i:s', $sub['current_period_end'] ?? time());
            $stmt->execute([$sub['status'] ?? 'active', $period_end, $sub['id']]);
            break;
    }
}

function log_event($type, $data) {
    $stmt = db()->prepare("INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['user_id'] ?? null,
        $type,
        json_encode($data),
        'logged'
    ]);
}
