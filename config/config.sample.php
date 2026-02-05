<?php
// Configurazione Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'brico_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Debug Mode
ini_set('display_errors', 0); // Disabilitare in produzione
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Configurazione Applicazione
// Rilevamento automatico URL base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']); 
// Rimuove eventuali slash finali
$scriptPath = rtrim($scriptPath, '/\\');

define('BASE_URL', $protocol . $domainName . $scriptPath);
define('ROOT_PATH', dirname(__DIR__));
