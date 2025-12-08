<?php
/*
 * DATABASE PRIVILEGES ANALYSIS
 * Shows what's ACTUALLY in the database
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

header('Content-Type: text/plain; charset=utf-8');

echo "=== DATABASE PRIVILEGES ANALYSIS ===\n\n";

echo "Currently logged in as: {$adminRow['username']} (ID: {$adminRow['adminid']})\n\n";

// Query the ACTUAL database
$result = $db->Query('SELECT adminid, username, type, privileges FROM {pre}admins WHERE adminid=?', $adminRow['adminid']);

if($row = $result->FetchArray(MYSQLI_ASSOC)) {
    echo "=== DATABASE RECORD ===\n";
    echo "Admin ID: {$row['adminid']}\n";
    echo "Username: {$row['username']}\n";
    echo "Type: {$row['type']}\n";
    echo "Privileges (raw): " . (empty($row['privileges']) ? "(empty)" : "YES") . "\n\n";
    
    if(!empty($row['privileges'])) {
        echo "=== UNSERIALIZING PRIVILEGES ===\n";
        $privs = unserialize($row['privileges']);
        
        if($privs === false) {
            echo "ERROR: Failed to unserialize privileges!\n";
            echo "Raw data: " . substr($row['privileges'], 0, 200) . "...\n";
        } else {
            echo "Successfully unserialized\n";
            echo "Type: " . gettype($privs) . "\n";
            
            if(is_array($privs)) {
                echo "Top-level keys: " . implode(', ', array_keys($privs)) . "\n\n";
                
                if(isset($privs['plugins'])) {
                    echo "=== PLUGINS ARRAY ===\n";
                    echo "Type: " . gettype($privs['plugins']) . "\n";
                    
                    if(is_array($privs['plugins'])) {
                        echo "Number of plugins: " . count($privs['plugins']) . "\n";
                        echo "Plugins:\n";
                        foreach($privs['plugins'] as $plugin => $val) {
                            $check = ($plugin === 'ModernFrontendPlugin') ? " ← TARGET" : "";
                            echo "  - $plugin: " . var_export($val, true) . "$check\n";
                        }
                        
                        echo "\n";
                        if(isset($privs['plugins']['ModernFrontendPlugin'])) {
                            echo "✅ ModernFrontendPlugin IS in database\n";
                            echo "   Value: " . var_export($privs['plugins']['ModernFrontendPlugin'], true) . "\n";
                        } else {
                            echo "❌ ModernFrontendPlugin NOT in database\n";
                        }
                    }
                } else {
                    echo "❌ NO 'plugins' key in privileges array\n";
                    echo "Available keys: " . implode(', ', array_keys($privs)) . "\n";
                }
            }
        }
        
        echo "\n=== FULL PRIVILEGES DUMP ===\n";
        print_r($privs);
    }
}

echo "\n=== COMPARISON: DB vs MEMORY ===\n\n";

// Compare DB privileges with $adminRow privileges
echo "Database privileges (from query above)\n";
echo "vs\n";
echo "\$adminRow['privileges'] (loaded in memory):\n\n";

if(isset($adminRow['privileges'])) {
    if(is_array($adminRow['privileges'])) {
        echo "Type: array\n";
        echo "Keys: " . implode(', ', array_keys($adminRow['privileges'])) . "\n";
        
        if(isset($adminRow['privileges']['plugins'])) {
            echo "\nPlugins in \$adminRow:\n";
            foreach($adminRow['privileges']['plugins'] as $plugin => $val) {
                echo "  - $plugin: " . var_export($val, true) . "\n";
            }
        }
    } else {
        echo "Type: " . gettype($adminRow['privileges']) . "\n";
        echo "Value: " . var_export($adminRow['privileges'], true) . "\n";
    }
} else {
    echo "❌ \$adminRow['privileges'] is NOT SET in memory!\n";
}
?>
