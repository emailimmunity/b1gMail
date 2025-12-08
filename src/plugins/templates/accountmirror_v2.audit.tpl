{*
 * AccountMirror v2.0 - Audit-Logs (TKÜV-konform)
 * UNENDLICHE SPEICHERUNG (gesetzliche Pflicht)
 *}

<div class="section-header">
	<h2>📜 Audit-Logs (TKÜV § 5 - Unendliche Speicherung)</h2>
	<p class="notice">
		<strong>⚖️ Hinweis:</strong> Diese Logs werden UNENDLICH gespeichert (TKÜV-Pflicht). 
		Keine automatische Löschung!
	</p>
</div>

{* Compliance-Check *}
<fieldset>
	<legend>✅ TKÜV/BNetzA Compliance-Status</legend>
	
	{if $compliance.compliant}
		<div class="alert alert-success">
			<strong>✅ KONFORM:</strong> Alle TKÜV/BNetzA-Anforderungen sind erfüllt.
		</div>
	{else}
		<div class="alert alert-error">
			<strong>⚠️ PROBLEME GEFUNDEN:</strong>
			<ul>
				{foreach from=$compliance.issues item=issue}
					<li>
						{if $issue.severity == 'critical'}
							<span style="color:red; font-weight:bold;">🔴 KRITISCH:</span>
						{else}
							<span style="color:orange; font-weight:bold;">🟠 WARNUNG:</span>
						{/if}
						{$issue.message}
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}
</fieldset>

{* Filter *}
<fieldset>
	<legend>🔍 Filter</legend>
	
	<form method="get" action="{$pageURL}">
		<input type="hidden" name="page" value="accountmirror_v2">
		<input type="hidden" name="tab" value="audit">
		
		<table>
			<tr>
				<td><strong>Spiegelung-ID:</strong></td>
				<td><input type="number" name="mirrorid" value="{$mirrorid|default:''}" /></td>
				
				<td><strong>Jahr:</strong></td>
				<td>
					<select name="year">
						<option value="">Alle</option>
						{section name=y start=2024 loop=2030}
							<option value="{$smarty.section.y.index}" 
							        {if $year|default:'' == $smarty.section.y.index}selected{/if}>
								{$smarty.section.y.index}
							</option>
						{/section}
					</select>
				</td>
				
				<td>
					<button type="submit" class="btn btn-primary">🔍 Filtern</button>
					<a href="{$pageURL}&tab=audit&sid={$sid}" class="btn btn-secondary">↻ Zurücksetzen</a>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

{* Audit-Logs Tabelle *}
<fieldset>
	<legend>📋 Audit-Log Einträge ({$audit_logs|@count})</legend>
	
	{if $audit_logs|@count > 0}
		<table class="list">
			<tr>
				<th width="150">Timestamp</th>
				<th width="80">Spiegelung</th>
				<th width="120">Event</th>
				<th width="200">Admin</th>
				<th width="120">IP-Adresse</th>
				<th>Details</th>
				<th width="200">Rechtsgrundlage</th>
			</tr>
			
			{foreach from=$audit_logs item=log}
			{cycle name=class values="td1,td2" assign=class}
			<tr class="{$class}">
				<td>
					<strong>{$log.timestamp|date_format:"%d.%m.%Y"}</strong><br>
					<small>{$log.timestamp|date_format:"%H:%M:%S"}</small>
				</td>
				<td align="center">
					<strong>#{$log.mirrorid}</strong>
				</td>
				<td>
					{if $log.event_type == 'created'}
						<span style="color:green; font-weight:bold;">✅ Erstellt</span>
					{elseif $log.event_type == 'activated'}
						<span style="color:blue; font-weight:bold;">▶️ Aktiviert</span>
					{elseif $log.event_type == 'deactivated'}
						<span style="color:orange; font-weight:bold;">⏸️ Deaktiviert</span>
					{elseif $log.event_type == 'deleted'}
						<span style="color:red; font-weight:bold;">🗑️ Gelöscht</span>
					{elseif $log.event_type == 'modified'}
						<span style="color:purple; font-weight:bold;">✏️ Geändert</span>
					{else}
						<span style="color:gray;">👁️ Zugegriffen</span>
					{/if}
				</td>
				<td>
					{if $log.performed_by_email}
						<strong>{$log.performed_by_email|escape}</strong><br>
						<small>ID: {$log.performed_by}</small>
					{else}
						<em>System (ID: {$log.performed_by})</em>
					{/if}
				</td>
				<td>
					<code class="ip">{$log.ip_address}</code>
					{if $log.user_agent}
						<br><small title="{$log.user_agent|escape}">
							{$log.user_agent|truncate:30|escape}
						</small>
					{/if}
				</td>
				<td>
					{if $log.event_details_decoded}
						<details>
							<summary style="cursor:pointer; color:#667eea;">Details anzeigen</summary>
							<pre style="margin-top:10px; background:#f0f0f0; padding:10px; border-radius:4px; font-size:11px;">{$log.event_details|escape}</pre>
						</details>
					{else}
						<em>—</em>
					{/if}
				</td>
				<td>
					{if $log.legal_reference}
						{$log.legal_reference|escape|nl2br}
					{else}
						<em>—</em>
					{/if}
				</td>
			</tr>
			{/foreach}
		</table>
		
		<div style="margin-top:20px; text-align:center; color:#666;">
			<strong>Gesamt:</strong> {$audit_logs|@count} Audit-Einträge
		</div>
	{else}
		<div style="text-align:center; padding:40px; color:#666;">
			<p>📋 Keine Audit-Logs gefunden</p>
			{if $mirrorid|default:0 > 0 || ($year|default:0 > 0 && $year|default:0 != $smarty.now|date_format:"%Y")}
				<p><a href="{$pageURL}&tab=audit&sid={$sid}">Filter zurücksetzen</a></p>
			{/if}
		</div>
	{/if}
</fieldset>

{* Info-Box *}
<fieldset>
	<legend>ℹ️ Wichtige Informationen zu Audit-Logs</legend>
	
	<div class="info-grid">
		<div class="info-item">
			<h4>📜 Unendliche Speicherung</h4>
			<p>
				Diese Logs werden <strong>NIEMALS automatisch gelöscht</strong>.<br>
				Grund: TKÜV § 5 Abs. 2 - Dokumentationspflicht für Überwachungsmaßnahmen.
			</p>
		</div>
		
		<div class="info-item">
			<h4>🔒 Was wird geloggt?</h4>
			<ul>
				<li>Erstellung von Spiegelungen</li>
				<li>Aktivierung/Deaktivierung</li>
				<li>Änderungen an Spiegelungen</li>
				<li>Löschung von Spiegelungen</li>
				<li>Zugriffe auf Spiegelungen</li>
			</ul>
		</div>
		
		<div class="info-item">
			<h4>📊 Gespeicherte Daten</h4>
			<ul>
				<li><strong>IP-Adresse</strong> des Admins (TKÜV-Pflicht!)</li>
				<li><strong>Timestamp</strong> (exakte Uhrzeit)</li>
				<li><strong>Admin-ID & E-Mail</strong></li>
				<li><strong>Event-Details</strong> (JSON)</li>
				<li><strong>Rechtsgrundlage</strong> (falls vorhanden)</li>
			</ul>
		</div>
		
		<div class="info-item">
			<h4>⚖️ Rechtsgrundlagen</h4>
			<p>
				<strong>TKÜV § 5 Abs. 2:</strong> Dokumentationspflicht<br>
				<strong>BVerfG Az. 2 BvR 2377/16:</strong> IP-Logging-Pflicht<br>
				<strong>BNetzA:</strong> Audit-Trail für Behörden
			</p>
		</div>
	</div>
</fieldset>

<style>
.section-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 20px;
	border-radius: 8px;
	margin-bottom: 20px;
}

.section-header h2 {
	margin: 0 0 10px 0;
	color: white;
}

.notice {
	background: rgba(255,255,255,0.2);
	padding: 10px;
	border-radius: 4px;
	margin: 10px 0 0 0;
}

.alert {
	padding: 15px;
	border-radius: 6px;
	margin-bottom: 20px;
}

.alert-success {
	background: #d4edda;
	border: 1px solid #c3e6cb;
	color: #155724;
}

.alert-error {
	background: #f8d7da;
	border: 1px solid #f5c6cb;
	color: #721c24;
}

.alert ul {
	margin: 10px 0 0 20px;
	padding: 0;
}

.list {
	width: 100%;
	border-collapse: collapse;
}

.list th {
	background: #f0f0f0;
	padding: 10px;
	font-weight: bold;
	border-bottom: 2px solid #ddd;
}

.list td {
	padding: 10px;
	border-bottom: 1px solid #eee;
	vertical-align: top;
}

.td1 { background: #f9f9f9; }
.td2 { background: #fff; }

.ip {
	background: #f0f0f0;
	padding: 4px 8px;
	border-radius: 4px;
	font-family: monospace;
	font-size: 13px;
}

.btn {
	padding: 6px 12px;
	border-radius: 4px;
	text-decoration: none;
	display: inline-block;
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
	border: none;
	margin: 0 5px;
}

.btn-primary { background: #667eea; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn:hover { opacity: 0.9; }

.info-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.info-item {
	background: #f8f9fa;
	padding: 15px;
	border-radius: 6px;
	border: 1px solid #dee2e6;
}

.info-item h4 {
	margin-top: 0;
	color: #667eea;
	border-bottom: 2px solid #667eea;
	padding-bottom: 5px;
}

.info-item ul {
	margin: 10px 0 0 0;
	padding-left: 20px;
}

.info-item li {
	margin-bottom: 5px;
	font-size: 13px;
}

fieldset {
	margin-bottom: 20px;
	border: 1px solid #ddd;
	border-radius: 6px;
	padding: 15px;
}

legend {
	font-weight: bold;
	color: #667eea;
	padding: 0 10px;
}

details summary {
	cursor: pointer;
	color: #667eea;
	font-weight: 600;
}

details summary:hover {
	text-decoration: underline;
}
</style>
