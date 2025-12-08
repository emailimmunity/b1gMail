<?php
/**
 * Password Management Pro Plugin
 * Version: 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Direct access not allowed');

require_once(B1GMAIL_DIR . 'serverlib/password.class.php');

class PasswordManagerPlugin extends BMPlugin
{
	const VERSION = '1.0.0';
	
	function __construct()
	{
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'Password Management Pro';
		$this->author = 'b1gMail Team';
		$this->version = self::VERSION;
		$this->website = 'https://www.b1gmail.com/';
		$this->description = 'Passwort-Modus verwalten (MD5/Hybrid/bcrypt)';
		
		$this->admin_pages = true;
		$this->admin_page_title = 'Password Management';
		$this->admin_page_icon = 'favicon.png';
	}
	
	function Install()
	{
		global $db, $bm_prefs;
		
		try {
			// Ensure columns exist
			$result = $db->Query('SHOW COLUMNS FROM {pre}prefs LIKE "password_mode"');
			if(!$result->FetchArray()) {
				@$db->Query('ALTER TABLE {pre}prefs ADD COLUMN password_mode VARCHAR(20) DEFAULT "hybrid"');
			}
			
			$result = $db->Query('SHOW COLUMNS FROM {pre}prefs LIKE "password_migration_enabled"');
			if(!$result->FetchArray()) {
				@$db->Query('ALTER TABLE {pre}prefs ADD COLUMN password_migration_enabled ENUM("yes","no") DEFAULT "yes"');
			}
		} catch (Exception $e) {
		}
		
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}password_migrations (
			id INT PRIMARY KEY AUTO_INCREMENT,
			userid INT NOT NULL,
			old_version INT,
			new_version INT,
			migrated_at DATETIME,
			triggered_by ENUM("login", "admin", "cron") DEFAULT "login",
			INDEX(userid),
			INDEX(migrated_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		
		return true;
	}
	
	function AdminHandler()
	{
		global $db, $bm_prefs, $tpl;
		
		$tpl->assign('page', B1GMAIL_DIR . 'plugins/templates/passwordmanager.dashboard.tpl');
		$tpl->assign('plugin_title', $this->name);
		
		if(isset($_REQUEST['do']))
		{
			switch($_REQUEST['do'])
			{
				case 'set_mode':
					if(isset($_REQUEST['mode']) && in_array($_REQUEST['mode'], ['md5', 'hybrid', 'bcrypt']))
					{
						$db->Query('UPDATE {pre}prefs SET password_mode=?', $_REQUEST['mode']);
						$tpl->assign('success', 'Passwort-Modus geaendert auf: ' . $_REQUEST['mode']);
					}
					break;
					
				case 'toggle_migration':
					$current = isset($bm_prefs['password_migration_enabled']) ? $bm_prefs['password_migration_enabled'] : 'yes';
					$new = $current == 'yes' ? 'no' : 'yes';
					$db->Query('UPDATE {pre}prefs SET password_migration_enabled=?', $new);
					$tpl->assign('success', 'Auto-Migration ' . ($new == 'yes' ? 'aktiviert' : 'deaktiviert'));
					break;
			}
		}
		
		$stats = $this->getPasswordStats();
		$tpl->assign('stats', $stats);
		
		$currentMode = isset($bm_prefs['password_mode']) ? $bm_prefs['password_mode'] : 'hybrid';
		$tpl->assign('current_mode', $currentMode);
		$tpl->assign('currentMode', $currentMode);
		
		return true;
	}
	
	private function getPasswordStats()
	{
		global $db;
		
		$stats = [];
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users');
		list($stats['total']) = $res->FetchArray(MYSQLI_NUM);
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE password_version IS NULL OR password_version = 0');
		list($stats['md5']) = $res->FetchArray(MYSQLI_NUM);
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE password_version = 1');
		list($stats['bcrypt']) = $res->FetchArray(MYSQLI_NUM);
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE password_version = 2');
		list($stats['bcrypt_sha256']) = $res->FetchArray(MYSQLI_NUM);
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE password_version = 3');
		list($stats['argon2']) = $res->FetchArray(MYSQLI_NUM);
		
		$res = $db->Query('SELECT COUNT(*) FROM {pre}password_migrations WHERE migrated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
		list($stats['migrations_7d']) = $res->FetchArray(MYSQLI_NUM);
		
		$stats['md5_percent'] = $stats['total'] > 0 ? round(($stats['md5'] / $stats['total']) * 100, 1) : 0;
		$stats['bcrypt_percent'] = $stats['total'] > 0 ? round(($stats['bcrypt'] / $stats['total']) * 100, 1) : 0;
		
		return $stats;
	}
}

$plugins->registerPlugin('PasswordManagerPlugin');
