<?php
/**
 * Create surveillance measure for RemoveIP V2 testing
 */

require_once __DIR__ . '/serverlib/init.inc.php';

echo "=== RemoveIP V2: Überwachungsmaßnahme anlegen ===\n\n";

$email = 'test-surveillance@localhost';

// User-ID holen
$res = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email);
$row = $res->FetchArray();

if (!$row) {
    die("❌ User nicht gefunden: $email\n");
}

$userid = $row['id'];
echo "User-ID: $userid\n";
echo "Email: $email\n\n";

// Prüfen ob bereits Überwachung existiert
$res = $db->Query('SELECT * FROM {pre}mod_removeip_surveillance WHERE userid = ?', $userid);
if ($res->FetchArray()) {
    echo "⚠️  Überwachung existiert bereits\n\n";
    
    // Anzeigen
    $res = $db->Query('SELECT * FROM {pre}mod_removeip_surveillance WHERE userid = ?', $userid);
    $row = $res->FetchArray();
    echo "Aktuelle Überwachung:\n";
    echo "  ID: " . $row['id'] . "\n";
    echo "  Grund: " . $row['reason'] . "\n";
    echo "  Behörde: " . $row['authority'] . "\n";
    echo "  Aktenzeichen: " . $row['file_number'] . "\n";
    echo "  Von: " . $row['valid_from'] . "\n";
    echo "  Bis: " . $row['valid_until'] . "\n";
    echo "  Aktiv: " . $row['active'] . "\n";
} else {
    // Neue Überwachungsmaßnahme anlegen
    $reason = 'TKÜV-Test: Verdacht auf Straftat §202a StGB (Ausspähen von Daten)';
    $authority = 'Bundeskriminalamt (BKA) - Abteilung Cybercrime';
    $file_number = 'BKA-2025-TEST-' . date('YmdHis');
    $created_by = 1; // Admin-ID
    $valid_from = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $valid_until = date('Y-m-d H:i:s', strtotime('+30 days'));
    $active = 1;
    
    $db->Query(
        'INSERT INTO {pre}mod_removeip_surveillance 
        (userid, email, reason, authority, file_number, created_by, created_at, valid_from, valid_until, active)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)',
        $userid, $email, $reason, $authority, $file_number, $created_by, $valid_from, $valid_until, $active
    );
    
    echo "✅ Überwachungsmaßnahme angelegt:\n\n";
    echo "  Grund: $reason\n";
    echo "  Behörde: $authority\n";
    echo "  Aktenzeichen: $file_number\n";
    echo "  Gültig von: $valid_from\n";
    echo "  Gültig bis: $valid_until\n";
    echo "  Status: Aktiv\n";
}

echo "\n✅ Überwachungsmaßnahme bereit für Tests!\n";
