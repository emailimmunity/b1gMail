<?php
define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== PLUGIN DATABASE DIAGNOSIS ===\n\n";

// Check mods table
echo "1. Checking bm60_mods table for 'pop3acc':\n";
$res = $db->Query('SELECT * FROM {pre}mods WHERE modname = ?', 'pop3acc');
echo "   Found " . $res->RowCount() . " row(s)\n\n";

while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    echo "   Entry:\n";
    foreach($row as $key => $value) {
        echo "     $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
    echo "\n";
}
$res->Free();

// Check for all plugins
echo "2. All entries in mods table:\n";
$res = $db->Query('SELECT modname, installed, paused FROM {pre}mods ORDER BY modname');
echo "   Total: " . $res->RowCount() . " plugins\n\n";

while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    $status = $row['installed'] == 1 ? 'INSTALLED' : 'NOT INSTALLED';
    if($row['paused'] == 1) $status .= ' (PAUSED)';
    echo "   - {$row['modname']}: $status\n";
}
$res->Free();

// Check table structure
echo "\n3. Table structure:\n";
$res = $db->Query('DESCRIBE {pre}mods');
while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    echo "   {$row['Field']}: {$row['Type']} ";
    if($row['Key'] == 'PRI') echo "[PRIMARY KEY] ";
    if($row['Null'] == 'NO') echo "[NOT NULL] ";
    if($row['Default']) echo "[DEFAULT: {$row['Default']}]";
    echo "\n";
}
$res->Free();

echo "\n=== DIAGNOSIS COMPLETE ===\n";
