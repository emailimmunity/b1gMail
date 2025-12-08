<?php
/**
 * b1gMail Yubikey OTP Class
 */

if(!defined('B1GMAIL_INIT'))
	exit;

class BMYubikey {
	const API_URL = 'https://api.yubico.com/wsapi/2.0/verify';
	const OTP_LENGTH = 44;
	
	public static function isEnabled() {
		global $bm_prefs;
		return isset($bm_prefs['yubikey_enabled']) && $bm_prefs['yubikey_enabled'] == 'yes';
	}
	
	public static function verify($otp, $userID = null) {
		if(!self::isEnabled() || strlen($otp) !== self::OTP_LENGTH) {
			return false;
		}
		
		$publicID = substr($otp, 0, 12);
		
		if($userID !== null && !self::isKeyRegistered($userID, $publicID)) {
			return false;
		}
		
		PutLog('Yubikey verified: ' . $publicID, PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	public static function registerKey($userID, $otp, $name = 'YubiKey') {
		global $db;
		
		if(!self::verify($otp)) {
			return array('error' => 'Invalid OTP');
		}
		
		$publicID = substr($otp, 0, 12);
		
		$db->Query('INSERT INTO {pre}yubikey_keys (userID, public_id, name, created, last_used, use_count) VALUES(?,?,?,?,?,?)',
			$userID, $publicID, $name, time(), time(), 0);
		
		return array('success' => true);
	}
	
	private static function isKeyRegistered($userID, $publicID) {
		global $db;
		$result = $db->Query('SELECT id FROM {pre}yubikey_keys WHERE userID=? AND public_id=?', $userID, $publicID);
		return $result->FetchArray(MYSQLI_ASSOC) !== false;
	}
	
	public static function getUserKeys($userID) {
		global $db;
		$keys = array();
		$result = $db->Query('SELECT id, public_id, name, created, last_used, use_count FROM {pre}yubikey_keys WHERE userID=? ORDER BY created DESC', $userID);
		while($row = $result->FetchArray(MYSQLI_ASSOC)) {
			$keys[] = $row;
		}
		return $keys;
	}
	
	public static function deleteKey($userID, $keyID) {
		global $db;
		$db->Query('DELETE FROM {pre}yubikey_keys WHERE id=? AND userID=?', $keyID, $userID);
		return true;
	}
	
	public static function deleteAllForUser($userID) {
		global $db;
		$db->Query('DELETE FROM {pre}yubikey_keys WHERE userID=?', $userID);
		PutLog('All Yubikeys deleted for user #' . $userID, PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	public static function createTable() {
		global $db;
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}yubikey_keys (
			id INT PRIMARY KEY AUTO_INCREMENT,
			userID INT NOT NULL,
			public_id VARCHAR(12) NOT NULL,
			name VARCHAR(255) NOT NULL,
			created INT NOT NULL,
			last_used INT DEFAULT 0,
			use_count INT DEFAULT 0,
			INDEX(userID)
		)');
		return true;
	}
}
