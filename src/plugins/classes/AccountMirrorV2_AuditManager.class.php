<?php
/**
 * AccountMirror v2.0 - Audit Manager
 * TKÜV/BNetzA-konforme Audit-Trail-Verwaltung
 * 
 * WICHTIG: Audit-Logs werden UNENDLICH gespeichert (TKÜV-Pflicht!)
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

class AccountMirrorV2_AuditManager
{
	private $db;
	
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}
	
	/**
	 * Überwachungsmaßnahme im Audit-Log protokollieren
	 * TKÜV-PFLICHT: Jede Änderung muss dokumentiert werden!
	 */
	public function logEvent($mirrorId, $eventType, $details = null, $legalReference = null)
	{
		global $_SESSION;
		
		// Admin-Info
		$adminId = $_SESSION['b1gmailadmin'] ?? 0;
		$adminEmail = null;
		
		if($adminId > 0)
		{
			$admin = _new('BMUser', array($adminId));
			$adminRow = $admin->Fetch();
			$adminEmail = $adminRow['email'] ?? null;
		}
		
		// IP-Adresse (TKÜV-Pflicht!)
		$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		
		// User-Agent
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
		
		// Details als JSON
		$detailsJson = $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
		
		// In Audit-Log eintragen
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_audit_log 
		                  (mirrorid, event_type, event_details, performed_by, performed_by_email, 
		                   ip_address, user_agent, legal_reference) 
		                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
		                  $mirrorId,
		                  $eventType,
		                  $detailsJson,
		                  $adminId,
		                  $adminEmail,
		                  $ipAddress,
		                  $userAgent,
		                  $legalReference);
		
		// Zusätzlich im System-Log
		if(in_array($eventType, array('created', 'deleted', 'activated', 'deactivated')))
		{
			PutLog(sprintf('AccountMirror v2 AUDIT: %s for Mirror #%d by Admin %s (IP: %s)',
			               $eventType, $mirrorId, $adminEmail ?? $adminId, $ipAddress),
			       PRIO_NOTE,
			       __FILE__,
			       __LINE__);
		}
	}
	
	/**
	 * Audit-Log für eine Spiegelung abrufen
	 */
	public function getAuditLog($mirrorId, $limit = 100, $offset = 0)
	{
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_audit_log 
		                         WHERE mirrorid = ? 
		                         ORDER BY timestamp DESC 
		                         LIMIT ? OFFSET ?',
		                         $mirrorId, $limit, $offset);
		
		$logs = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			// Details JSON dekodieren
			if($row['event_details'])
			{
				$row['event_details_decoded'] = json_decode($row['event_details'], true);
			}
			
			$logs[] = $row;
		}
		$res->Free();
		
		return $logs;
	}
	
	/**
	 * Alle Audit-Logs abrufen (für Behörden-Export)
	 */
	public function getAllAuditLogs($filters = array())
	{
		$where = array();
		$params = array();
		
		if(isset($filters['mirrorid']))
		{
			$where[] = 'mirrorid = ?';
			$params[] = $filters['mirrorid'];
		}
		
		if(isset($filters['event_type']))
		{
			$where[] = 'event_type = ?';
			$params[] = $filters['event_type'];
		}
		
		if(isset($filters['from_date']))
		{
			$where[] = 'timestamp >= ?';
			$params[] = $filters['from_date'];
		}
		
		if(isset($filters['to_date']))
		{
			$where[] = 'timestamp <= ?';
			$params[] = $filters['to_date'];
		}
		
		if(isset($filters['year']))
		{
			$where[] = 'YEAR(timestamp) = ?';
			$params[] = $filters['year'];
		}
		
		$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
		
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_audit_log 
		                         ' . $whereClause . ' 
		                         ORDER BY timestamp DESC',
		                         ...$params);
		
		$logs = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			if($row['event_details'])
			{
				$row['event_details_decoded'] = json_decode($row['event_details'], true);
			}
			$logs[] = $row;
		}
		$res->Free();
		
		return $logs;
	}
	
	/**
	 * Jahres-Auswertung generieren
	 */
	public function generateYearlyReport($year = null, $adminId = null)
	{
		if(!$year)
		{
			$year = (int)date('Y');
		}
		
		// Statistiken sammeln
		$stats = array();
		
		// Anzahl Spiegelungen
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2 
		                         WHERE YEAR(created_at) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['total_mirrorings'] = $row['cnt'];
		$res->Free();
		
		// Aktive Spiegelungen
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2 
		                         WHERE active = 1 AND YEAR(created_at) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['active_mirrorings'] = $row['cnt'];
		$res->Free();
		
		// Gesamt-Syncs
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_sync_log 
		                         WHERE YEAR(synced_at) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['total_syncs'] = $row['cnt'];
		$res->Free();
		
		// Gesamt gespiegelte Mails
		$res = $this->db->Query('SELECT SUM(mail_count) as total FROM {pre}mod_accountmirror_v2 
		                         WHERE YEAR(created_at) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['total_mails'] = $row['total'] ?? 0;
		$res->Free();
		
		// Audit-Events
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_audit_log 
		                         WHERE YEAR(timestamp) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['total_audit_events'] = $row['cnt'];
		$res->Free();
		
		// Auskunftsersuchen
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_information_requests 
		                         WHERE YEAR(received_at) = ?', $year);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$stats['total_information_requests'] = $row['cnt'];
		$res->Free();
		
		// Top Behörden
		$res = $this->db->Query('SELECT authority, COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_information_requests 
		                         WHERE YEAR(received_at) = ? 
		                         GROUP BY authority 
		                         ORDER BY cnt DESC 
		                         LIMIT 10',
		                         $year);
		$stats['top_authorities'] = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$stats['top_authorities'][] = $row;
		}
		$res->Free();
		
		// Monatsstatistik
		$res = $this->db->Query('SELECT 
		                          MONTH(created_at) as month,
		                          COUNT(*) as cnt 
		                         FROM {pre}mod_accountmirror_v2 
		                         WHERE YEAR(created_at) = ? 
		                         GROUP BY MONTH(created_at) 
		                         ORDER BY month',
		                         $year);
		$stats['monthly_mirrorings'] = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$stats['monthly_mirrorings'][$row['month']] = $row['cnt'];
		}
		$res->Free();
		
		// Generierungszeitpunkt
		$stats['generated_at'] = date('Y-m-d H:i:s');
		$stats['generated_by_admin_id'] = $adminId;
		
		// In Datenbank speichern
		$reportData = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
		$this->db->Query('INSERT INTO {pre}mod_accountmirror_v2_yearly_reports 
		                  (year, report_type, report_data, generated_by) 
		                  VALUES (?, ?, ?, ?) 
		                  ON DUPLICATE KEY UPDATE 
		                  report_data = VALUES(report_data), 
		                  generated_at = NOW()',
		                  $year,
		                  'annual_summary',
		                  $reportData,
		                  $adminId);
		
		PutLog(sprintf('AccountMirror v2: Jahres-Auswertung für %d generiert', $year),
		       PRIO_NOTE,
		       __FILE__,
		       __LINE__);
		
		return array(
			'success' => true,
			'year' => $year,
			'stats' => $stats
		);
	}
	
	/**
	 * Jahres-Auswertung abrufen
	 */
	public function getYearlyReport($year)
	{
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_yearly_reports 
		                         WHERE year = ? AND report_type = ?',
		                         $year, 'annual_summary');
		
		if($res->RowCount() > 0)
		{
			$row = $res->FetchArray(MYSQLI_ASSOC);
			$row['report_data_decoded'] = json_decode($row['report_data'], true);
			$res->Free();
			return $row;
		}
		
		$res->Free();
		return null;
	}
	
	/**
	 * Alle Jahres-Auswertungen auflisten
	 */
	public function listYearlyReports()
	{
		$res = $this->db->Query('SELECT * FROM {pre}mod_accountmirror_v2_yearly_reports 
		                         WHERE report_type = ? 
		                         ORDER BY year DESC',
		                         'annual_summary');
		
		$reports = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$row['report_data_decoded'] = json_decode($row['report_data'], true);
			$reports[] = $row;
		}
		$res->Free();
		
		return $reports;
	}
	
	/**
	 * Compliance-Check durchführen
	 */
	public function checkCompliance()
	{
		$issues = array();
		
		// Prüfen ob alle Spiegelungen einen Grund haben
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2 
		                         WHERE reason IS NULL OR reason = ""');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		if($row['cnt'] > 0)
		{
			$issues[] = array(
				'severity' => 'critical',
				'message' => $row['cnt'] . ' Spiegelung(en) ohne Grund (TKÜV-Verstoß!)'
			);
		}
		$res->Free();
		
		// Prüfen ob alle Audit-Events IP haben
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_audit_log 
		                         WHERE ip_address IS NULL OR ip_address = "0.0.0.0"');
		$row = $res->FetchArray(MYSQLI_ASSOC);
		if($row['cnt'] > 0)
		{
			$issues[] = array(
				'severity' => 'warning',
				'message' => $row['cnt'] . ' Audit-Event(s) ohne IP-Adresse'
			);
		}
		$res->Free();
		
		// Prüfen ob Jahres-Auswertung für letztes Jahr existiert
		$lastYear = (int)date('Y') - 1;
		$res = $this->db->Query('SELECT COUNT(*) as cnt FROM {pre}mod_accountmirror_v2_yearly_reports 
		                         WHERE year = ?', $lastYear);
		$row = $res->FetchArray(MYSQLI_ASSOC);
		if($row['cnt'] == 0)
		{
			$issues[] = array(
				'severity' => 'warning',
				'message' => 'Keine Jahres-Auswertung für ' . $lastYear . ' vorhanden'
			);
		}
		$res->Free();
		
		return array(
			'compliant' => count($issues) == 0,
			'issues' => $issues
		);
	}
}

?>
