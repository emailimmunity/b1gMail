<div class="emailadmin-mx">
	<h2 class="mb-4">📧 MX Record Management</h2>
	
	<div class="alert alert-info">
		<i class="ti ti-info-circle"></i>
		<strong>Hinweis:</strong> Hier konfigurieren Sie, welche MX Records für Domain-Registrierungen gültig sind. 
		User können nur Domains hinzufügen, die einen dieser MX Records haben. Maximum: 20 Records.
	</div>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- MX Record hinzufügen -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">Neuen MX Record hinzufügen</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=mx">
				<div class="row">
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label required">MX Record</label>
							<input type="text" name="mx_record" class="form-control" placeholder="mail.example.com" required>
							<small class="form-hint">FQDN des Mail-Servers (z.B. mail.example.com)</small>
						</div>
					</div>
					<div class="col-md-3">
						<div class="mb-3">
							<label class="form-label">Priority</label>
							<input type="number" name="priority" class="form-control" value="10" min="1" max="100">
							<small class="form-hint">Niedrigere Zahl = höhere Priorität</small>
						</div>
					</div>
					<div class="col-md-3">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="add_mx" class="btn btn-primary w-100">
							<i class="ti ti-plus"></i> Hinzufügen
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- MX Records-Liste -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Konfigurierte MX Records ({$mx_records|@count}/20)</h3>
		</div>
		<div class="card-body p-0">
			{if $mx_records}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>MX Record</th>
							<th>Priority</th>
							<th>Status</th>
							<th>Erstellt</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $mx_records as $mx}
						<tr>
							<td><span class="text-muted">{$mx.id}</span></td>
							<td><code>{$mx.mx_record}</code></td>
							<td>
								<span class="badge bg-blue">{$mx.priority}</span>
							</td>
							<td>
								{if $mx.active}
								<span class="badge bg-success">✅ Aktiv</span>
								{else}
								<span class="badge bg-secondary">⏸️ Inaktiv</span>
								{/if}
							</td>
							<td>{$mx.created_at|date_format:"%d.%m.%Y %H:%M"}</td>
							<td>
								<a href="{$pageURL}&action=mx&delete={$mx.id}" 
								   onclick="return confirm('MX Record {$mx.mx_record} wirklich löschen?')" 
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
					<i class="ti ti-mail" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine MX Records konfiguriert</p>
				<p class="empty-subtitle text-muted">
					Fügen Sie oben einen MX Record hinzu um Domain-Validierung zu aktivieren
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>

