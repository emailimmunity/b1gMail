<?php
/**
 * DAV Statistics Tracking
 * Analog zu IMAP/POP3/SMTP Stats im b1gMailServer Plugin
 * 
 * Trackt:
 * - CalDAV: Sessions, Traffic, Events (Created/Updated/Deleted)
 * - CardDAV: Sessions, Traffic, Contacts (Created/Updated/Deleted)
 * - WebDAV: Sessions, Traffic, Files (Uploaded/Downloaded/Deleted)
 */

class BMDAVStats
{
	/**
	 * Track CalDAV Session
	 */
	public static function trackCalDAVSession($userID, $bytesIn, $bytesOut, $eventsCreated = 0, $eventsUpdated = 0, $eventsDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			// Check if entry exists for today
			$res = $db->Query('SELECT * FROM {pre}bms_caldav_stats WHERE date=?', $date);
			$exists = ($res->FetchArray(MYSQLI_ASSOC) !== false);
			$res->Free();
			
			if($exists) {
				// Update existing
				$db->Query('UPDATE {pre}bms_caldav_stats SET 
				            sessions = sessions + 1,
				            traffic_in = traffic_in + ?,
				            traffic_out = traffic_out + ?,
				            events_created = events_created + ?,
				            events_updated = events_updated + ?,
				            events_deleted = events_deleted + ?
				            WHERE date=?',
				            $bytesIn, $bytesOut, $eventsCreated, $eventsUpdated, $eventsDeleted, $date);
			} else {
				// Insert new
				$db->Query('INSERT INTO {pre}bms_caldav_stats 
				            (date, sessions, traffic_in, traffic_out, events_created, events_updated, events_deleted)
				            VALUES (?, 1, ?, ?, ?, ?, ?)',
				            $date, $bytesIn, $bytesOut, $eventsCreated, $eventsUpdated, $eventsDeleted);
			}
			
			// Log to b1gMailServer log
			BMLog(BMS_CMP_CALDAV, BMS_LOG_NOTICE, sprintf('CalDAV session: User=%d, In=%d, Out=%d, Events=%d/%d/%d',
				$userID, $bytesIn, $bytesOut, $eventsCreated, $eventsUpdated, $eventsDeleted));
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Track CardDAV Session
	 */
	public static function trackCardDAVSession($userID, $bytesIn, $bytesOut, $contactsCreated = 0, $contactsUpdated = 0, $contactsDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			// Check if entry exists for today
			$res = $db->Query('SELECT * FROM {pre}bms_carddav_stats WHERE date=?', $date);
			$exists = ($res->FetchArray(MYSQLI_ASSOC) !== false);
			$res->Free();
			
			if($exists) {
				// Update existing
				$db->Query('UPDATE {pre}bms_carddav_stats SET 
				            sessions = sessions + 1,
				            traffic_in = traffic_in + ?,
				            traffic_out = traffic_out + ?,
				            contacts_created = contacts_created + ?,
				            contacts_updated = contacts_updated + ?,
				            contacts_deleted = contacts_deleted + ?
				            WHERE date=?',
				            $bytesIn, $bytesOut, $contactsCreated, $contactsUpdated, $contactsDeleted, $date);
			} else {
				// Insert new
				$db->Query('INSERT INTO {pre}bms_carddav_stats 
				            (date, sessions, traffic_in, traffic_out, contacts_created, contacts_updated, contacts_deleted)
				            VALUES (?, 1, ?, ?, ?, ?, ?)',
				            $date, $bytesIn, $bytesOut, $contactsCreated, $contactsUpdated, $contactsDeleted);
			}
			
			// Log to b1gMailServer log
			BMLog(BMS_CMP_CARDDAV, BMS_LOG_NOTICE, sprintf('CardDAV session: User=%d, In=%d, Out=%d, Contacts=%d/%d/%d',
				$userID, $bytesIn, $bytesOut, $contactsCreated, $contactsUpdated, $contactsDeleted));
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Track WebDAV Session
	 */
	public static function trackWebDAVSession($userID, $bytesIn, $bytesOut, $filesUploaded = 0, $filesDownloaded = 0, $filesDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			// Check if entry exists for today
			$res = $db->Query('SELECT * FROM {pre}bms_webdav_stats WHERE date=?', $date);
			$exists = ($res->FetchArray(MYSQLI_ASSOC) !== false);
			$res->Free();
			
			if($exists) {
				// Update existing
				$db->Query('UPDATE {pre}bms_webdav_stats SET 
				            sessions = sessions + 1,
				            traffic_in = traffic_in + ?,
				            traffic_out = traffic_out + ?,
				            files_uploaded = files_uploaded + ?,
				            files_downloaded = files_downloaded + ?,
				            files_deleted = files_deleted + ?
				            WHERE date=?',
				            $bytesIn, $bytesOut, $filesUploaded, $filesDownloaded, $filesDeleted, $date);
			} else {
				// Insert new
				$db->Query('INSERT INTO {pre}bms_webdav_stats 
				            (date, sessions, traffic_in, traffic_out, files_uploaded, files_downloaded, files_deleted)
				            VALUES (?, 1, ?, ?, ?, ?, ?)',
				            $date, $bytesIn, $bytesOut, $filesUploaded, $filesDownloaded, $filesDeleted);
			}
			
			// Log to b1gMailServer log
			BMLog(BMS_CMP_WEBDAV, BMS_LOG_NOTICE, sprintf('WebDAV session: User=%d, In=%d, Out=%d, Files=%d/%d/%d',
				$userID, $bytesIn, $bytesOut, $filesUploaded, $filesDownloaded, $filesDeleted));
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Update last session (called after request completion)
	 */
	public static function updateLastCalDAVSession($userID, $bytesIn, $bytesOut, $eventsCreated = 0, $eventsUpdated = 0, $eventsDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			// Update nur die Werte, die sich geändert haben
			$db->Query('UPDATE {pre}bms_caldav_stats SET 
			            traffic_out = traffic_out + ?,
			            events_created = events_created + ?,
			            events_updated = events_updated + ?,
			            events_deleted = events_deleted + ?
			            WHERE date=?',
			            $bytesOut, $eventsCreated, $eventsUpdated, $eventsDeleted, $date);
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Update last session (CardDAV)
	 */
	public static function updateLastCardDAVSession($userID, $bytesIn, $bytesOut, $contactsCreated = 0, $contactsUpdated = 0, $contactsDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			$db->Query('UPDATE {pre}bms_carddav_stats SET 
			            traffic_out = traffic_out + ?,
			            contacts_created = contacts_created + ?,
			            contacts_updated = contacts_updated + ?,
			            contacts_deleted = contacts_deleted + ?
			            WHERE date=?',
			            $bytesOut, $contactsCreated, $contactsUpdated, $contactsDeleted, $date);
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Update last session (WebDAV)
	 */
	public static function updateLastWebDAVSession($userID, $bytesIn, $bytesOut, $filesUploaded = 0, $filesDownloaded = 0, $filesDeleted = 0)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return false;
		
		try {
			$date = date('Y-m-d');
			
			$db->Query('UPDATE {pre}bms_webdav_stats SET 
			            traffic_out = traffic_out + ?,
			            files_uploaded = files_uploaded + ?,
			            files_downloaded = files_downloaded + ?,
			            files_deleted = files_deleted + ?
			            WHERE date=?',
			            $bytesOut, $filesUploaded, $filesDownloaded, $filesDeleted, $date);
			
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
	
	/**
	 * Get CalDAV stats for date range
	 */
	public static function getCalDAVStats($startDate, $endDate)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return array();
		
		$stats = array();
		
		try {
			$res = $db->Query('SELECT * FROM {pre}bms_caldav_stats 
			                   WHERE date >= ? AND date <= ?
			                   ORDER BY date ASC',
			                   $startDate, $endDate);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $stats;
	}
	
	/**
	 * Get CardDAV stats for date range
	 */
	public static function getCardDAVStats($startDate, $endDate)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return array();
		
		$stats = array();
		
		try {
			$res = $db->Query('SELECT * FROM {pre}bms_carddav_stats 
			                   WHERE date >= ? AND date <= ?
			                   ORDER BY date ASC',
			                   $startDate, $endDate);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $stats;
	}
	
	/**
	 * Get WebDAV stats for date range
	 */
	public static function getWebDAVStats($startDate, $endDate)
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return array();
		
		$stats = array();
		
		try {
			$res = $db->Query('SELECT * FROM {pre}bms_webdav_stats 
			                   WHERE date >= ? AND date <= ?
			                   ORDER BY date ASC',
			                   $startDate, $endDate);
			while($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$stats[] = $row;
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $stats;
	}
	
	/**
	 * Get today's totals for all DAV protocols
	 */
	public static function getTodayTotals()
	{
		global $db;
		
		if(!isset($db) || !is_object($db))
			return array();
		
		$date = date('Y-m-d');
		$totals = array(
			'caldav' => array('sessions' => 0, 'traffic' => 0, 'events' => 0),
			'carddav' => array('sessions' => 0, 'traffic' => 0, 'contacts' => 0),
			'webdav' => array('sessions' => 0, 'traffic' => 0, 'files' => 0)
		);
		
		try {
			// CalDAV
			$res = $db->Query('SELECT SUM(sessions) as sessions, 
			                          SUM(traffic_in)+SUM(traffic_out) as traffic,
			                          SUM(events_created)+SUM(events_updated)+SUM(events_deleted) as events
			                   FROM {pre}bms_caldav_stats WHERE date=?', $date);
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$totals['caldav'] = array(
					'sessions' => (int)$row['sessions'],
					'traffic' => (int)$row['traffic'],
					'events' => (int)$row['events']
				);
			}
			$res->Free();
			
			// CardDAV
			$res = $db->Query('SELECT SUM(sessions) as sessions, 
			                          SUM(traffic_in)+SUM(traffic_out) as traffic,
			                          SUM(contacts_created)+SUM(contacts_updated)+SUM(contacts_deleted) as contacts
			                   FROM {pre}bms_carddav_stats WHERE date=?', $date);
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$totals['carddav'] = array(
					'sessions' => (int)$row['sessions'],
					'traffic' => (int)$row['traffic'],
					'contacts' => (int)$row['contacts']
				);
			}
			$res->Free();
			
			// WebDAV
			$res = $db->Query('SELECT SUM(sessions) as sessions, 
			                          SUM(traffic_in)+SUM(traffic_out) as traffic,
			                          SUM(files_uploaded)+SUM(files_downloaded)+SUM(files_deleted) as files
			                   FROM {pre}bms_webdav_stats WHERE date=?', $date);
			if($row = $res->FetchArray(MYSQLI_ASSOC)) {
				$totals['webdav'] = array(
					'sessions' => (int)$row['sessions'],
					'traffic' => (int)$row['traffic'],
					'files' => (int)$row['files']
				);
			}
			$res->Free();
		} catch(Exception $e) {
			// Ignore
		}
		
		return $totals;
	}
}

/**
 * Helper function: BMLog wrapper
 */
function BMLog($component, $priority, $message)
{
	if(function_exists('PutLog')) {
		PutLog($message, $priority, __FILE__, __LINE__);
	}
}

?>
