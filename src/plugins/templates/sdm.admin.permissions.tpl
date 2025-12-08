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

<div class="sdm-permissions">
	<h2>🔐 Permissions Manager</h2>
	
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert"></a>
	</div>
	{/if}
	
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Rechte pro Gruppe verwalten</h3>
		</div>
		<div class="card-body">
			<form method="get" action="" class="mb-4">
				<input type="hidden" name="p" value="plugins">
				<input type="hidden" name="plugin" value="SubDomainManagerPlugin">
				<input type="hidden" name="action" value="permissions">
				<input type="hidden" name="sid" value="{$sid}">
				
				<div class="row">
					<div class="col-md-6">
						<label class="form-label">Gruppe auswählen</label>
						<select name="group_id" class="form-select" onchange="this.form.submit()">
							<option value="">-- Gruppe wählen --</option>
							{foreach $groups as $group}
							<option value="{$group.id}" {if $selected_group_id == $group.id}selected{/if}>
								{$group.gruppe}
							</option>
							{/foreach}
						</select>
					</div>
				</div>
			</form>
			
			{if $selected_group_id}
			<form method="post" action="">
				<input type="hidden" name="group_id" value="{$selected_group_id}">
				
				<table class="table table-vcenter">
					<thead>
						<tr>
							<th>Permission</th>
							<th>Beschreibung</th>
							<th class="w-1">Aktiviert</th>
						</tr>
					</thead>
					<tbody>
						{foreach $permissions as $perm}
						<tr>
							<td><strong>{$perm.permission_name}</strong></td>
							<td>{$perm.permission_desc}</td>
							<td>
								<label class="form-check form-switch">
									<input type="checkbox" 
										   name="permissions[{$perm.id}]" 
										   value="1"
										   class="form-check-input"
										   {if isset($currentPerms[$perm.id]) && $currentPerms[$perm.id]}checked{/if}>
								</label>
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
				
				<div class="card-footer text-end">
					<button type="submit" name="save_permissions" class="btn btn-primary">
						<i class="ti ti-device-floppy"></i> Speichern
					</button>
				</div>
			</form>
			{else}
			<div class="alert alert-info">
				Bitte wählen Sie oben eine Gruppe aus.
			</div>
			{/if}
		</div>
	</div>
	
	<div class="card mt-4">
		<div class="card-header">
			<h3 class="card-title">Verfügbare Permissions</h3>
		</div>
		<div class="card-body">
			<table class="table">
				<thead>
					<tr>
						<th>Key</th>
						<th>Name</th>
						<th>Beschreibung</th>
						<th>Default</th>
					</tr>
				</thead>
				<tbody>
					{foreach $permissions as $perm}
					<tr>
						<td><code>{$perm.permission_key}</code></td>
						<td>{$perm.permission_name}</td>
						<td>{$perm.permission_desc}</td>
						<td>{if $perm.is_default}<span class="badge bg-success">Ja</span>{else}<span class="badge bg-secondary">Nein</span>{/if}</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
