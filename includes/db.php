<?php
/**
 * ContractPeer - Database (SQLite)
 */

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    return $pdo;
}

function init_db() {
    $db = db();
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        name TEXT,
        plan TEXT DEFAULT 'free',
        stripe_customer_id TEXT,
        contracts_used INTEGER DEFAULT 0,
        contracts_limit INTEGER DEFAULT 3,
        trial_ends_at TEXT,
        referral_code TEXT UNIQUE,
        referred_by TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");

    // Add referral_code column if it doesn't exist (for existing DBs)
    try { $db->exec("ALTER TABLE users ADD COLUMN referral_code TEXT"); } catch(Exception $e) {}
    try { $db->exec("ALTER TABLE users ADD COLUMN referred_by TEXT"); } catch(Exception $e) {}

    $db->exec("CREATE TABLE IF NOT EXISTS analyses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        filename TEXT,
        contract_text TEXT,
        analysis_result TEXT,
        risk_count_high INTEGER DEFAULT 0,
        risk_count_medium INTEGER DEFAULT 0,
        risk_count_low INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS subscriptions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        stripe_subscription_id TEXT UNIQUE,
        stripe_customer_id TEXT,
        plan TEXT NOT NULL,
        status TEXT DEFAULT 'active',
        current_period_end TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        stripe_payment_intent_id TEXT,
        stripe_invoice_id TEXT,
        amount INTEGER,
        currency TEXT DEFAULT 'usd',
        status TEXT,
        description TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS refund_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        payment_id INTEGER,
        user_id INTEGER,
        stripe_refund_id TEXT,
        amount INTEGER,
        reason TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS dsr_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        request_type TEXT,
        details TEXT,
        status TEXT DEFAULT 'completed',
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function get_input() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

// Initialize on load
init_db();
