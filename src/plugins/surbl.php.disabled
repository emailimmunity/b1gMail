<?php
declare(strict_types=1);

/**
 * SURBL Plugin
 * 
 * SURBL (Spam URI Realtime Blocklists) plugin for filtering spam emails
 * based on URLs contained in the message content. Checks URLs against
 * various SURBL servers and applies scoring to identify spam.
 * 
 * @version 1.1.0
 * @since PHP 8.3
 * @license GPL
 */
class surbl extends BMPlugin 
{
	/**
	 * Action constants for admin pages
	 */
	private const ADMIN_PAGE1 = 'page1';
	private const ADMIN_PAGE2 = 'page2';
	private const ADMIN_PAGE3 = 'page3';
	private const ADMIN_PAGE4 = 'page4';

	/**
	 * PHP 8.3: Readonly properties for immutable values
	 */
	private readonly string $pluginName;
	private readonly string $pluginVersion;
	private readonly string $pluginAuthor;

	/**
	 * Plugin constructor
	 * 
	 * Initializes all plugin properties and configurations.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// PHP 8.3: Initialize readonly properties
		$this->pluginName 			= 'SURBL';
		$this->pluginVersion 		= '1.1.0';
		$this->pluginAuthor 		= 'Peter Michalk';

		$this->name					= $this->pluginName;
		$this->version				= $this->pluginVersion;
		$this->designedfor			= '7.3.0';
		$this->type					= BMPLUGIN_DEFAULT;

		$this->author				= $this->pluginAuthor;

		$this->admin_pages			= true;
		$this->admin_page_title		= $this->pluginName;
		$this->admin_page_icon		= "surbl_icon.png";
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

		// Tabs in admin area
		$tabs = [
			0 => [
				'title'		=> $lang_admin['surbl_name'],
				'link'		=> $this->_adminLink() . '&action=' . self::ADMIN_PAGE1 . '&',
				'active'	=> $action === self::ADMIN_PAGE1,
				'icon'		=> '../plugins/templates/images/surbl_logo.png'
			],
			1 => [
				'title'		=> $lang_admin['surbl_list'],
				'link'		=> $this->_adminLink() . '&action=' . self::ADMIN_PAGE2 . '&',
				'active'	=> $action === self::ADMIN_PAGE2,
				'icon'		=> '../plugins/templates/images/surbl_logo.png'
			],
			2 => [
				'title'		=> $lang_admin['surbl_blacklist'],
				'link'		=> $this->_adminLink() . '&action=' . self::ADMIN_PAGE3 . '&',
				'active'	=> $action === self::ADMIN_PAGE3,
				'icon'		=> '../plugins/templates/images/surbl_logo.png'
			],
			3 => [
				'title'		=> $lang_admin['surbl_whitelist'],
				'link'		=> $this->_adminLink() . '&action=' . self::ADMIN_PAGE4 . '&',
				'active'	=> $action === self::ADMIN_PAGE4,
				'icon'		=> '../plugins/templates/images/surbl_logo.png'
			]
		];
		$tpl->assign('tabs', $tabs);

		// Plugin call with action
		if($_REQUEST['action'] === self::ADMIN_PAGE1) {
			$tpl->assign('page', $this->_templatePath('surbl1.pref.tpl'));
			$this->_Page1();
		} elseif($_REQUEST['action'] === self::ADMIN_PAGE2) {
			$tpl->assign('page', $this->_templatePath('surbl2.pref.tpl'));
			$this->_Page2();
		} elseif($_REQUEST['action'] === self::ADMIN_PAGE3) {
			$tpl->assign('page', $this->_templatePath('surbl3.pref.tpl'));
			$this->_Page3();
		} elseif($_REQUEST['action'] === self::ADMIN_PAGE4) {
			$tpl->assign('page', $this->_templatePath('surbl4.pref.tpl'));
			$this->_Page4();
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
	 */
	public function OnReadLang(array &$lang_user, array &$lang_client, array &$lang_custom, array &$lang_admin, string $lang): void
	{
		$lang_admin['surbl_name']					= "SURBL";
		$lang_admin['surbl_text']					= "SURBL's sind Listen von Webseiten, die in unerwünschten E-Mails auftauchen.";		

		$lang_admin['surbl_filter']					= "SURBL-Filter";
		$lang_admin['surbl_list']					= "SURBL-Liste";
		$lang_admin['surbl_blacklist']				= "Blacklist";
		$lang_admin['surbl_whitelist']				= "Whitelist";
		$lang_admin['surbl_server']					= "SURBL-Server";
		$lang_admin['surbl_required']				= "Erforderlicher SURBL Score";
	}

	/**
	 * Plugin installation
	 * 
	 * Performs all necessary steps for plugin installation.
	 * Creates database tables, configures settings and logs
	 * the installation process.
	 * 
	 * @return bool True on successful installation, false on errors
	 */
	public function Install(): bool
	{
		PutLog('Plugin "'. $this->name .' - '. $this->version .'" was successfully installed.', PRIO_PLUGIN, __FILE__, __LINE__);
		return true;
	}

	/**
	 * Plugin uninstallation
	 * 
	 * Performs all necessary steps for plugin uninstallation.
	 * Removes database tables, cleans up configurations and logs
	 * the uninstallation process.
	 * 
	 * @return bool True on successful uninstallation, false on errors
	 */
	public function Uninstall(): bool
	{
		PutLog('Plugin "'. $this->name .' - '. $this->version .'" was successfully uninstalled.', PRIO_PLUGIN, __FILE__, __LINE__);
		return true;
	}

	function _Page1()
	{
		global $tpl;

		// save
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'save')
		{
			$surblArray = explode("\n", $_REQUEST['surbl_bl']);
			foreach($surblArray as $key=>$val)
				if(($val = trim($val)) != '')
					$surblArray[$key] = $val;
				else 
					unset($surblArray[$key]);
			$surbl = implode(':', $surblArray);

			$this->_setPref("surbl_aktiv", 		isset($_REQUEST['surbl_aktiv']));
			$this->_setPref("surbl_surbl",		$surbl);
			$this->_setPref("surbl_required",	$_REQUEST['surbl_required']);
		}
		//reset
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'reset')
		{
			$this->_setPref("surbl_calls", 	0);
		}

		$surbl_bl 		= str_replace(':', "\n", $this->_getPref("surbl_surbl"));
		$tpl->assign('surbl_aktiv', 		$this->_getPref("surbl_aktiv"));
		$tpl->assign('surbl_bl',			$surbl_bl);
		$tpl->assign('surbl_required',		$this->_getPref("surbl_required"));
		$tpl->assign('surblCount',			$this->_getPref("surbl_calls"));
	}

	function _Page2()
	{
		global $tpl;

		// save
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'save')
		{
			$surblArray = explode("\n", $_REQUEST['surbl_list']);
			foreach($surblArray as $key=>$val)
				if(($val = trim($val)) != '')
					$surblArray[$key] = $val;
				else 
					unset($surblArray[$key]);
			$surbl = implode(':', $surblArray);

			$this->_setPref("surbl_list",		$surbl);
		}

		$surbl_list 		= str_replace(':', "\n", $this->_getPref("surbl_list"));
		$tpl->assign('surbl_list',			$surbl_list);

	}

	function _Page3()
	{
		global $tpl;

		// save
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'save')
		{
			$surblArray = explode("\n", $_REQUEST['surbl_bl']);
			foreach($surblArray as $key=>$val)
				if(($val = trim($val)) != '')
					$surblArray[$key] = $val;
				else 
					unset($surblArray[$key]);
			$surbl = implode(':', $surblArray);

			$this->_setPref("surbl_bl",		$surbl);
		}

		$surbl_bl 		= str_replace(':', "\n", $this->_getPref("surbl_bl"));
		$tpl->assign('surbl_bl',			$surbl_bl);

	}
	
	function _Page4()
	{
		global $tpl;

		// save
		if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'save')
		{
			$surblArray = explode("\n", $_REQUEST['surbl_wl']);
			foreach($surblArray as $key=>$val)
				if(($val = trim($val)) != '')
					$surblArray[$key] = $val;
				else 
					unset($surblArray[$key]);
			$surbl = implode(':', $surblArray);

			$this->_setPref("surbl_wl",		$surbl);
		}

		$surbl_wl 		= str_replace(':', "\n", $this->_getPref("surbl_wl"));
		$tpl->assign('surbl_wl',			$surbl_wl);

	}

	function AfterReceiveMail(&$mail, &$mailbox, &$user)
	{
		if($this->_getPref("surbl_aktiv"))
		{
			if(($mail->flags & FLAG_SPAM) != 0)
				return;

			$msg 			= $mail->GetTextParts();
			if($msg['html'] == null) {
				$msg 		= strip_tags($msg['text']);
			} else {
				$msg 		= strip_tags($msg['html']);
			}

			$ret_arr_full 	= array();
			if(preg_match_all('((ht|f)tp(s?)\://{1}\S+)', $msg, $ret_arr_full))
			{
				$surbl 			= $this->_getPref("surbl_surbl");
				$list 			= $this->_getPref("surbl_list");
				$wl 			= $this->_getPref("surbl_wl");
				$bl 			= $this->_getPref("surbl_bl");
				$surbl_required	= $this->_getPref("surbl_required");
				$surbl_calls	= $this->_getPref("surbl_calls");

				$surblServers 	= explode(':', $surbl);
				$grList			= explode(':', $list);
				$wlList			= explode(':', $wl);
				$blList			= explode(':', $bl);

				$posServers 	= 0;
				$domains 		= array();
				foreach($ret_arr_full[0] as $domain)
				{
					$uri		= $this->cutDomain($domain);
					
					if($uri !== false)
					{
						$domains[] 	= $uri;
					}
				}

				$checked 	= array();
				foreach($domains as $domain) 
				{
					$surblServers = explode(':', $surbl);
					// ip
					if($this->isIP($domain))
					{
						continue;
					}
					// whitelist -4
					if(in_array($domain, $wlList))
					{
						$posServers = $posServers-4;
						continue;
					}
					// blacklist +5
					if(in_array($domain, $blList))
					{
						$posServers = $posServers+5;
						continue;
					}
					// list +2
					if(in_array($domain, $grList))
					{
						$posServers = $posServers+2;
						continue;
					}
					// good domain and already surbl checked
					if(in_array($domain, $checked))
					{
						continue;
					}
					// surbl +2
					foreach($surblServers as $server)
					{
						$dns 				= @gethostbyname($domain . '.' . $server);
						$surbl_calls		= $surbl_calls+1;

						if($dns == "127.0.1.255")
						{
							PutLog(sprintf('SURBL - <%s> is not a valid Domain', $domain), PRIO_PLUGIN, __FILE__, __LINE__);
						} else if($dns == "127.0.1.2" OR $dns == "127.0.1.3") {							
							$posServers 	= $posServers+2;
							$list			= $list.":".$domain;
							$grList			= explode(':', $list);
						} else {
							$checked[] 		= $domain;
						}
					}
					$this->_setPref("surbl_calls",	$surbl_calls);
					$this->_setPref("surbl_list",	$list);
				}

				if($posServers >= $surbl_required)
				{
					PutLog(sprintf('Mail to %s identified as spam by SURBL Plugin with Score <%s>', $user->_id, $posServers), PRIO_PLUGIN, __FILE__, __LINE__);

					// SetSpamStatus
					$mailbox->SetSpamStatus($mail->id, true);
				}
			}
		}
	}

	/**
	 * Check if a string is a valid IP address
	 * 
	 * Validates if the given string represents a valid IPv4 address
	 * using regular expression pattern matching.
	 * 
	 * @param string $ipaddr The IP address string to validate
	 * @return bool True if valid IP address, false otherwise
	 */
	private function isIP(string $ipaddr): bool
	{
		if(preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ipaddr)) {
			return true;
		}
		return false;
	}

	/**
	 * Extract and clean domain from URL
	 * 
	 * Extracts the domain name from a full URL by removing protocol,
	 * www prefix, path, and query parameters. Returns false for
	 * domains that are too short to be valid.
	 * 
	 * @param string $url The full URL or domain to clean
	 * @return string|false The cleaned domain name or false if invalid
	 */
	private function cutDomain(string $url): string|false
	{
		// Early validation - check if input is empty or too short
		if (empty($url) || strlen($url) < 4) {
			return false;
		}

		// Convert to lowercase early for consistent processing
		$url = strtolower(trim($url));

		// Remove protocol prefixes using more efficient single regex
		$url = preg_replace('/^https?:\/\//', '', $url);
		
		// Remove www prefix (only at the beginning)
		if (str_starts_with($url, 'www.')) {
			$url = substr($url, 4);
		}

		// Split by common URL delimiters and take only the domain part
		$domain = strtok($url, '/?#:');
		
		// Additional cleanup - remove port numbers
		$domain = strtok($domain, ':');

		// Validate domain format and length
		if (!$this->isValidDomain($domain)) {
			return false;
		}

		return $domain;
	}

	/**
	 * Validate domain name format
	 * 
	 * Checks if a domain name follows valid format rules:
	 * - Minimum length of 4 characters
	 * - Contains at least one dot
	 * - Valid characters only (letters, numbers, dots, hyphens)
	 * - Proper TLD format
	 * 
	 * @param string|false $domain The domain to validate
	 * @return bool True if domain is valid, false otherwise
	 */
	private function isValidDomain(string|false $domain): bool
	{
		// Check if domain is false or empty
		if ($domain === false || empty($domain)) {
			return false;
		}

		// Check minimum length
		if (strlen($domain) < 4) {
			return false;
		}

		// Check maximum length (RFC compliant)
		if (strlen($domain) > 253) {
			return false;
		}

		// Must contain at least one dot
		if (strpos($domain, '.') === false) {
			return false;
		}

		// Check for valid domain format using regex
		if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*$/i', $domain)) {
			return false;
		}

		// Check that it doesn't start or end with a hyphen or dot
		if (str_starts_with($domain, '-') || str_ends_with($domain, '-') || 
			str_starts_with($domain, '.') || str_ends_with($domain, '.')) {
			return false;
		}

		// Check for consecutive dots
		if (strpos($domain, '..') !== false) {
			return false;
		}

		// Validate TLD (must be at least 2 characters)
		$parts = explode('.', $domain);
		$tld = end($parts);
		if (strlen($tld) < 2 || !ctype_alpha($tld)) {
			return false;
		}

		return true;
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
$plugins->registerPlugin('surbl');