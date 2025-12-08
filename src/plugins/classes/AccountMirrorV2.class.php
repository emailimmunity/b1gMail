<?php
/**
 * AccountMirror v2.0 - Vollständige Account-Spiegelung
 * 
 * GESETZESKONFORMITÄT:
 * - TKÜV § 5 Abs. 2 (Telekommunikations-Überwachungsverordnung)
 * - BVerfG Az. 2 BvR 2377/16
 * - BNetzA-Vorgaben für E-Mail-Überwachung
 * 
 * FEATURES:
 * - Vollständige E-Mail-Spiegelung (inkl. Ordnerstruktur, Flags)
 * - Webdisk-Spiegelung (Dateien & Ordner)
 * - Kalender-Spiegelung
 * - Adressbuch-Spiegelung
 * - Bidirektionale Synchronisation (optional)
 * - Bestehende Daten spiegeln (Snapshot)
 * - KEINE Löschungen (außer explizit aktiviert)
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class AccountMirrorV2
{
	private $db;
	private $mirrorId;
	private $config;
	
	public function __construct($mirrorId = null)
	{
		global $db;
		$this->db = $db;
		$this->mirrorId = $mirrorId;
		
		if($mirrorId)
		{
			$this->loadConfig();
		}
	}
	
	/**
	 * Konfiguration laden
	 */
	private function loadConfig()
	{
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2 WHERE mirrorid = ?', 
		                         $this->mirrorId);
		
		if($res->RowCount() > 0)
		{
			$this->config = $res->FetchArray(MYSQLI_ASSOC);
		}
		else
		{
			$this->config = null;
		}
		
		$res->Free();
	}
	
	/**
	 * Spiegelung erstellen
	 */
	public function createMirroring($params)
	{
		$required = array('userid', 'mirror_to', 'reason', 'created_by');
		foreach($required as $field)
		{
			if(!isset($params[$field]))
			{
				return array('success' => false, 'message' => "Feld $field fehlt");
			}
		}
		
		// Validierung
		if($params['userid'] == $params['mirror_to'])
		{
			return array('success' => false, 'message' => 'Account kann nicht sich selbst spiegeln');
		}
		
		// Prüfen ob Ziel-Account bereits Quelle ist
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2 
		                         WHERE userid = ? AND active = 1',
		                         $params['mirror_to']);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if($row['cnt'] > 0)
		{
			return array('success' => false, 'message' => 'Ziel-Account ist bereits Quelle einer Spiegelung');
		}
		
		// Defaults
		$defaults = array(
			'begin' => time(),
			'end' => 0,
			'authority' => null,
			'file_number' => null,
			'mirror_mode' => 'live',
			'bidirectional' => 0,
			'include_existing' => 0,
			'mirror_emails' => 1,
			'mirror_folders' => 1,
			'mirror_flags' => 1,
			'mirror_webdisk' => 0,
			'mirror_calendar' => 0,
			'mirror_contacts' => 0,
			'mirror_deletions' => 0,
			'active' => 1
		);
		
		$params = array_merge($defaults, $params);
		
		// Einfügen
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2 
		                  (userid, mirror_to, begin, end, reason, authority, file_number,
		                   mirror_mode, bidirectional, include_existing,
		                   mirror_emails, mirror_folders, mirror_flags, mirror_webdisk,
		                   mirror_calendar, mirror_contacts, mirror_deletions,
		                   created_by, active) 
		                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
		                  $params['userid'],
		                  $params['mirror_to'],
		                  $params['begin'],
		                  $params['end'],
		                  $params['reason'],
		                  $params['authority'],
		                  $params['file_number'],
		                  $params['mirror_mode'],
		                  $params['bidirectional'],
		                  $params['include_existing'],
		                  $params['mirror_emails'],
		                  $params['mirror_folders'],
		                  $params['mirror_flags'],
		                  $params['mirror_webdisk'],
		                  $params['mirror_calendar'],
		                  $params['mirror_contacts'],
		                  $params['mirror_deletions'],
		                  $params['created_by'],
		                  $params['active']);
		
		$mirrorId = $this->db->InsertId();
		
		// Log
		PutLog(sprintf('AccountMirror v2: Spiegelung #%d erstellt (User %d → %d, Grund: %s)',
		               $mirrorId, $params['userid'], $params['mirror_to'], $params['reason']),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		// AUDIT-LOG (TKÜV-Pflicht!)
		require_once(dirname(__FILE__) . '/AccountMirrorV2_AuditManager.class.php');
		$auditManager = new AccountMirrorV2_AuditManager();
		$auditManager->logEvent($mirrorId, 'created', array(
			'source_userid' => $params['userid'],
			'target_userid' => $params['mirror_to'],
			'reason' => $params['reason'],
			'authority' => $params['authority'],
			'file_number' => $params['file_number'],
			'mirror_mode' => $params['mirror_mode'],
			'features' => array(
				'emails' => $params['mirror_emails'],
				'folders' => $params['mirror_folders'],
				'flags' => $params['mirror_flags'],
				'webdisk' => $params['mirror_webdisk'],
				'calendar' => $params['mirror_calendar'],
				'contacts' => $params['mirror_contacts']
			)
		), $params['reason'] . ($params['authority'] ? ' | Behörde: ' . $params['authority'] : ''));
		
		// Wenn include_existing aktiviert: Initial-Sync starten
		if($params['include_existing'])
		{
			$mirror = new AccountMirrorV2($mirrorId);
			$mirror->initialSync();
		}
		
		return array(
			'success' => true,
			'mirrorid' => $mirrorId,
			'message' => 'Spiegelung erstellt'
		);
	}
	
	/**
	 * Initial-Sync (bestehende Daten spiegeln)
	 */
	public function initialSync()
	{
		if(!$this->config || !$this->config['include_existing'])
		{
			return array('success' => false, 'message' => 'Initial-Sync nicht aktiviert');
		}
		
		$stats = array(
			'emails' => 0,
			'folders' => 0,
			'webdisk_files' => 0,
			'calendar_events' => 0,
			'contacts' => 0,
			'errors' => 0
		);
		
		// 1. Ordnerstruktur spiegeln
		if($this->config['mirror_folders'])
		{
			$stats['folders'] = $this->syncFolders();
		}
		
		// 2. E-Mails spiegeln
		if($this->config['mirror_emails'])
		{
			$stats['emails'] = $this->syncExistingEmails();
		}
		
		// 3. Webdisk spiegeln
		if($this->config['mirror_webdisk'])
		{
			$stats['webdisk_files'] = $this->syncWebdisk();
		}
		
		// 4. Kalender spiegeln
		if($this->config['mirror_calendar'])
		{
			$stats['calendar_events'] = $this->syncCalendar();
		}
		
		// 5. Kontakte spiegeln
		if($this->config['mirror_contacts'])
		{
			$stats['contacts'] = $this->syncContacts();
		}
		
		// Last-Sync aktualisieren
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2 SET last_sync = NOW() WHERE mirrorid = ?',
		                 $this->mirrorId);
		
		PutLog(sprintf('AccountMirror v2: Initial-Sync #%d abgeschlossen (%d Mails, %d Ordner, %d Webdisk, %d Kalender, %d Kontakte)',
		               $this->mirrorId, $stats['emails'], $stats['folders'], 
		               $stats['webdisk_files'], $stats['calendar_events'], $stats['contacts']),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		return array('success' => true, 'stats' => $stats);
	}
	
	/**
	 * Ordnerstruktur spiegeln
	 */
	private function syncFolders()
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Ordner des Quell-Accounts holen
		$res = $this->db->Query('SELECT * FROM {pre}folders WHERE userid = ? ORDER BY parent, id', 
		                         $sourceUserId);
		
		$count = 0;
		$folderMap = array(); // parent_id mapping
		
		while($folder = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Prüfen ob Ordner bereits existiert (via Mapping)
			$mapRes = $this->db->Query('SELECT target_folder_id FROM {pre}mod_accountmirror_v2_folder_map 
			                             WHERE mirrorid = ? AND source_folder_id = ?',
			                             $this->mirrorId, $folder['id']);
			
			if($mapRes->RowCount() > 0)
			{
				$mapRow = $mapRes->FetchArray(MYSQLI_ASSOC);
				$folderMap[$folder['id']] = $mapRow['target_folder_id'];
				$mapRes->Free();
				continue;
			}
			$mapRes->Free();
			
			// Parent-ID anpassen
			$targetParent = 0;
			if($folder['parent'] > 0 && isset($folderMap[$folder['parent']]))
			{
				$targetParent = $folderMap[$folder['parent']];
			}
			
			// Neuen Ordner im Ziel-Account erstellen
			$this->db->Query('INSERT INTO {pre}folders (userid, parent, title, subscribed) 
			                  VALUES (?, ?, ?, ?)',
			                  $targetUserId,
			                  $targetParent,
			                  $folder['title'] . ' (Mirror)',
			                  $folder['subscribed']);
			
			$newFolderId = $this->db->InsertId();
			$folderMap[$folder['id']] = $newFolderId;
			
			// Mapping speichern
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_folder_map 
			                  (mirrorid, source_folder_id, source_folder_name, target_folder_id, target_folder_name) 
			                  VALUES (?, ?, ?, ?, ?)',
			                  $this->mirrorId,
			                  $folder['id'],
			                  $folder['title'],
			                  $newFolderId,
			                  $folder['title'] . ' (Mirror)');
			
			$count++;
		}
		$res->Free();
		
		return $count;
	}
	
	/**
	 * Bestehende E-Mails spiegeln
	 */
	private function syncExistingEmails()
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Alle Mails des Quell-Accounts
		$res = $this->db->Query('SELECT id, folder FROM {pre}mails WHERE userid = ?', 
		                         $sourceUserId);
		
		$count = 0;
		
		while($mail = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Mail spiegeln
			$result = $this->mirrorEmail($mail['id'], $mail['folder']);
			
			if($result['success'])
			{
				$count++;
			}
		}
		$res->Free();
		
		return $count;
	}
	
	/**
	 * Einzelne E-Mail spiegeln
	 */
	public function mirrorEmail($mailId, $sourceFolderId)
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Prüfen ob bereits gespiegelt
		$mapRes = $this->db->Query('SELECT target_mail_id FROM {pre}mod_accountmirror_v2_mail_map 
		                             WHERE mirrorid = ? AND source_mail_id = ?',
		                             $this->mirrorId, $mailId);
		
		if($mapRes->RowCount() > 0)
		{
			$mapRes->Free();
			return array('success' => false, 'message' => 'Bereits gespiegelt');
		}
		$mapRes->Free();
		
		// Quell-Mailbox öffnen
		$sourceUser = _new('BMUser', array($sourceUserId));
		$sourceUserRow = $sourceUser->Fetch();
		$sourceMailbox = _new('BMMailbox', array($sourceUserId, $sourceUserRow['email'], $sourceUser));
		
		// Mail holen
		$mailObj = $sourceMailbox->GetMail($mailId);
		
		if(!$mailObj || !is_object($mailObj))
		{
			$this->logSync('email', $mailId, null, 'create', 'error', 'Mail nicht gefunden');
			return array('success' => false, 'message' => 'Mail nicht gefunden');
		}
		
		// Ziel-Folder ermitteln
		$targetFolderId = $this->getTargetFolderId($sourceFolderId);
		
		// Ziel-Mailbox öffnen
		$targetUser = _new('BMUser', array($targetUserId));
		$targetUserRow = $targetUser->Fetch();
		$targetMailbox = _new('BMMailbox', array($targetUserId, $targetUserRow['email'], $targetUser));
		
		// Mail speichern
		$storeResult = $targetMailbox->StoreMail($mailObj, $targetFolderId);
		
		if($storeResult != STORE_RESULT_OK)
		{
			$this->logSync('email', $mailId, null, 'create', 'error', 'Speichern fehlgeschlagen: ' . $storeResult);
			$this->incrementErrorCount();
			return array('success' => false, 'message' => 'Speichern fehlgeschlagen');
		}
		
		// Neue Mail-ID ermitteln (letzte eingefügte)
		$newMailRes = $this->db->Query('SELECT id FROM {pre}mails WHERE userid = ? ORDER BY id DESC LIMIT 1',
		                                 $targetUserId);
		$newMailRow = $newMailRes->FetchArray(MYSQLI_ASSOC);
		$newMailId = $newMailRow['id'];
		$newMailRes->Free();
		
		// Flags synchronisieren
		if($this->config['mirror_flags'])
		{
			$this->syncMailFlags($mailId, $newMailId);
		}
		
		// Mapping speichern
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_mail_map 
		                  (mirrorid, source_mail_id, target_mail_id, source_folder_id, target_folder_id, flags_synced) 
		                  VALUES (?, ?, ?, ?, ?, ?)',
		                  $this->mirrorId, $mailId, $newMailId, $sourceFolderId, $targetFolderId, 1);
		
		// Statistik
		$this->incrementMailCount();
		
		// Log
		$this->logSync('email', $mailId, $newMailId, 'create', 'success', null);
		
		return array('success' => true, 'target_mail_id' => $newMailId);
	}
	
	/**
	 * Mail-Flags synchronisieren
	 */
	private function syncMailFlags($sourceMailId, $targetMailId)
	{
		// Flags der Quell-Mail holen
		$res = $this->db->Query('SELECT flagged, answered, deleted, draft, seen FROM {pre}mails WHERE id = ?',
		                         $sourceMailId);
		
		if($res->RowCount() == 0)
		{
			$res->Free();
			return;
		}
		
		$flags = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Auf Ziel-Mail übertragen
		$this->db->Query('UPDATE {pre}mails 
		                  SET flagged = ?, answered = ?, deleted = ?, draft = ?, seen = ? 
		                  WHERE id = ?',
		                  $flags['flagged'],
		                  $flags['answered'],
		                  $flags['deleted'],
		                  $flags['draft'],
		                  $flags['seen'],
		                  $targetMailId);
	}
	
	/**
	 * Ziel-Folder-ID ermitteln (via Mapping)
	 */
	private function getTargetFolderId($sourceFolderId)
	{
		// System-Ordner (negativ) bleiben gleich
		if($sourceFolderId <= 0)
		{
			return $sourceFolderId;
		}
		
		// Mapping suchen
		$res = $this->db->Query('SELECT target_folder_id FROM {pre}mod_accountmirror_v2_folder_map 
		                         WHERE mirrorid = ? AND source_folder_id = ?',
		                         $this->mirrorId, $sourceFolderId);
		
		if($res->RowCount() > 0)
		{
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$res->Free();
			return $row['target_folder_id'];
		}
		
		$res->Free();
		
		// Fallback: INBOX
		return FOLDER_INBOX;
	}
	
	/**
	 * Webdisk spiegeln
	 */
	private function syncWebdisk()
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// 1. Ordnerstruktur kopieren
		$res = $this->db->Query('SELECT * FROM {pre}webdisk WHERE userid=? AND `type`=\'folder\' ORDER BY id ASC',
		                         $sourceUserId);
		
		$folderMap = array(); // Source ID => Target ID
		
		while($folder = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Parent-Ordner mappen
			$targetParent = ($folder['parent'] > 0 && isset($folderMap[$folder['parent']])) 
			                ? $folderMap[$folder['parent']] 
			                : 0;
			
			// Ordner im Ziel-Account erstellen
			$this->db->Query('INSERT INTO {pre}webdisk (userid, `type`, parent, name, created) 
			                  VALUES (?, \'folder\', ?, ?, ?)',
			                  $targetUserId, $targetParent, $folder['name'], time());
			
			$targetFolderId = $this->db->InsertID();
			$folderMap[$folder['id']] = $targetFolderId;
			
			// Mapping speichern
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_webdisk_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $folder['id'], $targetFolderId);
		}
		$res->Free();
		
		// 2. Dateien kopieren
		$res = $this->db->Query('SELECT * FROM {pre}webdisk WHERE userid=? AND `type`=\'file\' ORDER BY id ASC',
		                         $sourceUserId);
		
		$fileCount = 0;
		
		while($file = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Parent-Ordner mappen
			$targetParent = ($file['parent'] > 0 && isset($folderMap[$file['parent']])) 
			                ? $folderMap[$file['parent']] 
			                : 0;
			
			// Datei-Pfad ermitteln
			$sourcePath = B1GMAIL_DIR . 'webdisk/' . $sourceUserId . '/' . $file['id'];
			
			if(!file_exists($sourcePath))
				continue;
			
			// Datei im Ziel-Account erstellen
			$this->db->Query('INSERT INTO {pre}webdisk (userid, `type`, parent, name, size, created, modified) 
			                  VALUES (?, \'file\', ?, ?, ?, ?, ?)',
			                  $targetUserId, $targetParent, $file['name'], 
			                  $file['size'], $file['created'], $file['modified']);
			
			$targetFileId = $this->db->InsertID();
			
			// Datei kopieren
			$targetPath = B1GMAIL_DIR . 'webdisk/' . $targetUserId . '/' . $targetFileId;
			@mkdir(dirname($targetPath), 0777, true);
			copy($sourcePath, $targetPath);
			
			// Mapping speichern
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_webdisk_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $file['id'], $targetFileId);
			
			$fileCount++;
		}
		$res->Free();
		
		$this->logSync('webdisk_sync', 0, 0, 'initial_sync', 'success', "Synced $fileCount files");
		
		return $fileCount;
	}
	
	/**
	 * Kalender spiegeln
	 */
	private function syncCalendar()
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Kalender-Events kopieren
		$res = $this->db->Query('SELECT * FROM {pre}organizer WHERE userid=? AND type=\'event\'', $sourceUserId);
		
		$eventCount = 0;
		while($event = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Event im Ziel-Account erstellen
			$this->db->Query('INSERT INTO {pre}organizer 
			                  (userid, type, title, text, location, `from`, until, 
			                   allday, color, priority, alarm, recurrence, created, changed) 
			                  VALUES (?, \'event\', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			                  $targetUserId, $event['title'], $event['text'], 
			                  $event['location'], $event['from'], $event['until'],
			                  $event['allday'], $event['color'], $event['priority'],
			                  $event['alarm'], $event['recurrence'], 
			                  $event['created'], $event['changed']);
			
			$targetEventId = $this->db->InsertID();
			
			// Mapping speichern
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_calendar_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $event['id'], $targetEventId);
			
			$eventCount++;
		}
		$res->Free();
		
		$this->logSync('calendar_sync', 0, 0, 'initial_sync', 'success', "Synced $eventCount events");
		
		return $eventCount;
	}
	
	/**
	 * Kontakte spiegeln
	 */
	private function syncContacts()
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Adressbuch-Einträge kopieren
		$res = $this->db->Query('SELECT * FROM {pre}adressen WHERE userid=?', $sourceUserId);
		
		$contactCount = 0;
		while($contact = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Kontakt im Ziel-Account erstellen
			$this->db->Query('INSERT INTO {pre}adressen 
			                  (userid, vorname, nachname, email, firma, strasse, 
			                   plz, ort, land, tel, fax, mobil, web, notizen, typ) 
			                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			                  $targetUserId, 
			                  $contact['vorname'], $contact['nachname'], $contact['email'],
			                  $contact['firma'], $contact['strasse'], $contact['plz'],
			                  $contact['ort'], $contact['land'], $contact['tel'],
			                  $contact['fax'], $contact['mobil'], $contact['web'],
			                  $contact['notizen'], $contact['typ']);
			
			$targetContactId = $this->db->InsertID();
			
			// Mapping speichern
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_contact_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $contact['id'], $targetContactId);
			
			$contactCount++;
		}
		$res->Free();
		
		$this->logSync('contacts_sync', 0, 0, 'initial_sync', 'success', "Synced $contactCount contacts");
		
		return $contactCount;
	}
	
	/**
	 * Sync loggen
	 */
	private function logSync($syncType, $sourceId, $targetId, $action, $status, $errorMessage = null)
	{
		// IP-Adresse (TKÜV-Pflicht bei Überwachung)
		$ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_sync_log 
		                  (mirrorid, sync_type, source_id, target_id, action, status, error_message, ip_address) 
		                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
		                  $this->mirrorId, $syncType, $sourceId, $targetId, $action, $status, $errorMessage, $ipAddress);
	}
	
	/**
	 * Statistiken erhöhen
	 */
	private function incrementMailCount()
	{
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2 SET mail_count = mail_count + 1 WHERE mirrorid = ?',
		                 $this->mirrorId);
	}
	
	private function incrementErrorCount()
	{
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2 SET error_count = error_count + 1 WHERE mirrorid = ?',
		                 $this->mirrorId);
	}
	
	/**
	 * Einzelne Webdisk-Datei spiegeln (für Hook)
	 */
	public function mirrorWebdiskFile($fileId)
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		// Datei-Info laden
		$res = $this->db->Query('SELECT * FROM {pre}webdisk WHERE id=? AND userid=?', $fileId, $sourceUserId);
		if($res->RowCount() == 0)
		{
			$res->Free();
			return array('success' => false, 'message' => 'File not found');
		}
		
		$file = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Parent-Ordner im Ziel ermitteln
		$targetParent = 0;
		if($file['parent'] > 0)
		{
			$resMap = $this->db->Query('SELECT target_id FROM {pre}mod_accountmirror_v2_webdisk_map 
			                             WHERE mirrorid=? AND source_id=?', 
			                             $this->mirrorId, $file['parent']);
			if($resMap->RowCount() > 0)
			{
				$map = $resMap->FetchArray(MYSQLI_ASSOC);
				$targetParent = $map['target_id'];
			}
			$resMap->Free();
		}
		
		// Datei spiegeln
		if($file['type'] == 'file')
		{
			$sourcePath = B1GMAIL_DIR . 'webdisk/' . $sourceUserId . '/' . $fileId;
			
			if(!file_exists($sourcePath))
				return array('success' => false, 'message' => 'Source file does not exist');
			
			$this->db->Query('INSERT INTO {pre}webdisk (userid, `type`, parent, name, size, created, modified) 
			                  VALUES (?, \'file\', ?, ?, ?, ?, ?)',
			                  $targetUserId, $targetParent, $file['name'], 
			                  $file['size'], $file['created'], time());
			
			$targetFileId = $this->db->InsertID();
			
			$targetPath = B1GMAIL_DIR . 'webdisk/' . $targetUserId . '/' . $targetFileId;
			@mkdir(dirname($targetPath), 0777, true);
			copy($sourcePath, $targetPath);
			
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_webdisk_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $fileId, $targetFileId);
			
			$this->logSync('webdisk_file', $fileId, $targetFileId, 'mirror', 'success', null);
		}
		elseif($file['type'] == 'folder')
		{
			$this->db->Query('INSERT INTO {pre}webdisk (userid, `type`, parent, name, created) 
			                  VALUES (?, \'folder\', ?, ?, ?)',
			                  $targetUserId, $targetParent, $file['name'], time());
			
			$targetFolderId = $this->db->InsertID();
			
			$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_webdisk_map 
			                  (mirrorid, source_id, target_id, sync_time) 
			                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
			                  $this->mirrorId, $fileId, $targetFolderId);
			
			$this->logSync('webdisk_folder', $fileId, $targetFolderId, 'mirror', 'success', null);
		}
		
		return array('success' => true, 'message' => 'Webdisk item mirrored');
	}
	
	/**
	 * Einzelnes Kalender-Event spiegeln (für Hook)
	 */
	public function mirrorCalendarEvent($eventId)
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		$res = $this->db->Query('SELECT * FROM {pre}organizer WHERE id=? AND userid=? AND type=\'event\'', 
		                         $eventId, $sourceUserId);
		if($res->RowCount() == 0)
		{
			$res->Free();
			return array('success' => false, 'message' => 'Event not found');
		}
		
		$event = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$this->db->Query('INSERT INTO {pre}organizer 
		                  (userid, type, title, text, location, `from`, until, 
		                   allday, color, priority, alarm, recurrence, created, changed) 
		                  VALUES (?, \'event\', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
		                  $targetUserId, $event['title'], $event['text'], 
		                  $event['location'], $event['from'], $event['until'],
		                  $event['allday'], $event['color'], $event['priority'],
		                  $event['alarm'], $event['recurrence'], 
		                  $event['created'], time());
		
		$targetEventId = $this->db->InsertID();
		
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_calendar_map 
		                  (mirrorid, source_id, target_id, sync_time) 
		                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
		                  $this->mirrorId, $eventId, $targetEventId);
		
		$this->logSync('calendar_event', $eventId, $targetEventId, 'mirror', 'success', null);
		
		return array('success' => true, 'message' => 'Calendar event mirrored');
	}
	
	/**
	 * Einzelnen Kontakt spiegeln (für Hook)
	 */
	public function mirrorContact($contactId)
	{
		$sourceUserId = $this->config['userid'];
		$targetUserId = $this->config['mirror_to'];
		
		$res = $this->db->Query('SELECT * FROM {pre}adressen WHERE id=? AND userid=?', 
		                         $contactId, $sourceUserId);
		if($res->RowCount() == 0)
		{
			$res->Free();
			return array('success' => false, 'message' => 'Contact not found');
		}
		
		$contact = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$this->db->Query('INSERT INTO {pre}adressen 
		                  (userid, vorname, nachname, email, firma, strasse, 
		                   plz, ort, land, tel, fax, mobil, web, notizen, typ) 
		                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
		                  $targetUserId, 
		                  $contact['vorname'], $contact['nachname'], $contact['email'],
		                  $contact['firma'], $contact['strasse'], $contact['plz'],
		                  $contact['ort'], $contact['land'], $contact['tel'],
		                  $contact['fax'], $contact['mobil'], $contact['web'],
		                  $contact['notizen'], $contact['typ']);
		
		$targetContactId = $this->db->InsertID();
		
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_contact_map 
		                  (mirrorid, source_id, target_id, sync_time) 
		                  VALUES (?, ?, ?, UNIX_TIMESTAMP())',
		                  $this->mirrorId, $contactId, $targetContactId);
		
		$this->logSync('contact', $contactId, $targetContactId, 'mirror', 'success', null);
		
		return array('success' => true, 'message' => 'Contact mirrored');
	}
	
	/**
	 * Spiegelung deaktivieren
	 */
	public function deactivate()
	{
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2 SET active = 0 WHERE mirrorid = ?',
		                 $this->mirrorId);
		
		PutLog(sprintf('AccountMirror v2: Spiegelung #%d deaktiviert', $this->mirrorId),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		// AUDIT-LOG (TKÜV-Pflicht!)
		require_once(dirname(__FILE__) . '/AccountMirrorV2_AuditManager.class.php');
		$auditManager = new AccountMirrorV2_AuditManager();
		$auditManager->logEvent($this->mirrorId, 'deactivated', array(
			'action' => 'manual_deactivation'
		));
		
		return array('success' => true, 'message' => 'Spiegelung deaktiviert');
	}
}

?>
