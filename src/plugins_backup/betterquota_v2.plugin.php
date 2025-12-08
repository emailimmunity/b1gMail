<?php
/**
 * BetterQuota v2.0
 *
 * Flexible Quota-Verwaltung für b1gMail
 * Komplett neu entwickelt - OHNE Class Replacement!
 *
 * @version 2.0.0
 * @author Cascade AI
 * @date 2025-11-05
 * @license MIT
 * @requires b1gMail 7.3.0+, PHP 8.0+
 */

class BetterQuotaV2_Plugin extends BMPlugin {
    
    /**
     * Plugin Info
     */
    public function __construct() {
        $this->name = 'BetterQuota v2';
        $this->author = 'Cascade AI';
        $this->version = '2.0.0';
        $this->designedfor = '7.4.2';
        $this->type = BMPLUGIN_DEFAULT;
        
        $this->admin_pages = false; // Erstmal ohne Admin-Seite
        $this->website = '';
        $this->update_url = '';
    }
    
    /**
     * Plugin Installation
     * Erstellt Datenbank-Tabelle
     */
    public function Install() {
        global $db, $mysql;
        
        try {
            // Tabellen-Struktur
            $tableName = $mysql['prefix'] . 'betterquota_v2';
            
            $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
                `userid` INT(11) UNSIGNED NOT NULL PRIMARY KEY,
                `mail_quota` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                `webdisk_quota` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                `mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=auto, 1=manual',
                `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_mode` (`mode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $db->Query($sql);
            
            PutLog(sprintf('%s v%s installed successfully', $this->name, $this->version), 
                   PRIO_PLUGIN, __FILE__, __LINE__);
            
            return true;
            
        } catch (Exception $e) {
            PutLog(sprintf('%s installation failed: %s', $this->name, $e->getMessage()), 
                   PRIO_ERROR, __FILE__, __LINE__);
            return false;
        }
    }
    
    /**
     * Plugin Deinstallation
     * Tabelle wird NICHT gelöscht (Daten behalten!)
     */
    public function Uninstall() {
        PutLog(sprintf('%s v%s uninstalled (data preserved)', $this->name, $this->version), 
               PRIO_PLUGIN, __FILE__, __LINE__);
        return true;
    }
    
    /**
     * User gelöscht - Settings auch löschen
     */
    public function OnDeleteUser($userId) {
        global $db;
        try {
            $db->Query('DELETE FROM {pre}betterquota_v2 WHERE userid = ?', $userId);
        } catch (Exception $e) {
            // Silent fail - nicht kritisch
        }
    }
    
    /**
     * FileHandler: Registriert Prefs-Seite
     */
    public function FileHandler($file, $action = '') {
        global $thisUser;
        
        // Nur für eingeloggte User
        if (!$thisUser) {
            return;
        }
        
        // Nur auf prefs.php
        if ($file !== 'prefs.php') {
            return;
        }
        
        // Prüfe ob für Gruppe aktiviert
        $active = $this->GetGroupOptionValue('betterquota_mode');
        if ($active == 0) {
            return; // Deaktiviert
        }
        
        // Registriere Prefs-Item
        $GLOBALS['prefsItems']['betterquota_v2'] = true;
        $GLOBALS['prefsImages']['betterquota_v2'] = 'plugins/templates/images/betterquota_icon48.png';
        $GLOBALS['prefsIcons']['betterquota_v2'] = 'plugins/templates/images/betterquota_icon16.png';
    }
    
    /**
     * User Preferences Page Handler
     */
    public function UserPrefsPageHandler($action) {
        if ($action !== 'betterquota_v2') {
            return false;
        }
        
        // Prüfe ob aktiviert
        $active = $this->GetGroupOptionValue('betterquota_mode');
        if ($active == 0) {
            return false;
        }
        
        global $tpl, $db, $userRow, $groupRow, $thisUser;
        
        // Gesamt-Quota berechnen
        $totalQuota = (int)$groupRow['storage'] + (int)$groupRow['webdisk'];
        
        // Aktuell belegt
        $mailUsed = (int)$userRow['mailspace_used'];
        $webdiskUsed = (int)$userRow['diskspace_used'];
        
        // User-Settings laden
        $settings = $this->getUserSettings($userRow['id']);
        
        // POST verarbeiten?
        if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
            $this->handlePrefsPost($userRow['id'], $totalQuota, $webdiskUsed, $settings);
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
        
        // Steps für Dropdown berechnen
        $steps = $this->calculateSteps($totalQuota, $mailUsed, $webdiskUsed);
        
        // Template-Variablen
        $tpl->assign('bq2_mode_auto', $settings['mode'] == 0);
        $tpl->assign('bq2_mail_quota', $settings['mail_quota']);
        $tpl->assign('bq2_webdisk_quota', $settings['webdisk_quota']);
        $tpl->assign('bq2_mail_used', $mailUsed);
        $tpl->assign('bq2_webdisk_used', $webdiskUsed);
        $tpl->assign('bq2_total_quota', $totalQuota);
        $tpl->assign('bq2_steps', $steps);
        $tpl->assign('bq2_group_mode', $active); // 1=custom, 2=auto-only
        
        // Template anzeigen
        $tpl->assign('pageContent', $this->_templatePath('betterquota_v2.prefs.tpl'));
        $tpl->display('li/index.tpl');
        
        return true;
    }
    
    /**
     * Sprachen registrieren
     */
    public function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
        $isDeutsch = (strpos($lang, 'deutsch') !== false);
        
        if ($isDeutsch) {
            $lang_user['betterquota_v2'] = 'Speicherplatz';
            $lang_user['betterquota_v2_desc'] = 'Flexible Speicherverwaltung';
            $lang_user['prefs_d_betterquota_v2'] = 'Teile deinen Speicher flexibel zwischen E-Mail und WebDisk auf.';
            
            $lang_admin['bq2_disabled'] = 'Deaktiviert (Standard-Quota)';
            $lang_admin['bq2_custom'] = 'Benutzerdefiniert (User wählt)';
            $lang_admin['bq2_auto'] = 'Automatisch (Gemeinsamer Speicher)';
        } else {
            $lang_user['betterquota_v2'] = 'Storage';
            $lang_user['betterquota_v2_desc'] = 'Flexible storage management';
            $lang_user['prefs_d_betterquota_v2'] = 'Allocate your storage flexibly between email and webdisk.';
            
            $lang_admin['bq2_disabled'] = 'Disabled (Standard quota)';
            $lang_admin['bq2_custom'] = 'Custom (User chooses)';
            $lang_admin['bq2_auto'] = 'Automatic (Shared storage)';
        }
        
        // Gruppen-Option registrieren
        $this->RegisterGroupOption(
            'betterquota_mode',
            FIELD_DROPDOWN,
            'BetterQuota v2:',
            array(
                '0' => $lang_admin['bq2_disabled'],
                '1' => $lang_admin['bq2_custom'],
                '2' => $lang_admin['bq2_auto']
            )
        );
    }
    
    /**
     * User-Settings aus DB laden
     */
    private function getUserSettings($userId) {
        global $db, $groupRow;
        
        $res = $db->Query('SELECT * FROM {pre}betterquota_v2 WHERE userid = ?', $userId);
        
        if ($res->RowCount() > 0) {
            $settings = $res->FetchArray();
            $res->Free();
            return $settings;
        }
        
        // Default: Auto-Modus mit Gruppen-Quota
        return array(
            'userid' => $userId,
            'mail_quota' => (int)$groupRow['storage'],
            'webdisk_quota' => (int)$groupRow['webdisk'],
            'mode' => 0 // Auto
        );
    }
    
    /**
     * POST-Daten verarbeiten
     */
    private function handlePrefsPost($userId, $totalQuota, $webdiskUsed, $currentSettings) {
        global $db;
        
        $mode = isset($_POST['mode_auto']) && $_POST['mode_auto'] == '1' ? 0 : 1;
        
        if ($mode == 0) {
            // Auto-Modus
            $mailQuota = 0;
            $webdiskQuota = 0;
        } else {
            // Manuell-Modus
            $requestedMailQuota = (int)$_POST['mail_quota'];
            
            // Validierung
            $maxMailQuota = $totalQuota - $webdiskUsed;
            $mailQuota = min($requestedMailQuota, $maxMailQuota);
            $mailQuota = max($mailQuota, 0);
            
            $webdiskQuota = $totalQuota - $mailQuota;
        }
        
        // In DB speichern
        $db->Query(
            'REPLACE INTO {pre}betterquota_v2 (userid, mail_quota, webdisk_quota, mode) VALUES (?, ?, ?, ?)',
            $userId,
            $mailQuota,
            $webdiskQuota,
            $mode
        );
    }
    
    /**
     * Steps für Dropdown berechnen
     */
    private function calculateSteps($totalQuota, $mailUsed, $webdiskUsed) {
        $step = max(ceil($totalQuota / 10 / 1024 / 1024) * 1024 * 1024, 1024 * 1024); // Min 1 MB
        $maxAvailable = $totalQuota - $webdiskUsed;
        $start = max($mailUsed, $step);
        
        $steps = array();
        for ($i = $start; $i <= $maxAvailable; $i += $step) {
            $steps[] = $i;
        }
        
        // Mindestens ein Wert
        if (empty($steps)) {
            $steps[] = $start;
        }
        
        return $steps;
    }
    
    /**
     * PUBLIC API: Quota für User berechnen
     * Kann von außen aufgerufen werden
     */
    public function calculateQuota($userId, $type = 'mail') {
        global $groupRow;
        
        // Prüfe ob aktiviert
        $active = $this->GetGroupOptionValue('betterquota_mode');
        if ($active == 0) {
            return false; // Nicht aktiviert, Standard-Quota nutzen
        }
        
        // Settings laden
        $settings = $this->getUserSettings($userId);
        
        // Auto-Modus?
        if ($active == 2 || $settings['mode'] == 0) {
            // Gemeinsamer Speicher
            $totalQuota = (int)$groupRow['storage'] + (int)$groupRow['webdisk'];
            return $totalQuota;
        }
        
        // Manuell-Modus
        if ($type === 'mail') {
            return (int)$settings['mail_quota'];
        } else {
            return (int)$settings['webdisk_quota'];
        }
    }
}

/**
 * Plugin registrieren (mit Safety-Check!)
 */
if (isset($plugins) && is_object($plugins)) {
    $plugins->registerPlugin('BetterQuotaV2_Plugin');
} else {
    // CLI-Modus oder Test-Kontext - nicht registrieren
    // Das ist OK, Plugin kann trotzdem geladen werden
}
?>
