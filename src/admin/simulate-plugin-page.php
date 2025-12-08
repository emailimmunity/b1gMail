<?php
/**
 * SIMULATE plugin.page.php logic EXACTLY
 * This shows EXACTLY what happens
 */

// EXACTLY like plugin.page.php line 22
include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

// Simulate plugin request
$_REQUEST['plugin'] = 'ModernFrontendPlugin';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>SIMULATION OF plugin.page.php</h1>";
echo "<pre>";

echo "=== STEP 1: Check if plugin exists ===\n";
if(!isset($plugins->_plugins[$_REQUEST['plugin']])) {
    echo "❌ FAIL: Plugin not found!\n";
    exit;
}
echo "✅ Plugin found\n\n";

echo "=== STEP 2: Check if plugin supports admin_pages ===\n";
if(!$plugins->getParam('admin_pages', $_REQUEST['plugin'])) {
    echo "❌ FAIL: Plugin does not support admin pages!\n";
    exit;
}
echo "✅ Plugin supports admin pages\n\n";

echo "=== STEP 3: Check permissions (THE CRITICAL CHECK) ===\n\n";

echo "\$adminRow data:\n";
echo "  Username: {$adminRow['username']}\n";
echo "  Admin ID: {$adminRow['adminid']}\n";
echo "  Type: {$adminRow['type']}\n\n";

echo "Checking \$adminRow['privileges']:\n";
if(!isset($adminRow['privileges'])) {
    echo "  ❌ NOT SET!\n\n";
} else {
    echo "  ✅ Is set\n";
    echo "  Type: " . gettype($adminRow['privileges']) . "\n";
    
    if(is_array($adminRow['privileges'])) {
        echo "  Keys: " . implode(', ', array_keys($adminRow['privileges'])) . "\n\n";
        
        if(isset($adminRow['privileges']['plugins'])) {
            echo "  'plugins' key exists: ✅\n";
            echo "  Plugins: " . implode(', ', array_keys($adminRow['privileges']['plugins'])) . "\n\n";
            
            if(isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']])) {
                echo "  '{$_REQUEST['plugin']}' in plugins: ✅\n\n";
            } else {
                echo "  '{$_REQUEST['plugin']}' in plugins: ❌ NOT FOUND!\n\n";
            }
        } else {
            echo "  'plugins' key: ❌ NOT FOUND!\n\n";
        }
    } else {
        echo "  ❌ Is not an array!\n\n";
    }
}

echo "=== THE EXACT CHECK FROM plugin.page.php line 37 ===\n\n";

// EXACT condition from plugin.page.php
$condition = !($adminRow['type']==0 || (isset($adminRow['privileges']['plugins']) && isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']])));

echo "Condition breakdown:\n";
echo "  \$adminRow['type']==0: " . ($adminRow['type']==0 ? "TRUE" : "FALSE") . "\n";
echo "  isset(\$adminRow['privileges']['plugins']): " . (isset($adminRow['privileges']['plugins']) ? "TRUE" : "FALSE") . "\n";

if(isset($adminRow['privileges']['plugins'])) {
    echo "  isset(\$adminRow['privileges']['plugins']['{$_REQUEST['plugin']}']): " . (isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']]) ? "TRUE" : "FALSE") . "\n";
}

echo "\n  Condition 1 OR Condition 2: " . (($adminRow['type']==0 || (isset($adminRow['privileges']['plugins']) && isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']]))) ? "TRUE" : "FALSE") . "\n";
echo "  Negated (what triggers error): " . ($condition ? "TRUE (=UNAUTHORIZED)" : "FALSE (=ALLOWED)") . "\n\n";

if($condition) {
    echo "❌❌❌ RESULT: UNAUTHORIZED! ❌❌❌\n\n";
    echo "=== WHY? ===\n";
    
    if($adminRow['type'] != 0) {
        echo "- Admin type is not 0 (is {$adminRow['type']})\n";
    } else {
        echo "- Admin type IS 0 (Superadmin)\n";
    }
    
    if(!isset($adminRow['privileges']['plugins'])) {
        echo "- privileges['plugins'] does NOT exist\n";
    } else if(!isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']])) {
        echo "- Plugin NOT in privileges['plugins'] array\n";
        echo "- Available plugins: " . implode(', ', array_keys($adminRow['privileges']['plugins'])) . "\n";
    }
    
} else {
    echo "✅✅✅ RESULT: ACCESS GRANTED! ✅✅✅\n\n";
    echo "The plugin page should load normally!\n";
}

echo "</pre>";
?>
