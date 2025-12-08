<?php
header('Content-Type: text/plain');

echo "=== ERROR LOG CHECK ===\n\n";

// PHP Error Log
$phpErrorLog = ini_get('error_log');
echo "PHP Error Log: $phpErrorLog\n\n";

// Apache Error Log
$apacheLog = '/var/log/apache2/error.log';
if(file_exists($apacheLog)) {
    echo "=== APACHE ERROR LOG (Last 50 lines) ===\n";
    echo shell_exec("tail -n 50 $apacheLog");
}

echo "\n\n=== PHP INFO (error settings) ===\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
