<?php
/**
 * EXAKTE NACHBILDUNG des EmailAdmin Flows
 */

// b1gMail Admin init (wie im echten Admin)
define('ADMIN_MODE', true);
require_once('/var/www/html/b1gmail/serverlib/init.inc.php');

echo "<h1>EXAKTER EMAIL ADMIN TEST</h1>";

// Simuliere was emailadmin.plugin.php macht
echo "<h2>1. User-Rolle abrufen</h2>";

$res = $db->Query('SELECT * FROM {pre}emailadmin_roles WHERE user_id=?', $currentUser['id']);
$userRole = $res->FetchArray(MYSQLI_ASSOC);
$res->Free();

if(!$userRole) {
    die("<p style='color:red;'>❌ KEINE ROLLE! User muss erst Rolle bekommen!</p>");
}

echo "<p>✅ Rolle gefunden: <strong>" . htmlspecialchars($userRole['role']) . "</strong></p>";

// DashboardController laden
echo "<h2>2. DashboardController laden</h2>";

require_once('/var/www/html/b1gmail/plugins/emailadmin/controllers/DashboardController.php');

echo "<p>✅ DashboardController geladen</p>";

// Controller erstellen (wie im Plugin)
echo "<h2>3. Controller erstellen</h2>";

try {
    $controller = new EmailAdmin_DashboardController($currentUser['id'], $userRole);
    echo "<p>✅ Controller erstellt</p>";
    
    // index() aufrufen
    echo "<h2>4. Controller->index() aufrufen</h2>";
    
    $content = $controller->index();
    
    echo "<p style='color:green; font-size:20px;'>✅ ERFOLG!</p>";
    echo "<p>Content-Länge: " . strlen($content) . " Zeichen</p>";
    echo "<p>Erste 200 Zeichen:</p>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>";
    
    echo "<h2>5. PROBLEM GELÖST!</h2>";
    echo "<p>Wenn dieser Test funktioniert, dann liegt das Problem am Plugin-Aufruf selbst!</p>";
    
} catch(Exception $e) {
    echo "<p style='color:red; font-size:20px;'>❌ FEHLER!</p>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    
    echo "<h2>DEBUG INFO:</h2>";
    echo "<p>User ID: " . ($currentUser['id'] ?? 'UNDEFINED') . "</p>";
    echo "<p>User Role: " . print_r($userRole, true) . "</p>";
}
?>
