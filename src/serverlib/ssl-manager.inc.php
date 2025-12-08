<?php
/**
 * SSL Certificate Manager
 * Zentrale Verwaltung für SSL-Zertifikate
 * (c) 2025 b1gMail Development
 */

class SSLManager
{
	private $db;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
		$this->_ensureTablesExist();
	}
	
	/**
	 * Erstelle Datenbank-Tabellen falls nicht vorhanden
	 */
	private function _ensureTablesExist()
	{
		// Certificates table
		$this->db->Query("CREATE TABLE IF NOT EXISTS {pre}ssl_certificates (
			id INT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			type ENUM('single', 'wildcard', 'san') DEFAULT 'single',
			status ENUM('active', 'expired', 'pending', 'failed') DEFAULT 'pending',
			domains TEXT,
			primary_domain VARCHAR(255),
			certificate TEXT,
			private_key TEXT,
			chain_certificate TEXT,
			issuer VARCHAR(255),
			valid_from DATETIME,
			valid_until DATETIME,
			acme_account_id INT,
			acme_order_url TEXT,
			deployed_to TEXT,
			auto_renew TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_status (status),
			INDEX idx_valid_until (valid_until),
			INDEX idx_primary_domain (primary_domain)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		
		// ACME accounts table
		$this->db->Query("CREATE TABLE IF NOT EXISTS {pre}ssl_acme_accounts (
			id INT AUTO_INCREMENT PRIMARY KEY,
			email VARCHAR(255) NOT NULL,
			directory_url VARCHAR(500),
			account_key TEXT,
			account_url TEXT,
			status ENUM('active', 'deactivated') DEFAULT 'active',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_email (email)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		
		// Validations table (ohne FOREIGN KEY wegen {pre} Problem)
		$this->db->Query("CREATE TABLE IF NOT EXISTS {pre}ssl_validations (
			id INT AUTO_INCREMENT PRIMARY KEY,
			certificate_id INT,
			domain VARCHAR(255),
			method ENUM('http-01', 'dns-01') DEFAULT 'http-01',
			token VARCHAR(255),
			validation_content TEXT,
			status ENUM('pending', 'valid', 'invalid') DEFAULT 'pending',
			validated_at DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_certificate (certificate_id),
			INDEX idx_domain (domain)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		
		// Deployments table (ohne FOREIGN KEY wegen {pre} Problem)
		$this->db->Query("CREATE TABLE IF NOT EXISTS {pre}ssl_deployments (
			id INT AUTO_INCREMENT PRIMARY KEY,
			certificate_id INT,
			service VARCHAR(50),
			status ENUM('success', 'failed') DEFAULT 'success',
			error_message TEXT,
			deployed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_certificate (certificate_id),
			INDEX idx_service (service)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	}
	
	/**
	 * Auto-Discovery: Erkenne alle konfigurierten Domains aus Protokollen
	 */
	public function discoverDomainsFromProtocols()
	{
		$domains = array();
		
		// Load domain manager
		require_once(B1GMAIL_DIR . 'serverlib/domain-manager.inc.php');
		$domainManager = new DomainManager($this->db);
		$allDomains = $domainManager->getAllDomains();
		
		// Get protocol preferences
		$res = $this->db->Query('SELECT * FROM {pre}bms_prefs LIMIT 1');
		if($prefs = $res->FetchArray(MYSQLI_ASSOC))
		{
			foreach($allDomains as $domain)
			{
				$domainData = array(
					'domain' => $domain,
					'subdomains' => array()
				);
				
				// Dovecot subdomains
				if(!empty($prefs['dovecot_enabled']))
				{
					$imapSubdomain = $prefs['dovecot_imap_subdomain'] ?? 'imap';
					$pop3Subdomain = $prefs['dovecot_pop3_subdomain'] ?? 'pop3';
					$sieveSubdomain = $prefs['dovecot_sieve_subdomain'] ?? 'sieve';
					
					$domainData['subdomains'][] = array(
						'fqdn' => $imapSubdomain . '.' . $domain,
						'protocol' => 'Dovecot IMAP',
						'service' => 'dovecot'
					);
					$domainData['subdomains'][] = array(
						'fqdn' => $pop3Subdomain . '.' . $domain,
						'protocol' => 'Dovecot POP3',
						'service' => 'dovecot'
					);
					$domainData['subdomains'][] = array(
						'fqdn' => $sieveSubdomain . '.' . $domain,
						'protocol' => 'Dovecot Sieve',
						'service' => 'dovecot'
					);
				}
				
				// Postfix subdomains
				if(!empty($prefs['postfix_enabled']))
				{
					$smtpSubdomain = $prefs['postfix_subdomain'] ?? 'smtp';
					$domainData['subdomains'][] = array(
						'fqdn' => $smtpSubdomain . '.' . $domain,
						'protocol' => 'Postfix SMTP',
						'service' => 'postfix'
					);
				}
				
				// Grommunio subdomains
				if(!empty($prefs['grommunio_enabled']))
				{
					$mailSubdomain = $prefs['grommunio_subdomain'] ?? 'mail';
					$domainData['subdomains'][] = array(
						'fqdn' => $mailSubdomain . '.' . $domain,
						'protocol' => 'Grommunio MAPI/EWS/EAS',
						'service' => 'grommunio'
					);
					$domainData['subdomains'][] = array(
						'fqdn' => 'autodiscover.' . $domain,
						'protocol' => 'Grommunio AutoDiscover',
						'service' => 'grommunio'
					);
				}
				
				// SFTPGo subdomains
				if(!empty($prefs['sftpgo_enabled']))
				{
					$filesSubdomain = $prefs['sftpgo_subdomain'] ?? 'files';
					$domainData['subdomains'][] = array(
						'fqdn' => $filesSubdomain . '.' . $domain,
						'protocol' => 'SFTPGo Files',
						'service' => 'sftpgo'
					);
				}
				
				$domains[$domain] = $domainData;
			}
			$res->Free();
		}
		
		return $domains;
	}
	
	/**
	 * Hole alle Zertifikate
	 */
	public function getAllCertificates()
	{
		$certificates = array();
		
		$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates ORDER BY created_at DESC');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$row['domains'] = json_decode($row['domains'], true);
			$row['deployed_to'] = json_decode($row['deployed_to'], true);
			
			// Calculate days until expiry
			if($row['valid_until'])
			{
				$validUntil = strtotime($row['valid_until']);
				$now = time();
				$row['days_until_expiry'] = floor(($validUntil - $now) / 86400);
			}
			else
			{
				$row['days_until_expiry'] = null;
			}
			
			$certificates[] = $row;
		}
		$res->Free();
		
		return $certificates;
	}
	
	/**
	 * Hole ein spezifisches Zertifikat
	 */
	public function getCertificate($id)
	{
		$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates WHERE id=?', (int)$id);
		if($cert = $res->FetchArray(MYSQLI_ASSOC))
		{
			$cert['domains'] = json_decode($cert['domains'], true);
			$cert['deployed_to'] = json_decode($cert['deployed_to'], true);
			$res->Free();
			return $cert;
		}
		$res->Free();
		return false;
	}
	
	/**
	 * Erstelle neues Zertifikat
	 */
	public function createCertificate($data)
	{
		$domains = $data['domains'] ?? array();
		$type = $data['type'] ?? 'single';
		$issuer = $data['issuer'] ?? 'letsencrypt';
		$autoRenew = $data['auto_renew'] ?? 1;
		
		// Validate
		if(empty($domains))
		{
			return array('success' => false, 'message' => 'Keine Domains angegeben');
		}
		
		$primaryDomain = is_array($domains) ? $domains[0] : $domains;
		$name = $type === 'wildcard' ? '*.' . $primaryDomain : $primaryDomain;
		
		$this->db->Query('INSERT INTO {pre}ssl_certificates SET
			name=?,
			type=?,
			status=?,
			domains=?,
			primary_domain=?,
			issuer=?,
			auto_renew=?',
			$name,
			$type,
			'pending',
			json_encode($domains),
			$primaryDomain,
			$issuer,
			$autoRenew
		);
		
		$certId = $this->db->InsertId();
		
		return array(
			'success' => true,
			'certificate_id' => $certId,
			'message' => 'Zertifikat erstellt'
		);
	}
	
	/**
	 * Alias for createCertificate (for bridge compatibility)
	 */
	public function createCertificateRecord($data)
	{
		return $this->createCertificate($data);
	}
	
	/**
	 * Lösche Zertifikat
	 */
	public function deleteCertificate($id)
	{
		$this->db->Query('DELETE FROM {pre}ssl_certificates WHERE id=?', (int)$id);
		return array('success' => true, 'message' => 'Zertifikat gelöscht');
	}
	
	/**
	 * Prüfe welche Zertifikate bald ablaufen
	 */
	public function getExpiringCertificates($days = 30)
	{
		$certificates = array();
		
		$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates 
			WHERE status="active" 
			AND valid_until < DATE_ADD(NOW(), INTERVAL ? DAY)
			ORDER BY valid_until ASC',
			(int)$days
		);
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$row['domains'] = json_decode($row['domains'], true);
			$certificates[] = $row;
		}
		$res->Free();
		
		return $certificates;
	}
	
	/**
	 * Wildcard-Zertifikat empfehlen?
	 */
	public function suggestWildcard($domain)
	{
		$discovery = $this->discoverDomainsFromProtocols();
		
		if(isset($discovery[$domain]))
		{
			$subdomainCount = count($discovery[$domain]['subdomains']);
			
			// Wenn >= 3 Subdomains, Wildcard empfehlen
			if($subdomainCount >= 3)
			{
				return array(
					'suggest' => true,
					'reason' => sprintf('%d Subdomains erkannt - Wildcard würde alle abdecken!', $subdomainCount),
					'subdomains' => $discovery[$domain]['subdomains']
				);
			}
		}
		
		return array('suggest' => false);
	}
	
	/**
	 * Deploy certificate to service (with protocol/domain notification)
	 * @param int $certificateId Certificate ID
	 * @param string $service Service name (dovecot, postfix, grommunio, etc.)
	 * @return array Result with success/error
	 */
	public function deployCertificate($certificateId, $service)
	{
		$result = array('success' => false, 'message' => '');
		
		try {
			// Get certificate
			$res = $this->db->Query('SELECT * FROM {pre}ssl_certificates WHERE id=?', $certificateId);
			$cert = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$cert) {
				$result['message'] = 'Certificate not found';
				return $result;
			}
			
			// Update deployed_to list
			$deployedTo = json_decode($cert['deployed_to'], true) ?? array();
			if(!in_array($service, $deployedTo)) {
				$deployedTo[] = $service;
			}
			
			$this->db->Query('UPDATE {pre}ssl_certificates SET deployed_to=?, updated_at=NOW() WHERE id=?',
				json_encode($deployedTo), $certificateId);
			
			// Log deployment
			$this->db->Query('INSERT INTO {pre}ssl_deployments (certificate_id, service, status, deployed_at) VALUES (?, ?, "success", NOW())',
				$certificateId, $service);
			
			$result['success'] = true;
			$result['message'] = "Certificate deployed to {$service}";
			
			// ✅ NEW: Notify linked protocols/domains
			$this->_notifyLinkedProtocols($certificateId, 'deployed');
			
		} catch(Exception $e) {
			$result['message'] = 'Deployment error: ' . $e->getMessage();
			// Log failure
			$this->db->Query('INSERT INTO {pre}ssl_deployments (certificate_id, service, status, error_message, deployed_at) VALUES (?, ?, "failed", ?, NOW())',
				$certificateId, $service, $result['message']);
		}
		
		return $result;
	}
	
	/**
	 * Notify linked protocols and domains about certificate status change
	 * @param int $certificateId Certificate ID
	 * @param string $event Event type (deployed, renewed, expired)
	 */
	private function _notifyLinkedProtocols($certificateId, $event)
	{
		// Update protocol_links status
		$this->db->Query('UPDATE {pre}protocol_links SET 
			ssl_status = ?,
			ssl_last_check = NOW()
			WHERE ssl_certificate_id = ?',
			$event === 'deployed' ? 'active' : ($event === 'expired' ? 'expired' : 'pending'),
			$certificateId);
		
		// Update mf_domains status (ModernFrontend integration)
		$this->db->Query('UPDATE {pre}mf_domains SET 
			ssl_status = ?,
			ssl_last_check = NOW()
			WHERE ssl_certificate_id = ?',
			$event === 'deployed' ? 'active' : ($event === 'expired' ? 'expired' : 'pending'),
			$certificateId);
	}
}
