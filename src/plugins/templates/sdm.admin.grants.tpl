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

<div class="sdm-grants">
	<h2 class="mb-4">🔐 Domain-Freigaben</h2>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Domain freigeben / bearbeiten -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">
				{if $editGrant}
				✏️ Domain-Freigabe bearbeiten
				{else}
				➕ Domain für Gruppe freigeben
				{/if}
			</h3>
			{if $editGrant}
			<div class="card-actions">
				<a href="{$baseLink}&action=grants" class="btn btn-sm">Abbrechen</a>
			</div>
			{/if}
		</div>
		<div class="card-body">
			<form method="post" action="{$baseLink}&action=grants">
				{if $editGrant}
				<input type="hidden" name="grant_id" value="{$editGrant.id}">
				{/if}
				
				<div class="row">
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label">Domain</label>
							<select name="domain" class="form-select" required>
								<option value="">-- Domain wählen --</option>
								{foreach $availableDomains as $domain}
								<option value="{$domain}" {if $editGrant && $editGrant.domain == $domain}selected{/if}>{$domain}</option>
								{/foreach}
							</select>
							<small class="form-hint">System-Domains aus den Allgemeinen Einstellungen</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label">Gruppe</label>
							<select name="group_id" class="form-select" required>
								<option value="">-- Gruppe wählen --</option>
								{foreach $groups as $group}
								<option value="{$group.id}" {if $editGrant && $editGrant.group_id == $group.id}selected{/if}>{$group.gruppe}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<label class="form-label">Features</label>
						<div class="mb-3">
							<label class="form-check">
								<input type="checkbox" name="email_enabled" class="form-check-input" {if !$editGrant || $editGrant.email_enabled}checked{/if}>
								<span class="form-check-label">Email-Adressen erlauben</span>
							</label>
							<label class="form-check">
								<input type="checkbox" name="dyndns_enabled" class="form-check-input" {if $editGrant && $editGrant.dyndns_enabled}checked{/if}>
								<span class="form-check-label">DynDNS-Service aktivieren</span>
							</label>
							<label class="form-check">
								<input type="checkbox" name="keyhelp_enabled" class="form-check-input" {if $editGrant && $editGrant.keyhelp_enabled}checked{/if}>
								<span class="form-check-label">KeyHelp Webserver aktivieren</span>
							</label>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label">Max. Subdomains pro User</label>
							<input type="number" name="max_subdomains" class="form-control" value="{if $editGrant}{$editGrant.max_subdomains}{else}5{/if}" min="1" max="100">
						</div>
					</div>
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label">Max. Emails pro Subdomain</label>
							<input type="number" name="max_emails" class="form-control" value="{if $editGrant}{$editGrant.max_emails_per_subdomain}{else}10{/if}" min="1" max="100">
						</div>
					</div>
				</div>
				
				{if $editGrant}
				<button type="submit" name="update_grant" class="btn btn-primary">
					✏️ Freigabe aktualisieren
				</button>
				<a href="{$baseLink}&action=grants" class="btn">Abbrechen</a>
				{else}
				<button type="submit" name="add_grant" class="btn btn-primary">
					➕ Freigabe erstellen
				</button>
				{/if}
			</form>
		</div>
	</div>
	
	<!-- Aktive Freigaben -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Aktive Freigaben ({$grants|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $grants}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Domain</th>
							<th>Gruppe</th>
							<th>Features</th>
							<th>Limits</th>
							<th>Erstellt</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $grants as $grant}
						<tr>
							<td><strong>{$grant.domain}</strong></td>
							<td>{$grant.group_name|default:'Unbekannt'}</td>
							<td>
								{if $grant.email_enabled}
								<span class="badge bg-success">Email</span>
								{/if}
								{if $grant.dyndns_enabled}
								<span class="badge bg-info">DynDNS</span>
								{/if}
								{if $grant.keyhelp_enabled}
								<span class="badge bg-warning">KeyHelp</span>
								{/if}
							</td>
							<td>
								<small>
									Max. {$grant.max_subdomains} Subdomains<br>
									Max. {$grant.max_emails_per_subdomain} Emails
								</small>
							</td>
							<td>{$grant.created_at}</td>
							<td>
								<a href="{$baseLink}&action=grants&edit={$grant.id}" 
								   class="btn btn-sm btn-primary" 
								   title="Bearbeiten">
									✏️
								</a>
								<a href="{$baseLink}&action=grants&delete={$grant.id}" 
								   onclick="return confirm('Freigabe wirklich löschen?')" 
								   class="btn btn-sm btn-danger"
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
			<div class="empty">
				<div class="empty-icon">
					<i class="ti ti-lock-open" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine Freigaben vorhanden</p>
				<p class="empty-subtitle text-muted">
					Erstellen Sie oben eine Domain-Freigabe für eine Gruppe.
				</p>
			</div>
			{/if}
		</div>
	</div>
</div>
