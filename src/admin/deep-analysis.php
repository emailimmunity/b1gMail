<?php
/**
 * TIEFENANALYSE: Plugin Database Problem
 * Sammelt ALLE relevanten Daten für Analyse
 */

define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════════\n";
echo "    TIEFENANALYSE: PLUGIN DATABASE PROBLEM\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// SEKTION 1: DATENBANK-TABELLEN-STRUKTUR
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 1: DATENBANK-STRUKTUR                               │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "1.1 Tabelle: bm60_mods - Struktur\n";
echo "─────────────────────────────────────\n";
$res = $db->Query('DESCRIBE {pre}mods');
while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    $key = $row['Key'] ? " [{$row['Key']}]" : "";
    $null = $row['Null'] == 'NO' ? " NOT NULL" : " NULL";
    $default = $row['Default'] !== null ? " DEFAULT '{$row['Default']}'" : "";
    $extra = $row['Extra'] ? " {$row['Extra']}" : "";
    
    printf("   %-20s %-20s%s%s%s%s\n", 
        $row['Field'], 
        $row['Type'], 
        $key,
        $null,
        $default,
        $extra
    );
}
$res->Free();

echo "\n1.2 Tabellen-Indizes\n";
echo "─────────────────────────────────────\n";
$res = $db->Query('SHOW INDEXES FROM {pre}mods');
while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    printf("   Index: %-15s Column: %-15s Unique: %s\n",
        $row['Key_name'],
        $row['Column_name'],
        $row['Non_unique'] == 0 ? 'YES' : 'NO'
    );
}
$res->Free();

// ============================================================================
// SEKTION 2: AKTUELLER DATENBANK-INHALT
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 2: DATENBANK-INHALT                                 │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "2.1 ALLE Plugin-Einträge\n";
echo "─────────────────────────────────────\n";
$res = $db->Query('SELECT * FROM {pre}mods ORDER BY modname');
$totalPlugins = $res->RowCount();
echo "   Gesamt: $totalPlugins Einträge\n\n";

$installedCount = 0;
$pausedCount = 0;
$duplicates = array();

while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    if($row['installed'] == 1) $installedCount++;
    if($row['paused'] == 1) $pausedCount++;
    
    $status = [];
    if($row['installed'] == 1) $status[] = 'INSTALLED';
    if($row['paused'] == 1) $status[] = 'PAUSED';
    if(empty($status)) $status[] = 'INACTIVE';
    
    printf("   %-30s %s\n", $row['modname'], implode(', ', $status));
}
$res->Free();

echo "\n   Statistik:\n";
echo "   - Installiert: $installedCount\n";
echo "   - Pausiert: $pausedCount\n";
echo "   - Inaktiv: " . ($totalPlugins - $installedCount) . "\n";

// ============================================================================
// SEKTION 3: PROBLEM-PLUGIN DETAILANALYSE
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 3: PROBLEM-PLUGIN ANALYSE (pop3acc)                 │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

$problemPlugin = 'pop3acc';

echo "3.1 Datenbank-Einträge für '$problemPlugin'\n";
echo "─────────────────────────────────────\n";
$res = $db->Query('SELECT * FROM {pre}mods WHERE modname = ?', $problemPlugin);
$entryCount = $res->RowCount();
echo "   Anzahl Einträge: $entryCount\n\n";

if($entryCount > 1) {
    echo "   ⚠️  WARNUNG: MEHRFACH-EINTRAG ERKANNT!\n\n";
}

$entries = array();
$entryNum = 1;
while($row = $res->FetchArray(MYSQLI_ASSOC)) {
    $entries[] = $row;
    echo "   Eintrag #$entryNum:\n";
    foreach($row as $key => $value) {
        printf("     %-15s : %s\n", $key, is_null($value) ? 'NULL' : $value);
    }
    echo "\n";
    $entryNum++;
}
$res->Free();

// ============================================================================
// SEKTION 4: PLUGIN-DATEI-ANALYSE
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 4: PLUGIN-DATEI ANALYSE                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

$pluginFile = B1GMAIL_DIR . 'plugins/pop3acc.php';
echo "4.1 Plugin-Datei: $pluginFile\n";
echo "─────────────────────────────────────\n";
if(file_exists($pluginFile)) {
    echo "   ✓ Datei existiert\n";
    echo "   Größe: " . filesize($pluginFile) . " bytes\n";
    echo "   Lesbar: " . (is_readable($pluginFile) ? 'JA' : 'NEIN') . "\n";
    
    // Syntax check
    $output = array();
    $return = 0;
    exec("php -l " . escapeshellarg($pluginFile) . " 2>&1", $output, $return);
    echo "   Syntax: " . ($return === 0 ? '✓ OK' : '✗ FEHLER') . "\n";
    
    if($return !== 0) {
        echo "   Fehlerdetails:\n";
        foreach($output as $line) {
            echo "     $line\n";
        }
    }
} else {
    echo "   ✗ Datei existiert NICHT\n";
}

// ============================================================================
// SEKTION 5: PLUGIN-SYSTEM STATUS
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 5: PLUGIN-SYSTEM STATUS                             │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

echo "5.1 Geladene Plugins im System\n";
echo "─────────────────────────────────────\n";

if(isset($plugins) && is_object($plugins)) {
    echo "   ✓ Plugin-System aktiv\n";
    echo "   Klasse: " . get_class($plugins) . "\n\n";
    
    // Versuche interne Plugin-Liste zu bekommen
    $reflection = new ReflectionClass($plugins);
    
    // Private Plugins Property
    if($reflection->hasProperty('_plugins')) {
        $prop = $reflection->getProperty('_plugins');
        $prop->setAccessible(true);
        $loadedPlugins = $prop->getValue($plugins);
        
        echo "   Aktive Plugins: " . count($loadedPlugins) . "\n";
        if(isset($loadedPlugins['pop3acc'])) {
            echo "   ✓ pop3acc ist GELADEN\n";
            $pluginInfo = $loadedPlugins['pop3acc'];
            echo "     - Name: " . ($pluginInfo['name'] ?? 'N/A') . "\n";
            echo "     - Installed: " . ($pluginInfo['installed'] ? 'JA' : 'NEIN') . "\n";
            echo "     - Paused: " . ($pluginInfo['paused'] ? 'JA' : 'NEIN') . "\n";
        } else {
            echo "   ✗ pop3acc ist NICHT geladen\n";
        }
    }
    
    // Inactive Plugins
    if($reflection->hasProperty('_inactivePlugins')) {
        $prop = $reflection->getProperty('_inactivePlugins');
        $prop->setAccessible(true);
        $inactivePlugins = $prop->getValue($plugins);
        
        echo "\n   Inaktive Plugins: " . count($inactivePlugins) . "\n";
        if(isset($inactivePlugins['pop3acc'])) {
            echo "   ✓ pop3acc ist in INAKTIVE Liste\n";
        }
    }
    
} else {
    echo "   ✗ Plugin-System NICHT aktiv\n";
}

// ============================================================================
// SEKTION 6: FEHLER-LOG ANALYSE
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 6: FEHLER-LOG ANALYSE                               │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

$errorLog = '/var/log/apache2/error.log';
if(file_exists($errorLog)) {
    echo "6.1 Letzte PHP/MySQL Fehler\n";
    echo "─────────────────────────────────────\n";
    
    $errors = shell_exec("tail -n 50 $errorLog | grep -E 'PHP|MySQL|Duplicate|pop3acc' 2>&1");
    if(!empty($errors)) {
        $lines = explode("\n", trim($errors));
        $count = 0;
        foreach($lines as $line) {
            if(!empty($line) && $count < 10) {
                echo "   " . substr($line, 0, 120) . "\n";
                $count++;
            }
        }
        if(count($lines) > 10) {
            echo "   ... (" . (count($lines) - 10) . " weitere Zeilen)\n";
        }
    } else {
        echo "   Keine relevanten Fehler gefunden\n";
    }
}

// ============================================================================
// SEKTION 7: URSACHEN-ANALYSE
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 7: URSACHEN-ANALYSE                                 │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

$problems = array();
$causes = array();

// Prüfe auf Duplikate
if($entryCount > 1) {
    $problems[] = "MEHRFACH-EINTRAG: Plugin '$problemPlugin' existiert $entryCount mal in Datenbank";
    $causes[] = "Möglicherweise durch fehlgeschlagene Installation/Deinstallation";
}

// Prüfe auf installed=1 Konflikt
if($entryCount == 1 && !empty($entries)) {
    $entry = $entries[0];
    if($entry['installed'] == 1) {
        $problems[] = "Plugin ist bereits als 'installed=1' markiert";
        $causes[] = "activatePlugin() versucht erneut zu installieren → UPDATE betrifft 0 Zeilen → INSERT schlägt fehl";
    }
}

// Prüfe Plugin-Datei
if(!file_exists($pluginFile)) {
    $problems[] = "Plugin-Datei existiert nicht: $pluginFile";
    $causes[] = "Plugin wurde manuell gelöscht aber DB-Eintrag blieb";
}

echo "7.1 Identifizierte Probleme\n";
echo "─────────────────────────────────────\n";
if(count($problems) > 0) {
    foreach($problems as $i => $problem) {
        echo "   " . ($i+1) . ". $problem\n";
    }
} else {
    echo "   ✓ Keine Probleme erkannt\n";
}

echo "\n7.2 Mögliche Ursachen\n";
echo "─────────────────────────────────────\n";
if(count($causes) > 0) {
    foreach($causes as $i => $cause) {
        echo "   " . ($i+1) . ". $cause\n";
    }
} else {
    echo "   Keine Ursachen identifiziert\n";
}

// ============================================================================
// SEKTION 8: EMPFOHLENE MASSNAHMEN
// ============================================================================
echo "\n┌─────────────────────────────────────────────────────────────┐\n";
echo "│ SEKTION 8: EMPFOHLENE MASSNAHMEN                            │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

$actions = array();

if($entryCount > 1) {
    $actions[] = "1. DELETE alle Einträge von '$problemPlugin'";
    $actions[] = "2. INSERT einen neuen sauberen Eintrag mit installed=0";
    $actions[] = "3. Cache löschen";
    $actions[] = "4. Plugin neu aktivieren";
} elseif($entryCount == 1 && !empty($entries) && $entries[0]['installed'] == 1) {
    $actions[] = "1. UPDATE '$problemPlugin' SET installed=0";
    $actions[] = "2. Cache löschen";
    $actions[] = "3. Plugin neu aktivieren";
} elseif($entryCount == 0) {
    $actions[] = "1. INSERT neuen Eintrag mit installed=0";
    $actions[] = "2. Plugin aktivieren";
}

if(count($actions) > 0) {
    echo "Empfohlene Reihenfolge:\n";
    foreach($actions as $action) {
        echo "   $action\n";
    }
} else {
    echo "Keine Massnahmen erforderlich\n";
}

// ============================================================================
// ZUSAMMENFASSUNG
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "    DIAGNOSE ABGESCHLOSSEN\n";
echo "═══════════════════════════════════════════════════════════════\n";

echo "\nZeitpunkt: " . date('Y-m-d H:i:s') . "\n";
echo "System: b1gMail " . (defined('BMAIL_VERSION') ? BMAIL_VERSION : 'unknown') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
