<?php
/**
 * Multidomain-Admin Dashboard
 * Verwaltet mehrere Domains gleichzeitig (type=2)
 */

if(!defined('B1GMAIL_INIT'))
{
	define('B1GMAIL_INIT', true);
	require_once('../serverlib/init.inc.php');
}

// Check
if(!isset($_SESSION['adminID']) || $_SESSION['bm_adminType'] != 2)
	die('Access denied');

$adminID = $_SESSION['adminID'];

// Domains laden
$res = $db->Query('SELECT * FROM {pre}admins WHERE adminid=?', $adminID);
$adminRow = $res->FetchArray(MYSQLI_ASSOC);
$adminDomains = explode(',', $adminRow['domain']);

$currentDomain = $_SESSION['multidomain_current'] ?? $adminDomains[0];

if(isset($_POST['switch_domain']) && in_array($_POST['domain'], $adminDomains))
{
	$_SESSION['multidomain_current'] = $_POST['domain'];
	$currentDomain = $_POST['domain'];
}

$action = $_REQUEST['action'] ?? 'dashboard';

if($action == 'dashboard')
{
	$domainStats = array();
	foreach($adminDomains as $domain)
	{
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE email LIKE ?', '%@'.$domain);
		list($userCount) = $res->FetchArray(MYSQLI_NUM);
		$domainStats[] = array('domain' => $domain, 'users' => $userCount);
	}
	
	$tpl->assign('domains', $adminDomains);
	$tpl->assign('currentDomain', $currentDomain);
	$tpl->assign('domainStats', $domainStats);
	$tpl->display('admin/multidomain-dashboard.tpl');
}
