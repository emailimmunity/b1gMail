<?php
/**
 * Reseller Dashboard
 * 
 * Admin-Interface für Reseller (type=1)
 * - Mandanten verwalten
 * - Domains zuweisen
 * - User-Limits setzen
 * - Billing
 */

if(!defined('B1GMAIL_INIT'))
{
	define('B1GMAIL_INIT', true);
	require_once('../serverlib/init.inc.php');
}

// Reseller-Check
if(!isset($_SESSION['adminID']) || $_SESSION['bm_adminType'] != 1)
	die('Access denied - Reseller only!');

$resellerID = $_SESSION['adminID'];

// Actions
$action = $_REQUEST['action'] ?? 'dashboard';

switch($action)
{
	// === DASHBOARD ===
	case 'dashboard':
		// Statistiken laden
		$stats = array();
		
		// Anzahl Mandanten
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}reseller_tenants 
			WHERE reseller_id=? AND status="active"', $resellerID);
		list($stats['tenants']) = $res->FetchArray(MYSQLI_NUM);
		
		// Anzahl Domains
		$res = $db->Query('SELECT COUNT(DISTINCT rt.domain) as cnt 
			FROM {pre}reseller_tenants t
			JOIN {pre}reseller_domains rt ON rt.tenant_id = t.id
			WHERE t.reseller_id=?', $resellerID);
		list($stats['domains']) = $res->FetchArray(MYSQLI_NUM);
		
		// Anzahl User
		$res = $db->Query('SELECT COUNT(u.id) as cnt 
			FROM {pre}users u
			JOIN {pre}reseller_domains rd ON SUBSTRING_INDEX(u.email, "@", -1) = rd.domain
			JOIN {pre}reseller_tenants rt ON rt.id = rd.tenant_id
			WHERE rt.reseller_id=?', $resellerID);
		list($stats['users']) = $res->FetchArray(MYSQLI_NUM);
		
		// Mandanten-Liste
		$tenants = array();
		$res = $db->Query('SELECT * FROM {pre}reseller_tenants 
			WHERE reseller_id=? AND status!="deleted"
			ORDER BY tenant_name', $resellerID);
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Domains zählen
			$dres = $db->Query('SELECT COUNT(*) as cnt FROM {pre}reseller_domains WHERE tenant_id=?', $row['id']);
			list($row['domain_count']) = $dres->FetchArray(MYSQLI_NUM);
			
			// User zählen
			$ures = $db->Query('SELECT COUNT(u.id) as cnt 
				FROM {pre}users u
				JOIN {pre}reseller_domains rd ON SUBSTRING_INDEX(u.email, "@", -1) = rd.domain
				WHERE rd.tenant_id=?', $row['id']);
			list($row['user_count']) = $ures->FetchArray(MYSQLI_NUM);
			
			$tenants[] = $row;
		}
		
		$tpl->assign('stats', $stats);
		$tpl->assign('tenants', $tenants);
		$tpl->assign('pageTitle', 'Reseller Dashboard');
		$tpl->display('admin/reseller-dashboard.tpl');
		break;
	
	// === MANDANT ERSTELLEN ===
	case 'create_tenant':
		if(isset($_POST['submit']))
		{
			$tenantName = trim($_POST['tenant_name']);
			$company = trim($_POST['company']);
			$contactPerson = trim($_POST['contact_person']);
			$contactEmail = trim($_POST['contact_email']);
			$maxUsers = (int)$_POST['max_users'];
			$maxDomains = (int)$_POST['max_domains'];
			$maxStorageGB = (int)$_POST['max_storage_gb'];
			
			$db->Query('INSERT INTO {pre}reseller_tenants 
				(reseller_id, tenant_name, company, contact_person, contact_email, 
				 max_users, max_domains, max_storage_gb, status, created_at)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active", NOW())',
				$resellerID, $tenantName, $company, $contactPerson, $contactEmail,
				$maxUsers, $maxDomains, $maxStorageGB);
			
			header('Location: reseller-dashboard.php?action=dashboard&success=created');
			exit;
		}
		
		$tpl->assign('pageTitle', 'Neuer Mandant');
		$tpl->display('admin/reseller-create-tenant.tpl');
		break;
	
	// === MANDANT BEARBEITEN ===
	case 'edit_tenant':
		$tenantID = (int)$_REQUEST['id'];
		
		// Prüfen ob Mandant zum Reseller gehört
		$res = $db->Query('SELECT * FROM {pre}reseller_tenants 
			WHERE id=? AND reseller_id=?', $tenantID, $resellerID);
		if($res->RowCount() == 0)
			die('Access denied');
		
		$tenant = $res->FetchArray(MYSQLI_ASSOC);
		
		if(isset($_POST['submit']))
		{
			$db->Query('UPDATE {pre}reseller_tenants SET
				tenant_name=?, company=?, contact_person=?, contact_email=?,
				max_users=?, max_domains=?, max_storage_gb=?
				WHERE id=?',
				$_POST['tenant_name'], $_POST['company'], $_POST['contact_person'],
				$_POST['contact_email'], $_POST['max_users'], $_POST['max_domains'],
				$_POST['max_storage_gb'], $tenantID);
			
			header('Location: reseller-dashboard.php?action=dashboard&success=updated');
			exit;
		}
		
		// Domains des Mandanten laden
		$domains = array();
		$res = $db->Query('SELECT * FROM {pre}reseller_domains WHERE tenant_id=?', $tenantID);
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$domains[] = $row;
		
		$tpl->assign('tenant', $tenant);
		$tpl->assign('domains', $domains);
		$tpl->assign('pageTitle', 'Mandant bearbeiten: ' . $tenant['tenant_name']);
		$tpl->display('admin/reseller-edit-tenant.tpl');
		break;
	
	// === DOMAIN HINZUFÜGEN ===
	case 'add_domain':
		$tenantID = (int)$_REQUEST['tenant_id'];
		
		// Prüfen ob Mandant zum Reseller gehört
		$res = $db->Query('SELECT * FROM {pre}reseller_tenants 
			WHERE id=? AND reseller_id=?', $tenantID, $resellerID);
		if($res->RowCount() == 0)
			die('Access denied');
		
		if(isset($_POST['submit']))
		{
			$domain = strtolower(trim($_POST['domain']));
			
			// Domain bereits vorhanden?
			$res = $db->Query('SELECT * FROM {pre}reseller_domains WHERE domain=?', $domain);
			if($res->RowCount() > 0)
			{
				$tpl->assign('error', 'Domain bereits vergeben!');
			}
			else
			{
				$db->Query('INSERT INTO {pre}reseller_domains 
					(tenant_id, domain, mx_status, created_at)
					VALUES (?, ?, "ok", NOW())',
					$tenantID, $domain);
				
				header('Location: reseller-dashboard.php?action=edit_tenant&id=' . $tenantID . '&success=domain_added');
				exit;
			}
		}
		
		$tpl->assign('tenant_id', $tenantID);
		$tpl->assign('pageTitle', 'Domain hinzufügen');
		$tpl->display('admin/reseller-add-domain.tpl');
		break;
	
	// === MANDANT LÖSCHEN ===
	case 'delete_tenant':
		$tenantID = (int)$_REQUEST['id'];
		
		// Prüfen ob Mandant zum Reseller gehört
		$res = $db->Query('SELECT * FROM {pre}reseller_tenants 
			WHERE id=? AND reseller_id=?', $tenantID, $resellerID);
		if($res->RowCount() == 0)
			die('Access denied');
		
		if(isset($_POST['confirm']))
		{
			// Soft-Delete
			$db->Query('UPDATE {pre}reseller_tenants SET status="deleted" WHERE id=?', $tenantID);
			
			header('Location: reseller-dashboard.php?action=dashboard&success=deleted');
			exit;
		}
		
		$tenant = $res->FetchArray(MYSQLI_ASSOC);
		$tpl->assign('tenant', $tenant);
		$tpl->assign('pageTitle', 'Mandant löschen');
		$tpl->display('admin/reseller-delete-tenant.tpl');
		break;
	
	default:
		die('Unknown action');
}
