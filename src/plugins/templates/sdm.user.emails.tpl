{config_load file="default.conf" section=$theme_variant}
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Email-Accounts - SubDomainManager</title>
	<link rel="stylesheet" href="../css/tabler.min.css">
	<link rel="stylesheet" href="../css/tabler-icons.min.css">
</head>
<body>

<div class="container my-4">
	<h2 class="mb-4">📧 Email-Accounts für {$subdomain.full_domain}</h2>
	
	<!-- Zurück-Button -->
	<div class="mb-3">
		<a href="?p=plugins&plugin={$plugin_name}" class="btn btn-secondary">
			← Zurück zur Übersicht
		</a>
	</div>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Info-Box -->
	<div class="card mb-4">
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<h4>Subdomain: <strong>{$subdomain.full_domain}</strong></h4>
					<p class="text-muted mb-0">
						Status: <span class="badge bg-success">Aktiv</span>
					</p>
				</div>
				<div class="col-md-6 text-end">
					<h4>Email-Accounts: <strong>{$email_count} / {$max_emails}</strong></h4>
					{if $email_count >= $max_emails}
					<p class="text-danger mb-0">⚠️ Limit erreicht</p>
					{else}
					<p class="text-success mb-0">✅ {$max_emails - $email_count} verfügbar</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
	
	<!-- Neuen Email-Account erstellen -->
	{if $email_count < $max_emails}
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">➕ Neuen Email-Account erstellen</h3>
		</div>
		<div class="card-body">
			<form method="POST" action="">
				<input type="hidden" name="subdomain_id" value="{$subdomain.id}">
				
				<div class="row">
					<div class="col-md-5">
						<label class="form-label">Email-Adresse</label>
						<div class="input-group">
							<input type="text" name="email_local" class="form-control" placeholder="name" required pattern="[a-z0-9._-]+" title="Nur Kleinbuchstaben, Zahlen, Punkt, Unterstrich und Bindestrich">
							<span class="input-group-text">@{$subdomain.full_domain}</span>
						</div>
						<small class="text-muted">Nur Kleinbuchstaben, Zahlen, ., _ und -</small>
					</div>
					<div class="col-md-3">
						<label class="form-label">Passwort</label>
						<input type="password" name="email_password" class="form-control" placeholder="Mindestens 8 Zeichen" required minlength="8">
					</div>
					<div class="col-md-2">
						<label class="form-label">Quota (MB)</label>
						<input type="number" name="email_quota" class="form-control" value="1000" min="10" max="10000">
					</div>
					<div class="col-md-2">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="create_email" class="btn btn-primary w-100">
							➕ Erstellen
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	{else}
	<div class="alert alert-warning">
		<strong>⚠️ Limit erreicht!</strong> Sie haben das Maximum von {$max_emails} Email-Accounts für diese Subdomain erreicht.
	</div>
	{/if}
	
	<!-- Email-Liste -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Email-Accounts ({$emails|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $emails}
			<div class="table-responsive">
				<table class="table table-vcenter card-table table-hover">
					<thead>
						<tr>
							<th>Email-Adresse</th>
							<th>Quota</th>
							<th>Genutzt</th>
							<th>Status</th>
							<th>Erstellt</th>
							<th width="150">Aktionen</th>
						</tr>
					</thead>
					<tbody>
						{foreach $emails as $email}
						<tr>
							<td><strong>{$email.email_address}</strong></td>
							<td>{$email.quota_mb} MB</td>
							<td>
								{if $email.used_mb > 0}
								<div class="progress" style="height: 20px;">
									<div class="progress-bar" role="progressbar" style="width: {($email.used_mb / $email.quota_mb) * 100}%">
										{$email.used_mb} MB
									</div>
								</div>
								{else}
								<span class="text-muted">0 MB</span>
								{/if}
							</td>
							<td>
								{if $email.is_active}
								<span class="badge bg-success">✓ Aktiv</span>
								{else}
								<span class="badge bg-secondary">⏸️ Deaktiviert</span>
								{/if}
							</td>
							<td>{$email.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							<td>
								<a href="?p=plugins&plugin={$plugin_name}&action=emails&subdomain_id={$subdomain.id}&edit_email={$email.id}" 
								   class="btn btn-sm btn-primary" 
								   title="Bearbeiten">
									<i class="ti ti-edit"></i>
								</a>
								<a href="?p=plugins&plugin={$plugin_name}&action=emails&subdomain_id={$subdomain.id}&toggle_email={$email.id}" 
								   class="btn btn-sm btn-{if $email.is_active}warning{else}success{/if}" 
								   title="{if $email.is_active}Deaktivieren{else}Aktivieren{/if}">
									<i class="ti ti-{if $email.is_active}pause{else}player-play{/if}"></i>
								</a>
								<a href="?p=plugins&plugin={$plugin_name}&action=emails&subdomain_id={$subdomain.id}&delete_email={$email.id}" 
								   class="btn btn-sm btn-danger" 
								   onclick="return confirm('Email-Account wirklich löschen?\\n\\nAlle Emails in diesem Account gehen verloren!');"
								   title="Löschen">
									<i class="ti ti-trash"></i>
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<div class="empty p-4">
				<p class="empty-title h3">Keine Email-Accounts</p>
				<p class="empty-subtitle text-muted">Erstellen Sie Ihren ersten Email-Account für diese Subdomain.</p>
			</div>
			{/if}
		</div>
	</div>
	
	<!-- Info-Text -->
	<div class="alert alert-info mt-4">
		<h4>📖 Hinweise</h4>
		<ul class="mb-0">
			<li><strong>Webmail:</strong> Login unter <code>https://yourdomain.com/webmail</code></li>
			<li><strong>IMAP/POP3:</strong> Server: <code>mail.yourdomain.com</code></li>
			<li><strong>SMTP:</strong> Server: <code>mail.yourdomain.com</code>, Port: 587 (STARTTLS)</li>
			<li><strong>Benutzername:</strong> Ihre vollständige Email-Adresse</li>
		</ul>
	</div>
</div>

</body>
</html>
