<div class="emailadmin-groups">
	<h2 class="mb-4">👥 Gruppen Management</h2>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Gruppe hinzufügen -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">Neue Gruppe erstellen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=groups">
				<div class="row">
					<div class="col-md-4">
						<div class="mb-3">
							<label class="form-label required">Gruppenname</label>
							<input type="text" name="group_name" class="form-control" placeholder="z.B. Premium Users" required>
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-3">
							<label class="form-label">Beschreibung</label>
							<input type="text" name="group_desc" class="form-control" placeholder="Optionale Beschreibung">
						</div>
					</div>
					<div class="col-md-4">
						<div class="mb-3">
							<label class="form-label">Permissions (JSON)</label>
							<input type="text" name="permissions" class="form-control" placeholder='{ldelim}"email_quota": 5000{rdelim}' value="{ldelim}{rdelim}">
							<small class="form-hint">JSON-Format für Berechtigungen</small>
						</div>
					</div>
				</div>
				<button type="submit" name="add_group" class="btn btn-primary">
					<i class="ti ti-plus"></i> Gruppe erstellen
				</button>
			</form>
		</div>
	</div>
	
	<!-- Gruppen-Liste -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Vorhandene Gruppen ({$groups|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $groups}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Beschreibung</th>
							<th>Mitglieder</th>
							<th>Permissions</th>
							<th>Erstellt</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $groups as $group}
						<tr>
							<td><span class="text-muted">{$group.id}</span></td>
							<td><strong>{$group.name}</strong></td>
							<td>{$group.description|default:"-"}</td>
							<td>
								<span class="badge bg-blue">{$group.member_count} User</span>
							</td>
							<td>
								{if $group.permissions && $group.permissions != '{}'}
								<code class="text-muted" style="font-size:0.75rem;">{$group.permissions|truncate:30:"..."}</code>
								{else}
								<span class="text-muted">Keine</span>
								{/if}
							</td>
							<td>{$group.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							<td>
								<a href="{$pageURL}&action=groups&delete={$group.id}" 
								   onclick="return confirm('Gruppe {$group.name} wirklich löschen?')" 
								   class="btn btn-sm btn-danger">
									<i class="ti ti-trash"></i>
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
					<i class="ti ti-users" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine Gruppen vorhanden</p>
				<p class="empty-subtitle text-muted">
					Erstellen Sie oben eine neue Gruppe
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>

