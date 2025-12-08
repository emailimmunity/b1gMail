<?php
/**
 * PremiumAccount Plugin Extension
 * Erweitert das PremiumAccount-Plugin um das neue Produkt-System
 * 
 * Diese Datei wird am Ende von premiumaccount.plugin.php eingefügt
 */

// =========================================================
// ADMIN HANDLER ERWEITERUNG
// =========================================================

/**
 * In der AdminHandler() Methode nach den bestehenden if/elseif Blöcken einfügen:
 */

/*
else if($_REQUEST['action'] == 'products_v2')
{
    $this->_productsV2Page();
}
*/

/**
 * Neue Methode in der PremiumAccountPlugin Klasse:
 */

/*
function _productsV2Page()
{
    global $db, $tpl, $currentUser, $lang_admin, $bm_prefs;
    
    // Globale Variablen sicherstellen
    if(!isset($currentUser)) {
        global $currentUser;
    }
    
    // products.php einbinden
    $productsFile = B1GMAIL_DIR . 'admin/products.php';
    
    if(file_exists($productsFile)) {
        // Output-Buffering für saubere Integration
        ob_start();
        
        // Menü-Header mit Tabs
        echo '<div class="premium-admin-tabs" style="margin-bottom: 20px; border-bottom: 2px solid #ddd;">';
        echo '<a href="?plugin=PremiumAccountPlugin&action=packages" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 2px solid transparent;">';
        echo '📦 Legacy-Pakete';
        echo '</a>';
        echo '<a href="?plugin=PremiumAccountPlugin&action=products_v2" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #667eea; border-bottom: 2px solid #667eea; font-weight: bold;">';
        echo '🎁 Neue Produkte (24 Pakete)';
        echo '</a>';
        echo '<a href="?plugin=PremiumAccountPlugin&action=subscriptions" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 2px solid transparent;">';
        echo '📊 Abonnements';
        echo '</a>';
        echo '</div>';
        
        // Include products.php
        // WICHTIG: Kein require_once am Anfang von products.php!
        // products.php muss angepasst werden:
        //   - Kein admin.inc.php laden (schon geladen)
        //   - Nur die Logik/HTML ausgeben
        
        // Da products.php eigenständig ist, müssen wir es anpassen
        // Für jetzt zeigen wir nur einen Link
        echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h2 style="color: #333; margin-bottom: 15px;">🎁 Neue Produkt-Verwaltung</h2>';
        echo '<p style="color: #666; margin-bottom: 15px;">Die neue Produktverwaltung bietet erweiterte Funktionen:</p>';
        echo '<ul style="color: #666; margin-left: 20px; margin-bottom: 20px;">';
        echo '<li>✅ 24 vordefinierte Pakete (User, Domain, Multi-Domain, Reseller)</li>';
        echo '<li>✅ 48 togglebare Features (CalDAV, API, White-Label, etc.)</li>';
        echo '<li>✅ Flexible Preisverwaltung mit Historie</li>';
        echo '<li>✅ Reseller-Preis-Override</li>';
        echo '<li>✅ Feature-basierte Rechtevergabe</li>';
        echo '</ul>';
        echo '<div style="display: flex; gap: 10px;">';
        echo '<a href="../admin/products.php" target="_blank" style="display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">';
        echo '→ Produktverwaltung öffnen';
        echo '</a>';
        echo '<a href="../product-configurator.php" target="_blank" style="display: inline-block; padding: 12px 24px; background: #48bb78; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">';
        echo '→ Konfigurator testen';
        echo '</a>';
        echo '</div>';
        echo '<p style="color: #999; margin-top: 15px; font-size: 0.9em;">Hinweis: Öffnet in neuem Tab. Vollständige Integration folgt.</p>';
        echo '</div>';
        
        $content = ob_get_clean();
        
        // Template-Variable setzen
        $tpl->assign('page', $content);
        $tpl->assign('pageTitle', 'Produkt-Verwaltung V2');
        
        // Output
        echo $content;
    } else {
        echo '<div class="error">Produktverwaltung nicht gefunden: ' . $productsFile . '</div>';
    }
}
*/

// =========================================================
// USER PREFS HANDLER ERWEITERUNG
// =========================================================

/**
 * In der UserPrefsPageHandler() Methode vor dem return(true) einfügen:
 */

/*
// New product configurator
else if($_REQUEST['do'] == 'configurator_v2')
{
    $this->_showConfiguratorV2();
    return true;
}
*/

/**
 * Neue Methode in der PremiumAccountPlugin Klasse:
 */

/*
function _showConfiguratorV2()
{
    global $db, $tpl, $userRow, $lang_user, $bm_prefs;
    
    // Redirect zum neuen Konfigurator
    // (Da der Konfigurator eigenständig ist und eigenes Layout hat)
    header('Location: ../product-configurator.php');
    exit;
}
*/

// =========================================================
// MENÜ-ERWEITERUNGEN
// =========================================================

/**
 * In der _packagesPage() Methode am Anfang einfügen (nach global Deklarationen):
 */

/*
// Tab-Navigation
echo '<div class="premium-admin-tabs" style="margin-bottom: 20px; border-bottom: 2px solid #ddd;">';
echo '<a href="?plugin=PremiumAccountPlugin&action=packages" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #667eea; border-bottom: 2px solid #667eea; font-weight: bold;">';
echo '📦 Legacy-Pakete';
echo '</a>';
echo '<a href="?plugin=PremiumAccountPlugin&action=products_v2" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 2px solid transparent;">';
echo '🎁 Neue Produkte (24 Pakete)';
echo '</a>';
echo '<a href="?plugin=PremiumAccountPlugin&action=subscriptions" style="display: inline-block; padding: 10px 20px; text-decoration: none; color: #666; border-bottom: 2px solid transparent;">';
echo '📊 Abonnements';
echo '</a>';
echo '</div>';
*/

/**
 * Im User-Template (pacc.user.overview.tpl) einen Link zum neuen Konfigurator hinzufügen:
 */

/*
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin: 20px 0; color: white;">
    <h3 style="margin: 0 0 10px 0;">🎁 Neu: Moderner Produkt-Konfigurator</h3>
    <p style="margin: 0 0 15px 0; opacity: 0.9;">Entdecke unsere neuen Pakete mit erweiterten Funktionen!</p>
    <a href="?action=pacc_mod&do=configurator_v2" style="display: inline-block; padding: 10px 20px; background: white; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: bold;">
        → Zum neuen Konfigurator
    </a>
</div>
*/

?>
