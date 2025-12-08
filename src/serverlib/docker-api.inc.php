<?php
/**
 * Docker API Helper für b1gMail
 * Ermöglicht Kommunikation mit Docker-Containern für Config-Sync
 * Copyright (c) 2025 b1gMail Project
 */

class DockerAPI {
	private $dockerHost = 'http://localhost:2375'; // Docker API Endpoint
	private $useSocket = true; // Use Unix socket by default
	private $socketPath = '/var/run/docker.sock';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// Check if we're in Docker or Windows
		if (PHP_OS_FAMILY === 'Windows' || !file_exists($this->socketPath)) {
			$this->useSocket = false;
			$this->dockerHost = 'http://host.docker.internal:2375';
		}
	}
	
	/**
	 * Execute Docker command via exec
	 */
	public function execCommand($container, $command) {
		// Use docker exec for reliability
		$escapedCommand = escapeshellarg($command);
		$fullCommand = "docker exec $container sh -c $escapedCommand";
		
		exec($fullCommand, $output, $returnCode);
		
		return array(
			'success' => $returnCode === 0,
			'output' => implode("\n", $output),
			'code' => $returnCode
		);
	}
	
	/**
	 * Restart a container
	 */
	public function restartContainer($container) {
		exec("docker restart $container 2>&1", $output, $returnCode);
		
		return array(
			'success' => $returnCode === 0,
			'message' => $returnCode === 0 ? "Container $container restarted" : implode("\n", $output)
		);
	}
	
	/**
	 * Copy file to container
	 */
	public function copyToContainer($container, $localPath, $containerPath) {
		$escapedLocal = escapeshellarg($localPath);
		$command = "docker cp $escapedLocal $container:$containerPath 2>&1";
		
		exec($command, $output, $returnCode);
		
		return array(
			'success' => $returnCode === 0,
			'message' => implode("\n", $output)
		);
	}
	
	/**
	 * Get container environment variables
	 */
	public function getContainerEnv($container) {
		$result = $this->execCommand($container, 'env');
		
		if ($result['success']) {
			$lines = explode("\n", $result['output']);
			$env = array();
			foreach ($lines as $line) {
				if (strpos($line, '=') !== false) {
					list($key, $value) = explode('=', $line, 2);
					$env[$key] = $value;
				}
			}
			return $env;
		}
		
		return array();
	}
	
	/**
	 * Check if container is running
	 */
	public function isContainerRunning($container) {
		exec("docker ps --filter name=$container --format '{{.Names}}' 2>&1", $output, $returnCode);
		return !empty($output) && in_array($container, $output);
	}
	
	/**
	 * Get container logs (last N lines)
	 */
	public function getContainerLogs($container, $lines = 50) {
		exec("docker logs --tail $lines $container 2>&1", $output, $returnCode);
		
		return array(
			'success' => $returnCode === 0,
			'logs' => implode("\n", $output)
		);
	}
}

/**
 * Protocol Config Sync Helper
 */
class ProtocolConfigSync {
	private $db;
	private $docker;
	private $prefs;
	
	public function __construct(&$db, $prefs) {
		$this->db = $db;
		$this->docker = new DockerAPI();
		$this->prefs = $prefs;
	}
	
	/**
	 * Sync Dovecot configuration
	 */
	public function syncDovecot() {
		$container = 'b1gmail-cyrus';
		
		// Check if container is running
		if (!$this->docker->isContainerRunning($container)) {
			return array(
				'success' => false,
				'message' => "❌ Container $container ist nicht aktiv!"
			);
		}
		
		// Generate dovecot-sql.conf
		$sqlConf = $this->generateDovecotSQLConfig();
		$tmpFile = tempnam(sys_get_temp_dir(), 'dovecot-sql-');
		file_put_contents($tmpFile, $sqlConf);
		
		// Copy to container
		$copyResult = $this->docker->copyToContainer($container, $tmpFile, '/etc/dovecot/dovecot-sql.conf.ext');
		unlink($tmpFile);
		
		if (!$copyResult['success']) {
			return array(
				'success' => false,
				'message' => '❌ Fehler beim Kopieren: ' . $copyResult['message']
			);
		}
		
		// Reload Dovecot
		$reloadResult = $this->docker->execCommand($container, 'doveadm reload');
		
		if ($reloadResult['success']) {
			return array(
				'success' => true,
				'message' => '✅ Dovecot-Konfiguration erfolgreich übertragen und geladen!'
			);
		} else {
			return array(
				'success' => false,
				'message' => '❌ Reload fehlgeschlagen: ' . $reloadResult['output']
			);
		}
	}
	
	/**
	 * Generate Dovecot SQL config
	 */
	private function generateDovecotSQLConfig() {
		$host = $this->prefs['dovecot_sql_host'];
		$db = $this->prefs['dovecot_sql_database'];
		$user = $this->prefs['dovecot_sql_user'];
		$pass = $this->prefs['dovecot_sql_password'];
		
		return <<<SQL
driver = mysql
connect = host=$host dbname=$db user=$user password=$pass
default_pass_scheme = MD5-CRYPT

password_query = SELECT email as user, passwort as password FROM bm60_users WHERE email='%u' AND gesperrt=0
user_query = SELECT email as user, email as home, 1000 AS uid, 1000 AS gid FROM bm60_users WHERE email='%u'
iterate_query = SELECT email as user FROM bm60_users WHERE gesperrt=0
SQL;
	}
	
	/**
	 * Sync Postfix configuration
	 */
	public function syncPostfix() {
		$container = 'b1gmail-postfix';
		
		if (!$this->docker->isContainerRunning($container)) {
			return array(
				'success' => false,
				'message' => "❌ Container $container ist nicht aktiv!"
			);
		}
		
		// Generate main.cf changes
		$mainCfChanges = array();
		
		if (!empty($this->prefs['postfix_relayhost'])) {
			$mainCfChanges[] = "postconf -e 'relayhost={$this->prefs['postfix_relayhost']}'";
		}
		
		if (!empty($this->prefs['postfix_allowed_domains'])) {
			$domains = str_replace("\n", ",", $this->prefs['postfix_allowed_domains']);
			$mainCfChanges[] = "postconf -e 'mydestination=$domains'";
		}
		
		// Apply changes
		foreach ($mainCfChanges as $cmd) {
			$this->docker->execCommand($container, $cmd);
		}
		
		// Reload Postfix
		$reloadResult = $this->docker->execCommand($container, 'postfix reload');
		
		if ($reloadResult['success']) {
			return array(
				'success' => true,
				'message' => '✅ Postfix-Konfiguration erfolgreich übertragen und geladen!'
			);
		} else {
			return array(
				'success' => false,
				'message' => '❌ Reload fehlgeschlagen: ' . $reloadResult['output']
			);
		}
	}
	
	/**
	 * Sync Grommunio via API
	 */
	public function syncGrommunio() {
		if (!$this->prefs['grommunio_enabled']) {
			return array(
				'success' => false,
				'message' => '❌ Grommunio ist nicht aktiviert!'
			);
		}
		
		$apiUrl = $this->prefs['grommunio_api_url'];
		$user = $this->prefs['grommunio_admin_user'];
		$pass = $this->prefs['grommunio_admin_pass'];
		
		// Test API connection
		$ch = curl_init($apiUrl . '/system/status');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($httpCode === 200) {
			return array(
				'success' => true,
				'message' => '✅ Grommunio API-Verbindung erfolgreich!',
				'data' => json_decode($response, true)
			);
		} else {
			return array(
				'success' => false,
				'message' => "❌ API-Verbindung fehlgeschlagen (HTTP $httpCode)"
			);
		}
	}
	
	/**
	 * Sync SFTPGo via API
	 */
	public function syncSFTPGo() {
		if (!$this->prefs['sftpgo_enabled']) {
			return array(
				'success' => false,
				'message' => '❌ SFTPGo ist nicht aktiviert!'
			);
		}
		
		$apiUrl = $this->prefs['sftpgo_api_url'];
		$user = $this->prefs['sftpgo_admin_user'];
		$pass = $this->prefs['sftpgo_admin_pass'];
		
		// Test API connection
		$ch = curl_init($apiUrl . '/healthz');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($httpCode === 200) {
			return array(
				'success' => true,
				'message' => '✅ SFTPGo API-Verbindung erfolgreich!'
			);
		} else {
			return array(
				'success' => false,
				'message' => "❌ API-Verbindung fehlgeschlagen (HTTP $httpCode)"
			);
		}
	}
}
