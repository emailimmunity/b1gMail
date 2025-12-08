<?php
/**
 * ModernFrontend <-> SSL Manager Integration Bridge
 * Handles automatic SSL provisioning for ModernFrontend domains
 * 
 * @package b1gMail
 * @subpackage ModernFrontend
 * @version 1.0.0
 * @date 2025-11-27
 */

if(!defined('B1GMAIL_INIT'))
	die('Direct access not allowed');

class ModernFrontendSSLBridge
{
	private $db;
	private $sslManager = null;
	private $domainManager = null;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Initialize SSL Manager (lazy loading)
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
	 * Initialize Domain Manager (lazy loading)
	 */
	private function _getDomainManager()
	{
		if($this->domainManager === null) {
			if(file_exists(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainManager.class.php')) {
				require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/domains/DomainManager.class.php');
				if(class_exists('DomainManager')) {
					$this->domainManager = new DomainManager();
				}
			}
		}
		return $this->domainManager;
	}
	
	/**
	 * Hook: Called when domain is created in ModernFrontend
	 * 
	 * @param int $domainId ModernFrontend domain ID
	 * @return array Result with success status
	 */
	public function onDomainCreated($domainId)
	{
		$domainManager = $this->_getDomainManager();
		if(!$domainManager) {
			return array('success' => false, 'error' => 'DomainManager not available');
		}
		
		$domain = $domainManager->getDomainById($domainId);
		
		if(!$domain) {
			return array('success' => false, 'error' => 'Domain not found');
		}
		
		// Check if SSL is enabled for this domain
		if($domain['ssl_enabled'] != 1) {
			return array('success' => true, 'message' => 'SSL not enabled for this domain');
		}
		
		PutLog('ModernFrontend-SSL Bridge: Domain #' . $domainId . ' created with SSL enabled (' . $domain['domain'] . ')',
			PRIO_NOTE, __FILE__, __LINE__);
		
		return $this->provisionSSLCertificate($domainId, $domain['domain']);
	}
	
	/**
	 * Hook: Called when domain SSL setting is updated
	 * 
	 * @param int $domainId ModernFrontend domain ID
	 * @param bool $sslEnabled New SSL status
	 * @return array Result
	 */
	public function onDomainSSLChanged($domainId, $sslEnabled)
	{
		if(!$sslEnabled) {
			// SSL disabled - update status
			$this->_updateDomainSSLStatus($domainId, 'none');
			PutLog('ModernFrontend-SSL Bridge: SSL disabled for domain #' . $domainId,
				PRIO_NOTE, __FILE__, __LINE__);
			return array('success' => true, 'message' => 'SSL disabled');
		}
		
		$domainManager = $this->_getDomainManager();
		if(!$domainManager) {
			return array('success' => false, 'error' => 'DomainManager not available');
		}
		
		$domain = $domainManager->getDomainById($domainId);
		if(!$domain) {
			return array('success' => false, 'error' => 'Domain not found');
		}
		
		PutLog('ModernFrontend-SSL Bridge: SSL enabled for domain #' . $domainId . ' (' . $domain['domain'] . ')',
			PRIO_NOTE, __FILE__, __LINE__);
		
		return $this->provisionSSLCertificate($domainId, $domain['domain']);
	}
	
	/**
	 * Provision SSL Certificate for domain
	 * 
	 * @param int $domainId ModernFrontend domain ID
	 * @param string $hostname Domain hostname (e.g., "example.com")
	 * @return array Result with certificate ID
	 */
	public function provisionSSLCertificate($domainId, $hostname)
	{
		$sslManager = $this->_getSSLManager();
		
		if(!$sslManager) {
			PutLog('ModernFrontend-SSL Bridge: SSL Manager not available',
				PRIO_WARNING, __FILE__, __LINE__);
			return array('success' => false, 'error' => 'SSL Manager not available');
		}
		
		// Check if certificate already exists
		$existingCert = $this->_findExistingCertificate($hostname);
		if($existingCert) {
			// Certificate exists, link it to domain
			$this->_linkCertificateToDomain($domainId, $existingCert['id']);
			PutLog('ModernFrontend-SSL Bridge: Linked existing certificate #' . $existingCert['id'] . ' to domain #' . $domainId,
				PRIO_NOTE, __FILE__, __LINE__);
			return array(
				'success' => true,
				'certificate_id' => $existingCert['id'],
				'message' => 'Existing certificate linked'
			);
		}
		
		// Create new certificate via SSL Manager
		try {
			$certData = array(
				'name' => 'ModernFrontend: ' . $hostname,
				'type' => 'single',
				'domains' => json_encode(array($hostname)),
				'primary_domain' => $hostname,
				'status' => 'pending',
				'auto_renew' => 1
			);
			
			$result = $sslManager->createCertificateRecord($certData);
			
			if($result['success'] && isset($result['certificate_id'])) {
				$certId = $result['certificate_id'];
				
				// Link certificate to domain
				$this->_linkCertificateToDomain($domainId, $certId);
				
				PutLog('ModernFrontend-SSL Bridge: Created certificate #' . $certId . ' for domain #' . $domainId . ' (' . $hostname . ')',
					PRIO_NOTE, __FILE__, __LINE__);
				
				// Note: Actual ACME provisioning would be triggered by cron job or separate process
				// For now, we just create the record and set status to pending
				
				return array(
					'success' => true,
					'certificate_id' => $certId,
					'message' => 'SSL certificate provisioning started'
				);
			}
			
			return $result;
			
		} catch(Exception $e) {
			PutLog('ModernFrontend-SSL Bridge: Error provisioning certificate - ' . $e->getMessage(),
				PRIO_WARNING, __FILE__, __LINE__);
			return array('success' => false, 'error' => $e->getMessage());
		}
	}
	
	/**
	 * Find existing SSL certificate for hostname
	 */
	private function _findExistingCertificate($hostname)
	{
		$hostname = $this->db->Escape($hostname);
		$result = $this->db->Query("SELECT * FROM {pre}ssl_certificates 
			WHERE primary_domain = '$hostname' 
			AND status IN ('active', 'pending')
			ORDER BY created_at DESC LIMIT 1");
			
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return $row;
		}
		return null;
	}
	
	/**
	 * Link SSL certificate to ModernFrontend domain
	 */
	private function _linkCertificateToDomain($domainId, $certificateId)
	{
		$domainId = intval($domainId);
		$certificateId = intval($certificateId);
		
		// Get certificate details
		$cert = $this->db->Query("SELECT valid_until, status FROM {pre}ssl_certificates WHERE id = $certificateId");
		$certData = $cert->FetchArray(MYSQLI_ASSOC);
		
		$sslStatus = $certData['status'] ?? 'pending';
		$validUntil = $certData['valid_until'] ? "'" . $this->db->Escape($certData['valid_until']) . "'" : 'NULL';
		
		$this->db->Query("UPDATE {pre}mf_domains 
			SET ssl_certificate_id = $certificateId,
				ssl_status = '$sslStatus',
				ssl_valid_until = $validUntil,
				ssl_last_check = NOW()
			WHERE id = $domainId");
	}
	
	/**
	 * Hook: Called when SSL certificate is successfully issued
	 * 
	 * @param int $certificateId SSL Manager certificate ID
	 */
	public function onCertificateIssued($certificateId)
	{
		$certificateId = intval($certificateId);
		
		// Find all ModernFrontend domains using this certificate
		$result = $this->db->Query("SELECT id FROM {pre}mf_domains WHERE ssl_certificate_id = $certificateId");
		
		$count = 0;
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$this->_updateDomainSSLStatus($row['id'], 'active');
			$count++;
		}
		
		if($count > 0) {
			PutLog("ModernFrontend-SSL Bridge: Certificate #$certificateId issued, updated $count domain(s)",
				PRIO_NOTE, __FILE__, __LINE__);
		}
	}
	
	/**
	 * Update domain SSL status
	 */
	private function _updateDomainSSLStatus($domainId, $status)
	{
		$domainId = intval($domainId);
		$status = $this->db->Escape($status);
		
		// If setting to 'active', also update valid_until from certificate
		if($status === 'active') {
			$this->db->Query("UPDATE {pre}mf_domains d
				JOIN {pre}ssl_certificates c ON d.ssl_certificate_id = c.id
				SET d.ssl_status = '$status',
					d.ssl_valid_until = c.valid_until,
					d.ssl_last_check = NOW()
				WHERE d.id = $domainId");
		} else {
			$this->db->Query("UPDATE {pre}mf_domains 
				SET ssl_status = '$status',
					ssl_last_check = NOW()
				WHERE id = $domainId");
		}
	}
	
	/**
	 * Check SSL status for all ModernFrontend domains
	 * (Called via cron job)
	 * 
	 * @return array Statistics
	 */
	public function checkAllDomains()
	{
		$result = $this->db->Query("SELECT d.id, d.domain, d.ssl_certificate_id, d.ssl_enabled
			FROM {pre}mf_domains d
			WHERE d.ssl_enabled = 1");
			
		$checked = 0;
		$updated = 0;
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$checked++;
			
			if($row['ssl_certificate_id']) {
				// Check certificate status
				$cert = $this->db->Query("SELECT status, valid_until FROM {pre}ssl_certificates 
					WHERE id = " . intval($row['ssl_certificate_id']));
					
				if($certData = $cert->FetchArray(MYSQLI_ASSOC)) {
					$this->db->Query("UPDATE {pre}mf_domains 
						SET ssl_status = '" . $this->db->Escape($certData['status']) . "',
							ssl_valid_until = " . ($certData['valid_until'] ? "'" . $this->db->Escape($certData['valid_until']) . "'" : 'NULL') . ",
							ssl_last_check = NOW()
						WHERE id = " . intval($row['id']));
					$updated++;
				}
			} else {
				// No certificate assigned, try to provision
				$provisionResult = $this->provisionSSLCertificate($row['id'], $row['domain']);
				if($provisionResult['success']) {
					$updated++;
				}
			}
		}
		
		PutLog("ModernFrontend-SSL Bridge: Checked $checked domains, updated $updated",
			PRIO_NOTE, __FILE__, __LINE__);
			
		return array('checked' => $checked, 'updated' => $updated);
	}
}

// Global helper function
function getModernFrontendSSLBridge()
{
	static $bridge = null;
	if($bridge === null) {
		$bridge = new ModernFrontendSSLBridge();
	}
	return $bridge;
}
?>
