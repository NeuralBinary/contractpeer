<?php
/**
 * ContractPeer - Configuration
 * Secrets loaded from environment at runtime, never hardcoded.
 */

// Load environment variables FIRST, before any config constants
require_once __DIR__ . '/env.php';

// Database (SQLite for MVP - native to Hostinger shared hosting)
define('DB_PATH', __DIR__ . '/../storage/contractpeer.db');
define('STORAGE_PATH', __DIR__ . '/../storage');
define('UPLOAD_PATH', __DIR__ . '/../storage/uploads');

// Stripe (loaded from environment)
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: '');
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');

// LLM API (loaded from environment)
define('LLM_PROVIDER', getenv('LLM_PROVIDER') ?: 'openai');
define('LLM_API_KEY', getenv('LLM_API_KEY') ?: '');
define('LLM_MODEL', getenv('LLM_MODEL') ?: 'gpt-4o');

// App settings
define('APP_NAME', 'ContractPeer');
define('APP_DOMAIN', 'contractpeer.com');
define('APP_URL', getenv('APP_URL') ?: 'https://contractpeer.com');
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'support@contractpeer.com');

// Pricing tiers
define('PRICING', [
    'starter' => ['price' => 49, 'contracts' => 10, 'name' => 'Starter'],
    'professional' => ['price' => 99, 'contracts' => 50, 'name' => 'Professional'],
    'firm' => ['price' => 199, 'contracts' => 200, 'name' => 'Firm'],
]);

// Refund thresholds (per AGENTS.md - blended ARPU $94, 2x=$188 capped at $150)
define('REFUND_THRESHOLD_PER_TRANSACTION', 150);
define('REFUND_THRESHOLD_DAILY_CUMULATIVE', 450);

// Session
define('SESSION_LIFETIME', 86400 * 30); // 30 days

// Error reporting
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('UTC');

// Include core files
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/stripe.php';
require_once __DIR__ . '/llm.php';
require_once __DIR__ . '/file_processor.php';
require_once __DIR__ . '/email.php';
