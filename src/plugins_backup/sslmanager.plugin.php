<?php
/**
 * SSL Certificate Manager Plugin
 * Zentrale SSL-Verwaltung mit Let's Encrypt Integration
 * (c) 2025 b1gMail Development
 */

class SSLManager_Plugin extends BMPlugin
{
	// Plugin metadata
	var $type = BMPLUGIN_DEFAULT;
	var $name = 'SSL Manager';
	var $version = '1.0.0';
	var $author = 'b1gMail Development';
	var $description = 'Zentrale Verwaltung von SSL-Zertifikaten mit Let\'s Encrypt Integration';
	var $id = 'sslmanager';
	var $internal_name = 'SSLManager_Plugin';
	var $order = 100;
	
	// Admin page configuration
	var $admin_pages = true;
	var $admin_page_title = 'SSL-Zertifikate';
	var $admin_page_icon = 'bms_logo.png';
	
	private $sslManager = null;
	private $loadError = null;
	
	/**
	 * OnLoad - Called when plugin is loaded
	 */
	function OnLoad()
	{
		// Plugin loaded - do nothing here to avoid early errors
	}
	
	/**
	 * Get SSL Manager instance (lazy loading with error handling)
	 */
	private function _getSSLManager()
	{
		if($this->sslManager === null)
		{
			try {
				// Check B1GMAIL_DIR
				if(!defined('B1GMAIL_DIR')) {
					throw new Exception('B1GMAIL_DIR not defined');
				}
				
				// Check file exists
				$sslManagerFile = B1GMAIL_DIR . 'serverlib/ssl-manager.inc.php';
				if(!file_exists($sslManagerFile)) {
					throw new Exception('ssl-manager.inc.php not found');
				}
				
				// Load SSL Manager
				require_once($sslManagerFile);
				
				if(!class_exists('SSLManager')) {
					throw new Exception('SSLManager class not found');
				}
				
				// Check if $db is available
				global $db;
				if(!isset($db) || !is_object($db)) {
					throw new Exception('Database connection not available');
				}
				
				// Create instance
				$this->sslManager = new SSLManager();
			} catch(Exception $e) {
				error_log('SSLManager instantiation error: ' . $e->getMessage());
				return null;
			}
		}
		return $this->sslManager;
	}
	
	/**
	 * AdminHandler - Admin interface (called by b1gMail admin system)
	 */
	function AdminHandler()
	{
		global $tpl;
		
		try {
			// Check for load errors
			if($this->loadError) {
				throw new Exception('Plugin load error: ' . $this->loadError);
			}
			
			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'overview';
			
			switch($action)
			{
				case 'overview':
					$this->_adminOverview($tpl);
					break;
					
				case 'create':
					$this->_adminCreate($tpl);
					break;
					
				case 'discovery':
					$this->_adminDiscovery($tpl);
					break;
					
				case 'settings':
					$this->_adminSettings($tpl);
					break;
					
				case 'delete':
					$this->_adminDelete();
					break;
					
				case 'renew':
					$this->_adminRenew();
					break;
					
				default:
					$this->_adminOverview($tpl);
			}
		} catch(Exception $e) {
			$tpl->assign('error', $e->getMessage());
			$tpl->assign('page', $this->_templatePath('sslm.admin.error.tpl'));
		}
	}
	
	/**
	 * Overview Page
	 */
	function _adminOverview(&$tpl)
	{
		try {
			$manager = $this->_getSSLManager();
			
			if(!$manager) {
				throw new Exception('SSL Manager konnte nicht geladen werden. Fehlerdetails: ' . 
					($this->loadError ? $this->loadError : 'Unbekannter Fehler'));
			}
			
			$certificates = $manager->getAllCertificates();
		
		// Statistics
		$stats = array(
			'total' => 0,
			'active' => 0,
			'expiring_soon' => 0,
			'expired' => 0
		);
		
		foreach($certificates as $cert)
		{
			$stats['total']++;
			
			if($cert['status'] === 'active')
			{
				$stats['active']++;
				
				if(isset($cert['days_until_expiry']) && $cert['days_until_expiry'] <= 30)
				{
					$stats['expiring_soon']++;
				}
			}
			elseif($cert['status'] === 'expired')
			{
				$stats['expired']++;
			}
		}
		
			$tpl->assign('certificates', $certificates);
			$tpl->assign('stats', $stats);
			$tpl->assign('pageURL', $this->_adminLink());
			$tpl->assign('page', $this->_templatePath('sslm.admin.overview.tpl'));
		} catch(Exception $e) {
			throw new Exception('Overview Fehler: ' . $e->getMessage() . ' [File: ' . $e->getFile() . ', Line: ' . $e->getLine() . ']');
		}
	}
	
	/**
	 * Create Certificate Page
	 */
	function _adminCreate(&$tpl)
	{
		$manager = $this->_getSSLManager();
		
		if(!$manager) {
			throw new Exception('SSL Manager konnte nicht geladen werden.');
		}
		
		// Handle form submission
		if(isset($_POST['create']) && function_exists('IsPOSTRequest') && IsPOSTRequest())
		{
			$type = $_POST['cert_type'] ?? 'single';
			$domains = array();
			
			// Check if this is an external certificate upload
			if($type === 'upload')
			{
				$result = $this->_handleExternalCertUpload($_POST);
				$tpl->assign('createResult', $result);
				if($result['success']) {
					// Redirect to overview after successful upload
					header('Location: ' . $this->_adminLink());
					die();
				}
			}
			elseif($type === 'wildcard')
			{
				$baseDomain = $_POST['base_domain'] ?? '';
				$domains[] = '*.' . $baseDomain;
			}
			elseif($type === 'san')
			{
				$selectedDomains = $_POST['selected_domains'] ?? array();
				$domains = $selectedDomains;
			}
			else
			{
				$domains[] = $_POST['single_domain'] ?? '';
			}
			
			// Create certificate
			$result = $manager->createCertificate(array(
				'domains' => $domains,
				'type' => $type,
				'issuer' => 'letsencrypt',
				'auto_renew' => isset($_POST['auto_renew']) ? 1 : 0
			));
			
			$tpl->assign('createResult', $result);
		}
		
		// Get discovered domains
		$discoveredDomains = $manager->discoverDomainsFromProtocols();
		
		$tpl->assign('discovered_domains', $discoveredDomains);
		$tpl->assign('pageURL', $this->_adminLink() . '&action=create');
		$tpl->assign('page', $this->_templatePath('sslm.admin.create.tpl'));
	}
	
	/**
	 * Discovery Page
	 */
	function _adminDiscovery(&$tpl)
	{
		$manager = $this->_getSSLManager();
		
		if(!$manager) {
			throw new Exception('SSL Manager konnte nicht geladen werden.');
		}
		
		$discoveredDomains = $manager->discoverDomainsFromProtocols();
		
		// Analyze each domain
		foreach($discoveredDomains as $domain => &$data)
		{
			$suggestion = $manager->suggestWildcard($domain);
			$data['wildcard_suggestion'] = $suggestion;
		}
		
		$tpl->assign('discovered_domains', $discoveredDomains);
		$tpl->assign('pageURL', $this->_adminLink() . '&action=discovery');
		$tpl->assign('page', $this->_templatePath('sslm.admin.discovery.tpl'));
	}
	
	/**
	 * Settings Page
	 */
	function _adminSettings(&$tpl)
	{
		global $db;
		
		try {
			// Save settings
			if(isset($_POST['save']) && function_exists('IsPOSTRequest') && IsPOSTRequest())
			{
				// Check if columns exist
				$res = $db->Query('SHOW COLUMNS FROM {pre}config LIKE "ssl_acme_email"');
				$columnExists = ($res->FetchArray() !== false);
				$res->Free();
			
			if(!$columnExists) {
				// Add columns
				$db->Query('ALTER TABLE {pre}config 
					ADD COLUMN ssl_acme_email VARCHAR(255) DEFAULT "",
					ADD COLUMN ssl_acme_production TINYINT(1) DEFAULT 0,
					ADD COLUMN ssl_auto_renew_days INT DEFAULT 30
				');
			}
			
			$db->Query('UPDATE {pre}config SET
				ssl_acme_email=?,
				ssl_acme_production=?,
				ssl_auto_renew_days=?',
				$_POST['acme_email'] ?? '',
				isset($_POST['acme_production']) ? 1 : 0,
				(int)($_POST['auto_renew_days'] ?? 30)
			);
			
			$tpl->assign('saveResult', array('success' => true, 'message' => 'Einstellungen gespeichert'));
		}
		
		// Load settings
		$res = $db->Query('SELECT * FROM {pre}config LIMIT 1');
		$config = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Set defaults if not exists
		if(!isset($config['ssl_acme_email'])) $config['ssl_acme_email'] = '';
		if(!isset($config['ssl_acme_production'])) $config['ssl_acme_production'] = 0;
		if(!isset($config['ssl_auto_renew_days'])) $config['ssl_auto_renew_days'] = 30;
		
			$tpl->assign('config', $config);
			$tpl->assign('pageURL', $this->_adminLink() . '&action=settings');
			$tpl->assign('page', $this->_templatePath('sslm.admin.settings.tpl'));
		} catch(Exception $e) {
			// Fallback with default config if DB access fails
			$tpl->assign('config', array(
				'ssl_acme_email' => '',
				'ssl_acme_production' => 0,
				'ssl_auto_renew_days' => 30
			));
			$tpl->assign('saveResult', array(
				'success' => false, 
				'message' => 'Warnung: Einstellungen konnten nicht aus Datenbank geladen werden. Details: ' . $e->getMessage()
			));
			$tpl->assign('pageURL', $this->_adminLink() . '&action=settings');
			$tpl->assign('page', $this->_templatePath('sslm.admin.settings.tpl'));
		}
	}
	
	/**
	 * Delete Certificate
	 */
	function _adminDelete()
	{
		$manager = $this->_getSSLManager();
		
		if($manager && isset($_GET['id']))
		{
			$manager->deleteCertificate($_GET['id']);
		}
		
		header('Location: ' . $this->_adminLink());
		die();
	}
	
	/**
	 * Renew Certificate
	 */
	function _adminRenew()
	{
		$manager = $this->_getSSLManager();
		
		if($manager && isset($_GET['id']))
		{
			require_once(B1GMAIL_DIR . 'serverlib/acme-client.inc.php');
			$cert = $manager->getCertificate($_GET['id']);
			
			if($cert && isset($cert['acme_account_id']) && $cert['acme_account_id'])
			{
				$acme = new AcmeClient(true, $cert['acme_account_id']);
				// TODO: Implement renewal
			}
		}
		
		header('Location: ' . $this->_adminLink());
		die();
	}
	
	/**
	 * Handle External Certificate Upload
	 */
	private function _handleExternalCertUpload($postData)
	{
		global $db;
		
		try {
			// Validate input
			if(empty($postData['cert_file'])) {
				throw new Exception('Certificate file is required');
			}
			if(empty($postData['private_key'])) {
				throw new Exception('Private key is required');
			}
			
			$certData = trim($postData['cert_file']);
			$privateKey = trim($postData['private_key']);
			$chainCert = trim($postData['chain_cert'] ?? '');
			
			// Parse certificate
			$certInfo = openssl_x509_parse($certData);
			if($certInfo === false) {
				throw new Exception('Invalid certificate format. Please check your certificate file.');
			}
			
			// Extract domains
			$domains = array();
			
			// Primary domain (CN)
			if(isset($certInfo['subject']['CN'])) {
				$domains[] = $certInfo['subject']['CN'];
			}
			
			// SAN domains
			if(isset($certInfo['extensions']['subjectAltName'])) {
				$sanList = explode(',', $certInfo['extensions']['subjectAltName']);
				foreach($sanList as $san) {
					$san = trim(str_replace('DNS:', '', $san));
					if(!in_array($san, $domains)) {
						$domains[] = $san;
					}
				}
			}
			
			if(empty($domains)) {
				throw new Exception('No domains found in certificate');
			}
			
			// Determine certificate type
			$primaryDomain = $domains[0];
			if(strpos($primaryDomain, '*.') === 0) {
				$certType = 'wildcard';
			} elseif(count($domains) > 1) {
				$certType = 'san';
			} else {
				$certType = 'single';
			}
			
			// Extract validity dates
			$validFrom = date('Y-m-d H:i:s', $certInfo['validFrom_time_t']);
			$validUntil = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);
			
			// Check if certificate is still valid
			if($certInfo['validTo_time_t'] < time()) {
				throw new Exception('Certificate has expired on ' . date('Y-m-d', $certInfo['validTo_time_t']));
			}
			
			// Extract issuer
			$issuer = $certInfo['issuer']['O'] ?? 'Unknown CA';
			
			// Validate private key
			$keyResource = openssl_pkey_get_private($privateKey);
			if($keyResource === false) {
				throw new Exception('Invalid private key. Make sure it is unencrypted (no passphrase).');
			}
			
			// Verify that private key matches certificate
			$pubKeyFromCert = openssl_pkey_get_public($certData);
			$certDetails = openssl_pkey_get_details($pubKeyFromCert);
			$keyDetails = openssl_pkey_get_details($keyResource);
			
			if($certDetails['key'] !== $keyDetails['key']) {
				throw new Exception('Private key does not match certificate');
			}
			
			// Generate certificate name
			$certName = 'External: ' . $primaryDomain;
			
			// Save to database
			$db->Query('INSERT INTO {pre}ssl_certificates (
				name, type, status, domains, primary_domain,
				certificate, private_key, chain_certificate,
				issuer, valid_from, valid_until,
				auto_renew, deployed_to
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)',
				$certName,
				$certType,
				'active',
				json_encode($domains),
				$primaryDomain,
				$certData,
				$privateKey,
				$chainCert,
				$issuer,
				$validFrom,
				$validUntil,
				json_encode(array())
			);
			
			$certId = $db->InsertId();
			
			// Deploy if requested
			$deployed = array();
			if(!empty($postData['deploy_apache'])) {
				// TODO: Deploy to Apache
				$deployed[] = 'apache';
			}
			if(!empty($postData['deploy_dovecot'])) {
				// TODO: Deploy to Dovecot
				$deployed[] = 'dovecot';
			}
			if(!empty($postData['deploy_postfix'])) {
				// TODO: Deploy to Postfix
				$deployed[] = 'postfix';
			}
			if(!empty($postData['deploy_sftpgo'])) {
				// TODO: Deploy to SFTPGo
				$deployed[] = 'sftpgo';
			}
			
			if(!empty($deployed)) {
				$db->Query('UPDATE {pre}ssl_certificates SET deployed_to=? WHERE id=?',
					json_encode($deployed),
					$certId
				);
			}
			
			return array(
				'success' => true,
				'message' => 'External certificate uploaded successfully! <br>' .
					'<strong>Domains:</strong> ' . implode(', ', $domains) . '<br>' .
					'<strong>Type:</strong> ' . strtoupper($certType) . '<br>' .
					'<strong>Issuer:</strong> ' . $issuer . '<br>' .
					'<strong>Valid until:</strong> ' . date('Y-m-d', $certInfo['validTo_time_t'])
			);
			
		} catch(Exception $e) {
			return array(
				'success' => false,
				'message' => 'Upload failed: ' . $e->getMessage()
			);
		}
	}
	
	/**
	 * Override _adminLink to ALWAYS include session ID
	 */
	public function _adminLink($append = '')
	{
		global $sid;
		
		// Get session ID from multiple sources
		if(!empty($sid)) {
			$sessionId = $sid;
		} else if(isset($_REQUEST['sid'])) {
			$sessionId = $_REQUEST['sid'];
		} else if(session_id()) {
			$sessionId = session_id();
		} else {
			$sessionId = '';
		}
		
		return 'plugin.page.php?plugin=' . $this->internal_name . '&sid=' . $sessionId . $append;
	}
}

// Register plugin
$plugins->registerPlugin('SSLManager_Plugin');
