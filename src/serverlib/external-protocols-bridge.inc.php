<?php
/**
 * External Protocols Bridge
 * Vereinfachte Integration für Cyrus, Postfix, Grommunio, SFTPGo
 * 
 * Diese Klasse bietet einfache Status-Abfragen für alle externen Protokoll-Server
 */

class BMExternalProtocolsBridge
{
	/**
	 * Get Cyrus IMAP Status
	 */
	public static function getCyrusStatus()
	{
		global $db;
		
		if(!defined('CYRUS_ENABLED') || !CYRUS_ENABLED) {
			return array(
				'enabled' => false,
				'message' => 'Cyrus IMAP ist nicht aktiviert'
			);
		}
		
		$status = array(
			'enabled' => true,
			'server' => CYRUS_SERVER,
			'imap_port' => CYRUS_IMAP_PORT,
			'pop3_port' => CYRUS_POP3_PORT,
			'jmap_enabled' => defined('CYRUS_JMAP_URL') && !empty(CYRUS_JMAP_URL),
			'jmap_url' => defined('CYRUS_JMAP_URL') ? CYRUS_JMAP_URL : '',
			'connection' => 'unknown'
		);
		
		// Try to connect
		try {
			$connection = @fsockopen(CYRUS_SERVER, CYRUS_IMAP_PORT, $errno, $errstr, 5);
			if($connection) {
				$status['connection'] = 'success';
				fclose($connection);
			} else {
				$status['connection'] = 'failed';
				$status['error'] = $errstr;
			}
		} catch(Exception $e) {
			$status['connection'] = 'failed';
			$status['error'] = $e->getMessage();
		}
		
		return $status;
	}
	
	/**
	 * Get Postfix SMTP Status
	 */
	public static function getPostfixStatus()
	{
		if(!defined('POSTFIX_SERVER') || !defined('SMTP_PORT')) {
			return array(
				'enabled' => false,
				'message' => 'Postfix ist nicht konfiguriert'
			);
		}
		
		$status = array(
			'enabled' => true,
			'server' => POSTFIX_SERVER,
			'smtp_port' => SMTP_PORT,
			'submission_port' => defined('SUBMISSION_PORT') ? SUBMISSION_PORT : 587,
			'smtps_port' => defined('SMTPS_PORT') ? SMTPS_PORT : 465,
			'connection' => 'unknown'
		);
		
		// Try to connect to SMTP port
		try {
			$connection = @fsockopen(POSTFIX_SERVER, SMTP_PORT, $errno, $errstr, 5);
			if($connection) {
				$status['connection'] = 'success';
				// Read SMTP banner
				$banner = fgets($connection);
				$status['banner'] = trim($banner);
				fclose($connection);
			} else {
				$status['connection'] = 'failed';
				$status['error'] = $errstr;
			}
		} catch(Exception $e) {
			$status['connection'] = 'failed';
			$status['error'] = $e->getMessage();
		}
		
		return $status;
	}
	
	/**
	 * Get Grommunio Status
	 */
	public static function getGrommunioStatus()
	{
		if(!defined('GROMMUNIO_ENABLED') || !GROMMUNIO_ENABLED) {
			return array(
				'enabled' => false,
				'message' => 'Grommunio ist nicht aktiviert'
			);
		}
		
		$status = array(
			'enabled' => true,
			'server' => GROMMUNIO_SERVER,
			'admin_api' => GROMMUNIO_ADMIN_API,
			'mapi_url' => defined('GROMMUNIO_MAPI_URL') ? GROMMUNIO_MAPI_URL : '',
			'ews_url' => defined('GROMMUNIO_EWS_URL') ? GROMMUNIO_EWS_URL : '',
			'eas_url' => defined('GROMMUNIO_EAS_URL') ? GROMMUNIO_EAS_URL : '',
			'connection' => 'unknown'
		);
		
		// Try to connect to admin API
		try {
			$ch = curl_init(GROMMUNIO_ADMIN_API . '/system/status');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 401) {
				$status['connection'] = 'success';
			} else {
				$status['connection'] = 'failed';
				$status['http_code'] = $httpCode;
			}
		} catch(Exception $e) {
			$status['connection'] = 'failed';
			$status['error'] = $e->getMessage();
		}
		
		return $status;
	}
	
	/**
	 * Get SFTPGo Status
	 */
	public static function getSFTPGoStatus()
	{
		if(!defined('SFTPGO_ENABLED') || !SFTPGO_ENABLED) {
			return array(
				'enabled' => false,
				'message' => 'SFTPGo ist nicht aktiviert'
			);
		}
		
		$status = array(
			'enabled' => true,
			'server' => SFTPGO_SERVER,
			'sftp_port' => SFTPGO_SFTP_PORT,
			'ftps_port' => defined('SFTPGO_FTPS_PORT') ? SFTPGO_FTPS_PORT : 990,
			'admin_api' => SFTPGO_ADMIN_API,
			's3_enabled' => defined('SFTPGO_S3_ENABLED') && SFTPGO_S3_ENABLED,
			's3_endpoint' => defined('SFTPGO_S3_ENDPOINT') ? SFTPGO_S3_ENDPOINT : '',
			'webdav_enabled' => defined('SFTPGO_WEBDAV_ENABLED') && SFTPGO_WEBDAV_ENABLED,
			'webdav_endpoint' => defined('SFTPGO_WEBDAV_ENDPOINT') ? SFTPGO_WEBDAV_ENDPOINT : '',
			'connection' => 'unknown'
		);
		
		// Try to connect to SFTP port
		try {
			$connection = @fsockopen(SFTPGO_SERVER, SFTPGO_SFTP_PORT, $errno, $errstr, 5);
			if($connection) {
				$status['connection'] = 'success';
				fclose($connection);
			} else {
				$status['connection'] = 'failed';
				$status['error'] = $errstr;
			}
		} catch(Exception $e) {
			$status['connection'] = 'failed';
			$status['error'] = $e->getMessage();
		}
		
		// Try Admin API
		try {
			$ch = curl_init(SFTPGO_ADMIN_API . '/healthz');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($httpCode == 200 || $httpCode == 401) {
				$status['api_connection'] = 'success';
			} else {
				$status['api_connection'] = 'failed';
				$status['api_http_code'] = $httpCode;
			}
		} catch(Exception $e) {
			$status['api_connection'] = 'failed';
			$status['api_error'] = $e->getMessage();
		}
		
		return $status;
	}
	
	/**
	 * Get all protocols status
	 */
	public static function getAllStatus()
	{
		return array(
			'cyrus' => self::getCyrusStatus(),
			'postfix' => self::getPostfixStatus(),
			'grommunio' => self::getGrommunioStatus(),
			'sftpgo' => self::getSFTPGoStatus()
		);
	}
}

?>
