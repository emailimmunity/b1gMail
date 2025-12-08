<?php
/**
 * LIVE SESSION DEBUG - Call this in browser while logged into admin
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

echo "<h1>LIVE ADMIN SESSION DEBUG</h1>";
echo "<pre>";

echo "=== SESSION DATA ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Admin Session ID: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NOT SET') . "\n\n";

echo "=== CURRENT USER INFO ===\n";
echo "Username: " . $adminRow['username'] . "\n";
echo "Admin ID: " . $adminRow['adminid'] . "\n";
echo "Type: " . $adminRow['type'] . " (0 = Superadmin)\n\n";

echo "=== ADMIN ROW (from admin.inc.php) ===\n";
if(isset($adminRow)) {
    echo "Type: " . $adminRow['type'] . "\n";
    echo "Username: " . $adminRow['username'] . "\n\n";
    
    echo "=== PRIVILEGES ===\n";
    if(isset($adminRow['privileges'])) {
        echo "Privileges array exists: YES\n";
        
        if(isset($adminRow['privileges']['plugins'])) {
            echo "Plugins array exists: YES\n";
            echo "Plugins:\n";
            foreach($adminRow['privileges']['plugins'] as $plugin => $value) {
                echo "  - $plugin: " . ($value ? "YES" : "NO") . "\n";
            }
            
            echo "\n";
            if(isset($adminRow['privileges']['plugins']['ModernFrontendPlugin'])) {
                echo "✅ HAS ModernFrontendPlugin permission!\n";
            } else {
                echo "❌ MISSING ModernFrontendPlugin permission!\n";
            }
        } else {
            echo "❌ plugins array NOT found in privileges!\n";
            echo "Privileges structure:\n";
            print_r($adminRow['privileges']);
        }
    } else {
        echo "❌ No privileges array!\n";
    }
} else {
    echo "❌ \$adminRow is NOT SET!\n";
}

echo "\n=== PERMISSION CHECK (exactly like plugin.page.php) ===\n";
$pluginName = 'ModernFrontendPlugin';

// Exact check from plugin.page.php
$hasPermission = ($adminRow['type']==0 || 
                  (isset($adminRow['privileges']['plugins']) && 
                   isset($adminRow['privileges']['plugins'][$pluginName])));

if($hasPermission) {
    echo "✅ PERMISSION CHECK PASSED!\n";
    echo "You SHOULD be able to access the plugin!\n";
} else {
    echo "❌ PERMISSION CHECK FAILED!\n";
    echo "This is why you see 'Unauthorized'\n\n";
    
    echo "Debug info:\n";
    echo "  Type is 0? " . ($adminRow['type']==0 ? "YES" : "NO ({$adminRow['type']})") . "\n";
    echo "  privileges['plugins'] exists? " . (isset($adminRow['privileges']['plugins']) ? "YES" : "NO") . "\n";
    if(isset($adminRow['privileges']['plugins'])) {
        echo "  privileges['plugins']['$pluginName'] exists? " . (isset($adminRow['privileges']['plugins'][$pluginName]) ? "YES" : "NO") . "\n";
    }
}

echo "\n=== DATABASE CHECK ===\n";
global $db;
$result = $db->Query('SELECT adminid, username, type, privileges FROM {pre}admins WHERE adminid=?', $adminRow['adminid']);
if($row = $result->FetchArray(MYSQLI_ASSOC)) {
    echo "Database record for current admin:\n";
    echo "  Type: {$row['type']}\n";
    if(!empty($row['privileges'])) {
        $dbPrivs = unserialize($row['privileges']);
        if(isset($dbPrivs['plugins']['ModernFrontendPlugin'])) {
            echo "  ✅ Database HAS ModernFrontendPlugin permission\n";
        } else {
            echo "  ❌ Database MISSING ModernFrontendPlugin permission\n";
        }
    }
}

echo "</pre>";
?>
