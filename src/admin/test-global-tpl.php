<?php
/**
 * TEST: Ist $tpl global verfügbar?
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>";
echo "<h1>GLOBAL \$tpl TEST</h1>";

// b1gMail Admin init (wie im echten Admin)
define('ADMIN_MODE', true);
require_once('/var/www/html/b1gmail/serverlib/init.inc.php');

echo "<h2>1. \$tpl Variable prüfen</h2>";

if(isset($tpl)) {
    echo "<p>✅ \$tpl ist gesetzt!</p>";
    echo "<p>Klasse: <strong>" . get_class($tpl) . "</strong></p>";
    
    // Test: fetch() mit einem echten Template
    $testTemplate = '/var/www/html/b1gmail/plugins/emailadmin/templates/dashboard/superadmin.tpl';
    
    echo "<h2>2. Template Test</h2>";
    echo "<p>Template: <code>$testTemplate</code></p>";
    
    if(file_exists($testTemplate)) {
        echo "<p>✅ Template existiert</p>";
        
        try {
            echo "<h2>3. \$tpl->fetch() Test</h2>";
            
            // Setze einige Test-Variablen
            $tpl->assign('userRole', array('role' => 'superadmin'));
            $tpl->assign('roleName', 'Superadmin');
            $tpl->assign('stats', array());
            $tpl->assign('recentActivity', array());
            $tpl->assign('systemInfo', array());
            
            $result = $tpl->fetch($testTemplate);
            
            echo "<p style='color:green; font-size:20px;'>✅ ERFOLG!</p>";
            echo "<p>\$tpl->fetch() funktioniert!</p>";
            echo "<p>Result-Länge: " . strlen($result) . " Zeichen</p>";
            
            echo "<h2>4. PROBLEM IDENTIFIZIERT!</h2>";
            echo "<p>Wenn dieser Test funktioniert, aber Email Admin nicht...</p>";
            echo "<p>→ Das Problem liegt am <strong>Return-Value</strong> der Controller!</p>";
            
        } catch(Exception $e) {
            echo "<p style='color:red;'>❌ FEHLER:</p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    } else {
        echo "<p style='color:red;'>❌ Template nicht gefunden</p>";
    }
    
} else {
    echo "<p style='color:red;'>❌ \$tpl ist NICHT gesetzt!</p>";
    echo "<p>Das ist das Problem!</p>";
}

echo "</body></html>";
?>
