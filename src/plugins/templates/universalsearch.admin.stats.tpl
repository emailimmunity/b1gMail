<h2>📊 Universal Search - Statistiken (TKÜV Audit-Log)</h2>

<fieldset>
	<legend>🔍 Top-Suchanfragen</legend>
	
	<table class="list">
		<tr>
			<th width="50">#</th>
			<th>Suchanfrage</th>
			<th width="100">Anzahl</th>
		</tr>
		{foreach from=$top_searches item=search name=toploop}
		<tr class="{cycle values="td1,td2"}">
			<td align="center">{$smarty.foreach.toploop.iteration}</td>
			<td><strong>{$search.query|escape}</strong></td>
			<td align="center">{$search.count}</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="3" align="center" style="padding:20px;">
				<em>Noch keine Suchanfragen</em>
			</td>
		</tr>
		{/foreach}
	</table>
</fieldset>

<fieldset>
	<legend>📋 Letzte 100 Suchanfragen (TKÜV Audit-Log)</legend>
	
	<div style="background:#fffacd; border:1px solid #ffd700; padding:15px; margin-bottom:20px; border-radius:5px;">
		<strong>⚖️ TKÜV-Hinweis:</strong> Diese Logs werden für TKÜV-Compliance gespeichert.
		Bei überwachten Benutzern werden die Logs unendlich gespeichert.
	</div>
	
	<table class="list">
		<tr>
			<th width="50">ID</th>
			<th width="150">Benutzer</th>
			<th>Suchanfrage</th>
			<th width="80">Treffer</th>
			<th width="100">Typ</th>
			<th width="120">IP-Adresse</th>
			<th width="150">Zeitstempel</th>
		</tr>
		{foreach from=$searches item=item}
		<tr class="{cycle values="td1,td2"}">
			<td align="center">{$item.id}</td>
			<td align="center">User #{$item.userid}</td>
			<td><strong>{$item.query|escape}</strong></td>
			<td align="center">{$item.results_count}</td>
			<td align="center">
				{if $item.search_type == 'all'}
					<span class="badge bg-primary">Alles</span>
				{elseif $item.search_type == 'emails'}
					<span class="badge bg-info">E-Mails</span>
				{elseif $item.search_type == 'files'}
					<span class="badge bg-warning">Dateien</span>
				{elseif $item.search_type == 'calendar'}
					<span class="badge bg-success">Kalender</span>
				{elseif $item.search_type == 'contacts'}
					<span class="badge bg-secondary">Kontakte</span>
				{else}
					{$item.search_type}
				{/if}
			</td>
			<td><small>{$item.ip_address}</small></td>
			<td><small>{$item.timestamp|date_format:"%d.%m.%Y %H:%M:%S"}</small></td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="7" align="center" style="padding:30px;">
				<em>Noch keine Suchanfragen vorhanden</em>
			</td>
		</tr>
		{/foreach}
	</table>
</fieldset>

<div class="alert alert-info mt-4">
	<h5><i class="fa fa-info-circle"></i> TKÜV-Compliance</h5>
	<ul>
		<li><strong>Unendliche Speicherung:</strong> Logs von überwachten Benutzern werden nie gelöscht.</li>
		<li><strong>Vollständige Dokumentation:</strong> User-ID, Query, IP, Zeitstempel, User-Agent.</li>
		<li><strong>Behörden-Zugriff:</strong> Diese Logs sind bei Auskunftsersuchen verfügbar.</li>
		<li><strong>Datenschutz:</strong> Normale Benutzer-Logs werden nach 90 Tagen gelöscht (konfigurierbar).</li>
	</ul>
</div>

<style>
.list {
	width: 100%;
	border-collapse: collapse;
}

.list th {
	background: #f0f0f0;
	padding: 8px;
	font-weight: bold;
	border-bottom: 2px solid #ddd;
	text-align: left;
}

.list td {
	padding: 8px;
	border-bottom: 1px solid #eee;
}

.td1 {
	background: #f9f9f9;
}

.td2 {
	background: #fff;
}

.badge {
	display: inline-block;
	padding: 4px 8px;
	border-radius: 3px;
	color: white;
	font-size: 11px;
	font-weight: bold;
}

.bg-primary { background: #007bff; }
.bg-info { background: #17a2b8; }
.bg-warning { background: #ffc107; color: #000; }
.bg-success { background: #28a745; }
.bg-secondary { background: #6c757d; }
</style>

