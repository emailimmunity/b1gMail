{*
 * ============================================================================
 * SFTPGo Admin Template
 * ============================================================================
 * Analog zu: bms.admin.cyrus.tpl & bms.admin.grommunio.tpl
 * ============================================================================
 *}

{if $_tplname=='modern'}
<div id="contentHeader">
	<div class="left">
		<i class="fa fa-cloud" aria-hidden="true"></i>
		SFTPGo (Cloud Storage)
	</div>
</div>

<div class="scrollContainer"><div class="pad">
{else}
<h1><i class="fa fa-cloud" aria-hidden="true"></i> SFTPGo (Cloud Storage)</h1>
{/if}

{* Connection Status *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-plug"></i> Verbindungs-Status
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">SFTPGo Server:</td>
		<td class="listTableRight">
			{if $sftpgo_connection.connected}
				<span style="color: green;"><i class="fa fa-check-circle"></i> <strong>Verbunden</strong></span><br />
				<small>Version: {$sftpgo_connection.version}</small>
			{else}
				<span style="color: red;"><i class="fa fa-times-circle"></i> <strong>Getrennt</strong></span><br />
				<small>Fehler: {$sftpgo_connection.error}</small>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Protokolle:</td>
		<td class="listTableRight">
			<i class="fa fa-lock"></i> <strong>SFTP</strong> (Port 2022)<br />
			<i class="fa fa-shield"></i> <strong>FTPS</strong> (Port 990)<br />
			<i class="fa fa-cloud"></i> <strong>S3 API</strong> (Port 8333)<br />
			<i class="fa fa-folder"></i> <strong>WebDAV</strong> (Port 8090)
		</td>
	</tr>
</table>
<br />

{* Statistics Overview *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-bar-chart"></i> Statistiken
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Gesamt Benutzer:</td>
		<td class="listTableRight">
			<strong>{$sftpgo_stats.total_users|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">SFTPGo aktiviert:</td>
		<td class="listTableRight">
			<strong>{$sftpgo_stats.enabled_users|number_format}</strong>
			({if $sftpgo_stats.total_users > 0}{($sftpgo_stats.enabled_users / $sftpgo_stats.total_users * 100)|string_format:"%.1f"}{else}0{/if}%)
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Speicher-Nutzung:</td>
		<td class="listTableRight">
			<strong>{$sftpgo_stats.total_storage_gb|number_format:2} GB</strong>
			({$sftpgo_stats.total_files|number_format} Dateien)
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Übertragungen (heute):</td>
		<td class="listTableRight">
			<strong>{$sftpgo_stats.transfers_today|number_format}</strong>
			(↑ {$sftpgo_stats.uploads_today|number_format} / ↓ {$sftpgo_stats.downloads_today|number_format})<br />
			<small>Volumen: {$sftpgo_stats.bytes_today_gb|number_format:2} GB</small>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Aktive Verbindungen:</td>
		<td class="listTableRight">
			<strong>{$sftpgo_stats.active_connections|number_format}</strong><br />
			<small>
				SFTP: {$sftpgo_stats.sftp_connections|number_format} | 
				FTPS: {$sftpgo_stats.ftps_connections|number_format} | 
				S3: {$sftpgo_stats.s3_connections|number_format}
			</small>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Fehler (heute):</td>
		<td class="listTableRight">
			{if $sftpgo_stats.errors_today > 0}
				<span style="color: red;"><strong>{$sftpgo_stats.errors_today|number_format}</strong></span>
			{else}
				<span style="color: green;">0</span>
			{/if}
		</td>
	</tr>
</table>
<br />

{* Active Connections *}
{if $sftpgo_connections|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="7">
			<i class="fa fa-plug"></i> Aktive Verbindungen
		</th>
	</tr>
	<tr>
		<th>Benutzer</th>
		<th>Protokoll</th>
		<th>IP-Adresse</th>
		<th>Verbunden seit</th>
		<th>Gesendet</th>
		<th>Empfangen</th>
		<th>Aktion</th>
	</tr>
	{foreach from=$sftpgo_connections item=conn}
	<tr>
		<td>{$conn.email}</td>
		<td><span class="badge">{$conn.protocol}</span></td>
		<td><small><code>{$conn.ip_address}</code></small></td>
		<td>{$conn.connected_at|date_format:"%H:%M:%S"}</td>
		<td>{$conn.bytes_sent_formatted}</td>
		<td>{$conn.bytes_received_formatted}</td>
		<td>
			<a href="admin.php?page=bms&action=sftpgo&do=close_connection&connection_id={$conn.connection_id|urlencode}" 
			   class="btn btn-xs btn-danger"
			   onclick="return confirm('Verbindung wirklich trennen?')">
				<i class="fa fa-times"></i> Trennen
			</a>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Top Users by Storage *}
{if $sftpgo_top_users|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="5">
			<i class="fa fa-users"></i> Top Benutzer (Speicher)
		</th>
	</tr>
	<tr>
		<th>Benutzer</th>
		<th>Verwendet</th>
		<th>Quota</th>
		<th>Dateien</th>
		<th>Auslastung</th>
	</tr>
	{foreach from=$sftpgo_top_users item=user}
	<tr>
		<td>{$user.email}</td>
		<td><strong>{$user.used_gb} GB</strong></td>
		<td>{$user.quota_gb} GB</td>
		<td>{$user.used_quota_files|number_format}</td>
		<td>
			{$user.percent}%
			<div style="width: 100%; height: 10px; background-color: #e0e0e0; margin-top: 3px;">
				<div style="width: {$user.percent}%; height: 10px; background-color: {if $user.percent >= 90}#f44336{elseif $user.percent >= 75}#ff9800{else}#4caf50{/if};"></div>
			</div>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Recent Transfers *}
{if $sftpgo_transfers|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="7">
			<i class="fa fa-exchange"></i> Letzte Übertragungen
		</th>
	</tr>
	<tr>
		<th>Zeit</th>
		<th>Benutzer</th>
		<th>Aktion</th>
		<th>Protokoll</th>
		<th>Datei</th>
		<th>Größe</th>
		<th>Status</th>
	</tr>
	{foreach from=$sftpgo_transfers item=transfer}
	<tr>
		<td>{$transfer.timestamp|date_format:"%d.%m. %H:%M"}</td>
		<td><small>{$transfer.email}</small></td>
		<td>
			{if $transfer.action == 'upload'}
				<span style="color: green;">↑ Upload</span>
			{elseif $transfer.action == 'download'}
				<span style="color: blue;">↓ Download</span>
			{else}
				{$transfer.action}
			{/if}
		</td>
		<td><span class="badge">{$transfer.protocol}</span></td>
		<td><small>{$transfer.filepath|truncate:40}</small></td>
		<td>{$transfer.filesize_formatted}</td>
		<td>
			{if $transfer.status == 'success'}
				<span style="color: green;"><i class="fa fa-check"></i></span>
			{else}
				<span style="color: red;"><i class="fa fa-times"></i></span>
			{/if}
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Recent Errors *}
{if $sftpgo_errors|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="6">
			<i class="fa fa-exclamation-triangle"></i> Aktuelle Fehler
		</th>
	</tr>
	<tr>
		<th>Zeit</th>
		<th>Benutzer</th>
		<th>Protokoll</th>
		<th>Operation</th>
		<th>Fehler</th>
		<th>Aktion</th>
	</tr>
	{foreach from=$sftpgo_errors item=error}
	<tr>
		<td>{$error.timestamp|date_format:"%d.%m. %H:%M"}</td>
		<td><small>{$error.email}</small></td>
		<td><span class="badge">{$error.protocol}</span></td>
		<td><code>{$error.operation}</code></td>
		<td><small>{$error.error_message|truncate:60}</small></td>
		<td>
			<a href="admin.php?page=bms&action=sftpgo&do=resolve_error&error_id={$error.id}" 
			   class="btn btn-xs btn-success">
				<i class="fa fa-check"></i> Beheben
			</a>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Actions *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-cogs"></i> Aktionen
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Migration:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=sftpgo&do=start_migration" class="btn btn-primary">
				<i class="fa fa-play"></i> Benutzer-Migration starten
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Logs:</td>
		<td class="listTableRight">
			<a href="/var/log/sftpgo/sftpgo.log" target="_blank" class="btn btn-secondary">
				<i class="fa fa-file-text"></i> SFTPGo-Log anzeigen
			</a>
			<a href="/var/log/b1gmail-sftpgo.log" target="_blank" class="btn btn-secondary">
				<i class="fa fa-file-text"></i> Integration-Log anzeigen
			</a>
		</td>
	</tr>
</table>
<br />

{* System Info *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-info-circle"></i> System-Information
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">SFTPGo Version:</td>
		<td class="listTableRight">
			<code>{$sftpgo_connection.version}</code>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Unterstützte Clients:</td>
		<td class="listTableRight">
			<strong>SFTP:</strong> FileZilla, WinSCP, Cyberduck, OpenSSH<br />
			<strong>FTPS:</strong> FileZilla, Total Commander, WinSCP<br />
			<strong>S3:</strong> AWS CLI, S3 Browser, Cyberduck, rclone<br />
			<strong>WebDAV:</strong> Windows Explorer, macOS Finder, Nautilus
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Dokumentation:</td>
		<td class="listTableRight">
			<a href="/SFTPGO_DEPLOYMENT_GUIDE.md" target="_blank">
				<i class="fa fa-book"></i> Deployment Guide
			</a>
		</td>
	</tr>
</table>

{if $_tplname=='modern'}
</div></div>
{/if}
