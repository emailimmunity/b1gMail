<?php
/**
 * SubdomainManager Plugin - Isolated Debug Environment
 * 
 * This script provides a safe sandbox for debugging the SubdomainManager plugin
 * without affecting the main application or healthcheck endpoints.
 * 
 * Usage: http://localhost:8095/tools/subdomain-debug.php
 */

// Maximum error visibility
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/var/log/b1gmail/subdomain-debug.log');

// Prevent any output buffering
while (ob_get_level()) {
    ob_end_clean();
}

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>SubdomainManager Debug</title>\n";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
echo ".step{margin:10px 0;padding:10px;background:#2d2d2d;border-left:3px solid #0e639c;}";
echo ".success{border-left-color:#4ec9b0;}.error{border-left-color:#f44747;}";
echo "h1{color:#569cd6;}h2{color:#4ec9b0;}</style>\n</head>\n<body>\n";

echo "<h1>🔧 SubdomainManager Plugin Debug Session</h1>\n";
echo "<p>Started: " . date('Y-m-d H:i:s') . "</p>\n";

// Step 1: Check environment
echo "<div class='step success'><h2>Step 1: Environment Check</h2>\n";
echo "PHP Version: " . phpversion() . "<br>\n";
echo "Working Directory: " . getcwd() . "<br>\n";
echo "Script: " . __FILE__ . "<br>\n";
echo "</div>\n";
flush();

// Step 2: Load core system
echo "<div class='step'><h2>Step 2: Loading Core System</h2>\n";
try {
    $rootPath = dirname(__DIR__);
    chdir($rootPath);
    echo "Changed to root: " . getcwd() . "<br>\n";
    
    // Load autoloader
    if (file_exists('./vendor/autoload.php')) {
        require_once './vendor/autoload.php';
        echo "✅ Autoloader loaded<br>\n";
    } else {
        throw new Exception("Autoloader not found");
    }
    
    // Load config
    if (file_exists('./config.inc.php')) {
        require_once './config.inc.php';
        echo "✅ Config loaded<br>\n";
    } else {
        throw new Exception("Config not found");
    }
    
    echo "</div>\n";
    flush();
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
    echo "</body></html>";
    exit(1);
}

// Step 3: Check database
echo "<div class='step'><h2>Step 3: Database Connection</h2>\n";
try {
    if (file_exists('./serverlib/init.inc.php')) {
        // We'll test DB without full init to avoid plugin auto-loading
        $dbTest = new mysqli(
            defined('MYSQL_HOST') ? MYSQL_HOST : 'mysql',
            defined('MYSQL_USER') ? MYSQL_USER : 'b1gmail',
            defined('MYSQL_PASSWORD') ? MYSQL_PASSWORD : 'b1gmail_password',
            defined('MYSQL_DATABASE') ? MYSQL_DATABASE : 'b1gmail'
        );
        
        if ($dbTest->connect_error) {
            throw new Exception("Database connection failed: " . $dbTest->connect_error);
        }
        
        echo "✅ Database connected<br>\n";
        echo "Database: " . $dbTest->get_server_info() . "<br>\n";
        $dbTest->close();
    }
    echo "</div>\n";
    flush();
} catch (Exception $e) {
    echo "<div class='error'>❌ DB Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}

// Step 4: Check plugin files
echo "<div class='step'><h2>Step 4: SubdomainManager Plugin Files</h2>\n";
$pluginPaths = [
    'Active' => './plugins/subdomainmanager.plugin.php',
    'Disabled' => './plugins_disabled/subdomainmanager.plugin.php',
    'Backup' => './plugins_backup/subdomainmanager.plugin.php'
];

$foundPlugin = null;
foreach ($pluginPaths as $location => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ Found in <strong>$location</strong>: $path (" . round($size/1024, 2) . " KB)<br>\n";
        if (!$foundPlugin) $foundPlugin = $path;
    } else {
        echo "⚪ Not in $location<br>\n";
    }
}

if (!$foundPlugin) {
    echo "<div class='error'>❌ Plugin not found in any location!</div>\n";
    echo "</body></html>";
    exit(1);
}
echo "</div>\n";
flush();

// Step 5: Syntax check
echo "<div class='step'><h2>Step 5: Plugin Syntax Check</h2>\n";
$output = [];
$return = 0;
exec("php -l " . escapeshellarg($foundPlugin) . " 2>&1", $output, $return);
if ($return === 0) {
    echo "✅ PHP Syntax: Valid<br>\n";
    echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>\n";
} else {
    echo "<div class='error'>❌ Syntax Error:<br>\n";
    echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre></div>\n";
}
echo "</div>\n";
flush();

// Step 6: Try to load plugin (isolated)
echo "<div class='step'><h2>Step 6: Plugin Load Test (Isolated)</h2>\n";
echo "<strong>⚠️ This will attempt to load the plugin class...</strong><br><br>\n";
flush();

try {
    // Check for dependencies first
    $helpers = [
        './plugins_disabled/subdomainmanager.dns.helper.php',
        './plugins_disabled/subdomainmanager.emailadmin.helper.php',
        './plugins_disabled/subdomainmanager.keyhelp.helper.php'
    ];
    
    foreach ($helpers as $helper) {
        if (file_exists($helper)) {
            echo "Loading helper: " . basename($helper) . "<br>\n";
            flush();
            require_once $helper;
        }
    }
    
    // Now try to load the main plugin
    echo "Loading main plugin...<br>\n";
    flush();
    
    // Read plugin without executing
    $pluginContent = file_get_contents($foundPlugin);
    echo "✅ Plugin file read successfully (" . strlen($pluginContent) . " bytes)<br>\n";
    
    // Check for class definition
    if (preg_match('/class\s+(\w+)/i', $pluginContent, $matches)) {
        echo "✅ Found class: <strong>" . htmlspecialchars($matches[1]) . "</strong><br>\n";
    }
    
    // Check for common issues
    $issues = [];
    if (strpos($pluginContent, 'mysql_') !== false) {
        $issues[] = "Uses deprecated mysql_* functions";
    }
    if (strpos($pluginContent, 'each(') !== false) {
        $issues[] = "Uses deprecated each() function";
    }
    if (preg_match('/\$HTTP_(GET|POST|SERVER)_VARS/i', $pluginContent)) {
        $issues[] = "Uses deprecated HTTP_*_VARS superglobals";
    }
    
    if (!empty($issues)) {
        echo "<div class='error'><strong>⚠️ Potential Compatibility Issues:</strong><br>\n";
        foreach ($issues as $issue) {
            echo "• " . htmlspecialchars($issue) . "<br>\n";
        }
        echo "</div>\n";
    } else {
        echo "✅ No obvious compatibility issues detected<br>\n";
    }
    
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div class='error'>❌ Load Error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "Stack trace:<br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></div>\n";
}
flush();

// Step 7: Check required database tables
echo "<div class='step'><h2>Step 7: Database Schema Check</h2>\n";
try {
    $db = new mysqli(
        defined('MYSQL_HOST') ? MYSQL_HOST : 'mysql',
        defined('MYSQL_USER') ? MYSQL_USER : 'b1gmail',
        defined('MYSQL_PASSWORD') ? MYSQL_PASSWORD : 'b1gmail_password',
        defined('MYSQL_DATABASE') ? MYSQL_DATABASE : 'b1gmail'
    );
    
    $expectedTables = [
        'bm60_subdomains',
        'bm60_subdomain_templates',
        'bm60_subdomain_assignments'
    ];
    
    foreach ($expectedTables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $count = $db->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
            echo "✅ Table <strong>$table</strong> exists ($count rows)<br>\n";
        } else {
            echo "⚠️ Table <strong>$table</strong> NOT found (may need migration)<br>\n";
        }
    }
    
    $db->close();
    echo "</div>\n";
} catch (Exception $e) {
    echo "<div class='error'>❌ Schema Check Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
}
flush();

// Final summary
echo "<div class='step success'><h2>✅ Debug Session Complete</h2>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Review any errors or warnings above</li>\n";
echo "<li>Check <code>/var/log/b1gmail/subdomain-debug.log</code> for detailed errors</li>\n";
echo "<li>If syntax is OK, check for missing database tables</li>\n";
echo "<li>Test plugin activation via admin panel only after fixing issues</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<p style='margin-top:30px;padding:10px;background:#2d2d2d;'>Session ended: " . date('Y-m-d H:i:s') . "</p>\n";
echo "</body></html>";
