<?php
/**
 * KeyHelp Integration Helper für SubDomainManager Plugin
 * 
 * Integriert Subdomains mit KeyHelp für automatische Webspace-Erstellung
 * 
 * @package SubDomainManager
 * @author b1gMail Plugin System
 * @version 1.0
 */

class SubDomainManagerKeyhelpHelper
{
	private $apiUrl;
	private $apiKey;
	private $lastError = '';
	
	/**
	 * Constructor
	 */
	public function __construct($apiUrl, $apiKey)
	{
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->apiKey = $apiKey;
	}
	
	/**
	 * Get last error message
	 */
	public function getLastError()
	{
		return $this->lastError;
	}
	
	/**
	 * HTTP-Request an KeyHelp API
	 */
	private function apiRequest($method, $endpoint, $data = null)
	{
		$url = $this->apiUrl . $endpoint;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		
		// Headers
		$headers = [
			'X-API-Key: ' . $this->apiKey,
			'Content-Type: application/json',
			'Accept: application/json'
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		// Method & Data
		if($method === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			if($data) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			}
		} elseif($method === 'PUT') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			if($data) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			}
		} elseif($method === 'DELETE') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		if($error) {
			$this->lastError = 'cURL Error: ' . $error;
			return false;
		}
		
		if($httpCode >= 400) {
			$this->lastError = 'HTTP ' . $httpCode . ': ' . $response;
			return false;
		}
		
		return json_decode($response, true);
	}
	
	/**
	 * Erstelle Webspace/Domain in KeyHelp
	 * 
	 * @param string $domain Domain-Name
	 * @param string $username Benutzername für FTP/SSH
	 * @param string $password Passwort
	 * @param array $options Zusätzliche Optionen
	 * @return mixed Domain-ID oder false
	 */
	public function createDomain($domain, $username, $password, $options = [])
	{
		$data = array_merge([
			'domain' => $domain,
			'username' => $username,
			'password' => $password,
			'document_root' => '/var/www/' . $domain,
			'php_version' => '8.2',
			'ssl_enabled' => true,
			'ssl_redirect' => true,
			'autossl' => true // Let's Encrypt
		], $options);
		
		$result = $this->apiRequest('POST', '/api/v2/domains', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Lösche Domain aus KeyHelp
	 */
	public function deleteDomain($domainId)
	{
		$result = $this->apiRequest('DELETE', '/api/v2/domains/' . $domainId);
		return $result !== false;
	}
	
	/**
	 * Erstelle FTP-Account
	 */
	public function createFTPAccount($domainId, $username, $password, $path = '/')
	{
		$data = [
			'domain_id' => $domainId,
			'username' => $username,
			'password' => $password,
			'home_directory' => $path
		];
		
		$result = $this->apiRequest('POST', '/api/v2/ftp-accounts', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Erstelle SSL-Zertifikat (Let's Encrypt)
	 */
	public function createSSLCertificate($domainId)
	{
		$data = [
			'domain_id' => $domainId,
			'provider' => 'letsencrypt'
		];
		
		$result = $this->apiRequest('POST', '/api/v2/ssl-certificates', $data);
		
		return $result !== false;
	}
	
	/**
	 * Hole Domain-Informationen
	 */
	public function getDomain($domainId)
	{
		return $this->apiRequest('GET', '/api/v2/domains/' . $domainId);
	}
	
	/**
	 * Test API-Verbindung
	 */
	public function testConnection()
	{
		$result = $this->apiRequest('GET', '/api/v2/domains');
		return $result !== false;
	}
	
	/**
	 * Erstelle komplettes Setup für Subdomain
	 * 
	 * @param string $domain Subdomain
	 * @param string $username Benutzername
	 * @param string $password Passwort
	 * @return array ['success' => bool, 'domain_id' => int, 'ftp_id' => int, 'error' => string]
	 */
	public function createCompleteSetup($domain, $username, $password)
	{
		// Erstelle Domain/Webspace
		$domainId = $this->createDomain($domain, $username, $password);
		
		if(!$domainId) {
			return [
				'success' => false,
				'error' => 'Domain creation failed: ' . $this->getLastError()
			];
		}
		
		// SSL-Zertifikat erstellen
		$this->createSSLCertificate($domainId);
		
		return [
			'success' => true,
			'domain_id' => $domainId,
			'error' => ''
		];
	}
}
?>
