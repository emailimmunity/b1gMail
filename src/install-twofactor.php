<?php
/**
 * Install TwoFactor Plugin
 * Creates required database tables
 */

require_once __DIR__ . '/serverlib/init.inc.php';
require_once __DIR__ . '/plugins/twofactor.plugin.php';

echo "=== TwoFactor Plugin Installation ===\n\n";

$plugin = new TwoFactorPlugin();
echo "Plugin: " . $plugin->name . " v" . $plugin->version . "\n\n";

echo "Erstelle Tabellen...\n";

// TwoFactor Plugin uses OnInstall() method
if (method_exists($plugin, 'OnInstall')) {
    $result = $plugin->OnInstall();
} elseif (method_exists($plugin, 'Install')) {
    $result = $plugin->Install();
} else {
    echo "❌ Keine Install-Methode gefunden!\n";
    exit(1);
}

if ($result) {
    echo "✅ Installation erfolgreich!\n\n";
    
    // Prüfe erstellte Tabellen
    echo "Prüfe Tabellen...\n";
    $tables = array(
        'twofactor_settings',
        'twofactor_sessions',
        'twofactor_log'
    );
    
    foreach ($tables as $table) {
        $res = $db->Query('SHOW TABLES LIKE "{pre}' . $table . '"');
        if ($res->FetchArray(MYSQLI_NUM)) {
            echo "  ✅ {$table}\n";
            
            // Zeige Tabellenstruktur
            $res2 = $db->Query('DESCRIBE {pre}' . $table);
            $fieldCount = 0;
            while ($row = $res2->FetchArray(MYSQLI_ASSOC)) {
                $fieldCount++;
            }
            echo "     → {$fieldCount} Felder\n";
            $res2->Free();
        } else {
            echo "  ❌ {$table} - FEHLT!\n";
        }
        $res->Free();
    }
    
    echo "\n✅ TwoFactor Plugin ready for use!\n";
    echo "\nNächste Schritte:\n";
    echo "1. Admin-Bereich → Plugins → TwoFactor öffnen\n";
    echo "2. 2FA für Admin-Account aktivieren\n";
    echo "3. QR-Code mit Google Authenticator/Authy scannen\n";
    echo "4. Backup-Codes sicher speichern\n";
} else {
    echo "❌ Installation fehlgeschlagen!\n";
    exit(1);
}
