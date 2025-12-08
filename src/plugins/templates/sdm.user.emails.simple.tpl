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
	<h2 class="mb-4">📧 Email-Verwaltung für Ihre Subdomains</h2>
	
	<!-- Zurück-Button -->
	<div class="mb-3">
		<a href="?p=plugins&plugin={$plugin_name}" class="btn btn-secondary">
			← Zurück zur Übersicht
		</a>
	</div>
	
	<!-- Info-Box: Integration mit b1gMail -->
	<div class="alert alert-info">
		<h4 class="alert-title">✅ Email-Verwaltung integriert!</h4>
		<p class="mb-2">
			Ihre Subdomains sind automatisch für Email-Empfang konfiguriert. 
			<strong>Verwenden Sie die Standard-Email-Verwaltung von b1gMail</strong> um Email-Accounts zu erstellen und zu verwalten.
		</p>
		<div class="mt-3">
			<a href="../index.php?p=prefs&s=email" class="btn btn-primary">
				<i class="ti ti-mail"></i> Zur Email-Verwaltung
			</a>
		</div>
	</div>
	
	<!-- Subdomain-Übersicht mit Email-Counts -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Ihre Subdomains mit Email-Accounts</h3>
		</div>
		<div class="card-body p-0">
			{if $mySubdomains}
			<div class="table-responsive">
				<table class="table table-vcenter card-table table-hover">
					<thead>
						<tr>
							<th>Subdomain</th>
							<th>Email-Accounts</th>
							<th>Status</th>
							<th>Erstellt</th>
							<th>Aktionen</th>
						</tr>
					</thead>
					<tbody>
						{foreach $mySubdomains as $sub}
						<tr>
							<td><strong>{$sub.full_domain}</strong></td>
							<td>
								{if $sub.email_count > 0}
								<span class="badge bg-success">{$sub.email_count} Account(s)</span>
								{else}
								<span class="text-muted">Keine Accounts</span>
								{/if}
							</td>
							<td>
								{if $sub.status == 'active'}
								<span class="badge bg-success">✓ Aktiv</span>
								{else}
								<span class="badge bg-secondary">{$sub.status}</span>
								{/if}
							</td>
							<td>{$sub.created_at|date_format:"%d.%m.%Y"}</td>
							<td>
								<a href="../index.php?p=prefs&s=email&domain={$sub.full_domain}" 
								   class="btn btn-sm btn-primary"
								   title="Email-Accounts verwalten">
									<i class="ti ti-mail"></i> Emails verwalten
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<div class="empty p-4">
				<p class="empty-title h3">Keine Subdomains</p>
				<p class="empty-subtitle text-muted">Erstellen Sie zuerst eine Subdomain.</p>
			</div>
			{/if}
		</div>
	</div>
	
	<!-- Anleitung -->
	<div class="card mt-4">
		<div class="card-header">
			<h3 class="card-title">📖 Wie funktioniert es?</h3>
		</div>
		<div class="card-body">
			<h4>1. Email-Account erstellen</h4>
			<ol>
				<li>Klicken Sie auf <strong>"Emails verwalten"</strong> neben Ihrer Subdomain</li>
				<li>Oder gehen Sie zu <strong>Einstellungen → Email</strong></li>
				<li>Wählen Sie Ihre Subdomain als Domain aus</li>
				<li>Erstellen Sie einen neuen Email-Account</li>
			</ol>
			
			<h4 class="mt-4">2. Email-Adressen Format</h4>
			<p>Alle Email-Accounts unter Ihrer Subdomain haben das Format:</p>
			<div class="bg-light p-3 rounded">
				<code>benutzername@ihre-subdomain.domain.com</code>
			</div>
			
			<h4 class="mt-4">3. Webmail & Email-Clients</h4>
			<ul class="mb-0">
				<li><strong>Webmail:</strong> <a href="../webmail" target="_blank">Zum Webmail</a></li>
				<li><strong>IMAP:</strong> <code>mail.yourdomain.com</code> Port: 993 (SSL)</li>
				<li><strong>SMTP:</strong> <code>mail.yourdomain.com</code> Port: 587 (STARTTLS)</li>
			</ul>
		</div>
	</div>
	
	<!-- Vorteile -->
	<div class="alert alert-success mt-4">
		<h4>✅ Vorteile der Integration</h4>
		<ul class="mb-0">
			<li>✓ <strong>Automatisch:</strong> Subdomains sofort Email-fähig</li>
			<li>✓ <strong>Zentral:</strong> Alle Emails an einem Ort verwalten</li>
			<li>✓ <strong>Professionell:</strong> Vollständige b1gMail-Funktionen</li>
			<li>✓ <strong>Sicher:</strong> Spam-Filter, Virus-Schutz, Verschlüsselung</li>
		</ul>
	</div>
</div>

</body>
</html>
