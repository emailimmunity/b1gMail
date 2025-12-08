<?php
/**
 * DEBUG: Exact copy of plugin.page.php logic with detailed output
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

// Force plain text output
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG PLUGIN AUTHENTICATION ===\n\n";

// Simulate the request
$_REQUEST['plugin'] = 'ModernFrontendPlugin';
if(isset($_GET['page'])) {
    $_REQUEST['page'] = $_GET['page'];
}

echo "Requested Plugin: {$_REQUEST['plugin']}\n";
if(isset($_REQUEST['page'])) {
    echo "Requested Page: {$_REQUEST['page']}\n";
}
echo "\n";

echo "=== CHECKING \$adminRow ===\n\n";

echo "Username: " . (isset($adminRow['username']) ? $adminRow['username'] : 'NOT SET') . "\n";
echo "Admin ID: " . (isset($adminRow['adminid']) ? $adminRow['adminid'] : 'NOT SET') . "\n";
echo "Type: " . (isset($adminRow['type']) ? $adminRow['type'] : 'NOT SET') . "\n";
echo "\n";

echo "Checking \$adminRow['privileges']:\n";
if(!isset($adminRow['privileges'])) {
    echo "  ❌ NOT SET!\n\n";
    echo "THIS IS THE PROBLEM!\n";
    echo "\$adminRow does not contain 'privileges' key!\n\n";
} else {
    echo "  ✅ Is set\n";
    echo "  Type: " . gettype($adminRow['privileges']) . "\n";
    
    if(!is_array($adminRow['privileges'])) {
        echo "  ❌ NOT AN ARRAY!\n";
        echo "  Value: " . var_export($adminRow['privileges'], true) . "\n\n";
    } else {
        echo "  Keys: " . implode(', ', array_keys($adminRow['privileges'])) . "\n\n";
        
        if(!isset($adminRow['privileges']['plugins'])) {
            echo "  ❌ 'plugins' key NOT FOUND!\n\n";
            echo "  THIS IS THE PROBLEM!\n";
            echo "  Available keys: " . implode(', ', array_keys($adminRow['privileges'])) . "\n\n";
        } else {
            echo "  ✅ 'plugins' key exists\n";
            echo "  Type: " . gettype($adminRow['privileges']['plugins']) . "\n";
            
            if(!is_array($adminRow['privileges']['plugins'])) {
                echo "  ❌ NOT AN ARRAY!\n\n";
            } else {
                echo "  Number of plugins: " . count($adminRow['privileges']['plugins']) . "\n";
                echo "  Plugins:\n";
                foreach($adminRow['privileges']['plugins'] as $p => $v) {
                    $marker = ($p === $_REQUEST['plugin']) ? " ← REQUESTED" : "";
                    echo "    - $p: " . var_export($v, true) . "$marker\n";
                }
                echo "\n";
                
                if(!isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']])) {
                    echo "  ❌ '{$_REQUEST['plugin']}' NOT IN ARRAY!\n\n";
                    echo "  THIS IS THE PROBLEM!\n\n";
                } else {
                    echo "  ✅ '{$_REQUEST['plugin']}' IS IN ARRAY!\n\n";
                }
            }
        }
    }
}

echo "=== THE EXACT CHECK (line 37 of plugin.page.php) ===\n\n";

$condition1 = $adminRow['type']==0;
$condition2 = isset($adminRow['privileges']['plugins']) && isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']]);
$combinedCondition = $condition1 || $condition2;
$negatedCondition = !$combinedCondition;

echo "Condition 1 (\$adminRow['type']==0): " . ($condition1 ? "TRUE" : "FALSE") . "\n";
echo "Condition 2 (plugin in privileges): " . ($condition2 ? "TRUE" : "FALSE") . "\n";
echo "Combined (Condition1 OR Condition2): " . ($combinedCondition ? "TRUE" : "FALSE") . "\n";
echo "Negated (triggers error if TRUE): " . ($negatedCondition ? "TRUE" : "FALSE") . "\n\n";

if($negatedCondition) {
    echo "❌❌❌ RESULT: ACCESS DENIED (Unauthorized) ❌❌❌\n\n";
    
    echo "WHY?\n";
    if(!$condition1) {
        echo "- Type is not 0 (is: {$adminRow['type']})\n";
    }
    if(!$condition2) {
        if(!isset($adminRow['privileges']['plugins'])) {
            echo "- privileges['plugins'] does not exist\n";
        } else if(!isset($adminRow['privileges']['plugins'][$_REQUEST['plugin']])) {
            echo "- Plugin '{$_REQUEST['plugin']}' not in privileges['plugins']\n";
        }
    }
    
} else {
    echo "✅✅✅ RESULT: ACCESS GRANTED ✅✅✅\n\n";
}

echo "\n=== FULL \$adminRow DUMP ===\n\n";
print_r($adminRow);

echo "\n=== SESSION DATA ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "bm_adminLoggedIn: " . (isset($_SESSION['bm_adminLoggedIn']) && $_SESSION['bm_adminLoggedIn'] ? "YES" : "NO") . "\n";
echo "bm_adminID: " . (isset($_SESSION['bm_adminID']) ? $_SESSION['bm_adminID'] : "NOT SET") . "\n";
echo "bm_adminAuth: " . (isset($_SESSION['bm_adminAuth']) ? "SET (hash)" : "NOT SET") . "\n";
?>
