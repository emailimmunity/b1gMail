<?php
/**
 * b1gMail Unified System - Navigation Helper
 * 
 * Generiert gruppenbasierte Navigation für Webmail-Interface
 */

if(!defined('B1GMAIL_INIT'))
    die('Direct access not allowed');

require_once(__DIR__ . '/permissions.class.php');

/**
 * Lädt Navigation basierend auf User-Gruppe
 */
function getUnifiedNavigation($userID) {
    global $db;
    
    $navigation = array();
    
    // User-Daten laden
    $res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userID);
    if($res->RowCount() == 0) {
        return $navigation;
    }
    $user = $res->FetchArray(MYSQLI_ASSOC);
    $gruppe = $user['gruppe'];
    
    // Basis-Navigation (für alle)
    $navigation[] = array(
        'title' => 'Webmail',
        'icon' => 'mail',
        'link' => 'index.php?action=start',
        'active' => false
    );
    
    $navigation[] = array(
        'title' => 'Kalender',
        'icon' => 'calendar',
        'link' => 'index.php?action=calendar',
        'active' => false
    );
    
    $navigation[] = array(
        'title' => 'Kontakte',
        'icon' => 'contacts',
        'link' => 'index.php?action=contacts',
        'active' => false
    );
    
    // Reseller-Navigation (gruppe = 50)
    if($gruppe == 50 && BMPermissions::isReseller($userID)) {
        $navigation[] = array(
            'title' => 'Mandanten-Verwaltung',
            'icon' => 'business',
            'link' => 'unified-reseller.php',
            'active' => false,
            'badge' => 'Reseller',
            'submenu' => array(
                array('title' => 'Dashboard', 'link' => 'unified-reseller.php?action=dashboard'),
                array('title' => 'Mandanten', 'link' => 'unified-reseller.php?action=tenants'),
                array('title' => 'Domains', 'link' => 'unified-reseller.php?action=domains'),
                array('title' => 'User', 'link' => 'unified-reseller.php?action=users'),
                array('title' => 'Billing', 'link' => 'unified-reseller.php?action=billing'),
            )
        );
    }
    
    // Domain-Admin-Navigation (gruppe = 10)
    if($gruppe == 10 && BMPermissions::isDomainAdmin($userID)) {
        $navigation[] = array(
            'title' => 'Domain-Verwaltung',
            'icon' => 'settings',
            'link' => 'unified-domain-admin.php',
            'active' => false,
            'badge' => 'Admin',
            'submenu' => array(
                array('title' => 'Dashboard', 'link' => 'unified-domain-admin.php?action=dashboard'),
                array('title' => 'User', 'link' => 'unified-domain-admin.php?action=users'),
                array('title' => 'Subdomains', 'link' => 'unified-domain-admin.php?action=subdomains'),
                array('title' => 'E-Mail-Konten', 'link' => 'unified-domain-admin.php?action=email'),
                array('title' => 'Statistiken', 'link' => 'unified-domain-admin.php?action=stats'),
            )
        );
    }
    
    // Subdomain-Navigation (gruppe >= 5 UND Grant vorhanden)
    if($gruppe >= 5) {
        // Prüfe ob User Subdomain-Grants hat
        $hasGrants = hasSubdomainGrants($userID);
        if($hasGrants) {
            $navigation[] = array(
                'title' => 'Meine Subdomains',
                'icon' => 'globe',
                'link' => 'index.php?page=prefs&action=subdomains',
                'active' => false
            );
        }
    }
    
    // Einstellungen (für alle)
    $navigation[] = array(
        'title' => 'Einstellungen',
        'icon' => 'settings',
        'link' => 'index.php?page=prefs',
        'active' => false
    );
    
    // Super-Admin Link (nur für gruppe=99)
    if($gruppe == 99 && BMPermissions::isSuperAdmin($userID)) {
        $navigation[] = array(
            'title' => 'System-Admin',
            'icon' => 'admin',
            'link' => '/admin/',
            'active' => false,
            'badge' => 'Super-Admin',
            'external' => true
        );
    }
    
    return $navigation;
}

/**
 * Prüft ob User Subdomain-Grants hat
 */
function hasSubdomainGrants($userID) {
    global $db;
    
    $user = GetUserRow($userID);
    $userGroupID = $user['gruppe'];
    
    // Prüfe ob Grants für die Gruppe existieren
    $res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}sdm_grants WHERE group_id=?', $userGroupID);
    $row = $res->FetchArray(MYSQLI_ASSOC);
    
    return $row['cnt'] > 0;
}

/**
 * Rendert Navigation als HTML
 */
function renderUnifiedNavigation($userID) {
    $nav = getUnifiedNavigation($userID);
    
    $html = '<div class="unified-navigation">';
    $html .= '<ul class="nav-list">';
    
    foreach($nav as $item) {
        $activeClass = $item['active'] ? ' active' : '';
        $externalAttr = isset($item['external']) && $item['external'] ? ' target="_blank"' : '';
        
        $html .= '<li class="nav-item' . $activeClass . '">';
        $html .= '<a href="' . htmlspecialchars($item['link']) . '"' . $externalAttr . '>';
        
        if(isset($item['icon'])) {
            $html .= '<i class="icon-' . htmlspecialchars($item['icon']) . '"></i>';
        }
        
        $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';
        
        if(isset($item['badge'])) {
            $html .= '<span class="badge">' . htmlspecialchars($item['badge']) . '</span>';
        }
        
        $html .= '</a>';
        
        // Submenu
        if(isset($item['submenu']) && count($item['submenu']) > 0) {
            $html .= '<ul class="submenu">';
            foreach($item['submenu'] as $subitem) {
                $html .= '<li><a href="' . htmlspecialchars($subitem['link']) . '">';
                $html .= htmlspecialchars($subitem['title']);
                $html .= '</a></li>';
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Gibt Navigation als Array für Template zurück
 */
function getUnifiedNavigationForTemplate($userID) {
    return getUnifiedNavigation($userID);
}
