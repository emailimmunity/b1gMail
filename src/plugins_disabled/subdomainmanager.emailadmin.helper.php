<?php
/**
 * EmailAdmin Integration Helper für SubDomainManager Plugin
 * 
 * Integriert Subdomains mit dem EmailAdmin Plugin für automatische Email-Verwaltung
 * 
 * @package SubDomainManager
 * @author b1gMail Plugin System
 * @version 1.0
 */

class SubDomainManagerEmailAdminHelper
{
	/**
	 * Registriere Subdomain im EmailAdmin
	 * 
	 * @param string $fullDomain Vollständige Subdomain (z.B. test.gtin.org)
	 * @param int $userId User-ID des Besitzers
	 * @return bool Success
	 */
	public static function registerSubdomainInEmailAdmin($fullDomain, $userId)
	{
		global $db;
		
		// Prüfe ob EmailAdmin Plugin aktiv ist
		if(!self::isEmailAdminActive()) {
			return false;
		}
		
		// Prüfe ob Domain bereits existiert
		$res = $db->Query('SELECT id FROM {pre}emp_domains WHERE domain=?', strtolower($fullDomain));
		if($res->RowCount() > 0) {
			$res->Free();
			return true; // Bereits registriert
		}
		$res->Free();
		
		// Registriere Domain in EmailAdmin
		$db->Query('
			INSERT INTO {pre}emp_domains 
			(domain, mx_valid, mx_last_check, created_at, created_by) 
			VALUES (?, 1, NOW(), NOW(), ?)
		', strtolower($fullDomain), $userId);
		
		PutLog('SubDomainManager: Domain ' . $fullDomain . ' registered in EmailAdmin', PRIO_NOTE, __FILE__, __LINE__);
		
		return true;
	}
	
	/**
	 * Entferne Subdomain aus EmailAdmin
	 * 
	 * @param string $fullDomain Vollständige Subdomain
	 * @return bool Success
	 */
	public static function unregisterSubdomainFromEmailAdmin($fullDomain)
	{
		global $db;
		
		if(!self::isEmailAdminActive()) {
			return false;
		}
		
		// Prüfe ob es eine System-Domain ist (dann NICHT löschen!)
		$res = $db->Query('SELECT domain FROM {pre}domains WHERE domain=?', strtolower($fullDomain));
		if($res->RowCount() > 0) {
			$res->Free();
			return false; // System-Domain, nicht löschen!
		}
		$res->Free();
		
		// Lösche aus EmailAdmin
		$db->Query('DELETE FROM {pre}emp_domains WHERE domain=?', strtolower($fullDomain));
		
		PutLog('SubDomainManager: Domain ' . $fullDomain . ' unregistered from EmailAdmin', PRIO_NOTE, __FILE__, __LINE__);
		
		return true;
	}
	
	/**
	 * Prüfe ob Subdomain bereits Email-Accounts hat
	 * 
	 * @param string $fullDomain Vollständige Subdomain
	 * @return int Anzahl Email-Accounts
	 */
	public static function countEmailAccountsForSubdomain($fullDomain)
	{
		global $db;
		
		// Zähle Users mit dieser Domain
		$res = $db->Query('
			SELECT COUNT(*) as cnt 
			FROM {pre}users 
			WHERE email LIKE ?
		', '%@' . strtolower($fullDomain));
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		return (int)$row['cnt'];
	}
	
	/**
	 * Hole alle Email-Accounts für eine Subdomain
	 * 
	 * @param string $fullDomain Vollständige Subdomain
	 * @return array Liste von Email-Accounts
	 */
	public static function getEmailAccountsForSubdomain($fullDomain)
	{
		global $db;
		
		$accounts = [];
		$res = $db->Query('
			SELECT id, email, vorname, nachname, gesperrt, reg_date, mailspace_used 
			FROM {pre}users 
			WHERE email LIKE ?
			ORDER BY email ASC
		', '%@' . strtolower($fullDomain));
		
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$accounts[] = $row;
		}
		$res->Free();
		
		return $accounts;
	}
	
	/**
	 * Erstelle neuen Email-Account für Subdomain
	 * 
	 * @param string $email Vollständige Email-Adresse
	 * @param string $password Passwort
	 * @param string $vorname Vorname (optional)
	 * @param string $nachname Nachname (optional)
	 * @return mixed User-ID oder false
	 */
	public static function createEmailAccount($email, $password, $vorname = '', $nachname = '')
	{
		global $db, $bm_prefs;
		
		// Validierung
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		
		// Prüfe ob Email bereits existiert
		$res = $db->Query('SELECT id FROM {pre}users WHERE email=?', strtolower($email));
		if($res->RowCount() > 0) {
			$res->Free();
			return false; // Email bereits vergeben
		}
		$res->Free();
		
		// Passwort hashen mit PasswordManager (bcrypt)
		require_once(B1GMAIL_DIR . 'serverlib/password.class.php');
		$hashed = PasswordManager::hash($password, 'bcrypt');
		
		// User erstellen
		$db->Query('
			INSERT INTO {pre}users 
			(email, passwort, password_version, vorname, nachname, gesperrt, reg_date, type) 
			VALUES (?, ?, ?, ?, ?, 0, ?, 1)
		', 
			strtolower($email), 
			$hashed['hash'],
			$hashed['version'],
			$vorname, 
			$nachname,
			time()
		);
		
		$userId = $db->InsertId();
		
		PutLog('SubDomainManager: Email account created: ' . $email, PRIO_NOTE, __FILE__, __LINE__);
		
		return $userId;
	}
	
	/**
	 * Lösche Email-Account
	 * 
	 * @param int $userId User-ID
	 * @return bool Success
	 */
	public static function deleteEmailAccount($userId)
	{
		global $db;
		
		// User löschen (b1gMail hat DeleteUser-Funktion, aber wir machen es einfach)
		$db->Query('UPDATE {pre}users SET gesperrt=1 WHERE id=?', $userId);
		
		PutLog('SubDomainManager: Email account deleted: User ID ' . $userId, PRIO_NOTE, __FILE__, __LINE__);
		
		return true;
	}
	
	/**
	 * Prüfe ob EmailAdmin Plugin aktiv ist
	 * 
	 * @return bool
	 */
	public static function isEmailAdminActive()
	{
		global $db;
		
		// Prüfe ob emp_domains Tabelle existiert
		$res = $db->Query('SHOW TABLES LIKE \'{pre}emp_domains\'');
		$exists = $res->RowCount() > 0;
		$res->Free();
		
		return $exists;
	}
	
	/**
	 * Synchronisiere Subdomain-Email-Count
	 * 
	 * @param int $subdomainId Subdomain-ID
	 */
	public static function syncEmailCount($subdomainId)
	{
		global $db;
		
		// Lade Subdomain
		$res = $db->Query('SELECT full_domain FROM {pre}sdm_subdomains WHERE id=?', $subdomainId);
		if($res->RowCount() == 0) {
			$res->Free();
			return;
		}
		$subdomain = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Zähle Email-Accounts
		$count = self::countEmailAccountsForSubdomain($subdomain['full_domain']);
		
		// Update Subdomain
		$db->Query('UPDATE {pre}sdm_subdomains SET email_count=? WHERE id=?', $count, $subdomainId);
	}
	
	/**
	 * Bulk-Synchronisierung aller Subdomains
	 */
	public static function syncAllEmailCounts()
	{
		global $db;
		
		$res = $db->Query('SELECT id FROM {pre}sdm_subdomains WHERE status="active"');
		while($row = $res->FetchArray(MYSQLI_ASSOC)) {
			self::syncEmailCount($row['id']);
		}
		$res->Free();
	}
}
?>
