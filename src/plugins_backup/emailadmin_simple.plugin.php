<?php
/**
 * EmailAdmin Simple - Single Page Version (OHNE Actions)
 * Alle Features auf EINER Seite, Tab-Wechsel via JavaScript
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class EmailAdminSimplePlugin extends BMPlugin
{
	function __construct()
	{
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'EmailAdmin Simple';
		$this->version = '1.0';
		$this->admin_pages = true;
		$this->admin_page_title = 'EmailAdmin Simple';
		$this->admin_page_icon = 'bms_logo.png';
	}
	
	function AdminHandler()
	{
		global $tpl, $db;
		
		// POST-Handling (Formulare)
		$message = '';
		$messageType = '';
		
		// Domain hinzufügen
		if(isset($_POST['add_domain'])) {
			$domain = strtolower(trim($_POST['domain']));
			if(preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
				$db->Query('INSERT INTO {pre}emp_domains (domain, user_id, empadmin, created_at) VALUES (?,1,1,NOW())', $domain);
				$message = 'Domain hinzugefügt!';
				$messageType = 'success';
			}
		}
		
		// Gruppe hinzufügen
		if(isset($_POST['add_group'])) {
			$name = trim($_POST['group_name']);
			$desc = trim($_POST['group_desc']);
			$permissions = trim($_POST['permissions']);
			$db->Query('INSERT INTO {pre}emp_groups (name, description, permissions, created_at) VALUES (?,?,?,NOW())', $name, $desc, $permissions);
			$message = 'Gruppe erstellt!';
			$messageType = 'success';
		}
		
		// Daten laden
		$stats = array();
		
		// Users
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}users');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		$stats['users'] = $row['cnt'];
		
		// Domains
		$domains = array();
		$res = $db->Query('SELECT * FROM {pre}emp_domains ORDER BY domain ASC LIMIT 100');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$domains[] = $row;
		}
		$res->Free();
		$stats['domains'] = count($domains);
		
		// Gruppen
		$groups = array();
		$res = $db->Query('SELECT * FROM {pre}emp_groups ORDER BY name ASC');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$groups[] = $row;
		}
		$res->Free();
		$stats['groups'] = count($groups);
		
		// Template-Variablen
		$tpl->assign('stats', $stats);
		$tpl->assign('domains', $domains);
		$tpl->assign('groups', $groups);
		$tpl->assign('message', $message);
		$tpl->assign('messageType', $messageType);
		$tpl->assign('pageURL', $this->_adminLink());
		
		// Single-Page Template
		$tpl->assign('page', $this->_templatePath('emailadmin.simple.tpl'));
	}
	
	function Install()
	{
		global $db;
		
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_domains (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			domain VARCHAR(255) NOT NULL,
			user_id INT UNSIGNED NOT NULL,
			empadmin INT UNSIGNED NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY (domain)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}emp_groups (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			permissions TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY (name)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		return true;
	}
	
	function Uninstall()
	{
		return true;
	}
}

$plugins->registerPlugin('EmailAdminSimplePlugin');

