<?php
/**
 * TRACE: Wo wird fetch() mit leerem String aufgerufen?
 */

define('ADMIN_MODE', true);
require_once('/var/www/html/b1gmail/serverlib/init.inc.php');

echo "<h1>FETCH TRACE DEBUG</h1>";

// Override fetch to trace calls
class DebugTemplate extends Template {
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null) {
        echo "<div style='background:yellow; padding:10px; margin:5px;'>";
        echo "<strong>fetch() aufgerufen!</strong><br>";
        echo "Template: " . (empty($template) ? "<span style='color:red;'>LEER!</span>" : htmlspecialchars($template)) . "<br>";
        echo "Backtrace:<br><pre>";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        echo "</pre></div>";
        
        if(empty($template)) {
            die("<h2 style='color:red;'>GEFUNDEN! fetch() mit leerem Template!</h2>");
        }
        
        return parent::fetch($template, $cache_id, $compile_id, $parent);
    }
}

// Replace $tpl
$tpl = new DebugTemplate();

echo "<h2>Teste EmailAdmin Plugin</h2>";

// Load plugin
require_once('/var/www/html/b1gmail/plugins/emailadmin.plugin.php');

$plugin = new BMPlugin_emailadmin();

echo "<h3>Rufe AdminPage() auf...</h3>";

// Simuliere currentUser
$currentUser = array('id' => 1, 'email' => 'postmaster@gtin.org');

try {
    $plugin->AdminPage();
    echo "<h3>ERFOLG! Kein Fehler!</h3>";
} catch(Exception $e) {
    echo "<h3 style='color:red;'>FEHLER!</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
