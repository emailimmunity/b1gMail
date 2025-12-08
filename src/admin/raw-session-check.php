<?php
/**
 * RAW SESSION CHECK - No security, just show what's in session
 */

// Start session manually
session_name('b1gmailAdminSID');
session_start();

echo "<h1>RAW SESSION DATA</h1>";
echo "<pre>";

echo "=== SESSION INFO ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session started: YES\n\n";

echo "=== SESSION CONTENTS ===\n";
if(empty($_SESSION)) {
    echo "❌ SESSION IS EMPTY!\n";
    echo "This means you are NOT logged in to admin.\n\n";
    echo "Please:\n";
    echo "1. Open http://localhost/admin/ in THIS SAME BROWSER\n";
    echo "2. Login\n";
    echo "3. Then come back to this page\n";
} else {
    echo "✅ Session has data\n\n";
    foreach($_SESSION as $key => $value) {
        if(is_array($value) || is_object($value)) {
            echo "$key: " . print_r($value, true) . "\n";
        } else {
            echo "$key: $value\n";
        }
    }
}

echo "\n=== COOKIES ===\n";
if(empty($_COOKIE)) {
    echo "No cookies found\n";
} else {
    foreach($_COOKIE as $key => $value) {
        echo "$key: $value\n";
    }
}

echo "</pre>";

echo "<hr>";
echo "<p><a href='http://localhost/admin/'>Go to Admin Login</a></p>";
echo "<p>After logging in, <a href='javascript:location.reload()'>reload this page</a></p>";
?>
