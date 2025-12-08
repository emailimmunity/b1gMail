{*
 * ============================================================================
 * SFTPGO/CLOUD STORAGE INTEGRATION FOR bms.user.tpl
 * ============================================================================
 * 
 * Diese Code-Blöcke müssen in bms.user.tpl eingefügt werden
 * um SFTP/FTPS/S3 Zugangsdaten anzuzeigen
 * 
 * EINFÜGEN NACH: Grommunio-Sektion
 * 
 * ============================================================================
 *}

{* SFTPGo (Cloud Storage) Protocol *}
{if $haveSFTPGo}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-cloud"></i> Cloud Storage (SFTP/FTP/S3)
		</th>
	</tr>

	<tr>
		<td class="listTableLeft">&nbsp;</td>
		<td class="listTableRight">
			Sicherer Datei-Speicher mit verschiedenen Protokollen.<br />
			Kompatibel mit allen gängigen FTP-Clients und Cloud-Tools.
		</td>
	</tr>

	{* SFTP (Secure FTP) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-lock"></i> {lng p="sftpgo_sftp"}:</td>
		<td class="listTableRight">
			<strong>Server:</strong> <code>{$bms_prefs.user_sftpgo_server}</code><br />
			<strong>Port:</strong> <code>{$bms_prefs.user_sftp_port}</code><br />
			<strong>{lng p="username"}:</strong> <code>{$username}</code><br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<br />
			<strong>Verbindungs-URL:</strong><br />
			<code>sftp://{$username}@{$bms_prefs.user_sftpgo_server}:{$bms_prefs.user_sftp_port}</code><br />
			<br />
			<span class="badge bg-success"><i class="fa fa-shield"></i> SSH-verschlüsselt</span>
		</td>
	</tr>

	{* FTPS (FTP over SSL) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-shield"></i> {lng p="sftpgo_ftps"}:</td>
		<td class="listTableRight">
			<strong>Server:</strong> <code>{$bms_prefs.user_sftpgo_server}</code><br />
			<strong>Port:</strong> <code>{$bms_prefs.user_ftps_port}</code><br />
			<strong>{lng p="username"}:</strong> <code>{$username}</code><br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<strong>Verschlüsselung:</strong> TLS/SSL (explizit)<br />
			<br />
			<strong>Verbindungs-URL:</strong><br />
			<code>ftps://{$username}@{$bms_prefs.user_sftpgo_server}:{$bms_prefs.user_ftps_port}</code><br />
			<br />
			<span class="badge bg-info"><i class="fa fa-lock"></i> TLS-verschlüsselt</span>
		</td>
	</tr>

	{* S3 (Cloud API) *}
	{if $bms_prefs.user_s3_enabled}
	<tr>
		<td class="listTableLeft"><i class="fa fa-cloud"></i> {lng p="sftpgo_s3"}:</td>
		<td class="listTableRight">
			<strong>Endpoint:</strong> <code>{$bms_prefs.user_s3_endpoint}</code><br />
			<strong>Region:</strong> <code>{$bms_prefs.user_s3_region}</code><br />
			<strong>Access Key:</strong> <code>{$bms_prefs.user_s3_access_key}</code><br />
			<strong>Secret Key:</strong> <code>••••••••••••••••</code> 
			<a href="#" onclick="alert('{$bms_prefs.user_s3_secret_key}'); return false;">
				<i class="fa fa-eye"></i> Anzeigen
			</a><br />
			<br />
			<strong>Kompatibel mit:</strong><br />
			• AWS CLI<br />
			• S3 Browser<br />
			• Cyberduck<br />
			• rclone<br />
			• boto3 (Python)<br />
			<br />
			<span class="badge bg-primary"><i class="fa fa-code"></i> Programmierbar</span>
		</td>
	</tr>
	{/if}

	{* WebDAV (Netzlaufwerk) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-folder"></i> {lng p="sftpgo_webdav"}:</td>
		<td class="listTableRight">
			<strong>URL:</strong> <code>{$bms_prefs.user_webdav_url}</code><br />
			<strong>{lng p="username"}:</strong> <code>{$username}</code><br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<br />
			<strong>Als Netzlaufwerk einbinden:</strong><br />
			<strong>Windows:</strong> Rechtsklick auf "Dieser PC" → "Netzlaufwerk verbinden"<br />
			<strong>macOS:</strong> Finder → "Gehe zu" → "Mit Server verbinden"<br />
			<strong>Linux:</strong> Nautilus → "Mit Server verbinden"
		</td>
	</tr>

	{* Speicher-Quota *}
	{if $sftpgo_quota}
	<tr>
		<td class="listTableLeft"><i class="fa fa-database"></i> {lng p="sftpgo_quota"}:</td>
		<td class="listTableRight">
			<strong>Verwendet:</strong> {$sftpgo_quota.used_gb} GB von {$sftpgo_quota.limit_gb} GB
			({$sftpgo_quota.percent}%)<br />
			<div style="width: 100%; height: 20px; background-color: #e0e0e0; margin-top: 5px;">
				<div style="width: {$sftpgo_quota.percent}%; height: 20px; 
				     background-color: {if $sftpgo_quota.percent >= 90}#f44336{elseif $sftpgo_quota.percent >= 75}#ff9800{else}#4caf50{/if};"></div>
			</div>
			<small>Dateien: {$sftpgo_quota.files|number_format}</small>
		</td>
	</tr>
	{/if}

	{* Client Downloads *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-download"></i> {lng p="sftpgo_clients"}:</td>
		<td class="listTableRight">
			<strong>SFTP/FTPS Clients:</strong><br />
			<a href="https://filezilla-project.org/" target="_blank">
				<i class="fa fa-external-link"></i> FileZilla
			</a> (Windows/Mac/Linux - kostenlos)<br />
			<a href="https://winscp.net/" target="_blank">
				<i class="fa fa-external-link"></i> WinSCP
			</a> (Windows - kostenlos)<br />
			<a href="https://cyberduck.io/" target="_blank">
				<i class="fa fa-external-link"></i> Cyberduck
			</a> (Windows/Mac - kostenlos)<br />
			<br />
			<strong>S3 Clients:</strong><br />
			<a href="https://aws.amazon.com/cli/" target="_blank">
				<i class="fa fa-external-link"></i> AWS CLI
			</a> (Kommandozeile)<br />
			<a href="https://s3browser.com/" target="_blank">
				<i class="fa fa-external-link"></i> S3 Browser
			</a> (Windows)<br />
			<a href="https://rclone.org/" target="_blank">
				<i class="fa fa-external-link"></i> rclone
			</a> (Sync-Tool)
		</td>
	</tr>

	{* Setup-Anleitungen *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-book"></i> Setup-Anleitungen:</td>
		<td class="listTableRight">
			<details>
				<summary><strong>FileZilla (SFTP) einrichten</strong></summary>
				<ol>
					<li>FileZilla öffnen</li>
					<li>Datei → Servermanager → "Neuer Server"</li>
					<li>Host: <code>{$bms_prefs.user_sftpgo_server}</code></li>
					<li>Port: <code>{$bms_prefs.user_sftp_port}</code></li>
					<li>Protokoll: SFTP</li>
					<li>Benutzer: <code>{$username}</code></li>
					<li>Passwort: Ihr Passwort</li>
					<li>Verbinden!</li>
				</ol>
			</details>
			<br />
			<details>
				<summary><strong>AWS CLI (S3) einrichten</strong></summary>
				<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">
# AWS CLI konfigurieren
aws configure --profile b1gmail
AWS Access Key ID: {$bms_prefs.user_s3_access_key}
AWS Secret Access Key: [Ihr Secret Key]
Default region name: {$bms_prefs.user_s3_region}

# Dateien auflisten
aws s3 ls --profile b1gmail --endpoint-url {$bms_prefs.user_s3_endpoint}

# Datei hochladen
aws s3 cp file.txt s3://mybucket/ --profile b1gmail --endpoint-url {$bms_prefs.user_s3_endpoint}
				</pre>
			</details>
			<br />
			<details>
				<summary><strong>Windows Netzlaufwerk (WebDAV)</strong></summary>
				<ol>
					<li>Windows Explorer öffnen</li>
					<li>Rechtsklick auf "Dieser PC"</li>
					<li>"Netzlaufwerk verbinden"</li>
					<li>URL: <code>{$bms_prefs.user_webdav_url}</code></li>
					<li>Benutzer: <code>{$username}</code></li>
					<li>Passwort: Ihr Passwort</li>
					<li>Fertig!</li>
				</ol>
			</details>
		</td>
	</tr>

	{* Vorteile *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-star"></i> Vorteile:</td>
		<td class="listTableRight">
			✅ <strong>SFTP:</strong> SSH-verschlüsselt, sehr sicher<br />
			✅ <strong>FTPS:</strong> Legacy-kompatibel, TLS-verschlüsselt<br />
			✅ <strong>S3 API:</strong> Programmierbar, Cloud-kompatibel<br />
			✅ <strong>WebDAV:</strong> Als Laufwerk nutzbar<br />
			✅ Multi-Protokoll-Zugriff auf dieselben Dateien<br />
			✅ Automatische Verschlüsselung<br />
			✅ Quota-Management
		</td>
	</tr>

	{* Sicherheitshinweis *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-exclamation-triangle"></i> Sicherheit:</td>
		<td class="listTableRight">
			<div style="padding: 10px; background-color: #fff3e0; border-left: 3px solid #ff9800;">
				<strong>⚠️ Wichtige Hinweise:</strong><br />
				• Verwenden Sie <strong>sichere Passwörter</strong><br />
				• Geben Sie Ihre <strong>S3-Credentials</strong> nicht weiter<br />
				• Nutzen Sie <strong>SSH-Keys</strong> für SFTP (empfohlen)<br />
				• Aktivieren Sie <strong>2FA</strong> wenn verfügbar
			</div>
		</td>
	</tr>
</table>
<br />
{/if}

{*
 * ============================================================================
 * ZUSÄTZLICHE SMARTY-VARIABLEN FÜR bms.user.tpl
 * ============================================================================
 * 
 * In b1gmailserver.plugin.php, Methode UserPage():
 *
 * $tpl->assign('haveSFTPGo', defined('SFTPGO_ENABLED') && SFTPGO_ENABLED);
 * 
 * // SFTPGo Config
 * if(defined('SFTPGO_ENABLED') && SFTPGO_ENABLED)
 * {
 *     $bms_prefs['user_sftpgo_server'] = SFTPGO_SERVER;
 *     $bms_prefs['user_sftp_port'] = SFTPGO_SFTP_PORT;
 *     $bms_prefs['user_ftps_port'] = SFTPGO_FTPS_PORT;
 *     $bms_prefs['user_s3_endpoint'] = SFTPGO_S3_ENDPOINT;
 *     $bms_prefs['user_s3_region'] = SFTPGO_S3_REGION;
 *     $bms_prefs['user_webdav_url'] = SFTPGO_WEBDAV_ENDPOINT;
 *     $bms_prefs['user_s3_enabled'] = SFTPGO_S3_ENABLED;
 *     
 *     // Get user's S3 credentials
 *     $res = $db->Query('SELECT sftpgo_s3_access_key, sftpgo_s3_secret_key FROM {pre}users WHERE id=?', $userRow['id']);
 *     $creds = $res->FetchArray(MYSQLI_ASSOC);
 *     $res->Free();
 *     
 *     $bms_prefs['user_s3_access_key'] = $creds['sftpgo_s3_access_key'];
 *     $bms_prefs['user_s3_secret_key'] = $creds['sftpgo_s3_secret_key'];
 *     
 *     // Get quota
 *     if(function_exists('sftpgoHookGetQuota'))
 *     {
 *         $quota = sftpgoHookGetQuota($userRow['email']);
 *         $tpl->assign('sftpgo_quota', $quota);
 *     }
 * }
 * 
 * ============================================================================
 *}
