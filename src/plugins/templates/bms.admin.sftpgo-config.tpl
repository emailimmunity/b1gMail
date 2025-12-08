<form action="{$pageURL}&sid={$sid}&action=sftpgo&save=true" method="post" onsubmit="spin(this)">
	
	{* Container & Version Status *}
	{if $sftpgo_container_status}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $sftpgo_container_status.running}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>
			{if $sftpgo_container_status.running}
				🟢 Container <code>{$sftpgo_container_status.name}</code> läuft | Uptime: {$sftpgo_container_status.uptime}
			{else}
				🔴 Container <code>{$sftpgo_container_status.name}</code> NICHT AKTIV
			{/if}
		</strong>
		{if $sftpgo_version && $sftpgo_version.installed_version_found}
			<br><small>Version: {$sftpgo_version.installed_version}</small>
		{/if}
	</div>
	{/if}
	
	{if $syncResult}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $syncResult.success}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>{$syncResult.message}</strong>
	</div>
	{/if}
	
	{if $testResult}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $testResult.success}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>{$testResult.message}</strong>
	</div>
	{/if}
	
	<fieldset>
		<legend>📨 Protokoll-Übersicht</legend>
		<table width="100%" style="margin-bottom:20px;">
			<tr>
				<td style="padding:10px;background:#f8f9fa;border:1px solid #dee2e6;">
					<strong>SFTPGo unterstützt 4 Protokolle:</strong><br>
					✅ <strong>SFTP</strong> - Secure File Transfer (Standard: Port 2022)<br>
					✅ <strong>FTPS</strong> - FTP over SSL/TLS (Standard: Port 2021)<br>
					✅ <strong>WebDAV</strong> - Web-basierter Dateizugriff (Standard: Port 8090)<br>
					✅ <strong>HTTP/S</strong> - Web-UI + Admin API (Standard: Port 9090)
				</td>
			</tr>
		</table>
	</fieldset>
	
	<fieldset>
		<legend>🗂️ SFTPGo (File Server)</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="../plugins/templates/images/bms_logo.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">SFTPGo aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="sftpgo_enabled" id="sftpgo_enabled"{if $bms_prefs.sftpgo_enabled} checked="checked"{/if} />
					<label for="sftpgo_enabled">Server aktivieren</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Server-Hostname:</td>
				<td class="td2">
					<input type="text" name="sftpgo_server" value="{text value=$bms_prefs.sftpgo_server}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="td1">Multi-Domain Config:</td>
				<td class="td2">
					<strong>🌐 {$available_domains_count} Domain(s) erkannt</strong><br>
					<small>Automatische Erkennung aus b1gMail</small><br><br>
					
					<label><input type="radio" name="sftpgo_domain_mode" value="auto" {if $bms_prefs.sftpgo_domain_mode=='auto' || !$bms_prefs.sftpgo_domain_mode}checked{/if}> Automatisch (alle Domains)</label><br>
					<label><input type="radio" name="sftpgo_domain_mode" value="manual" {if $bms_prefs.sftpgo_domain_mode=='manual'}checked{/if}> Manuell</label><br><br>
					
					<div id="manual_sftpgo_domain_config" style="display:{if $bms_prefs.sftpgo_domain_mode=='manual'}block{else}none{/if};">
						<strong>Subdomain-Präfix:</strong> <input type="text" name="sftpgo_subdomain" value="{text value=$bms_prefs.sftpgo_subdomain}" size="15" placeholder="files" /><br>
						<small>Wird angewendet auf alle Domains</small><br><br>
						
						<strong>Erlaubte Domains:</strong><br>
						<textarea name="sftpgo_allowed_domains" rows="5" cols="50">{text value=$bms_prefs.sftpgo_allowed_domains}</textarea><br>
						<small>(Eine Domain pro Zeile, leer = alle)</small>
					</div>
					
					<script>
					document.querySelectorAll('input[name="sftpgo_domain_mode"]').forEach(function(radio) {
						radio.addEventListener('change', function() {
							document.getElementById('manual_sftpgo_domain_config').style.display = 
								this.value === 'manual' ? 'block' : 'none';
						});
					});
					</script>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🔌 Service Ports</legend>
		
		<table width="100%">
			<tr>
				<td align="left" valign="top" width="40"></td>
				<td class="td1" width="250">SFTP Port:</td>
				<td class="td2">
					<input type="text" name="sftpgo_sftp_port" value="{text value=$bms_prefs.sftpgo_sftp_port}" size="10" />
					<small>(Standard: 2022)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">FTPS Port:</td>
				<td class="td2">
					<input type="text" name="sftpgo_ftps_port" value="{text value=$bms_prefs.sftpgo_ftps_port}" size="10" />
					<small>(Standard: 2021)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">WebDAV Port:</td>
				<td class="td2">
					<input type="text" name="sftpgo_webdav_port" value="{text value=$bms_prefs.sftpgo_webdav_port}" size="10" />
					<small>(Standard: 8090)</small>
					<input type="checkbox" name="sftpgo_webdav_enabled" id="sftpgo_webdav_enabled"{if $bms_prefs.sftpgo_webdav_enabled} checked="checked"{/if} />
					<label for="sftpgo_webdav_enabled">Aktiviert</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">Admin UI Port:</td>
				<td class="td2">
					<input type="text" name="sftpgo_admin_port" value="{text value=$bms_prefs.sftpgo_admin_port}" size="10" />
					<small>(Standard: 9090)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🔐 Admin API</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="3" valign="top" width="40">
					<img src="{$tpldir}images/ico_admin.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">API URL:</td>
				<td class="td2">
					<input type="text" name="sftpgo_api_url" value="{text value=$bms_prefs.sftpgo_api_url}" size="60" />
				</td>
			</tr>
			<tr>
				<td class="td1">Admin-Benutzer:</td>
				<td class="td2">
					<input type="text" name="sftpgo_admin_user" value="{text value=$bms_prefs.sftpgo_admin_user}" size="30" />
				</td>
			</tr>
			<tr>
				<td class="td1">Admin-Passwort:</td>
				<td class="td2">
					<input type="password" name="sftpgo_admin_pass" value="{text value=$bms_prefs.sftpgo_admin_pass}" size="30" />
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>☁️ S3 Storage Backend</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="5" valign="top" width="40">
					<img src="{$tpldir}images/ico_storage.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">S3 aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="sftpgo_s3_enabled" id="sftpgo_s3_enabled"{if $bms_prefs.sftpgo_s3_enabled} checked="checked"{/if} />
					<label for="sftpgo_s3_enabled">S3/Minio Backend</label>
				</td>
			</tr>
			<tr>
				<td class="td1">S3 Endpoint:</td>
				<td class="td2">
					<input type="text" name="sftpgo_s3_endpoint" value="{text value=$bms_prefs.sftpgo_s3_endpoint}" size="60" />
				</td>
			</tr>
			<tr>
				<td class="td1">Bucket Name:</td>
				<td class="td2">
					<input type="text" name="sftpgo_s3_bucket" value="{text value=$bms_prefs.sftpgo_s3_bucket}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="td1">Access Key:</td>
				<td class="td2">
					<input type="text" name="sftpgo_s3_access_key" value="{text value=$bms_prefs.sftpgo_s3_access_key}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="td1">Secret Key:</td>
				<td class="td2">
					<input type="password" name="sftpgo_s3_secret_key" value="{text value=$bms_prefs.sftpgo_s3_secret_key}" size="40" />
				</td>
			</tr>
		</table>
	</fieldset>

	<div align="center">
		<input type="submit" class="button" value="{lng p="save"}" />
		<input type="button" class="button" value="🔄 Konfiguration übertragen" onclick="location.href='{$pageURL}&sid={$sid}&action=sftpgo&sync=true';" />
		<input type="button" class="button" value="🧪 Verbindung testen" onclick="location.href='{$pageURL}&sid={$sid}&action=sftpgo&test=true';" />
	</div>

</form>
