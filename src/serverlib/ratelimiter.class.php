<?php
/**
 * b1gMail Rate Limiter
 * 
 * Brute-force protection for login attempts and API calls
 * 
 * Features:
 * - Sliding window rate limiting
 * - IP-based and user-based limits
 * - Automatic blocking with exponential backoff
 * - MySQL-backed (no Redis required)
 * - Configurable limits per action
 * 
 * Usage:
 *   RateLimiter::check('login', $ip);
 *   RateLimiter::increment('login', $ip);
 *   RateLimiter::reset('login', $ip);
 * 
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class RateLimiter
{
	// ═══════════════════════════════════════════════════════════════
	// RATE LIMIT CONFIGURATIONS
	// ═══════════════════════════════════════════════════════════════
	
	private static $limits = array(
		'login' => array(
			'max_attempts' => 5,           // Max attempts before block
			'window_seconds' => 300,        // 5 minutes window
			'block_duration' => 1800,       // 30 minutes block
			'use_exponential' => true       // Exponential backoff
		),
		'password_reset' => array(
			'max_attempts' => 3,
			'window_seconds' => 3600,       // 1 hour
			'block_duration' => 7200,       // 2 hours
			'use_exponential' => false
		),
		'api_call' => array(
			'max_attempts' => 60,
			'window_seconds' => 60,         // 1 minute
			'block_duration' => 300,        // 5 minutes
			'use_exponential' => false
		),
		'mfa_code' => array(
			'max_attempts' => 5,
			'window_seconds' => 300,
			'block_duration' => 900,        // 15 minutes
			'use_exponential' => true
		),
		'webauthn_verify' => array(
			'max_attempts' => 10,
			'window_seconds' => 300,
			'block_duration' => 600,
			'use_exponential' => false
		)
	);
	
	// ═══════════════════════════════════════════════════════════════
	// PUBLIC API
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Check if action is rate-limited
	 * 
	 * @param string $action Action name (e.g. 'login')
	 * @param string $identifier IP address or user_id
	 * @return array ['allowed' => bool, 'remaining' => int, 'reset_in' => int, 'blocked_until' => ?string]
	 */
	public static function check($action, $identifier)
	{
		global $db;
		
		// Get limit configuration
		$config = self::getConfig($action);
		
		// Clean expired entries
		self::cleanup();
		
		// Get current state
		$result = $db->Query('SELECT * FROM {pre}rate_limit 
		                      WHERE identifier = ? AND action = ?',
		                      $identifier, $action);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			$attempts = (int)$row['attempts'];
			$blockedUntil = $row['blocked_until'];
			
			// Check if blocked
			if($blockedUntil !== null)
			{
				$blockedTimestamp = strtotime($blockedUntil);
				
				if($blockedTimestamp > time())
				{
					// Still blocked
					return array(
						'allowed' => false,
						'remaining' => 0,
						'reset_in' => $blockedTimestamp - time(),
						'blocked_until' => $blockedUntil,
						'reason' => 'rate_limited'
					);
				}
				else
				{
					// Block expired, reset
					self::reset($action, $identifier);
					
					return array(
						'allowed' => true,
						'remaining' => $config['max_attempts'],
						'reset_in' => $config['window_seconds'],
						'blocked_until' => null
					);
				}
			}
			
			// Check window
			$windowStart = time() - $config['window_seconds'];
			$firstAttempt = strtotime($row['first_attempt']);
			
			if($firstAttempt < $windowStart)
			{
				// Window expired, reset
				self::reset($action, $identifier);
				$attempts = 0;
			}
			
			// Calculate remaining attempts
			$remaining = max(0, $config['max_attempts'] - $attempts);
			
			return array(
				'allowed' => $remaining > 0,
				'remaining' => $remaining,
				'reset_in' => $config['window_seconds'] - (time() - $firstAttempt),
				'blocked_until' => null
			);
		}
		
		// No record = allowed
		return array(
			'allowed' => true,
			'remaining' => $config['max_attempts'],
			'reset_in' => $config['window_seconds'],
			'blocked_until' => null
		);
	}
	
	/**
	 * Increment attempt counter and possibly block
	 * 
	 * @param string $action Action name
	 * @param string $identifier IP or user_id
	 * @return array ['blocked' => bool, 'attempts' => int]
	 */
	public static function increment($action, $identifier)
	{
		global $db;
		
		$config = self::getConfig($action);
		
		// Upsert (insert or update)
		$result = $db->Query('SELECT * FROM {pre}rate_limit 
		                      WHERE identifier = ? AND action = ?',
		                      $identifier, $action);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			// Update existing
			$newAttempts = (int)$row['attempts'] + 1;
			
			// Check if should be blocked
			if($newAttempts >= $config['max_attempts'])
			{
				// Calculate block duration (exponential backoff)
				$blockDuration = $config['block_duration'];
				
				if($config['use_exponential'])
				{
					// Exponential: 2^(attempts - max) * base_duration
					$exponent = $newAttempts - $config['max_attempts'];
					$blockDuration = min(
						$config['block_duration'] * pow(2, $exponent),
						86400 // Max 24 hours
					);
				}
				
				$blockedUntil = date('Y-m-d H:i:s', time() + $blockDuration);
				
				$db->Query('UPDATE {pre}rate_limit 
				            SET attempts = ?, blocked_until = ?
				            WHERE identifier = ? AND action = ?',
				            $newAttempts, $blockedUntil, $identifier, $action);
				
				// Log blocking
				PutLog("Rate limit blocked: $action for $identifier ($newAttempts attempts, blocked until $blockedUntil)",
				       PRIO_WARNING, __FILE__, __LINE__);
				
				return array(
					'blocked' => true,
					'attempts' => $newAttempts,
					'blocked_until' => $blockedUntil
				);
			}
			else
			{
				// Increment attempts
				$db->Query('UPDATE {pre}rate_limit 
				            SET attempts = ?, last_attempt = NOW()
				            WHERE identifier = ? AND action = ?',
				            $newAttempts, $identifier, $action);
				
				return array(
					'blocked' => false,
					'attempts' => $newAttempts
				);
			}
		}
		else
		{
			// Insert new record
			$db->Query('INSERT INTO {pre}rate_limit 
			            (identifier, action, attempts, first_attempt, last_attempt)
			            VALUES (?, ?, 1, NOW(), NOW())',
			            $identifier, $action);
			
			return array(
				'blocked' => false,
				'attempts' => 1
			);
		}
	}
	
	/**
	 * Reset rate limit for identifier
	 * 
	 * @param string $action Action name
	 * @param string $identifier IP or user_id
	 */
	public static function reset($action, $identifier)
	{
		global $db;
		
		$db->Query('DELETE FROM {pre}rate_limit 
		            WHERE identifier = ? AND action = ?',
		            $identifier, $action);
	}
	
	/**
	 * Get block status
	 * 
	 * @param string $action Action name
	 * @param string $identifier IP or user_id
	 * @return array|null ['blocked_until' => string, 'attempts' => int] or null
	 */
	public static function getBlockStatus($action, $identifier)
	{
		global $db;
		
		$result = $db->Query('SELECT blocked_until, attempts FROM {pre}rate_limit 
		                      WHERE identifier = ? AND action = ? AND blocked_until IS NOT NULL',
		                      $identifier, $action);
		
		if($row = $result->FetchArray(MYSQLI_ASSOC))
		{
			$blockedUntil = strtotime($row['blocked_until']);
			
			if($blockedUntil > time())
			{
				return array(
					'blocked_until' => $row['blocked_until'],
					'attempts' => (int)$row['attempts'],
					'seconds_remaining' => $blockedUntil - time()
				);
			}
		}
		
		return null;
	}
	
	/**
	 * Unblock identifier (admin function)
	 * 
	 * @param string $action Action name
	 * @param string $identifier IP or user_id
	 */
	public static function unblock($action, $identifier)
	{
		global $db;
		
		$db->Query('UPDATE {pre}rate_limit 
		            SET blocked_until = NULL
		            WHERE identifier = ? AND action = ?',
		            $identifier, $action);
		
		PutLog("Rate limit unblocked: $action for $identifier (admin action)",
		       PRIO_NOTE, __FILE__, __LINE__);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// CLEANUP & MAINTENANCE
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Clean up expired entries
	 */
	public static function cleanup()
	{
		global $db;
		
		// Delete old records (older than 7 days)
		$db->Query('DELETE FROM {pre}rate_limit 
		            WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 7 DAY)
		            AND blocked_until IS NULL');
		
		// Delete expired blocks
		$db->Query('DELETE FROM {pre}rate_limit 
		            WHERE blocked_until IS NOT NULL 
		            AND blocked_until < NOW()');
	}
	
	/**
	 * Get statistics
	 * 
	 * @return array Statistics
	 */
	public static function getStats()
	{
		global $db;
		
		$stats = array();
		
		// Total active limits
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}rate_limit');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_records'] = (int)$row['count'];
		}
		
		// Currently blocked
		$result = $db->Query('SELECT COUNT(*) as count FROM {pre}rate_limit 
		                      WHERE blocked_until IS NOT NULL AND blocked_until > NOW()');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['currently_blocked'] = (int)$row['count'];
		}
		
		// By action
		$result = $db->Query('SELECT action, COUNT(*) as count FROM {pre}rate_limit 
		                      GROUP BY action');
		
		$stats['by_action'] = array();
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['by_action'][$row['action']] = (int)$row['count'];
		}
		
		return $stats;
	}
	
	/**
	 * Get list of currently blocked identifiers
	 * 
	 * @param int $limit Max results
	 * @return array Blocked identifiers
	 */
	public static function getBlocked($limit = 100)
	{
		global $db;
		
		$blocked = array();
		
		$result = $db->Query('SELECT identifier, action, attempts, blocked_until, last_attempt
		                      FROM {pre}rate_limit 
		                      WHERE blocked_until IS NOT NULL AND blocked_until > NOW()
		                      ORDER BY blocked_until DESC
		                      LIMIT ?', $limit);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$blocked[] = $row;
		}
		
		return $blocked;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// INTERNAL HELPERS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get configuration for action
	 * 
	 * @param string $action Action name
	 * @return array Configuration
	 */
	private static function getConfig($action)
	{
		if(isset(self::$limits[$action])) {
			return self::$limits[$action];
		}
		
		// Default fallback
		return array(
			'max_attempts' => 10,
			'window_seconds' => 300,
			'block_duration' => 600,
			'use_exponential' => false
		);
	}
	
	/**
	 * Get client IP address
	 * 
	 * @return string IP address
	 */
	public static function getClientIP()
	{
		// Check for proxy headers
		$headers = array(
			'HTTP_CF_CONNECTING_IP',    // Cloudflare
			'HTTP_X_FORWARDED_FOR',     // Standard proxy
			'HTTP_X_REAL_IP',           // Nginx
			'REMOTE_ADDR'               // Direct connection
		);
		
		foreach($headers as $header) {
			if(isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
				$ip = $_SERVER[$header];
				
				// Handle comma-separated list (X-Forwarded-For)
				if(strpos($ip, ',') !== false) {
					$ips = explode(',', $ip);
					$ip = trim($ips[0]);
				}
				
				// Validate IP
				if(filter_var($ip, FILTER_VALIDATE_IP)) {
					return $ip;
				}
			}
		}
		
		return '0.0.0.0';
	}
}
