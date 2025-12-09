<?php
/**
 * CleverCron Plugin Installation
 */

require_once __DIR__ . '/serverlib/init.inc.php';
require_once __DIR__ . '/plugins/tccrn.plugin.php';

echo "=== CleverCron Plugin Installation ===\n\n";

// Create plugin instance
$plugin = new TCCronPlugin();

echo "Plugin: " . $plugin->name . " v" . $plugin->version . "\n";
echo "Autor: " . $plugin->author . "\n\n";

echo "Starte Installation...\n";

try {
    $result = $plugin->Install();
    
    if ($result) {
        echo "✅ Installation erfolgreich!\n\n";
        
        // Verify tables
        echo "Prüfe Tabellen:\n";
        $res = $db->Query("SHOW TABLES LIKE 'bm60_tccrn%'");
        while ($row = $res->FetchArray(MYSQLI_NUM)) {
            echo "  ✅ " . $row[0] . "\n";
        }
        
        echo "\n✅ CleverCron Plugin erfolgreich installiert!\n";
    } else {
        echo "❌ Installation fehlgeschlagen!\n";
    }
} catch (Exception $e) {
    echo "❌ Fehler bei Installation: " . $e->getMessage() . "\n";
}
