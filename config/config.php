<?php
// ============================================================
// Research Grant Management System - Configuration
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'research_grants_db');
define('DB_USER', 'root');
define('DB_PASS', '00000000');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('SITE_NAME', 'MUH Research Grant System');

// Dynamic SITE_URL detection (works in any folder name or domain)
if (!defined('SITE_URL')) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Auto-detect base folder path relative to document root
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? 'C:/AppServ/www'));
    $projRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
    $subDir = '/research/';
    if ($docRoot && $projRoot && strpos($projRoot, $docRoot) === 0) {
        $relPath = substr($projRoot, strlen($docRoot));
        $subDir = '/' . trim($relPath, '/') . '/';
        if ($subDir === '//') $subDir = '/';
    }
    define('SITE_URL', $protocol . $host . $subDir);
}
define('SITE_EMAIL', 'info@researchgrants.com');
define('SITE_PHONE', '+234 09138386156');
define('SITE_ADDRESS', 'ZOO ROAD KANO STATE NIGERIA');
define('CURRENCY_SYMBOL', 'NGN');

// Path Configuration
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', BASE_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('PROFILE_PATH', UPLOAD_PATH . 'profiles' . DIRECTORY_SEPARATOR);
define('CV_PATH', UPLOAD_PATH . 'cvs' . DIRECTORY_SEPARATOR);
define('PROPOSAL_PATH', UPLOAD_PATH . 'proposals' . DIRECTORY_SEPARATOR);
define('GUIDELINE_PATH', UPLOAD_PATH . 'guidelines' . DIRECTORY_SEPARATOR);
define('REPORT_PATH', UPLOAD_PATH . 'reports' . DIRECTORY_SEPARATOR);

// Upload Settings
define('MAX_FILE_SIZE', 10485760);
define('ALLOWED_FILE_TYPES', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif');

// Session & Security
define('SESSION_TIMEOUT', 3600);
define('TOKEN_EXPIRY', 3600);

// Timezone
date_default_timezone_set('UTC');
