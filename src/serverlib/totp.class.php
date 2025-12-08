<?php
/**
 * b1gMail TOTP/2FA Class
 * Two-Factor Authentication Implementation (RFC 6238)
 * 
 * Features:
 * - TOTP generation and verification
 * - QR Code generation for authenticator apps
 * - Backup codes
 * - Admin management (reset, disable for users)
 */

if(!defined('B1GMAIL_INIT'))
	exit;

class BMTOTP {
	const SECRET_LENGTH = 32;
	const CODE_LENGTH = 6;
	const TIME_STEP = 30; // seconds
	const WINDOW = 1; // Allow 1 step before/after
	
	/**
	 * Check if MFA is enabled for user
	 */
	public static function isMFAEnabled($userID) {
		global $db;
		
		$result = $db->Query('SELECT mfa_enabled FROM {pre}users WHERE id=?', $userID);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			return (bool)$row['mfa_enabled'];
		}
		
		return false;
	}
	
	/**
	 * Generate a new secret
	 */
	public static function generateSecret() {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32
		$secret = '';
		
		for($i = 0; $i < self::SECRET_LENGTH; $i++) {
			$secret .= $chars[random_int(0, strlen($chars) - 1)];
		}
		
		return $secret;
	}
	
	/**
	 * Verify TOTP code
	 */
	public static function verifyCode($secret, $code, $timestamp = null) {
		if($timestamp === null) {
			$timestamp = time();
		}
		
		// Check current time and ±window
		for($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
			$timeSlice = floor($timestamp / self::TIME_STEP) + $i;
			$calculatedCode = self::generateCode($secret, $timeSlice);
			
			if($calculatedCode === $code) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Generate TOTP code for time slice
	 */
	private static function generateCode($secret, $timeSlice) {
		$secretKey = self::base32Decode($secret);
		$time = pack('N*', 0, $timeSlice);
		$hash = hash_hmac('sha1', $time, $secretKey, true);
		
		$offset = ord($hash[strlen($hash) - 1]) & 0x0F;
		$code = (
			((ord($hash[$offset]) & 0x7F) << 24) |
			((ord($hash[$offset + 1]) & 0xFF) << 16) |
			((ord($hash[$offset + 2]) & 0xFF) << 8) |
			(ord($hash[$offset + 3]) & 0xFF)
		) % pow(10, self::CODE_LENGTH);
		
		return str_pad($code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Base32 decode
	 */
	private static function base32Decode($input) {
		$base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$base32charsFlipped = array_flip(str_split($base32chars));
		
		$paddingCharCount = substr_count($input, '=');
		$allowedValues = array(6, 4, 3, 1, 0);
		
		if(!in_array($paddingCharCount, $allowedValues)) {
			return false;
		}
		
		for($i = 0; $i < 4; $i++) {
			if($paddingCharCount == $allowedValues[$i] &&
			   substr($input, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) {
				return false;
			}
		}
		
		$input = str_replace('=', '', $input);
		$input = str_split($input);
		$binaryString = '';
		
		for($i = 0; $i < count($input); $i = $i + 8) {
			$x = '';
			if(!in_array($input[$i], $base32charsFlipped)) {
				return false;
			}
			for($j = 0; $j < 8; $j++) {
				$x .= str_pad(base_convert(@$base32charsFlipped[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
			}
			$eightBits = str_split($x, 8);
			for($z = 0; $z < count($eightBits); $z++) {
				$binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
			}
		}
		
		return $binaryString;
	}
	
	/**
	 * Get QR code URL for authenticator apps
	 */
	public static function getQRCodeUrl($secret, $email, $issuer = 'b1gMail') {
		$otpauthUrl = sprintf(
			'otpauth://totp/%s:%s?secret=%s&issuer=%s',
			urlencode($issuer),
			urlencode($email),
			$secret,
			urlencode($issuer)
		);
		
		// Use Google Charts API or local QR generator
		return 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($otpauthUrl);
	}
	
	/**
	 * Enable MFA for user (with encryption)
	 */
	public static function enableForUser($userID, $secret) {
		global $db;
		
		// Encrypt TOTP secret using PasswordManager
		require_once('password.class.php');
		
		try {
			$encrypted = PasswordManager::encrypt($secret, 'totp');
			
			$db->Query('UPDATE {pre}users SET 
				mfa_enabled=1, 
				mfa_secret=?,
				mfa_secret_iv=?,
				mfa_secret_tag=?,
				mfa_secret_encrypted=1,
				mfa_secret_version=?
				WHERE id=?',
				$encrypted['encrypted'],
				$encrypted['iv'],
				$encrypted['tag'],
				$encrypted['version'],
				$userID);
			
			PutLog('MFA enabled for user #' . $userID . ' (encrypted)', PRIO_NOTE, __FILE__, __LINE__);
		} catch(Exception $e) {
			PutLog('MFA enable failed for user #' . $userID . ': ' . $e->getMessage(), PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Disable MFA for user (Admin function)
	 */
	public static function disableForUser($userID) {
		global $db;
		
		$db->Query('UPDATE {pre}users SET 
			mfa_enabled=0, 
			mfa_secret=NULL,
			mfa_secret_iv=NULL,
			mfa_secret_tag=NULL,
			mfa_secret_encrypted=0,
			mfa_secret_version=NULL
			WHERE id=?',
			$userID);
		
		PutLog('MFA disabled for user #' . $userID, PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	/**
	 * Get user's MFA secret (with decryption)
	 */
	public static function getUserSecret($userID) {
		global $db;
		
		$result = $db->Query('SELECT mfa_secret, mfa_secret_iv, mfa_secret_tag, mfa_secret_encrypted, mfa_secret_version FROM {pre}users WHERE id=?', $userID);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			// Check if encrypted
			if(isset($row['mfa_secret_encrypted']) && $row['mfa_secret_encrypted'] == 1) {
				// Decrypt using PasswordManager
				require_once('password.class.php');
				
				try {
					return PasswordManager::decrypt(
						$row['mfa_secret'],
						$row['mfa_secret_iv'],
						$row['mfa_secret_tag'],
						'totp',
						$row['mfa_secret_version'] ?? PasswordManager::CRED_VERSION_AES256_GCM
					);
				} catch(Exception $e) {
					PutLog('MFA secret decryption failed for user #' . $userID . ': ' . $e->getMessage(), PRIO_WARNING, __FILE__, __LINE__);
					return null;
				}
			}
			
			// Legacy: Still plaintext (during migration)
			return $row['mfa_secret'];
		}
		
		return null;
	}
	
	/**
	 * Generate backup codes
	 */
	public static function generateBackupCodes($count = 10) {
		$codes = array();
		
		for($i = 0; $i < $count; $i++) {
			$code = '';
			for($j = 0; $j < 8; $j++) {
				$code .= random_int(0, 9);
			}
			$codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
		}
		
		return $codes;
	}
	
	/**
	 * Save backup codes for user
	 */
	public static function saveBackupCodes($userID, $codes) {
		global $db;
		
		// Hash codes before storing
		$hashedCodes = array();
		foreach($codes as $code) {
			$hashedCodes[] = password_hash($code, PASSWORD_BCRYPT);
		}
		
		$db->Query('UPDATE {pre}users SET mfa_backup_codes=? WHERE id=?',
			json_encode($hashedCodes), $userID);
	}
	
	/**
	 * Verify and consume backup code
	 */
	public static function verifyBackupCode($userID, $code) {
		global $db;
		
		$result = $db->Query('SELECT mfa_backup_codes FROM {pre}users WHERE id=?', $userID);
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$codes = json_decode($row['mfa_backup_codes'], true);
			
			if(!is_array($codes)) {
				return false;
			}
			
			foreach($codes as $index => $hashedCode) {
				if(password_verify($code, $hashedCode)) {
					// Remove used code
					unset($codes[$index]);
					$db->Query('UPDATE {pre}users SET mfa_backup_codes=? WHERE id=?',
						json_encode(array_values($codes)), $userID);
					
					PutLog('Backup code used for user #' . $userID, PRIO_NOTE, __FILE__, __LINE__);
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Get MFA statistics (for admin)
	 */
	public static function getStats() {
		global $db;
		
		$stats = array();
		
		$result = $db->Query('SELECT COUNT(*) as total FROM {pre}users WHERE gesperrt=0');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['total_users'] = $row['total'];
		}
		
		$result = $db->Query('SELECT COUNT(*) as enabled FROM {pre}users WHERE mfa_enabled=1 AND gesperrt=0');
		if($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$stats['mfa_enabled'] = $row['enabled'];
		}
		
		$stats['mfa_disabled'] = $stats['total_users'] - $stats['mfa_enabled'];
		$stats['percentage'] = $stats['total_users'] > 0 
			? round(($stats['mfa_enabled'] / $stats['total_users']) * 100, 2) 
			: 0;
		
		return $stats;
	}
	
	/**
	 * Get list of users with MFA status (for admin)
	 */
	public static function getUserList($limit = 100, $offset = 0) {
		global $db;
		
		$users = array();
		
		$result = $db->Query('SELECT id, email, mfa_enabled, registriert 
			FROM {pre}users 
			WHERE gesperrt=0 
			ORDER BY email 
			LIMIT ? OFFSET ?',
			$limit, $offset);
		
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$users[] = $row;
		}
		
		return $users;
	}
}
