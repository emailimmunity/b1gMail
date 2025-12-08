<div class="sdm-user-subdomains">
	<h2 class="mb-4">🌐 Meine Subdomains</h2>
	
	<!-- Verfügbare Domains zum Erstellen -->
	{if $availableGrants}
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">Neue Subdomain erstellen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="?action=prefs&do=subdomains&create=1">
				<div class="row">
					{foreach $availableGrants as $grant}
					<div class="col-md-6 mb-3">
						<div class="card">
							<div class="card-body">
								<h4>{$grant.domain}</h4>
								<p class="text-muted">
									{if $grant.can_create}
										{$grant.current_subdomains} von {$grant.max_subdomains} Subdomains verwendet
									{else}
										<span class="text-danger">Limit erreicht ({$grant.max_subdomains} Subdomains)</span>
									{/if}
								</p>
								
								<div class="mb-3">
									<label class="form-label">Subdomain-Name</label>
									<div class="input-group">
										<input type="text" name="subdomain" class="form-control" 
											   placeholder="meinname" pattern="[a-z0-9-]+" 
											   {if !$grant.can_create}disabled{/if}>
										<span class="input-group-text">.{$grant.domain}</span>
									</div>
									<small class="form-hint">Nur Kleinbuchstaben, Zahlen und Bindestriche</small>
								</div>
								
								<div class="mb-3">
									{if $grant.dyndns_enabled}
									<label class="form-check">
										<input type="checkbox" name="enable_dyndns" class="form-check-input" 
											   {if !$grant.can_create}disabled{/if}>
										<span class="form-check-label">DynDNS aktivieren</span>
									</label>
									{/if}
									
									{if $grant.keyhelp_enabled}
									<label class="form-check">
										<input type="checkbox" name="enable_keyhelp" class="form-check-input" 
											   {if !$grant.can_create}disabled{/if}>
										<span class="form-check-label">Webserver (KeyHelp) aktivieren</span>
									</label>
									{/if}
								</div>
								
								<input type="hidden" name="parent_domain" value="{$grant.domain}">
								
								<button type="submit" class="btn btn-primary w-100" {if !$grant.can_create}disabled{/if}>
									<i class="ti ti-plus"></i> Subdomain erstellen
								</button>
							</div>
						</div>
					</div>
					{/foreach}
				</div>
			</form>
		</div>
	</div>
	{else}
	<div class="alert alert-info">
		<h4 class="alert-title">Keine Domains verfügbar</h4>
		<div class="text-muted">
			Ihr Administrator hat noch keine Domains für Ihre Gruppe freigegeben.
			Kontaktieren Sie Ihren Administrator für weitere Informationen.
		</div>
	</div>
	{/if}
	
	<!-- Meine Subdomains -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Meine Subdomains ({$mySubdomains|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $mySubdomains}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Domain</th>
							<th>Status</th>
							<th>Features</th>
							<th>Erstellt</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $mySubdomains as $subdomain}
						<tr>
							<td><strong>{$subdomain.full_domain}</strong></td>
							<td>
								{if $subdomain.status == 'active'}
								<span class="badge bg-success">Aktiv</span>
								{else}
								<span class="badge bg-warning">{$subdomain.status}</span>
								{/if}
							</td>
							<td>
								{if $subdomain.dyndns_enabled}
								<span class="badge bg-info">DynDNS</span>
								{/if}
								{if $subdomain.keyhelp_enabled}
								<span class="badge bg-warning">KeyHelp</span>
								{/if}
							</td>
							<td>{$subdomain.created_at}</td>
							<td>
								{if $subdomain.dyndns_enabled}
								<a href="?action=prefs&do=subdomains&dyndns={$subdomain.id}" 
								   class="btn btn-sm btn-info" 
								   title="DynDNS Info">
									<i class="ti ti-refresh"></i>
								</a>
								{/if}
								<a href="?action=prefs&do=subdomains&emails={$subdomain.id}" 
								   class="btn btn-sm btn-primary" 
								   title="Email-Adressen">
									<i class="ti ti-mail"></i>
								</a>
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
				<p class="empty-title">Keine Subdomains</p>
				<p class="empty-subtitle text-muted">
					Sie haben noch keine Subdomains erstellt. 
					Erstellen Sie oben Ihre erste Subdomain!
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>
