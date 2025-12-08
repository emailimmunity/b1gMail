<?php
/**
 * Protocol ↔ SSL Manager Integration Bridge
 * Automatically provisions & deploys SSL certificates for protocol subdomains
 * 
 * @version 1.0
 * @date 2025-11-27
 */

class ProtocolSSLBridge
{
	private $db;
	private $sslManager = null;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Get SSL Manager instance (lazy loading)
	 * @return SSLManager|null
	 */
	private function _getSSLManager()
	{
		if($this->sslManager === null) {
			if(file_exists(B1GMAIL_DIR . 'serverlib/ssl-manager.inc.php')) {
				require_once(B1GMAIL_DIR . 'serverlib/ssl-manager.inc.php');
				if(class_exists('SSLManager')) {
					$this->sslManager = new SSLManager();
				}
			}
		}
		return $this->sslManager;
	}
	
	/**
	 * Hook: Protocol subdomain added or SSL enabled
	 * Auto-provision SSL certificate
	 * 
	 * @param int $protocolId Protocol ID from bm60_protocol_links
	 * @param string $domain Domain/subdomain (e.g., imap.domain.tld)
	 * @param string $protocolType Protocol type (dovecot, postfix, grommunio, etc.)
	 * @return array Result array with success status
	 */
	public function onProtocolAdded($protocolId, $domain, $protocolType)
	{
		$result = array('success' => false, 'message' => '', 'certificate_id' => null);
		
		// Validate inputs
		if(empty($domain) || empty($protocolType)) {
			$result['message'] = 'Invalid domain or protocol type';
			return $result;
		}
		
		// Check if SSL Manager plugin is available
		$sslManager = $this->_getSSLManager();
		if(!$sslManager) {
			$result['message'] = 'SSL Manager plugin not available';
			PutLog('Protocol SSL: SSL Manager not available for ' . $domain, PRIO_WARNING, __FILE__, __LINE__);
			return $result;
		}
		
		// Check if certificate already exists for this domain
		$existingCert = $this->_findExistingCertificate($domain);
		if($existingCert) {
			// Reuse existing certificate
			$this->_linkCertificateToProtocol($protocolId, $existingCert['id'], $protocolType);
			$result['success'] = true;
			$result['certificate_id'] = $existingCert['id'];
			$result['message'] = 'Linked to existing certificate #' . $existingCert['id'];
			
			PutLog('Protocol SSL: Linked protocol ' . $protocolType . ' (' . $domain . ') to existing cert #' . $existingCert['id'],
				PRIO_NOTE, __FILE__, __LINE__);
			
			return $result;
		}
		
		// Create new SSL certificate
		$certData = array(
			'name' => 'Protocol: ' . $domain . ' (' . $protocolType . ')',
			'type' => 'single',
			'domains' => json_encode(array($domain)),
			'primary_domain' => $domain,
			'status' => 'pending',
			'auto_renew' => 1,
			'protocol_type' => $protocolType,
			'protocol_id' => $protocolId
		);
		
		try {
			$certResult = $sslManager->createCertificateRecord($certData);
			
			if($certResult && isset($certResult['certificate_id'])) {
				// Link certificate to protocol
				$this->_linkCertificateToProtocol($protocolId, $certResult['certificate_id'], $protocolType);
				
				$result['success'] = true;
				$result['certificate_id'] = $certResult['certificate_id'];
				$result['message'] = 'SSL certificate provisioning started (ID: ' . $certResult['certificate_id'] . ')';
				
				// Log deployment
				$this->_logDeployment($certResult['certificate_id'], $protocolId, $protocolType, 'create', 'pending', 
					'Certificate provisioning initiated');
				
				PutLog('Protocol SSL: Certificate provisioning started for ' . $domain . ' (cert #' . $certResult['certificate_id'] . ')',
					PRIO_NOTE, __FILE__, __LINE__);
			} else {
				$result['message'] = 'Certificate creation failed';
				PutLog('Protocol SSL: Certificate creation failed for ' . $domain, PRIO_WARNING, __FILE__, __LINE__);
			}
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			PutLog('Protocol SSL: Exception during cert creation for ' . $domain . ' - ' . $e->getMessage(),
				PRIO_ERROR, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * Hook: Protocol SSL toggled on/off
	 * 
	 * @param int $protocolId Protocol ID
	 * @param bool $enabled SSL enabled status
	 * @return array Result array
	 */
	public function onProtocolSSLChanged($protocolId, $enabled)
	{
		$result = array('success' => false, 'message' => '');
		
		// Get protocol details
		$res = $this->db->Query('SELECT * FROM {pre}protocol_links WHERE id=?', $protocolId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			$result['message'] = 'Protocol not found';
			return $result;
		}
		
		$protocol = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if($enabled && empty($protocol['ssl_certificate_id'])) {
			// SSL enabled but no certificate - provision one
			$domain = $protocol['server_host'] ?: $protocol['protocol_type'] . '.example.com';
			return $this->onProtocolAdded($protocolId, $domain, $protocol['protocol_type']);
		}
		elseif(!$enabled && $protocol['ssl_certificate_id']) {
			// SSL disabled - update status
			$this->db->Query('UPDATE {pre}protocol_links SET ssl_status="none" WHERE id=?', $protocolId);
			$result['success'] = true;
			$result['message'] = 'SSL disabled for protocol';
		}
		
		return $result;
	}
	
	/**
	 * Hook: Protocol created with SSL enabled
	 * Wrapper for onProtocolAdded for backward compatibility
	 * 
	 * @param int $protocolId Protocol ID
	 * @return array Result array
	 */
	public function onProtocolCreated($protocolId)
	{
		// Get protocol details
		$res = $this->db->Query('SELECT * FROM {pre}protocol_links WHERE id=?', $protocolId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			return array('success' => false, 'error' => 'Protocol not found');
		}
		
		$protocol = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$domain = $protocol['server_host'] ?: $protocol['protocol_type'] . '.example.com';
		return $this->onProtocolAdded($protocolId, $domain, $protocol['protocol_type']);
	}
	
	/**
	 * Hook: Protocol updated (SSL type changed)
	 * 
	 * @param int $protocolId Protocol ID
	 * @param string $sslType SSL type (ssl, tls, starttls, none)
	 * @return array Result array
	 */
	public function onProtocolUpdated($protocolId, $sslType)
	{
		$result = array('success' => false, 'message' => '');
		
		// Get protocol details
		$res = $this->db->Query('SELECT * FROM {pre}protocol_links WHERE id=?', $protocolId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			$result['error'] = 'Protocol not found';
			return $result;
		}
		
		$protocol = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Check if SSL is enabled
		if($sslType !== 'none' && !empty($protocol['server_host'])) {
			// SSL enabled - provision or update certificate
			if(empty($protocol['ssl_certificate_id'])) {
				// No certificate yet - provision one
				return $this->onProtocolAdded($protocolId, $protocol['server_host'], $protocol['protocol_type']);
			} else {
				// Certificate exists - verify it's still valid
				$result['success'] = true;
				$result['message'] = 'SSL certificate already provisioned (ID: ' . $protocol['ssl_certificate_id'] . ')';
			}
		} else {
			// SSL disabled
			$result['success'] = true;
			$result['message'] = 'SSL disabled for this protocol';
		}
		
		return $result;
	}
	
	/**
	 * Daily cron job: Check all protocols and provision missing certificates
	 * 
	 * @return array Result with statistics
	 */
	public function checkAllProtocols()
	{
		$result = array(
			'checked' => 0,
			'provisioned' => 0,
			'updated' => 0,
			'errors' => 0
		);
		
		// Get all protocols with SSL enabled but no certificate
		$res = $this->db->Query("SELECT * FROM {pre}protocol_links 
			WHERE ssl_type != 'none' 
			AND ssl_type IS NOT NULL
			AND server_host IS NOT NULL
			AND server_host != ''");
		
		if($res) {
			while($protocol = $res->FetchArray(MYSQLI_ASSOC)) {
				$result['checked']++;
				
				// Check if certificate is missing
				if(empty($protocol['ssl_certificate_id'])) {
					// Provision certificate
					$provisionResult = $this->onProtocolAdded(
						$protocol['id'],
						$protocol['server_host'],
						$protocol['protocol_type']
					);
					
					if($provisionResult['success']) {
						$result['provisioned']++;
					} else {
						$result['errors']++;
					}
				} else {
					// Certificate exists - sync status
					$certRes = $this->db->Query('SELECT status, valid_until FROM {pre}ssl_certificates WHERE id=?',
						$protocol['ssl_certificate_id']);
					
					if($certRes && $certRes->RowCount() > 0) {
						$cert = $certRes->FetchArray(MYSQLI_ASSOC);
						$certRes->Free();
						
						// Update if status changed
						if($cert['status'] != $protocol['ssl_status']) {
							$this->db->Query('UPDATE {pre}protocol_links SET 
								ssl_status=?,
								ssl_valid_until=?,
								ssl_last_check=NOW()
								WHERE id=?',
								$cert['status'],
								$cert['valid_until'],
								$protocol['id']);
							$result['updated']++;
						}
					} else {
						if($certRes) $certRes->Free();
						$result['errors']++;
					}
				}
			}
			$res->Free();
		}
		
		return $result;
	}
	
	/**
	 * Hook: SSL certificate issued by Let's Encrypt
	 * Deploy to protocol service & update status
	 * 
	 * @param int $certificateId Certificate ID
	 * @return array Result array with deployed services and errors
	 */
	public function onCertificateIssued($certificateId)
	{
		$result = array('success' => false, 'deployed' => array(), 'errors' => array());
		
		// Get certificate details
		$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates WHERE id=?', $certificateId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			$result['errors'][] = 'Certificate not found';
			return $result;
		}
		
		$cert = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Find all protocols using this certificate
		$res = $this->db->Query('SELECT * FROM {pre}protocol_links WHERE ssl_certificate_id=?', $certificateId);
		$protocols = array();
		if($res) {
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$protocols[] = $row;
			}
			$res->Free();
		}
		
		if(empty($protocols)) {
			$result['errors'][] = 'No protocols linked to this certificate';
			return $result;
		}
		
		// Deploy to each protocol's service
		foreach($protocols as $protocol) {
			$deployResult = $this->_deployToService($protocol['protocol_type'], $cert, $protocol);
			
			if($deployResult['success']) {
				// Update protocol SSL status
				$this->db->Query('UPDATE {pre}protocol_links SET 
					ssl_status=?,
					ssl_valid_until=?,
					ssl_last_check=NOW()
					WHERE id=?',
					'active',
					$cert['valid_until'],
					$protocol['id']);
				
				// Update certificate deployment status
				$this->db->Query('UPDATE {pre}ssl_certificates SET 
					deployment_status=?,
					last_deployed=NOW()
					WHERE id=?',
					'deployed',
					$certificateId);
				
				// Log success
				$this->_logDeployment($certificateId, $protocol['id'], $protocol['protocol_type'], 
					'deploy', 'success', 'Deployed to ' . $protocol['protocol_type']);
				
				$result['deployed'][] = $protocol['protocol_type'];
			} else {
				// Log failure
				$this->_logDeployment($certificateId, $protocol['id'], $protocol['protocol_type'], 
					'deploy', 'failed', $deployResult['error']);
				
				$result['errors'][] = $protocol['protocol_type'] . ': ' . $deployResult['error'];
			}
		}
		
		$result['success'] = count($result['deployed']) > 0;
		
		if($result['success']) {
			PutLog('Protocol SSL: Certificate #' . $certificateId . ' deployed to ' . count($result['deployed']) . ' service(s)',
				PRIO_NOTE, __FILE__, __LINE__);
		}
		
		if(!empty($result['errors'])) {
			PutLog('Protocol SSL: Deployment errors for cert #' . $certificateId . ' - ' . implode(', ', $result['errors']),
				PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * Deploy certificate to service (Dovecot, Postfix, Grommunio, etc.)
	 * 
	 * @param string $protocolType Protocol type
	 * @param array $cert Certificate data
	 * @param array $protocol Protocol data
	 * @return array Result with success status and error message
	 */
	private function _deployToService($protocolType, $cert, $protocol)
	{
		$result = array('success' => false, 'error' => '');
		
		// Determine certificate file paths
		$certPath = '/etc/ssl/certs/' . $cert['primary_domain'] . '.crt';
		$keyPath = '/etc/ssl/private/' . $cert['primary_domain'] . '.key';
		
		// Check if certificate files exist
		if(!file_exists($certPath) || !file_exists($keyPath)) {
			$result['error'] = 'Certificate files not found on filesystem';
			return $result;
		}
		
		switch(strtolower($protocolType)) {
			case 'dovecot':
			case 'imap':
			case 'pop3':
			case 'sieve':
				$result = $this->_deployToDovecot($certPath, $keyPath, $protocol);
				break;
				
			case 'postfix':
			case 'smtp':
				$result = $this->_deployToPostfix($certPath, $keyPath, $protocol);
				break;
				
			case 'grommunio':
			case 'mapi':
			case 'ews':
			case 'eas':
			case 'autodiscover':
				$result = $this->_deployToGrommunio($certPath, $keyPath, $cert['primary_domain'], $protocol);
				break;
				
			case 'apache':
			case 'nginx':
			case 'http':
			case 'https':
				$result = $this->_deployToWebServer($certPath, $keyPath, $cert['primary_domain'], $protocol);
				break;
				
			default:
				$result['error'] = 'Unknown protocol type: ' . $protocolType;
				PutLog('Protocol SSL: Unknown protocol type "' . $protocolType . '" for deployment',
					PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * Deploy certificate to Dovecot (IMAP/POP3/Sieve)
	 */
	private function _deployToDovecot($certPath, $keyPath, $protocol)
	{
		$result = array('success' => false, 'error' => '');
		
		try {
			$dovecotSSLDir = '/etc/dovecot/ssl';
			
			// Create SSL directory if not exists
			if(!is_dir($dovecotSSLDir)) {
				mkdir($dovecotSSLDir, 0755, true);
			}
			
			// Copy certificate files
			if(!copy($certPath, $dovecotSSLDir . '/dovecot.crt')) {
				$result['error'] = 'Failed to copy certificate file';
				return $result;
			}
			
			if(!copy($keyPath, $dovecotSSLDir . '/dovecot.key')) {
				$result['error'] = 'Failed to copy key file';
				return $result;
			}
			
			// Set correct permissions
			chmod($dovecotSSLDir . '/dovecot.key', 0600);
			
			// Reload Dovecot
			exec('doveadm reload 2>&1', $output, $returnCode);
			
			if($returnCode === 0) {
				$result['success'] = true;
				PutLog('Protocol SSL: Dovecot certificate deployed and reloaded', PRIO_NOTE, __FILE__, __LINE__);
			} else {
				$result['error'] = 'Dovecot reload failed: ' . implode("\n", $output);
				PutLog('Protocol SSL: Dovecot reload failed - ' . implode(', ', $output), PRIO_WARNING, __FILE__, __LINE__);
			}
		} catch(Exception $e) {
			$result['error'] = 'Exception: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Deploy certificate to Postfix (SMTP)
	 */
	private function _deployToPostfix($certPath, $keyPath, $protocol)
	{
		$result = array('success' => false, 'error' => '');
		
		try {
			$postfixSSLDir = '/etc/postfix/ssl';
			
			// Create SSL directory if not exists
			if(!is_dir($postfixSSLDir)) {
				mkdir($postfixSSLDir, 0755, true);
			}
			
			// Copy certificate files
			if(!copy($certPath, $postfixSSLDir . '/postfix.crt')) {
				$result['error'] = 'Failed to copy certificate file';
				return $result;
			}
			
			if(!copy($keyPath, $postfixSSLDir . '/postfix.key')) {
				$result['error'] = 'Failed to copy key file';
				return $result;
			}
			
			// Set correct permissions
			chmod($postfixSSLDir . '/postfix.key', 0600);
			
			// Reload Postfix
			exec('postfix reload 2>&1', $output, $returnCode);
			
			if($returnCode === 0) {
				$result['success'] = true;
				PutLog('Protocol SSL: Postfix certificate deployed and reloaded', PRIO_NOTE, __FILE__, __LINE__);
			} else {
				$result['error'] = 'Postfix reload failed: ' . implode("\n", $output);
				PutLog('Protocol SSL: Postfix reload failed - ' . implode(', ', $output), PRIO_WARNING, __FILE__, __LINE__);
			}
		} catch(Exception $e) {
			$result['error'] = 'Exception: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Deploy certificate to Grommunio (MAPI/EWS/EAS via Nginx)
	 */
	private function _deployToGrommunio($certPath, $keyPath, $domain, $protocol)
	{
		$result = array('success' => false, 'error' => '');
		
		try {
			$nginxSitesDir = '/etc/nginx/sites-available';
			$nginxEnabledDir = '/etc/nginx/sites-enabled';
			$configFile = $nginxSitesDir . '/grommunio-' . str_replace('.', '-', $domain) . '.conf';
			
			// Create nginx config directories if not exists
			if(!is_dir($nginxSitesDir)) {
				mkdir($nginxSitesDir, 0755, true);
			}
			if(!is_dir($nginxEnabledDir)) {
				mkdir($nginxEnabledDir, 0755, true);
			}
			
			// Create Nginx configuration
			$nginxConf = "# Grommunio SSL Configuration for " . $domain . "\n";
			$nginxConf .= "# Auto-generated by Protocol SSL Bridge\n\n";
			$nginxConf .= "server {\n";
			$nginxConf .= "    listen 443 ssl http2;\n";
			$nginxConf .= "    server_name " . $domain . ";\n\n";
			$nginxConf .= "    ssl_certificate " . $certPath . ";\n";
			$nginxConf .= "    ssl_certificate_key " . $keyPath . ";\n";
			$nginxConf .= "    ssl_protocols TLSv1.2 TLSv1.3;\n";
			$nginxConf .= "    ssl_prefer_server_ciphers on;\n\n";
			$nginxConf .= "    # Proxy to Grommunio\n";
			$nginxConf .= "    location / {\n";
			$nginxConf .= "        proxy_pass http://localhost:5000/;\n";
			$nginxConf .= "        proxy_set_header Host \$host;\n";
			$nginxConf .= "        proxy_set_header X-Real-IP \$remote_addr;\n";
			$nginxConf .= "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n";
			$nginxConf .= "        proxy_set_header X-Forwarded-Proto \$scheme;\n";
			$nginxConf .= "    }\n";
			$nginxConf .= "}\n";
			
			file_put_contents($configFile, $nginxConf);
			
			// Enable site (create symlink)
			$enabledLink = $nginxEnabledDir . '/grommunio-' . str_replace('.', '-', $domain) . '.conf';
			if(!file_exists($enabledLink)) {
				symlink($configFile, $enabledLink);
			}
			
			// Test Nginx configuration
			exec('nginx -t 2>&1', $output, $returnCode);
			if($returnCode === 0) {
				// Reload Nginx
				exec('nginx -s reload 2>&1', $reloadOutput, $reloadCode);
				if($reloadCode === 0) {
					$result['success'] = true;
					PutLog('Protocol SSL: Grommunio certificate deployed to Nginx for ' . $domain, PRIO_NOTE, __FILE__, __LINE__);
				} else {
					$result['error'] = 'Nginx reload failed';
				}
			} else {
				$result['error'] = 'Nginx config test failed: ' . implode(', ', $output);
			}
		} catch(Exception $e) {
			$result['error'] = 'Exception: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Deploy certificate to Apache/Nginx web server
	 */
	private function _deployToWebServer($certPath, $keyPath, $domain, $protocol)
	{
		$result = array('success' => false, 'error' => '');
		
		// Check if Apache or Nginx is running
		exec('which apache2ctl 2>&1', $apacheCheck, $apacheCode);
		exec('which nginx 2>&1', $nginxCheck, $nginxCode);
		
		if($apacheCode === 0) {
			// Apache deployment
			try {
				$apacheSSLDir = '/etc/apache2/ssl';
				if(!is_dir($apacheSSLDir)) {
					mkdir($apacheSSLDir, 0755, true);
				}
				
				copy($certPath, $apacheSSLDir . '/' . $domain . '.crt');
				copy($keyPath, $apacheSSLDir . '/' . $domain . '.key');
				chmod($apacheSSLDir . '/' . $domain . '.key', 0600);
				
				exec('apache2ctl graceful 2>&1', $output, $returnCode);
				if($returnCode === 0) {
					$result['success'] = true;
					PutLog('Protocol SSL: Apache certificate deployed for ' . $domain, PRIO_NOTE, __FILE__, __LINE__);
				}
			} catch(Exception $e) {
				$result['error'] = 'Apache deployment failed: ' . $e->getMessage();
			}
		}
		elseif($nginxCode === 0) {
			// Nginx deployment (similar to Grommunio but for generic web)
			$result = $this->_deployToGrommunio($certPath, $keyPath, $domain, $protocol);
		}
		else {
			$result['error'] = 'No web server (Apache/Nginx) found';
		}
		
		return $result;
	}
	
	/**
	 * Find existing certificate for domain
	 */
	private function _findExistingCertificate($domain)
	{
		$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates 
			WHERE primary_domain=? AND status IN ("active", "pending") 
			ORDER BY id DESC LIMIT 1', $domain);
		
		if($res && $res->RowCount() > 0) {
			$cert = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			return $cert;
		}
		
		if($res) $res->Free();
		return null;
	}
	
	/**
	 * Link certificate to protocol
	 */
	private function _linkCertificateToProtocol($protocolId, $certificateId, $protocolType = '')
	{
		$this->db->Query('UPDATE {pre}protocol_links SET 
			ssl_certificate_id=?, 
			ssl_status=?,
			ssl_last_check=NOW() 
			WHERE id=?',
			$certificateId,
			'pending',
			$protocolId);
		
		// Also update certificate with protocol link
		if($protocolType) {
			$this->db->Query('UPDATE {pre}ssl_certificates SET 
				protocol_id=?,
				protocol_type=?
				WHERE id=?',
				$protocolId,
				$protocolType,
				$certificateId);
		}
	}
	
	/**
	 * Log deployment action
	 */
	private function _logDeployment($certificateId, $protocolId, $protocolType, $action, $status, $message)
	{
		$this->db->Query('INSERT INTO {pre}ssl_deployment_log 
			(certificate_id, protocol_id, protocol_type, action, status, message, created_at)
			VALUES (?, ?, ?, ?, ?, ?, NOW())',
			$certificateId,
			$protocolId,
			$protocolType,
			$action,
			$status,
			$message);
	}
	
	/**
	 * Daily cron: Sync SSL status for all protocols
	 * 
	 * @return array Result with checked and updated counts
	 */
	public function syncAllProtocols()
	{
		$result = array('checked' => 0, 'updated' => 0, 'errors' => 0);
		
		// Get all protocols with SSL certificates
		$res = $this->db->Query('SELECT * FROM {pre}protocol_links WHERE ssl_certificate_id IS NOT NULL');
		
		if($res) {
			while($protocol = $res->FetchArray(MYSQLI_ASSOC)) {
				$result['checked']++;
				
				// Get current certificate status
				$certRes = $this->db->Query('SELECT status, valid_until FROM {pre}ssl_certificates WHERE id=?',
					$protocol['ssl_certificate_id']);
				
				if($certRes && $certRes->RowCount() > 0) {
					$cert = $certRes->FetchArray(MYSQLI_ASSOC);
					$certRes->Free();
					
					// Check if status changed
					if($cert['status'] != $protocol['ssl_status'] || $cert['valid_until'] != $protocol['ssl_valid_until']) {
						// Update protocol SSL status
						$this->db->Query('UPDATE {pre}protocol_links SET 
							ssl_status=?,
							ssl_valid_until=?,
							ssl_last_check=NOW()
							WHERE id=?',
							$cert['status'],
							$cert['valid_until'],
							$protocol['id']);
						
						$result['updated']++;
					}
				} else {
					if($certRes) $certRes->Free();
					$result['errors']++;
				}
			}
			$res->Free();
		}
		
		return $result;
	}
}

/**
 * Global helper function - Singleton pattern
 * 
 * @return ProtocolSSLBridge
 */
function getProtocolSSLBridge()
{
	static $bridge = null;
	if($bridge === null) {
		$bridge = new ProtocolSSLBridge();
	}
	return $bridge;
}
