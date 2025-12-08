<div class="emailadmin-dashboard">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h2 class="mb-0">📊 EmailAdmin Dashboard</h2>
		{if $currentRole}
		<div>
			<span class="badge bg-blue">
				{if $currentRole == 'superadmin'}👑 Superadmin
				{elseif $currentRole == 'reseller'}🏢 Reseller
				{elseif $currentRole == 'multidomain_admin'}🌐 Multidomain Admin
				{elseif $currentRole == 'domain_admin'}📧 Domain Admin
				{elseif $currentRole == 'subdomain_admin'}📁 Subdomain Admin
				{else}👤 User
				{/if}
			</span>
		</div>
		{/if}
	</div>
	
	<!-- Statistiken -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="subheader">Total Users</div>
					</div>
					<div class="h1 mb-0">{$stats.users|number_format:0:",":"."}</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="subheader">Total Domains</div>
					</div>
					<div class="h1 mb-0">{$stats.domains|number_format:0:",":"."}</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="subheader">Total Gruppen</div>
					</div>
					<div class="h1 mb-0">{$stats.groups|number_format:0:",":"."}</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="subheader">MX Records</div>
					</div>
					<div class="h1 mb-0">{$stats.mx_records|number_format:0:",":"."}</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Schnellzugriff -->
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">🌐 Neueste Domains</h3>
				</div>
				<div class="card-body">
					{if $recent_domains}
					<div class="list-group list-group-flush">
						{foreach $recent_domains as $domain}
						<div class="list-group-item">
							<div class="row">
								<div class="col">
									<strong>{$domain.domain}</strong>
								</div>
								<div class="col-auto">
									{if $domain.mx_validated}
									<span class="badge bg-success">MX OK</span>
									{else}
									<span class="badge bg-warning">MX Pending</span>
									{/if}
								</div>
							</div>
						</div>
						{/foreach}
					</div>
					<div class="card-footer">
						<a href="{$pageURL}&action=domains" class="btn btn-primary btn-sm">Alle Domains anzeigen →</a>
					</div>
					{else}
					<p class="text-muted">Keine Domains vorhanden</p>
					<a href="{$pageURL}&action=domains" class="btn btn-primary btn-sm">Erste Domain hinzufügen →</a>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">👥 Neueste Gruppen</h3>
				</div>
				<div class="card-body">
					{if $recent_groups}
					<div class="list-group list-group-flush">
						{foreach $recent_groups as $group}
						<div class="list-group-item">
							<div class="row">
								<div class="col">
									<strong>{$group.name}</strong>
									{if $group.description}
									<br><small class="text-muted">{$group.description}</small>
									{/if}
								</div>
							</div>
						</div>
						{/foreach}
					</div>
					<div class="card-footer">
						<a href="{$pageURL}&action=groups" class="btn btn-primary btn-sm">Alle Gruppen anzeigen →</a>
					</div>
					{else}
					<p class="text-muted">Keine Gruppen vorhanden</p>
					<a href="{$pageURL}&action=groups" class="btn btn-primary btn-sm">Erste Gruppe erstellen →</a>
					{/if}
				</div>
			</div>
		</div>
	</div>
	
	<!-- Schnellaktionen -->
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">⚡ Schnellzugriff</h3>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=domains" class="btn btn-outline-primary w-100">
								<i class="ti ti-world"></i> Domain Management
							</a>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=mx" class="btn btn-outline-primary w-100">
								<i class="ti ti-mail"></i> MX Records
							</a>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=groups" class="btn btn-outline-primary w-100">
								<i class="ti ti-users"></i> Gruppen
							</a>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=users" class="btn btn-outline-primary w-100">
								<i class="ti ti-user"></i> User-Verwaltung
							</a>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=permissions" class="btn btn-outline-primary w-100">
								<i class="ti ti-lock"></i> Permissions
							</a>
						</div>
						<div class="col-md-4 mb-3">
							<a href="{$pageURL}&action=audit" class="btn btn-outline-primary w-100">
								<i class="ti ti-file-text"></i> Audit-Logs
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

