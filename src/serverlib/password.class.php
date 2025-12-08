<?php
/**
 * b1gMail Password Manager - Enterprise Edition
 * 
 * Universal Password & Credentials Management
 * - User Passwords: bcrypt/Argon2ID (hashing)
 * - Credentials: AES-256-GCM (encryption)
 * - TKÜV-compliant (Master-Key-Escrowed)
 * - Multi-Tenant ready
 * 
 * (c) 2025 b1gMail Project
 * 
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class PasswordManager
{
	// ═══════════════════════════════════════════════════════════════
	// CONSTANTS
	// ═══════════════════════════════════════════════════════════════
	
	// User-Password Versions (Hashing)
	const VERSION_MD5 = 1;          // Legacy (deprecated)
	const VERSION_BCRYPT = 2;       // Current standard
	const VERSION_ARGON2ID = 3;     // Future (PHP >= 7.2)
	
	// Credentials-Encryption Versions
	const CRED_VERSION_PLAINTEXT = 0;   // Legacy (deprecated)
	const CRED_VERSION_AES256_GCM = 1;  // Current standard
	
	// bcrypt Cost (12 = ~300ms, balance between security and UX)
	const BCRYPT_COST = 12;
	
	// Argon2ID Parameters
	const ARGON2_MEMORY = 65536;    // 64 MB
	const ARGON2_TIME = 4;          // 4 iterations
	const ARGON2_THREADS = 2;       // 2 parallel threads
	
	// Private static cache
	private static $masterKey = null;
	private static $stats = array(
		'migrations' => 0,
		'encryptions' => 0,
		'decryptions' => 0
	);
	
	// ═══════════════════════════════════════════════════════════════
	// USER PASSWORDS (HASHING)
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Hash a password with modern algorithm
	 * 
	 * @param string $password Plaintext password
	 * @param string $algorithm 'bcrypt' or 'argon2id'
	 * @return array ['hash' => string, 'version' => int]
	 */
	public static function hash($password, $algorithm = 'bcrypt')
	{
		if($algorithm === 'bcrypt')
		{
			$hash = password_hash($password, PASSWORD_BCRYPT, [
				'cost' => self::BCRYPT_COST
			]);
			
			if($hash === false)
			{
				throw new Exception('bcrypt hashing failed');
			}
			
			return array(
				'hash' => $hash,
				'version' => self::VERSION_BCRYPT
			);
		}
		
		if($algorithm === 'argon2id' && PHP_VERSION_ID >= 70200)
		{
			$hash = password_hash($password, PASSWORD_ARGON2ID, [
				'memory_cost' => self::ARGON2_MEMORY,
				'time_cost' => self::ARGON2_TIME,
				'threads' => self::ARGON2_THREADS
			]);
			
			if($hash === false)
			{
				throw new Exception('Argon2ID hashing failed');
			}
			
			return array(
				'hash' => $hash,
				'version' => self::VERSION_ARGON2ID
			);
		}
		
		// Fallback to bcrypt
		if($algorithm === 'argon2id')
		{
			PutLog('Argon2ID not available (PHP < 7.2), falling back to bcrypt', 
			       PRIO_WARNING, __FILE__, __LINE__);
			return self::hash($password, 'bcrypt');
		}
		
		throw new Exception('Unsupported hashing algorithm: ' . $algorithm);
	}
	
	/**
	 * Verify password (version-aware, supports legacy MD5!)
	 * 
	 * @param string $password Plaintext password
	 * @param string $hash Stored hash from database
	 * @param int $version Hash version (1=MD5, 2=bcrypt, 3=Argon2ID)
	 * @param string $salt Salt (only for MD5)
	 * @return bool Password is valid
	 */
	public static function verify($password, $hash, $version, $salt = null)
	{
		// Legacy: MD5 (for backwards compatibility)
		if($version == self::VERSION_MD5)
		{
			if(self::looksLikeMD5Hash($password))
			{
				// Password is already MD5-hashed (e.g. from old API)
				$calculated = md5($password . $salt);
			}
			else
			{
				// Password is plaintext → double-hash
				$calculated = md5(md5($password) . $salt);
			}
			
			return strtolower($hash) === strtolower($calculated);
		}
		
		// Modern: bcrypt or Argon2ID
		if($version == self::VERSION_BCRYPT || $version == self::VERSION_ARGON2ID)
		{
			return password_verify($password, $hash);
		}
		
		// Unknown version
		PutLog('Unknown password version: ' . $version, PRIO_WARNING, __FILE__, __LINE__);
		return false;
	}
	
	/**
	 * Check if password needs rehashing
	 * 
	 * @param string $hash Current hash
	 * @param int $version Current version
	 * @return bool True if rehash recommended
	 */
	public static function needsRehash($hash, $version)
	{
		// Always rehash MD5
		if($version == self::VERSION_MD5)
		{
			return true;
		}
		
		// Check if bcrypt cost changed
		if($version == self::VERSION_BCRYPT)
		{
			return password_needs_rehash($hash, PASSWORD_BCRYPT, [
				'cost' => self::BCRYPT_COST
			]);
		}
		
		// Check if Argon2ID parameters changed
		if($version == self::VERSION_ARGON2ID)
		{
			return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
				'memory_cost' => self::ARGON2_MEMORY,
				'time_cost' => self::ARGON2_TIME,
				'threads' => self::ARGON2_THREADS
			]);
		}
		
		return false;
	}
	
	/**
	 * Migrate user password from MD5 to bcrypt
	 * 
	 * Called automatically after successful login!
	 * 
	 * @param int $userid User ID
	 * @param string $password Plaintext password (from successful login)
	 * @return bool Success
	 */
	public static function migrate($userid, $password)
	{
		global $db, $bm_prefs;
		
		// Check if migration is enabled
		if(!isset($bm_prefs['password_v2_enabled']) || !$bm_prefs['password_v2_enabled'])
		{
			return false;
		}
		
		$startTime = microtime(true);
		
		try
		{
			// Create new hash
			$algorithm = $bm_prefs['password_algorithm'] ?? 'bcrypt';
			$result = self::hash($password, $algorithm);
			
			// Update database
			$db->Query('UPDATE {pre}users 
			            SET passwort = ?,
			                password_version = ?,
			                password_migrated_at = NOW(),
			                passwort_salt = NULL
			            WHERE id = ?',
			            $result['hash'],
			            $result['version'],
			            $userid);
			
			$duration = (microtime(true) - $startTime) * 1000; // milliseconds
			
			// Update statistics
			self::$stats['migrations']++;
			self::logMigration($userid, true, $duration);
			
			// Log success
			PutLog(sprintf('Password migrated for user #%d (version %d, %dms)',
			              $userid, $result['version'], $duration),
			       PRIO_NOTE,
			       __FILE__,
			       __LINE__);
			
			return true;
		}
		catch(Exception $e)
		{
			// Log error
			self::logMigrationError($userid, $e->getMessage());
			
			PutLog(sprintf('Password migration failed for user #%d: %s',
			              $userid, $e->getMessage()),
			       PRIO_WARNING,
			       __FILE__,
			       __LINE__);
			
			return false;
		}
	}
	
	// ═══════════════════════════════════════════════════════════════
	// CREDENTIALS ENCRYPTION (AES-256-GCM)
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Load Master Key (lazy loading with priority chain)
	 * 
	 * Priority:
	 * 1. Environment Variable (Docker/Kubernetes)
	 * 2. File (/etc/b1gmail/master.key)
	 * 3. Config constant (B1GMAIL_MASTER_KEY)
	 * 
	 * @return string 32-byte master key
	 */
	private static function loadMasterKey()
	{
		// Already loaded?
		if(self::$masterKey !== null)
		{
			return self::$masterKey;
		}
		
		// Priority 1: Environment Variable
		if(getenv('B1GMAIL_MASTER_KEY'))
		{
			self::$masterKey = base64_decode(getenv('B1GMAIL_MASTER_KEY'));
			
			if(strlen(self::$masterKey) === 32)
			{
				return self::$masterKey;
			}
		}
		
		// Priority 2: File
		$keyFile = '/etc/b1gmail/master.key';
		if(file_exists($keyFile))
		{
			$encoded = trim(file_get_contents($keyFile));
			self::$masterKey = base64_decode($encoded);
			
			if(strlen(self::$masterKey) === 32)
			{
				return self::$masterKey;
			}
		}
		
		// Priority 3: Config constant
		if(defined('B1GMAIL_MASTER_KEY'))
		{
			self::$masterKey = base64_decode(B1GMAIL_MASTER_KEY);
			
			if(strlen(self::$masterKey) === 32)
			{
				return self::$masterKey;
			}
		}
		
		// No key found or invalid length
		throw new Exception('Master key not found or invalid! Please set B1GMAIL_MASTER_KEY environment variable or create /etc/b1gmail/master.key with 32-byte base64-encoded key');
	}
	
	/**
	 * Encrypt credentials (UNIVERSAL for ALL protocols)
	 * 
	 * Uses AES-256-GCM (AEAD - Authenticated Encryption with Additional Data)
	 * 
	 * @param string $plaintext Plaintext credential
	 * @param string $context Context (e.g. 'pop3', 'smtp', 's3', etc.)
	 * @return array ['encrypted' => string, 'iv' => string, 'tag' => string, 'version' => int]
	 */
	public static function encrypt($plaintext, $context = 'generic')
	{
		$masterKey = self::loadMasterKey();
		
		// Generate nonce/IV (96 bits = 12 bytes for GCM)
		$iv = openssl_random_pseudo_bytes(12);
		
		// Additional Authenticated Data (AEAD)
		// Prevents ciphertext from being used in different contexts
		$aad = json_encode(array(
			'context' => $context,
			'version' => self::CRED_VERSION_AES256_GCM,
			'created' => time()
		));
		
		// Encrypt with AES-256-GCM
		$tag = ''; // Will be filled by openssl_encrypt
		$encrypted = openssl_encrypt(
			$plaintext,
			'aes-256-gcm',
			$masterKey,
			OPENSSL_RAW_DATA,
			$iv,
			$tag,
			$aad,
			16 // Tag length (128 bits)
		);
		
		if($encrypted === false)
		{
			throw new Exception('Encryption failed: ' . openssl_error_string());
		}
		
		self::$stats['encryptions']++;
		
		return array(
			'encrypted' => base64_encode($encrypted),
			'iv' => base64_encode($iv),
			'tag' => base64_encode($tag),
			'version' => self::CRED_VERSION_AES256_GCM
		);
	}
	
	/**
	 * Decrypt credentials (UNIVERSAL for ALL protocols)
	 * 
	 * @param string $encrypted Encrypted data (base64)
	 * @param string $iv IV (base64)
	 * @param string $tag Authentication tag (base64)
	 * @param string $context Context (must match encryption context!)
	 * @param int $version Encryption version
	 * @return string Plaintext
	 */
	public static function decrypt($encrypted, $iv, $tag, $context = 'generic', $version = self::CRED_VERSION_AES256_GCM)
	{
		// Legacy: Plaintext (during migration)
		if($version == self::CRED_VERSION_PLAINTEXT)
		{
			return $encrypted; // Not yet encrypted
		}
		
		$masterKey = self::loadMasterKey();
		
		// Reconstruct AAD (MUST be identical!)
		$aad = json_encode(array(
			'context' => $context,
			'version' => $version
		));
		
		// Decrypt
		$decrypted = openssl_decrypt(
			base64_decode($encrypted),
			'aes-256-gcm',
			$masterKey,
			OPENSSL_RAW_DATA,
			base64_decode($iv),
			base64_decode($tag),
			$aad
		);
		
		if($decrypted === false)
		{
			throw new Exception('Decryption failed: ' . openssl_error_string());
		}
		
		self::$stats['decryptions']++;
		
		return $decrypted;
	}
	
	/**
	 * Encrypt database field (helper)
	 * 
	 * @param string $table Table name (without prefix)
	 * @param string $field Field name
	 * @param int $id Record ID
	 * @param string $value Plaintext value
	 * @param string $context Encryption context
	 * @return bool Success
	 */
	public static function encryptField($table, $field, $id, $value, $context)
	{
		global $db;
		
		if(empty($value))
		{
			return false; // Skip empty values
		}
		
		try
		{
			$result = self::encrypt($value, $context);
			
			// Update with new columns
			$db->Query("UPDATE {pre}$table 
			            SET $field = ?,
			                {$field}_iv = ?,
			                {$field}_tag = ?,
			                {$field}_encrypted = 1,
			                {$field}_version = ?
			            WHERE id = ?",
			            $result['encrypted'],
			            $result['iv'],
			            $result['tag'],
			            $result['version'],
			            $id);
			
			return ($db->AffectedRows() == 1);
		}
		catch(Exception $e)
		{
			PutLog("Field encryption failed for $table.$field #$id: " . $e->getMessage(),
			       PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
	}
	
	/**
	 * Decrypt database field (helper with hybrid mode!)
	 * 
	 * Supports BOTH encrypted AND plaintext (during migration)
	 * 
	 * @param array $row Database row
	 * @param string $field Field name
	 * @param string $context Encryption context
	 * @return string Plaintext value
	 */
	public static function decryptField($row, $field, $context)
	{
		// Check if encrypted
		$encryptedFlag = $field . '_encrypted';
		
		if(!isset($row[$encryptedFlag]) || $row[$encryptedFlag] == 0)
		{
			// Still plaintext (during migration)
			return $row[$field];
		}
		
		// Encrypted → decrypt
		try
		{
			$ivField = $field . '_iv';
			$tagField = $field . '_tag';
			$versionField = $field . '_version';
			
			return self::decrypt(
				$row[$field],
				$row[$ivField],
				$row[$tagField],
				$context,
				$row[$versionField] ?? self::CRED_VERSION_AES256_GCM
			);
		}
		catch(Exception $e)
		{
			PutLog("Field decryption failed for $field: " . $e->getMessage(),
			       PRIO_WARNING, __FILE__, __LINE__);
			
			// Fallback to plaintext (emergency)
			return $row[$field];
		}
	}
	
	// ═══════════════════════════════════════════════════════════════
	// MASS MIGRATION
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Migrate ALL credentials to AES-256-GCM
	 * 
	 * @param string $protocol 'all' or specific protocol name
	 * @return array Statistics
	 */
	public static function migrateAllCredentials($protocol = 'all')
	{
		global $db;
		
		$stats = array(
			'total' => 0,
			'encrypted' => 0,
			'failed' => 0,
			'skipped' => 0
		);
		
		// Credential definitions (which tables/fields to encrypt)
		$credentialMap = self::getCredentialMap();
		
		// Filter by protocol
		$protocolsToMigrate = ($protocol === 'all') 
			? $credentialMap 
			: array($protocol => $credentialMap[$protocol]);
		
		// Process each protocol
		foreach($protocolsToMigrate as $protocolKey => $config)
		{
			if(!isset($credentialMap[$protocolKey]))
			{
				PutLog("Unknown protocol: $protocolKey", PRIO_WARNING, __FILE__, __LINE__);
				continue;
			}
			
			$condition = isset($config['condition']) 
				? $config['condition'] 
				: "{$config['encrypted_flag']} = 0 OR {$config['encrypted_flag']} IS NULL";
			
			$query = "SELECT * FROM {pre}{$config['table']} WHERE $condition";
			
			try
			{
				$res = $db->Query($query);
			}
			catch(Exception $e)
			{
				// Table might not exist
				PutLog("Table {$config['table']} not found, skipping", PRIO_NOTE, __FILE__, __LINE__);
				continue;
			}
			
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$stats['total']++;
				
				$password = $row[$config['password_field']];
				
				// Skip empty
				if(empty($password))
				{
					$stats['skipped']++;
					continue;
				}
				
				// Encrypt & update
				$success = self::encryptField(
					$config['table'],
					$config['password_field'],
					$row[$config['id_field']],
					$password,
					$config['context']
				);
				
				if($success)
				{
					$stats['encrypted']++;
				}
				else
				{
					$stats['failed']++;
				}
			}
			
			$res->Free();
		}
		
		return $stats;
	}
	
	/**
	 * Get credential map (which tables/fields contain credentials)
	 * 
	 * @return array Credential definitions
	 */
	private static function getCredentialMap()
	{
		return array(
			// USER CREDENTIALS (external accounts)
			'pop3' => array(
				'table' => 'pop3',
				'id_field' => 'id',
				'password_field' => 'p_pass',
				'encrypted_flag' => 'p_pass_encrypted',
				'context' => 'pop3'
			),
			
			'smtp_relay' => array(
				'table' => 'prefs',
				'id_field' => 'prefid',
				'password_field' => 'smtp_pass',
				'encrypted_flag' => 'smtp_pass_encrypted',
				'context' => 'smtp'
			),
			
			'ftp' => array(
				'table' => 'webdisk_ftp',
				'id_field' => 'id',
				'password_field' => 'ftp_pass',
				'encrypted_flag' => 'ftp_pass_encrypted',
				'context' => 'ftp'
			),
			
			// SYSTEM CREDENTIALS
			'cyrus' => array(
				'table' => 'protocols_config',
				'id_field' => 'id',
				'password_field' => 'config_value',
				'encrypted_flag' => 'encrypted',
				'context' => 'cyrus',
				'condition' => "config_key = 'cyrus_admin_pass' AND (encrypted = 0 OR encrypted IS NULL)"
			),
			
			'grommunio' => array(
				'table' => 'protocols_config',
				'id_field' => 'id',
				'password_field' => 'config_value',
				'encrypted_flag' => 'encrypted',
				'context' => 'grommunio',
				'condition' => "config_key = 'grommunio_admin_pass' AND (encrypted = 0 OR encrypted IS NULL)"
			),
			
			'sftpgo' => array(
				'table' => 'protocols_config',
				'id_field' => 'id',
				'password_field' => 'config_value',
				'encrypted_flag' => 'encrypted',
				'context' => 'sftpgo',
				'condition' => "config_key = 'sftpgo_admin_pass' AND (encrypted = 0 OR encrypted IS NULL)"
			),
			
			's3' => array(
				'table' => 'protocols_config',
				'id_field' => 'id',
				'password_field' => 'config_value',
				'encrypted_flag' => 'encrypted',
				'context' => 's3',
				'condition' => "config_key = 's3_secret_key' AND (encrypted = 0 OR encrypted IS NULL)"
			)
		);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// STATISTICS & LOGGING
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get migration statistics
	 * 
	 * @return array Statistics
	 */
	public static function getStats()
	{
		global $db;
		
		$stats = array();
		
		// User password migration status
		$res = $db->Query('SELECT 
		                   COUNT(*) as total,
		                   SUM(CASE WHEN password_version = 1 THEN 1 ELSE 0 END) as md5_count,
		                   SUM(CASE WHEN password_version = 2 THEN 1 ELSE 0 END) as bcrypt_count,
		                   SUM(CASE WHEN password_version = 3 THEN 1 ELSE 0 END) as argon2_count
		                   FROM {pre}users
		                   WHERE gesperrt = "no"');
		
		if($res->RowCount() > 0)
		{
			$stats['users'] = $res->FetchArray(MYSQLI_ASSOC);
		}
		$res->Free();
		
		// Credentials encryption status
		$stats['credentials'] = array();
		
		foreach(self::getCredentialMap() as $protocol => $config)
		{
			try
			{
				$res = $db->Query("SELECT 
				                   COUNT(*) as total,
				                   SUM(CASE WHEN {$config['encrypted_flag']} = 1 THEN 1 ELSE 0 END) as encrypted_count
				                   FROM {pre}{$config['table']}");
				
				if($res->RowCount() > 0)
				{
					$stats['credentials'][$protocol] = $res->FetchArray(MYSQLI_ASSOC);
				}
				$res->Free();
			}
			catch(Exception $e)
			{
				// Table might not exist
				continue;
			}
		}
		
		// Runtime stats
		$stats['runtime'] = self::$stats;
		
		return $stats;
	}
	
	/**
	 * Log password migration
	 */
	private static function logMigration($userid, $success, $duration_ms)
	{
		global $db;
		
		try
		{
			// Update daily statistics
			$db->Query('INSERT INTO {pre}password_migration_stats 
			            (date, total_users, bcrypt_users, failed_migrations, avg_migration_time_ms) 
			            VALUES (CURDATE(), 1, ?, 0, ?)
			            ON DUPLICATE KEY UPDATE 
			            total_users = total_users + 1,
			            bcrypt_users = bcrypt_users + VALUES(bcrypt_users),
			            avg_migration_time_ms = (avg_migration_time_ms + VALUES(avg_migration_time_ms)) / 2',
			            $success ? 1 : 0,
			            $duration_ms);
		}
		catch(Exception $e)
		{
			// Stats table might not exist yet
		}
	}
	
	/**
	 * Log migration error
	 */
	private static function logMigrationError($userid, $errorMessage)
	{
		global $db;
		
		try
		{
			$db->Query('INSERT INTO {pre}password_migration_errors 
			            (userid, error_type, error_message) 
			            VALUES (?, ?, ?)',
			            $userid,
			            'migration_failed',
			            $errorMessage);
		}
		catch(Exception $e)
		{
			// Error table might not exist yet
		}
	}
	
	// ═══════════════════════════════════════════════════════════════
	// HELPER FUNCTIONS
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Check if string looks like MD5 hash
	 * 
	 * @param string $string String to check
	 * @return bool
	 */
	private static function looksLikeMD5Hash($string)
	{
		return (strlen($string) === 32 && ctype_xdigit($string));
	}
	
	/**
	 * Generate random password
	 * 
	 * @param int $length Length
	 * @return string Random password
	 */
	public static function generateRandomPassword($length = 12)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
		$password = '';
		
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++)
		{
			$password .= $chars[random_int(0, $max)];
		}
		
		return $password;
	}
	
	/**
	 * Check password strength
	 * 
	 * @param string $password Password to check
	 * @return array ['score' => int (0-4), 'feedback' => array]
	 */
	public static function checkPasswordStrength($password)
	{
		$score = 0;
		$feedback = array();
		
		$length = strlen($password);
		
		// Length
		if($length >= 8) $score++;
		if($length >= 12) $score++;
		if($length >= 16) $score++;
		
		// Complexity
		if(preg_match('/[a-z]/', $password)) $score++;
		if(preg_match('/[A-Z]/', $password)) $score++;
		if(preg_match('/[0-9]/', $password)) $score++;
		if(preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
		
		// Normalize score (0-4)
		$score = min(4, floor($score / 2));
		
		// Feedback
		if($length < 8) $feedback[] = 'Mindestens 8 Zeichen';
		if(!preg_match('/[a-z]/', $password)) $feedback[] = 'Kleinbuchstaben';
		if(!preg_match('/[A-Z]/', $password)) $feedback[] = 'Großbuchstaben';
		if(!preg_match('/[0-9]/', $password)) $feedback[] = 'Zahlen';
		if(!preg_match('/[^a-zA-Z0-9]/', $password)) $feedback[] = 'Sonderzeichen';
		
		return array(
			'score' => $score,
			'feedback' => $feedback
		);
	}
	
	/**
	 * Generate Master Key (one-time setup)
	 * 
	 * CRITICAL: Run this ONCE and backup the key!
	 * 
	 * @param string $outputFile Path to save key
	 * @return string Base64-encoded master key
	 */
	public static function generateMasterKey($outputFile = '/etc/b1gmail/master.key')
	{
		// Generate 256-bit key
		$key = openssl_random_pseudo_bytes(32);
		
		if($key === false || strlen($key) !== 32)
		{
			throw new Exception('Failed to generate secure random key');
		}
		
		$encoded = base64_encode($key);
		
		// Save to file
		$dir = dirname($outputFile);
		if(!is_dir($dir))
		{
			mkdir($dir, 0700, true);
		}
		
		file_put_contents($outputFile, $encoded);
		chmod($outputFile, 0600);
		
		PutLog('Master key generated and saved to ' . $outputFile, PRIO_NOTE, __FILE__, __LINE__);
		
		// CLI-Mode only output
		if(php_sapi_name() === 'cli') {
			echo "═══════════════════════════════════════════════════════════════\n";
			echo " MASTER KEY GENERATED\n";
			echo "═══════════════════════════════════════════════════════════════\n\n";
			echo "Key saved to: $outputFile\n";
			echo "Permissions: 0600\n\n";
			echo "⚠️  CRITICAL: CREATE ENCRYPTED BACKUPS!\n\n";
			echo "Backup commands:\n";
			echo "  1. Encrypted backup:\n";
			echo "     gpg --encrypt --recipient admin@firma.de $outputFile > master.key.gpg\n\n";
			echo "  2. Copy to USB:\n";
			echo "     cp $outputFile /media/usb/master.key." . date('Ymd') . "\n\n";
			echo "  3. Upload to secure cloud storage\n\n";
			echo "═══════════════════════════════════════════════════════════════\n";
		}
		
		return $encoded;
	}
}
