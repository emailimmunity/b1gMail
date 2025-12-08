<?php
/**
 * EmailAdmin TEST - Minimal Version zum Debuggen
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class EmailAdminTestPlugin extends BMPlugin
{
	function __construct()
	{
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'EmailAdmin Test';
		$this->version = '0.1';
		$this->admin_pages = true;
		$this->admin_page_title = 'EmailAdmin Test';
		$this->admin_page_icon = 'bms_logo.png';
	}
	
	function AdminHandler()
	{
		global $tpl, $db;
		
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'dashboard';
		
		// Tabs
		$tabs = array(
			array('title' => 'Dashboard', 'link' => $this->_adminLink() . '&action=dashboard&', 'active' => $action == 'dashboard'),
			array('title' => 'Domains', 'link' => $this->_adminLink() . '&action=domains&', 'active' => $action == 'domains')
		);
		
		$tpl->assign('tabs', $tabs);
		$tpl->assign('pageURL', $this->_adminLink());
		
		// Output basierend auf Action
		if($action == 'domains') {
			echo '<h2>Domain Test</h2><p>Domain-Funktion wird getestet...</p>';
		} else {
			echo '<h2>Dashboard Test</h2><p>EmailAdmin Test funktioniert!</p>';
		}
		
		$tpl->assign('page', '');
	}
	
	function Install()
	{
		return true;
	}
	
	function Uninstall()
	{
		return true;
	}
}

$plugins->registerPlugin('EmailAdminTestPlugin');

