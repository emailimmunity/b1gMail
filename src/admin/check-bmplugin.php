<?php
define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== BMPlugin CLASS CHECK ===\n\n";

echo "1. Checking if BMPlugin class exists...\n";
if(class_exists('BMPlugin')) {
    echo "   ✓ BMPlugin class EXISTS\n\n";
    
    echo "2. BMPlugin Methods:\n";
    $methods = get_class_methods('BMPlugin');
    foreach($methods as $method) {
        echo "   - $method\n";
    }
    
    echo "\n3. BMPlugin Properties:\n";
    $vars = get_class_vars('BMPlugin');
    foreach($vars as $var => $value) {
        echo "   - \$$var = " . var_export($value, true) . "\n";
    }
    
    echo "\n4. Trying to create test plugin class...\n";
    
    eval('
    class TestPlugin extends BMPlugin {
        var $type = "admin";
        var $name = "Test";
        var $version = "1.0";
        var $author = "Test";
        var $description = "Test";
        var $id = "test";
        var $order = 100;
        
        function OnLoad() { echo "OnLoad called\n"; }
        function OnStart() { echo "OnStart called\n"; }
    }
    ');
    
    echo "   ✓ TestPlugin class created\n\n";
    
    echo "5. Trying to instantiate TestPlugin...\n";
    try {
        $test = new TestPlugin();
        echo "   ✓ TestPlugin instantiated\n";
        echo "   - type: {$test->type}\n";
        echo "   - name: {$test->name}\n";
        echo "   - id: {$test->id}\n";
    } catch(Exception $e) {
        echo "   ✗ ERROR: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "   ✗ BMPlugin class DOES NOT EXIST\n";
    
    echo "\n2. Checking available classes:\n";
    $classes = get_declared_classes();
    $pluginClasses = array_filter($classes, function($class) {
        return stripos($class, 'plugin') !== false;
    });
    
    if(count($pluginClasses) > 0) {
        foreach($pluginClasses as $class) {
            echo "   - $class\n";
        }
    } else {
        echo "   (no plugin-related classes found)\n";
    }
}

echo "\n=== CHECK COMPLETE ===\n";
