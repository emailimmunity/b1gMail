<div class="emailadmin-audit">
	<h2 class="mb-4">📋 Audit-Logs</h2>
	
	<div class="alert alert-info">
		<i class="ti ti-info-circle"></i>
		Alle Admin-Aktionen werden hier protokolliert. Dies dient der Nachvollziehbarkeit und Compliance.
	</div>
	
	<!-- Audit-Logs -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Aktivitäts-Protokoll (Neueste 200 Einträge)</h3>
		</div>
		<div class="card-body p-0">
			{if $audit_logs}
			<div class="table-responsive">
				<table class="table table-vcenter card-table table-sm">
					<thead>
						<tr>
							<th>Zeitstempel</th>
							<th>User</th>
							<th>Aktion</th>
							<th>Details</th>
							<th>IP-Adresse</th>
						</tr>
					</thead>
					<tbody>
						{foreach $audit_logs as $log}
						<tr>
							<td class="text-nowrap">
								{$log.created_at|date_format:"%d.%m.%Y %H:%M:%S"}
							</td>
							<td>
								{if $log.user_email}
								<span class="text-muted">{$log.user_email}</span>
								{else}
								<span class="text-muted">User #{$log.user_id}</span>
								{/if}
							</td>
							<td>
								<code>{$log.action}</code>
							</td>
							<td>
								{if $log.details}
								<small class="text-muted">{$log.details|truncate:80:"..."}</small>
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td>
								<code class="text-muted" style="font-size:0.75rem;">{$log.ip_address}</code>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="card-footer">
				<div class="text-muted">
					Zeige neueste 200 Einträge. Ältere Einträge werden automatisch archiviert.
				</div>
			</div>
			{else}
			<div class="empty">
				<div class="empty-icon">
					<i class="ti ti-file-text" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine Audit-Logs vorhanden</p>
				<p class="empty-subtitle text-muted">
					Logs werden automatisch erstellt wenn Admin-Aktionen durchgeführt werden
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>

