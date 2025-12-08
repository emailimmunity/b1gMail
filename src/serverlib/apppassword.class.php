<?php
/**
 * b1gMail App Passwords (Device Credentials)
 * 
 * Secure authentication for legacy IMAP/POP3/SMTP clients without MFA support
 * 
 * Features:
 * - Scope-limited credentials (per protocol)
 * - IP whitelist support
 * - Expiration dates
 * - Audit trail
 * - Compatible with MFA-enabled accounts
 * 
 * Usage:
 *   $appPwd = AppPassword::create($userId, 'iPhone Mail', ['imap', 'smtp']);
 *   $valid = AppPassword::verify($userId, $password, 'imap', $ip);
 *   AppPassword::revoke($userId, $appPasswordId);
 * 
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class AppPassword
{
	// ═══════════════════════════════════════════════════════════════
	// CONSTANTS
	// ═══════════════════════════════════════════════════════════════
	
	const PASSWORD_LENGTH = 16;
	const PASSWORD_DISPLAY_FORMAT = 'xxxx-xxxx-xxxx-xxxx'; // 16 chars in 4 groups
	
	// Supported protocols
	const PROTOCOL_IMAP = 'imap';
	const PROTOCOL_POP3 = 'pop3';
	const PROTOCOL_SMTP = 'smtp';
	const PROTOCOL_WEBDAV = 'webdav';
	const PROTOCOL_CALDAV = 'caldav';
	const PROTOCOL_CARDDAV = 'carddav';
	
	// ═══════════════════════════════════════════════════════════════
	// CREATE & MANAGE
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Create new app password
	 * 
	 * @param int $userId User ID
	 * @param string $name Device name (e.g. "iPhone Mail")
	 * @param array $allowedProtocols Array of protocols or ['*'] for all
	 * @param string|null $description Optional description
	 * @param array|null $allowedIPs Array of CIDR ranges or null for no restriction
	 * @param int|null $expiresInDays Expiration in days or null for never
	 * @return array ['id' => int, 'password' => string, 'password_formatted' => string]
	 */
	public static function create($userId, $name, $allowedProtocols = ['*'], $description = null, $allowedIPs = null, $expiresInDays = null)
	{
		global $db;
		
		// Generate random password
		$password = self::generatePassword();
		$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
		
		// Calculate expiration
		$expiresAt = null;
		if($expiresInDays !== null) {
			$expiresAt = date('Y-m-d H:i:s', time() + ($expiresInDays * 86400));
		}
		
		// Encode arrays as JSON
		$allowedProtocolsJson = json_encode($allowedProtocols);
		$allowedIPsJson = $allowedIPs ? json_encode($allowedIPs) : null;
		
		// Insert into database
		$db->Query('INSERT INTO {pre}app_passwords 
		            (user_id, name, description, password_hash, allowed_protocols, allowed_ips, expires_at, created_at, is_active)
		            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)',
		            $userId, $name, $description, $passwordHash, $allowedProtocolsJson, $allowedIPsJson, $expiresAt);
		
		$appPasswordId = $db->InsertId();
		
		// Log creation
		require_once('securityaudit.class.php');
		SecurityAudit::log(
			SecurityAudit::EVENT_APP_PASSWORD_CREATED,
			SecurityAudit::CATEGORY_AUTH,
			'success',
			$userId,
			array(
				'app_password_id' => $appPasswordId,
				'name' => $name,
				'protocols' => $allowedProtocols
			)
		);
		
		PutLog("App password created for user #$userId: $name", PRIO_NOTE, __FILE__, __LINE__);
		
		return array(
			'id' => $appPasswordId,
			'password' => $password,
			'password_formatted' => self::formatPassword($password)
		);
	}
	
	/**
	 * Verify app password and check permissions
	 * 
	 * @param int $userId User ID
	 * @param string $password App password (plain)
	 * @param string $protocol Protocol being accessed
	 * @param string $ip Client IP address
	 * @return array|false ['id' => int, 'name' => string] or false
	 */
	public static function verify($userId, $password, $protocol, $ip)
	{
		global $db;
		
		// Get all active app passwords for user
		$result = $db->Query('SELECT * FROM {pre}app_passwords 
		                      WHERE user_id = ? 
		                      AND is_active = 1
		                      AND (expires_at IS NULL OR expires_at > NOW())
		                      ORDER BY created_at DESC',
		                      $userId);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			// Check password hash
			if(!password_verify($password, $row['password_hash'])) {
				continue; // Wrong password
			}
			
			// Check protocol permission
			$allowedProtocols = json_decode($row['allowed_protocols'], true);
			if(!in_array('*', $allowedProtocols) && !in_array($protocol, $allowedProtocols)) {
				continue; // Protocol not allowed
			}
			
			// Check IP whitelist
			if($row['allowed_ips'] !== null) {
				$allowedIPs = json_decode($row['allowed_ips'], true);
				if(!self::isIPAllowed($ip, $allowedIPs)) {
					// Log suspicious activity
					require_once('securityaudit.class.php');
					SecurityAudit::logSuspiciousActivity(
						$userId,
						'App password used from unauthorized IP',
						array(
							'app_password_id' => $row['id'],
							'app_password_name' => $row['name'],
							'ip' => $ip,
							'allowed_ips' => $allowedIPs
						)
					);
					
					continue; // IP not allowed
				}
			}
			
			// Valid! Update usage stats
			$db->Query('UPDATE {pre}app_passwords 
			            SET last_used_at = NOW(), use_count = use_count + 1 
			            WHERE id = ?',
			            $row['id']);
			
			// Log usage
			require_once('securityaudit.class.php');
			SecurityAudit::log(
				SecurityAudit::EVENT_APP_PASSWORD_USED,
				SecurityAudit::CATEGORY_AUTH,
				'success',
				$userId,
				array(
					'app_password_id' => $row['id'],
					'name' => $row['name'],
					'protocol' => $protocol,
					'ip' => $ip
				)
			);
			
			return array(
				'id' => (int)$row['id'],
				'name' => $row['name']
			);
		}
		
		return false; // No matching app password found
	}
	
	/**
	 * Revoke (delete) app password
	 * 
	 * @param int $userId User ID
	 * @param int $appPasswordId App password ID
	 * @return bool Success
	 */
	public static function revoke($userId, $appPasswordId)
	{
		global $db;
		
		// Get app password name for logging
		$result = $db->Query('SELECT name FROM {pre}app_passwords WHERE id = ? AND user_id = ?',
		                      $appPasswordId, $userId);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$name = $row['name'];
			
			// Delete
			$db->Query('DELETE FROM {pre}app_passwords WHERE id = ? AND user_id = ?',
			           $appPasswordId, $userId);
			
			// Log revocation
			require_once('securityaudit.class.php');
			SecurityAudit::log(
				SecurityAudit::EVENT_APP_PASSWORD_REVOKED,
				SecurityAudit::CATEGORY_AUTH,
				'success',
				$userId,
				array(
					'app_password_id' => $appPasswordId,
					'name' => $name
				),
				SecurityAudit::SEVERITY_WARNING
			);
			
			PutLog("App password revoked for user #$userId: $name", PRIO_NOTE, __FILE__, __LINE__);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Revoke all app passwords for user
	 * 
	 * @param int $userId User ID
	 * @return int Number of revoked passwords
	 */
	public static function revokeAll($userId)
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}app_passwords WHERE user_id = ?', $userId);
		$count = $db->AffectedRows();
		
		if($count > 0) {
			PutLog("All app passwords revoked for user #$userId ($count total)", PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $count;
	}
	
	/**
	 * Deactivate (soft-delete) app password
	 * 
	 * @param int $userId User ID
	 * @param int $appPasswordId App password ID
	 * @return bool Success
	 */
	public static function deactivate($userId, $appPasswordId)
	{
		global $db;
		
		$db->Query('UPDATE {pre}app_passwords SET is_active = 0 WHERE id = ? AND user_id = ?',
		           $appPasswordId, $userId);
		
		return ($db->AffectedRows() == 1);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// QUERY
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get all app passwords for user
	 * 
	 * @param int $userId User ID
	 * @param bool $activeOnly Only active passwords
	 * @return array List of app passwords
	 */
	public static function getList($userId, $activeOnly = true)
	{
		global $db;
		
		$query = 'SELECT id, name, description, allowed_protocols, allowed_ips, expires_at, created_at, last_used_at, use_count, is_active 
		          FROM {pre}app_passwords 
		          WHERE user_id = ?';
		
		if($activeOnly) {
			$query .= ' AND is_active = 1';
		}
		
		$query .= ' ORDER BY created_at DESC';
		
		$result = $db->Query($query, $userId);
		
		$passwords = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			$row['allowed_protocols'] = json_decode($row['allowed_protocols'], true);
			$row['allowed_ips'] = $row['allowed_ips'] ? json_decode($row['allowed_ips'], true) : null;
			$row['is_expired'] = ($row['expires_at'] && strtotime($row['expires_at']) < time());
			
			$passwords[] = $row;
		}
		
		return $passwords;
	}
	
	/**
	 * Get app password details
	 * 
	 * @param int $appPasswordId App password ID
	 * @param int $userId User ID
	 * @return array|null App password details
	 */
	public static function getDetails($appPasswordId, $userId)
	{
		global $db;
		
		$result = $db->Query('SELECT * FROM {pre}app_passwords WHERE id = ? AND user_id = ?',
		                      $appPasswordId, $userId);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$row['allowed_protocols'] = json_decode($row['allowed_protocols'], true);
			$row['allowed_ips'] = $row['allowed_ips'] ? json_decode($row['allowed_ips'], true) : null;
			$row['is_expired'] = ($row['expires_at'] && strtotime($row['expires_at']) < time());
			
			return $row;
		}
		
		return null;
	}
	
	/**
	 * Count app passwords for user
	 * 
	 * @param int $userId User ID
	 * @return int Count
	 */
	public static function count($userId)
	{
		global $db;
		
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE user_id = ? AND is_active = 1',
		                      $userId);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return (int)$row['count'];
		}
		
		return 0;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// HELPERS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Generate random app password
	 * 
	 * @return string 16-character password (alphanumeric)
	 */
	private static function generatePassword()
	{
		// Use only alphanumeric (avoid confusing characters like 0/O, 1/l)
		$chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
		$password = '';
		
		for($i = 0; $i < self::PASSWORD_LENGTH; $i++) {
			$password .= $chars[random_int(0, strlen($chars) - 1)];
		}
		
		return $password;
	}
	
	/**
	 * Format password for display (xxxx-xxxx-xxxx-xxxx)
	 * 
	 * @param string $password Raw password
	 * @return string Formatted password
	 */
	private static function formatPassword($password)
	{
		return implode('-', str_split($password, 4));
	}
	
	/**
	 * Check if IP is in allowed list (CIDR support)
	 * 
	 * @param string $ip IP address to check
	 * @param array $allowedIPs Array of IPs or CIDR ranges
	 * @return bool IP is allowed
	 */
	private static function isIPAllowed($ip, $allowedIPs)
	{
		if(empty($allowedIPs)) {
			return true; // No restriction
		}
		
		foreach($allowedIPs as $allowed)
		{
			// Check for CIDR notation
			if(strpos($allowed, '/') !== false)
			{
				// CIDR range
				list($subnet, $bits) = explode('/', $allowed);
				
				$ip_long = ip2long($ip);
				$subnet_long = ip2long($subnet);
				$mask = -1 << (32 - $bits);
				$subnet_long &= $mask;
				
				if(($ip_long & $mask) == $subnet_long) {
					return true;
				}
			}
			else
			{
				// Exact IP match
				if($ip === $allowed) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Get all supported protocols
	 * 
	 * @return array List of protocols
	 */
	public static function getSupportedProtocols()
	{
		return array(
			self::PROTOCOL_IMAP,
			self::PROTOCOL_POP3,
			self::PROTOCOL_SMTP,
			self::PROTOCOL_WEBDAV,
			self::PROTOCOL_CALDAV,
			self::PROTOCOL_CARDDAV
		);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// STATISTICS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get statistics (for admin)
	 * 
	 * @return array Statistics
	 */
	public static function getStats()
	{
		global $db;
		
		$stats = array();
		
		// Total active
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords WHERE is_active = 1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_active'] = (int)$row['count'];
		}
		
		// Users with app passwords
		$result = $db->Query('SELECT COUNT(DISTINCT user_id) as count FROM {pre}app_passwords WHERE is_active = 1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['users_with_app_passwords'] = (int)$row['count'];
		}
		
		// Recently used (last 7 days)
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE last_used_at > DATE_SUB(NOW(), INTERVAL 7 DAY)');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['used_last_7_days'] = (int)$row['count'];
		}
		
		// Expired
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE expires_at IS NOT NULL AND expires_at < NOW() AND is_active = 1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['expired'] = (int)$row['count'];
		}
		
		return $stats;
	}
	
	/**
	 * Clean up expired app passwords
	 * 
	 * @return int Number of deleted passwords
	 */
	public static function cleanupExpired()
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}app_passwords 
		            WHERE expires_at IS NOT NULL AND expires_at < NOW()');
		
		$deleted = $db->AffectedRows();
		
		if($deleted > 0) {
			PutLog("Expired app passwords cleaned up: $deleted deleted", PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $deleted;
	}
}
