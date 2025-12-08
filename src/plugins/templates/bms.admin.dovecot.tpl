<form action="{$pageURL}&sid={$sid}&action=dovecot&save=true" method="post" onsubmit="spin(this)">
	
	{* Container & Version Status *}
	{if $dovecot_container_status}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $dovecot_container_status.running}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>
			{if $dovecot_container_status.running}
				🟢 Container <code>{$dovecot_container_status.name}</code> läuft | Uptime: {$dovecot_container_status.uptime}
			{else}
				🔴 Container <code>{$dovecot_container_status.name}</code> NICHT AKTIV
			{/if}
		</strong>
		{if $dovecot_version && $dovecot_version.installed_version_found}
			<br><small>Version: {$dovecot_version.installed_version}</small>
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
		<legend>📧 Dovecot (IMAP/POP3/Sieve Gateway)</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="../plugins/templates/images/bms_logo.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Dovecot aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="dovecot_enabled" id="dovecot_enabled"{if $bms_prefs.dovecot_enabled} checked="checked"{/if} />
					<label for="dovecot_enabled">Gateway aktivieren</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Server-Hostname:</td>
				<td class="td2">
					<input type="text" name="dovecot_server" value="{text value=$bms_prefs.dovecot_server}" size="40" />
					<small>(Container-Name oder IP)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Multi-Domain Config:</td>
				<td class="td2">
					<strong>🌐 {$available_domains_count} Domain(s) im System erkannt</strong><br>
					<small>Automatische Erkennung aus b1gMail-Domains + User-Emails</small><br><br>
					
					<label><input type="radio" name="dovecot_domain_mode" value="auto" {if $bms_prefs.dovecot_domain_mode=='auto' || !$bms_prefs.dovecot_domain_mode}checked{/if}> Automatisch (alle Domains)</label><br>
					<label><input type="radio" name="dovecot_domain_mode" value="manual" {if $bms_prefs.dovecot_domain_mode=='manual'}checked{/if}> Manuell</label><br><br>
					
					<div id="manual_domain_config" style="display:{if $bms_prefs.dovecot_domain_mode=='manual'}block{else}none{/if};">
						<table width="100%" style="margin-bottom:15px;">
							<tr>
								<td width="20%"><strong>📬 IMAP:</strong></td>
								<td><input type="text" name="dovecot_imap_subdomain" value="{text value=$bms_prefs.dovecot_imap_subdomain|default:'imap'}" size="15" placeholder="imap" /></td>
								<td><small>→ imap.domain1.tld, imap.domain2.tld, ...</small></td>
							</tr>
							<tr>
								<td><strong>📭 POP3:</strong></td>
								<td><input type="text" name="dovecot_pop3_subdomain" value="{text value=$bms_prefs.dovecot_pop3_subdomain|default:'pop3'}" size="15" placeholder="pop3" /></td>
								<td><small>→ pop3.domain1.tld, pop3.domain2.tld, ...</small></td>
							</tr>
							<tr>
								<td><strong>🔧 Sieve:</strong></td>
								<td><input type="text" name="dovecot_sieve_subdomain" value="{text value=$bms_prefs.dovecot_sieve_subdomain|default:'sieve'}" size="15" placeholder="sieve" /></td>
								<td><small>→ sieve.domain1.tld, sieve.domain2.tld, ...</small></td>
							</tr>
						</table>
						
						<strong>Erlaubte Domains:</strong><br>
						<textarea name="dovecot_allowed_domains" rows="5" cols="50">{text value=$bms_prefs.dovecot_allowed_domains}</textarea><br>
						<small>(Eine Domain pro Zeile, leer = alle)</small>
					</div>
					
					<script>
					document.querySelectorAll('input[name="dovecot_domain_mode"]').forEach(function(radio) {
						radio.addEventListener('change', function() {
							document.getElementById('manual_domain_config').style.display = 
								this.value === 'manual' ? 'block' : 'none';
						});
					});
					</script>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>📨 Protokoll-Übersicht</legend>
		<table width="100%" style="margin-bottom:20px;">
			<tr>
				<td style="padding:10px;background:#f8f9fa;border:1px solid #dee2e6;">
					<strong>Dovecot unterstützt 3 Protokolle:</strong><br>
					✅ <strong>IMAP</strong> - Email abrufen (Standard: Port 143/993)<br>
					✅ <strong>POP3</strong> - Email abrufen (Standard: Port 110/995)<br>
					✅ <strong>ManageSieve</strong> - Mail-Filter verwalten (Standard: Port 4190)
				</td>
			</tr>
		</table>
	</fieldset>
	
	<fieldset>
		<legend>📬 IMAP Ports</legend>
		
		<table width="100%">
			<tr>
				<td align="left" valign="top" width="40"></td>
				<td class="td1" width="250">IMAP Port (Plain):</td>
				<td class="td2">
					<input type="text" name="dovecot_imap_port" value="{text value=$bms_prefs.dovecot_imap_port}" size="10" />
					<small>(Standard: 2143)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">IMAPS Port (SSL/TLS):</td>
				<td class="td2">
					<input type="text" name="dovecot_imaps_port" value="{text value=$bms_prefs.dovecot_imaps_port}" size="10" />
					<small>(Standard: 2993)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>📬 POP3 Ports</legend>
		
		<table width="100%">
			<tr>
				<td align="left" valign="top" width="40"></td>
				<td class="td1" width="250">POP3 Port (Plain):</td>
				<td class="td2">
					<input type="text" name="dovecot_pop3_port" value="{text value=$bms_prefs.dovecot_pop3_port}" size="10" />
					<small>(Standard: 2110)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">POP3S Port (SSL/TLS):</td>
				<td class="td2">
					<input type="text" name="dovecot_pop3s_port" value="{text value=$bms_prefs.dovecot_pop3s_port}" size="10" />
					<small>(Standard: 2995)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend> ManageSieve (Mail-Filter Protokoll)</legend>
		
		<table width="100%">
			<tr>
				<td colspan="3" style="padding:10px;background:#fff3cd;border:1px solid #ffeaa7;margin-bottom:10px;">
					<strong> ManageSieve</strong> - Ermöglicht Clients (Thunderbird, etc.) Serverfilter (Sieve) zu verwalten.<br>
					<small>Beispiel: mail.example.com:4190</small>
				</td>
			</tr>
			<tr>
				<td align="left" valign="top" width="40"></td>
				<td class="td1" width="250">ManageSieve Port:</td>
				<td class="td2">
					<input type="text" name="dovecot_sieve_port" value="{text value=$bms_prefs.dovecot_sieve_port}" size="10" />
					<small>(Standard: 4190, Empfohlen für Sieve-Filter)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">Sieve aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="dovecot_sieve_enabled" id="dovecot_sieve_enabled"{if $bms_prefs.dovecot_sieve_enabled} checked="checked"{/if} />
					<label for="dovecot_sieve_enabled">ManageSieve-Protokoll aktivieren</label>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend> Admin-Zugang</legend>
		<legend>🔐 Admin-Zugang</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="{$tpldir}images/ico_admin.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Admin-Benutzer:</td>
				<td class="td2">
					<input type="text" name="dovecot_admin_user" value="{text value=$bms_prefs.dovecot_admin_user}" size="30" />
				</td>
			</tr>
			<tr>
				<td class="td1">Admin-Passwort:</td>
				<td class="td2">
					<input type="password" name="dovecot_admin_pass" value="{text value=$bms_prefs.dovecot_admin_pass}" size="30" />
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🗄️ Datenbank-Verbindung</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="4" valign="top" width="40">
					<img src="{$tpldir}images/ico_database.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">MySQL Host:</td>
				<td class="td2">
					<input type="text" name="dovecot_sql_host" value="{text value=$bms_prefs.dovecot_sql_host}" size="40" />
				</td>
			</tr>
			<tr>
				<td class="td1">Datenbank:</td>
				<td class="td2">
					<input type="text" name="dovecot_sql_database" value="{text value=$bms_prefs.dovecot_sql_database}" size="30" />
				</td>
			</tr>
			<tr>
				<td class="td1">Benutzer:</td>
				<td class="td2">
					<input type="text" name="dovecot_sql_user" value="{text value=$bms_prefs.dovecot_sql_user}" size="30" />
				</td>
			</tr>
			<tr>
				<td class="td1">Passwort:</td>
				<td class="td2">
					<input type="password" name="dovecot_sql_password" value="{text value=$bms_prefs.dovecot_sql_password}" size="30" />
				</td>
			</tr>
		</table>
	</fieldset>

	<div align="center">
		<input type="submit" class="button" value="{lng p="save"}" />
		<input type="button" class="button" value="🔄 Konfiguration zu Dovecot übertragen" onclick="location.href='{$pageURL}&sid={$sid}&action=dovecot&sync=true';" />
		<input type="button" class="button" value="🧪 Verbindung testen" onclick="location.href='{$pageURL}&sid={$sid}&action=dovecot&test=true';" />
	</div>

</form>
