{*
 * AccountMirror v2.0 - Jahres-Auswertungen
 * Automatisch generiert am 31.12. jeden Jahres
 *}

<div class="section-header">
	<h2>📊 Jahres-Auswertungen (TKÜV/BNetzA)</h2>
	<p class="notice">
		<strong>ℹ️ Automatisch:</strong> Wird jeden 31.12. um 23:00 Uhr automatisch generiert. 
		Manuelle Generierung jederzeit möglich.
	</p>
</div>

{* Neue Auswertung generieren *}
<fieldset>
	<legend>➕ Neue Jahres-Auswertung generieren</legend>
	
	<form method="post" action="{$pageURL}&tab=reports&generate=1&sid={$sid}">
		<table>
			<tr>
				<td><strong>Jahr:</strong></td>
				<td>
					<select name="year" required>
						{section name=y start=2020 loop=2030}
							<option value="{$smarty.section.y.index}" 
							        {if $smarty.section.y.index == $smarty.now|date_format:"%Y"}selected{/if}>
								{$smarty.section.y.index}
							</option>
						{/section}
					</select>
				</td>
				<td>
					<button type="submit" class="btn btn-primary">📊 Auswertung generieren</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>

{* Liste der Auswertungen *}
<fieldset>
	<legend>📋 Vorhandene Jahres-Auswertungen</legend>
	
	{if $reports|@count > 0}
		<div class="reports-grid">
			{foreach from=$reports item=report}
			<div class="report-card">
				<div class="report-header">
					<h3>📊 Jahres-Auswertung {$report.year}</h3>
					<div class="report-date">
						Generiert: {$report.generated_at|date_format:"%d.%m.%Y %H:%M"}
					</div>
				</div>
				
				<div class="report-stats">
					{assign var="data" value=$report.report_data_decoded}
					
					<div class="stat-row">
						<div class="stat-item">
							<div class="stat-label">Gesamt Spiegelungen:</div>
							<div class="stat-value">{$data.total_mirrorings|default:0}</div>
						</div>
						<div class="stat-item">
							<div class="stat-label">Davon aktiv:</div>
							<div class="stat-value">{$data.active_mirrorings|default:0}</div>
						</div>
					</div>
					
					<div class="stat-row">
						<div class="stat-item">
							<div class="stat-label">Sync-Vorgänge:</div>
							<div class="stat-value">{$data.total_syncs|default:0|number_format:0:".":" "}</div>
						</div>
						<div class="stat-item">
							<div class="stat-label">Gespiegelte E-Mails:</div>
							<div class="stat-value">{$data.total_mails|default:0|number_format:0:".":" "}</div>
						</div>
					</div>
					
					<div class="stat-row">
						<div class="stat-item">
							<div class="stat-label">Audit-Events:</div>
							<div class="stat-value">{$data.total_audit_events|default:0|number_format:0:".":" "}</div>
						</div>
						<div class="stat-item">
							<div class="stat-label">Auskunftsersuchen:</div>
							<div class="stat-value">{$data.total_information_requests|default:0}</div>
						</div>
					</div>
					
					{* Monatsstatistik *}
					{if $data.monthly_mirrorings}
					<div class="monthly-chart">
						<h4>Monatliche Verteilung</h4>
						<div class="chart-bars">
							{section name=m start=1 loop=13}
								{assign var="month" value=$smarty.section.m.index}
								{assign var="count" value=$data.monthly_mirrorings.$month|default:0}
								{if $count > 0}
									{assign var="max" value=10}
									{foreach from=$data.monthly_mirrorings item=c}
										{if $c > $max}{assign var="max" value=$c}{/if}
									{/foreach}
									{assign var="percentage" value=($count / $max * 100)|string_format:"%.0f"}
								{else}
									{assign var="percentage" value=0}
								{/if}
								
								<div class="chart-bar">
									<div class="bar-label">{$month|string_format:"%02d"}</div>
									<div class="bar-container">
										<div class="bar-fill" style="width:{$percentage}%; background:#667eea;"></div>
										<div class="bar-count">{$count}</div>
									</div>
								</div>
							{/section}
						</div>
					</div>
					{/if}
					
					{* Top Behörden *}
					{if $data.top_authorities|@count > 0}
					<div class="top-authorities">
						<h4>Top Behörden ({$report.year})</h4>
						<table class="mini-table">
							<tr>
								<th>Behörde</th>
								<th width="80">Anzahl</th>
							</tr>
							{foreach from=$data.top_authorities item=auth}
							<tr>
								<td>{$auth.authority|escape}</td>
								<td align="center"><strong>{$auth.cnt}</strong></td>
							</tr>
							{/foreach}
						</table>
					</div>
					{/if}
				</div>
				
				<div class="report-actions">
					<a href="{$pageURL}&tab=reports&download={$report.id}&sid={$sid}" 
					   class="btn btn-primary">
						📥 PDF Download
					</a>
					<a href="{$pageURL}&tab=reports&export_json={$report.id}&sid={$sid}" 
					   class="btn btn-secondary">
						📋 JSON Export
					</a>
				</div>
			</div>
			{/foreach}
		</div>
	{else}
		<div style="text-align:center; padding:40px; color:#666;">
			<p>📊 Noch keine Jahres-Auswertungen vorhanden</p>
			<p>Generieren Sie die erste Auswertung mit dem Formular oben.</p>
		</div>
	{/if}
</fieldset>

{* Info-Box *}
<fieldset>
	<legend>ℹ️ Informationen zu Jahres-Auswertungen</legend>
	
	<div class="info-grid">
		<div class="info-item">
			<h4>🤖 Automatische Generierung</h4>
			<p>
				Jeden <strong>31. Dezember um 23:00 Uhr</strong> wird automatisch eine Jahres-Auswertung erstellt.<br>
				MySQL Event: <code>generate_accountmirror_yearly_report</code>
			</p>
		</div>
		
		<div class="info-item">
			<h4>📊 Enthaltene Daten</h4>
			<ul>
				<li>Anzahl Spiegelungen (gesamt & aktiv)</li>
				<li>Sync-Vorgänge</li>
				<li>Gespiegelte E-Mails</li>
				<li>Audit-Events</li>
				<li>Auskunftsersuchen</li>
				<li>Top Behörden</li>
				<li>Monatliche Verteilung</li>
			</ul>
		</div>
		
		<div class="info-item">
			<h4>⚖️ Rechtliche Anforderungen</h4>
			<p>
				<strong>TKÜV:</strong> Jährliche Dokumentation erforderlich<br>
				<strong>BNetzA:</strong> Statistische Auswertung für Compliance<br>
				<strong>DSGVO:</strong> Transparenz & Rechenschaftspflicht
			</p>
		</div>
		
		<div class="info-item">
			<h4>💾 Export-Formate</h4>
			<ul>
				<li><strong>PDF:</strong> Für Behörden-Berichte</li>
				<li><strong>JSON:</strong> Für Datenanalyse</li>
				<li><strong>Datenbank:</strong> Unendlich gespeichert</li>
			</ul>
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

.reports-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.report-card {
	background: white;
	border: 2px solid #667eea;
	border-radius: 8px;
	overflow: hidden;
}

.report-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 15px;
}

.report-header h3 {
	margin: 0 0 5px 0;
	color: white;
}

.report-date {
	font-size: 12px;
	opacity: 0.9;
}

.report-stats {
	padding: 20px;
}

.stat-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 15px;
	margin-bottom: 15px;
}

.stat-item {
	background: #f8f9fa;
	padding: 15px;
	border-radius: 6px;
	border: 1px solid #dee2e6;
	text-align: center;
}

.stat-label {
	font-size: 12px;
	color: #666;
	margin-bottom: 8px;
}

.stat-value {
	font-size: 28px;
	font-weight: bold;
	color: #667eea;
}

.monthly-chart,
.top-authorities {
	margin-top: 20px;
	padding: 15px;
	background: #f8f9fa;
	border-radius: 6px;
}

.monthly-chart h4,
.top-authorities h4 {
	margin-top: 0;
	color: #667eea;
	border-bottom: 2px solid #667eea;
	padding-bottom: 5px;
}

.chart-bars {
	margin-top: 15px;
}

.chart-bar {
	display: flex;
	align-items: center;
	margin-bottom: 8px;
}

.bar-label {
	width: 30px;
	font-size: 11px;
	font-weight: bold;
	color: #666;
}

.bar-container {
	flex: 1;
	height: 20px;
	background: #e0e0e0;
	border-radius: 10px;
	position: relative;
	overflow: hidden;
}

.bar-fill {
	height: 100%;
	border-radius: 10px;
	transition: width 0.3s;
}

.bar-count {
	position: absolute;
	right: 5px;
	top: 50%;
	transform: translateY(-50%);
	font-size: 11px;
	font-weight: bold;
	color: white;
	text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.mini-table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 10px;
}

.mini-table th {
	background: #667eea;
	color: white;
	padding: 8px;
	text-align: left;
}

.mini-table td {
	padding: 6px 8px;
	border-bottom: 1px solid #ddd;
}

.report-actions {
	padding: 15px;
	background: #f8f9fa;
	border-top: 1px solid #dee2e6;
	display: flex;
	gap: 10px;
	justify-content: center;
}

.btn {
	padding: 8px 16px;
	border-radius: 4px;
	text-decoration: none;
	display: inline-block;
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
	border: none;
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
</style>
