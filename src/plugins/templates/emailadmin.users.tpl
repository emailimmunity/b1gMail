<div class="emailadmin-users">
	<h2 class="mb-4">👤 User Management</h2>
	
	<div class="alert alert-info">
		<i class="ti ti-info-circle"></i>
		Übersicht aller Benutzer im System. Hier können Sie sehen welche User in welchen Gruppen sind und deren Status überprüfen.
	</div>
	
	<!-- User-Liste -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">User-Liste (Neueste 100)</h3>
		</div>
		<div class="card-body p-0">
			{if $users}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Email</th>
							<th>Name</th>
							<th>Gruppen</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						{foreach $users as $user}
						<tr>
							<td><span class="text-muted">{$user.id}</span></td>
							<td><strong>{$user.email}</strong></td>
							<td>{$user.vorname} {$user.nachname}</td>
							<td>
								{if $user.group_count > 0}
								<span class="badge bg-blue" title="{$user.group_names}">{$user.group_count} Gruppe(n)</span>
								<br><small class="text-muted">{$user.group_names|truncate:50:"..."}</small>
								{else}
								<span class="text-muted">Keine Gruppen</span>
								{/if}
							</td>
							<td>
								{if $user.gesperrt == 'yes'}
								<span class="badge bg-danger">🔒 Gesperrt</span>
								{else}
								<span class="badge bg-success">✅ Aktiv</span>
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
					<i class="ti ti-user" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine User gefunden</p>
			</div>
			{/if}
		</div>
		<div class="card-footer">
			<div class="d-flex">
				<div class="text-muted">
					Zeige neueste 100 User. Für erweiterte Suche nutzen Sie User-Verwaltung.
				</div>
			</div>
		</div>
	</div>
</div>
