{*
 * b1gMailServer Admin - External Protocols Status
 * Cyrus (IMAP/POP3/JMAP), Postfix (SMTP), Grommunio (MAPI/EWS/EAS), SFTPGo (SFTP/FTPS/S3)
 *}

<h2>Externe Mail-Server - Status-Übersicht</h2>
<p style="color: #666; font-size: 0.9em;">
	<i class="fa fa-info-circle"></i>
	<strong>Hinweis:</strong> Diese Seite zeigt den Status <strong>externer</strong> Mail-Server-Integrationen 
	(Cyrus, Postfix, Grommunio, SFTPGo). Die integrierten b1gMail-Protokolle (POP3/IMAP/SMTP) finden Sie unter "Übersicht".
</p>

{* Cyrus IMAP/POP3/JMAP *}
<fieldset>
	<legend><i class="fa fa-server"></i> Cyrus IMAP (IMAP/POP3/JMAP)</legend>
	
	{if $cyrus_status.enabled}
		<div class="row mb-3">
			<div class="col-md-12">
				<div class="alert alert-{if $cyrus_status.connection == 'success'}success{else}danger{/if}">
					<h5>
						<i class="fa {if $cyrus_status.connection == 'success'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
						Connection: {if $cyrus_status.connection == 'success'}Erfolgreich{else}Fehlgeschlagen{/if}
					</h5>
					{if $cyrus_status.error}
						<p><strong>Fehler:</strong> {$cyrus_status.error}</p>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>Server:</strong> {$cyrus_status.server}</div>
					<div class="list-group-item">
						<strong>IMAP Port:</strong> {$cyrus_status.imap_port}</div>
					<div class="list-group-item">
						<strong>POP3 Port:</strong> {$cyrus_status.pop3_port}</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>JMAP:</strong> {if $cyrus_status.jmap_enabled}Aktiviert{else}Deaktiviert{/if}</div>
					{if $cyrus_status.jmap_enabled}
						<div class="list-group-item">
							<strong>JMAP URL:</strong> <code>{$cyrus_status.jmap_url}</code></div>
					{/if}
				</div>
			</div>
		</div>
	{else}
		<div class="alert alert-warning">
			<i class="fa fa-warning"></i> {$cyrus_status.message}
		</div>
	{/if}
</fieldset>

{* Postfix SMTP *}
<fieldset>
	<legend><i class="fa fa-envelope"></i> Postfix (SMTP)</legend>
	
	{if $postfix_status.enabled}
		<div class="row mb-3">
			<div class="col-md-12">
				<div class="alert alert-{if $postfix_status.connection == 'success'}success{else}danger{/if}">
					<h5>
						<i class="fa {if $postfix_status.connection == 'success'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
						Connection: {if $postfix_status.connection == 'success'}Erfolgreich{else}Fehlgeschlagen{/if}
					</h5>
					{if $postfix_status.banner}
						<p><strong>Banner:</strong> <code>{$postfix_status.banner}</code></p>
					{/if}
					{if $postfix_status.error}
						<p><strong>Fehler:</strong> {$postfix_status.error}</p>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>Server:</strong> {$postfix_status.server}</div>
					<div class="list-group-item">
						<strong>SMTP Port:</strong> {$postfix_status.smtp_port}</div>
					<div class="list-group-item">
						<strong>Submission Port:</strong> {$postfix_status.submission_port}</div>
					<div class="list-group-item">
						<strong>SMTPS Port:</strong> {$postfix_status.smtps_port}</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>Admin API:</strong> <code>{$postfix_status.admin_api}</code></div>
				</div>
			</div>
		</div>
		
		<div class="row mt-3">
			<div class="col-md-12">
				<a href="admin.php?page=bms&action=postfix" class="btn btn-primary">
					<i class="fa fa-cog"></i> Postfix SMTP Verwaltung & Queue Management
				</a>
			</div>
		</div>
	{else}
		<div class="alert alert-warning">
			<i class="fa fa-warning"></i> {$postfix_status.message}
		</div>
	{/if}
</fieldset>

{* Grommunio MAPI/EWS/EAS *}
<fieldset>
	<legend><i class="fa fa-exchange"></i> Grommunio (MAPI/EWS/EAS)</legend>
	
	{if $grommunio_status.enabled}
		<div class="row mb-3">
			<div class="col-md-12">
				<div class="alert alert-{if $grommunio_status.connection == 'success'}success{else}danger{/if}">
					<h5>
						<i class="fa {if $grommunio_status.connection == 'success'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
						Connection: {if $grommunio_status.connection == 'success'}Erfolgreich{else}Fehlgeschlagen{/if}
					</h5>
					{if $grommunio_status.error}
						<p><strong>Fehler:</strong> {$grommunio_status.error}</p>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>Server:</strong> {$grommunio_status.server}</div>
					<div class="list-group-item">
						<strong>Admin API:</strong> <code>{$grommunio_status.admin_api}</code></div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>MAPI URL:</strong> <code>{$grommunio_status.mapi_url}</code></div>
					<div class="list-group-item">
						<strong>EWS URL:</strong> <code>{$grommunio_status.ews_url}</code></div>
					<div class="list-group-item">
						<strong>EAS URL:</strong> <code>{$grommunio_status.eas_url}</code></div>
				</div>
			</div>
		</div>
		
		<div class="row mt-3">
			<div class="col-md-12">
				<a href="admin.php?page=bms&action=grommunio" class="btn btn-primary">
					<i class="fa fa-cog"></i> Grommunio Verwaltung & User-Sync
				</a>
			</div>
		</div>
	{else}
		<div class="alert alert-warning">
			<i class="fa fa-warning"></i> {$grommunio_status.message}
		</div>
	{/if}
</fieldset>

{* SFTPGo SFTP/FTPS/S3 *}
<fieldset>
	<legend><i class="fa fa-cloud-upload"></i> SFTPGo (SFTP/FTPS/S3)</legend>
	
	{if $sftpgo_status.enabled}
		<div class="row mb-3">
			<div class="col-md-12">
				<div class="alert alert-{if $sftpgo_status.connection == 'success'}success{else}danger{/if}">
					<h5>
						<i class="fa {if $sftpgo_status.connection == 'success'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
						SFTP Connection: {if $sftpgo_status.connection == 'success'}Erfolgreich{else}Fehlgeschlagen{/if}
					</h5>
					{if $sftpgo_status.api_connection}
						<p><strong>API Connection:</strong> {if $sftpgo_status.api_connection == 'success'}Erfolgreich{else}Fehlgeschlagen{/if}</p>
					{/if}
					{if $sftpgo_status.error}
						<p><strong>Fehler:</strong> {$sftpgo_status.error}</p>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>Server:</strong> {$sftpgo_status.server}</div>
					<div class="list-group-item">
						<strong>SFTP Port:</strong> {$sftpgo_status.sftp_port}</div>
					<div class="list-group-item">
						<strong>FTPS Port:</strong> {$sftpgo_status.ftps_port}</div>
					<div class="list-group-item">
						<strong>Admin API:</strong> <code>{$sftpgo_status.admin_api}</code></div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="list-group">
					<div class="list-group-item">
						<strong>S3:</strong> {if $sftpgo_status.s3_enabled}Aktiviert{else}Deaktiviert{/if}</div>
					{if $sftpgo_status.s3_enabled}
						<div class="list-group-item">
							<strong>S3 Endpoint:</strong> <code>{$sftpgo_status.s3_endpoint}</code></div>
					{/if}
					<div class="list-group-item">
						<strong>WebDAV:</strong> {if $sftpgo_status.webdav_enabled}Aktiviert{else}Deaktiviert{/if}</div>
					{if $sftpgo_status.webdav_enabled}
						<div class="list-group-item">
							<strong>WebDAV Endpoint:</strong> <code>{$sftpgo_status.webdav_endpoint}</code></div>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="row mt-3">
			<div class="col-md-12">
				<a href="admin.php?page=bms&action=sftpgo" class="btn btn-primary">
					<i class="fa fa-cog"></i> SFTPGo Verwaltung & User-Sync
				</a>
			</div>
		</div>
	{else}
		<div class="alert alert-warning">
			<i class="fa fa-warning"></i> {$sftpgo_status.message}
		</div>
	{/if}
</fieldset>

{* Help Text *}
<div class="alert alert-info mt-4">
	<h5><i class="fa fa-info-circle"></i> Hinweise</h5>
	<ul>
		<li><strong>Cyrus IMAP:</strong> Stellt IMAP, POP3 und JMAP bereit. Konfiguration in protocols-config.inc.php</li>
		<li><strong>Postfix:</strong> SMTP-Server für E-Mail-Versand. Konfiguration in protocols-config.inc.php</li>
		<li><strong>Grommunio:</strong> Exchange-kompatible Groupware (MAPI/EWS/EAS). Konfiguration in protocols-config.inc.php</li>
		<li><strong>SFTPGo:</strong> Datei-Transfer via SFTP/FTPS/S3. Konfiguration in protocols-config.inc.php</li>
		<li><strong>Connection-Tests:</strong> Die Verbindungen werden automatisch getestet und der Status angezeigt.</li>
	</ul>
</div>
