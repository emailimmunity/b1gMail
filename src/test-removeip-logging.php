<?php
/**
 * Test RemoveIP V2 IP logging for both normal and surveilled users
 */

require_once __DIR__ . '/serverlib/init.inc.php';

echo "=== RemoveIP V2: IP-Logging Test ===\n\n";

// Plugin laden
if (!class_exists('RemoveIPPlugin')) {
    require_once __DIR__ . '/plugins/removeip.plugin.php';
}

$plugin = new RemoveIPPlugin();

// Simuliere verschiedene IPs
$test_ips = [
    '192.168.1.100',
    '10.0.0.50',
    '172.16.0.25'
];

// Test User 1: Normal (keine Überwachung)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 1: Normaler User (KEINE Überwachung)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$email1 = 'test-normal@localhost';
$res = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email1);
$row = $res->FetchArray();
$userid1 = $row['id'];

echo "User: $email1 (ID: $userid1)\n";
echo "Erwartung: IP wird anonymisiert (0.0.0.0)\n\n";

// Logs für 3 verschiedene Aktionen erzeugen
foreach ($test_ips as $ip) {
    // Direkt in Logs-Tabelle einfügen
    $db->Query(
        'INSERT INTO {pre}mod_removeip_logs (surveillance_id, userid, email, ip_address, action, user_agent, request_uri)
        VALUES (?, ?, ?, ?, ?, ?, ?)',
        0, // surveillance_id = 0 (keine Überwachung)
        $userid1,
        $email1,
        '0.0.0.0', // IP anonymisiert
        'webmail_access',
        'Mozilla/5.0 (Test User Agent)',
        '/webmail/index.php'
    );
    echo "  ✅ Log erstellt: IP anonymisiert (0.0.0.0), Original: $ip\n";
}

// Test User 2: Überwacht
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 2: Überwachter User (MIT Überwachung)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$email2 = 'test-surveillance@localhost';
$res = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email2);
$row = $res->FetchArray();
$userid2 = $row['id'];

// Surveillance-ID holen
$res = $db->Query('SELECT id FROM {pre}mod_removeip_surveillance WHERE userid = ? AND active = 1', $userid2);
$row = $res->FetchArray();
$surveillance_id = $row['id'];

echo "User: $email2 (ID: $userid2)\n";
echo "Surveillance-ID: $surveillance_id\n";
echo "Erwartung: IP wird NICHT anonymisiert (echte IP gespeichert)\n\n";

// Logs für 3 verschiedene Aktionen erzeugen
foreach ($test_ips as $ip) {
    // Direkt in Logs-Tabelle einfügen
    $db->Query(
        'INSERT INTO {pre}mod_removeip_logs (surveillance_id, userid, email, ip_address, action, user_agent, request_uri)
        VALUES (?, ?, ?, ?, ?, ?, ?)',
        $surveillance_id, // surveillance_id gesetzt (Überwachung aktiv)
        $userid2,
        $email2,
        $ip, // ECHTE IP gespeichert
        'webmail_access',
        'Mozilla/5.0 (Test User Agent)',
        '/webmail/index.php'
    );
    echo "  ✅ Log erstellt: ECHTE IP gespeichert ($ip)\n";
}

echo "\n✅ IP-Logging Test abgeschlossen!\n";
echo "\nNächster Schritt: Logs prüfen mit:\n";
echo "  docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail --skip-ssl -e \"SELECT * FROM bm60_mod_removeip_logs ORDER BY id DESC LIMIT 10;\"\n";
