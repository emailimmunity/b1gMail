<?php
/**
 * b1gMail App-Specific Passwords (Device Credentials)
 * 
 * Secure authentication for legacy IMAP/POP3/SMTP/WebDAV/CalDAV/CardDAV clients
 * without modern authentication support (like Google/Apple App Passwords)
 * 
 * Features:
 * - Scope-limited credentials (per protocol)
 * - IP whitelist support (CIDR)
 * - Expiration dates
 * - Usage tracking and audit trail
 * - Compatible with MFA-enabled accounts
 * - Auto-generation for 2FA users
 * 
 * Usage Examples:
 * ```php
 * // Create app password
 * $result = BMAppPasswords::generate($userId, 'iPhone Mail', ['imap', 'smtp']);
 * echo "Password: " . $result['password_formatted']; // xxxx-xxxx-xxxx-xxxx
 * 
 * // Verify for protocol access
 * $userID = BMAppPasswords::verify($email, $password, 'imap', $clientIP);
 * 
 * // Get user's app passwords
 * $list = BMAppPasswords::getList($userId);
 * 
 * // Revoke app password
 * BMAppPasswords::revoke($userId, $appPasswordId);
 * ```
 * 
 * @version 2.0
 * @date 2025-12-01
 */

if(!defined('B1GMAIL_INIT'))
	exit('Directly calling this file is not supported');

class BMAppPasswords
{
	// ═══════════════════════════════════════════════════════════════
	// CONSTANTS
	// ═══════════════════════════════════════════════════════════════
	
	const PASSWORD_LENGTH = 16;
	const PASSWORD_DISPLAY_FORMAT = 'xxxx-xxxx-xxxx-xxxx';
	const MAX_PASSWORDS_PER_USER = 20;
	
	// Supported protocols
	const PROTOCOL_IMAP = 'imap';
	const PROTOCOL_POP3 = 'pop3';
	const PROTOCOL_SMTP = 'smtp';
	const PROTOCOL_WEBDAV = 'webdav';
	const PROTOCOL_CALDAV = 'caldav';
	const PROTOCOL_CARDDAV = 'carddav';
	const PROTOCOL_JMAP = 'jmap';
	const PROTOCOL_EWS = 'ews';
	const PROTOCOL_EAS = 'eas';
	
	// ═══════════════════════════════════════════════════════════════
	// CREATE & MANAGE
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Generate new app password
	 * 
	 * @param int $userID User ID
	 * @param string $name Device/application name (e.g. "iPhone Mail", "Thunderbird Laptop")
	 * @param array $allowedProtocols Array of protocols or empty for all
	 * @param string|null $description Optional description
	 * @param array|null $allowedIPs Array of CIDR ranges or null for no restriction
	 * @param int|null $expiresInDays Expiration in days or null for never
	 * @return array ['id' => int, 'password' => string, 'password_formatted' => string, 'error' => string|null]
	 */
	public static function generate($userID, $name, $allowedProtocols = array(), $description = null, $allowedIPs = null, $expiresInDays = null)
	{
		global $db, $bm_prefs;
		
		// Check limit
		$result = $db->Query('SELECT COUNT(*) as cnt FROM {pre}app_passwords WHERE user_id=? AND is_active=1', $userID);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$maxPasswords = isset($bm_prefs['app_passwords_max_per_user']) ? (int)$bm_prefs['app_passwords_max_per_user'] : self::MAX_PASSWORDS_PER_USER;
			if($row['cnt'] >= $maxPasswords) {
				return array('error' => 'Maximum number of app passwords reached (' . $maxPasswords . ')');
			}
		}
		
		// Generate random password
		$password = self::generatePassword();
		$passwordHash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 10));
		
		// Calculate expiration
		$expiresAt = null;
		if($expiresInDays !== null && $expiresInDays > 0) {
			$expiresAt = time() + ($expiresInDays * 86400);
		}
		
		// Encode arrays as JSON
		$allowedProtocolsJson = !empty($allowedProtocols) ? json_encode($allowedProtocols) : null;
		$allowedIPsJson = $allowedIPs ? json_encode($allowedIPs) : null;
		
		// Insert into database
		$db->Query('INSERT INTO {pre}app_passwords 
		            (user_id, name, description, password_hash, allowed_protocols, allowed_ips, created_at, expires_at, is_active)
		            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)',
		            $userID, $name, $description, $passwordHash, $allowedProtocolsJson, $allowedIPsJson, time(), $expiresAt);
		
		$appPasswordId = $db->InsertId();
		
		// Log creation
		PutLog(sprintf('App password created for user #%d: %s (protocols: %s)',
		              $userID, $name, !empty($allowedProtocols) ? implode(',', $allowedProtocols) : 'all'),
		       PRIO_NOTE, __FILE__, __LINE__);
		
		return array(
			'id' => $appPasswordId,
			'password' => $password,
			'password_formatted' => self::formatPassword($password),
			'error' => null
		);
	}
	
	/**
	 * Verify app password and check permissions
	 * 
	 * @param string $email User email address
	 * @param string $password App password (plain, with or without dashes)
	 * @param string $protocol Protocol being accessed (imap, pop3, smtp, etc)
	 * @param string|null $ip Client IP address (optional, for IP whitelist check)
	 * @return int|false User ID if valid, false if invalid
	 */
	public static function verify($email, $password, $protocol, $ip = null)
	{
		global $db;
		
		// Get user ID
		$result = $db->Query('SELECT id FROM {pre}users WHERE email=? AND gesperrt=0', $email);
		if(!($row = $result->FetchArray(MYSQLI_ASSOC))) {
			return false;
		}
		$userID = $row['id'];
		
		// Remove formatting from password (user might paste with dashes)
		$password = str_replace(array('-', ' '), '', $password);
		
		// Get all active app passwords for this user
		$result = $db->Query('SELECT * FROM {pre}app_passwords 
		                      WHERE user_id=? 
		                      AND is_active=1
		                      AND (expires_at IS NULL OR expires_at > ?)
		                      ORDER BY created_at DESC',
		                      $userID, time());
		
		while($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			// Check password hash
			if(!password_verify($password, $row['password_hash'])) {
				continue; // Wrong password
			}
			
			// Check protocol permission
			if($row['allowed_protocols'] !== null) {
				$allowedProtocols = json_decode($row['allowed_protocols'], true);
				if(is_array($allowedProtocols) && !empty($allowedProtocols) && !in_array($protocol, $allowedProtocols)) {
					PutLog(sprintf('App password protocol mismatch for user #%d: tried %s, allowed: %s',
					              $userID, $protocol, implode(',', $allowedProtocols)),
					       PRIO_WARNING, __FILE__, __LINE__);
					continue; // Protocol not allowed
				}
			}
			
			// Check IP whitelist
			if($ip && $row['allowed_ips'] !== null) {
				$allowedIPs = json_decode($row['allowed_ips'], true);
				if(!self::isIPAllowed($ip, $allowedIPs)) {
					PutLog(sprintf('App password IP mismatch for user #%d: %s not in whitelist',
					              $userID, $ip),
					       PRIO_WARNING, __FILE__, __LINE__);
					continue; // IP not allowed
				}
			}
			
			// Valid! Update usage stats
			$db->Query('UPDATE {pre}app_passwords 
			            SET last_used_at=?, last_used_ip=?, use_count=use_count+1 
			            WHERE id=?',
			            time(), $ip, $row['id']);
			
			PutLog(sprintf('App password used for user #%d (%s): %s via %s from %s',
			              $userID, $email, $row['name'], $protocol, $ip),
			       PRIO_NOTE, __FILE__, __LINE__);
			
			return $userID;
		}
		
		return false; // No matching app password found
	}
	
	/**
	 * Revoke (delete) app password
	 * 
	 * @param int $userID User ID
	 * @param int $appPasswordId App password ID
	 * @return bool Success
	 */
	public static function revoke($userID, $appPasswordId)
	{
		global $db;
		
		// Get app password name for logging
		$result = $db->Query('SELECT name FROM {pre}app_passwords WHERE id=? AND user_id=?',
		                      $appPasswordId, $userID);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$name = $row['name'];
			
			// Soft delete (mark as inactive)
			$db->Query('UPDATE {pre}app_passwords 
			            SET is_active=0, revoked_at=? 
			            WHERE id=? AND user_id=?',
			            time(), $appPasswordId, $userID);
			
			PutLog(sprintf('App password revoked for user #%d: %s', $userID, $name),
			       PRIO_NOTE, __FILE__, __LINE__);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Hard delete app password (permanent)
	 * 
	 * @param int $userID User ID
	 * @param int $appPasswordId App password ID
	 * @return bool Success
	 */
	public static function delete($userID, $appPasswordId)
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}app_passwords WHERE id=? AND user_id=?',
		           $appPasswordId, $userID);
		
		return ($db->AffectedRows() == 1);
	}
	
	/**
	 * Revoke all app passwords for user
	 * 
	 * @param int $userID User ID
	 * @return int Number of revoked passwords
	 */
	public static function revokeAll($userID)
	{
		global $db;
		
		$db->Query('UPDATE {pre}app_passwords 
		            SET is_active=0, revoked_at=? 
		            WHERE user_id=? AND is_active=1',
		            time(), $userID);
		
		$count = $db->AffectedRows();
		
		if($count > 0) {
			PutLog(sprintf('All app passwords revoked for user #%d (%d total)', $userID, $count),
			       PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $count;
	}
	
	/**
	 * Delete all app passwords for user (permanent)
	 * Admin function
	 * 
	 * @param int $userID User ID
	 * @return int Number of deleted passwords
	 */
	public static function deleteAllForUser($userID)
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}app_passwords WHERE user_id=?', $userID);
		
		$count = $db->AffectedRows();
		
		if($count > 0) {
			PutLog(sprintf('All app passwords deleted for user #%d (%d total)', $userID, $count),
			       PRIO_WARNING, __FILE__, __LINE__);
		}
		
		return $count;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// QUERY
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get list of app passwords for user
	 * 
	 * @param int $userID User ID
	 * @param bool $activeOnly Only active passwords (default: true)
	 * @return array List of app passwords
	 */
	public static function getList($userID, $activeOnly = true)
	{
		global $db;
		
		$query = 'SELECT id, name, description, allowed_protocols, allowed_ips, 
		                 created_at, expires_at, last_used_at, last_used_ip, use_count, 
		                 is_active, device_fingerprint, auto_generated 
		          FROM {pre}app_passwords 
		          WHERE user_id=?';
		
		if($activeOnly) {
			$query .= ' AND is_active=1';
		}
		
		$query .= ' ORDER BY created_at DESC';
		
		$result = $db->Query($query, $userID);
		
		$passwords = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			// Decode JSON fields
			$row['allowed_protocols'] = $row['allowed_protocols'] ? json_decode($row['allowed_protocols'], true) : null;
			$row['allowed_ips'] = $row['allowed_ips'] ? json_decode($row['allowed_ips'], true) : null;
			
			// Add formatted dates
			$row['created_formatted'] = date('Y-m-d H:i:s', $row['created_at']);
			$row['last_used_formatted'] = $row['last_used_at'] > 0 ? date('Y-m-d H:i:s', $row['last_used_at']) : 'Never';
			$row['expires_formatted'] = $row['expires_at'] ? date('Y-m-d H:i:s', $row['expires_at']) : 'Never';
			
			// Check if expired
			$row['is_expired'] = ($row['expires_at'] && $row['expires_at'] < time());
			
			$passwords[] = $row;
		}
		
		return $passwords;
	}
	
	/**
	 * Get app password details
	 * 
	 * @param int $appPasswordId App password ID
	 * @param int $userID User ID
	 * @return array|null App password details or null if not found
	 */
	public static function getDetails($appPasswordId, $userID)
	{
		global $db;
		
		$result = $db->Query('SELECT * FROM {pre}app_passwords WHERE id=? AND user_id=?',
		                      $appPasswordId, $userID);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$row['allowed_protocols'] = $row['allowed_protocols'] ? json_decode($row['allowed_protocols'], true) : null;
			$row['allowed_ips'] = $row['allowed_ips'] ? json_decode($row['allowed_ips'], true) : null;
			$row['is_expired'] = ($row['expires_at'] && $row['expires_at'] < time());
			
			return $row;
		}
		
		return null;
	}
	
	/**
	 * Count active app passwords for user
	 * 
	 * @param int $userID User ID
	 * @return int Count
	 */
	public static function count($userID)
	{
		global $db;
		
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE user_id=? AND is_active=1',
		                      $userID);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return (int)$row['count'];
		}
		
		return 0;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// AUTHENTICATION
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Check if app passwords are enforced for a user
	 * (When enforced, main password won't work for IMAP/POP3/SMTP)
	 * 
	 * @param int $userID User ID
	 * @return bool True if enforced
	 */
	public static function isEnforced($userID)
	{
		global $db;
		
		$result = $db->Query('SELECT app_passwords_enforced FROM {pre}users WHERE id=? LIMIT 1', $userID);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return isset($row['app_passwords_enforced']) && $row['app_passwords_enforced'] == 1;
		}
		return false;
	}
	
	/**
	 * Authenticate for a specific protocol
	 * Checks if app passwords are enforced, and if so, only allows app passwords
	 * Otherwise tries app password first, then main password
	 * 
	 * @param string $email Email address
	 * @param string $password Password (main or app password)
	 * @param string $protocol Protocol name (imap, pop3, smtp, webdav, etc)
	 * @param string|null $ip Client IP address
	 * @return array|false User row if success, false if failed
	 */
	public static function authenticateProtocol($email, $password, $protocol, $ip = null)
	{
		global $db;
		
		// Get user ID and user row
		$result = $db->Query('SELECT * FROM {pre}users WHERE email=? AND gesperrt=0 LIMIT 1', $email);
		$userRow = $result->FetchArray(MYSQLI_ASSOC);
		if(!$userRow) {
			return false;
		}
		$userID = $userRow['id'];
		
		// Check if app passwords are enforced
		$enforced = isset($userRow['app_passwords_enforced']) && $userRow['app_passwords_enforced'] == 1;
		
		if($enforced) {
			// App passwords ENFORCED - ONLY allow app passwords
			$appPasswordAuth = self::verify($email, $password, $protocol, $ip);
			if($appPasswordAuth) {
				PutLog(sprintf('Protocol auth <%s> via APP-PASSWORD for %s (enforced mode)', $email, $protocol),
				       PRIO_NOTE, __FILE__, __LINE__);
				return $userRow;
			} else {
				PutLog(sprintf('Protocol auth <%s> REJECTED for %s (app-password enforced, but invalid)', $email, $protocol),
				       PRIO_WARNING, __FILE__, __LINE__);
				return false;
			}
		} else {
			// App passwords NOT enforced - try app password first, then main password
			$appPasswordAuth = self::verify($email, $password, $protocol, $ip);
			if($appPasswordAuth) {
				PutLog(sprintf('Protocol auth <%s> via APP-PASSWORD for %s', $email, $protocol),
				       PRIO_NOTE, __FILE__, __LINE__);
				return $userRow;
			}
			
			// App password failed - try main password
			require_once(B1GMAIL_DIR . 'serverlib/password.class.php');
			
			$passwordVersion = isset($userRow['password_version']) ? (int)$userRow['password_version'] : 1;
			$passwordPlain = $password;
			
			$passwordValid = PasswordManager::verify(
				$passwordPlain,
				$userRow['passwort'],
				$passwordVersion,
				$userRow['passwort_salt']
			);
			
			if($passwordValid) {
				PutLog(sprintf('Protocol auth <%s> via MAIN-PASSWORD for %s', $email, $protocol),
				       PRIO_NOTE, __FILE__, __LINE__);
				return $userRow;
			}
			
			// Both failed
			return false;
		}
	}
	
	// ═══════════════════════════════════════════════════════════════
	// HELPERS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Generate secure random app password
	 * 
	 * @return string 16-character password (alphanumeric, no confusing characters)
	 */
	private static function generatePassword()
	{
		// Avoid confusing characters: 0/O, 1/l/I
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
	 * Check if IP is in allowed list (supports CIDR ranges)
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
	 * @return array List of protocol constants
	 */
	public static function getSupportedProtocols()
	{
		return array(
			self::PROTOCOL_IMAP,
			self::PROTOCOL_POP3,
			self::PROTOCOL_SMTP,
			self::PROTOCOL_WEBDAV,
			self::PROTOCOL_CALDAV,
			self::PROTOCOL_CARDDAV,
			self::PROTOCOL_JMAP,
			self::PROTOCOL_EWS,
			self::PROTOCOL_EAS
		);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// STATISTICS (Admin)
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get statistics for admin
	 * 
	 * @return array Statistics
	 */
	public static function getStats()
	{
		global $db;
		
		$stats = array();
		
		// Total active
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords WHERE is_active=1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_active'] = (int)$row['count'];
		}
		
		// Total all (including revoked)
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_all'] = (int)$row['count'];
		}
		
		// Users with app passwords
		$result = $db->Query('SELECT COUNT(DISTINCT user_id) as count FROM {pre}app_passwords WHERE is_active=1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['users_with_passwords'] = (int)$row['count'];
		}
		
		// Recently used (last 30 days)
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE last_used_at > ? AND is_active=1',
		                      time() - 30*86400);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['used_last_30_days'] = (int)$row['count'];
		}
		
		// Expired but still active (should be cleaned up)
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords 
		                      WHERE expires_at IS NOT NULL AND expires_at < ? AND is_active=1',
		                      time());
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['expired'] = (int)$row['count'];
		}
		
		// Auto-generated
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}app_passwords WHERE auto_generated=1');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['auto_generated'] = (int)$row['count'];
		}
		
		return $stats;
	}
	
	/**
	 * Get all app passwords for all users (Admin function)
	 * 
	 * @param int $limit Limit
	 * @param int $offset Offset
	 * @return array List of app passwords
	 */
	public static function getAllPasswords($limit = 100, $offset = 0)
	{
		global $db;
		
		$passwords = array();
		
		$result = $db->Query('SELECT ap.*, u.email 
		                      FROM {pre}app_passwords ap
		                      LEFT JOIN {pre}users u ON ap.user_id = u.id
		                      ORDER BY ap.created_at DESC
		                      LIMIT ? OFFSET ?',
		                      $limit, $offset);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$row['allowed_protocols'] = $row['allowed_protocols'] ? json_decode($row['allowed_protocols'], true) : null;
			$row['allowed_ips'] = $row['allowed_ips'] ? json_decode($row['allowed_ips'], true) : null;
			$row['created_formatted'] = date('Y-m-d H:i:s', $row['created_at']);
			$row['last_used_formatted'] = $row['last_used_at'] > 0 ? date('Y-m-d H:i:s', $row['last_used_at']) : 'Never';
			$passwords[] = $row;
		}
		
		return $passwords;
	}
	
	/**
	 * Clean up expired app passwords
	 * 
	 * @return int Number of cleaned passwords
	 */
	public static function cleanupExpired()
	{
		global $db;
		
		// Deactivate expired passwords
		$db->Query('UPDATE {pre}app_passwords 
		            SET is_active=0, revoked_at=? 
		            WHERE expires_at IS NOT NULL AND expires_at < ? AND is_active=1',
		            time(), time());
		
		$count = $db->AffectedRows();
		
		if($count > 0) {
			PutLog(sprintf('Expired app passwords cleaned up: %d deactivated', $count),
			       PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $count;
	}
	
	/**
	 * Create table (for installation)
	 * 
	 * @return bool Success
	 */
	public static function createTable()
	{
		global $db;
		
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}app_passwords (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			name VARCHAR(255) NOT NULL,
			description VARCHAR(512) DEFAULT NULL,
			password_hash VARCHAR(255) NOT NULL,
			allowed_protocols TEXT DEFAULT NULL,
			allowed_ips TEXT DEFAULT NULL,
			created_at INT NOT NULL,
			expires_at INT DEFAULT NULL,
			last_used_at INT DEFAULT 0,
			last_used_ip VARCHAR(45) DEFAULT NULL,
			use_count INT DEFAULT 0,
			is_active TINYINT(1) DEFAULT 1,
			revoked_at INT DEFAULT NULL,
			device_fingerprint VARCHAR(64) DEFAULT NULL,
			auto_generated TINYINT(1) DEFAULT 0,
			INDEX idx_user_id (user_id),
			INDEX idx_is_active (is_active),
			INDEX idx_expires_at (expires_at),
			INDEX idx_last_used_at (last_used_at),
			INDEX idx_device_fingerprint (device_fingerprint)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
		
		return true;
	}
}
