<div class="emailadmin-domains">
	<h2 class="mb-4">🌐 Domain Management</h2>
	
	<!-- SYSTEM-DOMAINS WARNUNG -->
	{if $systemDomains && count($systemDomains) > 0}
	<div class="alert alert-info alert-important">
		<div class="d-flex">
			<div class="flex-fill">
				<h4 class="alert-title">🔒 System-Domains geschützt</h4>
				<div class="text-muted">
					<strong>{count($systemDomains)}</strong> Domains sind in den <strong>"Allgemeinen Einstellungen"</strong> definiert und können NICHT übernommen oder gelöscht werden.
					<br>
					<a href="#system-domains-list" data-bs-toggle="collapse" class="btn btn-sm btn-info mt-2">
						<i class="ti ti-eye"></i> Details anzeigen / ausblenden
					</a>
				</div>
				
				<!-- Collapsible Domain-Liste -->
				<div class="collapse mt-3" id="system-domains-list">
					<div class="card card-sm">
						<div class="card-body">
							<h5 class="card-title">Geschützte System-Domains:</h5>
							<div class="row">
								{foreach $systemDomains as $sysDomain}
								<div class="col-md-4 col-sm-6">
									<span class="badge bg-warning mb-2">🔒 {$sysDomain}</span>
								</div>
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{/if}
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Domain hinzufügen -->
	{if $canCreateDomain|default:true}
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">Neue Domain hinzufügen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=domains">
				<div class="row">
					<div class="col-md-8">
						<div class="mb-3">
							<label class="form-label">Domain</label>
							<input type="text" name="domain" class="form-control" placeholder="example.com" required>
							<small class="form-hint">Geben Sie eine Domain ohne http:// oder www ein</small>
						</div>
					</div>
					<div class="col-md-4">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="add_domain" class="btn btn-primary w-100">
							<i class="ti ti-plus"></i> Domain hinzufügen
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	{/if}
	
	<!-- Domains-Liste -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Vorhandene Domains ({$domains|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $domains}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Domain</th>
							<th>Status</th>
							<th>MX Validiert</th>
							<th>Letzter Check</th>
							<th>Erstellt</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $domains as $domain}
						<tr {if $domain.is_system_domain}style="background-color: #fff3cd;"{/if}>
							<td><span class="text-muted">{$domain.id}</span></td>
							<td>
								<strong>{$domain.domain}</strong>
								{if $domain.is_system_domain}
								<br><small class="text-warning"><i class="ti ti-lock"></i> System-Domain</small>
								{/if}
							</td>
							<td>
								{if $domain.is_system_domain}
								<span class="badge bg-warning">🔒 Geschützt</span>
								{else}
								<span class="badge bg-success">✓ Verwaltet</span>
								{/if}
							</td>
							<td>
								{if $domain.mx_validated}
								<span class="badge bg-success">✅ Validiert</span>
								{else}
								<span class="badge bg-secondary">⏳ Ausstehend</span>
								{/if}
							</td>
							<td>
								{if $domain.last_mx_check}
								{$domain.last_mx_check|date_format:"%d.%m.%Y %H:%M"}
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td>{$domain.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							<td>
								{if $domain.is_system_domain}
								<button class="btn btn-sm btn-secondary" disabled title="System-Domains können nicht gelöscht werden">
									<i class="ti ti-lock"></i> Geschützt
								</button>
								{else}
								<a href="{$pageURL}&action=domains&delete={$domain.id}" 
								   onclick="return confirm('Domain {$domain.domain} wirklich löschen?')" 
								   class="btn btn-sm btn-danger">
									<i class="ti ti-trash"></i>
								</a>
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<div class="empty">
				<div class="empty-icon">
					<i class="ti ti-world" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine Domains vorhanden</p>
				<p class="empty-subtitle text-muted">
					{if $canCreateDomain|default:true}
					Fügen Sie oben eine Domain hinzu
					{else}
					Sie haben keine Domains zugeordnet. Kontaktieren Sie Ihren Administrator.
					{/if}
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>

