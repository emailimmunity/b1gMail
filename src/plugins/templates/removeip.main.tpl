<form action="{$pageURL}&sid={$sid}" method="post" name="f1">
	<fieldset>
		<legend>🔍 Überwachungsmaßnahmen (TKÜV)</legend>
		
		<div style="background:#fffacd; border:1px solid #ffd700; padding:15px; margin-bottom:20px; border-radius:5px;">
			<strong>⚖️ Rechtsgrundlage:</strong><br>
			• TKÜV § 5 Abs. 2 (Telekommunikations-Überwachungsverordnung)<br>
			• BVerfG Az. 2 BvR 2377/16 (Urteil vom 20.12.2018)<br>
			• Bundesnetzagentur-Vorgaben für E-Mail-Überwachung<br><br>
			
			<strong>📋 Hinweis:</strong> IP-Adressen werden NUR bei aktivierten Überwachungsmaßnahmen oder Account-Spiegelungen gespeichert.
		</div>
		
		<table class="list">
			<tr>
				<th width="30">&nbsp;</th>
				<th>{lng p="removeip_email"}</th>
				<th>{lng p="removeip_authority"}</th>
				<th>{lng p="removeip_file_number"}</th>
				<th>{lng p="removeip_valid_from"}</th>
				<th>{lng p="removeip_valid_until"}</th>
				<th width="80">{lng p="removeip_logs"}</th>
				<th width="80">Status</th>
				<th width="70">&nbsp;</th>
			</tr>
			
			{foreach from=$surveillances item=item}
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
					<strong>{$item.email|escape}</strong>
					{if $item.userid}
						<br><small>User-ID: {$item.userid}</small>
					{/if}
				</td>
				<td>{$item.authority|escape}</td>
				<td><strong>{$item.file_number|escape}</strong></td>
				<td>{$item.valid_from|date_format:"%d.%m.%Y %H:%M"}</td>
				<td>
					{if $item.valid_until}
						{$item.valid_until|date_format:"%d.%m.%Y %H:%M"}
					{else}
						<em>Unbegrenzt</em>
					{/if}
				</td>
				<td align="center">
					<strong style="font-size:16px;">{$item.log_count}</strong>
				</td>
				<td align="center">
					{if $item.active == 1}
						<span style="color:green; font-weight:bold;">AKTIV</span>
					{else}
						<span style="color:red;">Inaktiv</span>
					{/if}
				</td>
				<td>
					{if $item.active == 1}
						<a href="{$pageURL}&deactivate={$item.id}&sid={$sid}" 
						   onclick="return confirm('Überwachung deaktivieren?');" 
						   title="Deaktivieren">
							<img src="{$tpldir}images/stop.png" border="0" alt="Deaktivieren" width="16" height="16" />
						</a>
					{/if}
					<a href="{$pageURL}&delete={$item.id}&sid={$sid}" 
					   onclick="return confirm('Überwachung und alle Logs löschen?');">
						<img src="{$tpldir}images/delete.png" border="0" alt="{lng p="delete"}" width="16" height="16" />
					</a>
				</td>
			</tr>
			<tr class="{$class}">
				<td></td>
				<td colspan="8" style="padding:5px 10px; background:#f9f9f9;">
					<small><strong>Grund:</strong> {$item.reason|escape}</small>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="9" align="center" style="padding:30px;">
					<em>Keine aktiven Überwachungsmaßnahmen</em>
				</td>
			</tr>
			{/foreach}
		</table>
	</fieldset>
</form>

<fieldset>
	<legend>➕ {lng p="removeip_add"}</legend>
	
	<form action="{$pageURL}&sid={$sid}&add=true" method="post" onsubmit="spin(this)">
		<table width="100%">
			<tr>
				<td width="40" valign="top" rowspan="7">
					<img src="{$tpldir}images/info.png" border="0" alt="" width="32" height="32" />
				</td>
				<td class="td1" width="200">{lng p="removeip_email"} *:</td>
				<td class="td2">
					<input type="text" size="40" name="email" value="" required 
					       placeholder="user@example.com" />
					<br><small>Account-ID wird automatisch ermittelt</small>
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="removeip_authority"} *:</td>
				<td class="td2">
					<input type="text" size="40" name="authority" value="" required 
					       placeholder="z.B. Staatsanwaltschaft München I" />
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="removeip_file_number"} *:</td>
				<td class="td2">
					<input type="text" size="40" name="file_number" value="" required 
					       placeholder="z.B. 123 Js 45678/24" />
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="removeip_reason"} *:</td>
				<td class="td2">
					<textarea name="reason" rows="3" cols="50" required 
					          placeholder="z.B. Gerichtsbeschluss vom 15.01.2025"></textarea>
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="removeip_valid_from"}:</td>
				<td class="td2">
					{html_select_date prefix="valid_from" start_year="-1" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="valid_from" display_seconds=false}
				</td>
			</tr>
			<tr>
				<td class="td1">{lng p="removeip_valid_until"}:</td>
				<td class="td2">
					<input type="checkbox" id="valid_until_unlim" name="valid_until_unlim" checked="checked" />
					<label for="valid_until_unlim"><b>{lng p="unlimited"}</b></label>
					{lng p="or"}
					{html_select_date prefix="valid_until" end_year="+2" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="valid_until" display_seconds=false}
				</td>
			</tr>
		</table>
		
		<p align="right">
			<input class="button" type="submit" value=" ✓ Überwachung aktivieren " />
		</p>
	</form>
</fieldset>

<fieldset>
	<legend>ℹ️ Funktionsweise</legend>
	
	<table width="100%">
		<tr>
			<td width="50%" valign="top" style="padding-right:20px;">
				<h4>✅ NORMAL (Privacy-Modus)</h4>
				<ul style="margin:0; padding-left:20px;">
					<li>IP-Adressen werden auf 0.0.0.0 gesetzt</li>
					<li>Keine Speicherung von IPs</li>
					<li>Voller Datenschutz für User</li>
					<li>IP-Locks deaktiviert</li>
				</ul>
			</td>
			<td width="50%" valign="top" style="padding-left:20px; border-left:1px solid #ddd;">
				<h4>🔍 ÜBERWACHUNG AKTIV</h4>
				<ul style="margin:0; padding-left:20px;">
					<li><strong>Echte IP wird gespeichert</strong></li>
					<li>Logging aller Zugriffe (Login, Webmail, Versand)</li>
					<li>User-Agent & Request-URI werden protokolliert</li>
					<li>Account Mirror: IP-Speicherung ebenfalls aktiv</li>
				</ul>
			</td>
		</tr>
	</table>
	
	<div style="background:#e7f3ff; border:1px solid #b3d9ff; padding:15px; margin-top:20px; border-radius:5px;">
		<strong>⚖️ Rechtliche Anforderungen (TKÜV):</strong><br>
		• Überwachungsmaßnahmen müssen dokumentiert werden<br>
		• IP-Adressen dürfen nur bei gerichtlicher Anordnung gespeichert werden<br>
		• Logs müssen nach Ablauf der Maßnahme gelöscht werden<br>
		• Bundesnetzagentur-Compliance erforderlich
	</div>
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
}

.td1 {
	background: #f9f9f9;
	font-weight: 600;
	padding: 10px;
}

.td2 {
	background: #fff;
	padding: 10px;
}

input[type="text"], textarea, select {
	border: 1px solid #ddd;
	padding: 6px;
	border-radius: 3px;
}

input[type="text"]:focus, textarea:focus {
	border-color: #4CAF50;
	outline: none;
}

.button {
	background: #4CAF50;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 4px;
	cursor: pointer;
	font-weight: bold;
}

.button:hover {
	background: #45a049;
}

h4 {
	margin-top: 0;
	color: #333;
}
</style>
