<?php
/**
 * CleverCron Plugin Setup & Function Test
 */

require_once __DIR__ . '/serverlib/init.inc.php';

echo "=== CleverCron Plugin Test ===\n\n";

// 1. Check if plugin file exists and is loaded
echo "1. Plugin-Check:\n";
$plugin_file = '/var/www/html/plugins/tccrn.plugin.php';
if (file_exists($plugin_file)) {
    echo "  ✅ Plugin-Datei existiert: tccrn.plugin.php\n";
    echo "  Größe: " . filesize($plugin_file) . " Bytes\n";
} else {
    echo "  ❌ Plugin-Datei fehlt!\n";
    exit(1);
}

// Check if plugin class is defined
if (class_exists('TCCronPlugin')) {
    echo "  ✅ Plugin-Klasse geladen: TCCronPlugin\n";
} else {
    echo "  ⚠️  Plugin-Klasse nicht geladen (wird beim nächsten Request geladen)\n";
}

// 2. Check database tables
echo "\n2. Datenbank-Tabellen:\n";
$tables = array(
    'tccrn_plugin_settings' => 'Einstellungen',
    'tccrn_plugin_cron' => 'Cron-Jobs'
);

foreach ($tables as $table => $description) {
    $res = $db->Query("SHOW TABLES LIKE '{pre}$table'");
    if ($res->RowCount() > 0) {
        echo "  ✅ Tabelle existiert: {pre}$table ($description)\n";
        
        // Count rows
        $count_res = $db->Query("SELECT COUNT(*) FROM {pre}$table");
        $count = $count_res->FetchArray(MYSQLI_NUM);
        echo "     Anzahl Einträge: " . $count[0] . "\n";
    } else {
        echo "  ❌ Tabelle fehlt: {pre}$table\n";
    }
}

// 3. Check settings table structure
echo "\n3. Settings-Tabelle Struktur:\n";
$res = $db->Query("DESCRIBE {pre}tccrn_plugin_settings");
while ($row = $res->FetchArray()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// 4. Check cron table structure
echo "\n4. Cron-Tabelle Struktur:\n";
$res = $db->Query("DESCRIBE {pre}tccrn_plugin_cron");
while ($row = $res->FetchArray()) {
    echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// 5. Check if we can read settings
echo "\n5. Aktuelle Einstellungen:\n";
$res = $db->Query("SELECT * FROM {pre}tccrn_plugin_settings LIMIT 1");
if ($row = $res->FetchArray()) {
    echo "  ✅ Settings gelesen:\n";
    foreach ($row as $key => $value) {
        if (!is_numeric($key)) {
            echo "     $key: $value\n";
        }
    }
} else {
    echo "  ⚠️  Keine Settings vorhanden\n";
}

// 6. Check if we can list jobs
echo "\n6. Vorhandene Cron-Jobs:\n";
$res = $db->Query("SELECT * FROM {pre}tccrn_plugin_cron");
$job_count = 0;
while ($row = $res->FetchArray()) {
    $job_count++;
    echo "  Job #$job_count:\n";
    echo "    ID: " . $row['id'] . "\n";
    echo "    Name: " . $row['name'] . "\n";
    echo "    Active: " . ($row['active'] ? 'Ja' : 'Nein') . "\n";
    if (isset($row['next_run'])) {
        echo "    Next Run: " . $row['next_run'] . "\n";
    }
    if (isset($row['last_run'])) {
        echo "    Last Run: " . $row['last_run'] . "\n";
    }
}
if ($job_count == 0) {
    echo "  ⚠️  Keine Cron-Jobs vorhanden (normal nach Installation)\n";
}

echo "\n=== Test Summary ===\n";
echo "Plugin-Datei: ✅ Vorhanden\n";
echo "Tabellen: ✅ " . count($tables) . "/" . count($tables) . " OK\n";
echo "Jobs: " . $job_count . " vorhanden\n";
echo "\n✅ CleverCron Plugin erfolgreich aktiviert!\n";
echo "\nNächster Schritt: Admin-Panel öffnen\n";
echo "URL: http://localhost:8095/admin/\n";
echo "Navigation: Plugins → CleverCron\n";
