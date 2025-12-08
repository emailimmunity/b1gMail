<div class="emailadmin-simple">
	<h2 class="mb-4">📧 EmailAdmin Simple</h2>
	
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert"></a>
	</div>
	{/if}
	
	<!-- JavaScript Tabs (KEIN neuer Request!) -->
	<ul class="nav nav-tabs mb-4" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dashboard" type="button">Dashboard</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" data-bs-toggle="tab" data-bs-target="#domains" type="button">Domains</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" data-bs-toggle="tab" data-bs-target="#groups" type="button">Gruppen</button>
		</li>
	</ul>
	
	<div class="tab-content">
		<!-- Dashboard Tab -->
		<div class="tab-pane fade show active" id="dashboard" role="tabpanel">
			<h3>📊 Statistiken</h3>
			<div class="row">
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<h4>Total Users</h4>
							<p class="h1">{$stats.users}</p>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<h4>Total Domains</h4>
							<p class="h1">{$stats.domains}</p>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<h4>Total Gruppen</h4>
							<p class="h1">{$stats.groups}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Domains Tab -->
		<div class="tab-pane fade" id="domains" role="tabpanel">
			<h3>🌐 Domain Management</h3>
			
			<div class="card mb-3">
				<div class="card-header">
					<h4>Neue Domain hinzufügen</h4>
				</div>
				<div class="card-body">
					<form method="post" action="{$pageURL}">
						<div class="row">
							<div class="col-md-8">
								<input type="text" name="domain" class="form-control" placeholder="example.com" required>
							</div>
							<div class="col-md-4">
								<button type="submit" name="add_domain" class="btn btn-primary w-100">Hinzufügen</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<div class="card">
				<div class="card-header">
					<h4>Vorhandene Domains ({$domains|@count})</h4>
				</div>
				<div class="card-body">
					{if $domains}
					<table class="table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Domain</th>
								<th>Erstellt</th>
							</tr>
						</thead>
						<tbody>
							{foreach $domains as $domain}
							<tr>
								<td>{$domain.id}</td>
								<td><strong>{$domain.domain}</strong></td>
								<td>{$domain.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
					{else}
					<p>Keine Domains vorhanden</p>
					{/if}
				</div>
			</div>
		</div>
		
		<!-- Gruppen Tab -->
		<div class="tab-pane fade" id="groups" role="tabpanel">
			<h3>👥 Gruppen Management</h3>
			
			<div class="card mb-3">
				<div class="card-header">
					<h4>Neue Gruppe erstellen</h4>
				</div>
				<div class="card-body">
					<form method="post" action="{$pageURL}">
						<div class="row mb-2">
							<div class="col-md-4">
								<input type="text" name="group_name" class="form-control" placeholder="Gruppenname" required>
							</div>
							<div class="col-md-4">
								<input type="text" name="group_desc" class="form-control" placeholder="Beschreibung">
							</div>
							<div class="col-md-4">
								<input type="text" name="permissions" class="form-control" placeholder='{ldelim}"quota":5000{rdelim}' value="{ldelim}{rdelim}">
							</div>
						</div>
						<button type="submit" name="add_group" class="btn btn-primary">Gruppe erstellen</button>
					</form>
				</div>
			</div>
			
			<div class="card">
				<div class="card-header">
					<h4>Vorhandene Gruppen ({$groups|@count})</h4>
				</div>
				<div class="card-body">
					{if $groups}
					<table class="table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Name</th>
								<th>Beschreibung</th>
								<th>Permissions</th>
							</tr>
						</thead>
						<tbody>
							{foreach $groups as $group}
							<tr>
								<td>{$group.id}</td>
								<td><strong>{$group.name}</strong></td>
								<td>{$group.description}</td>
								<td><code>{$group.permissions|truncate:50}</code></td>
							</tr>
							{/foreach}
						</tbody>
					</table>
					{else}
					<p>Keine Gruppen vorhanden</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
</div>

