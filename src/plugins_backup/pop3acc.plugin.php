<?php
declare(strict_types=1);

/**
 * POP3 Collection Services Plugin
 * 
 * Provides an overview of users with POP3 collection services.
 * Allows administrators to manage and monitor POP3 accounts.
 * 
 * @version 1.2.0
 * @since PHP 8.2
 * @license GPL
 */
class pop3acc extends BMPlugin 
{
	/**
	 * Plugin constants
	 */
	private const PLUGIN_NAME 			= 'POP3-Sammeldienste';
	private const PLUGIN_VERSION 		= '1.2.0';
	private const PLUGIN_DESIGNEDFOR 	= '7.4.1';
	private const PLUGIN_AUTHOR 		= 'Peter Michalk';

	/**
	 * Action constants for admin pages
	 */
	private const ADMIN_PAGE1 			= 'page1';
	private const ADMIN_PAGE2 			= 'page2';
	private const ADMIN_PAGE3 			= 'page3';

	/**
	 * Plugin constructor
	 * 
	 * Initializes all plugin properties and configurations.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->name 				= self::PLUGIN_NAME;
		$this->version 				= self::PLUGIN_VERSION;
		$this->designedfor 			= self::PLUGIN_DESIGNEDFOR;
		$this->type 				= BMPLUGIN_DEFAULT;

		$this->author 				= self::PLUGIN_AUTHOR;

		$this->admin_pages 			= true;
		$this->admin_page_title 	= self::PLUGIN_NAME;
		$this->admin_page_icon 		= 'pop3acc_icon.png';
	}

	/**
	 * Admin handler for plugin pages
	 * 
	 * Manages navigation and display of admin pages.
	 * Creates tabs for different areas and forwards to corresponding
	 * template files.
	 * 
	 * @return void
	 * @global object $tpl Template engine
	 * @global array $lang_admin Language variables for admin area
	 */
	public function AdminHandler(): void
	{
		global $tpl, $lang_admin;

		// Plugin call without action
		$action = $_REQUEST['action'] ?? self::ADMIN_PAGE1;

		$tabs = [
			0 => [
				'title' => $lang_admin['overview'],
				'link' => $this->_adminLink() . '&action=' . self::ADMIN_PAGE1 . '&',
				'active' => $action === self::ADMIN_PAGE1,
				'icon' => '../plugins/templates/images/pop3acc_logo.png'
			],
			1 => [
				'title' => $lang_admin['all'],
				'link' => $this->_adminLink() . '&action=' . self::ADMIN_PAGE2 . '&',
				'active' => $action === self::ADMIN_PAGE2,
				'icon' => '../plugins/templates/images/pop3acc_logo.png'
			],
			2 => [
				'title' => $lang_admin['pop3acc_refresh'],
				'link' => $this->_adminLink() . '&action=' . self::ADMIN_PAGE3 . '&',
				'active' => $action === self::ADMIN_PAGE3,
				'icon' => './templates/images/ico_prefs_receiving.png'
			]
		];

		$tpl->assign('tabs', $tabs);

		// Plugin call with action
		if($action === self::ADMIN_PAGE1) {
			$tpl->assign('page', $this->_templatePath('pop3acc1.pref.tpl'));
			$this->_Page1();
		} elseif($action === self::ADMIN_PAGE2) {
			$tpl->assign('page', $this->_templatePath('pop3acc2.pref.tpl'));
			$this->_Page2();
		} elseif($action === self::ADMIN_PAGE3) {
			$tpl->assign('page', $this->_templatePath('pop3acc3.pref.tpl'));
			$this->_Page3();
		}
	}

	/**
	 * Language variables handler
	 * 
	 * Loads and defines all required language variables for the plugin.
	 * Overrides or extends existing language variables.
	 * 
	 * @param array $lang_user Reference to user language variables
	 * @param array $lang_client Reference to client language variables
	 * @param array $lang_custom Reference to custom language variables
	 * @param array $lang_admin Reference to admin language variables
	 * @param string $lang Current language
	 * @return void
	 * @global array $lang_user Global user language variables
	 */
	public function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang): void
	{
		global $lang_user;

		$lang_admin['pop3acc_name']				= 'POP3-Sammeldienste';
		$lang_admin['pop3acc_text']				= 'Zeigt eine kleine Übersicht, welcher Benutzer ein POP3 Sammeldienst besitzt.';
		$lang_admin['pop3acc_refresh']			= 'Abrufen';
		$lang_admin['pop3acc_starttext']		= 'Hier können Sie E-Mails von allen externen POP3-Accounts einsammeln.';
		
		$lang_admin['lastfetch']				= $lang_user['lastfetch'];
		$lang_admin['extpop3']					= $lang_user['extpop3'];
		$lang_admin['keepmails']				= $lang_user['keepmails'];
	}

	/**
	 * Plugin installation
	 * 
	 * Performs all necessary steps for plugin installation.
	 * Logs the installation process.
	 * 
	 * @return bool True on successful installation, false on errors
	 */
	public function Install(): bool
	{
		// log
		PutLog(sprintf('%s v%s installed',
			$this->name,
			$this->version),
			PRIO_PLUGIN,
			__FILE__,
			__LINE__);

		return(true);
	}

	/**
	 * Plugin uninstallation
	 * 
	 * Performs all necessary steps for plugin uninstallation.
	 * Logs the uninstallation process.
	 * 
	 * @return bool True on successful uninstallation, false on errors
	 */
	public function Uninstall(): bool
	{
		PutLog(sprintf('%s v%s uninstalled',
			$this->name,
			$this->version),
			PRIO_PLUGIN,
			__FILE__,
			__LINE__);

		return(true);
	}

	/**
	 * Admin page 1: POP3 overview
	 * 
	 * Displays an overview of users with POP3 collection services.
	 * Allows deletion of POP3 accounts and shows sorting options.
	 * 
	 * @return void
	 * @global object $tpl Template engine
	 * @global object $db Database connection
	 */
	private function _Page1(): void
	{
		global $tpl, $db;

		// delete
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'delete')
		{
			$db->Query('DELETE FROM {pre}pop3 WHERE id=?',
				(int) $_REQUEST['id']);

			if($db->AffectedRows() == 1)
			{
				$db->Query('DELETE FROM {pre}uidindex WHERE pop3=?',
					(int) $_REQUEST['id']);
			}
		}
		
		// sort options
		$sortBy = isset($_REQUEST['sortBy'])
					? $_REQUEST['sortBy']
					: 'id';
		$sortOrder = isset($_REQUEST['sortOrder'])
						? strtolower($_REQUEST['sortOrder'])
						: 'asc';

		// Query pop3acc and pop3user for Page1 display
		$pop3acc = $pop3user = [];
		$res = $db->Query('SELECT id, email, gruppe FROM {pre}users ORDER by ' . $sortBy . ' ' . $sortOrder);
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$res2 = $db->Query('SELECT * FROM {pre}pop3 WHERE user=?', 
				(int) $row['id']);

			if($res2->RowCount() >= 1)
			{
				while($row2 = $res2->FetchArray(MYSQLI_ASSOC))
				{
					$pop3acc[$row2['id']] = [
						'user_id' => $row['id'],
						'id' => $row2['id'],
						'user' => $row2['p_user'],
						'host' => $row2['p_host'],
						'port' => $row2['p_port'],
						'ssl' => $row2['p_ssl'],
						'last' => $row2['last_fetch'],
						'keep' => $row2['p_keep']
					];
				}

				$res3 = $db->Query('SELECT ownpop3 FROM {pre}gruppen WHERE id=?', 
					(int) $row['gruppe']);
				$gruppe = $res3->FetchArray(MYSQLI_ASSOC);
				$res3->Free();

				$pop3user[$row['id']] = [
					'id' => $row['id'],
					'email' => $row['email'],
					'count' => $res2->RowCount(),
					'gruppe_p' => $gruppe['ownpop3']
				];
			}
			$res2->Free();
		}
		$res->Free();

		$tpl->assign('sortBy', $sortBy);
		$tpl->assign('sortOrder', $sortOrder);

		$tpl->assign('pop3acc', $pop3acc);
		$tpl->assign('pop3user', $pop3user);
	}

	/**
	 * Admin page 2: All POP3 accounts
	 * 
	 * Shows all POP3 accounts with detailed information.
	 * Provides sorting capabilities for better management.
	 * 
	 * @return void
	 * @global object $tpl Template engine
	 * @global object $db Database connection
	 */
	private function _Page2(): void
	{
		global $tpl, $db;
		// pop3acc array
		$pop3acc = [];

		// sort options
		$sortBy = isset($_REQUEST['sortBy'])
					? $_REQUEST['sortBy']
					: 'email';
		$sortOrder = isset($_REQUEST['sortOrder'])
						? strtolower($_REQUEST['sortOrder'])
						: 'asc';

		$res = $db->Query('SELECT id, email FROM {pre}users ORDER by  ' . $sortBy . ' ' . $sortOrder);
		while($row = $res->FetchArray())
		{
			$res2 = $db->Query('SELECT * FROM {pre}pop3 WHERE user=?', 
			$row['id']);

			if($res2->RowCount() >= 1)
			{
				while($row2 = $res2->FetchArray())
				{
					$pop3acc[$row2['id']] = [
						'id' => $row['id'],
						'email' => $row['email'],
						'pop3_id' => $row2['id'],
						'host' => $row2['p_host'],
						'port' => $row2['p_port'],
						'ssl' => $row2['p_ssl'],
						'last' => $row2['last_fetch'],
						'keep' => $row2['p_keep']
					];
				}
			}
			$res2->Free();
		}
		$res->Free();

		$tpl->assign('sortBy', $sortBy);
		$tpl->assign('sortOrder', $sortOrder);

		$tpl->assign('pop3acc', $pop3acc);
	}

	/**
	 * Admin page 3: POP3 refresh
	 * 
	 * Handles POP3 account refresh functionality.
	 * Shows start button for manual refresh process.
	 * 
	 * @return void
	 * @global object $tpl Template engine
	 */
	private function _Page3(): void
	{
		global $tpl;
		$tpl->assign('start', isset($_REQUEST['start']));
	}
}

/**
 * Plugin registration
 * 
 * Registers the plugin in the b1gMail plugin system.
 * This line must be at the end of the file so that the plugin
 * is recognized and loaded by b1gMail.
 * 
 * @global object $plugins b1gMail plugin manager
 */
$plugins->registerPlugin('pop3acc');