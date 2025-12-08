<?php
/**
 * AccountMirror v2.0 Plugin
 * Vollständige Account-Spiegelung (TKÜV/BNetzA-konform)
 * 
 * (c) 2025 b1gMail Project
 * 
 * FEATURES:
 * - Vollständige E-Mail-Spiegelung (Ordner, Flags, Markierungen)
 * - Webdisk-Spiegelung (Dateien & Ordnerstruktur)
 * - Kalender-Spiegelung
 * - Adressbuch-Spiegelung
 * - Bidirektionale Sync (optional)
 * - Initial-Sync für bestehende Daten
 * - KEINE Löschungen (Sicherheit)
 * - TKÜV & BNetzA konform
 */

class AccountMirrorV2Plugin extends BMPlugin
{
	function __construct()
	{
		$this->name					= 'Account Mirror v2.0';
		$this->author				= 'b1gMail Project';
		$this->web					= 'https://www.b1gmail.org/';
		$this->mail					= 'info@b1gmail.org';
		$this->version				= '2.0.0';
		$this->designedfor			= '7.4.0';
		$this->type					= BMPLUGIN_DEFAULT;
		$this->update_url			= 'https://service.b1gmail.org/plugin_updates/';
		$this->website				= 'https://www.b1gmail.org/';

		$this->admin_pages			= true;
		$this->admin_page_title		= 'Account Mirror v2.0';
		$this->admin_page_icon		= 'accountmirror_logo.png';
	}

	function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang)
	{
		if($lang == 'deutsch')
		{
			$lang_admin['am2_title']			= 'Account Mirror v2.0';
			$lang_admin['am2_subtitle']			= 'Vollständige Account-Spiegelung (TKÜV-konform)';
			$lang_admin['am2_notice1']			= 'Diese Funktion unterliegt strengen rechtlichen Vorgaben (TKÜV, BNetzA).';
			$lang_admin['am2_notice2']			= 'Spiegelungen erfolgen NUR bei behördlicher Anordnung oder Überwachungsmaßnahmen.';
			$lang_admin['am2_notice3']			= 'Es werden ALLE Daten gespiegelt: E-Mails, Webdisk, Kalender, Kontakte.';
			$lang_admin['am2_notice4']			= 'Ordnerstrukturen werden 1:1 übernommen.';
			$lang_admin['am2_notice5']			= 'Löschungen werden NICHT gespiegelt (Sicherheit).';
			$lang_admin['am2_source']			= 'Quell-Account';
			$lang_admin['am2_target']			= 'Ziel-Account';
			$lang_admin['am2_reason']			= 'Grund / Rechtsgrundlage';
			$lang_admin['am2_authority']		= 'Behörde';
			$lang_admin['am2_file_number']		= 'Aktenzeichen';
			$lang_admin['am2_mode']				= 'Modus';
			$lang_admin['am2_mode_live']		= 'Live (ab jetzt)';
			$lang_admin['am2_mode_snapshot']	= 'Snapshot (inkl. bestehende Daten)';
			$lang_admin['am2_what_to_mirror']	= 'Was spiegeln?';
			$lang_admin['am2_emails']			= 'E-Mails (inkl. Ordner & Flags)';
			$lang_admin['am2_webdisk']			= 'Webdisk (Dateien & Ordner)';
			$lang_admin['am2_calendar']			= 'Kalender';
			$lang_admin['am2_contacts']			= 'Kontakte';
			$lang_admin['am2_bidirectional']	= 'Bidirektional (Änderungen in beide Richtungen)';
			$lang_admin['am2_active']			= 'Aktiv';
			$lang_admin['am2_inactive']			= 'Inaktiv';
			$lang_admin['am2_stats']			= 'Statistik';
		}
		else
		{
			$lang_admin['am2_title']			= 'Account Mirror v2.0';
			$lang_admin['am2_subtitle']			= 'Complete Account Mirroring (TKÜV-compliant)';
			$lang_admin['am2_notice1']			= 'This function is subject to strict legal requirements (TKÜV, BNetzA).';
			$lang_admin['am2_notice2']			= 'Mirroring ONLY for law enforcement or surveillance measures.';
			$lang_admin['am2_notice3']			= 'ALL data is mirrored: Emails, Webdisk, Calendar, Contacts.';
			$lang_admin['am2_notice4']			= 'Folder structures are preserved 1:1.';
			$lang_admin['am2_notice5']			= 'Deletions are NOT mirrored (security).';
			$lang_admin['am2_source']			= 'Source Account';
			$lang_admin['am2_target']			= 'Target Account';
			$lang_admin['am2_reason']			= 'Reason / Legal Basis';
			$lang_admin['am2_authority']		= 'Authority';
			$lang_admin['am2_file_number']		= 'File Number';
			$lang_admin['am2_mode']				= 'Mode';
			$lang_admin['am2_mode_live']		= 'Live (from now on)';
			$lang_admin['am2_mode_snapshot']	= 'Snapshot (incl. existing data)';
			$lang_admin['am2_what_to_mirror']	= 'What to mirror?';
			$lang_admin['am2_emails']			= 'Emails (incl. folders & flags)';
			$lang_admin['am2_webdisk']			= 'Webdisk (files & folders)';
			$lang_admin['am2_calendar']			= 'Calendar';
			$lang_admin['am2_contacts']			= 'Contacts';
			$lang_admin['am2_bidirectional']	= 'Bidirectional (changes both ways)';
			$lang_admin['am2_active']			= 'Active';
			$lang_admin['am2_inactive']			= 'Inactive';
			$lang_admin['am2_stats']			= 'Statistics';
		}
	}

	function Install()
	{
		global $db;

		// Schema aus Datei ausführen
		$schemaFile = dirname(__FILE__) . '/accountmirror_v2_schema.sql';
		
		if(!file_exists($schemaFile))
		{
			PutLog('AccountMirror v2: Schema-Datei nicht gefunden!', PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
		
		// Schema einlesen
		$sql = file_get_contents($schemaFile);
		global $mysql;
		$sql = str_replace('{PREFIX}', $mysql['prefix'], $sql);
		
		// Entferne DELIMITER statements (nicht kompatibel mit mysqli_query)
		$sql = preg_replace('/DELIMITER\s+.*$/m', '', $sql);
		$sql = str_replace('//', ';', $sql); // Ersetze // durch ;
		
		// Statements ausführen
		$statements = explode(';', $sql);
		foreach($statements as $stmt)
		{
			$stmt = trim($stmt);
			if(empty($stmt) || substr($stmt, 0, 2) == '--')
				continue;
				
			try {
				$db->Query($stmt);
			} catch(Exception $e) {
				PutLog('AccountMirror v2: SQL Error: ' . $e->getMessage(), PRIO_WARNING, __FILE__, __LINE__);
			}
		}
		
		// Audit-Schema auch laden!
		$auditSchemaFile = dirname(__FILE__) . '/accountmirror_v2_audit_schema.sql';
		
		if(file_exists($auditSchemaFile))
		{
			$auditSql = file_get_contents($auditSchemaFile);
			$auditSql = str_replace('{PREFIX}', $mysql['prefix'], $auditSql);
			$auditSql = preg_replace('/DELIMITER\s+.*$/m', '', $auditSql);
			$auditSql = str_replace('//', ';', $auditSql);
			
			$auditStatements = explode(';', $auditSql);
			foreach($auditStatements as $stmt)
			{
				$stmt = trim($stmt);
				if(empty($stmt) || substr($stmt, 0, 2) == '--')
					continue;
					
				try {
					$db->Query($stmt);
				} catch(Exception $e) {
					PutLog('AccountMirror v2 Audit: SQL Error: ' . $e->getMessage(), PRIO_WARNING, __FILE__, __LINE__);
				}
			}
		}
		
		PutLog(sprintf('%s v%s installed - TKÜV/BNetzA-konform',
			$this->name,
			$this->version),
			PRIO_PLUGIN,
			__FILE__,
			__LINE__);

		return true;
	}
	
	function Uninstall()
	{
		global $db;
		
		// Tabellen löschen (VORSICHT!)
		$tables = array(
			'mod_accountmirror_v2',
			'mod_accountmirror_v2_sync_log',
			'mod_accountmirror_v2_folder_map',
			'mod_accountmirror_v2_mail_map',
			'mod_accountmirror_v2_webdisk_map',
			'mod_accountmirror_v2_calendar_map',
			'mod_accountmirror_v2_contact_map'
		);
		
		foreach($tables as $table)
		{
			$db->Query('DROP TABLE IF EXISTS {pre}' . $table);
		}
		
		// Event löschen
		$db->Query('DROP EVENT IF EXISTS cleanup_accountmirror_v2_logs');
		
		return true;
	}

	function AdminHandler()
	{
		global $tpl, $db, $lang_admin;

		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2_AuditManager.class.php');
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2_InformationRequestManager.class.php');

		// Aktiver Tab
		$activeTab = $_REQUEST['tab'] ?? 'mirror';

		$tabs = array(
			0 => array(
				'title'		=> 'Spiegelungen',
				'icon'		=> '../plugins/templates/images/accountmirror_logo.png',
				'link'		=> $this->_adminLink() . '&tab=mirror&',
				'active'	=> ($activeTab == 'mirror')
			),
			1 => array(
				'title'		=> 'Audit-Logs (TKÜV)',
				'icon'		=> '../plugins/templates/images/bms_logging.png',
				'link'		=> $this->_adminLink() . '&tab=audit&',
				'active'	=> ($activeTab == 'audit')
			),
			2 => array(
				'title'		=> 'Auskunftsersuchen',
				'icon'		=> '../plugins/templates/images/bms_show.png',
				'link'		=> $this->_adminLink() . '&tab=requests&',
				'active'	=> ($activeTab == 'requests')
			),
			3 => array(
				'title'		=> 'Jahres-Auswertungen',
				'icon'		=> '../plugins/templates/images/bms_stats.png',
				'link'		=> $this->_adminLink() . '&tab=reports&',
				'active'	=> ($activeTab == 'reports')
			)
		);

		$tpl->assign('pageURL', 	$this->_adminLink());
		$tpl->assign('tabs', 		$tabs);
		
		// Tab-spezifische Handler
		switch($activeTab)
		{
			case 'audit':
				return $this->handleAuditTab();
				
			case 'requests':
				return $this->handleRequestsTab();
				
			case 'reports':
				return $this->handleReportsTab();
				
			case 'mirror':
			default:
				return $this->handleMirrorTab();
		}

		// Neue Spiegelung hinzufügen
		if(isset($_REQUEST['add']))
		{
			$params = array(
				'userid' => BMUser::GetID($_POST['email_source']),
				'mirror_to' => BMUser::GetID($_POST['email_target']),
				'reason' => $_POST['reason'],
				'authority' => $_POST['authority'] ?? null,
				'file_number' => $_POST['file_number'] ?? null,
				'created_by' => $_SESSION['b1gmailadmin'],
				'begin' => isset($_POST['begin_now']) ? time() : SmartyDateTime('begin'),
				'end' => isset($_POST['end_unlimited']) ? 0 : SmartyDateTime('end'),
				'mirror_mode' => $_POST['mirror_mode'] ?? 'live',
				'include_existing' => ($_POST['mirror_mode'] == 'snapshot') ? 1 : 0,
				'bidirectional' => isset($_POST['bidirectional']) ? 1 : 0,
				'mirror_emails' => isset($_POST['mirror_emails']) ? 1 : 0,
				'mirror_folders' => isset($_POST['mirror_folders']) ? 1 : 0,
				'mirror_flags' => isset($_POST['mirror_flags']) ? 1 : 0,
				'mirror_webdisk' => isset($_POST['mirror_webdisk']) ? 1 : 0,
				'mirror_calendar' => isset($_POST['mirror_calendar']) ? 1 : 0,
				'mirror_contacts' => isset($_POST['mirror_contacts']) ? 1 : 0,
				'mirror_deletions' => isset($_POST['mirror_deletions']) ? 1 : 0
			);
			
			$mirror = new AccountMirrorV2();
			$result = $mirror->createMirroring($params);
			
			if($result['success'])
			{
				$tpl->assign('msgText', 'Spiegelung erstellt (ID: ' . $result['mirrorid'] . ')');
				$tpl->assign('msgTitle', 'Erfolg');
				$tpl->assign('msgIcon', 'success32');
			}
			else
			{
				$tpl->assign('msgText', $result['message']);
				$tpl->assign('msgTitle', $lang_admin['error']);
				$tpl->assign('msgIcon', 'error32');
			}
		}

		// Spiegelung deaktivieren
		if(isset($_REQUEST['deactivate']))
		{
			$mirror = new AccountMirrorV2((int)$_REQUEST['deactivate']);
			$mirror->deactivate();
		}

		// Spiegelung löschen
		if(isset($_REQUEST['delete']))
		{
			$db->Query('DELETE FROM {pre}mod_accountmirror_v2 WHERE mirrorid=?',
				(int)$_REQUEST['delete']);
			
			// Logs auch löschen
			$db->Query('DELETE FROM {pre}mod_accountmirror_v2_sync_log WHERE mirrorid=?',
				(int)$_REQUEST['delete']);
			$db->Query('DELETE FROM {pre}mod_accountmirror_v2_folder_map WHERE mirrorid=?',
				(int)$_REQUEST['delete']);
			$db->Query('DELETE FROM {pre}mod_accountmirror_v2_mail_map WHERE mirrorid=?',
				(int)$_REQUEST['delete']);
		}

		// Spiegelungen auflisten
		$mirrorings = array();
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 ORDER BY created_at DESC');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$source = _new('BMUser', array($row['userid']));
			$target = _new('BMUser', array($row['mirror_to']));
			
			$sourceRow = $source->Fetch();
			$targetRow = $target->Fetch();

			$row['source_email'] = $sourceRow['email'] ?? 'Unbekannt';
			$row['target_email'] = $targetRow['email'] ?? 'Unbekannt';

			$mirrorings[] = $row;
		}
		$res->Free();

		$tpl->assign('mirrorings', $mirrorings);
		$tpl->assign('page', $this->_templatePath('accountmirror_v2.main.tpl'));
	}
	
	/**
	 * HOOK: Nach dem Speichern einer E-Mail
	 * Automatische Live-Spiegelung
	 */
	function AfterStoreMail($mailID, &$mail, &$mailbox)
	{
		global $db;

		if(!is_object($mailbox) || !is_object($mail))
			return;

		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');

		// Aktive Spiegelungen für diesen User suchen
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 
		                   WHERE userid=? AND active=1 AND mirror_emails=1 
		                   AND (begin <= UNIX_TIMESTAMP() OR begin=0) 
		                   AND (end >= UNIX_TIMESTAMP() OR end=0)',
			$mailbox->_userID);
		
		if($res->RowCount() < 1)
		{
			$res->Free();
			return;
		}

		$folderID = $mail->_row['folder'] ?? FOLDER_INBOX;

		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mirror = new AccountMirrorV2($row['mirrorid']);
			$result = $mirror->mirrorEmail($mailID, $folderID);
			
			if(!$result['success'])
			{
				PutLog(sprintf('AccountMirror v2: Fehler bei Spiegelung #%d (Mail %d): %s',
					$row['mirrorid'], $mailID, $result['message']),
					PRIO_WARNING,
					__FILE__,
					__LINE__);
			}
		}
		$res->Free();
	}
	
	/**
	 * HOOK: Nach Folder-Änderung
	 * Ordner-Spiegelung
	 */
	function OnFolderCreated($folderID, $userID)
	{
		global $db;
		
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');
		
		// Aktive Spiegelungen suchen
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 
		                   WHERE userid=? AND active=1 AND mirror_emails=1',
		                   $userID);
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mirror = new AccountMirrorV2($row['mirrorid']);
			// Ordner werden automatisch bei E-Mail-Spiegelung mit-gespiegelt (Mapping)
			PutLog(sprintf('AccountMirror v2: Folder #%d created for mirroring #%d', 
			               $folderID, $row['mirrorid']),
			       PRIO_NOTE, __FILE__, __LINE__);
		}
		$res->Free();
	}
	
	/**
	 * HOOK: Nach Webdisk-Upload
	 * Webdisk-Spiegelung
	 */
	function OnWebdiskFileUpload($fileID, $userID)
	{
		global $db;
		
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');
		
		// Aktive Spiegelungen mit Webdisk-Option suchen
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 
		                   WHERE userid=? AND active=1 AND mirror_webdisk=1 
		                   AND (begin <= UNIX_TIMESTAMP() OR begin=0) 
		                   AND (end >= UNIX_TIMESTAMP() OR end=0)',
		                   $userID);
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mirror = new AccountMirrorV2($row['mirrorid']);
			$result = $mirror->mirrorWebdiskFile($fileID);
			
			if(!$result['success'])
			{
				PutLog(sprintf('AccountMirror v2: Fehler bei Webdisk-Spiegelung #%d (File %d): %s',
				               $row['mirrorid'], $fileID, $result['message']),
				       PRIO_WARNING, __FILE__, __LINE__);
			}
			else
			{
				PutLog(sprintf('AccountMirror v2: Webdisk-Datei #%d gespiegelt (Spiegelung #%d)',
				               $fileID, $row['mirrorid']),
				       PRIO_NOTE, __FILE__, __LINE__);
			}
		}
		$res->Free();
	}
	
	/**
	 * HOOK: Nach Kalender-Event
	 * Kalender-Spiegelung
	 */
	function OnCalendarEventCreated($eventID, $userID)
	{
		global $db;
		
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');
		
		// Aktive Spiegelungen mit Kalender-Option suchen
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 
		                   WHERE userid=? AND active=1 AND mirror_calendar=1 
		                   AND (begin <= UNIX_TIMESTAMP() OR begin=0) 
		                   AND (end >= UNIX_TIMESTAMP() OR end=0)',
		                   $userID);
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mirror = new AccountMirrorV2($row['mirrorid']);
			$result = $mirror->mirrorCalendarEvent($eventID);
			
			if(!$result['success'])
			{
				PutLog(sprintf('AccountMirror v2: Fehler bei Kalender-Spiegelung #%d (Event %d): %s',
				               $row['mirrorid'], $eventID, $result['message']),
				       PRIO_WARNING, __FILE__, __LINE__);
			}
			else
			{
				PutLog(sprintf('AccountMirror v2: Kalender-Event #%d gespiegelt (Spiegelung #%d)',
				               $eventID, $row['mirrorid']),
				       PRIO_NOTE, __FILE__, __LINE__);
			}
		}
		$res->Free();
	}
	
	/**
	 * HOOK: Nach Kontakt-Erstellung
	 * Kontakte-Spiegelung
	 */
	function OnContactCreated($contactID, $userID)
	{
		global $db;
		
		require_once(dirname(__FILE__) . '/classes/AccountMirrorV2.class.php');
		
		// Aktive Spiegelungen mit Kontakte-Option suchen
		$res = $db->Query('SELECT * FROM {pre}mod_accountmirror_v2 
		                   WHERE userid=? AND active=1 AND mirror_contacts=1 
		                   AND (begin <= UNIX_TIMESTAMP() OR begin=0) 
		                   AND (end >= UNIX_TIMESTAMP() OR end=0)',
		                   $userID);
		
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mirror = new AccountMirrorV2($row['mirrorid']);
			$result = $mirror->mirrorContact($contactID);
			
			if(!$result['success'])
			{
				PutLog(sprintf('AccountMirror v2: Fehler bei Kontakte-Spiegelung #%d (Contact %d): %s',
				               $row['mirrorid'], $contactID, $result['message']),
				       PRIO_WARNING, __FILE__, __LINE__);
			}
			else
			{
				PutLog(sprintf('AccountMirror v2: Kontakt #%d gespiegelt (Spiegelung #%d)',
				               $contactID, $row['mirrorid']),
				       PRIO_NOTE, __FILE__, __LINE__);
			}
		}
		$res->Free();
	}
	
	/**
	 * TAB HANDLER: Spiegelungen
	 */
	private function handleMirrorTab()
	{
		global $tpl, $db, $lang_admin;
		
		// Bestehender Code bleibt hier
		$tpl->assign('page', $this->_templatePath('accountmirror_v2.main.tpl'));
	}
	
	/**
	 * TAB HANDLER: Audit-Logs
	 */
	private function handleAuditTab()
	{
		global $tpl, $db;
		
		$auditManager = new AccountMirrorV2_AuditManager();
		
		// Jahres-Auswertung manuell generieren
		if(isset($_REQUEST['generate_year']))
		{
			$year = (int)$_REQUEST['year'];
			$result = $auditManager->generateYearlyReport($year, $_SESSION['b1gmailadmin']);
			
			if($result['success'])
			{
				$tpl->assign('msgText', 'Jahres-Auswertung für ' . $year . ' generiert');
				$tpl->assign('msgTitle', 'Erfolg');
				$tpl->assign('msgIcon', 'success32');
			}
		}
		
		// Filter
		$filters = array();
		$mirrorid = 0;
		$year = date('Y');
		
		if(isset($_GET['mirrorid'])) {
			$filters['mirrorid'] = (int)$_GET['mirrorid'];
			$mirrorid = (int)$_GET['mirrorid'];
		}
		if(isset($_GET['year'])) {
			$filters['year'] = (int)$_GET['year'];
			$year = (int)$_GET['year'];
		}
			
		// Audit-Logs abrufen
		$auditLogs = $auditManager->getAllAuditLogs($filters);
		
		// Compliance-Check
		$compliance = $auditManager->checkCompliance();
		
		// Template-Variablen zuweisen (FIX für Undefined array key)
		$tpl->assign('mirrorid', $mirrorid);
		$tpl->assign('year', $year);
		$tpl->assign('audit_logs', $auditLogs);
		$tpl->assign('compliance', $compliance);
		$tpl->assign('page', $this->_templatePath('accountmirror_v2.audit.tpl'));
	}
	
	/**
	 * TAB HANDLER: Auskunftsersuchen
	 */
	private function handleRequestsTab()
	{
		global $tpl;
		
		$requestManager = new AccountMirrorV2_InformationRequestManager();
		
		// Neues Ersuchen erstellen
		if(isset($_REQUEST['add_request']))
		{
			$params = array(
				'request_number' => $_POST['request_number'],
				'authority' => $_POST['authority'],
				'authority_contact' => $_POST['authority_contact'] ?? null,
				'request_type' => $_POST['request_type'],
				'legal_basis' => $_POST['legal_basis'],
				'file_number' => $_POST['file_number'] ?? null,
				'target_email' => $_POST['target_email'] ?? null,
				'mirrorid' => $_POST['mirrorid'] ?? null,
				'request_period_from' => isset($_POST['period_from']) ? SmartyDateTime('period_from') : null,
				'request_period_to' => isset($_POST['period_to']) ? SmartyDateTime('period_to') : null,
				'deadline' => isset($_POST['deadline']) ? SmartyDateTime('deadline') : null,
				'priority' => $_POST['priority'] ?? 'normal',
				'notes' => $_POST['notes'] ?? null,
				'created_by' => $_SESSION['b1gmailadmin']
			);
			
			$result = $requestManager->createRequest($params);
			
			if($result['success'])
			{
				$tpl->assign('msgText', 'Auskunftsersuchen erstellt (ID: ' . $result['request_id'] . ')');
				$tpl->assign('msgTitle', 'Erfolg');
				$tpl->assign('msgIcon', 'success32');
			}
			else
			{
				$tpl->assign('msgText', $result['message']);
				$tpl->assign('msgTitle', 'Fehler');
				$tpl->assign('msgIcon', 'error32');
			}
		}
		
		// Auskunft generieren
		if(isset($_REQUEST['generate_response']))
		{
			$requestId = (int)$_REQUEST['request_id'];
			$result = $requestManager->generateInformationResponse($requestId, $_SESSION['b1gmailadmin']);
			
			if($result['success'])
			{
				$tpl->assign('msgText', 'Auskunft generiert');
				$tpl->assign('msgTitle', 'Erfolg');
				$tpl->assign('msgIcon', 'success32');
			}
		}
		
		// CSV-Export
		if(isset($_REQUEST['export_csv']))
		{
			$requestId = (int)$_REQUEST['request_id'];
			$requestManager->exportAsCSV($requestId);
			// Script endet hier (exit in exportAsCSV)
		}
		
		// Status ändern
		if(isset($_REQUEST['change_status']))
		{
			$requestId = (int)$_REQUEST['request_id'];
			$newStatus = $_POST['new_status'];
			$requestManager->updateStatus($requestId, $newStatus, $_SESSION['b1gmailadmin']);
		}
		
		// Liste abrufen
		$requests = $requestManager->listRequests();
		
		$tpl->assign('requests', $requests);
		$tpl->assign('page', $this->_templatePath('accountmirror_v2.requests.tpl'));
	}
	
	/**
	 * TAB HANDLER: Jahres-Auswertungen
	 */
	private function handleReportsTab()
	{
		global $tpl;
		
		$auditManager = new AccountMirrorV2_AuditManager();
		
		// Auswertung generieren
		if(isset($_REQUEST['generate']))
		{
			$year = (int)$_REQUEST['year'];
			$result = $auditManager->generateYearlyReport($year, $_SESSION['b1gmailadmin']);
			
			if($result['success'])
			{
				$tpl->assign('msgText', 'Jahres-Auswertung für ' . $year . ' generiert');
				$tpl->assign('msgTitle', 'Erfolg');
				$tpl->assign('msgIcon', 'success32');
			}
		}
		
		// Alle Jahres-Auswertungen
		$reports = $auditManager->listYearlyReports();
		
		$tpl->assign('reports', $reports);
		$tpl->assign('page', $this->_templatePath('accountmirror_v2.reports.tpl'));
	}
}

/**
 * Plugin registrieren
 */
$plugins->registerPlugin('AccountMirrorV2Plugin');

?>
