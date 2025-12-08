<?php
/**
 * EMAIL ADMIN DEBUG SCRIPT
 * Zeigt genau was an Smarty übergeben wird
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>EMAIL ADMIN DEBUG</h1>\n\n";

// Simuliere b1gMail Init
define('B1GMAIL_INIT', true);
$_SERVER['DOCUMENT_ROOT'] = '/var/www/html/b1gmail';

// DashboardController laden
$controllerPath = '/var/www/html/b1gmail/plugins/emailadmin/controllers/DashboardController.php';
echo "<h2>1. Controller Pfad:</h2>\n";
echo "<pre>$controllerPath</pre>\n";
echo "<p>Existiert: " . (file_exists($controllerPath) ? "✅ JA" : "❌ NEIN") . "</p>\n\n";

if(file_exists($controllerPath)) {
    // Template-Pfad testen
    $basePath = dirname(dirname($controllerPath)) . '/templates/dashboard/';
    echo "<h2>2. Template Base Path:</h2>\n";
    echo "<pre>$basePath</pre>\n";
    
    $templates = array(
        'superadmin' => $basePath . 'superadmin.tpl',
        'reseller' => $basePath . 'reseller.tpl',
        'multi_domain_admin' => $basePath . 'domain_admin.tpl',
        'single_domain_admin' => $basePath . 'domain_admin.tpl',
        'subdomain_admin' => $basePath . 'subdomain_admin.tpl',
        'user' => $basePath . 'user.tpl'
    );
    
    echo "<h2>3. Template-Dateien:</h2>\n";
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>Rolle</th><th>Pfad</th><th>Existiert?</th><th>Größe</th></tr>\n";
    
    foreach($templates as $role => $path) {
        $exists = file_exists($path);
        $size = $exists ? filesize($path) : 0;
        echo "<tr>";
        echo "<td>$role</td>";
        echo "<td>" . htmlspecialchars($path) . "</td>";
        echo "<td>" . ($exists ? "✅" : "❌") . "</td>";
        echo "<td>" . ($exists ? $size . " bytes" : "-") . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n\n";
    
    // Test welcher Template-Pfad zurückgegeben würde
    echo "<h2>4. Test für superadmin Rolle:</h2>\n";
    $testRole = 'superadmin';
    $testTemplate = $templates[$testRole] ?? 'FEHLT!';
    echo "<p>Rolle: <strong>$testRole</strong></p>\n";
    echo "<p>Template: <code>" . htmlspecialchars($testTemplate) . "</code></p>\n";
    echo "<p>Existiert: " . (file_exists($testTemplate) ? "✅ JA" : "❌ NEIN") . "</p>\n";
    
    if(file_exists($testTemplate)) {
        echo "<p>Inhalt (erste 200 Zeichen):</p>\n";
        echo "<pre>" . htmlspecialchars(substr(file_get_contents($testTemplate), 0, 200)) . "...</pre>\n";
    }
}

echo "\n<h2>5. Controller Code prüfen:</h2>\n";
if(file_exists($controllerPath)) {
    $code = file_get_contents($controllerPath);
    
    // Prüfe ob getTemplateFile vorhanden
    if(strpos($code, 'function getTemplateFile') !== false || strpos($code, 'private function getTemplateFile') !== false) {
        echo "<p>✅ getTemplateFile() Methode gefunden</p>\n";
    } else {
        echo "<p>❌ getTemplateFile() Methode FEHLT!</p>\n";
    }
    
    // Prüfe ob fetch aufgerufen wird
    if(strpos($code, '$tpl->fetch(') !== false) {
        echo "<p>✅ \$tpl->fetch() Aufruf gefunden</p>\n";
    } else {
        echo "<p>❌ \$tpl->fetch() Aufruf FEHLT!</p>\n";
    }
    
    // Zeige index() Methode
    if(preg_match('/public function index\(\).*?\{(.*?)\n\t}/s', $code, $matches)) {
        echo "<h3>index() Methode:</h3>\n";
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>\n";
    }
}

echo "\n<p><strong>DEBUG ENDE</strong></p>\n";
?>
