<?php
header('Content-Type: text/plain');

echo "=== HOMEPAGE DIAGNOSIS ===\n\n";

echo "1. Making request to homepage...\n";
$ch = curl_init('http://localhost/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "   HTTP Code: $httpCode\n";
echo "   Body length: " . strlen($body) . " bytes\n\n";

echo "2. Response Headers:\n";
$headerLines = explode("\n", $headers);
foreach($headerLines as $line) {
    echo "   $line\n";
}

echo "\n3. Body content:\n";
if(strlen($body) === 0) {
    echo "   ✗ BODY IS EMPTY!\n";
} else if(strlen($body) < 100) {
    echo "   Body:\n";
    echo "   " . str_replace("\n", "\n   ", $body) . "\n";
} else {
    echo "   First 500 chars:\n";
    echo "   " . substr($body, 0, 500) . "\n";
}

echo "\n4. Checking PHP errors...\n";
$errorLog = '/var/log/apache2/error.log';
if(file_exists($errorLog)) {
    $errors = shell_exec("tail -n 20 $errorLog | grep -i 'PHP\\|Fatal\\|Error'");
    if(!empty($errors)) {
        echo "   Recent errors:\n";
        echo "   " . str_replace("\n", "\n   ", trim($errors)) . "\n";
    } else {
        echo "   No recent PHP errors found\n";
    }
} else {
    echo "   Error log not accessible\n";
}

echo "\n5. Checking if index.php is accessible...\n";
$indexFile = '/var/www/html/index.php';
if(file_exists($indexFile)) {
    echo "   ✓ index.php exists\n";
    echo "   Size: " . filesize($indexFile) . " bytes\n";
    echo "   Readable: " . (is_readable($indexFile) ? 'YES' : 'NO') . "\n";
    
    // Syntax check
    $output = [];
    exec("php -l $indexFile 2>&1", $output);
    echo "   Syntax: " . implode(' ', $output) . "\n";
} else {
    echo "   ✗ index.php NOT FOUND\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
