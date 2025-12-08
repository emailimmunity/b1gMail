<?php
define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain');

echo "=== SSL MANAGER PLUGIN DB CHECK ===\n\n";

$res = $db->Query('SELECT * FROM {pre}mods WHERE modname LIKE "%SSL%" OR modname LIKE "%ssl%"');

echo "Found " . $res->RowCount() . " matching rows:\n\n";

while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    echo "- ID: {$row['id']}\n";
    echo "  modname: {$row['modname']}\n";
    echo "  installed: {$row['installed']}\n";
    echo "  paused: {$row['paused']}\n";
    if(isset($row['signature'])) echo "  signature: {$row['signature']}\n";
    echo "\n";
}

$res->Free();

echo "=== DONE ===\n";
