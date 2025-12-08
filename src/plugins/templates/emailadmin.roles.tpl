<div class="emailadmin-roles">
	<h2 class="mb-4">👑 Rollen-System & Mandanten</h2>
	
	<div class="alert alert-info">
		<i class="ti ti-info-circle"></i>
		<strong>Hierarchisches Rollen-System:</strong> Definieren Sie Rollen und Rechte für verschiedene Admin-Ebenen.
		<br><strong>Superadmin</strong> → Reseller → Multidomain Admin → Domain Admin → Subdomain Admin → User
	</div>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Rolle zuweisen -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">User-Rolle zuweisen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=roles">
				<div class="row">
					<div class="col-md-4">
						<div class="mb-3">
							<label class="form-label required">User auswählen</label>
							<select name="user_id" class="form-select" required>
								<option value="">-- Bitte wählen --</option>
								{foreach $all_users as $user}
								<option value="{$user.id}">{$user.email} (#{$user.id})</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-3">
							<label class="form-label required">Rolle</label>
							<select name="role" class="form-select" required>
								<option value="superadmin">👑 Superadmin (Alle Rechte)</option>
								<option value="reseller">🏢 Reseller (Mehrere Domains)</option>
								<option value="multidomain_admin">🌐 Multidomain Admin</option>
								<option value="domain_admin">📧 Domain Admin</option>
								<option value="subdomain_admin">📁 Subdomain Admin</option>
								<option value="user">👤 User (Normale Rechte)</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="assign_role" class="btn btn-primary w-100">
							<i class="ti ti-user-check"></i> Rolle zuweisen
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Rollen-Hierarchie Info -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">📋 Rollen-Hierarchie & Berechtigungen</h3>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-vcenter">
					<thead>
						<tr>
							<th>Rolle</th>
							<th>Berechtigungen</th>
							<th>Beschreibung</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="badge bg-red">👑 Superadmin</span></td>
							<td><span class="badge bg-success">Alle Rechte</span></td>
							<td>Eigentümer des Systems, volle Kontrolle über alle Bereiche</td>
						</tr>
						<tr>
							<td><span class="badge bg-orange">🏢 Reseller</span></td>
							<td><span class="badge bg-success">Mehrere Domains</span>, Sub-Admins erstellen</td>
							<td>Kann mehrere Domains verwalten und Sub-Admins anlegen</td>
						</tr>
						<tr>
							<td><span class="badge" style="background: #1976D2; color: white;">🌐 Multidomain Admin</span></td>
							<td><span class="badge bg-success">Mehrere Domains</span>, <span class="badge bg-info">Sub-Admins erstellen</span></td>
							<td>Verwaltet mehrere Domains und kann Sub-Admins für eigene Domains anlegen</td>
						</tr>
						<tr>
							<td><span class="badge" style="background: #0288D1; color: white;">📧 Domain Admin</span></td>
							<td><span class="badge bg-info">Eigene Domain</span>, <span class="badge bg-info">Sub-Admins erstellen</span></td>
							<td>Verwaltet eine Domain und kann Sub-Admins (z.B. Subdomain-Admins) anlegen</td>
						</tr>
						<tr>
							<td><span class="badge bg-cyan">📁 Subdomain Admin</span></td>
							<td><span class="badge bg-secondary">Nur Subdomain</span></td>
							<td>Verwaltet nur Subdomains einer Domain</td>
						</tr>
						<tr>
							<td><span class="badge bg-gray">👤 User</span></td>
							<td><span class="badge bg-secondary">Normale Rechte</span></td>
							<td>Standard-Benutzer ohne Admin-Rechte</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	<!-- User mit Rollen -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">User mit zugewiesenen Rollen ({$users_with_roles|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $users_with_roles}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>User</th>
							<th>Email</th>
							<th>Rolle</th>
							<th>Zugewiesen am</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $users_with_roles as $user}
						<tr>
							<td><span class="text-muted">#{$user.user_id}</span></td>
							<td>{$user.email}</td>
							<td>
								{if $user.role == 'superadmin'}
								<span class="badge" style="background: #C62828; color: white;">👑 Superadmin</span>
								{elseif $user.role == 'reseller'}
								<span class="badge" style="background: #D84315; color: white;">🏢 Reseller</span>
								{elseif $user.role == 'multidomain_admin'}
								<span class="badge" style="background: #1976D2; color: white;">🌐 Multidomain Admin</span>
								{elseif $user.role == 'domain_admin'}
								<span class="badge" style="background: #0288D1; color: white;">📧 Domain Admin</span>
								{elseif $user.role == 'subdomain_admin'}
								<span class="badge" style="background: #00ACC1; color: white;">📁 Subdomain Admin</span>
								{else}
								<span class="badge" style="background: #616161; color: white;">👤 User</span>
								{/if}
							</td>
							<td>{$user.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							<td>
								<a href="{$pageURL}&action=roles&delete={$user.id}" 
								   onclick="return confirm('Rolle für {$user.email} entfernen?')" 
								   class="btn btn-sm btn-danger">
									<i class="ti ti-trash"></i> Entfernen
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
					<i class="ti ti-crown" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine Rollen zugewiesen</p>
				<p class="empty-subtitle text-muted">
					Weisen Sie oben einem User eine Rolle zu
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>

