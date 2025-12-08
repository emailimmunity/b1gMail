<?php
/**
 * Protocol Helper Functions
 * Erweiterte Funktionen für neue Protokoll-Unterstützung
 * 
 * Datum: 31. Oktober 2025
 */

/**
 * Get allowed protocols for group (MODERNIZED)
 * 
 * Unterstützt jetzt neue Protokolle:
 * - Cyrus (IMAP/POP3/JMAP)
 * - Grommunio (MAPI/EWS/EAS)
 * - SFTPGo (SFTP/FTPS/S3/WebDAV)
 * - Postfix (SMTP)
 *
 * @param BMGroup $thisGroup Group object
 * @return array List of allowed protocol strings
 */
function getGroupProtocols($thisGroup)
{
	$protocols = array();

	// NEUE PROTOKOLLE (bevorzugt)
	
	// Cyrus IMAP/POP3/JMAP
	if(isset($thisGroup->_row['cyrus']) && $thisGroup->_row['cyrus'] == 'yes')
	{
		$protocols[] = 'CYRUS_IMAP';
		$protocols[] = 'CYRUS_POP3';
		
		// JMAP optional (wenn in Gruppen-Optionen aktiviert)
		if(isset($thisGroup->options['cyrus_jmap_enabled']) && $thisGroup->options['cyrus_jmap_enabled'] == 'yes')
			$protocols[] = 'CYRUS_JMAP';
	}
	
	// Grommunio MAPI/EWS/EAS
	if(isset($thisGroup->_row['grommunio']) && $thisGroup->_row['grommunio'] == 'yes')
	{
		// Prüfe welche Sub-Protokolle aktiviert sind
		if(!isset($thisGroup->options['grommunio_mapi_enabled']) || $thisGroup->options['grommunio_mapi_enabled'] == 'yes')
			$protocols[] = 'GROMMUNIO_MAPI';
		if(!isset($thisGroup->options['grommunio_ews_enabled']) || $thisGroup->options['grommunio_ews_enabled'] == 'yes')
			$protocols[] = 'GROMMUNIO_EWS';
		if(!isset($thisGroup->options['grommunio_eas_enabled']) || $thisGroup->options['grommunio_eas_enabled'] == 'yes')
			$protocols[] = 'GROMMUNIO_EAS';
	}
	
	// SFTPGo SFTP/FTPS/S3/WebDAV
	if(isset($thisGroup->_row['sftpgo']) && $thisGroup->_row['sftpgo'] == 'yes')
	{
		$protocols[] = 'SFTPGO_SFTP';
		$protocols[] = 'SFTPGO_FTPS';
		$protocols[] = 'SFTPGO_WEBDAV';
		
		// S3 optional
		if(isset($thisGroup->options['sftpgo_s3_enabled']) && $thisGroup->options['sftpgo_s3_enabled'] == 'yes')
			$protocols[] = 'SFTPGO_S3';
	}
	
	// Postfix SMTP Gateway
	if(isset($thisGroup->_row['postfix']) && $thisGroup->_row['postfix'] == 'yes')
	{
		$protocols[] = 'POSTFIX_SMTP';
	}
	
	// LEGACY PROTOKOLLE (nur wenn neue nicht aktiviert sind)
	// Diese werden nur noch für Backwards-Kompatibilität unterstützt
	if(!in_array('CYRUS_POP3', $protocols) && isset($thisGroup->_row['pop3_legacy']) && $thisGroup->_row['pop3_legacy'] == 'yes')
		$protocols[] = 'POP3_LEGACY';
	if(!in_array('CYRUS_IMAP', $protocols) && isset($thisGroup->_row['imap_legacy']) && $thisGroup->_row['imap_legacy'] == 'yes')
		$protocols[] = 'IMAP_LEGACY';
	if(!in_array('POSTFIX_SMTP', $protocols) && isset($thisGroup->_row['smtp_legacy']) && $thisGroup->_row['smtp_legacy'] == 'yes')
		$protocols[] = 'SMTP_LEGACY';
	
	// Fallback für ältere Installationen (vor Migration)
	if(count($protocols) == 0)
	{
		if(isset($thisGroup->_row['pop3']) && $thisGroup->_row['pop3'] == 'yes')
			$protocols[] = 'POP3';
		if(isset($thisGroup->_row['imap']) && $thisGroup->_row['imap'] == 'yes')
			$protocols[] = 'IMAP';
		if(isset($thisGroup->_row['smtp']) && $thisGroup->_row['smtp'] == 'yes')
			$protocols[] = 'SMTP';
	}

	return $protocols;
}

/**
 * Get protocol display name
 */
function getProtocolDisplayName($protocol)
{
	static $names = array(
		// Neue Protokolle
		'CYRUS_IMAP' => 'Cyrus IMAP',
		'CYRUS_POP3' => 'Cyrus POP3',
		'CYRUS_JMAP' => 'Cyrus JMAP',
		'GROMMUNIO_MAPI' => 'Grommunio MAPI (Outlook)',
		'GROMMUNIO_EWS' => 'Grommunio EWS (Exchange)',
		'GROMMUNIO_EAS' => 'Grommunio ActiveSync (Mobile)',
		'SFTPGO_SFTP' => 'SFTPGo SFTP',
		'SFTPGO_FTPS' => 'SFTPGo FTPS',
		'SFTPGO_S3' => 'SFTPGo S3',
		'SFTPGO_WEBDAV' => 'SFTPGo WebDAV',
		'POSTFIX_SMTP' => 'Postfix SMTP',
		// Legacy
		'POP3' => 'POP3 (Legacy)',
		'IMAP' => 'IMAP (Legacy)',
		'SMTP' => 'SMTP (Legacy)',
		'POP3_LEGACY' => 'POP3 (Legacy)',
		'IMAP_LEGACY' => 'IMAP (Legacy)',
		'SMTP_LEGACY' => 'SMTP (Legacy)'
	);
	
	return isset($names[$protocol]) ? $names[$protocol] : $protocol;
}

/**
 * Check if protocol is legacy
 */
function isLegacyProtocol($protocol)
{
	return in_array($protocol, array('POP3', 'IMAP', 'SMTP', 'POP3_LEGACY', 'IMAP_LEGACY', 'SMTP_LEGACY'));
}

/**
 * Get protocol category
 */
function getProtocolCategory($protocol)
{
	if(strpos($protocol, 'CYRUS_') === 0) return 'cyrus';
	if(strpos($protocol, 'GROMMUNIO_') === 0) return 'grommunio';
	if(strpos($protocol, 'SFTPGO_') === 0) return 'sftpgo';
	if(strpos($protocol, 'POSTFIX_') === 0) return 'postfix';
	return 'legacy';
}

?>
