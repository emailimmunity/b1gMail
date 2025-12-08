<!-- Tab Navigation -->
{assign var="baseLink" value="plugin.page.php?plugin="|cat:$plugin_name|cat:"&sid="|cat:$sid}
<div class="card mb-3">
	<div class="card-header p-0">
		<ul class="nav nav-tabs card-header-tabs" role="tablist">
			<li class="nav-item">
				<a href="{$baseLink}&action=dashboard" class="nav-link {if $current_action == 'dashboard'}active{/if}">
					Dashboard
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=grants" class="nav-link {if $current_action == 'grants'}active{/if}">
					Domain-Freigaben
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=subdomains" class="nav-link {if $current_action == 'subdomains'}active{/if}">
					Subdomains
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=dyndns" class="nav-link {if $current_action == 'dyndns'}active{/if}">
					DynDNS
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=permissions" class="nav-link {if $current_action == 'permissions'}active{/if}">
					Permissions
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=blacklist" class="nav-link {if $current_action == 'blacklist'}active{/if}">
					Blacklist
				</a>
			</li>
			<li class="nav-item">
				<a href="{$baseLink}&action=settings" class="nav-link {if $current_action == 'settings'}active{/if}">
					Einstellungen
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="sdm-blacklist">
	<h2 class="mb-4">🚫 Blacklist - Verbotene Subdomain-Namen</h2>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Neuer Eintrag / Bearbeiten -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">
				{if $editItem}
				✏️ Blacklist-Eintrag bearbeiten
				{else}
				➕ Blacklist-Eintrag hinzufügen
				{/if}
			</h3>
			{if $editItem}
			<div class="card-actions">
				<a href="{$baseLink}&action=blacklist" class="btn btn-sm">Abbrechen</a>
			</div>
			{/if}
		</div>
		<div class="card-body">
			<form method="POST" action="{$baseLink}&action=blacklist">
				{if $editItem}
				<input type="hidden" name="item_id" value="{$editItem.id}">
				{/if}
				
				<div class="row">
					<div class="col-md-4">
						<label class="form-label">Begriff (verbotener Subdomain-Name)</label>
						<input type="text" name="term" class="form-control" placeholder="z.B. sex, admin, porn..." value="{if $editItem}{$editItem.term}{/if}" required>
						<small class="text-muted">Kleinbuchstaben, wird auch in Teilstrings gefunden</small>
					</div>
					<div class="col-md-6">
						<label class="form-label">Grund / Kategorie</label>
						<input type="text" name="reason" class="form-control" placeholder="z.B. Unangemessener Inhalt, System-reserviert..." value="{if $editItem}{$editItem.reason}{/if}">
					</div>
					<div class="col-md-2">
						<label class="form-label">&nbsp;</label>
						{if $editItem}
						<button type="submit" name="update_blacklist" class="btn btn-success w-100">
							✏️ Aktualisieren
						</button>
						{else}
						<button type="submit" name="add_blacklist" class="btn btn-primary w-100">
							➕ Hinzufügen
						</button>
						{/if}
					</div>
				</div>
				
				{if $editItem}
				<div class="row mt-3">
					<div class="col-md-12">
						<label class="form-check form-switch">
							<input type="checkbox" name="is_active" class="form-check-input" {if $editItem.is_active}checked{/if}>
							<span class="form-check-label">Eintrag ist aktiv</span>
						</label>
					</div>
				</div>
				{/if}
			</form>
		</div>
	</div>
	
	<!-- Blacklist-Tabelle -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Aktuelle Blacklist ({$blacklist|@count} Einträge)</h3>
		</div>
		<div class="card-body p-0">
			{if $blacklist}
			<div class="table-responsive">
				<table class="table table-hover mb-0">
					<thead>
						<tr>
							<th>Begriff</th>
							<th>Grund / Kategorie</th>
							<th>Status</th>
							<th>Erstellt</th>
							<th width="150">Aktionen</th>
						</tr>
					</thead>
					<tbody>
						{foreach $blacklist as $item}
						<tr>
							<td><strong><code>{$item.term}</code></strong></td>
							<td>{$item.reason|default:'-'}</td>
							<td>
								{if $item.is_active}
								<span class="badge bg-danger">🚫 Aktiv</span>
								{else}
								<span class="badge bg-secondary">⏸️ Deaktiviert</span>
								{/if}
							</td>
							<td>{$item.created_at|date_format:"%d.%m.%Y %H:%M"|default:'-'}</td>
							<td>
								<a href="{$baseLink}&action=blacklist&edit={$item.id}" 
								   class="btn btn-sm btn-primary" 
								   title="Bearbeiten">
									✏️
								</a>
								<a href="{$baseLink}&action=blacklist&toggle={$item.id}" 
								   class="btn btn-sm btn-{if $item.is_active}warning{else}success{/if}" 
								   title="{if $item.is_active}Deaktivieren{else}Aktivieren{/if}">
									{if $item.is_active}⏸️{else}▶️{/if}
								</a>
								<a href="{$baseLink}&action=blacklist&delete={$item.id}" 
								   class="btn btn-sm btn-danger" 
								   onclick="return confirm('Blacklist-Eintrag wirklich löschen?');"
								   title="Löschen">
									🗑️
								</a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<div class="empty p-4">
				<p class="empty-title h3">Keine Blacklist-Einträge</p>
				<p class="empty-subtitle text-muted">Fügen Sie verbotene Subdomain-Namen hinzu, um sie zu blockieren.</p>
			</div>
			{/if}
		</div>
	</div>
	
	<div class="alert alert-info mt-4">
		<strong>ℹ️ Hinweis:</strong> Blacklist-Einträge werden bei der Subdomain-Erstellung geprüft:
		<ul class="mb-0 mt-2">
			<li><strong>Exakter Match:</strong> "sex" blockiert "sex.domain.com"</li>
			<li><strong>Teilstring-Match:</strong> "sex" blockiert auch "sex123.domain.com" oder "mysex.domain.com"</li>
			<li><strong>Groß-/Kleinschreibung:</strong> Wird ignoriert (case-insensitive)</li>
		</ul>
	</div>
</div>
