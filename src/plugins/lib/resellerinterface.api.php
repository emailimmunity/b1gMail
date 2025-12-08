<?php
/**
 * ResellerInterface.com API Client
 * 
 * API-Dokumentation: https://resellerinterface.com/rest-api/
 * 
 * @author b1gMail Development
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

/**
 * ResellerInterface API Klasse
 */
class ResellerInterfaceAPI
{
	private $apiKey;
	private $apiUrl;
	private $lastError = '';
	
	/**
	 * Constructor
	 */
	public function __construct($apiKey, $apiUrl = 'https://resellerinterface.com/api/v1')
	{
		$this->apiKey = $apiKey;
		$this->apiUrl = rtrim($apiUrl, '/');
	}
	
	/**
	 * DNS A-Record erstellen
	 */
	public function createARecord($domain, $ip, $ttl = 300)
	{
		$data = [
			'type' => 'A',
			'name' => $domain,
			'content' => $ip,
			'ttl' => $ttl
		];
		
		$result = $this->request('POST', '/dns/create-record', $data);
		
		if($result['success']) {
			PutLog("ResellerInterface: A-Record created for $domain → $ip", PRIO_NOTE, __FILE__, __LINE__);
			return true;
		}
		
		$this->lastError = $result['error'] ?? 'Unknown error';
		PutLog("ResellerInterface: Failed to create A-Record for $domain: " . $this->lastError, 
			PRIO_ERROR, __FILE__, __LINE__);
		return false;
	}
	
	/**
	 * DNS-Record aktualisieren (für DynDNS)
	 */
	public function updateARecord($domain, $newIP)
	{
		$data = [
			'name' => $domain,
			'content' => $newIP
		];
		
		$result = $this->request('PUT', '/dns/update-record', $data);
		
		if($result['success']) {
			PutLog("ResellerInterface: A-Record updated for $domain → $newIP", PRIO_NOTE, __FILE__, __LINE__);
			return true;
		}
		
		$this->lastError = $result['error'] ?? 'Unknown error';
		PutLog("ResellerInterface: Failed to update A-Record for $domain: " . $this->lastError, 
			PRIO_ERROR, __FILE__, __LINE__);
		return false;
	}
	
	/**
	 * DNS-Record löschen
	 */
	public function deleteRecord($domain)
	{
		$data = [
			'name' => $domain
		];
		
		$result = $this->request('DELETE', '/dns/delete-record', $data);
		
		if($result['success']) {
			PutLog("ResellerInterface: Record deleted for $domain", PRIO_NOTE, __FILE__, __LINE__);
			return true;
		}
		
		$this->lastError = $result['error'] ?? 'Unknown error';
		PutLog("ResellerInterface: Failed to delete record for $domain: " . $this->lastError, 
			PRIO_ERROR, __FILE__, __LINE__);
		return false;
	}
	
	/**
	 * DNS-Records für Domain abrufen
	 */
	public function listRecords($domain)
	{
		$result = $this->request('GET', '/dns/list-records?domain=' . urlencode($domain));
		
		if($result['success']) {
			return $result['data'] ?? [];
		}
		
		$this->lastError = $result['error'] ?? 'Unknown error';
		return [];
	}
	
	/**
	 * DynDNS Update
	 */
	public function updateDynDNS($domain, $ip)
	{
		// Wrapper für updateARecord
		return $this->updateARecord($domain, $ip);
	}
	
	/**
	 * API-Request durchführen
	 */
	private function request($method, $endpoint, $data = [])
	{
		$url = $this->apiUrl . $endpoint;
		
		$ch = curl_init();
		
		// Headers
		$headers = [
			'Authorization: Bearer ' . $this->apiKey,
			'Content-Type: application/json',
			'Accept: application/json'
		];
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		
		// Method & Data
		switch(strtoupper($method)) {
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				break;
			
			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				break;
			
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if(!empty($data)) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			
			case 'GET':
			default:
				// GET ist default
				break;
		}
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		// Fehlerbehandlung
		if($error) {
			return [
				'success' => false,
				'error' => 'CURL Error: ' . $error
			];
		}
		
		// Response parsen
		$responseData = json_decode($response, true);
		
		if($httpCode >= 200 && $httpCode < 300) {
			return [
				'success' => true,
				'data' => $responseData
			];
		}
		
		return [
			'success' => false,
			'error' => $responseData['message'] ?? 'HTTP Error ' . $httpCode,
			'http_code' => $httpCode
		];
	}
	
	/**
	 * Letzten Fehler abrufen
	 */
	public function getLastError()
	{
		return $this->lastError;
	}
	
	/**
	 * API-Verbindung testen
	 */
	public function testConnection()
	{
		$result = $this->request('GET', '/status');
		return $result['success'];
	}
}
