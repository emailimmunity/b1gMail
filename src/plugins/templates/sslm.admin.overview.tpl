<form action="{$pageURL}" method="get">

{* Statistics Dashboard *}
<div class="row mb-4">
	<div class="col-md-3">
		<div class="card text-white bg-success">
			<div class="card-body">
				<h5 class="card-title">✅ Aktive Zertifikate</h5>
				<h2>{$stats.active}</h2>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card text-white bg-warning">
			<div class="card-body">
				<h5 class="card-title">⚠️ Läuft bald ab</h5>
				<h2>{$stats.expiring_soon}</h2>
				<small>&lt; 30 Tage</small>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card text-white bg-danger">
			<div class="card-body">
				<h5 class="card-title">❌ Abgelaufen</h5>
				<h2>{$stats.expired}</h2>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card text-white bg-info">
			<div class="card-body">
				<h5 class="card-title">📊 Gesamt</h5>
				<h2>{$stats.total}</h2>
			</div>
		</div>
	</div>
</div>

{* Action Buttons *}
<div class="btn-group mb-3" role="group">
	<a href="{$pageURL}&action=create" class="btn btn-success">
		<i class="fa fa-plus"></i> Neues Zertifikat
	</a>
	<a href="{$pageURL}&action=discovery" class="btn btn-primary">
		<i class="fa fa-search"></i> Auto-Discovery
	</a>
	<a href="{$pageURL}&action=settings" class="btn btn-secondary">
		<i class="fa fa-cog"></i> Einstellungen
	</a>
</div>

{* Certificates Table *}
<fieldset>
	<legend>🔐 SSL-Zertifikate</legend>
	
	{if count($certificates) > 0}
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>Domain</th>
				<th>Typ</th>
				<th>Status</th>
				<th>Aussteller</th>
				<th>Gültig bis</th>
				<th>Deployed zu</th>
				<th>Aktionen</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$certificates item=cert}
			<tr class="{if $cert.days_until_expiry < 15}table-danger{elseif $cert.days_until_expiry < 30}table-warning{/if}">
				<td>
					<strong>{$cert.primary_domain}</strong>
					{if count($cert.domains) > 1}
					<br><small class="text-muted">+{count($cert.domains)-1} weitere</small>
					{/if}
				</td>
				<td>
					{if $cert.type == 'wildcard'}
						<span class="badge bg-primary">Wildcard</span>
					{elseif $cert.type == 'san'}
						<span class="badge bg-info">SAN</span>
					{else}
						<span class="badge bg-secondary">Einzel</span>
					{/if}
				</td>
				<td>
					{if $cert.status == 'active'}
						<span class="badge bg-success">✅ Aktiv</span>
					{elseif $cert.status == 'expired'}
						<span class="badge bg-danger">❌ Abgelaufen</span>
					{elseif $cert.status == 'pending'}
						<span class="badge bg-warning">⏳ Ausstehend</span>
					{else}
						<span class="badge bg-danger">❌ Fehler</span>
					{/if}
				</td>
				<td>
					{if $cert.issuer == 'letsencrypt'}
						<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Ctext y='12' font-size='12'%3E🔒%3C/text%3E%3C/svg%3E" alt="LE"> Let's Encrypt
					{else}
						{$cert.issuer}
					{/if}
				</td>
				<td>
					{if $cert.valid_until}
						{$cert.valid_until|date_format:"%d.%m.%Y"}
						<br>
						<small class="
							{if $cert.days_until_expiry < 0}text-danger
							{elseif $cert.days_until_expiry < 15}text-danger
							{elseif $cert.days_until_expiry < 30}text-warning
							{else}text-success
							{/if}
						">
							{if $cert.days_until_expiry < 0}
								{assign var="days_positive" value=$cert.days_until_expiry*-1}
								Abgelaufen vor {$days_positive} Tagen
							{else}
								Noch {$cert.days_until_expiry} Tage
							{/if}
						</small>
					{else}
						<small class="text-muted">Nicht ausgegeben</small>
					{/if}
				</td>
				<td>
					{if $cert.deployed_to && count($cert.deployed_to) > 0}
						{foreach from=$cert.deployed_to item=service}
							<span class="badge bg-success">{$service}</span>
						{/foreach}
					{else}
						<small class="text-muted">Nicht deployed</small>
					{/if}
				</td>
				<td>
					<div class="btn-group btn-group-sm" role="group">
						<a href="{$pageURL}&action=renew&id={$cert.id}" class="btn btn-primary" title="Erneuern">
							<i class="fa fa-refresh"></i>
						</a>
						<a href="{$pageURL}&action=edit&id={$cert.id}" class="btn btn-secondary" title="Bearbeiten">
							<i class="fa fa-edit"></i>
						</a>
						<a href="{$pageURL}&action=delete&id={$cert.id}" class="btn btn-danger" 
						   onclick="return confirm('Zertifikat wirklich löschen?')" title="Löschen">
							<i class="fa fa-trash"></i>
						</a>
					</div>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{else}
	<div class="alert alert-info">
		<h5><i class="fa fa-info-circle"></i> Noch keine Zertifikate vorhanden</h5>
		<p>Erstelle dein erstes SSL-Zertifikat oder nutze die <strong>Auto-Discovery</strong> Funktion, 
		   um automatisch Domains aus deinen Protokoll-Konfigurationen zu erkennen.</p>
		<a href="{$pageURL}&action=create" class="btn btn-success">
			<i class="fa fa-plus"></i> Erstes Zertifikat erstellen
		</a>
		<a href="{$pageURL}&action=discovery" class="btn btn-primary">
			<i class="fa fa-search"></i> Auto-Discovery starten
		</a>
	</div>
	{/if}
</fieldset>

{* Help Box *}
<div class="alert alert-success mt-4">
	<h5><i class="fa fa-lightbulb-o"></i> 💡 Tipps</h5>
	<ul>
		<li><strong>Auto-Discovery:</strong> Erkennt automatisch alle konfigurierten Subdomains aus Dovecot, Postfix, Grommunio und SFTPGo</li>
		<li><strong>Wildcard-Zertifikate:</strong> Ein Zertifikat für alle Subdomains (*.domain.tld) - erfordert DNS-01 Validierung</li>
		<li><strong>Auto-Renewal:</strong> Zertifikate werden automatisch 30 Tage vor Ablauf erneuert</li>
		<li><strong>Let's Encrypt:</strong> Kostenlose SSL-Zertifikate mit 90 Tagen Gültigkeit</li>
		<li><strong>SAN-Zertifikate:</strong> Ein Zertifikat für mehrere spezifische Domains (bis zu 100)</li>
	</ul>
</div>

</form>
