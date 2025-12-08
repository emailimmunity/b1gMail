<div class="container-fluid">
	<h1>🔧 Protokoll-Verwaltung</h1>
	<p class="text-muted">Verwalten Sie alle Protokolle, die Benutzern auf der Mailserver-Seite angezeigt werden.</p>
	
	{if $message}
	<div class="alert alert-success alert-dismissible fade show">
		{$message}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	{/if}
	
	{if $error}
	<div class="alert alert-danger alert-dismissible fade show">
		{$error}
		<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
	{/if}
	
	<!-- STATISTIKEN -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card text-white bg-primary">
				<div class="card-body">
					<h5 class="card-title">📊 Gesamt</h5>
					<h2>{$stats.total}</h2>
					<p class="mb-0 small">Protokolle</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-success">
				<div class="card-body">
					<h5 class="card-title">✅ Aktiviert</h5>
					<h2>{$stats.enabled}</h2>
					<p class="mb-0 small">Sichtbar für User</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-secondary">
				<div class="card-body">
					<h5 class="card-title">❌ Deaktiviert</h5>
					<h2>{$stats.disabled}</h2>
					<p class="mb-0 small">Verborgen</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-info">
				<div class="card-body">
					<h5 class="card-title">📧 E-Mail</h5>
					<h2>{$stats.by_category.email|default:0}</h2>
					<p class="mb-0 small">IMAP/POP3/SMTP</p>
				</div>
			</div>
		</div>
	</div>
	
	<!-- PROTOKOLL-LISTE -->
	<div class="card">
		<div class="card-header bg-dark text-white">
			<h5 class="mb-0">📋 Alle Protokolle</h5>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover mb-0">
					<thead class="thead-light">
						<tr>
							<th width="30">#</th>
							<th width="50">Status</th>
							<th>Typ</th>
							<th>Kategorie</th>
							<th>Titel (DE/EN)</th>
							<th>Server</th>
							<th>Port</th>
							<th>SSL</th>
							<th width="150">Aktionen</th>
						</tr>
					</thead>
					<tbody id="protocolList">
						{foreach from=$protocols item=protocol}
						<tr data-id="{$protocol.id}">
							<td class="text-muted">{$protocol.display_order}</td>
							<td class="text-center">
								{if $protocol.enabled}
									<span class="badge badge-success">✅ ON</span>
								{else}
									<span class="badge badge-secondary">❌ OFF</span>
								{/if}
							</td>
							<td>
								<strong>
									{if $protocol.icon}<i class="fa {$protocol.icon}"></i> {/if}
									{$protocol.protocol_type}
								</strong>
								{if $protocol.is_system}
									<span class="badge badge-warning badge-sm">System</span>
								{/if}
							</td>
							<td>
								<span class="badge badge-info">{$protocol.protocol_category}</span>
							</td>
							<td>
								<div>{$protocol.title_de}</div>
								<small class="text-muted">{$protocol.title_en}</small>
							</td>
							<td>
								<code class="small">{$protocol.server_host|default:'-'}</code>
							</td>
							<td>
								<code class="small">{$protocol.server_port|default:'-'}</code>
							</td>
							<td>
								<span class="badge badge-{if $protocol.ssl_type == 'ssl'}success{elseif $protocol.ssl_type == 'tls'}info{else}secondary{/if}">
									{$protocol.ssl_type}
								</span>
							</td>
							<td class="text-right">
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-info" onclick="editProtocol({$protocol.id})" title="Bearbeiten">
										<i class="fa fa-edit"></i>
									</button>
									<a href="?action=toggle&id={$protocol.id}" class="btn btn-{if $protocol.enabled}warning{else}success{/if}" title="{if $protocol.enabled}Deaktivieren{else}Aktivieren{/if}">
										<i class="fa fa-{if $protocol.enabled}eye-slash{else}eye{/if}"></i>
									</a>
									{if !$protocol.is_system}
									<a href="?action=delete&id={$protocol.id}" class="btn btn-danger" onclick="return confirm('Wirklich löschen?')" title="Löschen">
										<i class="fa fa-trash"></i>
									</a>
									{/if}
								</div>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	<!-- HILFE -->
	<div class="alert alert-info mt-4">
		<h5><i class="fa fa-info-circle"></i> Hinweise</h5>
		<ul class="mb-0">
			<li><strong>Server-Host:</strong> Verwenden Sie <code>{literal}{domain}{/literal}</code> als Platzhalter für die aktuelle Domain</li>
			<li><strong>System-Protokolle:</strong> Können nicht gelöscht werden (IMAP, SMTP, etc.)</li>
			<li><strong>Reihenfolge:</strong> Protokolle werden nach <code>display_order</code> sortiert angezeigt</li>
			<li><strong>Deaktivierte Protokolle:</strong> Werden Benutzern nicht angezeigt</li>
		</ul>
	</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="post" action="?action=update">
				<div class="modal-header">
					<h5 class="modal-title">Protokoll bearbeiten</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<input type="hidden" name="id" id="edit_id">
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Titel (Deutsch)</label>
								<input type="text" name="title_de" id="edit_title_de" class="form-control" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Titel (English)</label>
								<input type="text" name="title_en" id="edit_title_en" class="form-control" required>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Beschreibung (Deutsch)</label>
								<textarea name="description_de" id="edit_description_de" class="form-control" rows="2"></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Beschreibung (English)</label>
								<textarea name="description_en" id="edit_description_en" class="form-control" rows="2"></textarea>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Icon (Font Awesome)</label>
								<input type="text" name="icon" id="edit_icon" class="form-control" placeholder="fa-envelope">
							</div>
						</div>
					</div>
					
					<hr>
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Server Host</label>
								<input type="text" name="server_host" id="edit_server_host" class="form-control" placeholder="{literal}{domain}{/literal}">
								<small class="form-text text-muted">Platzhalter: <code>{literal}{domain}{/literal}</code></small>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Port</label>
								<input type="number" name="server_port" id="edit_server_port" class="form-control">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>SSL Typ</label>
								<select name="ssl_type" id="edit_ssl_type" class="form-control">
									{foreach from=$ssl_types key=k item=v}
									<option value="{$k}">{$v}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<label>Server Pfad</label>
						<input type="text" name="server_path" id="edit_server_path" class="form-control" placeholder="/dav">
					</div>
					
					<hr>
					
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Hilfe-Link Titel (DE)</label>
								<input type="text" name="help_link_title_de" id="edit_help_link_title_de" class="form-control">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Hilfe-Link Titel (EN)</label>
								<input type="text" name="help_link_title_en" id="edit_help_link_title_en" class="form-control">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Hilfe-Link URL</label>
								<input type="url" name="help_link_url" id="edit_help_link_url" class="form-control">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
					<button type="submit" class="btn btn-primary">Speichern</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
// Protocol data for edit modal
const protocols = {
	{foreach from=$protocols item=p name=ploop}
	{$p.id}: {
		id: {$p.id},
		title_de: '{$p.title_de|escape:'javascript'}',
		title_en: '{$p.title_en|escape:'javascript'}',
		description_de: '{$p.description_de|escape:'javascript'}',
		description_en: '{$p.description_en|escape:'javascript'}',
		icon: '{$p.icon|escape:'javascript'}',
		server_host: '{$p.server_host|escape:'javascript'}',
		server_port: {$p.server_port|default:'null'},
		server_path: '{$p.server_path|escape:'javascript'}',
		ssl_type: '{$p.ssl_type}',
		help_link_title_de: '{$p.help_link_title_de|escape:'javascript'}',
		help_link_title_en: '{$p.help_link_title_en|escape:'javascript'}',
		help_link_url: '{$p.help_link_url|escape:'javascript'}'
	}{if !$smarty.foreach.ploop.last},{/if}
	{/foreach}
};

function editProtocol(id) {
	const p = protocols[id];
	if(!p) return;
	
	document.getElementById('edit_id').value = p.id;
	document.getElementById('edit_title_de').value = p.title_de;
	document.getElementById('edit_title_en').value = p.title_en;
	document.getElementById('edit_description_de').value = p.description_de;
	document.getElementById('edit_description_en').value = p.description_en;
	document.getElementById('edit_icon').value = p.icon;
	document.getElementById('edit_server_host').value = p.server_host;
	document.getElementById('edit_server_port').value = p.server_port || '';
	document.getElementById('edit_server_path').value = p.server_path;
	document.getElementById('edit_ssl_type').value = p.ssl_type;
	document.getElementById('edit_help_link_title_de').value = p.help_link_title_de;
	document.getElementById('edit_help_link_title_en').value = p.help_link_title_en;
	document.getElementById('edit_help_link_url').value = p.help_link_url;
	
	$('#editModal').modal('show');
}
</script>
