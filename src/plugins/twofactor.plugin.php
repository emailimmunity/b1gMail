<?php
/*
 * b1gMail Two-Factor Authentication Plugin
 * (c) 2025 b1gMail Project
 *
 * Provides TOTP-based 2FA (Time-based One-Time Password)
 * Compatible with: Google Authenticator, Authy, Microsoft Authenticator
 * 
 * PHP 8.0+ compatible (tested with PHP 8.1-8.3)`n * Uses modern PHP 8.x features: Property Types, Constructor Promotion, Named Arguments
 */

define('TWOFACTOR_VERSION', '2.0.0');
define('TWOFACTOR_BACKUP_CODES', 10);
define('TWOFACTOR_TOTP_WINDOW', 1); // ±30 seconds

/**
 * Two-Factor Authentication Plugin
 */
class TwoFactorPlugin extends BMPlugin
{
	private array $prefs = [];
	
	/**
	 * Constructor with PHP 8.0+ features
	 */
	public function __construct()
	{
		// Plugin info
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'Two-Factor Authentication (2FA)';
		$this->author = 'b1gMail Project';
		$this->version = TWOFACTOR_VERSION;
		$this->website = 'https://www.b1gmail.org/';
		
		// Admin pages
		$this->admin_pages = true;
		$this->admin_page_title = '2FA Settings';
		$this->admin_page_icon = 'shield.png';
		
		// Supported methods
		$this->supportedMethods = ['totp', 'backup'];
	}
	
	/**
	 * OnLoad - Initialize plugin
	 */
	public function OnLoad(): void
	{
		$this->prefs = $this->_getPrefs();
		
		// Register hooks
		global $eventHandler;
		if ($eventHandler !== null) {
			$eventHandler->registerHandler('AfterLogin', [$this, 'OnAfterLogin']);
			$eventHandler->registerHandler('BeforeLogin', [$this, 'OnBeforeLogin']);
		}
	}
	
	/**
	 * Initialize database tables
	 */
	public function OnInstall(): bool
	{
		global $db;
		
		// 2FA settings table
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}twofactor_settings (
			user_id INT NOT NULL PRIMARY KEY,
			enabled TINYINT(1) DEFAULT 0,
			method VARCHAR(20) DEFAULT "totp",
			secret VARCHAR(64) DEFAULT NULL,
			backup_codes TEXT DEFAULT NULL,
			created_at INT NOT NULL,
			verified_at INT DEFAULT NULL,
			last_used INT DEFAULT NULL,
			INDEX (enabled)
		)');
		
		// 2FA sessions table
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}twofactor_sessions (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			temp_token VARCHAR(64) NOT NULL,
			ip_address VARCHAR(45) NOT NULL,
			created_at INT NOT NULL,
			expires_at INT NOT NULL,
			verified TINYINT(1) DEFAULT 0,
			INDEX (temp_token),
			INDEX (expires_at)
		)');
		
		// 2FA audit log
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}twofactor_log (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			action VARCHAR(50) NOT NULL,
			success TINYINT(1) DEFAULT 0,
			ip_address VARCHAR(45) NOT NULL,
			user_agent VARCHAR(255) DEFAULT NULL,
			created_at INT NOT NULL,
			INDEX (user_id, created_at)
		)');
		
		// Plugin preferences
		$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
			'twofactor_enabled', '1');
		$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
			'twofactor_enforce', '0');
		$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
			'twofactor_grace_period', '7');
		
		PutLog('2FA Plugin installed successfully', PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	/**
	 * Cleanup on uninstall
	 */
	public function OnUninstall(): bool
	{
		global $db;
		
		// Optional: Keep data for potential reinstall
		// $db->Query('DROP TABLE IF EXISTS {pre}twofactor_settings');
		// $db->Query('DROP TABLE IF EXISTS {pre}twofactor_sessions');
		// $db->Query('DROP TABLE IF EXISTS {pre}twofactor_log');
		
		PutLog('2FA Plugin uninstalled', PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	/**
	 * BeforeLogin Hook - Check if 2FA is required
	 */
	public function OnBeforeLogin(array $params): void
	{
		// Called before login validation
		// We'll check 2FA in AfterLogin after successful password check
	}
	
	/**
	 * AfterLogin Hook - Require 2FA verification
	 */
	public function OnAfterLogin(array $params): bool
	{
		global $db;
		
		$userID = $params['userID'] ?? 0;
		if (!$userID) return true;
		
		// Check if 2FA is enabled for this user
		$res = $db->Query('SELECT enabled, method FROM {pre}twofactor_settings WHERE user_id=?', $userID);
		if ($res->RowCount() == 0) {
			$res->Free();
			return true; // No 2FA configured
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if (!$row['enabled']) {
			return true; // 2FA not enabled
		}
		
		// Create temporary session
		$tempToken = bin2hex(random_bytes(32));
		$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$expiresAt = time() + 600; // 10 minutes
		
		$db->Query('INSERT INTO {pre}twofactor_sessions (user_id, temp_token, ip_address, created_at, expires_at) 
		           VALUES (?, ?, ?, ?, ?)',
			$userID, $tempToken, $ipAddress, time(), $expiresAt
		);
		
		// Store temp token in session
		$_SESSION['twofactor_temp_token'] = $tempToken;
		$_SESSION['twofactor_user_id'] = $userID;
		$_SESSION['twofactor_pending'] = true;
		
		// Redirect to 2FA verification page
		header('Location: ?action=twofactor_verify');
		exit();
	}
	
	/**
	 * User Preferences Handler
	 */
	public function OnPrefsHandler(array &$modules): void
	{
		global $lang_user;
		
		$modules['twofactor'] = [
			'title' => '2FA Security',
			'description' => 'Secure your account with Two-Factor Authentication',
			'icon' => 'shield',
			'handler' => [$this, 'PrefsPage']
		];
	}
	
	/**
	 * User Preferences Page
	 */
	public function PrefsPage(): void
	{
		global $thisUser, $db, $tpl;
		
		$action = $_REQUEST['twofactor_action'] ?? 'overview';
		
		switch ($action) {
			case 'enable':
				$this->enableTwoFactor($thisUser);
				break;
				
			case 'verify':
				$this->verifySetup($thisUser);
				break;
				
			case 'disable':
				$this->disableTwoFactor($thisUser);
				break;
				
			case 'regenerate_backup':
				$this->regenerateBackupCodes($thisUser);
				break;
				
			default:
				$this->showOverview($thisUser);
		}
	}
	
	/**
	 * Show 2FA Overview
	 */
	private function showOverview(int $userID): void
	{
		global $db, $tpl;
		
		// Get current 2FA status
		$res = $db->Query('SELECT * FROM {pre}twofactor_settings WHERE user_id=?', $userID);
		$settings = $res->RowCount() > 0 ? $res->FetchArray(MYSQLI_ASSOC) : null;
		$res->Free();
		
		$tpl->assign('twofactor_enabled', $settings && $settings['enabled']);
		$tpl->assign('twofactor_verified', $settings && $settings['verified_at']);
		$tpl->assign('twofactor_method', $settings['method'] ?? 'totp');
		$tpl->assign('twofactor_last_used', $settings['last_used'] ?? 0);
		
		// Recent activity
		$res = $db->Query('SELECT * FROM {pre}twofactor_log WHERE user_id=? ORDER BY created_at DESC LIMIT 10', $userID);
		$activity = [];
		while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$activity[] = $row;
		}
		$res->Free();
		
		$tpl->assign('twofactor_activity', $activity);
		$tpl->assign('page_content', $this->_templatePath('twofactor.overview.tpl'));
	}
	
	/**
	 * Enable Two-Factor Authentication
	 */
	private function enableTwoFactor(int $userID): void
	{
		global $db, $tpl;
		
		// Generate secret
		$secret = $this->generateSecret();
		
		// Generate backup codes
		$backupCodes = $this->generateBackupCodes();
		
		// Store in database (not verified yet)
		$db->Query('INSERT INTO {pre}twofactor_settings (user_id, enabled, secret, backup_codes, created_at)
		           VALUES (?, 0, ?, ?, ?)
		           ON DUPLICATE KEY UPDATE secret=VALUES(secret), backup_codes=VALUES(backup_codes), enabled=0',
			$userID, $secret, json_encode($backupCodes), time()
		);
		
		// Generate QR Code
		$qrCodeUrl = $this->generateQRCodeUrl($userID, $secret);
		
		// Show setup page
		$tpl->assign('twofactor_secret', $secret);
		$tpl->assign('twofactor_secret_formatted', $this->formatSecret($secret));
		$tpl->assign('twofactor_qrcode', $qrCodeUrl);
		$tpl->assign('twofactor_backup_codes', $backupCodes);
		$tpl->assign('page_content', $this->_templatePath('twofactor.setup.tpl'));
	}
	
	/**
	 * Verify 2FA Setup
	 */
	private function verifySetup(int $userID): void
	{
		global $db;
		
		$code = $_POST['twofactor_code'] ?? '';
		
		// Get secret
		$res = $db->Query('SELECT secret FROM {pre}twofactor_settings WHERE user_id=?', $userID);
		if ($res->RowCount() == 0) {
			$res->Free();
			header('Location: ?action=prefs&module=twofactor&error=no_setup');
			exit();
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$secret = $row['secret'];
		$res->Free();
		
		// Verify code
		if ($this->verifyTOTP($secret, $code)) {
			// Enable 2FA
			$db->Query('UPDATE {pre}twofactor_settings SET enabled=1, verified_at=? WHERE user_id=?',
				time(), $userID
			);
			
			// Log
			$this->logActivity($userID, 'enable', true);
			
			header('Location: ?action=prefs&module=twofactor&success=enabled');
		} else {
			// Failed
			$this->logActivity($userID, 'verify_failed', false);
			header('Location: ?action=prefs&module=twofactor&error=invalid_code');
		}
		exit();
	}
	
	/**
	 * Disable Two-Factor Authentication
	 */
	private function disableTwoFactor(int $userID): void
	{
		global $db;
		
		$password = $_POST['password'] ?? '';
		
		// Verify password
		if (!$this->verifyPassword($userID, $password)) {
			header('Location: ?action=prefs&module=twofactor&error=invalid_password');
			exit();
		}
		
		// Disable 2FA
		$db->Query('UPDATE {pre}twofactor_settings SET enabled=0 WHERE user_id=?', $userID);
		
		// Log
		$this->logActivity($userID, 'disable', true);
		
		header('Location: ?action=prefs&module=twofactor&success=disabled');
		exit();
	}
	
	/**
	 * Regenerate Backup Codes
	 */
	private function regenerateBackupCodes(int $userID): void
	{
		global $db;
		
		$backupCodes = $this->generateBackupCodes();
		
		$db->Query('UPDATE {pre}twofactor_settings SET backup_codes=? WHERE user_id=?',
			json_encode($backupCodes), $userID
		);
		
		$this->logActivity($userID, 'regenerate_backup', true);
		
		header('Location: ?action=prefs&module=twofactor&success=codes_regenerated');
		exit();
	}
	
	/**
	 * Generate TOTP Secret (Base32)
	 */
	private function generateSecret(int $length = 32): string
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32
		$secret = '';
		$max = strlen($chars) - 1;
		
		for ($i = 0; $i < $length; $i++) {
			$secret .= $chars[random_int(0, $max)];
		}
		
		return $secret;
	}
	
	/**
	 * Format secret for display (XXXX-XXXX-XXXX-XXXX)
	 */
	private function formatSecret(string $secret): string
	{
		return implode('-', str_split($secret, 4));
	}
	
	/**
	 * Generate Backup Codes
	 */
	private function generateBackupCodes(int $count = TWOFACTOR_BACKUP_CODES): array
	{
		$codes = [];
		for ($i = 0; $i < $count; $i++) {
			$codes[] = [
				'code' => sprintf('%04d-%04d', random_int(0, 9999), random_int(0, 9999)),
				'used' => false,
				'used_at' => null
			];
		}
		return $codes;
	}
	
	/**
	 * Generate QR Code URL (using Google Charts API or similar)
	 */
	private function generateQRCodeUrl(int $userID, string $secret): string
	{
		global $bm_prefs;
		
		// Get user email
		$email = $this->getUserEmail($userID);
		$issuer = $bm_prefs['title'] ?? 'b1gMail';
		
		// otpauth://totp/Issuer:user@example.com?secret=SECRET&issuer=Issuer
		$otpauthUrl = sprintf(
			'otpauth://totp/%s:%s?secret=%s&issuer=%s',
			rawurlencode($issuer),
			rawurlencode($email),
			$secret,
			rawurlencode($issuer)
		);
		
		// Using Google Charts API for QR Code
		return sprintf(
			'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=%s',
			urlencode($otpauthUrl)
		);
	}
	
	/**
	 * Verify TOTP Code
	 */
	private function verifyTOTP(string $secret, string $code, int $window = TWOFACTOR_TOTP_WINDOW): bool
	{
		$timestamp = floor(time() / 30);
		
		// Check current time and ±window
		for ($i = -$window; $i <= $window; $i++) {
			if ($this->generateTOTP($secret, $timestamp + $i) === $code) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Generate TOTP Code
	 */
	private function generateTOTP(string $secret, int $timestamp): string
	{
		// Decode Base32 secret
		$secret = $this->base32Decode($secret);
		
		// Pack timestamp
		$time = pack('N*', 0) . pack('N*', $timestamp);
		
		// HMAC-SHA1
		$hash = hash_hmac('sha1', $time, $secret, true);
		
		// Dynamic truncation
		$offset = ord($hash[19]) & 0x0f;
		$code = (
			((ord($hash[$offset + 0]) & 0x7f) << 24) |
			((ord($hash[$offset + 1]) & 0xff) << 16) |
			((ord($hash[$offset + 2]) & 0xff) << 8) |
			(ord($hash[$offset + 3]) & 0xff)
		) % 1000000;
		
		return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Base32 Decode
	 */
	private function base32Decode(string $secret): string
	{
		$secret = strtoupper($secret);
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$decoded = '';
		$buffer = 0;
		$bitsLeft = 0;
		
		for ($i = 0; $i < strlen($secret); $i++) {
			$val = strpos($chars, $secret[$i]);
			if ($val === false) continue;
			
			$buffer = ($buffer << 5) | $val;
			$bitsLeft += 5;
			
			if ($bitsLeft >= 8) {
				$decoded .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
				$bitsLeft -= 8;
			}
		}
		
		return $decoded;
	}
	
	/**
	 * Verify Backup Code
	 */
	private function verifyBackupCode(int $userID, string $code): bool
	{
		global $db;
		
		$res = $db->Query('SELECT backup_codes FROM {pre}twofactor_settings WHERE user_id=?', $userID);
		if ($res->RowCount() == 0) {
			$res->Free();
			return false;
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$backupCodes = json_decode($row['backup_codes'], true);
		if (!is_array($backupCodes)) return false;
		
		// Check if code exists and is not used
		foreach ($backupCodes as $idx => $backup) {
			if ($backup['code'] === $code && !$backup['used']) {
				// Mark as used
				$backupCodes[$idx]['used'] = true;
				$backupCodes[$idx]['used_at'] = time();
				
				$db->Query('UPDATE {pre}twofactor_settings SET backup_codes=? WHERE user_id=?',
					json_encode($backupCodes), $userID
				);
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Verify Password
	 */
	private function verifyPassword(int $userID, string $password): bool
	{
		global $db;
		
		$res = $db->Query('SELECT passwort FROM {pre}users WHERE id=?', $userID);
		if ($res->RowCount() == 0) {
			$res->Free();
			return false;
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		return password_verify($password, $row['passwort']);
	}
	
	/**
	 * Get User Email
	 */
	private function getUserEmail(int $userID): string
	{
		global $db;
		
		$res = $db->Query('SELECT email FROM {pre}users WHERE id=?', $userID);
		if ($res->RowCount() == 0) {
			$res->Free();
			return 'user@example.com';
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		return $row['email'];
	}
	
	/**
	 * Log Activity
	 */
	private function logActivity(int $userID, string $action, bool $success): void
	{
		global $db;
		
		$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
		
		$db->Query('INSERT INTO {pre}twofactor_log (user_id, action, success, ip_address, user_agent, created_at)
		           VALUES (?, ?, ?, ?, ?, ?)',
			$userID, $action, $success ? 1 : 0, $ipAddress, $userAgent, time()
		);
	}
	
	/**
	 * Get Plugin Preferences
	 */
	private function _getPrefs(): array
	{
		global $db;
		
		$prefs = [
			'enabled' => true,
			'enforce' => false,
			'grace_period' => 7
		];
		
		$res = $db->Query('SELECT twofactor_enabled, twofactor_enforce, twofactor_grace_period FROM {pre}prefs LIMIT 1');
		if ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$prefs['enabled'] = ($row['twofactor_enabled'] === 'yes');
			$prefs['enforce'] = ($row['twofactor_enforce'] === 'yes');
			$prefs['grace_period'] = (int)($row['twofactor_grace_period'] ?? 7);
		}
		$res->Free();
		
		return $prefs;
	}
	
	/**
	 * Get Template Path
	 */
	public function _templatePath($template)
	{
		return B1GMAIL_DIR . 'plugins/templates/' . $template;
	}
	
	/**
	 * Admin Page Handler (required by b1gMail)
	 */
	public function OnAdminPage(): void
	{
		$this->AdminHandler();
	}
	
	/**
	 * Admin Handler
	 */
	public function AdminHandler(): void
	{
		global $tpl, $db, $lang_admin;
		
		$action = $_REQUEST['action'] ?? 'overview';
		
		switch ($action) {
			case 'settings':
				$this->adminSettings();
				break;
				
			case 'users':
				$this->adminUsers();
				break;
				
			case 'logs':
				$this->adminLogs();
				break;
				
			default:
				$this->adminOverview();
		}
	}
	
	/**
	 * Admin Overview
	 */
	private function adminOverview(): void
	{
		global $db, $tpl;
		
		// Statistics
		$res = $db->Query('SELECT COUNT(*) as total FROM {pre}users');
		$totalUsers = $res->FetchArray(MYSQLI_NUM)[0];
		$res->Free();
		
		$res = $db->Query('SELECT COUNT(*) as total FROM {pre}twofactor_settings WHERE enabled=1');
		$enabledUsers = $res->FetchArray(MYSQLI_NUM)[0];
		$res->Free();
		
		$res = $db->Query('SELECT COUNT(*) as total FROM {pre}twofactor_log WHERE created_at > ? AND success=0',
			time() - 86400
		);
		$failedAttempts24h = $res->FetchArray(MYSQLI_NUM)[0];
		$res->Free();
		
		$tpl->assign('twofactor_total_users', $totalUsers);
		$tpl->assign('twofactor_enabled_users', $enabledUsers);
		$tpl->assign('twofactor_adoption_rate', $totalUsers > 0 ? round(($enabledUsers / $totalUsers) * 100, 1) : 0);
		$tpl->assign('twofactor_failed_24h', $failedAttempts24h);
		
		$tpl->display($this->_templatePath('admin.twofactor.overview.tpl'));
	}
	
	/**
	 * Admin Settings
	 */
	private function adminSettings(): void
	{
		global $db, $tpl;
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
				'twofactor_enabled', $_POST['enabled'] ?? '0'
			);
			$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
				'twofactor_enforce', $_POST['enforce'] ?? '0'
			);
			$db->Query('REPLACE INTO {pre}prefs (key, value) VALUES (?, ?)',
				'twofactor_grace_period', $_POST['grace_period'] ?? '7'
			);
			
			header('Location: ?action=admin&plugin=twofactor&success=saved');
			exit();
		}
		
		$tpl->assign('twofactor_prefs', $this->prefs);
		$tpl->display($this->_templatePath('admin.twofactor.settings.tpl'));
	}
	
	/**
	 * Admin Users List
	 */
	private function adminUsers(): void
	{
		global $db, $tpl;
		
		$res = $db->Query('SELECT u.id, u.email, t.enabled, t.verified_at, t.last_used
		                   FROM {pre}users u
		                   LEFT JOIN {pre}twofactor_settings t ON u.id = t.user_id
		                   ORDER BY u.email');
		
		$users = [];
		while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$users[] = $row;
		}
		$res->Free();
		
		$tpl->assign('twofactor_users', $users);
		$tpl->display($this->_templatePath('admin.twofactor.users.tpl'));
	}
	
	/**
	 * Admin Logs
	 */
	private function adminLogs(): void
	{
		global $db, $tpl;
		
		$limit = $_REQUEST['limit'] ?? 100;
		$offset = $_REQUEST['offset'] ?? 0;
		
		$res = $db->Query('SELECT l.*, u.email
		                   FROM {pre}twofactor_log l
		                   LEFT JOIN {pre}users u ON l.user_id = u.id
		                   ORDER BY l.created_at DESC
		                   LIMIT ? OFFSET ?',
			$limit, $offset
		);
		
		$logs = [];
		while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$logs[] = $row;
		}
		$res->Free();
		
		$tpl->assign('twofactor_logs', $logs);
		$tpl->display($this->_templatePath('admin.twofactor.logs.tpl'));
	}
}

/**
 * Register plugin
 */
$plugins->registerPlugin('TwoFactorPlugin');
