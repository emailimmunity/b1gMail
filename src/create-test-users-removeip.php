<?php
/**
 * Create test users for RemoveIP V2 testing
 */

require_once __DIR__ . '/serverlib/init.inc.php';

echo "=== RemoveIP V2: Test-User anlegen ===\n\n";

$password = 'TestPass123!';

// User 1: Normal (keine Überwachung)
$email1 = 'test-normal@localhost';
$res = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email1);
if (!$res->FetchArray()) {
    $db->Query('INSERT INTO {pre}users (email, passwort, vorname, nachname, gesperrt, locked) VALUES (?, MD5(?), ?, ?, ?, ?)',
        $email1, $password, 'Normal', 'User', 'no', 'no');
    echo "✅ User 1 angelegt: $email1\n";
} else {
    echo "✅ User 1 existiert bereits: $email1\n";
}

// User 2: Überwacht
$email2 = 'test-surveillance@localhost';
$res = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email2);
if (!$res->FetchArray()) {
    $db->Query('INSERT INTO {pre}users (email, passwort, vorname, nachname, gesperrt, locked) VALUES (?, MD5(?), ?, ?, ?, ?)',
        $email2, $password, 'Surveillance', 'User', 'no', 'no');
    echo "✅ User 2 angelegt: $email2\n";
} else {
    echo "✅ User 2 existiert bereits: $email2\n";
}

echo "\n=== Login-Daten ===\n";
echo "Email 1: $email1\n";
echo "Email 2: $email2\n";
echo "Passwort: $password\n\n";

// User-IDs anzeigen
$res1 = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email1);
$row1 = $res1->FetchArray();
echo "User 1 ID: " . $row1['id'] . "\n";

$res2 = $db->Query('SELECT id FROM {pre}users WHERE email = ?', $email2);
$row2 = $res2->FetchArray();
echo "User 2 ID: " . $row2['id'] . "\n";

echo "\n✅ Test-User bereit!\n";
