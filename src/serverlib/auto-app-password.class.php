<?php
/**
 * b1gMail Auto App-Password Generator
 * 
 * MACHT ES SUPER BEQUEM WIE BEI GOOGLE!
 * 
 * Flow:
 * 1. User versucht IMAP-Login mit Hauptpasswort
 * 2. System erkennt: User hat 2FA, aber kein App-Passwort
 * 3. System generiert AUTOMATISCH App-Passwort
 * 4. System sendet Email an User mit Info
 * 5. User kann in Einstellungen sehen & verwalten
 * 
 * (c) 2025 b1gMail Project
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class BMAutoAppPassword
{
	/**
	 * Check if auto-generation should happen
	 * 
	 * Called when user tries to login via IMAP/POP3/SMTP with main password
	 * but has 2FA enabled
	 * 
	 * @param int $userID User ID
	 * @param string $protocol Protocol (imap, pop3, smtp)
	 * @param string $clientInfo Client info (User-Agent, IP)
	 * @return array|false App password data or false
	 */
	public static function autoGenerate($userID, $protocol, $clientInfo = array())
	{
		global $db, $bm_prefs;
		
		// Check if auto-generation is enabled
		if(!isset($bm_prefs['auto_app_password_enabled']) || !$bm_prefs['auto_app_password_enabled'])
		{
			return false;
		}
		
		// Check if user has 2FA enabled
		require_once('totp.class.php');
		if(!BMTOTP::isMFAEnabled($userID))
		{
			return false; // No 2FA = no app password needed
		}
		
		// Check if auto-generated app password already exists for this client
		$deviceFingerprint = self::getDeviceFingerprint($clientInfo);
		
		$res = $db->Query('SELECT id FROM {pre}app_passwords 
		                   WHERE userid = ? 
		                   AND device_fingerprint = ? 
		                   AND revoked = 0',
		                   $userID,
		                   $deviceFingerprint);
		
		if($res->RowCount() > 0)
		{
			$res->Free();
			return false; // Already has app password for this device
		}
		$res->Free();
		
		// Generate app password!
		require_once('app-passwords.class.php');
		$appPW = new BMAppPasswords($userID);
		
		// Auto-detect device name from User-Agent
		$deviceName = self::detectDeviceName($clientInfo);
		
		// Auto-detect required scopes
		$scopes = self::detectRequiredScopes($protocol);
		
		$result = $appPW->generate($deviceName, $scopes);
		
		// Save device fingerprint
		$db->Query('UPDATE {pre}app_passwords 
		            SET device_fingerprint = ?,
		                auto_generated = 1
		            WHERE id = ?',
		            $deviceFingerprint,
		            $result['id']);
		
		// Send notification to user
		self::sendNotification($userID, $deviceName, $result['formatted']);
		
		PutLog(sprintf('Auto-generated app password for user #%d (device: %s, protocol: %s)',
		              $userID, $deviceName, $protocol),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		return $result;
	}
	
	/**
	 * Send notification to user about new app password
	 * 
	 * @param int $userID User ID
	 * @param string $deviceName Device name
	 * @param string $appPassword Formatted app password
	 */
	private static function sendNotification($userID, $deviceName, $appPassword)
	{
		global $bm_prefs, $lang_custom;
		
		$user = _new('BMUser', array($userID));
		$userRow = $user->Fetch();
		
		if(!$userRow) return;
		
		// Email-Benachrichtigung
		$subject = 'Neues Gerät verbunden: ' . $deviceName;
		
		$message = "Hallo " . $userRow['vorname'] . ",\n\n";
		$message .= "ein neues Gerät wurde mit Ihrem E-Mail-Konto verbunden:\n\n";
		$message .= "Gerät: " . $deviceName . "\n";
		$message .= "Zeit: " . date('d.m.Y H:i') . "\n";
		$message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unbekannt') . "\n\n";
		$message .= "Da Sie 2-Faktor-Authentifizierung aktiviert haben, wurde automatisch\n";
		$message .= "ein App-Passwort für dieses Gerät generiert:\n\n";
		$message .= "App-Passwort: " . $appPassword . "\n\n";
		$message .= "Sie können dieses Passwort jederzeit in Ihren Einstellungen unter\n";
		$message .= "'Sicherheit' → 'Verbundene Geräte' widerrufen.\n\n";
		$message .= "War das nicht Sie?\n";
		$message .= "→ Ändern Sie sofort Ihr Passwort!\n";
		$message .= "→ Widerrufen Sie alle App-Passwörter!\n\n";
		$message .= "Link: " . $bm_prefs['selfurl'] . "prefs.php?action=security\n\n";
		$message .= "--\n";
		$message .= "Diese E-Mail wurde automatisch erstellt.\n";
		
		SystemMail(
			$bm_prefs['passmail_abs'],
			$userRow['email'],
			$subject,
			$message
		);
		
		// Push-Benachrichtigung
		$user->PostNotification(
			'new_device_connected',
			array($deviceName),
			'openSecuritySettings()',
			'%%tpldir%%images/li/security.png',
			time(),
			0,
			NOTIFICATION_FLAG_USELANG
		);
	}
	
	/**
	 * Detect device name from client info
	 * 
	 * @param array $clientInfo Client info
	 * @return string Device name
	 */
	private static function detectDeviceName($clientInfo)
	{
		$userAgent = $clientInfo['user_agent'] ?? '';
		
		// Mail-Clients erkennen
		if(stripos($userAgent, 'thunderbird') !== false)
		{
			return 'Mozilla Thunderbird';
		}
		if(stripos($userAgent, 'outlook') !== false)
		{
			return 'Microsoft Outlook';
		}
		if(stripos($userAgent, 'apple mail') !== false || stripos($userAgent, 'mail.app') !== false)
		{
			return 'Apple Mail';
		}
		if(stripos($userAgent, 'k-9') !== false)
		{
			return 'K-9 Mail (Android)';
		}
		if(stripos($userAgent, 'aquamail') !== false)
		{
			return 'AquaMail';
		}
		
		// OS erkennen
		if(stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false)
		{
			return 'iOS Mail';
		}
		if(stripos($userAgent, 'android') !== false)
		{
			return 'Android Mail';
		}
		if(stripos($userAgent, 'windows') !== false)
		{
			return 'Windows Mail';
		}
		
		// Fallback
		return 'Unbekanntes Gerät (' . substr($userAgent, 0, 30) . ')';
	}
	
	/**
	 * Detect required scopes from protocol
	 * 
	 * @param string $protocol Protocol
	 * @return array Scopes
	 */
	private static function detectRequiredScopes($protocol)
	{
		// Meistens braucht ein Mail-Client: IMAP + SMTP
		// Manchmal auch: CalDAV + CardDAV
		
		$scopes = array($protocol);
		
		// Wenn IMAP, dann auch SMTP (User will auch senden!)
		if($protocol === 'imap')
		{
			$scopes[] = 'smtp';
		}
		
		// Wenn CalDAV, dann auch CardDAV (zusammen)
		if($protocol === 'caldav')
		{
			$scopes[] = 'carddav';
		}
		
		return $scopes;
	}
	
	/**
	 * Generate device fingerprint
	 * 
	 * @param array $clientInfo Client info
	 * @return string Fingerprint
	 */
	private static function getDeviceFingerprint($clientInfo)
	{
		$data = implode('|', array(
			$clientInfo['user_agent'] ?? '',
			$clientInfo['ip'] ?? '',
			$clientInfo['protocol'] ?? ''
		));
		
		return md5($data);
	}
}

?>

