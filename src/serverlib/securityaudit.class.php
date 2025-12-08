<?php
/**
 * b1gMail Security Audit Log
 * 
 * Comprehensive logging for security events, compliance, and forensics
 * 
 * Features:
 * - Structured event logging (JSON details)
 * - Severity levels (info, warning, critical)
 * - IP address and user agent tracking
 * - Session correlation
 * - GDPR/DSGVO compliant
 * - Retention policies
 * 
 * Usage:
 *   SecurityAudit::log('login', 'auth', 'success', $userId, ['method' => 'password']);
 *   SecurityAudit::logLoginAttempt($userId, $ip, true);
 *   SecurityAudit::logMFAEvent($userId, 'totp_enabled');
 * 
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class SecurityAudit
{
	// ═══════════════════════════════════════════════════════════════
	// EVENT TYPES
	// ═══════════════════════════════════════════════════════════════
	
	// Authentication events
	const EVENT_LOGIN = 'login';
	const EVENT_LOGOUT = 'logout';
	const EVENT_LOGIN_FAILED = 'login_failed';
	const EVENT_SESSION_EXPIRED = 'session_expired';
	const EVENT_PASSWORD_RESET = 'password_reset';
	const EVENT_PASSWORD_CHANGED = 'password_changed';
	
	// MFA events
	const EVENT_MFA_ENABLED = 'mfa_enabled';
	const EVENT_MFA_DISABLED = 'mfa_disabled';
	const EVENT_MFA_VERIFIED = 'mfa_verified';
	const EVENT_MFA_FAILED = 'mfa_failed';
	const EVENT_BACKUP_CODE_USED = 'backup_code_used';
	
	// WebAuthn events
	const EVENT_WEBAUTHN_REGISTERED = 'webauthn_registered';
	const EVENT_WEBAUTHN_VERIFIED = 'webauthn_verified';
	const EVENT_WEBAUTHN_FAILED = 'webauthn_failed';
	const EVENT_WEBAUTHN_REMOVED = 'webauthn_removed';
	
	// App password events
	const EVENT_APP_PASSWORD_CREATED = 'app_password_created';
	const EVENT_APP_PASSWORD_USED = 'app_password_used';
	const EVENT_APP_PASSWORD_REVOKED = 'app_password_revoked';
	
	// Admin events
	const EVENT_ADMIN_LOGIN = 'admin_login';
	const EVENT_USER_CREATED = 'user_created';
	const EVENT_USER_DELETED = 'user_deleted';
	const EVENT_USER_SUSPENDED = 'user_suspended';
	const EVENT_PERMISSIONS_CHANGED = 'permissions_changed';
	
	// System events
	const EVENT_RATE_LIMIT_BLOCKED = 'rate_limit_blocked';
	const EVENT_SUSPICIOUS_ACTIVITY = 'suspicious_activity';
	const EVENT_ENCRYPTION_ERROR = 'encryption_error';
	
	// Categories
	const CATEGORY_AUTH = 'auth';
	const CATEGORY_ADMIN = 'admin';
	const CATEGORY_USER = 'user';
	const CATEGORY_SYSTEM = 'system';
	
	// Severity
	const SEVERITY_INFO = 'info';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_CRITICAL = 'critical';
	
	// ═══════════════════════════════════════════════════════════════
	// CORE LOGGING
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Log security event
	 * 
	 * @param string $eventType Event type constant
	 * @param string $category Category constant
	 * @param string $result 'success', 'failure', or 'blocked'
	 * @param int|null $userId User ID (null for system events)
	 * @param array $details Additional event details
	 * @param string $severity Severity level
	 */
	public static function log($eventType, $category, $result, $userId = null, $details = array(), $severity = self::SEVERITY_INFO)
	{
		global $db;
		
		// Collect metadata
		$ip = self::getClientIP();
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
		$sessionId = session_id() ?: null;
		
		// Encode details as JSON
		$detailsJson = !empty($details) ? json_encode($details) : null;
		
		try {
			$db->Query('INSERT INTO {pre}security_audit_log 
			            (user_id, event_type, event_category, severity, ip_address, user_agent, session_id, details, result, timestamp)
			            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
			            $userId,
			            $eventType,
			            $category,
			            $severity,
			            $ip,
			            $userAgent,
			            $sessionId,
			            $detailsJson,
			            $result);
			
			// Also log critical events to system log
			if($severity === self::SEVERITY_CRITICAL) {
				PutLog("SECURITY: $eventType for user #$userId - $result", PRIO_WARNING, __FILE__, __LINE__);
			}
		} catch(Exception $e) {
			// Fallback to system log if DB insert fails
			PutLog("Security audit log failed: " . $e->getMessage(), PRIO_WARNING, __FILE__, __LINE__);
		}
	}
	
	// ═══════════════════════════════════════════════════════════════
	// CONVENIENT HELPERS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Log login attempt
	 * 
	 * @param int $userId User ID
	 * @param string $ip IP address
	 * @param bool $success Success status
	 * @param string $method Authentication method ('password', 'mfa', 'webauthn', 'app_password')
	 * @param string|null $failureReason Reason for failure
	 */
	public static function logLoginAttempt($userId, $ip, $success, $method = 'password', $failureReason = null)
	{
		$result = $success ? 'success' : 'failure';
		$severity = $success ? self::SEVERITY_INFO : self::SEVERITY_WARNING;
		
		$details = array(
			'method' => $method,
			'ip' => $ip
		);
		
		if(!$success && $failureReason) {
			$details['failure_reason'] = $failureReason;
		}
		
		self::log(
			$success ? self::EVENT_LOGIN : self::EVENT_LOGIN_FAILED,
			self::CATEGORY_AUTH,
			$result,
			$userId,
			$details,
			$severity
		);
	}
	
	/**
	 * Log logout
	 * 
	 * @param int $userId User ID
	 * @param bool $wasExpired Session expired (true) or manual logout (false)
	 */
	public static function logLogout($userId, $wasExpired = false)
	{
		self::log(
			$wasExpired ? self::EVENT_SESSION_EXPIRED : self::EVENT_LOGOUT,
			self::CATEGORY_AUTH,
			'success',
			$userId,
			array('expired' => $wasExpired)
		);
	}
	
	/**
	 * Log MFA event
	 * 
	 * @param int $userId User ID
	 * @param string $eventType Event type (mfa_enabled, mfa_disabled, mfa_verified, mfa_failed)
	 * @param array $details Additional details
	 */
	public static function logMFAEvent($userId, $eventType, $details = array())
	{
		$result = (strpos($eventType, 'failed') !== false) ? 'failure' : 'success';
		$severity = ($eventType === 'mfa_disabled') ? self::SEVERITY_WARNING : self::SEVERITY_INFO;
		
		self::log(
			$eventType,
			self::CATEGORY_AUTH,
			$result,
			$userId,
			$details,
			$severity
		);
	}
	
	/**
	 * Log WebAuthn event
	 * 
	 * @param int $userId User ID
	 * @param string $eventType Event type
	 * @param array $details Additional details (credential_name, aaguid, etc.)
	 */
	public static function logWebAuthnEvent($userId, $eventType, $details = array())
	{
		$result = (strpos($eventType, 'failed') !== false) ? 'failure' : 'success';
		$severity = ($eventType === self::EVENT_WEBAUTHN_REMOVED) ? self::SEVERITY_WARNING : self::SEVERITY_INFO;
		
		self::log(
			$eventType,
			self::CATEGORY_AUTH,
			$result,
			$userId,
			$details,
			$severity
		);
	}
	
	/**
	 * Log password change
	 * 
	 * @param int $userId User ID
	 * @param bool $isReset Password reset (true) or change (false)
	 * @param string|null $initiatedBy 'user', 'admin', or 'system'
	 */
	public static function logPasswordChange($userId, $isReset = false, $initiatedBy = 'user')
	{
		self::log(
			$isReset ? self::EVENT_PASSWORD_RESET : self::EVENT_PASSWORD_CHANGED,
			self::CATEGORY_AUTH,
			'success',
			$userId,
			array('initiated_by' => $initiatedBy),
			self::SEVERITY_INFO
		);
	}
	
	/**
	 * Log rate limit block
	 * 
	 * @param string $action Action that was blocked
	 * @param string $identifier IP or user_id
	 * @param int $attempts Number of attempts
	 */
	public static function logRateLimitBlock($action, $identifier, $attempts)
	{
		self::log(
			self::EVENT_RATE_LIMIT_BLOCKED,
			self::CATEGORY_SYSTEM,
			'blocked',
			null,
			array(
				'action' => $action,
				'identifier' => $identifier,
				'attempts' => $attempts
			),
			self::SEVERITY_WARNING
		);
	}
	
	/**
	 * Log suspicious activity
	 * 
	 * @param int|null $userId User ID
	 * @param string $description Description of suspicious activity
	 * @param array $details Additional details
	 */
	public static function logSuspiciousActivity($userId, $description, $details = array())
	{
		$details['description'] = $description;
		
		self::log(
			self::EVENT_SUSPICIOUS_ACTIVITY,
			self::CATEGORY_SYSTEM,
			'blocked',
			$userId,
			$details,
			self::SEVERITY_CRITICAL
		);
	}
	
	/**
	 * Log admin action
	 * 
	 * @param int $adminId Admin user ID
	 * @param string $eventType Event type
	 * @param int|null $targetUserId Target user ID (for user-related admin actions)
	 * @param array $details Additional details
	 */
	public static function logAdminAction($adminId, $eventType, $targetUserId = null, $details = array())
	{
		if($targetUserId) {
			$details['target_user_id'] = $targetUserId;
		}
		
		self::log(
			$eventType,
			self::CATEGORY_ADMIN,
			'success',
			$adminId,
			$details,
			self::SEVERITY_INFO
		);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// QUERY & ANALYTICS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get audit log for user
	 * 
	 * @param int $userId User ID
	 * @param int $limit Max results
	 * @param int $offset Offset for pagination
	 * @return array Log entries
	 */
	public static function getUserLog($userId, $limit = 50, $offset = 0)
	{
		global $db;
		
		$logs = array();
		
		$result = $db->Query('SELECT * FROM {pre}security_audit_log 
		                      WHERE user_id = ? 
		                      ORDER BY timestamp DESC 
		                      LIMIT ? OFFSET ?',
		                      $userId, $limit, $offset);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			if($row['details']) {
				$row['details'] = json_decode($row['details'], true);
			}
			$logs[] = $row;
		}
		
		return $logs;
	}
	
	/**
	 * Get recent critical events
	 * 
	 * @param int $limit Max results
	 * @return array Log entries
	 */
	public static function getCriticalEvents($limit = 100)
	{
		global $db;
		
		$logs = array();
		
		$result = $db->Query('SELECT * FROM {pre}security_audit_log 
		                      WHERE severity = ? 
		                      ORDER BY timestamp DESC 
		                      LIMIT ?',
		                      self::SEVERITY_CRITICAL, $limit);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			if($row['details']) {
				$row['details'] = json_decode($row['details'], true);
			}
			$logs[] = $row;
		}
		
		return $logs;
	}
	
	/**
	 * Get failed login attempts for IP
	 * 
	 * @param string $ip IP address
	 * @param int $hours Time window in hours
	 * @return int Number of failed attempts
	 */
	public static function getFailedLoginsForIP($ip, $hours = 24)
	{
		global $db;
		
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}security_audit_log 
		                      WHERE event_type = ? 
		                      AND ip_address = ? 
		                      AND result = ?
		                      AND timestamp > DATE_SUB(NOW(), INTERVAL ? HOUR)',
		                      self::EVENT_LOGIN_FAILED, $ip, 'failure', $hours);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return (int)$row['count'];
		}
		
		return 0;
	}
	
	/**
	 * Get statistics
	 * 
	 * @param int $days Time window in days
	 * @return array Statistics
	 */
	public static function getStats($days = 7)
	{
		global $db;
		
		$stats = array();
		
		// Total events
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}security_audit_log 
		                      WHERE timestamp > DATE_SUB(NOW(), INTERVAL ? DAY)',
		                      $days);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_events'] = (int)$row['count'];
		}
		
		// By severity
		$result = $db->Query('SELECT severity, COUNT(*) as count FROM {pre}security_audit_log 
		                      WHERE timestamp > DATE_SUB(NOW(), INTERVAL ? DAY)
		                      GROUP BY severity',
		                      $days);
		$stats['by_severity'] = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['by_severity'][$row['severity']] = (int)$row['count'];
		}
		
		// By event type (top 10)
		$result = $db->Query('SELECT event_type, COUNT(*) as count FROM {pre}security_audit_log 
		                      WHERE timestamp > DATE_SUB(NOW(), INTERVAL ? DAY)
		                      GROUP BY event_type 
		                      ORDER BY count DESC 
		                      LIMIT 10',
		                      $days);
		$stats['top_events'] = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['top_events'][$row['event_type']] = (int)$row['count'];
		}
		
		// Failed logins
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}security_audit_log 
		                      WHERE event_type = ? 
		                      AND timestamp > DATE_SUB(NOW(), INTERVAL ? DAY)',
		                      self::EVENT_LOGIN_FAILED, $days);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['failed_logins'] = (int)$row['count'];
		}
		
		return $stats;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// MAINTENANCE
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Clean up old logs (GDPR retention policy)
	 * 
	 * @param int $days Retention period in days (default: 90)
	 * @return int Number of deleted rows
	 */
	public static function cleanup($days = 90)
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}security_audit_log 
		            WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)',
		            $days);
		
		$deleted = $db->AffectedRows();
		
		if($deleted > 0) {
			PutLog("Security audit log cleanup: $deleted entries deleted (older than $days days)",
			       PRIO_NOTE, __FILE__, __LINE__);
		}
		
		return $deleted;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// HELPERS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get client IP address
	 * 
	 * @return string IP address
	 */
	private static function getClientIP()
	{
		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR'
		);
		
		foreach($headers as $header) {
			if(isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
				$ip = $_SERVER[$header];
				
				if(strpos($ip, ',') !== false) {
					$ips = explode(',', $ip);
					$ip = trim($ips[0]);
				}
				
				if(filter_var($ip, FILTER_VALIDATE_IP)) {
					return $ip;
				}
			}
		}
		
		return '0.0.0.0';
	}
}
