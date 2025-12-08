<?php
/**
 * Fix Duplicate Plugin Entry Error
 * Removes duplicate or corrupted plugin entries
 */

define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== PLUGIN DUPLICATE FIX ===\n\n";

$pluginName = isset($_GET['plugin']) ? $_GET['plugin'] : 'pop3acc';

echo "Target Plugin: $pluginName\n\n";

// Check current status
echo "1. Current status:\n";
$res = $db->Query('SELECT * FROM {pre}mods WHERE modname = ?', $pluginName);
$count = $res->RowCount();
echo "   Found $count entry/entries\n\n";

if($count > 0) {
    while($row = $res->FetchArray(MYSQLI_ASSOC)) {
        echo "   Entry details:\n";
        echo "     - modname: {$row['modname']}\n";
        echo "     - installed: {$row['installed']}\n";
        echo "     - paused: {$row['paused']}\n";
        echo "     - pos: {$row['pos']}\n";
        if(isset($row['signature'])) echo "     - signature: {$row['signature']}\n";
        echo "\n";
    }
}
$res->Free();

// Perform fix
if(isset($_GET['fix']) && $_GET['fix'] == 'yes') {
    echo "2. Performing fix:\n";
    
    // Delete the entry completely
    echo "   Deleting entry...\n";
    $db->Query('DELETE FROM {pre}mods WHERE modname = ?', $pluginName);
    echo "   ✓ Deleted: " . $db->AffectedRows() . " row(s)\n\n";
    
    // Re-insert with proper values
    echo "   Re-inserting entry...\n";
    $db->Query('INSERT INTO {pre}mods(modname, installed, paused, pos) VALUES(?, 0, 0, 0)', $pluginName);
    echo "   ✓ Inserted\n\n";
    
    // Clear cache
    echo "   Clearing plugin cache...\n";
    if(isset($cacheManager)) {
        $cacheManager->Delete('dbPlugins_v2');
        echo "   ✓ Cache cleared\n\n";
    }
    
    echo "3. Verification:\n";
    $res = $db->Query('SELECT * FROM {pre}mods WHERE modname = ?', $pluginName);
    if($res->RowCount() > 0) {
        $row = $res->FetchArray(MYSQLI_ASSOC);
        echo "   ✓ Entry exists:\n";
        echo "     - installed: {$row['installed']}\n";
        echo "     - paused: {$row['paused']}\n";
    } else {
        echo "   ✗ Entry not found!\n";
    }
    $res->Free();
    
    echo "\n=== FIX COMPLETE ===\n";
    echo "\nNow you can try to activate the plugin again!\n";
    
} else {
    echo "2. To perform the fix, add ?fix=yes to the URL\n";
    echo "   Example: fix-plugin-duplicate.php?plugin=$pluginName&fix=yes\n";
}
