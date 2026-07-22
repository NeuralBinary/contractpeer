<?php
/**
 * ContractPeer - Email Service
 * Sends transactional emails via SMTP (Titan email).
 * Credentials loaded from environment, never hardcoded.
 */

function send_email($to, $subject, $html_body, $text_body = '') {
    $smtp_host = getenv('SMTP_HOST') ?: 'smtp.titan.email';
    $smtp_port = getenv('SMTP_PORT') ?: 465;
    $smtp_user = getenv('SMTP_USER') ?: 'support@contractpeer.com';
    $smtp_pass = getenv('SMTP_PASS') ?: '';
    $from_name = getenv('SMTP_FROM_NAME') ?: 'ContractPeer';
    $from_email = getenv('SMTP_FROM_EMAIL') ?: 'support@contractpeer.com';
    
    if (!$smtp_pass) {
        error_log("Email not sent: SMTP_PASS not configured");
        return ['error' => 'SMTP not configured'];
    }
    
    // Build the email
    $boundary = md5(time());
    $headers = [
        "MIME-Version: 1.0",
        "From: {$from_name} <{$from_email}>",
        "To: {$to}",
        "Subject: {$subject}",
        "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
    ];
    
    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= ($text_body ?: strip_tags($html_body)) . "\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $html_body . "\r\n";
    $body .= "--{$boundary}--\r\n";
    
    // Connect via SMTP SSL
    $remote = "{$smtp_host}:{$smtp_port}";
    $context = stream_context_create();
    $socket = stream_socket_client("ssl://{$smtp_host}:{$smtp_port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    
    if (!$socket) {
        error_log("SMTP connection failed: {$errstr} ({$errno})");
        return ['error' => 'SMTP connection failed'];
    }
    
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) !== '220') {
        fclose($socket);
        return ['error' => 'SMTP not ready: ' . $response];
    }
    
    // EHLO
    fputs($socket, "EHLO contractpeer.com\r\n");
    $response = '';
    while (substr($response, 3, 1) !== ' ') {
        $response = fgets($socket, 515);
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_user) . "\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) !== '235') {
        fclose($socket);
        return ['error' => 'SMTP auth failed: ' . $response];
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM:<{$from_email}>\r\n");
    $response = fgets($socket, 515);
    
    // RCPT TO
    fputs($socket, "RCPT TO:<{$to}>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) !== '250') {
        fclose($socket);
        return ['error' => 'SMTP recipient rejected: ' . $response];
    }
    
    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    
    // Send headers and body
    fputs($socket, implode("\r\n", $headers) . "\r\n\r\n");
    fputs($socket, $body . "\r\n.\r\n");
    $response = fgets($socket, 515);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    if (substr($response, 0, 3) === '250') {
        return ['success' => true];
    }
    
    return ['error' => 'SMTP send failed: ' . $response];
}

/**
 * Send welcome email to new users
 */
function send_welcome_email($email, $name = '') {
    $first_name = $name ?: 'there';
    $subject = 'Welcome to ContractPeer — Your Free Trial Has Started';
    $html = email_template($subject, "
        <h2>Welcome to ContractPeer, {$first_name}!</h2>
        <p>Your 14-day free trial has started. You can now analyze up to 3 contracts for free — no credit card required.</p>
        <p><a href='https://contractpeer.com/dashboard.php' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;'>Analyze Your First Contract</a></p>
        <p>Here's what you can do:</p>
        <ul>
            <li>Upload contracts in PDF, DOCX, or TXT format</li>
            <li>Get instant AI-powered risk analysis across 15+ categories</li>
            <li>Review severity ratings, plain-language explanations, and recommended actions</li>
            <li>Track your analysis history</li>
        </ul>
        <p>Your trial expires in 14 days. After that, you can upgrade to a paid plan starting at $49/month.</p>
        <p>Questions? Reply to this email or contact us anytime.</p>
        <p>Best regards,<br>The ContractPeer Team</p>
    ");
    return send_email($email, $subject, $html);
}

/**
 * Send trial expiration warning
 */
function send_trial_warning_email($email, $days_left) {
    $subject = "Your ContractPeer Trial Expires in {$days_left} Days";
    $html = email_template($subject, "
        <h2>Your Free Trial is Ending Soon</h2>
        <p>Your ContractPeer free trial expires in {$days_left} days. Don't lose access to AI-powered contract review.</p>
        <p><a href='https://contractpeer.com/pricing.php' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;'>View Plans & Upgrade</a></p>
        <p>Plans start at just $49/month for 10 contract analyses.</p>
        <p>Best regards,<br>The ContractPeer Team</p>
    ");
    return send_email($email, $subject, $html);
}

/**
 * Send payment receipt
 */
function send_receipt_email($email, $amount, $plan_name, $period) {
    $subject = "ContractPeer — Payment Receipt";
    $html = email_template($subject, "
        <h2>Payment Received — Thank You!</h2>
        <p>We've received your subscription payment. Here are the details:</p>
        <table style='border-collapse:collapse;width:100%;max-width:400px;'>
            <tr><td style='padding:8px;border:1px solid #e5e7eb;font-weight:600;'>Plan:</td><td style='padding:8px;border:1px solid #e5e7eb;'>{$plan_name}</td></tr>
            <tr><td style='padding:8px;border:1px solid #e5e7eb;font-weight:600;'>Amount:</td><td style='padding:8px;border:1px solid #e5e7eb;'>\${$amount}</td></tr>
            <tr><td style='padding:8px;border:1px solid #e5e7eb;font-weight:600;'>Billing Period:</td><td style='padding:8px;border:1px solid #e5e7eb;'>{$period}</td></tr>
        </table>
        <p>You can manage your subscription anytime from your <a href='https://contractpeer.com/dashboard.php'>dashboard</a>.</p>
        <p>Best regards,<br>The ContractPeer Team</p>
    ");
    return send_email($email, $subject, $html);
}

/**
 * Send support reply
 */
function send_support_reply($email, $original_subject, $reply_text) {
    $subject = "Re: {$original_subject}";
    $html = email_template($subject, "
        <h2>ContractPeer Support</h2>
        <p>{$reply_text}</p>
        <p>Best regards,<br>The ContractPeer Team</p>
        <p style='font-size:0.85rem;color:#6b7280;'>Reply to this email if you need further assistance.</p>
    ");
    return send_email($email, $subject, $html);
}

/**
 * Base email template
 */
function email_template($title, $body) {
    return "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f9fafb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;'>
<table style='width:100%;max-width:600px;margin:0 auto;background:#fff;padding:32px;border-radius:12px;'>
<tr><td>
<h1 style='color:#111827;font-size:1.5rem;margin:0 0 8px;'>ContractPeer</h1>
<p style='color:#6b7280;font-size:0.85rem;margin:0 0 24px;border-bottom:1px solid #e5e7eb;padding-bottom:16px;'>AI-Powered Contract Review</p>
{$body}
<p style='color:#9ca3af;font-size:0.8rem;margin-top:32px;border-top:1px solid #e5e7eb;padding-top:16px;'>
ContractPeer (contractpeer.com) · <a href='https://contractpeer.com' style='color:#2563eb;'>Visit Website</a> · <a href='https://contractpeer.com/legal/privacy.php' style='color:#2563eb;'>Privacy Policy</a>
</p>
</td></tr>
</table>
</body>
</html>";
}
