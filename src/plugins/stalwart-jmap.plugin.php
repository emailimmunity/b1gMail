<?php
/**
 * Stalwart JMAP Integration Plugin for b1gMail
 * 
 * Enables modern JMAP (JSON Mail Access Protocol) support
 * via Stalwart Mail Server integration
 * 
 * @version 1.0
 * @author b1gMail Development Team
 * @license GPL-2.0
 */

class StalwartJMAPPlugin extends BMPlugin
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		// Plugin metadata
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'Stalwart JMAP Integration';
		$this->author = 'b1gMail Development Team';
		$this->version = '1.0.0';
		$this->website = 'https://stalw.art';
		$this->updateURL = '';
		
		// Admin configuration
		$this->admin_pages = true;
		$this->admin_page_title = 'JMAP Protocol';
		$this->admin_page_icon = 'jmap.png';
		$this->configurable = true;
		
		// Register group options
		$this->RegisterGroupOption('jmap_enabled',
			FIELD_CHECKBOX,
			'JMAP Protocol aktivieren',
			'Aktiviert Zugriff via JSON Mail Access Protocol (JMAP)',
			true);
		
		$this->RegisterGroupOption('jmap_server_url',
			FIELD_TEXT,
			'JMAP Server URL',
			'URL des Stalwart JMAP Servers',
			'http://stalwart:8080');
		
		$this->RegisterGroupOption('jmap_max_upload_mb',
			FIELD_TEXT,
			'Max. Upload-Größe (MB)',
			'Maximale Größe für Datei-Uploads',
			50);
		
		$this->RegisterGroupOption('jmap_max_objects',
			FIELD_TEXT,
			'Max. Objekte pro Request',
			'Maximale Anzahl Objekte (Mails, etc.) pro JMAP-Request',
			500);
		
		$this->RegisterGroupOption('jmap_websocket_enabled',
			FIELD_CHECKBOX,
			'WebSocket Push aktivieren',
			'Aktiviert Echtzeit-Benachrichtigungen via WebSocket',
			true);
		
		$this->RegisterGroupOption('jmap_debug_mode',
			FIELD_CHECKBOX,
			'Debug-Modus',
			'Aktiviert ausführliches Logging für JMAP-Requests',
			false);
	}
	
	/**
	 * Get JMAP session information for user
	 * 
	 * @param int $userId b1gMail user ID
	 * @return array JMAP session object
	 */
	public function getJMAPSession($userId)
	{
		global $db, $bm_prefs;
		
		// Check if JMAP is enabled
		if(!$bm_prefs['jmap_enabled']) {
			return array(
				'error' => 'JMAP protocol is not enabled',
				'type' => 'urn:ietf:params:jmap:error:disabled'
			);
		}
		
		// Get user information
		$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			return array(
				'error' => 'User not found',
				'type' => 'urn:ietf:params:jmap:error:notFound'
			);
		}
		
		$user = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Build JMAP session object (RFC 8620)
		$session = array(
			'capabilities' => array(
				// Core JMAP capability
				'urn:ietf:params:jmap:core' => array(
					'maxSizeUpload' => (int)$bm_prefs['jmap_max_upload_mb'] * 1024 * 1024,
					'maxConcurrentUpload' => 4,
					'maxSizeRequest' => 10000000,
					'maxConcurrentRequests' => 4,
					'maxCallsInRequest' => 16,
					'maxObjectsInGet' => (int)$bm_prefs['jmap_max_objects'],
					'maxObjectsInSet' => (int)$bm_prefs['jmap_max_objects'],
					'collationAlgorithms' => array(
						'i;ascii-numeric',
						'i;ascii-casemap',
						'i;unicode-casemap'
					)
				),
				// Mail capability
				'urn:ietf:params:jmap:mail' => array(
					'maxMailboxesPerEmail' => null,
					'maxMailboxDepth' => null,
					'maxSizeMailboxName' => 255,
					'maxSizeAttachmentsPerEmail' => (int)$bm_prefs['jmap_max_upload_mb'] * 1024 * 1024,
					'emailQuerySortOptions' => array('receivedAt', 'sentAt', 'size', 'from', 'to', 'subject'),
					'mayCreateTopLevelMailbox' => true
				),
				// Submission capability
				'urn:ietf:params:jmap:submission' => array(
					'maxDelayedSend' => 44236800,
					'submissionExtensions' => array()
				)
			),
			'accounts' => array(
				$user['email'] => array(
					'name' => trim($user['vorname'] . ' ' . $user['nachname']),
					'isPersonal' => true,
					'isReadOnly' => false,
					'accountCapabilities' => array(
						'urn:ietf:params:jmap:mail' => array(),
						'urn:ietf:params:jmap:submission' => array()
					)
				)
			),
			'primaryAccounts' => array(
				'urn:ietf:params:jmap:mail' => $user['email'],
				'urn:ietf:params:jmap:submission' => $user['email']
			),
			'username' => $user['email'],
			'apiUrl' => $bm_prefs['jmap_server_url'] . '/jmap',
			'downloadUrl' => $bm_prefs['jmap_server_url'] . '/download/{accountId}/{blobId}/{name}?accept={type}',
			'uploadUrl' => $bm_prefs['jmap_server_url'] . '/upload/{accountId}/',
			'eventSourceUrl' => $bm_prefs['jmap_server_url'] . '/eventsource/?types={types}&closeafter={closeafter}&ping={ping}',
			'state' => $this->getJMAPState($userId)
		);
		
		// Add WebSocket if enabled
		if($bm_prefs['jmap_websocket_enabled']) {
			$session['capabilities']['urn:ietf:params:jmap:websocket'] = array(
				'supportsPush' => true,
				'url' => str_replace('http://', 'ws://', str_replace('https://', 'wss://', $bm_prefs['jmap_server_url'])) . '/jmap/ws'
			);
		}
		
		return $session;
	}
	
	/**
	 * Get JMAP state for user
	 * 
	 * @param int $userId User ID
	 * @return string State string
	 */
	private function getJMAPState($userId)
	{
		global $db;
		
		// Generate state based on last mail modification
		$res = $db->Query('SELECT MAX(datum) as last_change FROM {pre}mails WHERE userid=?', $userId);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$lastChange = $row['last_change'] ? strtotime($row['last_change']) : time();
		return md5($userId . ':' . $lastChange);
	}
	
	/**
	 * Proxy JMAP request to Stalwart server
	 * 
	 * @param int $userId User ID
	 * @param string $requestBody JMAP request JSON
	 * @return array Response array with 'status' and 'body'
	 */
	public function proxyJMAPRequest($userId, $requestBody)
	{
		global $db, $bm_prefs;
		
		$result = array(
			'status' => 500,
			'body' => '',
			'error' => null
		);
		
		// Check if enabled
		if(!$bm_prefs['jmap_enabled']) {
			$result['status'] = 503;
			$result['error'] = 'JMAP not enabled';
			return $result;
		}
		
		// Get user email for authentication
		$res = $db->Query('SELECT email, passwd FROM {pre}users WHERE id=?', $userId);
		if(!$res || $res->RowCount() == 0) {
			if($res) $res->Free();
			$result['status'] = 404;
			$result['error'] = 'User not found';
			return $result;
		}
		
		$user = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Prepare request to Stalwart
		$stalwartUrl = $bm_prefs['jmap_server_url'] . '/jmap';
		
		$ch = curl_init($stalwartUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode($user['email'] . ':' . $user['passwd'])
		));
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		// Execute request
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);
		
		if($response === false) {
			$result['error'] = 'cURL error: ' . $curlError;
			return $result;
		}
		
		$result['status'] = $httpCode;
		$result['body'] = $response;
		
		// Log if debug mode
		if($bm_prefs['jmap_debug_mode']) {
			PutLog('JMAP Request: User ' . $userId . ', Status ' . $httpCode . ', Request: ' . substr($requestBody, 0, 500),
				PRIO_DEBUG, __FILE__, __LINE__);
		}
		
		return $result;
	}
	
	/**
	 * Check Stalwart server status
	 * 
	 * @return array Status information
	 */
	public function checkServerStatus()
	{
		global $bm_prefs;
		
		$status = array(
			'online' => false,
			'version' => 'Unknown',
			'message' => '',
			'response_time' => 0
		);
		
		if(!$bm_prefs['jmap_enabled']) {
			$status['message'] = 'JMAP is disabled';
			return $status;
		}
		
		try {
			$startTime = microtime(true);
			
			$ch = curl_init($bm_prefs['jmap_server_url'] . '/.well-known/jmap');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			$status['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
			
			if($httpCode >= 200 && $httpCode < 300) {
				$status['online'] = true;
				$status['message'] = 'Stalwart JMAP Server is online';
				
				// Try to get version from response
				$data = json_decode($response, true);
				if($data && isset($data['capabilities'])) {
					$status['version'] = 'Stalwart Mail Server';
				}
			} else {
				$status['message'] = 'Server returned HTTP ' . $httpCode;
			}
		} catch(Exception $e) {
			$status['message'] = 'Connection failed: ' . $e->getMessage();
		}
		
		return $status;
	}
	
	/**
	 * Admin page handler
	 */
	function AdminPage()
	{
		global $tpl, $bm_prefs, $lang_admin;
		
		// Get server status
		$serverStatus = $this->checkServerStatus();
		
		// Get statistics
		$stats = $this->getStatistics();
		
		// Assign to template
		$tpl->assign('jmapEnabled', $bm_prefs['jmap_enabled']);
		$tpl->assign('jmapServerUrl', $bm_prefs['jmap_server_url']);
		$tpl->assign('jmapMaxUpload', $bm_prefs['jmap_max_upload_mb']);
		$tpl->assign('jmapMaxObjects', $bm_prefs['jmap_max_objects']);
		$tpl->assign('jmapWebSocketEnabled', $bm_prefs['jmap_websocket_enabled']);
		$tpl->assign('jmapDebugMode', $bm_prefs['jmap_debug_mode']);
		
		$tpl->assign('serverStatus', $serverStatus);
		$tpl->assign('jmapStats', $stats);
		
		$tpl->assign('pageTitle', 'Stalwart JMAP Integration');
		$tpl->assign('page', 'stalwart-jmap.admin.tpl');
	}
	
	/**
	 * Get JMAP usage statistics
	 * 
	 * @return array Statistics
	 */
	public function getStatistics()
	{
		global $db;
		
		$stats = array(
			'total_users' => 0,
			'jmap_enabled_users' => 0,
			'total_mailboxes' => 0,
			'total_messages' => 0
		);
		
		// Total users
		$res = $db->Query("SELECT COUNT(*) FROM {pre}users WHERE gesperrt != 'delete'");
		list($stats['total_users']) = $res->FetchArray(MYSQLI_NUM);
		$res->Free();
		
		// All users are JMAP-enabled if plugin is active
		$stats['jmap_enabled_users'] = $stats['total_users'];
		
		// Total folders (mailboxes)
		$res = $db->Query('SELECT COUNT(*) FROM {pre}folders');
		list($stats['total_mailboxes']) = $res->FetchArray(MYSQLI_NUM);
		$res->Free();
		
		// Total messages
		$res = $db->Query('SELECT COUNT(*) FROM {pre}mails');
		list($stats['total_messages']) = $res->FetchArray(MYSQLI_NUM);
		$res->Free();
		
		return $stats;
	}
}
