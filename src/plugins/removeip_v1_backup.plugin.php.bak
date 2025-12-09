<?php
/*
 * b1gMail removeIP plugin (TKÜV-konform)
 * (c) 2021-2025 Patrick Schlangen et al
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * GESETZESKONFORMITÄT:
 * - TKÜV § 5 Abs. 2 (Telekommunikations-Überwachungsverordnung)
 * - BVerfG Az. 2 BvR 2377/16 (Urteil vom 20.12.2018)
 * - Bundesnetzagentur-Vorgaben für E-Mail-Überwachung
 *
 */
class RemoveIPPlugin extends BMPlugin 
{
	private $realIP = null;
	
	public function __construct()
	{
		$this->type				= BMPLUGIN_DEFAULT;
		$this->name				= 'RemoveIP Plugin (TKÜV)';
		$this->author			= 'b1gMail Project';
		$this->version			= '2.0.0';
		
		$this->admin_pages			= true;
		$this->admin_page_title		= 'IP Überwachung (TKÜV)';
		$this->admin_page_icon		= 'bms_logging.png'; // KORRIGIERT: Nur Dateiname, Pfad wird automatisch hinzugefügt
	}
	
	/**
	 * Installation - DB-Tabellen erstellen
	 */
	public function Install()
	{
		global $db;
		
		// Überwachungsmaßnahmen-Tabelle
		$databaseStructure = [
			'bm60_mod_removeip_surveillance' => [
				'fields' => [
					['id', 'int(11)', 'NO', 'PRI', NULL, 'auto_increment'],
					['userid', 'int(11)', 'NO', 'MUL', NULL, ''],
					['email', 'varchar(255)', 'NO', '', '', ''],
					['reason', 'varchar(500)', 'NO', '', '', ''],
					['authority', 'varchar(255)', 'NO', '', '', ''],
					['file_number', 'varchar(100)', 'NO', '', '', ''],
					['created_at', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''],
					['created_by', 'int(11)', 'NO', '', NULL, ''],
					['valid_from', 'datetime', 'NO', '', NULL, ''],
					['valid_until', 'datetime', 'YES', '', NULL, ''],
					['active', 'tinyint(1)', 'NO', '', '1', '']
				],
				'indexes' => [
					'PRIMARY' => ['id'],
					'userid' => ['userid'],
					'email' => ['email'],
					'active' => ['active']
				]
			],
			'bm60_mod_removeip_logs' => [
				'fields' => [
					['id', 'int(11)', 'NO', 'PRI', NULL, 'auto_increment'],
					['surveillance_id', 'int(11)', 'NO', 'MUL', NULL, ''],
					['userid', 'int(11)', 'NO', '', NULL, ''],
					['email', 'varchar(255)', 'NO', '', '', ''],
					['ip_address', 'varchar(45)', 'NO', '', '', ''],
					['action', 'varchar(100)', 'NO', '', '', ''],
					['timestamp', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''],
					['user_agent', 'text', 'YES', '', NULL, ''],
					['request_uri', 'text', 'YES', '', NULL, '']
				],
				'indexes' => [
					'PRIMARY' => ['id'],
					'surveillance_id' => ['surveillance_id'],
					'userid' => ['userid'],
					'timestamp' => ['timestamp']
				]
			]
		];
		
		SyncDBStruct($databaseStructure);
		
		PutLog(sprintf('%s v%s installed - TKÜV-konform',
			$this->name,
			$this->version),
			PRIO_PLUGIN,
			__FILE__,
			__LINE__);
		
		return true;
	}
	
	/**
	 * AfterInit - IP-Handling
	 */
	public function AfterInit()
	{
		global $bm_prefs, $db;
		
		// Echte IP speichern
		$this->realIP = $_SERVER['REMOTE_ADDR'];
		
		// Prüfen ob User unter Überwachung steht
		$isUnderSurveillance = false;
		$surveillanceID = null;
		
		if(isset($_SESSION['b1gmailuser']) && $_SESSION['b1gmailuser'] > 0)
		{
			$userID = (int)$_SESSION['b1gmailuser'];
			
			// Überwachung via User-ID prüfen
			$res = $db->Query('SELECT id FROM {pre}mod_removeip_surveillance 
			                   WHERE userid=? AND active=1 
			                   AND (valid_from <= NOW() AND (valid_until IS NULL OR valid_until >= NOW()))',
			                   $userID);
			
			if($res->RowCount() > 0)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				$isUnderSurveillance = true;
				$surveillanceID = $row['id'];
			}
			$res->Free();
			
			// Überwachung via E-Mail prüfen (falls nicht schon gefunden)
			if(!$isUnderSurveillance)
			{
				$user = _new('BMUser', array($userID));
				$userRow = $user->Fetch();
				
				if($userRow)
				{
					$res = $db->Query('SELECT id FROM {pre}mod_removeip_surveillance 
					                   WHERE email=? AND active=1 
					                   AND (valid_from <= NOW() AND (valid_until IS NULL OR valid_until >= NOW()))',
					                   $userRow['email']);
					
					if($res->RowCount() > 0)
					{
						$row = $res->FetchArray(MYSQLI_ASSOC);
						$isUnderSurveillance = true;
						$surveillanceID = $row['id'];
						
						// User-ID in Überwachung nachtragen falls leer
						$db->Query('UPDATE {pre}mod_removeip_surveillance SET userid=? WHERE id=? AND userid IS NULL',
						           $userID, $surveillanceID);
					}
					$res->Free();
				}
			}
		}
		
		// Prüfen ob Account Mirror aktiv ist
		$isMirrored = false;
		if(isset($_SESSION['b1gmailuser']) && $_SESSION['b1gmailuser'] > 0)
		{
			$userID = (int)$_SESSION['b1gmailuser'];
			
			// Prüfen ob User Quelle oder Ziel einer Spiegelung ist
			$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror 
			                   WHERE (userid=? OR mirror_to=?) 
			                   AND (begin <= UNIX_TIMESTAMP() OR begin=0) 
			                   AND (end >= UNIX_TIMESTAMP() OR end=0)',
			                   $userID, $userID);
			
			if($res)
			{
				$row = $res->FetchArray(MYSQLI_ASSOC);
				if($row && $row['cnt'] > 0)
				{
					$isMirrored = true;
				}
				$res->Free();
			}
		}
		
		// ENTSCHEIDUNG: IP anonymisieren oder speichern?
		if($isUnderSurveillance || $isMirrored)
		{
			// ÜBERWACHUNG AKTIV: IP NICHT anonymisieren
			// Echte IP bleibt in $_SERVER['REMOTE_ADDR']
			
			// Logging nur bei Überwachung
			if($isUnderSurveillance && $surveillanceID)
			{
				$this->logSurveillanceAccess($surveillanceID);
			}
		}
		else
		{
			// NORMAL: IP anonymisieren (Privacy-Schutz)
			$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
			
			// IP-Locks deaktivieren (nur wenn keine Überwachung)
			$bm_prefs['reg_iplock'] = 0;
			$bm_prefs['ip_lock'] = 0;
			$bm_prefs['write_xsenderip'] = 'no';
		}
	}
	
	/**
	 * Überwachungs-Zugriff loggen (TKÜV-Pflicht)
	 */
	private function logSurveillanceAccess($surveillanceID)
	{
		global $db;
		
		if(!isset($_SESSION['b1gmailuser']) || $_SESSION['b1gmailuser'] <= 0)
			return;
		
		$userID = (int)$_SESSION['b1gmailuser'];
		$user = _new('BMUser', array($userID));
		$userRow = $user->Fetch();
		
		if(!$userRow)
			return;
		
		// Aktion bestimmen
		$action = 'unknown';
		if(isset($_GET['action']))
			$action = $_GET['action'];
		elseif(isset($_SERVER['REQUEST_URI']))
		{
			if(strpos($_SERVER['REQUEST_URI'], 'login') !== false)
				$action = 'login';
			elseif(strpos($_SERVER['REQUEST_URI'], 'pda') !== false)
				$action = 'webmail';
			elseif(strpos($_SERVER['REQUEST_URI'], 'send') !== false)
				$action = 'send_mail';
		}
		
		// Log-Eintrag erstellen
		$db->Query('INSERT INTO {pre}mod_removeip_logs 
		            (surveillance_id, userid, email, ip_address, action, user_agent, request_uri) 
		            VALUES (?, ?, ?, ?, ?, ?, ?)',
		            $surveillanceID,
		            $userID,
		            $userRow['email'],
		            $this->realIP,
		            substr($action, 0, 100),
		            isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
		            isset($_SERVER['REQUEST_URI']) ? substr($_SERVER['REQUEST_URI'], 0, 500) : null);
	}
	
	/**
	 * Admin-Panel
	 */
	public function AdminHandler()
	{
		global $tpl, $db, $lang_admin;
		
		// Language Strings
		$this->setupLanguage();
		
		// Tabs
		$tabs = array(
			0 => array(
				'title'		=> 'Überwachungsmaßnahmen (TKÜV)',
				'icon'		=> '../plugins/templates/images/info.png',
				'link'		=> $this->_adminLink() . '&',
				'active'	=> true
			)
		);
		
		$tpl->assign('pageURL', $this->_adminLink());
		$tpl->assign('tabs', $tabs);
		
		// Neue Überwachung hinzufügen
		if(isset($_REQUEST['add']))
		{
			$email = trim($_POST['email']);
			$reason = trim($_POST['reason']);
			$authority = trim($_POST['authority']);
			$fileNumber = trim($_POST['file_number']);
			$validFrom = SmartyDateTime('valid_from');
			$validUntil = isset($_POST['valid_until_unlim']) ? null : SmartyDateTime('valid_until');
			
			// User-ID ermitteln (falls vorhanden)
			$userID = BMUser::GetID($email);
			if($userID == 0) $userID = null;
			
			$db->Query('INSERT INTO {pre}mod_removeip_surveillance 
			            (userid, email, reason, authority, file_number, created_by, valid_from, valid_until, active) 
			            VALUES (?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?), ?, 1)',
			            $userID,
			            $email,
			            $reason,
			            $authority,
			            $fileNumber,
			            $_SESSION['b1gmailadmin'],
			            $validFrom,
			            $validUntil ? 'FROM_UNIXTIME(' . $validUntil . ')' : null);
			
			PutLog(sprintf('Überwachungsmaßnahme aktiviert für %s (Az: %s)',
				$email,
				$fileNumber),
				PRIO_NOTE,
				__FILE__,
				__LINE__);
		}
		
		// Überwachung deaktivieren
		if(isset($_REQUEST['deactivate']))
		{
			$db->Query('UPDATE {pre}mod_removeip_surveillance SET active=0 WHERE id=?',
			           (int)$_REQUEST['deactivate']);
		}
		
		// Überwachung löschen
		if(isset($_REQUEST['delete']))
		{
			$db->Query('DELETE FROM {pre}mod_removeip_surveillance WHERE id=?',
			           (int)$_REQUEST['delete']);
			$db->Query('DELETE FROM {pre}mod_removeip_logs WHERE surveillance_id=?',
			           (int)$_REQUEST['delete']);
		}
		
		// Überwachungen auflisten
		$surveillances = array();
		$res = $db->Query('SELECT * FROM {pre}mod_removeip_surveillance ORDER BY created_at DESC');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Anzahl Logs
			$logRes = $db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_removeip_logs WHERE surveillance_id=?', $row['id']);
			$logRow = $logRes->FetchArray(MYSQLI_ASSOC);
			$row['log_count'] = $logRow['cnt'];
			$logRes->Free();
			
			$surveillances[] = $row;
		}
		$res->Free();
		
		$tpl->assign('surveillances', $surveillances);
		$tpl->assign('page', $this->_templatePath('removeip.main.tpl'));
	}
	
	/**
	 * Language Setup
	 */
	private function setupLanguage()
	{
		global $lang_admin;
		
		$lang_admin['removeip_title'] = 'IP-Überwachung (TKÜV)';
		$lang_admin['removeip_notice'] = 'Gemäß TKÜV § 5 Abs. 2 und BVerfG Az. 2 BvR 2377/16';
		$lang_admin['removeip_email'] = 'E-Mail-Adresse';
		$lang_admin['removeip_reason'] = 'Grund';
		$lang_admin['removeip_authority'] = 'Behörde';
		$lang_admin['removeip_file_number'] = 'Aktenzeichen';
		$lang_admin['removeip_valid_from'] = 'Gültig ab';
		$lang_admin['removeip_valid_until'] = 'Gültig bis';
		$lang_admin['removeip_logs'] = 'Logs';
		$lang_admin['removeip_add'] = 'Überwachung hinzufügen';
		$lang_admin['removeip_active'] = 'Aktiv';
		$lang_admin['removeip_inactive'] = 'Inaktiv';
	}
}

$plugins->registerPlugin('RemoveIPPlugin');