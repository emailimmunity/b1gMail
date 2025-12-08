<form action="{$pageURL}" method="post" onsubmit="spin(this)">

<fieldset>
	<legend>🔍 Auto-Discovery: Erkannte Domains</legend>
	
	<div class="alert alert-info">
		<h5><i class="fa fa-info-circle"></i> Was ist Auto-Discovery?</h5>
		<p>Diese Funktion analysiert automatisch deine Protokoll-Konfigurationen (Dovecot, Postfix, Grommunio, SFTPGo) 
		   und zeigt alle konfigurierten Subdomains an. Du kannst dann entscheiden, ob du einzelne Zertifikate 
		   oder ein Wildcard-Zertifikat erstellen möchtest.</p>
	</div>
	
	{if count($discovered_domains) > 0}
		{foreach from=$discovered_domains key=domain item=data}
		<div class="card mb-3">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0">
					<i class="fa fa-globe"></i> <strong>{$domain}</strong>
					<span class="badge bg-light text-dark float-end">
						{count($data.subdomains)} Subdomains erkannt
					</span>
				</h5>
			</div>
			<div class="card-body">
				{* Wildcard Suggestion *}
				{if $data.wildcard_suggestion.suggest}
				<div class="alert alert-warning">
					<h6><i class="fa fa-lightbulb-o"></i> 💡 Wildcard-Empfehlung</h6>
					<p>{$data.wildcard_suggestion.reason}</p>
					<a href="{$pageURL|replace:'discovery':'create'}&type=wildcard&domain={$domain}" 
					   class="btn btn-warning">
						<i class="fa fa-certificate"></i> Wildcard-Zertifikat erstellen (*.{$domain})
					</a>
				</div>
				{/if}
				
				{* Subdomain List *}
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Subdomain</th>
							<th>Protokoll</th>
							<th>Service</th>
							<th>Status</th>
							<th>Aktion</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$data.subdomains item=sub}
						<tr>
							<td><code>{$sub.fqdn}</code></td>
							<td>{$sub.protocol}</td>
							<td>
								<span class="badge bg-secondary">{$sub.service}</span>
							</td>
							<td>
								{* TODO: Check if certificate exists *}
								<span class="badge bg-danger">❌ Kein Zertifikat</span>
							</td>
							<td>
								<a href="{$pageURL|replace:'discovery':'create'}&type=single&domain={$sub.fqdn}" 
								   class="btn btn-sm btn-success">
									<i class="fa fa-plus"></i> Erstellen
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
				
				{* Bulk Actions *}
				<div class="mt-3">
					<strong>Bulk-Aktionen:</strong>
					<div class="btn-group" role="group">
						<a href="{$pageURL|replace:'discovery':'create'}&type=wildcard&domain={$domain}" 
						   class="btn btn-warning">
							<i class="fa fa-certificate"></i> Wildcard (*.{$domain})
						</a>
						<a href="{$pageURL|replace:'discovery':'create'}&type=san&domain={$domain}" 
						   class="btn btn-info">
							<i class="fa fa-certificate"></i> SAN (alle {count($data.subdomains)} Domains)
						</a>
					</div>
				</div>
			</div>
		</div>
		{/foreach}
		
		{* Summary *}
		<div class="alert alert-success">
			<h5><i class="fa fa-check-circle"></i> Zusammenfassung</h5>
			<ul>
				<li><strong>{count($discovered_domains)}</strong> Basis-Domains erkannt</li>
				<li><strong>{assign var="total_subs" value=0}{foreach from=$discovered_domains item=data}{assign var="total_subs" value=$total_subs+count($data.subdomains)}{/foreach}{$total_subs}</strong> Subdomains insgesamt</li>
			</ul>
			<p class="mb-0">
				<strong>💡 Empfehlung:</strong> Für jede Basis-Domain ein Wildcard-Zertifikat erstellen, 
				um alle Subdomains mit einem Zertifikat abzudecken.
			</p>
		</div>
		
	{else}
		<div class="alert alert-warning">
			<h5><i class="fa fa-exclamation-triangle"></i> Keine Domains erkannt</h5>
			<p>Es wurden keine konfigurierten Domains in deinen Protokoll-Einstellungen gefunden.</p>
			<p><strong>Mögliche Gründe:</strong></p>
			<ul>
				<li>Keine Protokolle (Dovecot, Postfix, Grommunio, SFTPGo) aktiviert</li>
				<li>Keine Domains in b1gMail oder Email-Admin Plugin konfiguriert</li>
				<li>Keine Benutzer mit Email-Adressen vorhanden</li>
			</ul>
			<a href="index.php?site=bms&action=dovecot" class="btn btn-primary">
				<i class="fa fa-cog"></i> Protokolle konfigurieren
			</a>
		</div>
	{/if}
	
	<div class="mt-3">
		<a href="{$pageURL|replace:'&action=discovery':''}" class="btn btn-secondary">
			<i class="fa fa-arrow-left"></i> Zurück zur Übersicht
		</a>
	</div>
</fieldset>

{* Info Box *}
<div class="alert alert-info mt-4">
	<h5><i class="fa fa-info-circle"></i> Zertifikat-Typen</h5>
	<table class="table table-sm">
		<tr>
			<td width="150"><strong>Einzelzertifikat</strong></td>
			<td>Ein Zertifikat für eine spezifische Domain (z.B. imap.domain.tld)</td>
			<td><span class="badge bg-secondary">HTTP-01</span></td>
		</tr>
		<tr>
			<td><strong>Wildcard</strong></td>
			<td>Ein Zertifikat für ALLE Subdomains (z.B. *.domain.tld)</td>
			<td><span class="badge bg-warning">DNS-01 erforderlich!</span></td>
		</tr>
		<tr>
			<td><strong>SAN</strong></td>
			<td>Ein Zertifikat für mehrere spezifische Domains (max. 100)</td>
			<td><span class="badge bg-secondary">HTTP-01</span></td>
		</tr>
	</table>
</div>

</form>
