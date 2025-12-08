{*
 * AccountMirror v2.0 - Admin Template
 * Vollständige Account-Spiegelung (TKÜV/BNetzA-konform)
 *}

<form action="{$pageURL}&sid={$sid}" method="post" name="f1">
	<fieldset>
		<legend>🔄 Account Mirror v2.0 - Vollständige Spiegelung</legend>
		
		<div style="background:#fffacd; border:1px solid #ffd700; padding:15px; margin-bottom:20px; border-radius:5px;">
			<strong>⚖️ GESETZLICHE GRUNDLAGEN:</strong><br>
			• <strong>TKÜV § 5 Abs. 2:</strong> Telekommunikations-Überwachungsverordnung<br>
			• <strong>BVerfG Az. 2 BvR 2377/16:</strong> Urteil vom 20.12.2018<br>
			• <strong>BNetzA:</strong> Bundesnetzagentur-Vorgaben für E-Mail-Überwachung<br><br>
			
			<strong>⚠️ WICHTIGE HINWEISE:</strong><br>
			• Spiegelungen NUR bei behördlicher Anordnung oder Überwachungsmaßnahmen<br>
			• ALLE Daten werden gespiegelt: E-Mails, Webdisk, Kalender, Kontakte<br>
			• Ordnerstrukturen werden 1:1 übernommen<br>
			• Löschungen werden NICHT gespiegelt (Sicherheit)<br>
			• IP-Adressen werden bei Sync-Vorgängen geloggt (TKÜV-Pflicht)
		</div>
		
		<table class="list">
			<tr>
				<th width="30">&nbsp;</th>
				<th>{lng p="am2_source"}</th>
				<th>{lng p="am2_target"}</th>
				<th>{lng p="am2_reason"}</th>
				<th>{lng p="am2_authority"}</th>
				<th>{lng p="am2_file_number"}</th>
				<th width="80">Modus</th>
				<th width="150">Was?</th>
				<th width="80">{lng p="am2_stats"}</th>
				<th width="100">Status</th>
				<th width="70">&nbsp;</th>
			</tr>
			
			{foreach from=$mirrorings item=item}
			{cycle name=class values="td1,td2" assign=class}
			<tr class="{$class}">
				<td align="center">
					{if $item.active == 1}
						<span style="color:green; font-size:18px;">●</span>
					{else}
						<span style="color:red; font-size:18px;">●</span>
					{/if}
				</td>
				<td>
					<strong>{$item.source_email|escape}</strong>
					<br><small>ID: {$item.userid}</small>
				</td>
				<td>
					<strong>{$item.target_email|escape}</strong>
					<br><small>ID: {$item.mirror_to}</small>
					{if $item.bidirectional == 1}
						<br><span style="color:#667eea;">⇄ Bidirektional</span>
					{else}
						<br><span style="color:#999;">→ Unidirektional</span>
					{/if}
				</td>
				<td>{$item.reason|escape}</td>
				<td>{$item.authority|escape|default:'-'}</td>
				<td>{$item.file_number|escape|default:'-'}</td>
				<td>
					{if $item.mirror_mode == 'live'}
						<span style="color:#28a745;">📡 Live</span>
					{elseif $item.mirror_mode == 'snapshot'}
						<span style="color:#17a2b8;">📸 Snapshot</span>
					{else}
						<span style="color:#6c757d;">💾 Backup</span>
					{/if}
				</td>
				<td style="font-size:11px;">
					{if $item.mirror_emails}📧 {/if}
					{if $item.mirror_webdisk}💾 {/if}
					{if $item.mirror_calendar}📅 {/if}
					{if $item.mirror_contacts}👤 {/if}
					{if $item.mirror_folders}📁 {/if}
					{if $item.mirror_flags}🏴 {/if}
				</td>
				<td align="center" style="font-size:11px;">
					<strong>📧 {$item.mail_count|number_format:0:".":" "}</strong><br>
					{if $item.webdisk_file_count > 0}
						💾 {$item.webdisk_file_count|number_format:0:".":" "}<br>
					{/if}
					{if $item.calendar_event_count > 0}
						📅 {$item.calendar_event_count|number_format:0:".":" "}<br>
					{/if}
					{if $item.contact_count > 0}
						👤 {$item.contact_count|number_format:0:".":" "}<br>
					{/if}
					{if $item.error_count > 0}
						<span style="color:red;">⚠️ {$item.error_count}</span>
					{/if}
				</td>
				<td align="center">
					{if $item.active == 1}
						<strong style="color:green;">AKTIV</strong><br>
						{if $item.last_sync}
							<small>Sync: {$item.last_sync|date_format:"%d.%m %H:%M"}</small>
						{/if}
					{else}
						<strong style="color:red;">INAKTIV</strong>
					{/if}
				</td>
				<td>
					{if $item.active == 1}
						<a href="{$pageURL}&deactivate={$item.mirrorid}&sid={$sid}" 
						   onclick="return confirm('Spiegelung deaktivieren?');" 
						   title="Deaktivieren">
							<img src="{$tpldir}images/stop.png" border="0" alt="Stop" width="16" height="16" />
						</a>
					{/if}
					<a href="{$pageURL}&delete={$item.mirrorid}&sid={$sid}" 
					   onclick="return confirm('Spiegelung und alle Logs löschen?');">
						<img src="{$tpldir}images/delete.png" border="0" alt="{lng p="delete"}" width="16" height="16" />
					</a>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="11" align="center" style="padding:30px;">
					<em>Keine aktiven Spiegelungen</em>
				</td>
			</tr>
			{/foreach}
		</table>
	</fieldset>
</form>

<fieldset>
	<legend>➕ {lng p="add"} - Neue Spiegelung</legend>
	
	<form action="{$pageURL}&sid={$sid}&add=true" method="post" onsubmit="spin(this)">
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="15">
					<img src="{$tpldir}images/accountmirror_logo.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="200"><strong>{lng p="am2_source"} *:</strong></td>
				<td class="td2">
					<input type="text" size="40" name="email_source" value="" required 
					       placeholder="quelle@example.com" />
					<br><small>E-Mail-Adresse des Quell-Accounts</small>
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>{lng p="am2_target"} *:</strong></td>
				<td class="td2">
					<input type="text" size="40" name="email_target" value="" required 
					       placeholder="ziel@example.com" />
					<br><small>E-Mail-Adresse des Ziel-Accounts (darf nicht Quelle einer anderen Spiegelung sein)</small>
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>{lng p="am2_reason"} *:</strong></td>
				<td class="td2">
					<textarea name="reason" rows="3" cols="50" required 
					          placeholder="z.B. Gerichtsbeschluss vom 15.01.2025, Az. 123 Js 456/24"></textarea>
					<br><small>Rechtsgrundlage / Grund für Spiegelung (PFLICHT für Dokumentation)</small>
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="am2_authority"}:</td>
				<td class="td2">
					<input type="text" size="40" name="authority" value="" 
					       placeholder="z.B. Staatsanwaltschaft München I" />
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="am2_file_number"}:</td>
				<td class="td2">
					<input type="text" size="40" name="file_number" value="" 
					       placeholder="z.B. 123 Js 456/24" />
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>{lng p="am2_mode"} *:</strong></td>
				<td class="td2">
					<select name="mirror_mode" required onchange="toggleIncludeExisting(this.value)">
						<option value="live">📡 Live (nur neue Daten ab jetzt)</option>
						<option value="snapshot">📸 Snapshot (inkl. ALLE bestehenden Daten)</option>
					</select>
					<br><small id="mode_hint" style="color:#667eea; display:none;">
						⚠️ <strong>Snapshot-Modus:</strong> ALLE bestehenden E-Mails, Webdisk-Dateien, Kalender und Kontakte werden initial gespiegelt!
					</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Zeitraum:</td>
				<td class="td2">
					<input type="checkbox" id="begin_now" name="begin_now" checked="checked" />
					<label for="begin_now"><b>Sofort beginnen</b></label>
					{lng p="or"}
					{html_select_date prefix="begin" end_year="+1" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="begin" display_seconds=false}
					<br><br>
					<input type="checkbox" id="end_unlimited" name="end_unlimited" checked="checked" />
					<label for="end_unlimited"><b>{lng p="unlimited"}</b></label>
					{lng p="or"}
					{html_select_date prefix="end" end_year="+2" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="end" display_seconds=false}
				</td>
			</tr>
			<tr>
				<td class="td1" colspan="2"><hr></td>
			</tr>
			<tr>
				<td class="td1" valign="top"><strong>{lng p="am2_what_to_mirror"}:</strong></td>
				<td class="td2">
					<input type="checkbox" id="mirror_emails" name="mirror_emails" checked="checked" />
					<label for="mirror_emails"><b>📧 {lng p="am2_emails"}</b></label><br>
					
					<input type="checkbox" id="mirror_folders" name="mirror_folders" checked="checked" />
					<label for="mirror_folders"><b>📁 Ordnerstruktur 1:1 übernehmen</b></label><br>
					
					<input type="checkbox" id="mirror_flags" name="mirror_flags" checked="checked" />
					<label for="mirror_flags"><b>🏴 Flags & Markierungen (gelesen, beantwortet, etc.)</b></label><br>
					
					<input type="checkbox" id="mirror_webdisk" name="mirror_webdisk" />
					<label for="mirror_webdisk"><b>💾 {lng p="am2_webdisk"}</b></label>
					<span style="color:#ff6b6b;">(Performance-intensiv!)</span><br>
					
					<input type="checkbox" id="mirror_calendar" name="mirror_calendar" />
					<label for="mirror_calendar"><b>📅 {lng p="am2_calendar"}</b></label><br>
					
					<input type="checkbox" id="mirror_contacts" name="mirror_contacts" />
					<label for="mirror_contacts"><b>👤 {lng p="am2_contacts"}</b></label><br>
				</td>
			</tr>
			<tr>
				<td class="td1" colspan="2"><hr></td>
			</tr>
			<tr>
				<td class="td1" valign="top"><strong>Erweiterte Optionen:</strong></td>
				<td class="td2">
					<input type="checkbox" id="bidirectional" name="bidirectional" />
					<label for="bidirectional"><b>⇄ {lng p="am2_bidirectional"}</b></label>
					<span style="color:#ff6b6b;">(ACHTUNG: Änderungen werden in BEIDE Richtungen synchronisiert!)</span><br>
					
					<input type="checkbox" id="mirror_deletions" name="mirror_deletions" />
					<label for="mirror_deletions"><b>🗑️ Löschungen auch spiegeln</b></label>
					<span style="color:#ff0000;">(GEFÄHRLICH: Gelöschte Daten werden auch im Ziel gelöscht!)</span><br>
				</td>
			</tr>
		</table>
		
		<p align="right">
			<input class="button" type="submit" value=" ✓ Spiegelung aktivieren " />
		</p>
	</form>
</fieldset>

<fieldset>
	<legend>ℹ️ Funktionsweise & Features</legend>
	
	<table width="100%">
		<tr>
			<td width="50%" valign="top" style="padding-right:20px;">
				<h4>✅ WAS WIRD GESPIEGELT?</h4>
				<ul style="margin:0; padding-left:20px;">
					<li><strong>E-Mails:</strong> ALLE eingehenden & ausgehenden</li>
					<li><strong>Ordner:</strong> 1:1 Struktur übernommen</li>
					<li><strong>Flags:</strong> Gelesen, beantwortet, markiert, etc.</li>
					<li><strong>Verschiebungen:</strong> Wenn Mail verschoben wird</li>
					<li><strong>Webdisk:</strong> Dateien & Ordnerstruktur (optional)</li>
					<li><strong>Kalender:</strong> Termine & Events (optional)</li>
					<li><strong>Kontakte:</strong> Adressbuch (optional)</li>
				</ul>
				
				<h4 style="margin-top:20px;">🔴 WAS WIRD NICHT GESPIEGELT?</h4>
				<ul style="margin:0; padding-left:20px;">
					<li><strong>Löschungen:</strong> Standardmäßig NEIN (Sicherheit)</li>
					<li><strong>Spam/Trash:</strong> Nur wenn explizit aktiviert</li>
				</ul>
			</td>
			<td width="50%" valign="top" style="padding-left:20px; border-left:1px solid #ddd;">
				<h4>⚙️ MODI:</h4>
				<ul style="margin:0; padding-left:20px;">
					<li><strong>📡 Live:</strong> Nur neue Daten ab Aktivierung</li>
					<li><strong>📸 Snapshot:</strong> Inkl. ALLE bestehenden Daten (einmalig beim Start)</li>
				</ul>
				
				<h4 style="margin-top:20px;">🔄 BIDIREKTIONAL:</h4>
				<ul style="margin:0; padding-left:20px;">
					<li>Änderungen werden in BEIDE Richtungen synchronisiert</li>
					<li>Nützlich für Team-Postfächer</li>
					<li>ACHTUNG: Kann zu Konflikten führen!</li>
				</ul>
				
				<h4 style="margin-top:20px;">⚖️ TKÜV-KONFORMITÄT:</h4>
				<ul style="margin:0; padding-left:20px;">
					<li>IP-Adressen werden bei Sync-Vorgängen geloggt</li>
					<li>Grund & Behörde werden dokumentiert</li>
					<li>Aktenzeichen wird gespeichert</li>
					<li>Vollständige Audit-Trail</li>
				</ul>
			</td>
		</tr>
	</table>
</fieldset>

<style>
.list th {
	background: #f0f0f0;
	padding: 8px;
	font-weight: bold;
	border-bottom: 2px solid #ddd;
}

.list td {
	padding: 8px;
	border-bottom: 1px solid #eee;
	vertical-align: top;
}

.td1 {
	background: #f9f9f9;
	font-weight: 600;
	padding: 10px;
	vertical-align: top;
}

.td2 {
	background: #fff;
	padding: 10px;
}

input[type="text"], textarea, select {
	border: 1px solid #ddd;
	padding: 6px;
	border-radius: 3px;
	font-family: inherit;
}

input[type="text"]:focus, textarea:focus, select:focus {
	border-color: #667eea;
	outline: none;
}

.button {
	background: #667eea;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 4px;
	cursor: pointer;
	font-weight: bold;
}

.button:hover {
	background: #5568d3;
}

h4 {
	margin-top: 0;
	color: #333;
	border-bottom: 2px solid #667eea;
	padding-bottom: 5px;
}

fieldset {
	margin-bottom: 20px;
	border: 1px solid #ddd;
	border-radius: 6px;
}

legend {
	font-weight: bold;
	color: #667eea;
	padding: 0 10px;
}
</style>

<script>
function toggleIncludeExisting(mode) {
	var hint = document.getElementById('mode_hint');
	if(mode == 'snapshot') {
		hint.style.display = 'block';
	} else {
		hint.style.display = 'none';
	}
}
</script>
