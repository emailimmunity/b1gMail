<?php
define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== DIRECT PLUGIN LOAD TEST ===\n\n";

$pluginFile = B1GMAIL_DIR . 'plugins/sslmanager.plugin.php';

echo "1. Plugin file check:\n";
echo "   Path: $pluginFile\n";

if(file_exists($pluginFile)) {
    echo "   ✓ File exists\n";
    echo "   Size: " . filesize($pluginFile) . " bytes\n";
    echo "   Readable: " . (is_readable($pluginFile) ? 'YES' : 'NO') . "\n\n";
    
    echo "2. Syntax check:\n";
    $output = [];
    exec("php -l " . escapeshellarg($pluginFile) . " 2>&1", $output);
    foreach($output as $line) {
        echo "   $line\n";
    }
    echo "\n";
    
    echo "3. File contents (first 50 lines):\n";
    $lines = file($pluginFile);
    for($i = 0; $i < min(50, count($lines)); $i++) {
        printf("   %3d: %s", $i+1, $lines[$i]);
    }
    echo "\n";
    
    echo "4. Attempting to include file...\n";
    ob_start();
    try {
        $includeResult = include($pluginFile);
        $output = ob_get_clean();
        
        echo "   ✓ Include successful\n";
        echo "   Return value: " . var_export($includeResult, true) . "\n";
        
        if(!empty($output)) {
            echo "   Output during include:\n";
            echo "   " . str_replace("\n", "\n   ", $output) . "\n";
        }
        
        echo "\n5. Checking if class was defined:\n";
        if(class_exists('SSLManager_Plugin')) {
            echo "   ✓ SSLManager_Plugin class exists\n";
            
            echo "\n6. Trying to instantiate:\n";
            try {
                $instance = new SSLManager_Plugin();
                echo "   ✓ Instance created\n";
                echo "   Type: {$instance->type}\n";
                echo "   Name: {$instance->name}\n";
                echo "   ID: {$instance->id}\n";
                
                echo "\n7. Trying to call OnLoad():\n";
                $instance->OnLoad();
                echo "   ✓ OnLoad() called\n";
                
                echo "\n8. Trying to call OnStart():\n";
                $instance->OnStart();
                echo "   ✓ OnStart() called\n";
                
            } catch(Exception $e) {
                echo "   ✗ ERROR: " . $e->getMessage() . "\n";
                echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                echo "   Trace:\n" . $e->getTraceAsString() . "\n";
            }
        } else {
            echo "   ✗ SSLManager_Plugin class NOT defined\n";
            
            echo "\n   Declared classes after include:\n";
            $classes = get_declared_classes();
            $newClasses = array_filter($classes, function($class) {
                return stripos($class, 'ssl') !== false || stripos($class, 'manager') !== false;
            });
            
            if(count($newClasses) > 0) {
                foreach($newClasses as $class) {
                    echo "     - $class\n";
                }
            } else {
                echo "     (no SSL/Manager related classes found)\n";
            }
        }
        
    } catch(Exception $e) {
        ob_end_clean();
        echo "   ✗ Include FAILED\n";
        echo "   ERROR: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    echo "   ✗ File DOES NOT exist\n";
}

echo "\n=== TEST COMPLETE ===\n";
