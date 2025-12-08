<?php
/*
 * EXACT ADMIN ROW CHECK
 * Shows EXACTLY what's in $adminRow when you access a plugin page
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

header('Content-Type: text/plain; charset=utf-8');

echo "=== EXACT \$adminRow ANALYSIS ===\n\n";

echo "1. \$adminRow['type'] = " . var_export($adminRow['type'], true) . "\n";
echo "   Is 0? " . ($adminRow['type'] == 0 ? "YES" : "NO") . "\n\n";

echo "2. isset(\$adminRow['privileges']) = " . (isset($adminRow['privileges']) ? "YES" : "NO") . "\n";

if(isset($adminRow['privileges'])) {
    echo "   Type: " . gettype($adminRow['privileges']) . "\n";
    
    if(is_array($adminRow['privileges'])) {
        echo "   Array keys: " . implode(', ', array_keys($adminRow['privileges'])) . "\n\n";
        
        echo "3. isset(\$adminRow['privileges']['plugins']) = " . (isset($adminRow['privileges']['plugins']) ? "YES" : "NO") . "\n";
        
        if(isset($adminRow['privileges']['plugins'])) {
            echo "   Type: " . gettype($adminRow['privileges']['plugins']) . "\n";
            
            if(is_array($adminRow['privileges']['plugins'])) {
                echo "   Plugins in array:\n";
                foreach($adminRow['privileges']['plugins'] as $plugin => $val) {
                    echo "     - $plugin: " . var_export($val, true) . "\n";
                }
                echo "\n";
                
                $pluginName = 'ModernFrontendPlugin';
                echo "4. isset(\$adminRow['privileges']['plugins']['$pluginName']) = " . (isset($adminRow['privileges']['plugins'][$pluginName]) ? "YES" : "NO") . "\n\n";
            }
        }
    }
} else {
    echo "   \$adminRow['privileges'] is NOT SET!\n\n";
}

echo "=== EXACT PERMISSION CHECK (like plugin.page.php does) ===\n\n";
$pluginName = 'ModernFrontendPlugin';
$_REQUEST['plugin'] = $pluginName;

// EXACT same check as plugin.page.php line 37
$condition1 = ($adminRow['type']==0);
$condition2 = (isset($adminRow['privileges']['plugins']) && isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']]));
$finalResult = ($condition1 || $condition2);

echo "Condition 1: \$adminRow['type']==0\n";
echo "  Result: " . ($condition1 ? "TRUE" : "FALSE") . "\n\n";

echo "Condition 2: isset(\$adminRow['privileges']['plugins']) && isset(\$adminRow['privileges']['plugins']['$pluginName'])\n";
echo "  Result: " . ($condition2 ? "TRUE" : "FALSE") . "\n\n";

echo "Final: Condition1 OR Condition2\n";
echo "  Result: " . ($finalResult ? "TRUE" : "FALSE") . "\n\n";

echo "Negated (what plugin.page.php checks): !(\$finalResult)\n";
echo "  Result: " . (!$finalResult ? "TRUE (triggers Unauthorized)" : "FALSE (allows access)") . "\n\n";

if(!$finalResult) {
    echo "❌ UNAUTHORIZED - This is why you see the error!\n\n";
    
    echo "=== WHY? ===\n";
    if(!$condition1) {
        echo "- Admin type is NOT 0 (is {$adminRow['type']})\n";
    }
    if(!$condition2) {
        echo "- Plugin permission is NOT set in privileges array\n";
        if(!isset($adminRow['privileges']['plugins'])) {
            echo "  → privileges['plugins'] array doesn't exist\n";
        } else if(!isset($adminRow['privileges']['plugins'][$pluginName])) {
            echo "  → '$pluginName' key doesn't exist in plugins array\n";
        }
    }
} else {
    echo "✅ AUTHORIZED - Access should be granted\n";
}

echo "\n=== RAW \$adminRow DUMP ===\n";
print_r($adminRow);
?>
