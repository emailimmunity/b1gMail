<form action="{$pageURL}&sid={$sid}&action=postfix&save=true" method="post" onsubmit="spin(this)">
	
	{* Container & Version Status *}
	{if $postfix_container_status}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $postfix_container_status.running}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>
			{if $postfix_container_status.running}
				🟢 Container <code>{$postfix_container_status.name}</code> läuft | Uptime: {$postfix_container_status.uptime}
			{else}
				🔴 Container <code>{$postfix_container_status.name}</code> NICHT AKTIV
			{/if}
		</strong>
		{if $postfix_version && $postfix_version.installed_version_found}
			<br><small>Version: {$postfix_version.installed_version}</small>
		{/if}
	</div>
	{/if}
	
	{if $syncResult}
	<div style="padding:15px;margin:10px 0;border-radius:5px;{if $syncResult.success}background:#d4edda;border:1px solid #c3e6cb;color:#155724;{else}background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;{/if}">
		<strong>{$syncResult.message}</strong>
	</div>
	{/if}
	
	<fieldset>
		<legend>📮 Postfix (SMTP Gateway)</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="../plugins/templates/images/bms_logo.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Postfix aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="postfix_enabled" id="postfix_enabled"{if $bms_prefs.postfix_enabled} checked="checked"{/if} />
					<label for="postfix_enabled">Gateway aktivieren</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Server-Hostname:</td>
				<td class="td2">
					<input type="text" name="postfix_server" value="{text value=$bms_prefs.postfix_server}" size="40" />
					<small>(Container-Name oder IP)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Multi-Domain Config:</td>
				<td class="td2">
					<strong>🌐 {$available_domains_count} Domain(s) erkannt</strong><br>
					<small>Automatische Erkennung aus b1gMail</small><br><br>
					
					<label><input type="radio" name="postfix_domain_mode" value="auto" {if $bms_prefs.postfix_domain_mode=='auto' || !$bms_prefs.postfix_domain_mode}checked{/if}> Automatisch (alle Domains)</label><br>
					<label><input type="radio" name="postfix_domain_mode" value="manual" {if $bms_prefs.postfix_domain_mode=='manual'}checked{/if}> Manuell</label><br><br>
					
					<div id="manual_postfix_domain_config" style="display:{if $bms_prefs.postfix_domain_mode=='manual'}block{else}none{/if};">
						<strong>Subdomain-Präfix:</strong> <input type="text" name="postfix_subdomain" value="{text value=$bms_prefs.postfix_subdomain}" size="15" placeholder="smtp" /><br>
						<small>Wird angewendet auf alle Domains</small><br><br>
						
						<strong>Erlaubte Domains (virtual_mailbox_domains):</strong><br>
						<textarea name="postfix_allowed_domains" rows="5" cols="50">{text value=$bms_prefs.postfix_allowed_domains}</textarea><br>
						<small>(Eine Domain pro Zeile, leer = alle)</small>
					</div>
					
					<script>
					document.querySelectorAll('input[name="postfix_domain_mode"]').forEach(function(radio) {
						radio.addEventListener('change', function() {
							document.getElementById('manual_postfix_domain_config').style.display = 
								this.value === 'manual' ? 'block' : 'none';
						});
					});
					</script>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🔌 SMTP Ports</legend>
		
		<table width="100%">
			<tr>
				<td align="left" valign="top" width="40"></td>
				<td class="td1" width="250">SMTP Port:</td>
				<td class="td2">
					<input type="text" name="postfix_smtp_port" value="{text value=$bms_prefs.postfix_smtp_port}" size="10" />
					<small>(Standard: 2025)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">Submission Port:</td>
				<td class="td2">
					<input type="text" name="postfix_submission_port" value="{text value=$bms_prefs.postfix_submission_port}" size="10" />
					<small>(Standard: 2587)</small>
				</td>
			</tr>
			<tr>
				<td></td>
				<td class="td1">SMTPS Port (SSL/TLS):</td>
				<td class="td2">
					<input type="text" name="postfix_smtps_port" value="{text value=$bms_prefs.postfix_smtps_port}" size="10" />
					<small>(Standard: 465)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>📡 Relay & Domains</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="2" valign="top" width="40">
					<img src="{$tpldir}images/ico_mail.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Relayhost:</td>
				<td class="td2">
					<input type="text" name="postfix_relayhost" value="{text value=$bms_prefs.postfix_relayhost allowEmpty=true}" size="50" />
					<small>(Optional: [mail.example.com]:587)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Erlaubte Domains:</td>
				<td class="td2">
					<textarea name="postfix_allowed_domains" rows="5" cols="50">{text value=$bms_prefs.postfix_allowed_domains allowEmpty=true}</textarea>
					<small>(Eine Domain pro Zeile)</small>
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend>🛡️ Sicherheit & Filter</legend>
		
		<table width="100%">
			<tr>
				<td align="left" rowspan="3" valign="top" width="40">
					<img src="{$tpldir}images/ico_secure.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="250">Spam-Check aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="postfix_spam_check" id="postfix_spam_check"{if $bms_prefs.postfix_spam_check} checked="checked"{/if} />
					<label for="postfix_spam_check">SpamAssassin Integration</label>
				</td>
			</tr>
			<tr>
				<td class="td1">Virus-Check aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="postfix_virus_check" id="postfix_virus_check"{if $bms_prefs.postfix_virus_check} checked="checked"{/if} />
					<label for="postfix_virus_check">ClamAV Integration</label>
				</td>
			</tr>
			<tr>
				<td class="td1">DKIM Signing aktiviert?</td>
				<td class="td2">
					<input type="checkbox" name="postfix_dkim_enabled" id="postfix_dkim_enabled"{if $bms_prefs.postfix_dkim_enabled} checked="checked"{/if} />
					<label for="postfix_dkim_enabled">DKIM-Signatur</label>
					<input type="text" name="postfix_dkim_selector" value="{text value=$bms_prefs.postfix_dkim_selector}" size="20" placeholder="Selector" />
				</td>
			</tr>
		</table>
	</fieldset>

	<div align="center">
		<input type="submit" class="button" value="{lng p="save"}" />
		<input type="button" class="button" value="🔄 Konfiguration zu Postfix übertragen" onclick="location.href='{$pageURL}&sid={$sid}&action=postfix&sync=true';" />
		<input type="button" class="button" value="🧪 Verbindung testen" onclick="location.href='{$pageURL}&sid={$sid}&action=postfix&test=true';" />
	</div>

</form>
