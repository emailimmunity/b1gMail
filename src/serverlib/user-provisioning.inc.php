<?php
/**
 * b1gMailServer User Provisioning
 * (c) 2025 b1gMail Development
 *
 * Automatisches Anlegen/Synchronisieren von Usern in Grommunio und SFTPGo
 */

require_once(dirname(__FILE__) . '/docker-api.inc.php');

class UserProvisioning
{
	private $db;
	private $prefs;
	
	public function __construct($db, $prefs)
	{
		$this->db = $db;
		$this->prefs = $prefs;
	}
	
	/**
	 * Provision user to Grommunio via API
	 */
	public function provisionGrommunioUser($userEmail, $password, $displayName)
	{
		if(empty($this->prefs['grommunio_enabled']) || empty($this->prefs['grommunio_api_url']))
		{
			return array('success' => false, 'message' => 'Grommunio nicht konfiguriert');
		}
		
		$apiUrl = $this->prefs['grommunio_api_url'];
		$user = $this->prefs['grommunio_admin_user'];
		$pass = $this->prefs['grommunio_admin_pass'];
		
		// Extract domain from email
		list($username, $domain) = explode('@', $userEmail, 2);
		
		// Create user payload
		$payload = array(
			'username' => $username,
			'properties' => array(
				'displayname' => $displayName,
				'storagequotalimit' => 10240 // 10GB default
			)
		);
		
		// First, ensure domain exists
		$this->ensureGrommunioDomain($domain);
		
		// Create user via API
		$ch = curl_init($apiUrl . '/domains/' . $domain . '/users');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 201 || $httpCode === 200)
		{
			// Set password
			$this->setGrommunioPassword($domain, $username, $password);
			
			return array(
				'success' => true,
				'message' => "✅ User $userEmail erfolgreich in Grommunio angelegt!",
				'data' => json_decode($response, true)
			);
		}
		else
		{
			return array(
				'success' => false,
				'message' => "❌ Fehler beim Anlegen (HTTP $httpCode)",
				'response' => $response
			);
		}
	}
	
	/**
	 * Ensure domain exists in Grommunio
	 */
	private function ensureGrommunioDomain($domain)
	{
		$apiUrl = $this->prefs['grommunio_api_url'];
		$user = $this->prefs['grommunio_admin_user'];
		$pass = $this->prefs['grommunio_admin_pass'];
		
		// Check if domain exists
		$ch = curl_init($apiUrl . '/domains/' . $domain);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 404)
		{
			// Domain doesn't exist, create it
			$payload = array(
				'domainname' => $domain,
				'domainStatus' => 0 // Active
			);
			
			$ch = curl_init($apiUrl . '/domains');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			
			curl_exec($ch);
			curl_close($ch);
		}
	}
	
	/**
	 * Set Grommunio user password
	 */
	private function setGrommunioPassword($domain, $username, $password)
	{
		$apiUrl = $this->prefs['grommunio_api_url'];
		$user = $this->prefs['grommunio_admin_user'];
		$pass = $this->prefs['grommunio_admin_pass'];
		
		$payload = array('password' => $password);
		
		$ch = curl_init($apiUrl . '/domains/' . $domain . '/users/' . $username . '/password');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
		curl_exec($ch);
		curl_close($ch);
	}
	
	/**
	 * Provision user to SFTPGo via API
	 */
	public function provisionSFTPGoUser($userEmail, $password)
	{
		if(empty($this->prefs['sftpgo_enabled']) || empty($this->prefs['sftpgo_api_url']))
		{
			return array('success' => false, 'message' => 'SFTPGo nicht konfiguriert');
		}
		
		$apiUrl = $this->prefs['sftpgo_api_url'];
		$user = $this->prefs['sftpgo_admin_user'];
		$pass = $this->prefs['sftpgo_admin_pass'];
		
		// Clean email for username
		$username = str_replace('@', '_', $userEmail);
		$username = str_replace('.', '_', $username);
		
		// Create user payload
		$payload = array(
			'username' => $username,
			'password' => $password,
			'status' => 1, // Active
			'home_dir' => '/data/' . $username,
			'permissions' => array(
				'/' => array('*') // All permissions
			),
			'quota_size' => 10737418240, // 10GB
			'quota_files' => 100000
		);
		
		// Check if S3 backend is enabled
		if(!empty($this->prefs['sftpgo_s3_enabled']))
		{
			$payload['filesystem'] = array(
				'provider' => 1, // S3
				's3config' => array(
					'bucket' => $this->prefs['sftpgo_s3_bucket'],
					'region' => 'us-east-1',
					'access_key' => $this->prefs['sftpgo_s3_access_key'],
					'access_secret' => $this->prefs['sftpgo_s3_secret_key'],
					'endpoint' => $this->prefs['sftpgo_s3_endpoint'],
					'key_prefix' => $username . '/'
				)
			);
		}
		
		// Create user via API
		$ch = curl_init($apiUrl . '/users');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 201 || $httpCode === 200)
		{
			return array(
				'success' => true,
				'message' => "✅ User $username erfolgreich in SFTPGo angelegt!",
				'username' => $username,
				'data' => json_decode($response, true)
			);
		}
		else
		{
			return array(
				'success' => false,
				'message' => "❌ Fehler beim Anlegen (HTTP $httpCode)",
				'response' => $response
			);
		}
	}
	
	/**
	 * Delete user from Grommunio
	 */
	public function deleteGrommunioUser($userEmail)
	{
		if(empty($this->prefs['grommunio_enabled']))
		{
			return array('success' => false, 'message' => 'Grommunio nicht konfiguriert');
		}
		
		$apiUrl = $this->prefs['grommunio_api_url'];
		$user = $this->prefs['grommunio_admin_user'];
		$pass = $this->prefs['grommunio_admin_pass'];
		
		list($username, $domain) = explode('@', $userEmail, 2);
		
		$ch = curl_init($apiUrl . '/domains/' . $domain . '/users/' . $username);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return array('success' => $httpCode === 204 || $httpCode === 200);
	}
	
	/**
	 * Delete user from SFTPGo
	 */
	public function deleteSFTPGoUser($userEmail)
	{
		if(empty($this->prefs['sftpgo_enabled']))
		{
			return array('success' => false, 'message' => 'SFTPGo nicht konfiguriert');
		}
		
		$apiUrl = $this->prefs['sftpgo_api_url'];
		$user = $this->prefs['sftpgo_admin_user'];
		$pass = $this->prefs['sftpgo_admin_pass'];
		
		$username = str_replace('@', '_', $userEmail);
		$username = str_replace('.', '_', $username);
		
		$ch = curl_init($apiUrl . '/users/' . $username);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return array('success' => $httpCode === 200);
	}
}
