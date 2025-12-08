<?php
/**
 * b1gMail User.class.php - Password V2 Integration Patch
 * 
 * This file shows the required changes to user.class.php for:
 * - bcrypt migration
 * - MFA integration  
 * - Admin impersonation compatibility
 * 
 * INTEGRATION INSTRUCTIONS:
 * 1. Include password.class.php and totp.class.php at top of user.class.php
 * 2. Replace Login() function with the version below
 * 3. Add getMFAStatus() helper function
 * 
 * (c) 2025 b1gMail Project
 */

// ═══════════════════════════════════════════════════════════════
// ADD THESE INCLUDES AT TOP OF user.class.php (after B1GMAIL_INIT check)
// ═══════════════════════════════════════════════════════════════

/*
require_once(B1GMAIL_DIR . 'serverlib/password.class.php');
require_once(B1GMAIL_DIR . 'serverlib/totp.class.php');
*/

// ═══════════════════════════════════════════════════════════════
// REPLACE EXISTING Login() FUNCTION WITH THIS VERSION
// ═══════════════════════════════════════════════════════════════

/**
 * login a user (PASSWORD V2 EDITION)
 *
 * @param string $email E-Mail
 * @param string $passwordPlain Password (PLAIN)
 * @param bool $createSession Create session?
 * @param bool $successLog Log successful logins?
 * @return string Session-ID
 */
public static function Login_V2($email, $passwordPlain, $createSession = true, $successLog = true, $ValidationCode = '', $skipSalting = false)
{
	global $db, $currentCharset, $currentLanguage, $bm_prefs;

	$passwordPlain = CharsetDecode($passwordPlain, false, 'ISO-8859-15');
	$result = array(USER_DOES_NOT_EXIST, false);
	$row = false;
	$userID = 0;
	
	// ═══════════════════════════════════════════════════════════════
	// CHANGE 1: Don't hash password yet (handle in PasswordManager)
	// ═══════════════════════════════════════════════════════════════
	// OLD: $password = LooksLikeMD5Hash($passwordPlain) ? $passwordPlain : md5($passwordPlain);
	// NEW: Keep plaintext for now
	$password = $passwordPlain;

	// try plugin authentication first
	$pluginAuth = BMUser::_pluginAuth($email, $password, $passwordPlain);

	// no plugin auth
	if(!is_array($pluginAuth))
	{
		// get user ID
		$userID = BMUser::GetID($email,false,$isAlias);
		$aliaslogin='no';
		if($isAlias === true)
		{
			$res = $db->Query('SELECT login FROM {pre}aliase WHERE user=? AND email=? LIMIT 1',
			$userID,$email);
			list($aliaslogin) = $res->FetchArray(MYSQLI_NUM);
			if($aliaslogin=='no') $userID=0;
		}
		
		// ═══════════════════════════════════════════════════════════════
		// CHANGE 2: Also select password_version for hybrid verification
		// ═══════════════════════════════════════════════════════════════
		$res = $db->Query('SELECT id,gesperrt,passwort,passwort_salt,password_version,email,last_login_attempt,sms_validation_code,ip,lastlogin,preferred_language,last_timezone 
		                   FROM {pre}users WHERE id=? LIMIT 1',
			$userID);
		$row = $res->FetchArray();
		$res->Free();
	}
	else
	{
		// plugin auth (keep as is for now)
		$res = $db->Query('SELECT id,gesperrt,passwort,passwort_salt,password_version,email,last_login_attempt,sms_validation_code,ip,lastlogin,preferred_language,last_timezone 
		                   FROM {pre}users WHERE uid=? LIMIT 1',
			$pluginAuth['uid']);
		if($res->RowCount() == 1)
		{
			$row = $res->FetchArray();
			$res->Free();
			
			// For plugin auth, use MD5 for now
			$row['passwort'] = md5(md5($password).$row['passwort_salt']);
			$userID = $row['id'];

			if(isset($pluginAuth['profile']) && $row['gesperrt'] == 'no')
			{
				$theOldUserRow = $theUserRow = BMUser::staticFetch($row['id']);
				$theUserRow['passwort'] = md5($password.$row['passwort_salt']);
				foreach($pluginAuth['profile'] as $key=>$val)
					$theUserRow[$key] = $val;
				if($theOldUserRow != $theUserRow)
					BMUser::UpdateContactData($theUserRow, false, true, $userID);
			}
		}
	}

	if(isset($row) && $userID > 0)
	{
		// ═══════════════════════════════════════════════════════════════
		// CHANGE 3: Admin impersonation (bcrypt-compatible!)
		// ═══════════════════════════════════════════════════════════════
		$adminAuthOK = false;
		$adminID = null;
		
		if(isset($_REQUEST['adminAuth']))
		{
			$adminAuth = @explode(',', @base64_decode($_REQUEST['adminAuth']));

			if(is_array($adminAuth) && count($adminAuth) == 3 && $adminAuth[0] == $userID)
			{
				$ares = $db->Query('SELECT adminid, password, password_version FROM {pre}admins WHERE adminid=?', 
				                   $adminAuth[1]);
				$arow = $ares->FetchArray(MYSQLI_ASSOC);
				$ares->Free();
				
				if($arow)
				{
					// Check admin privileges
					$adminPrivs = @unserialize($arow['privileges']);
					if(!is_array($adminPrivs)) $adminPrivs = array();
					
					if($arow['type'] == 0 || in_array('users', $adminPrivs))
					{
						// Calculate token (support both MD5 and bcrypt admin passwords!)
						$adminPasswordHash = ($arow['password_version'] == 2) 
							? $arow['password']  // bcrypt hash
							: md5($arow['password']);  // Legacy MD5
						
						$correctToken = md5(sprintf('%d,%d', $userID, $adminAuth[1])
						                   . $adminPasswordHash
						                   . $_SERVER['HTTP_USER_AGENT']);

						if($correctToken === $adminAuth[2])
						{
							$adminAuthOK = true;
							$adminID = $adminAuth[1];
							
							// Log admin impersonation (TKÜV!)
							PutLog(sprintf('Admin #%d logged in as user #%d (%s) via impersonation',
							              $adminID, $userID, $email),
							       PRIO_NOTE,
							       __FILE__,
							       __LINE__);
						}
					}
				}
			}
		}

		// ═══════════════════════════════════════════════════════════════
		// CHANGE 4: Password verification (hybrid: MD5 OR bcrypt)
		// ═══════════════════════════════════════════════════════════════
		
		$passwordValid = false;
		$skipMigration = false;
		
		if($adminAuthOK)
		{
			// Admin impersonation: BYPASS password check!
			$passwordValid = true;
			$skipMigration = true;  // Don't migrate (admin doesn't know password)
		}
		else if($skipSalting)
		{
			// Legacy: Skip salting
			$passwordValid = (strtolower($row['passwort']) === strtolower($passwordPlain));
		}
		else
		{
			// HYBRID PASSWORD VERIFICATION!
			try
			{
				$passwordValid = PasswordManager::verify(
					$passwordPlain,
					$row['passwort'],
					$row['password_version'] ?? 1,
					$row['passwort_salt']
				);
			}
			catch(Exception $e)
			{
				// Fallback to MD5 if PasswordManager fails
				PutLog('PasswordManager verification failed, falling back to MD5: ' . $e->getMessage(),
				       PRIO_WARNING, __FILE__, __LINE__);
				
				$password = LooksLikeMD5Hash($passwordPlain) ? $passwordPlain : md5($passwordPlain);
				$saltedPassword = md5($password . $row['passwort_salt']);
				$passwordValid = (strtolower($row['passwort']) === strtolower($saltedPassword));
			}
		}

		// user exists and password valid
		if($passwordValid && ($row['last_login_attempt'] < 100 || $row['last_login_attempt']+ACCOUNT_LOCK_TIME < time()))
		{
			// validation unlock?
			if($ValidationCode != '' && BMUser::RequiresValidation($email))
			{
				if(BMUser::ActivateAccount($userID, $ValidationCode))
					$row['gesperrt'] = 'no';
			}

			// password ok
			if($row['gesperrt'] == 'no')
			{
				// ═══════════════════════════════════════════════════════════════
				// CHANGE 5: Automatic password migration (Lazy Upgrade!)
				// ═══════════════════════════════════════════════════════════════
				
				if(!$skipMigration 
				   && isset($row['password_version']) 
				   && $row['password_version'] == 1 
				   && isset($bm_prefs['password_v2_enabled']) 
				   && $bm_prefs['password_v2_enabled'])
				{
					try
					{
						// Migrate password from MD5 to bcrypt!
						PasswordManager::migrate($userID, $passwordPlain);
						
						// Re-fetch user row (password changed!)
						$res = $db->Query('SELECT passwort, password_version FROM {pre}users WHERE id=?', $userID);
						$newRow = $res->FetchArray(MYSQLI_ASSOC);
						$res->Free();
						
						$row['passwort'] = $newRow['passwort'];
						$row['password_version'] = $newRow['password_version'];
					}
					catch(Exception $e)
					{
						// Migration failed, but login still succeeds
						PutLog('Password migration failed during login: ' . $e->getMessage(),
						       PRIO_WARNING, __FILE__, __LINE__);
					}
				}
				
				// ═══════════════════════════════════════════════════════════════
				// CHANGE 6: MFA Check (if enabled)
				// ═══════════════════════════════════════════════════════════════
				
				// Check if MFA is required
				$mfaRequired = false;
				$mfaMethod = null;
				
				if(!$adminAuthOK)  // Admin impersonation BYPASSES MFA!
				{
					try
					{
						// Check if user has MFA enabled
						if(class_exists('BMTOTP') && BMTOTP::isMFAEnabled($userID))
						{
							// Check if already verified in this session
							if(!isset($_SESSION['mfa_verified']) || $_SESSION['mfa_verified'] !== $userID)
							{
								$mfaRequired = true;
								$mfaMethod = BMTOTP::getPreferredMFAMethod($userID);
							}
						}
					}
					catch(Exception $e)
					{
						// MFA check failed (table might not exist yet)
						PutLog('MFA check failed: ' . $e->getMessage(), 
						       PRIO_WARNING, __FILE__, __LINE__);
					}
				}
				
				if($mfaRequired)
				{
					// MFA REQUIRED! Don't complete login yet.
					
					// Set pending session
					@session_start();
					$_SESSION['mfa_pending_userid'] = $userID;
					$_SESSION['mfa_pending_email'] = $email;
					$_SESSION['mfa_pending_password'] = $passwordPlain;
					$_SESSION['mfa_required_method'] = $mfaMethod;
					
					// Return special code for MFA required
					return array(USER_MFA_REQUIRED, session_id());
				}

				// Language handling
				if(isset($row['preferred_language']) && !empty($row['preferred_language']))
					$userLanguage = $row['preferred_language'];
				else
					$userLanguage = false;

				$availableLanguages = GetAvailableLanguages();
				if(!isset($availableLanguages[$userLanguage]))
					$userLanguage = false;

				// Update user row
				$db->Query('UPDATE {pre}users SET ip=?,lastlogin=?,last_login_attempt=0,charset=?,language=?,last_timezone=? WHERE id=?',
					$adminAuthOK ? $row['ip'] : $_SERVER['REMOTE_ADDR'],
					$adminAuthOK ? $row['lastlogin'] : time(),
					$currentCharset,
					$userLanguage ? $userLanguage : $currentLanguage,
					isset($_SESSION['bm_timezone']) ? (int)$_SESSION['bm_timezone'] : (isset($_REQUEST['timezone']) ? $_REQUEST['timezone'] : $row['last_timezone']),
					$userID);

				// create session
				if($createSession)
				{
					@session_start();
					$sessionID = session_id();

					if($bm_prefs['cookie_lock'] == 'yes')
					{
						$sessionSecret = GenerateRandomKey('sessionSecret');
						setcookie('sessionSecret_'.substr($sessionID, 0, 16), $sessionSecret, 0, '/');
						$_COOKIE['sessionSecret_'.substr($sessionID, 0, 16)] = $sessionSecret;
					}

					$_SESSION['bm_userLoggedIn']	= true;
					$_SESSION['bm_userID']			= $userID;
					$_SESSION['bm_loginTime']		= time();
					$_SESSION['bm_sessionToken']	= SessionToken();
					$_SESSION['bm_xorCryptKey']		= BMUser::GenerateXORCryptKey($userID, $passwordPlain);
					
					// Mark MFA as verified (either completed or not required)
					$_SESSION['mfa_verified'] = $userID;
					
					// Mark if admin impersonation
					if($adminAuthOK)
					{
						$_SESSION['admin_impersonation'] = true;
						$_SESSION['admin_impersonation_id'] = $adminID;
					}

					if($userLanguage)
						$_SESSION['bm_sessionLanguage'] = $userLanguage;
				}
				else
					$sessionID = $userID;

				// set result
				$result = array(USER_OK, $sessionID);
				ModuleFunction('OnLogin', array($userID));
			}
			else
			{
				// locked
				$result = array(USER_LOCKED, false);
				ModuleFunction('OnLoginFailed', array($email, $password, BM_LOCKED));
			}
		}
		else
		{
			// bad password or login lock
			$result = array(USER_BAD_PASSWORD, false);
			ModuleFunction('OnLoginFailed', array($email, $password, BM_WRONGLOGIN));

			// bruteforce login protection
			$lastLoginAttempt = $row['last_login_attempt'];
			if($lastLoginAttempt < 100)
			{
				$result = array(USER_BAD_PASSWORD, $lastLoginAttempt+1);
				if(++$lastLoginAttempt >= 5)
					$lastLoginAttempt = time();
				$db->Query('UPDATE {pre}users SET last_login_attempt=? WHERE id=?',
					$lastLoginAttempt,
					$userID);
			}
			else
			{
				$lockedUntil = $lastLoginAttempt + ACCOUNT_LOCK_TIME;
				if($lockedUntil < time())
				{
					$db->Query('UPDATE {pre}users SET last_login_attempt=? WHERE id=?',
						1,
						$userID);
					$result = array(USER_BAD_PASSWORD, 1);
				}
				else
				{
					$result = array(USER_LOGIN_BLOCK, $lockedUntil);
				}
			}
		}
	}

	return $result;
}

// ═══════════════════════════════════════════════════════════════
// ADD THESE CONSTANTS TO THE TOP OF user.class.php OR constants.php
// ═══════════════════════════════════════════════════════════════

/*
define('USER_MFA_REQUIRED', 10);  // User needs to complete MFA
*/

// ═══════════════════════════════════════════════════════════════
// ADD THIS HELPER FUNCTION TO BMUser CLASS
// ═══════════════════════════════════════════════════════════════

/**
 * Get MFA status for user
 * 
 * @param int $userID User ID
 * @return array MFA status
 */
public static function getMFAStatus($userID)
{
	$status = array(
		'enabled' => false,
		'methods' => array(),
		'preferred_method' => null
	);
	
	try
	{
		// Check TOTP
		if(class_exists('BMTOTP'))
		{
			$totp = new BMTOTP($userID);
			if($totp->isEnabled())
			{
				$status['enabled'] = true;
				$status['methods'][] = 'totp';
			}
		}
		
		// Check WebAuthn
		global $db;
		$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}webauthn_credentials WHERE userid = ?',
		                  $userID);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if($row['cnt'] > 0)
		{
			$status['enabled'] = true;
			$status['methods'][] = 'passkey';
		}
		
		// Get preferred method
		if($status['enabled'])
		{
			$status['preferred_method'] = BMTOTP::getPreferredMFAMethod($userID);
		}
	}
	catch(Exception $e)
	{
		// MFA not available yet
	}
	
	return $status;
}

// ═══════════════════════════════════════════════════════════════
// MODIFY GetPOP3Account() FOR CREDENTIALS DECRYPTION
// ═══════════════════════════════════════════════════════════════

/**
 * get pop3 account (PASSWORD V2 EDITION)
 *
 * @param int $id
 * @return array
 */
public function GetPOP3Account_V2($id)
{
	global $db;

	$res = $db->Query('SELECT id,p_host,p_user,p_pass,p_pass_iv,p_pass_tag,p_pass_encrypted,p_pass_version,p_target,p_port,p_keep,last_fetch,p_ssl,paused 
	                   FROM {pre}pop3 
	                   WHERE id=? AND user=?',
		$id,
		$this->_id);
	
	if($res->RowCount() == 0)
		return(false);
		
	$row = $res->FetchArray(MYSQLI_ASSOC);
	$res->Free();

	// ═══════════════════════════════════════════════════════════════
	// HYBRID DECRYPT: Works with BOTH plaintext AND encrypted!
	// ═══════════════════════════════════════════════════════════════
	
	try
	{
		$password = PasswordManager::decryptField($row, 'p_pass', 'pop3');
	}
	catch(Exception $e)
	{
		// Decryption failed, use plaintext (fallback)
		$password = $row['p_pass'];
		
		PutLog('POP3 password decryption failed, using plaintext: ' . $e->getMessage(),
		       PRIO_WARNING, __FILE__, __LINE__);
	}

	$result = array(
			'id'			=> $row['id'],
			'p_host'		=> $row['p_host'],
			'p_user'		=> $row['p_user'],
			'p_pass'		=> $password,  // Always plaintext returned
			'p_target'		=> $row['p_target'],
			'p_port'		=> $row['p_port'],
			'p_keep'		=> $row['p_keep'] == 'yes',
			'p_ssl'			=> $row['p_ssl'] == 'yes',
			'last_fetch'	=> $row['last_fetch'],
			'paused'		=> $row['paused'] == 'yes'
		);
	return($result);
}

?>

