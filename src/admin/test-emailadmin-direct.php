<?php
/**
 * DIREKTER EMAIL ADMIN TEST
 * Umgeht b1gMail und testet direkt
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>EmailAdmin Test</title></head><body>";
echo "<h1>EMAIL ADMIN DIREKTER TEST</h1>";

// Simuliere b1gMail Umgebung
define('B1GMAIL_INIT', true);
$_SERVER['DOCUMENT_ROOT'] = '/var/www/html/b1gmail';

echo "<h2>1. DashboardController laden</h2>";
$controllerFile = '/var/www/html/b1gmail/plugins/emailadmin/controllers/DashboardController.php';
echo "<p>Pfad: <code>$controllerFile</code></p>";

if(!file_exists($controllerFile)) {
    die("<p style='color:red;'>❌ FEHLER: DashboardController.php nicht gefunden!</p></body></html>");
}

echo "<p>✅ Datei existiert</p>";

// Template-Pfad testen
echo "<h2>2. Template-Pfad Test</h2>";
$basePath = dirname(dirname($controllerFile)) . '/templates/dashboard/';
echo "<p>Base Path: <code>$basePath</code></p>";

$templates = array(
    'superadmin' => $basePath . 'superadmin.tpl',
    'reseller' => $basePath . 'reseller.tpl',
    'multi_domain_admin' => $basePath . 'domain_admin.tpl',
    'single_domain_admin' => $basePath . 'domain_admin.tpl',
    'subdomain_admin' => $basePath . 'subdomain_admin.tpl',
    'user' => $basePath . 'user.tpl'
);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Rolle</th><th>Template</th><th>Existiert?</th></tr>";
foreach($templates as $role => $tpl) {
    $exists = file_exists($tpl);
    echo "<tr>";
    echo "<td>$role</td>";
    echo "<td><code>" . htmlspecialchars($tpl) . "</code></td>";
    echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test: Was passiert wenn wir getTemplateFile() simulieren?
echo "<h2>3. getTemplateFile() Simulation</h2>";

// Simuliere userRole
$userRole = array('role' => 'superadmin');
echo "<p>Test-Rolle: <strong>superadmin</strong></p>";

$templateFile = $templates[$userRole['role']] ?? $templates['user'];
echo "<p>Berechneter Template-Pfad: <code>" . htmlspecialchars($templateFile) . "</code></p>";
echo "<p>Existiert: " . (file_exists($templateFile) ? "✅ JA" : "❌ NEIN") . "</p>";

if(!file_exists($templateFile)) {
    die("<p style='color:red;'>❌ FEHLER: Template nicht gefunden!</p></body></html>");
}

// Test: Smarty laden
echo "<h2>4. Smarty Test</h2>";
$smartyPath = '/var/www/html/b1gmail/serverlib/3rdparty/smarty/src/Smarty.php';
echo "<p>Smarty-Pfad: <code>$smartyPath</code></p>";

if(!file_exists($smartyPath)) {
    die("<p style='color:red;'>❌ FEHLER: Smarty nicht gefunden!</p></body></html>");
}

echo "<p>✅ Smarty existiert</p>";

// Versuche Smarty zu laden
require_once($smartyPath);

echo "<p>✅ Smarty geladen</p>";

// Erstelle Smarty Instanz
try {
    $smarty = new Smarty\Smarty();
    echo "<p>✅ Smarty Instanz erstellt</p>";
    
    // Setze Template-Verzeichnisse
    $smarty->setTemplateDir(dirname($templateFile));
    $smarty->setCompileDir('/var/www/html/b1gmail/admin/templates/cache');
    
    echo "<p>Template Dir: <code>" . htmlspecialchars(dirname($templateFile)) . "</code></p>";
    echo "<p>Compile Dir: <code>/var/www/html/b1gmail/admin/templates/cache</code></p>";
    
    // Test: fetch() mit absolutem Pfad
    echo "<h2>5. Smarty fetch() Test</h2>";
    echo "<p>Versuche Template zu laden: <code>$templateFile</code></p>";
    
    // WICHTIG: Test ob Template-Name leer ist
    if(empty($templateFile)) {
        die("<p style='color:red;'>❌ KRITISCHER FEHLER: Template-Name ist LEER!</p><p>Das ist der Grund für 'Smarty: Source: Missing name'!</p></body></html>");
    }
    
    echo "<p>Template-Name ist NICHT leer: ✅</p>";
    
    // Versuche zu fetchen
    $result = $smarty->fetch($templateFile);
    
    echo "<p>✅ Smarty fetch() erfolgreich!</p>";
    echo "<p>Result-Länge: " . strlen($result) . " Zeichen</p>";
    
    echo "<h2>6. ERFOLG!</h2>";
    echo "<p style='color:green; font-size:20px;'>✅ ALLES FUNKTIONIERT!</p>";
    echo "<p>Das bedeutet: Der Fehler liegt NICHT am Template oder Smarty!</p>";
    
} catch(Exception $e) {
    echo "<p style='color:red;'>❌ FEHLER beim Smarty fetch():</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
