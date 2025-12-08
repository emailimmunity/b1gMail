<form action="{$pageURL}&sid={$sid}&action=grommunio&save=true" method="post" onsubmit="spin(this)">
	
	{* Version Status *}
	{if $grommunio_version}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $grommunio_version.status_class == 'success'}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{elseif $grommunio_version.status_class == 'danger'}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{else}background:#fff3cd;border:1px solid #ffeaa7;color:#856404;{/if}">
		<strong>{if $grommunio_version.installed_version_found}✅ {$grommunio_version.status_text}{else}⚠️ {$grommunio_version.status_text}{/if}</strong>
	</div>
	{/if}
	
	{if $syncResult}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $syncResult.success}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>{$syncResult.message}</strong>
		{if isset($syncResult.data_formatted) && $syncResult.data_formatted}
		<pre style="background:#fff;padding:10px;margin-top:10px;border:1px solid #ddd;overflow:auto;max-height:300px;">{$syncResult.data_formatted}</pre>
		{/if}
	</div>
	{/if}
	
	{if $testResult}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $testResult.success}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>{$testResult.message}</strong>
	</div>
	{/if}
	
	<fieldset>
		<legend>💼 Grommunio (MAPI/EWS/EAS Server)</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="4" valign="top" width="40">
					<img src="../plugins/templates/images/bms_logo.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Grommunio aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="grommunio_enabled" id="grommunio_enabled"{if $bms_prefs.grommunio_enabled} checked="checked"{/if} />
					<label for="grommunio_enabled">Server aktivieren</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Server (IP/Hostname):</td>
				<td class="td2">
					<input type="text" name="grommunio_server" value="{text value=$bms_prefs.grommunio_server}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="td1">Domain/Subdomain:</td>
				<td class="td2">
					<input type="text" name="grommunio_subdomain" value="{text value=$bms_prefs.grommunio_subdomain}" size="15" placeholder="mail" />.<input type="text" name="grommunio_domain" value="{text value=$bms_prefs.grommunio_domain}" size="30" placeholder="domain.tld" />
					<small>(z.B. mail.example.com)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Port:</td>
				<td class="td2">
					<input type="text" name="grommunio_port" value="{text value=$bms_prefs.grommunio_port}" size="10" />
					<input type="checkbox" name="grommunio_ssl" id="grommunio_ssl"{if $bms_prefs.grommunio_ssl} checked="checked"{/if} />
					<label for="grommunio_ssl">SSL/TLS</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Admin API URL:</td>
				<td class="td2">
					<input type="text" name="grommunio_api_url" value="{text value=$bms_prefs.grommunio_api_url}" size="60" />
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🔐 Admin-Zugangsdaten</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="{$tpldir}images/ico_admin.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Admin-Benutzer:</td>
				<td class="td2">
					<input type="text" name="grommunio_admin_user" value="{text value=$bms_prefs.grommunio_admin_user}" size="30" />
				</td>
			</tr>
			<tr>
				<td class="td1">Admin-Passwort:</td>
				<td class="td2">
					<input type="password" name="grommunio_admin_pass" value="{text value=$bms_prefs.grommunio_admin_pass}" size="30" />
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>📡 Protokoll-URLs</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="5" valign="top" width="40">
					<img src="{$tpldir}images/ico_mail.png" border="0" alt="" width="32" height="32" />
				</td>
				<td colspan="2" class="td1" style="background:#fffbcc;padding:10px;">
					<strong>ℹ️ Wichtig:</strong> Alle Protokolle verwenden <strong>EINE Domain</strong> mit unterschiedlichen Pfaden!<br>
					<small>Beispiel: Wenn Ihr Grommunio-Server unter <code>gtin.org</code> läuft (Port 8443), dann:</small>
				</td>
			</tr>
			<tr>
				<td class="td1" width="250">MAPI URL:</td>
				<td class="td2">
					<input type="text" name="grommunio_mapi_url" value="{text value=$bms_prefs.grommunio_mapi_url}" size="70" placeholder="https://gtin.org:8443/api/v1" /><br>
					<small style="color:#666;">📌 Standard: <code>https://IHR-SERVER:8443/api/v1</code> (Outlook Desktop)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">EWS URL:</td>
				<td class="td2">
					<input type="text" name="grommunio_ews_url" value="{text value=$bms_prefs.grommunio_ews_url}" size="70" placeholder="https://gtin.org/ews" /><br>
					<small style="color:#666;">📌 Standard: <code>https://IHR-SERVER/ews</code> (Exchange Web Services)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">EAS URL:</td>
				<td class="td2">
					<input type="text" name="grommunio_eas_url" value="{text value=$bms_prefs.grommunio_eas_url}" size="70" placeholder="https://gtin.org/mas" /><br>
					<small style="color:#666;">📌 Standard: <code>https://IHR-SERVER/mas</code> (ActiveSync für Mobile)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Autodiscover URL:</td>
				<td class="td2">
					<input type="text" name="grommunio_autodiscover_url" value="{text value=$bms_prefs.grommunio_autodiscover_url}" size="70" placeholder="https://gtin.org/autos" /><br>
					<small style="color:#666;">📌 Standard: <code>https://IHR-SERVER/autos</code> (Automatische Konfiguration)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>📅 CalDAV / CardDAV</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="{$tpldir}images/ico_calendar.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">CalDAV aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="grommunio_caldav_enabled" id="grommunio_caldav_enabled"{if $bms_prefs.grommunio_caldav_enabled} checked="checked"{/if} />
					<label for="grommunio_caldav_enabled">Kalender-Sync</label>
				</td>
			</tr>
			<tr>
				<td class="td1">CardDAV aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="grommunio_carddav_enabled" id="grommunio_carddav_enabled"{if $bms_prefs.grommunio_carddav_enabled} checked="checked"{/if} />
					<label for="grommunio_carddav_enabled">Kontakte-Sync</label>
				</td>
			</tr>
		</table>
	</fieldset>

	<div align="center">
		<input type="submit" class="button" value="{lng p="save"}" />
		<input type="button" class="button" value="🔄 Konfiguration übertragen" onclick="location.href='{$pageURL}&sid={$sid}&action=grommunio&sync=true';" />
		<input type="button" class="button" value="🧪 API-Verbindung testen" onclick="location.href='{$pageURL}&sid={$sid}&action=grommunio&test=true';" />
	</div>

</form>
