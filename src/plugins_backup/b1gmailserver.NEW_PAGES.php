<?php
/**
 * Neue Protocol Admin-Pages für b1gMailServer
 * HYBRID-ARCHITEKTUR: Externe Protokolle als Gateways, b1gMail als Backend
 * 
 * Architektur:
 * ============
 * Extern (User-facing):
 *   - Dovecot: IMAP/POP3/Sieve (Ports 2143, 2993, 2110, 2995)
 *   - Postfix: SMTP Gateway (Ports 2025, 2587)
 *   - Grommunio: MAPI/EWS/EAS (Externe VM, Outlook/Mobile)
 *   - SFTPGo: SFTP/FTPS/S3/WebDAV (Ports 2022, 9090)
 * 
 * Intern (Backend):
 *   - b1gMail POP3/IMAP/SMTP: KEINE externen Ports
 *   - Nur für Webmailer & Core-Logik
 *   - Externe Server delegieren an Backend
 * 
 * Ergebnis:
 *   ✅ Keine Port-Konflikte
 *   ✅ Externe Protokolle funktionieren
 *   ✅ b1gMail-Core bleibt stabil
 *   ✅ Webmailer funktioniert
 */

	/**
	 * Dovecot admin page (IMAP/POP3/Sieve Gateway)
	 */
	function _dovecotPage()
	{
		global $tpl;
		
		$info = array(
			'title' => 'Dovecot (IMAP/POP3 Gateway)',
			'description' => 'Dovecot fungiert als externes Gateway für IMAP/POP3/Sieve-Protokolle.<br><br>' .
							'<b>Architektur:</b><br>' .
							'User → Dovecot (Port 2143/2993/2110/2995) → b1gMail Backend → MySQL<br><br>' .
							'<b>Funktion:</b><br>' .
							'✅ Moderne IMAP/POP3-Implementation<br>' .
							'✅ JMAP-Unterstützung<br>' .
							'✅ Sieve Mail-Filter<br>' .
							'✅ Mobile Push (IDLE)<br><br>' .
							'<b>Backend:</b><br>' .
							'b1gMails eigene POP3/IMAP-Server (siehe Tab "POP3 (Backend)" und "IMAP (Backend)") ' .
							'laufen intern weiter für Webmailer und Core-Funktionen, haben aber KEINE externen Ports.',
			'ports' => array(
				'2143' => 'IMAP (unverschlüsselt)',
				'2993' => 'IMAPS (TLS)',
				'2110' => 'POP3 (unverschlüsselt)',
				'2995' => 'POP3S (TLS)'
			),
			'docker_service' => 'b1gmail-cyrus',
			'config_file' => 'docker-compose.yml'
		);
		
		$tpl->assign('protocolInfo', $info);
		$tpl->assign('pageURL', $this->_adminLink('&do=dovecot'));
		$tpl->assign('page', $this->_templatePath('bms.admin.protocol.tpl'));
	}
	
	/**
	 * Postfix admin page (SMTP Gateway)
	 */
	function _postfixPage()
	{
		global $tpl;
		
		$info = array(
			'title' => 'Postfix (SMTP Gateway)',
			'description' => 'Postfix fungiert als externes Gateway für SMTP-Protokoll.<br><br>' .
							'<b>Architektur:</b><br>' .
							'User → Postfix (Port 2025/2587) → b1gMail Backend → MySQL<br><br>' .
							'<b>Funktion:</b><br>' .
							'✅ Moderne SMTP-Implementation<br>' .
							'✅ Spam-Filter Integration<br>' .
							'✅ Virus-Scan Integration<br>' .
							'✅ Relay-Funktionen<br><br>' .
							'<b>Backend:</b><br>' .
							'b1gMails eigener SMTP-Server (siehe Tab "SMTP (Backend)") ' .
							'läuft intern weiter für Webmailer und Mail-Verarbeitung, hat aber KEINE externen Ports.',
			'ports' => array(
				'2025' => 'SMTP (unverschlüsselt)',
				'2587' => 'Submission (STARTTLS)'
			),
			'docker_service' => 'b1gmail-postfix',
			'config_file' => 'docker-compose.yml'
		);
		
		$tpl->assign('protocolInfo', $info);
		$tpl->assign('pageURL', $this->_adminLink('&do=postfix'));
		$tpl->assign('page', $this->_templatePath('bms.admin.protocol.tpl'));
	}
	
	/**
	 * Grommunio admin page (MAPI/EWS/EAS)
	 */
	function _grommunioPage()
	{
		global $tpl;
		
		$info = array(
			'title' => 'Grommunio (MAPI/EWS/EAS)',
			'description' => 'Grommunio ist ein vollständiger Exchange-Ersatz für Outlook und Mobile-Geräte.<br><br>' .
							'<b>Architektur:</b><br>' .
							'User → Grommunio VM (192.168.178.144:8443) → b1gMail Backend → MySQL<br><br>' .
							'<b>Protokolle:</b><br>' .
							'✅ MAPI (Microsoft Outlook Desktop)<br>' .
							'✅ EWS (Exchange Web Services)<br>' .
							'✅ ActiveSync (Mobile: iPhone, Android)<br>' .
							'✅ CalDAV/CardDAV (Kalender/Kontakte)<br><br>' .
							'<b>Integration:</b><br>' .
							'Grommunio läuft als externe Hyper-V VM und synchronisiert sich mit b1gMail via API.<br><br>' .
							'<b>Admin-UI:</b> https://192.168.178.144:8443',
			'ports' => array(
				'8443' => 'Web Admin UI (HTTPS)',
				'443' => 'MAPI/EWS/EAS (HTTPS)'
			),
			'docker_service' => 'Externe Hyper-V VM',
			'config_file' => 'Siehe Grommunio Admin UI'
		);
		
		$tpl->assign('protocolInfo', $info);
		$tpl->assign('pageURL', $this->_adminLink('&do=grommunio'));
		$tpl->assign('page', $this->_templatePath('bms.admin.protocol.tpl'));
	}
	
	/**
	 * SFTPGo admin page (SFTP/S3/WebDAV)
	 */
	function _sftpgoPage()
	{
		global $tpl;
		
		$info = array(
			'title' => 'SFTPGo (SFTP/FTPS/S3/WebDAV)',
			'description' => 'SFTPGo ist ein moderner File-Server mit S3-Backend.<br><br>' .
							'<b>Architektur:</b><br>' .
							'User → SFTPGo (Port 2022/9090) → Minio S3 (b1gmail-storage)<br><br>' .
							'<b>Protokolle:</b><br>' .
							'✅ SFTP (Port 2022)<br>' .
							'✅ FTPS (Port 2021)<br>' .
							'✅ S3-API (via Port 9090)<br>' .
							'✅ WebDAV (Port 8090)<br><br>' .
							'<b>Storage-Backend:</b><br>' .
							'Alle User-Dateien werden in Minio S3 (Bucket: b1gmail-storage) gespeichert.<br>' .
							'Dies ermöglicht UNIFIED STORAGE mit E-Mails zusammen!<br><br>' .
							'<b>Admin-UI:</b> http://localhost:9090<br>' .
							'<b>Login:</b> admin / admin123',
			'ports' => array(
				'2022' => 'SFTP',
				'2021' => 'FTPS',
				'8090' => 'WebDAV',
				'9090' => 'Web Admin UI + S3-API'
			),
			'docker_service' => 'b1gmail-sftpgo',
			'config_file' => 'docker-compose.yml',
			'links' => array(
				array('title' => 'SFTPGo Web-UI', 'url' => 'http://localhost:9090'),
				array('title' => 'Minio Console', 'url' => 'http://localhost:9001')
			)
		);
		
		$tpl->assign('protocolInfo', $info);
		$tpl->assign('pageURL', $this->_adminLink('&do=sftpgo'));
		$tpl->assign('page', $this->_templatePath('bms.admin.protocol.tpl'));
	}

?>
