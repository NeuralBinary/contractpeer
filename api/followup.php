<?php
/**
 * ContractPeer - Automated Follow-up & Retention System
 * Called by daily cron. Never spams - each email has a clear purpose.
 */
require_once __DIR__ . '/../includes/config.php';

$today = date('Y-m-d H:i:s');
$log = [];

// 1. Trial expiring in 3 days
$stmt = db()->prepare("SELECT id, email, name FROM users WHERE plan = 'free' AND trial_ends_at IS NOT NULL AND trial_ends_at > datetime('now') AND trial_ends_at < datetime('now', '+3 days') AND id NOT IN (SELECT user_id FROM dsr_log WHERE request_type = 'trial_3day_warning')");
$stmt->execute();
$users = $stmt->fetchAll();
foreach ($users as $u) {
    $result = send_trial_warning_email($u['email'], 3);
    if (isset($result['success'])) {
        $stmt2 = db()->prepare("INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, 'trial_3day_warning', ?, 'sent')");
        $stmt2->execute([$u['id'], json_encode(['days_left' => 3])]);
        $log[] = "3-day warning sent to {$u['email']}";
    }
}

// 2. Trial expired yesterday
$stmt = db()->prepare("SELECT id, email, name FROM users WHERE plan = 'free' AND trial_ends_at IS NOT NULL AND trial_ends_at < datetime('now', '-1 day') AND trial_ends_at > datetime('now', '-2 days') AND id NOT IN (SELECT user_id FROM dsr_log WHERE request_type = 'trial_expired_1day')");
$stmt->execute();
$users = $stmt->fetchAll();
foreach ($users as $u) {
    $subject = "Your ContractPeer Trial Has Ended";
    $html = email_template($subject, "
        <h2>Your Free Trial Has Ended</h2>
        <p>Your 14-day ContractPeer trial has ended. Your contracts and analysis history are still available — upgrade to keep using the service.</p>
        <p><a href='https://contractpeer.com/pricing.php' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;'>View Plans</a></p>
        <p>Plans start at just \$49/month for 10 contract analyses.</p>
    ");
    $result = send_email($u['email'], $subject, $subject, $html);
    $stmt2 = db()->prepare("INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, 'trial_expired_1day', ?, 'sent')");
    $stmt2->execute([$u['id'], json_encode(['days_after' => 1])]);
    $log[] = "Expired notice sent to {$u['email']}";
}

// 3. Trial expired 7 days ago — last chance
$stmt = db()->prepare("SELECT id, email, name FROM users WHERE plan = 'free' AND trial_ends_at IS NOT NULL AND trial_ends_at < datetime('now', '-7 days') AND trial_ends_at > datetime('now', '-8 days') AND id NOT IN (SELECT user_id FROM dsr_log WHERE request_type = 'trial_expired_7day')");
$stmt->execute();
$users = $stmt->fetchAll();
foreach ($users as $u) {
    $subject = "Your Contracts Are Still Waiting, {$u['name']}";
    $body = "Hey {$u['name']},\n\nYour ContractPeer trial ended a week ago, but we saved your analysis history. Upgrade to reactivate and continue reviewing contracts.\n\nPricing starts at \$49/month.\n\nhttps://contractpeer.com/pricing.php\n\nBest,\nThe ContractPeer Team";
    $result = send_email($u['email'], $subject, nl2br($body), $body);
    $stmt2 = db()->prepare("INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, 'trial_expired_7day', ?, 'sent')");
    $stmt2->execute([$u['id'], json_encode(['days_after' => 7])]);
    $log[] = "1-week follow-up sent to {$u['email']}";
}

// 4. Subscription cancelled — check stripe subscriptions table
$stmt = db()->prepare("SELECT s.user_id, u.email, u.name FROM subscriptions s JOIN users u ON s.user_id = u.id WHERE s.status = 'canceled' AND s.current_period_end < datetime('now') AND s.id NOT IN (SELECT details FROM dsr_log WHERE request_type = 'cancellation_followup' AND created_at > datetime('now', '-30 days'))");
$stmt->execute();
$cancelled = $stmt->fetchAll();
foreach ($cancelled as $c) {
    $subject = "We'd Love to Have You Back, {$c['name']}";
    $body = "Hey {$c['name']},\n\nWe noticed you cancelled your ContractPeer subscription. Your analysis history is saved — if you ever need contract review again, you can reactivate anytime.\n\nhttps://contractpeer.com/pricing.php\n\nQuestions or feedback? Reply to this email — we read every response.\n\nBest,\nThe ContractPeer Team";
    $result = send_email($c['email'], $subject, nl2br($body), $body);
    $stmt2 = db()->prepare("INSERT INTO dsr_log (user_id, request_type, details, status) VALUES (?, 'cancellation_followup', ?, 'sent')");
    $stmt2->execute([$c['user_id'], json_encode(['subscription_id' => $c['id'] ?? ''])]);
    $log[] = "Cancellation follow-up sent to {$c['email']}";
}

// Return log
echo json_encode(['checked' => date('c'), 'actions' => $log], JSON_PRETTY_PRINT);
