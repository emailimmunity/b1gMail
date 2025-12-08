<?php
/**
 * Design Dashboard - Quick Design Management
 */
if(!defined('B1GMAIL_INIT'))
    die('Access denied');

// Include shared admin initialization (sets $pageURL)
require_once(__DIR__ . '/_init.php');

require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/designs/DesignManager.class.php');
$designManager = new DesignManager();

// Get all designs
$allDesigns = $designManager->getAllDesigns(true);

// Get active design via DB for performance
$activeDesign = null;
try {
    global $db;
    $res = $db->Query('SELECT * FROM {pre}mf_designs WHERE is_active=1 LIMIT 1');
    if($res) {
        $row = $res->FetchArray(MYSQLI_ASSOC);
        if($row) $activeDesign = $row;
    }
} catch(Exception $e) { }

// Design statistics
$designStats = array(
    'total' => count($allDesigns),
    'active' => count(array_filter($allDesigns, function($d){return intval($d['is_active'])===1;})),
    'aikq_designs' => count(array_filter($allDesigns, function($d){return strpos($d['template_path'], 'aikq')!==false;})),
    'custom_designs' => count(array_filter($allDesigns, function($d){return strpos($d['template_path'], 'aikq')===false;}))
);

// Domains using active design
$domainsCount = 0;
if($activeDesign) {
    try {
        $r = $db->Query('SELECT COUNT(*) as c FROM {pre}mf_domains WHERE design_id=?', $activeDesign['id']);
        $rr = $r->FetchArray(MYSQLI_ASSOC);
        $domainsCount = intval($rr['c']);
    } catch(Exception $e) { }
}

// aikQ designs
$aikqDesigns = array_filter($allDesigns, function($d){return strpos($d['template_path'], 'aikq')!==false;});

// Assign
$tpl->assign('activeDesign', $activeDesign);
$tpl->assign('designStats', $designStats);
$tpl->assign('domainsCount', $domainsCount);
$tpl->assign('aikqDesigns', $aikqDesigns);
$tpl->assign('allDesigns', $allDesigns);
?>
