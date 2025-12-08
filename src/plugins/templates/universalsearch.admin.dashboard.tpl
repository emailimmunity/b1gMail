<h2>🔍 Universal Search - Dashboard</h2>

<fieldset>
	<legend>Elasticsearch Status</legend>
	
	<div class="row mb-3">
		<div class="col-md-12">
			<div class="alert alert-{if $es_status == 'connected'}success{else}danger{/if}">
				<h5>
					<i class="fa {if $es_status == 'connected'}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
					Status: {if $es_status == 'connected'}Verbunden{else}Nicht verbunden{/if}
				</h5>
				{if $es_status == 'connected'}
					<p><strong>Elasticsearch Version:</strong> {$es_version}</p>
					<p><strong>Kibana Dashboard:</strong> <a href="{$kibana_url}" target="_blank">{$kibana_url}</a></p>
				{else}
					<p class="text-danger"><strong>Fehler:</strong> {$es_status}</p>
				{/if}
			</div>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>Statistiken</legend>
	
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">🔍 Suchanfragen (TKÜV)</h5>
					<p class="card-text display-4">{$search_count|number_format:0:",":"."}</p>
					<small class="text-muted">Gesamt geloggte Suchen</small>
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">⏳ Warteschlange</h5>
					<p class="card-text display-4">{$queue_count|number_format:0:",":"."}</p>
					<small class="text-muted">Zu indexierende Elemente</small>
				</div>
			</div>
		</div>
		
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">✅ Status</h5>
					<p class="card-text">
						{if $es_status == 'connected'}
							<span class="badge bg-success">OPERATIV</span>
						{else}
							<span class="badge bg-danger">FEHLER</span>
						{/if}
					</p>
					<small class="text-muted">System-Status</small>
				</div>
			</div>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>Schnellzugriff</legend>
	
	<div class="row">
		<div class="col-md-12">
			<a href="{$pageURL}&action=reindex&" class="btn btn-primary">
				<i class="fa fa-refresh"></i> Benutzer neu indexieren
			</a>
			
			<a href="{$pageURL}&action=settings&" class="btn btn-secondary">
				<i class="fa fa-cog"></i> Einstellungen
			</a>
			
			<a href="{$pageURL}&action=stats&" class="btn btn-info">
				<i class="fa fa-bar-chart"></i> Such-Statistiken (TKÜV)
			</a>
			
			<a href="{$kibana_url}" target="_blank" class="btn btn-dark">
				<i class="fa fa-external-link"></i> Kibana Dashboard
			</a>
		</div>
	</div>
</fieldset>

<div class="alert alert-info mt-4">
	<h5><i class="fa fa-info-circle"></i> Hinweise</h5>
	<ul>
		<li><strong>Real-time Indexing:</strong> E-Mails, Dateien, Kalender und Kontakte werden automatisch indexiert.</li>
		<li><strong>User-Isolation:</strong> Jeder User hat eigene Indices (DSGVO-konform).</li>
		<li><strong>TKÜV-Compliance:</strong> Alle Suchanfragen werden geloggt.</li>
		<li><strong>Kibana:</strong> Verwenden Sie Kibana für erweiterte Analysen und Visualisierungen.</li>
	</ul>
</div>

<style>
.card {
	border: 1px solid #ddd;
	border-radius: 8px;
	margin-bottom: 20px;
}

.card-body {
	padding: 20px;
	text-align: center;
}

.display-4 {
	font-size: 2.5rem;
	font-weight: bold;
	color: #4CAF50;
}
</style>

