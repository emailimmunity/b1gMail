<?php
/**
 * b1gMail Admin User Manager - Extended Edition
 * 
 * Erweitert Admin-Funktionen für User-Management:
 * - Password-Reset (bereits vorhanden, wird erweitert)
 * - MFA-Reset (NEU!)
 * - Rollen-basierte Berechtigungen (Superadmin, Reseller, Domain-Admin)
 * 
 * PERMISSIONS:
 * - Superadmin: ALLES (inkl. Impersonation)
 * - Reseller: Nur SEINE User (kein Impersonation!)
 * - Multi-Domain Admin: Nur SEINE Domains (kein Impersonation!)
 * - Single-Domain Admin: Nur SEINE Domain (kein Impersonation!)
 * - Subdomain Admin: Nur SEINE Subdomain (kein Impersonation!)
 * 
 * (c) 2025 b1gMail Project
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class BMAdminUserManager
{
	private $adminID;
	private $adminRole;
	private $db;
	
	/**
	 * Constructor
	 * 
	 * @param int $adminID Admin ID (from session)
	 */
	public function __construct($adminID)
	{
		global $db;
		$this->adminID = (int)$adminID;
		$this->db = $db;
		$this->adminRole = $this->getAdminRole();
	}
	
	// ═══════════════════════════════════════════════════════════════
	// ROLE DETECTION
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get admin role from EmailAdmin plugin
	 * 
	 * @return string 'superadmin', 'reseller', 'multidomain_admin', etc.
	 */
	private function getAdminRole()
	{
		// Check if admin is in emp_roles table (EmailAdmin)
		$res = $this->db->Query('SELECT role FROM {pre}emp_roles WHERE user_id = ?',
		                        $this->adminID);
		
		if($res->RowCount() > 0)
		{
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			return $row['role'];
		}
		$res->Free();
		
		// Fallback: Check if admin table type = 0 (Superadmin)
		$res = $this->db->Query('SELECT type FROM {pre}admins WHERE adminid = ?',
		                        $this->adminID);
		
		if($res->RowCount() > 0)
		{
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if($row['type'] == 0)
			{
				return 'superadmin';
			}
		}
		$res->Free();
		
		return 'admin'; // Default
	}
	
	/**
	 * Check if admin can manage this user
	 * 
	 * @param int $targetUserID Target user ID
	 * @return bool Can manage
	 */
	public function canManageUser($targetUserID)
	{
		// Superadmin can manage ALL users
		if($this->adminRole === 'superadmin')
		{
			return true;
		}
		
		// Get user's domain
		$res = $this->db->Query('SELECT email FROM {pre}users WHERE id = ?', $targetUserID);
		if($res->RowCount() == 0)
		{
			$res->Free();
			return false;
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$userEmail = $row['email'];
		$userDomain = substr($userEmail, strpos($userEmail, '@') + 1);
		
		// Check if admin manages this domain
		switch($this->adminRole)
		{
			case 'reseller':
				// Check if domain belongs to this reseller
				$res = $this->db->Query('SELECT COUNT(*) as cnt 
				                         FROM {pre}emp_domains 
				                         WHERE domain = ? 
				                         AND (empadmin = ? OR parent_admin = ?)',
				                         $userDomain,
				                         $this->adminID,
				                         $this->adminID);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				return ($row['cnt'] > 0);
				
			case 'multidomain_admin':
				// Check if admin is assigned to this domain
				$res = $this->db->Query('SELECT COUNT(*) as cnt 
				                         FROM {pre}emp_domains d
				                         INNER JOIN {pre}emp_admin_domains ad ON d.id = ad.domain_id
				                         WHERE d.domain = ? AND ad.admin_id = ?',
				                         $userDomain,
				                         $this->adminID);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				return ($row['cnt'] > 0);
				
			case 'domain_admin':
			case 'subdomain_admin':
				// Check if this is THE domain
				$res = $this->db->Query('SELECT COUNT(*) as cnt 
				                         FROM {pre}emp_domains 
				                         WHERE domain = ? AND empadmin = ?',
				                         $userDomain,
				                         $this->adminID);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$res->Free();
				return ($row['cnt'] > 0);
				
			default:
				return false;
		}
	}
	
	/**
	 * Check if admin can impersonate (login as user)
	 * 
	 * ONLY Superadmin can impersonate!
	 * 
	 * @return bool Can impersonate
	 */
	public function canImpersonate()
	{
		return ($this->adminRole === 'superadmin');
	}
	
	// ═══════════════════════════════════════════════════════════════
	// PASSWORD MANAGEMENT
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Reset user password (EXTENDED!)
	 * 
	 * Allows admin to set new password OR generate random password
	 * 
	 * @param int $targetUserID Target user ID
	 * @param string $newPassword New password (or null for random)
	 * @param bool $forceChange Require password change on next login
	 * @return array Result
	 */
	public function resetPassword($targetUserID, $newPassword = null, $forceChange = true)
	{
		// Permission check
		if(!$this->canManageUser($targetUserID))
		{
			return array(
				'success' => false,
				'message' => 'Keine Berechtigung für diesen User!'
			);
		}
		
		// Generate random password if not provided
		if($newPassword === null)
		{
			require_once('password.class.php');
			$newPassword = PasswordManager::generateRandomPassword(12);
			$wasGenerated = true;
		}
		else
		{
			$wasGenerated = false;
		}
		
		// Get user info
		$user = _new('BMUser', array($targetUserID));
		$userRow = $user->Fetch();
		
		if(!$userRow)
		{
			return array(
				'success' => false,
				'message' => 'User nicht gefunden!'
			);
		}
		
		// Hash password with bcrypt
		require_once('password.class.php');
		$hashed = PasswordManager::hash($newPassword, 'bcrypt');
		
		// Update database
		$this->db->Query('UPDATE {pre}users 
		                  SET passwort = ?,
		                      password_version = ?,
		                      passwort_salt = NULL,
		                      password_force_change = ?
		                  WHERE id = ?',
		                  $hashed['hash'],
		                  $hashed['version'],
		                  $forceChange ? 1 : 0,
		                  $targetUserID);
		
		// Audit log
		PutLog(sprintf('Admin #%d (%s) reset password for user #%d (%s)',
		              $this->adminID,
		              $this->adminRole,
		              $targetUserID,
		              $userRow['email']),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		// TKÜV-Logging (if user is under surveillance)
		$this->logTKUVAction($targetUserID, 'password_reset', 
		                     'Admin ' . $this->adminRole . ' reset password');
		
		return array(
			'success' => true,
			'message' => 'Passwort erfolgreich zurückgesetzt!',
			'new_password' => $wasGenerated ? $newPassword : null,
			'force_change' => $forceChange
		);
	}
	
	// ═══════════════════════════════════════════════════════════════
	// MFA MANAGEMENT (NEU!)
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Disable MFA for user (Emergency/Support)
	 * 
	 * Allows admin to disable MFA if user lost device
	 * 
	 * @param int $targetUserID Target user ID
	 * @param string $reason Reason for disabling
	 * @return array Result
	 */
	public function disableMFA($targetUserID, $reason = '')
	{
		// Permission check
		if(!$this->canManageUser($targetUserID))
		{
			return array(
				'success' => false,
				'message' => 'Keine Berechtigung für diesen User!'
			);
		}
		
		$disabled = array();
		
		// Get user info
		$user = _new('BMUser', array($targetUserID));
		$userRow = $user->Fetch();
		
		// Disable TOTP
		try
		{
			$res = $this->db->Query('SELECT enabled FROM {pre}totp_secrets WHERE userid = ?',
			                        $targetUserID);
			
			if($res->RowCount() > 0)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				
				if($row['enabled'] == 1)
				{
					$this->db->Query('UPDATE {pre}totp_secrets 
					                  SET enabled = 0,
					                      disabled_by_admin = ?,
					                      disabled_reason = ?,
					                      disabled_at = NOW()
					                  WHERE userid = ?',
					                  $this->adminID,
					                  $reason,
					                  $targetUserID);
					
					$disabled[] = 'TOTP (Google Authenticator)';
				}
			}
			$res->Free();
		}
		catch(Exception $e)
		{
			// TOTP table might not exist
		}
		
		// Disable WebAuthn credentials
		try
		{
			$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}webauthn_credentials WHERE userid = ?',
			                        $targetUserID);
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			
			if($row['cnt'] > 0)
			{
				$this->db->Query('DELETE FROM {pre}webauthn_credentials WHERE userid = ?',
				                 $targetUserID);
				
				$disabled[] = 'Passkey (' . $row['cnt'] . ' Geräte)';
			}
		}
		catch(Exception $e)
		{
			// WebAuthn table might not exist
		}
		
		// Disable smsTAN (if plugin active)
		// TODO: Check if smsTAN plugin is installed
		
		// Delete trusted devices
		try
		{
			$this->db->Query('DELETE FROM {pre}mfa_trusted_devices WHERE userid = ?',
			                 $targetUserID);
		}
		catch(Exception $e)
		{
			// Table might not exist
		}
		
		// MFA Audit log
		try
		{
			$this->db->Query('INSERT INTO {pre}mfa_audit 
			                  (userid, mfa_method, action, ip_address, user_agent, admin_id, admin_reason) 
			                  VALUES (?, ?, ?, ?, ?, ?, ?)',
			                  $targetUserID,
			                  'all',
			                  'disabled_by_admin',
			                  $_SERVER['REMOTE_ADDR'] ?? null,
			                  $_SERVER['HTTP_USER_AGENT'] ?? null,
			                  $this->adminID,
			                  $reason);
		}
		catch(Exception $e)
		{
			// Table might not exist
		}
		
		// System log
		PutLog(sprintf('Admin #%d (%s) disabled MFA for user #%d (%s) - Reason: %s',
		              $this->adminID,
		              $this->adminRole,
		              $targetUserID,
		              $userRow['email'],
		              $reason),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		// TKÜV-Logging
		$this->logTKUVAction($targetUserID, 'mfa_disabled_by_admin', 
		                     'Admin ' . $this->adminRole . ' disabled MFA - Reason: ' . $reason);
		
		if(count($disabled) > 0)
		{
			return array(
				'success' => true,
				'message' => 'MFA deaktiviert: ' . implode(', ', $disabled),
				'methods_disabled' => $disabled
			);
		}
		else
		{
			return array(
				'success' => false,
				'message' => 'User hatte keine aktive MFA.'
			);
		}
	}
	
	/**
	 * Get MFA status for user (Admin-View)
	 * 
	 * @param int $targetUserID Target user ID
	 * @return array MFA status
	 */
	public function getMFAStatus($targetUserID)
	{
		if(!$this->canManageUser($targetUserID))
		{
			return array('error' => 'Keine Berechtigung');
		}
		
		$status = array(
			'totp' => false,
			'passkey' => false,
			'sms' => false,
			'any_enabled' => false
		);
		
		// TOTP
		try
		{
			$res = $this->db->Query('SELECT enabled, created_at, last_used_at 
			                         FROM {pre}totp_secrets 
			                         WHERE userid = ?',
			                         $targetUserID);
			
			if($res->RowCount() > 0)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$status['totp'] = array(
					'enabled' => ($row['enabled'] == 1),
					'created_at' => $row['created_at'],
					'last_used_at' => $row['last_used_at']
				);
				
				if($row['enabled'] == 1)
				{
					$status['any_enabled'] = true;
				}
			}
			$res->Free();
		}
		catch(Exception $e) {}
		
		// Passkey
		try
		{
			$res = $this->db->Query('SELECT credential_id, device_name, created_at, last_used_at 
			                         FROM {pre}webauthn_credentials 
			                         WHERE userid = ?',
			                         $targetUserID);
			
			$devices = array();
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$devices[] = $row;
				$status['any_enabled'] = true;
			}
			$res->Free();
			
			if(count($devices) > 0)
			{
				$status['passkey'] = array(
					'enabled' => true,
					'devices' => $devices
				);
			}
		}
		catch(Exception $e) {}
		
		return $status;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// PERMISSIONS CHECK
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Get all users that this admin can manage
	 * 
	 * @return array User IDs
	 */
	public function getManagedUsers()
	{
		$users = array();
		
		// Superadmin: ALL users
		if($this->adminRole === 'superadmin')
		{
			$res = $this->db->Query('SELECT id FROM {pre}users WHERE gesperrt = "no"');
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$users[] = $row['id'];
			}
			$res->Free();
			
			return $users;
		}
		
		// Reseller/Domain-Admin: Only users in their domains
		$domains = $this->getManagedDomains();
		
		foreach($domains as $domain)
		{
			$res = $this->db->Query('SELECT id FROM {pre}users 
			                         WHERE email LIKE ? AND gesperrt = "no"',
			                         '%@' . $domain);
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$users[] = $row['id'];
			}
			$res->Free();
		}
		
		return $users;
	}
	
	/**
	 * Get domains managed by this admin
	 * 
	 * @return array Domain names
	 */
	private function getManagedDomains()
	{
		$domains = array();
		
		switch($this->adminRole)
		{
			case 'superadmin':
				// ALL domains
				$res = $this->db->Query('SELECT domain FROM {pre}emp_domains');
				break;
				
			case 'reseller':
				// Domains where empadmin = this admin OR parent_admin = this admin
				$res = $this->db->Query('SELECT domain FROM {pre}emp_domains 
				                         WHERE empadmin = ? OR parent_admin = ?',
				                         $this->adminID,
				                         $this->adminID);
				break;
				
			case 'multidomain_admin':
				// Assigned domains
				$res = $this->db->Query('SELECT d.domain FROM {pre}emp_domains d
				                         INNER JOIN {pre}emp_admin_domains ad ON d.id = ad.domain_id
				                         WHERE ad.admin_id = ?',
				                         $this->adminID);
				break;
				
			case 'domain_admin':
			case 'subdomain_admin':
				// Only ONE domain
				$res = $this->db->Query('SELECT domain FROM {pre}emp_domains 
				                         WHERE empadmin = ? 
				                         LIMIT 1',
				                         $this->adminID);
				break;
				
			default:
				return array();
		}
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$domains[] = $row['domain'];
		}
		$res->Free();
		
		return $domains;
	}
	
	// ═══════════════════════════════════════════════════════════════
	// TKÜV LOGGING
	// ═══════════════════════════════════════════════════════════════
	
	/**
	 * Log admin action to TKÜV systems
	 * 
	 * Logs to:
	 * - Account Mirror (if user is mirrored)
	 * - RemoveIP (if user is under surveillance)
	 * - System log
	 * 
	 * @param int $targetUserID Target user ID
	 * @param string $action Action performed
	 * @param string $details Details
	 */
	private function logTKUVAction($targetUserID, $action, $details)
	{
		// Check if user is under Account Mirror
		try
		{
			$res = $this->db->Query('SELECT mirrorid FROM {pre}mod_accountmirror_v2 
			                         WHERE userid = ? AND active = 1',
			                         $targetUserID);
			
			if($res->RowCount() > 0)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				
				// Log to Account Mirror audit
				require_once(dirname(__FILE__) . '/../plugins/classes/AccountMirrorV2_AuditManager.class.php');
				$auditManager = new AccountMirrorV2_AuditManager();
				$auditManager->logAction(
					$row['mirrorid'],
					$action,
					$details,
					$this->adminID
				);
			}
			$res->Free();
		}
		catch(Exception $e)
		{
			// Account Mirror might not be installed
		}
		
		// Check if user is under IP surveillance
		try
		{
			$res = $this->db->Query('SELECT id FROM {pre}mod_removeip_surveillance 
			                         WHERE userid = ? AND active = 1',
			                         $targetUserID);
			
			if($res->RowCount() > 0)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				
				// Log to RemoveIP
				$this->db->Query('INSERT INTO {pre}mod_removeip_logs 
				                  (surveillance_id, userid, ip_address, action, user_agent, details) 
				                  VALUES (?, ?, ?, ?, ?, ?)',
				                  $row['id'],
				                  $targetUserID,
				                  $_SERVER['REMOTE_ADDR'] ?? null,
				                  $action,
				                  'Admin: ' . $this->adminRole,
				                  $details);
			}
			$res->Free();
		}
		catch(Exception $e)
		{
			// RemoveIP might not be installed
		}
	}
}

?>

