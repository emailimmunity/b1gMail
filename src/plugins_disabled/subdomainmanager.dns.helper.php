<?php
/**
 * DNS Helper für SubDomainManager Plugin
 * ResellerInterface API-Client für automatische DNS-Verwaltung
 * 
 * @package SubDomainManager
 * @author b1gMail Plugin System
 * @version 1.0
 */

class SubDomainManagerDNSHelper
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
	 * HTTP-Request an ResellerInterface API
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
			'Authorization: Bearer ' . $this->apiKey,
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
	 * Erstelle DNS A-Record
	 * 
	 * @param string $zone Domain (z.B. gtin.org)
	 * @param string $name Subdomain (z.B. test)
	 * @param string $ip IPv4-Adresse
	 * @param int $ttl TTL in Sekunden (default: 300)
	 * @return mixed Record-ID oder false
	 */
	public function createARecord($zone, $name, $ip, $ttl = 300)
	{
		// Validierung
		if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$this->lastError = 'Invalid IPv4 address: ' . $ip;
			return false;
		}
		
		$data = [
			'zone' => $zone,
			'type' => 'A',
			'name' => $name,
			'content' => $ip,
			'ttl' => $ttl
		];
		
		$result = $this->apiRequest('POST', '/api/dns/records', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Erstelle DNS AAAA-Record (IPv6)
	 */
	public function createAAAARecord($zone, $name, $ipv6, $ttl = 300)
	{
		if(!filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$this->lastError = 'Invalid IPv6 address: ' . $ipv6;
			return false;
		}
		
		$data = [
			'zone' => $zone,
			'type' => 'AAAA',
			'name' => $name,
			'content' => $ipv6,
			'ttl' => $ttl
		];
		
		$result = $this->apiRequest('POST', '/api/dns/records', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Erstelle MX-Record
	 */
	public function createMXRecord($zone, $name, $mailServer, $priority = 10, $ttl = 300)
	{
		$data = [
			'zone' => $zone,
			'type' => 'MX',
			'name' => $name,
			'content' => $mailServer,
			'priority' => $priority,
			'ttl' => $ttl
		];
		
		$result = $this->apiRequest('POST', '/api/dns/records', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Erstelle TXT-Record (z.B. für SPF)
	 */
	public function createTXTRecord($zone, $name, $content, $ttl = 300)
	{
		$data = [
			'zone' => $zone,
			'type' => 'TXT',
			'name' => $name,
			'content' => $content,
			'ttl' => $ttl
		];
		
		$result = $this->apiRequest('POST', '/api/dns/records', $data);
		
		if($result && isset($result['id'])) {
			return $result['id'];
		}
		
		return false;
	}
	
	/**
	 * Update DNS A-Record (z.B. für DynDNS)
	 */
	public function updateARecord($recordId, $newIp)
	{
		if(!filter_var($newIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$this->lastError = 'Invalid IPv4 address: ' . $newIp;
			return false;
		}
		
		$data = [
			'content' => $newIp
		];
		
		$result = $this->apiRequest('PUT', '/api/dns/records/' . $recordId, $data);
		
		return $result !== false;
	}
	
	/**
	 * Lösche DNS-Record
	 */
	public function deleteRecord($recordId)
	{
		$result = $this->apiRequest('DELETE', '/api/dns/records/' . $recordId);
		return $result !== false;
	}
	
	/**
	 * Finde DNS-Records für eine Subdomain
	 */
	public function findRecords($zone, $name, $type = null)
	{
		$endpoint = '/api/dns/records?zone=' . urlencode($zone) . '&name=' . urlencode($name);
		
		if($type) {
			$endpoint .= '&type=' . urlencode($type);
		}
		
		$result = $this->apiRequest('GET', $endpoint);
		
		if($result && isset($result['records'])) {
			return $result['records'];
		}
		
		return [];
	}
	
	/**
	 * Lösche alle DNS-Records für eine Subdomain
	 */
	public function deleteAllRecordsForSubdomain($zone, $name)
	{
		$records = $this->findRecords($zone, $name);
		$deletedCount = 0;
		
		foreach($records as $record) {
			if(isset($record['id'])) {
				if($this->deleteRecord($record['id'])) {
					$deletedCount++;
				}
			}
		}
		
		return $deletedCount;
	}
	
	/**
	 * Test API-Verbindung
	 */
	public function testConnection()
	{
		$result = $this->apiRequest('GET', '/api/dns/zones');
		return $result !== false;
	}
	
	/**
	 * Erstelle komplettes DNS-Setup für Subdomain
	 * 
	 * @param string $zone Domain (z.B. gtin.org)
	 * @param string $name Subdomain (z.B. test)
	 * @param string $ip IPv4-Adresse
	 * @param bool $createMX MX-Record erstellen?
	 * @param string $mailServer Mail-Server (default: mail.{zone})
	 * @return array ['success' => bool, 'records' => array, 'error' => string]
	 */
	public function createFullDNSSetup($zone, $name, $ip, $createMX = false, $mailServer = null)
	{
		$records = [];
		$errors = [];
		
		// A-Record erstellen
		$aRecordId = $this->createARecord($zone, $name, $ip);
		if($aRecordId) {
			$records['A'] = $aRecordId;
		} else {
			$errors[] = 'A-Record: ' . $this->getLastError();
		}
		
		// MX-Record erstellen (optional)
		if($createMX) {
			if(!$mailServer) {
				$mailServer = 'mail.' . $zone;
			}
			
			$mxRecordId = $this->createMXRecord($zone, $name, $mailServer, 10);
			if($mxRecordId) {
				$records['MX'] = $mxRecordId;
			} else {
				$errors[] = 'MX-Record: ' . $this->getLastError();
			}
		}
		
		return [
			'success' => count($records) > 0,
			'records' => $records,
			'errors' => $errors,
			'error' => implode('; ', $errors)
		];
	}
}
?>
