<?php
// load .env from project root if present
$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!defined($key)) define($key, $value);
        }
    }
}

// fallback database defaults
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'blog_db');

// project root directory on server
define('ROOT_PATH', dirname(__DIR__));

// auto-detect BASE_URL if not explicitly set in .env
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $appRoot = rtrim(str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH), '/');

    $basePath = '';
    if (!empty($docRoot) && !empty($appRoot) && strpos($appRoot, $docRoot) === 0) {
        $basePath = substr($appRoot, strlen($docRoot));
    }
    $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
    define('BASE_URL', $basePath);
}
