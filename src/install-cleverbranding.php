<?php
/**
 * CleverBranding Plugin Installation
 */

require_once __DIR__ . '/serverlib/init.inc.php';
require_once __DIR__ . '/plugins/tcbrn.plugin.php';

echo "=== CleverBranding Plugin Installation ===\n\n";

// Create plugin instance
$plugin = new TCBrandPlugin();

echo "Plugin: " . $plugin->name . " v" . $plugin->version . "\n";
echo "Autor: " . $plugin->author . "\n\n";

echo "Starte Installation...\n";

try {
    $result = $plugin->Install();
    
    if ($result) {
        echo "✅ Installation erfolgreich!\n\n";
        
        // Verify tables
        echo "Prüfe Tabellen:\n";
        $res = $db->Query("SHOW TABLES LIKE 'bm60_tcbrn%'");
        while ($row = $res->FetchArray(MYSQLI_NUM)) {
            echo "  ✅ " . $row[0] . "\n";
            
            // Show row count
            $countRes = $db->Query("SELECT COUNT(*) as cnt FROM " . $row[0]);
            $countRow = $countRes->FetchArray(MYSQLI_ASSOC);
            echo "     Einträge: " . $countRow['cnt'] . "\n";
        }
        
        echo "\n✅ CleverBranding Plugin erfolgreich installiert!\n";
        echo "\nNächster Schritt: Branding-Profile im Admin-Panel konfigurieren\n";
        echo "URL: http://localhost:8095/admin/\n";
        echo "Navigation: Plugins → CleverBranding\n";
    } else {
        echo "❌ Installation fehlgeschlagen!\n";
    }
} catch (Exception $e) {
    echo "❌ Fehler bei Installation: " . $e->getMessage() . "\n";
}
