<?php
define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== PLUGIN SYSTEM CHECK ===\n\n";

echo "1. Checking global \$plugins object...\n";
if(isset($plugins)) {
    echo "   ✓ \$plugins exists\n";
    echo "   Class: " . get_class($plugins) . "\n\n";
    
    echo "2. Loaded plugins:\n";
    if(method_exists($plugins, 'GetPlugins')) {
        $loadedPlugins = $plugins->GetPlugins();
        echo "   Found " . count($loadedPlugins) . " plugins:\n\n";
        
        foreach($loadedPlugins as $pluginName => $pluginData) {
            echo "   - $pluginName\n";
            if(is_array($pluginData)) {
                if(isset($pluginData['name'])) echo "     Name: {$pluginData['name']}\n";
                if(isset($pluginData['version'])) echo "     Version: {$pluginData['version']}\n";
                if(isset($pluginData['type'])) echo "     Type: {$pluginData['type']}\n";
                if(isset($pluginData['id'])) echo "     ID: {$pluginData['id']}\n";
            }
            echo "\n";
        }
    } else {
        echo "   ✗ GetPlugins() method not found\n";
    }
    
    echo "3. Plugin methods:\n";
    $methods = get_class_methods($plugins);
    foreach($methods as $method) {
        if(strpos($method, 'plugin') !== false || strpos($method, 'Plugin') !== false) {
            echo "   - $method\n";
        }
    }
    
} else {
    echo "   ✗ \$plugins does NOT exist\n";
}

echo "\n4. Checking plugin directory:\n";
$pluginDir = B1GMAIL_DIR . 'plugins/';
echo "   Path: $pluginDir\n";

if(is_dir($pluginDir)) {
    $files = scandir($pluginDir);
    $phpFiles = array_filter($files, function($file) {
        return substr($file, -4) === '.php';
    });
    
    echo "   Found " . count($phpFiles) . " PHP files:\n\n";
    
    foreach($phpFiles as $file) {
        $fullPath = $pluginDir . $file;
        $size = filesize($fullPath);
        echo "   - $file ($size bytes)\n";
        
        // Check for syntax errors
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($fullPath) . " 2>&1", $output, $return);
        
        if($return === 0) {
            echo "     ✓ No syntax errors\n";
        } else {
            echo "     ✗ SYNTAX ERROR:\n";
            foreach($output as $line) {
                echo "       $line\n";
            }
        }
        echo "\n";
    }
} else {
    echo "   ✗ Plugin directory does NOT exist\n";
}

echo "\n=== CHECK COMPLETE ===\n";
