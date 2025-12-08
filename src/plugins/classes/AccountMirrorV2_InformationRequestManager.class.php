<?php
/**
 * AccountMirror v2.0 - Information Request Manager
 * Auskunftsersuchen von Ermittlungsbehörden (TKÜV § 113 TKG)
 * 
 * GESTAFFELTE AUSKUNFTSSTUFEN:
 * 1. Bestandsdaten (wer ist der Kunde?)
 * 2. Verkehrsdaten (mit wem kommuniziert?)
 * 3. Inhaltsdaten (was steht in den Mails?)
 * 4. Vollumfänglich (alles)
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class AccountMirrorV2_InformationRequestManager
{
	private $db;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Auskunftsersuchen erstellen
	 */
	public function createRequest($params)
	{
		$required = array('request_number', 'authority', 'request_type', 'legal_basis', 'created_by');
		foreach($required as $field)
		{
			if(!isset($params[$field]) || empty($params[$field]))
			{
				return array('success' => false, 'message' => "Feld $field fehlt");
			}
		}
		
		// Validierung Auskunftsstufe
		$validTypes = array('bestandsdaten', 'verkehrsdaten', 'inhaltsdaten', 'vollumfaenglich');
		if(!in_array($params['request_type'], $validTypes))
		{
			return array('success' => false, 'message' => 'Ungültige Auskunftsstufe');
		}
		
		// User-ID ermitteln (falls E-Mail angegeben)
		$targetUserId = null;
		if(isset($params['target_email']))
		{
			$targetUserId = BMUser::GetID($params['target_email']);
			if($targetUserId == 0)
			{
				return array('success' => false, 'message' => 'User nicht gefunden');
			}
		}
		
		// Defaults
		$defaults = array(
			'target_userid' => $targetUserId,
			'target_email' => $params['target_email'] ?? null,
			'mirrorid' => $params['mirrorid'] ?? null,
			'authority_contact' => $params['authority_contact'] ?? null,
			'file_number' => $params['file_number'] ?? null,
			'request_period_from' => $params['request_period_from'] ?? null,
			'request_period_to' => $params['request_period_to'] ?? null,
			'deadline' => $params['deadline'] ?? null,
			'priority' => $params['priority'] ?? 'normal',
			'notes' => $params['notes'] ?? null,
			'status' => 'pending'
		);
		
		$params = array_merge($defaults, $params);
		
		// Einfügen
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_information_requests 
		                  (request_number, authority, authority_contact, request_type, legal_basis, 
		                   file_number, target_userid, target_email, mirrorid, 
		                   request_period_from, request_period_to, 
		                   status, priority, deadline, notes, created_by) 
		                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
		                  $params['request_number'],
		                  $params['authority'],
		                  $params['authority_contact'],
		                  $params['request_type'],
		                  $params['legal_basis'],
		                  $params['file_number'],
		                  $params['target_userid'],
		                  $params['target_email'],
		                  $params['mirrorid'],
		                  $params['request_period_from'],
		                  $params['request_period_to'],
		                  $params['status'],
		                  $params['priority'],
		                  $params['deadline'],
		                  $params['notes'],
		                  $params['created_by']);
		
		$requestId = $this->db->InsertId();
		
		// Audit-Log
		$this->logRequestAction($requestId, 'created', array(
			'authority' => $params['authority'],
			'request_type' => $params['request_type'],
			'target_email' => $params['target_email']
		), $params['created_by']);
		
		// System-Log
		PutLog(sprintf('AccountMirror v2: Auskunftsersuchen #%s von %s erstellt (Typ: %s, User: %s)',
		               $params['request_number'], $params['authority'], 
		               $params['request_type'], $params['target_email']),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		return array(
			'success' => true,
			'request_id' => $requestId,
			'message' => 'Auskunftsersuchen erstellt'
		);
	}
	
	/**
	 * Auskunft generieren (basierend auf Stufe)
	 */
	public function generateInformationResponse($requestId, $adminId)
	{
		// Request laden
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_information_requests WHERE id = ?',
		                         $requestId);
		
		if($res->RowCount() == 0)
		{
			$res->Free();
			return array('success' => false, 'message' => 'Ersuchen nicht gefunden');
		}
		
		$request = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		$responseData = array();
		
		// STUFE 1: BESTANDSDATEN
		if(in_array($request['request_type'], array('bestandsdaten', 'vollumfaenglich')))
		{
			$responseData['bestandsdaten'] = $this->getBestandsdaten($request);
		}
		
		// STUFE 2: VERKEHRSDATEN
		if(in_array($request['request_type'], array('verkehrsdaten', 'vollumfaenglich')))
		{
			$responseData['verkehrsdaten'] = $this->getVerkehrsdaten($request);
		}
		
		// STUFE 3: INHALTSDATEN
		if(in_array($request['request_type'], array('inhaltsdaten', 'vollumfaenglich')))
		{
			$responseData['inhaltsdaten'] = $this->getInhaltsdaten($request);
		}
		
		// Als JSON speichern
		$responseJson = json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
		// In Datenbank speichern
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2_information_requests 
		                  SET response_data = ?, 
		                      status = ?, 
		                      processed_by = ?, 
		                      completed_at = NOW() 
		                  WHERE id = ?',
		                  $responseJson,
		                  'completed',
		                  $adminId,
		                  $requestId);
		
		// Audit-Log
		$this->logRequestAction($requestId, 'response_generated', array(
			'data_types' => array_keys($responseData)
		), $adminId);
		
		PutLog(sprintf('AccountMirror v2: Auskunft für Ersuchen #%d generiert (Typ: %s)',
		               $requestId, $request['request_type']),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		return array(
			'success' => true,
			'response_data' => $responseData,
			'message' => 'Auskunft generiert'
		);
	}
	
	/**
	 * STUFE 1: Bestandsdaten (§ 113 TKG Abs. 1)
	 */
	private function getBestandsdaten($request)
	{
		$data = array();
		
		if($request['target_userid'])
		{
			$user = _new('BMUser', array($request['target_userid']));
			$userRow = $user->Fetch();
			
			if($userRow)
			{
				$data['user_id'] = $userRow['id'];
				$data['email'] = $userRow['email'];
				$data['vorname'] = $userRow['vorname'];
				$data['nachname'] = $userRow['nachname'];
				$data['anrede'] = $userRow['anrede'];
				$data['strasse'] = $userRow['strasse'];
				$data['plz'] = $userRow['plz'];
				$data['ort'] = $userRow['ort'];
				$data['land'] = $userRow['land'];
				$data['telefon'] = $userRow['telefon'];
				$data['fax'] = $userRow['fax'];
				$data['geburtsdatum'] = $userRow['geburtsdatum'];
				$data['reg_date'] = $userRow['reg_date'];
				$data['last_login'] = $userRow['lastlogin'];
				$data['mail_quota'] = $userRow['mailspace_add'];
				$data['status'] = $userRow['gesperrt'] ? 'gesperrt' : 'aktiv';
			}
		}
		
		return $data;
	}
	
	/**
	 * STUFE 2: Verkehrsdaten (§ 113 TKG Abs. 2)
	 */
	private function getVerkehrsdaten($request)
	{
		$data = array();
		
		if(!$request['target_userid'])
		{
			return $data;
		}
		
		// Zeitraum
		$fromDate = $request['request_period_from'] ?? '2000-01-01';
		$toDate = $request['request_period_to'] ?? date('Y-m-d H:i:s');
		
		// E-Mail-Verkehrsdaten
		$res = $this->db->Query('SELECT 
		                          id, folder, from_email, to_email, cc, bcc, 
		                          subject, date, size, flags, ip
		                         FROM {pre}mails 
		                         WHERE userid = ? 
		                         AND date >= ? 
		                         AND date <= ? 
		                         ORDER BY date DESC',
		                         $request['target_userid'],
		                         $fromDate,
		                         $toDate);
		
		$data['emails'] = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$data['emails'][] = array(
				'mail_id' => $row['id'],
				'folder' => $row['folder'],
				'from' => $row['from_email'],
				'to' => $row['to_email'],
				'cc' => $row['cc'],
				'bcc' => $row['bcc'],
				'subject' => $row['subject'], // Betreff gehört zu Verkehrsdaten
				'date' => $row['date'],
				'size' => $row['size'],
				'flags' => $row['flags'],
				'sender_ip' => $row['ip']
			);
		}
		$res->Free();
		
		$data['total_emails'] = count($data['emails']);
		
		// Login-Verkehrsdaten (falls vorhanden)
		// TODO: Aus EmailAdmin Admin-Access-Logs holen
		
		return $data;
	}
	
	/**
	 * STUFE 3: Inhaltsdaten (§ 100a StPO - NUR mit richterlicher Anordnung!)
	 */
	private function getInhaltsdaten($request)
	{
		$data = array();
		
		if(!$request['target_userid'])
		{
			return $data;
		}
		
		// ⚠️ WICHTIG: Inhaltsdaten NUR bei richterlicher Anordnung!
		// Rechtsgrundlage MUSS § 100a/b StPO sein!
		
		// Zeitraum
		$fromDate = $request['request_period_from'] ?? '2000-01-01';
		$toDate = $request['request_period_to'] ?? date('Y-m-d H:i:s');
		
		// E-Mail-Inhalte
		$user = _new('BMUser', array($request['target_userid']));
		$userRow = $user->Fetch();
		$mailbox = _new('BMMailbox', array($request['target_userid'], $userRow['email'], $user));
		
		$res = $this->db->Query('SELECT id FROM {pre}mails 
		                         WHERE userid = ? 
		                         AND date >= ? 
		                         AND date <= ? 
		                         ORDER BY date DESC',
		                         $request['target_userid'],
		                         $fromDate,
		                         $toDate);
		
		$data['emails'] = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$mailObj = $mailbox->GetMail($row['id']);
			
			if($mailObj && is_object($mailObj))
			{
				$data['emails'][] = array(
					'mail_id' => $row['id'],
					'from' => $mailObj->_row['from_email'],
					'to' => $mailObj->_row['to_email'],
					'subject' => $mailObj->_row['subject'],
					'date' => $mailObj->_row['date'],
					'body_text' => $mailObj->GetTextBody(), // INHALT!
					'body_html' => $mailObj->GetHTMLBody(), // INHALT!
					'attachments' => $mailObj->GetAttachmentList() // Anhänge
				);
			}
		}
		$res->Free();
		
		$data['total_emails_with_content'] = count($data['emails']);
		
		// Webdisk-Inhalte (falls angefordert)
		// TODO: Webdisk-Dateien & Inhalte
		
		return $data;
	}
	
	/**
	 * Auskunft als CSV exportieren
	 */
	public function exportAsCSV($requestId)
	{
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_information_requests WHERE id = ?',
		                         $requestId);
		
		if($res->RowCount() == 0)
		{
			$res->Free();
			return false;
		}
		
		$request = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		if(!$request['response_data'])
		{
			return false;
		}
		
		$responseData = json_decode($request['response_data'], true);
		
		// CSV-Header
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="auskunft_' . $request['request_number'] . '_' . date('Y-m-d') . '.csv"');
		
		$fp = fopen('php://output', 'w');
		
		// BOM für UTF-8
		fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
		
		// Kopfzeile
		fputcsv($fp, array('Auskunftsersuchen', $request['request_number']), ';');
		fputcsv($fp, array('Behörde', $request['authority']), ';');
		fputcsv($fp, array('Typ', $request['request_type']), ';');
		fputcsv($fp, array('Erstellt am', $request['received_at']), ';');
		fputcsv($fp, array(''), ';');
		
		// Bestandsdaten
		if(isset($responseData['bestandsdaten']))
		{
			fputcsv($fp, array('BESTANDSDATEN'), ';');
			foreach($responseData['bestandsdaten'] as $key => $value)
			{
				fputcsv($fp, array($key, $value), ';');
			}
			fputcsv($fp, array(''), ';');
		}
		
		// Verkehrsdaten
		if(isset($responseData['verkehrsdaten']['emails']))
		{
			fputcsv($fp, array('VERKEHRSDATEN - E-MAILS'), ';');
			fputcsv($fp, array('Von', 'An', 'Betreff', 'Datum', 'Größe'), ';');
			foreach($responseData['verkehrsdaten']['emails'] as $email)
			{
				fputcsv($fp, array(
					$email['from'],
					$email['to'],
					$email['subject'],
					$email['date'],
					$email['size']
				), ';');
			}
		}
		
		fclose($fp);
		
		// Audit-Log
		$this->logRequestAction($requestId, 'exported_csv', null, $_SESSION['b1gmailadmin']);
		
		exit;
	}
	
	/**
	 * Request-Aktion loggen
	 */
	private function logRequestAction($requestId, $action, $details = null, $adminId = null)
	{
		if(!$adminId)
		{
			$adminId = $_SESSION['b1gmailadmin'] ?? 0;
		}
		
		$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		$detailsJson = $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
		
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_information_requests_log 
		                  (request_id, action, details, performed_by, ip_address) 
		                  VALUES (?, ?, ?, ?, ?)',
		                  $requestId, $action, $detailsJson, $adminId, $ipAddress);
	}
	
	/**
	 * Alle Auskunftsersuchen auflisten
	 */
	public function listRequests($filters = array())
	{
		$where = array();
		$params = array();
		
		if(isset($filters['status']))
		{
			$where[] = 'status = ?';
			$params[] = $filters['status'];
		}
		
		if(isset($filters['authority']))
		{
			$where[] = 'authority LIKE ?';
			$params[] = '%' . $filters['authority'] . '%';
		}
		
		if(isset($filters['priority']))
		{
			$where[] = 'priority = ?';
			$params[] = $filters['priority'];
		}
		
		$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
		
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_information_requests 
		                         ' . $whereClause . ' 
		                         ORDER BY received_at DESC',
		                         ...$params);
		
		$requests = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$requests[] = $row;
		}
		$res->Free();
		
		return $requests;
	}
	
	/**
	 * Status ändern
	 */
	public function updateStatus($requestId, $newStatus, $adminId)
	{
		$this->db->Query('UPDATE {pre}mod_accountmirror_v2_information_requests 
		                  SET status = ?, processed_by = ? 
		                  WHERE id = ?',
		                  $newStatus, $adminId, $requestId);
		
		$this->logRequestAction($requestId, 'status_changed', array('new_status' => $newStatus), $adminId);
		
		return array('success' => true, 'message' => 'Status geändert');
	}
}

?>
