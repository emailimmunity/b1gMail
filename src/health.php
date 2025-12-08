<?php
/**
 * Docker Health Check Endpoint
 * 
 * This is a minimal health check that does NOT load any plugins
 * to prevent infinite loops or plugin-related crashes during healthchecks.
 * 
 * Returns: HTTP 200 "OK" if system is operational
 */

// Minimal error suppression for health checks
error_reporting(0);
ini_set('display_errors', '0');

// No sessions, no plugins, no redirects
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
http_response_code(200);

// Simple checks
$checks = [
    'php' => true,
    'disk' => is_writable('/var/www/html/temp'),
    'config' => file_exists('/var/www/html/config.inc.php')
];

// Optional: Quick DB check (without full b1gMail init)
if (file_exists('/var/www/html/config.inc.php')) {
    require_once '/var/www/html/config.inc.php';
    
    try {
        $db = @new mysqli(
            defined('MYSQL_HOST') ? MYSQL_HOST : 'mysql',
            defined('MYSQL_USER') ? MYSQL_USER : 'b1gmail',
            defined('MYSQL_PASSWORD') ? MYSQL_PASSWORD : 'b1gmail_password',
            defined('MYSQL_DATABASE') ? MYSQL_DATABASE : 'b1gmail'
        );
        
        $checks['database'] = !$db->connect_error;
        if (!$db->connect_error) {
            $db->close();
        }
    } catch (Exception $e) {
        $checks['database'] = false;
    }
}

// Output
$allOk = !in_array(false, $checks, true);

if ($allOk) {
    echo "OK\n";
    echo "status: healthy\n";
} else {
    http_response_code(500);
    echo "UNHEALTHY\n";
    foreach ($checks as $name => $status) {
        echo "$name: " . ($status ? 'ok' : 'FAIL') . "\n";
    }
}

// No plugin loading, no init.inc.php, no redirects
exit();
