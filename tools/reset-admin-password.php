#!/usr/bin/env php
<?php
/**
 * reset-admin-password.php
 * Setzt das Superadmin-Password zurück
 * 
 * Verwendung:
 *   docker exec b1gmail php /var/www/html/tools/reset-admin-password.php <username> <new_password>
 * 
 * Beispiel:
 *   docker exec b1gmail php /var/www/html/tools/reset-admin-password.php admin neues_password_123
 */

// CLI-only
if (php_sapi_name() !== 'cli') {
    die("Nur via CLI ausführbar!\n");
}

echo "\n";
echo "═══════════════════════════════════════\n";
echo "  b1gMail Admin Password Reset\n";
echo "═══════════════════════════════════════\n";
echo "\n";

// Argumente prüfen
if ($argc < 3) {
    echo "Verwendung:\n";
    echo "  php reset-admin-password.php <username> <new_password>\n";
    echo "\n";
    echo "Beispiel:\n";
    echo "  php reset-admin-password.php admin mein_neues_password\n";
    echo "\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2];

echo "Username:     $username\n";
echo "New Password: " . str_repeat('*', strlen($newPassword)) . "\n";
echo "\n";

// b1gMail initialisieren
$B1GMAIL_DIR = '/var/www/html/';
if (!file_exists($B1GMAIL_DIR . 'serverlib/init.inc.php')) {
    die("❌ ERROR: b1gMail init.inc.php nicht gefunden!\n");
}

// PHP-Fehler unterdrücken für saubere Ausgabe
error_reporting(E_ERROR | E_PARSE);

define('NO_SESSION', true);
define('STANDALONE_SCRIPT', true);

// b1gMail laden
require_once($B1GMAIL_DIR . 'serverlib/init.inc.php');

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1/3  Admin suchen\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

// Admin suchen
$admin = $db->Query("SELECT * FROM {pre}admins WHERE `username`=? LIMIT 1", $username);

if ($admin->RowCount() == 0) {
    echo "❌ ERROR: Admin '$username' nicht gefunden!\n";
    echo "\n";
    echo "Verfügbare Admins:\n";
    $allAdmins = $db->Query("SELECT `adminid`, `username`, `firstname`, `lastname` FROM {pre}admins ORDER BY adminid");
    while ($row = $allAdmins->FetchArray(MYSQLI_ASSOC)) {
        echo "  - ID {$row['adminid']}: {$row['username']} ({$row['firstname']} {$row['lastname']})\n";
    }
    echo "\n";
    exit(1);
}

$adminData = $admin->FetchArray(MYSQLI_ASSOC);
echo "✅ Admin gefunden:\n";
echo "   ID:       {$adminData['adminid']}\n";
echo "   Username: {$adminData['username']}\n";
echo "   Name:     {$adminData['firstname']} {$adminData['lastname']}\n";
echo "\n";

// 2. Password-Hash generieren
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "2/3  Password-Hash generieren\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

// b1gMail verwendet gesalzene MD5-Hashes
// Format: MD5(salt + password)
$salt = substr(md5(mt_rand()), 0, 8);
$passwordHash = md5($salt . $newPassword);

echo "Hash-Methode: MD5(salt + password)\n";
echo "Salt:         $salt\n";
echo "Hash:         $passwordHash\n";
echo "\n";

// 3. Password in DB speichern
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "3/3  Password in Datenbank speichern\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";

$db->Query("UPDATE {pre}admins SET `password`=?, `password_salt`=? WHERE `adminid`=?",
    $passwordHash,
    $salt,
    $adminData['adminid']
);

// Verifizieren
$verify = $db->Query("SELECT `password`, `password_salt` FROM {pre}admins WHERE `adminid`=? LIMIT 1", $adminData['adminid']);
$verifyData = $verify->FetchArray(MYSQLI_ASSOC);

if ($verifyData['password'] === $passwordHash && $verifyData['password_salt'] === $salt) {
    echo "✅ Password erfolgreich aktualisiert!\n";
} else {
    echo "❌ ERROR: Password-Update fehlgeschlagen!\n";
    exit(1);
}

echo "\n";

// Zusammenfassung
echo "═══════════════════════════════════════\n";
echo "  ✅ PASSWORD ERFOLGREICH ZURÜCKGESETZT\n";
echo "═══════════════════════════════════════\n";
echo "\n";
echo "Login-Daten:\n";
echo "  URL:      http://localhost:8095/admin/\n";
echo "  Username: $username\n";
echo "  Password: $newPassword\n";
echo "\n";
echo "WICHTIG: Bitte notiere das neue Password sicher!\n";
echo "\n";

exit(0);
