<?php
/**
 * Grommunio Bridge
 * Vollständige Integration für MAPI/EWS/EAS
 */

class BMGrommunioBridge
{
	private $adminApi;
	private $adminUser;
	private $adminPass;
	private $sslVerify = true; // SECURITY: Enable SSL verification by default
	
	public function __construct()
	{
		$this->adminApi = defined('GROMMUNIO_ADMIN_API') ? GROMMUNIO_ADMIN_API : '';
		$this->adminUser = defined('GROMMUNIO_ADMIN_USER') ? GROMMUNIO_ADMIN_USER : 'admin';
		$this->adminPass = defined('GROMMUNIO_ADMIN_PASS') ? GROMMUNIO_ADMIN_PASS : '';
		// Allow disabling SSL verify only for development (not recommended for production)
		// @phpstan-ignore-next-line - Constant defined at runtime in config.inc.php
		$this->sslVerify = defined('GROMMUNIO_SSL_VERIFY') ? (bool)GROMMUNIO_SSL_VERIFY : true;
	}
	
	/**
	 * Setup secure cURL request
	 * @param resource|\CurlHandle $ch cURL handle (resource in PHP 7.4, CurlHandle in PHP 8.0+)
	 */
	private function _setupSecureCurl($ch)
	{
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerify ? 2 : 0);
		
		if(!empty($this->adminUser) && !empty($this->adminPass)) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->adminUser . ':' . $this->adminPass);
		}
	}
	
	/**
	 * Test Grommunio Connection
	 */
	public function test()
	{
		$result = array(
			'connected' => false,
			'version' => '',
			'message' => ''
		);
		
		if(empty($this->adminApi)) {
			$result['message'] = 'Admin-API URL nicht konfiguriert';
			return $result;
		}
		
		try {
			$ch = curl_init($this->adminApi . '/system/status');
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$result['connected'] = true;
				$result['message'] = 'Verbindung erfolgreich';
				
				$data = json_decode($response, true);
				if($data && isset($data['version'])) {
					$result['version'] = $data['version'];
				}
			} elseif($httpCode == 401) {
				$result['message'] = 'Authentifizierung fehlgeschlagen';
			} else {
				$result['message'] = 'Verbindung fehlgeschlagen (HTTP ' . $httpCode . ')';
			}
		} catch(Exception $e) {
			$result['message'] = 'Fehler: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get Grommunio Stats
	 */
	public function getStats()
	{
		global $db;
		
		$stats = array(
			'total_users' => 0,
			'synced_users' => 0,
			'pending_users' => 0,
			'failed_users' => 0,
			'active_sessions' => 0,
			'total_devices' => 0,
			// @phpstan-ignore-next-line - Constants defined at runtime in config.inc.php
			'mapi_enabled' => defined('GROMMUNIO_MAPI_URL') && !empty(GROMMUNIO_MAPI_URL),
			// @phpstan-ignore-next-line
			'ews_enabled' => defined('GROMMUNIO_EWS_URL') && !empty(GROMMUNIO_EWS_URL),
			// @phpstan-ignore-next-line
			'eas_enabled' => defined('GROMMUNIO_EAS_URL') && !empty(GROMMUNIO_EAS_URL)
		);
		
		if(!isset($db)) {
			return $stats;
		}
		
		try {
			// User counts
			$res = $db->Query('SELECT COUNT(*) FROM {pre}users');
			list($stats['total_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// Synced users (have grommunio_id)
			$res = $db->Query('SELECT COUNT(*) FROM {pre}users WHERE grommunio_id IS NOT NULL');
			list($stats['synced_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			$stats['pending_users'] = $stats['total_users'] - $stats['synced_users'];
			
			// Failed sync attempts
			$res = $db->Query('SELECT COUNT(DISTINCT userid) FROM {pre}grommunio_sync_log 
				WHERE status="failed" AND created >= DATE_SUB(NOW(), INTERVAL 24 HOUR)');
			list($stats['failed_users']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// Active sessions (if tracking table exists)
			$res = $db->Query('SELECT COUNT(*) FROM {pre}grommunio_sessions WHERE active=1');
			list($stats['active_sessions']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
			// ActiveSync devices
			$res = $db->Query('SELECT COUNT(*) FROM {pre}grommunio_devices WHERE active=1');
			list($stats['total_devices']) = $res->FetchArray(MYSQLI_NUM);
			$res->Free();
			
		} catch(Exception $e) {
			// Tables might not exist
		}
		
		return $stats;
	}
	
	/**
	 * Get Users List with Sync Status
	 */
	public function getUsersList($limit = 50, $offset = 0)
	{
		global $db;
		
		$users = array();
		
		if(!isset($db)) {
			return $users;
		}
		
		try {
			$res = $db->Query('SELECT u.id, u.email, u.vorname, u.nachname, 
				u.grommunio_id, u.grommunio_synced, u.grommunio_last_sync
				FROM {pre}users u
				ORDER BY u.id ASC
				LIMIT ?, ?', $offset, $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$users[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $users;
	}
	
	/**
	 * Sync User to Grommunio
	 */
	public function syncUser($userId)
	{
		global $db;
		
		$result = array(
			'success' => false,
			'message' => '',
			'grommunio_id' => null
		);
		
		if(!isset($db)) {
			$result['message'] = 'Datenbank nicht verfügbar';
			return $result;
		}
		
		if(empty($this->adminApi)) {
			$result['message'] = 'Admin-API nicht konfiguriert';
			return $result;
		}
		
		try {
			// Get user data
			$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userId);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$user) {
				$result['message'] = 'Benutzer nicht gefunden';
				return $result;
			}
			
			// Prepare user data for Grommunio
			$userData = array(
				'username' => $user['email'],
				'email' => $user['email'],
				'displayName' => trim($user['vorname'] . ' ' . $user['nachname']),
				'properties' => array(
					'storagequotalimit' => $user['mailspace'] * 1024, // KB to Bytes
				)
			);
			
			// Create or update user in Grommunio
			$ch = curl_init($this->adminApi . '/users');
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 201) {
				$responseData = json_decode($response, true);
				
				if($responseData && isset($responseData['id'])) {
					$result['success'] = true;
					$result['grommunio_id'] = $responseData['id'];
					$result['message'] = 'User erfolgreich synchronisiert';
					
					// Update user record
					$db->Query('UPDATE {pre}users SET 
						grommunio_id=?, 
						grommunio_synced="yes", 
						grommunio_last_sync=NOW() 
						WHERE id=?',
						$result['grommunio_id'], $userId);
					
					// Log success
					$db->Query('INSERT INTO {pre}grommunio_sync_log 
						(userid, status, message, created) 
						VALUES (?, "success", ?, NOW())',
						$userId, $result['message']);
				}
			} else {
				$result['message'] = 'Sync fehlgeschlagen (HTTP ' . $httpCode . '): ' . $response;
				
				// Log failure
				$db->Query('INSERT INTO {pre}grommunio_sync_log 
					(userid, status, message, created) 
					VALUES (?, "failed", ?, NOW())',
					$userId, $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Fehler: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get Sync Log
	 */
	public function getSyncLog($limit = 50)
	{
		global $db;
		
		$log = array();
		
		if(!isset($db)) {
			return $log;
		}
		
		try {
			$res = $db->Query('SELECT l.*, u.email 
				FROM {pre}grommunio_sync_log l
				LEFT JOIN {pre}users u ON l.userid = u.id
				ORDER BY l.created DESC
				LIMIT ?', $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$log[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $log;
	}
	
	/**
	 * Get ActiveSync Devices
	 */
	public function getActiveDevices($limit = 50)
	{
		global $db;
		
		$devices = array();
		
		if(!isset($db)) {
			return $devices;
		}
		
		try {
			$res = $db->Query('SELECT d.*, u.email 
				FROM {pre}grommunio_devices d
				LEFT JOIN {pre}users u ON d.userid = u.id
				WHERE d.active=1
				ORDER BY d.last_sync DESC
				LIMIT ?', $limit);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$devices[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $devices;
	}
	
	/**
	 * Create User in Grommunio (AUTO-SYNC)
	 */
	public function createUser($userId, $password = null)
	{
		global $db;
		
		$result = array('success' => false, 'message' => '', 'grommunio_id' => null);
		
		if(!isset($db)) {
			$result['message'] = 'Database not available';
			return $result;
		}
		
		if(empty($this->adminApi)) {
			$result['message'] = 'Admin API not configured';
			return $result;
		}
		
		try {
			$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userId);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$user) {
				$result['message'] = 'User not found';
				return $result;
			}
			
			$emailParts = explode('@', $user['email']);
			$domain = isset($emailParts[1]) ? $emailParts[1] : '';
			$username = $emailParts[0];
			
			if(empty($domain)) {
				$result['message'] = 'Invalid email domain';
				return $result;
			}
			
			$this->createDomain($domain);
			
			$displayName = trim($user['vorname'] . ' ' . $user['nachname']);
			if(empty($displayName)) $displayName = $user['email'];
			
			$userData = array(
				'username' => $username,
				'properties' => array(
					'displayname' => $displayName,
					'storagequotalimit' => ($user['mailspace'] * 1024 * 1024)
				)
			);
			
			if($password) $userData['password'] = $password;
			
			$ch = curl_init($this->adminApi . '/domains/' . $domain . '/users');
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 201) {
				$responseData = json_decode($response, true);
				
				if($responseData && isset($responseData['ID'])) {
					$result['success'] = true;
					$result['grommunio_id'] = $responseData['ID'];
					$result['message'] = 'User created in Grommunio';
					
					$db->Query('UPDATE {pre}users SET grommunio_id=?, grommunio_synced=1, grommunio_last_sync=NOW() WHERE id=?',
						$result['grommunio_id'], $userId);
					
					$this->logSync($userId, 'create', 'success', $result['message']);
				}
			} elseif($httpCode == 409) {
				$existingUser = $this->getGrommunioUser($domain, $username);
				if($existingUser && isset($existingUser['ID'])) {
					$result['success'] = true;
					$result['grommunio_id'] = $existingUser['ID'];
					$result['message'] = 'User linked (already exists)';
					
					$db->Query('UPDATE {pre}users SET grommunio_id=?, grommunio_synced=1, grommunio_last_sync=NOW() WHERE id=?',
						$result['grommunio_id'], $userId);
					
					$this->logSync($userId, 'create', 'success', $result['message']);
				}
			} else {
				$result['message'] = 'Create failed (HTTP ' . $httpCode . ')';
				$this->logSync($userId, 'create', 'failed', $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			$this->logSync($userId, 'create', 'failed', $result['message']);
		}
		
		return $result;
	}
	
	/**
	 * Update User in Grommunio (AUTO-SYNC)
	 */
	public function updateUser($userId)
	{
		global $db;
		
		$result = array('success' => false, 'message' => '');
		
		if(!isset($db) || empty($this->adminApi)) {
			$result['message'] = 'Invalid parameters';
			return $result;
		}
		
		try {
			$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userId);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$user) {
				$result['message'] = 'User not found';
				return $result;
			}
			
			if(empty($user['grommunio_id'])) {
				return $this->createUser($userId);
			}
			
			$emailParts = explode('@', $user['email']);
			$domain = isset($emailParts[1]) ? $emailParts[1] : '';
			
			$displayName = trim($user['vorname'] . ' ' . $user['nachname']);
			if(empty($displayName)) $displayName = $user['email'];
			
			$updateData = array(
				'properties' => array(
					'displayname' => $displayName,
					'storagequotalimit' => ($user['mailspace'] * 1024 * 1024)
				)
			);
			
			$ch = curl_init($this->adminApi . '/domains/' . $domain . '/users/' . $user['grommunio_id']);
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$result['success'] = true;
				$result['message'] = 'User updated in Grommunio';
				$db->Query('UPDATE {pre}users SET grommunio_last_sync=NOW() WHERE id=?', $userId);
				$this->logSync($userId, 'update', 'success', $result['message']);
			} else {
				$result['message'] = 'Update failed (HTTP ' . $httpCode . ')';
				$this->logSync($userId, 'update', 'failed', $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			$this->logSync($userId, 'update', 'failed', $result['message']);
		}
		
		return $result;
	}
	
	/**
	 * Delete User from Grommunio (AUTO-SYNC)
	 */
	public function deleteUser($userId, $grommunioId = null, $email = null)
	{
		global $db;
		
		$result = array('success' => false, 'message' => '');
		
		if(!isset($db) || empty($this->adminApi)) {
			$result['message'] = 'Invalid parameters';
			return $result;
		}
		
		try {
			if(!$grommunioId || !$email) {
				$res = $db->Query('SELECT email, grommunio_id FROM {pre}users WHERE id=?', $userId);
				$user = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				
				if($user) {
					$grommunioId = $user['grommunio_id'];
					$email = $user['email'];
				}
			}
			
			if(empty($grommunioId)) {
				$result['success'] = true;
				$result['message'] = 'User not synced';
				return $result;
			}
			
			$emailParts = explode('@', $email);
			$domain = isset($emailParts[1]) ? $emailParts[1] : '';
			
			$ch = curl_init($this->adminApi . '/domains/' . $domain . '/users/' . $grommunioId);
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 204 || $httpCode == 404) {
				$result['success'] = true;
				$result['message'] = 'User deleted from Grommunio';
				$this->logSync($userId, 'delete', 'success', $result['message']);
			} else {
				$result['message'] = 'Delete failed (HTTP ' . $httpCode . ')';
				$this->logSync($userId, 'delete', 'failed', $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			$this->logSync($userId, 'delete', 'failed', $result['message']);
		}
		
		return $result;
	}
	
	/**
	 * Update Password in Grommunio (AUTO-SYNC)
	 */
	public function updatePassword($userId, $newPassword)
	{
		global $db;
		
		$result = array('success' => false, 'message' => '');
		
		if(!isset($db) || empty($this->adminApi) || empty($newPassword)) {
			$result['message'] = 'Invalid parameters';
			return $result;
		}
		
		try {
			$res = $db->Query('SELECT email, grommunio_id FROM {pre}users WHERE id=?', $userId);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if(!$user) {
				$result['message'] = 'User not found';
				return $result;
			}
			
			if(empty($user['grommunio_id'])) {
				return $this->createUser($userId, $newPassword);
			}
			
			$emailParts = explode('@', $user['email']);
			$domain = isset($emailParts[1]) ? $emailParts[1] : '';
			
			$passwordData = array('password' => $newPassword);
			
			$ch = curl_init($this->adminApi . '/domains/' . $domain . '/users/' . $user['grommunio_id']);
			$this->_setupSecureCurl($ch);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($passwordData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$result['success'] = true;
				$result['message'] = 'Password updated in Grommunio';
				$this->logSync($userId, 'password', 'success', $result['message']);
			} else {
				$result['message'] = 'Password update failed (HTTP ' . $httpCode . ')';
				$this->logSync($userId, 'password', 'failed', $result['message']);
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
			$this->logSync($userId, 'password', 'failed', $result['message']);
		}
		
		return $result;
	}
	
	/**
	 * Create Domain in Grommunio
	 */
	public function createDomain($domainName)
	{
		$result = array('success' => false, 'message' => '', 'domain_id' => null);
		
		if(empty($this->adminApi) || empty($domainName)) {
			$result['message'] = 'Invalid parameters';
			return $result;
		}
		
		try {
			$domainData = array(
				'domainname' => $domainName,
				'domainStatus' => 0,
				'maxUser' => 0
			);
			
			$ch = curl_init($this->adminApi . '/domains');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($domainData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if(!empty($this->adminUser) && !empty($this->adminPass)) {
				curl_setopt($ch, CURLOPT_USERPWD, $this->adminUser . ':' . $this->adminPass);
			}
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 201) {
				$responseData = json_decode($response, true);
				if($responseData && isset($responseData['ID'])) {
					$result['success'] = true;
					$result['domain_id'] = $responseData['ID'];
					$result['message'] = 'Domain created';
				}
			} elseif($httpCode == 409) {
				$result['success'] = true;
				$result['message'] = 'Domain already exists';
			}
			
		} catch(Exception $e) {
			$result['message'] = 'Exception: ' . $e->getMessage();
		}
		
		return $result;
	}
	
	/**
	 * Get Grommunio User (Helper)
	 */
	private function getGrommunioUser($domain, $username)
	{
		if(empty($this->adminApi) || empty($domain) || empty($username)) {
			return null;
		}
		
		try {
			$ch = curl_init($this->adminApi . '/domains/' . $domain . '/users?query=' . urlencode($username));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if(!empty($this->adminUser) && !empty($this->adminPass)) {
				curl_setopt($ch, CURLOPT_USERPWD, $this->adminUser . ':' . $this->adminPass);
			}
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200) {
				$data = json_decode($response, true);
				if($data && isset($data['data']) && count($data['data']) > 0) {
					return $data['data'][0];
				}
			}
		} catch(Exception $e) {
			// Ignore
		}
		
		return null;
	}
	
	/**
	 * Log Sync Event (Helper)
	 */
	private function logSync($userId, $action, $status, $message)
	{
		global $db;
		
		if(!isset($db)) return;
		
		try {
			$db->Query('INSERT INTO {pre}grommunio_sync_log (userid, action, status, message, created) VALUES (?, ?, ?, ?, NOW())',
				$userId, $action, $status, substr($message, 0, 255));
		} catch(Exception $e) {
			// Table might not exist
		}
	}
}

/**
 * Get Grommunio Bridge Instance (Helper Function)
 */
function getGrommunioBridge()
{
	static $bridge = null;
	
	if($bridge === null) {
		$bridge = new BMGrommunioBridge();
	}
	
	return $bridge;
}

?>
