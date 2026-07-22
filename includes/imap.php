<?php
/**
 * ContractPeer - IMAP Email Reading
 * Reads customer support emails from the inbox.
 * Uses raw socket IMAP with SASL-IR PLAIN authentication.
 */

function imap_connect_contractpeer() {
    $host = getenv('IMAP_HOST') ?: 'imap.titan.email';
    $port = getenv('IMAP_PORT') ?: 993;
    $user = getenv('SMTP_USER') ?: 'support@contractpeer.com';
    $pass = getenv('SMTP_PASS') ?: '';
    
    if (!$pass) {
        return ['error' => 'IMAP credentials not configured'];
    }
    
    // Connect via SSL socket
    $remote = "ssl://{$host}:{$port}";
    $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    
    if (!$socket) {
        return ['error' => "IMAP connection failed: {$errstr}"];
    }
    
    // Read greeting
    $greeting = fgets($socket, 515);
    if (strpos($greeting, 'OK') === false) {
        fclose($socket);
        return ['error' => 'IMAP server not ready'];
    }
    
    // SASL-IR PLAIN authentication (single command)
    $auth_string = base64_encode("\0{$user}\0{$pass}");
    $tag = 'A001';
    fputs($socket, "{$tag} AUTHENTICATE PLAIN {$auth_string}\r\n");
    $response = fgets($socket, 515);
    
    if (strpos($response, 'OK') === false) {
        fclose($socket);
        return ['error' => 'IMAP auth failed: ' . $response];
    }
    
    return ['socket' => $socket];
}

function imap_command($socket, $command) {
    static $tag_num = 1;
    $tag = 'A' . str_pad(++$tag_num, 3, '0', STR_PAD_LEFT);
    fputs($socket, "{$tag} {$command}\r\n");
    
    $response = '';
    while (true) {
        $line = fgets($socket, 8192);
        $response .= $line;
        if (strpos($line, $tag) === 0) {
            break;
        }
    }
    
    return $response;
}

function imap_select_inbox($socket) {
    return imap_command($socket, 'SELECT INBOX');
}

function imap_search_unseen($socket) {
    $response = imap_command($socket, 'SEARCH UNSEEN');
    if (preg_match('/\* SEARCH (.+)/', $response, $matches)) {
        return array_filter(explode(' ', trim($matches[1])));
    }
    return [];
}

function imap_fetch_headers($socket, $uid) {
    $response = imap_command($socket, "FETCH {$uid} (BODY.PEEK[HEADER.FIELDS (SUBJECT FROM DATE)])");
    return $response;
}

function imap_fetch_body($socket, $uid) {
    $response = imap_command($socket, "FETCH {$uid} (BODY.PEEK[TEXT])");
    return $response;
}

function imap_mark_seen($socket, $uid) {
    return imap_command($socket, "STORE {$uid} +FLAGS (\\Seen)");
}

function imap_disconnect($socket) {
    fputs($socket, "A999 LOGOUT\r\n");
    fclose($socket);
}

function get_unread_support_emails() {
    $conn = imap_connect_contractpeer();
    if (isset($conn['error'])) {
        return $conn;
    }
    
    $socket = $conn['socket'];
    imap_select_inbox($socket);
    $unseen = imap_search_unseen($socket);
    
    $emails = [];
    foreach ($unseen as $uid) {
        $headers = imap_fetch_headers($socket, $uid);
        
        // Parse subject, from, date from headers
        $subject = '';
        $from = '';
        $date = '';
        if (preg_match('/Subject:\s*(.+)/i', $headers, $m)) $subject = trim($m[1]);
        if (preg_match('/From:\s*(.+)/i', $headers, $m)) $from = trim($m[1]);
        if (preg_match('/Date:\s*(.+)/i', $headers, $m)) $date = trim($m[1]);
        
        // Skip automated emails (bounces, delivery failures, our own emails)
        if (stripos($from, 'contractpeer.com') !== false) continue;
        if (stripos($from, 'mailer-daemon') !== false) continue;
        if (stripos($from, 'postmaster') !== false) continue;
        
        $body = imap_fetch_body($socket, $uid);
        // Extract plain text from body
        $body_text = '';
        if (preg_match('/\{(\d+)\}\r\n(.+)/s', $body, $m)) {
            $body_text = substr($m[2], 0, intval($m[1]));
        }
        
        $emails[] = [
            'uid' => $uid,
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
            'body_preview' => substr($body_text, 0, 500)
        ];
        
        imap_mark_seen($socket, $uid);
    }
    
    imap_disconnect($socket);
    return $emails;
}
