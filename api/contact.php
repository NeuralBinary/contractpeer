<?php
/**
 * ContractPeer - Contact Form API
 * Accepts form submissions and sends email to support@contractpeer.com
 */
require_once __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if (!$name || !$email || !$message) {
    json_response(['error' => 'Name, email, and message are required.'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Invalid email address.'], 400);
}
if (strlen($message) < 10) {
    json_response(['error' => 'Message must be at least 10 characters.'], 400);
}

// Send to support inbox
$full_subject = $subject ? "Contact Form: {$subject}" : "Contact Form: Message from {$name}";
$body = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\nMessage:\n{$message}\n\n---\nSent via contractpeer.com contact form";

$result = send_email(SUPPORT_EMAIL, $full_subject, nl2br(htmlspecialchars($body)), $body);

if (isset($result['success'])) {
    json_response(['success' => true, 'message' => 'Thanks! Your message has been sent. We\'ll respond within 24 hours.']);
} else {
    json_response(['error' => 'Failed to send message. Please try again or email us directly.'], 500);
}
