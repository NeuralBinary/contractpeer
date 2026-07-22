<?php
/**
 * ContractPeer - Environment Loader
 * Loads .env file from storage directory (outside web root).
 * Falls back to getenv() for Apache SetEnv.
 */

function load_env() {
    // Try .env file in storage directory (outside public web root)
    $env_file = __DIR__ . '/../storage/.env';
    if (!file_exists($env_file)) {
        $env_file = __DIR__ . '/../.env';
    }
    
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments
            if (strpos($line, '#') === 0) continue;
            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                // Only set if not already in environment
                if (getenv($key) === false) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}

// Load environment
load_env();
