<div class="emailadmin-permissions">
	<h2 class="mb-4">🔒 Permissions & User-Gruppen-Zuordnung</h2>
	
	<div class="alert alert-info">
		<i class="ti ti-info-circle"></i>
		<strong>Hinweis:</strong> Weisen Sie User zu Gruppen zu. User erben dann automatisch die Permissions ihrer Gruppen (JSON-Format).
	</div>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- User zu Gruppe zuweisen -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">User zu Gruppe zuweisen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=permissions">
				<div class="row">
					<div class="col-md-5">
						<div class="mb-3">
							<label class="form-label required">User auswählen</label>
							<select name="user_id" class="form-select" required>
								<option value="">-- Bitte wählen --</option>
								{foreach $all_users as $user}
								<option value="{$user.id}">{$user.email} ({$user.vorname} {$user.nachname})</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="col-md-5">
						<div class="mb-3">
							<label class="form-label required">Gruppe auswählen</label>
							<select name="group_id" class="form-select" required>
								<option value="">-- Bitte wählen --</option>
								{foreach $groups_with_users as $group}
								<option value="{$group.id}">{$group.name} ({$group.members|@count} Mitglieder)</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="assign_user" class="btn btn-primary w-100">
							<i class="ti ti-user-plus"></i> Zuweisen
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Gruppen mit Mitgliedern -->
	{if $groups_with_users}
	{foreach $groups_with_users as $group}
	<div class="card mb-3">
		<div class="card-header">
			<div class="row align-items-center">
				<div class="col">
					<h3 class="card-title">
						<i class="ti ti-users"></i> {$group.name}
						<span class="badge bg-blue ms-2">{$group.members|@count} Mitglied(er)</span>
					</h3>
					{if $group.description}
					<p class="text-muted mb-0"><small>{$group.description}</small></p>
					{/if}
				</div>
			</div>
		</div>
		<div class="card-body">
			{if $group.permissions && $group.permissions != '{ldelim}{rdelim}'}
			<div class="mb-3">
				<strong>Permissions:</strong>
				<code class="ms-2">{$group.permissions}</code>
			</div>
			{/if}
			
			{if $group.members}
			<div class="table-responsive">
				<table class="table table-sm table-vcenter">
					<thead>
						<tr>
							<th>ID</th>
							<th>Email</th>
							<th>Name</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $group.members as $member}
						<tr>
							<td><span class="text-muted">#{$member.id}</span></td>
							<td>{$member.email}</td>
							<td>{$member.vorname} {$member.nachname}</td>
							<td>
								<a href="{$pageURL}&action=permissions&remove_user={$member.id}&group_id={$group.id}" 
								   onclick="return confirm('User {$member.email} aus Gruppe {$group.name} entfernen?')" 
								   class="btn btn-sm btn-danger">
									<i class="ti ti-user-minus"></i> Entfernen
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<p class="text-muted">Keine Mitglieder in dieser Gruppe</p>
			{/if}
		</div>
	</div>
	{/foreach}
	{else}
	<div class="empty">
		<div class="empty-icon">
			<i class="ti ti-lock" style="font-size: 3rem;"></i>
		</div>
		<p class="empty-title">Keine Gruppen vorhanden</p>
		<p class="empty-subtitle text-muted">
			Erstellen Sie zuerst Gruppen unter "Gruppen Management"
		</p>
		<a href="{$pageURL}&action=groups" class="btn btn-primary">
			Zu Gruppen Management →
		</a>
	</div>
	{/if}
</div>

