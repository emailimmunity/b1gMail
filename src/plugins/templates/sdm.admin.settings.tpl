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

<div class="sdm-settings">
	<h2 class="mb-4">⚙️ Einstellungen</h2>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<form method="post" action="">
		<!-- ResellerInterface API -->
		<div class="card mb-4">
			<div class="card-header">
				<h3 class="card-title">ResellerInterface.com API</h3>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<label class="form-label">API URL</label>
					<input type="text" name="resellerinterface_api_url" class="form-control" 
						   value="{$settings.resellerinterface_api_url|default:'https://resellerinterface.com/api/v1'}" 
						   placeholder="https://resellerinterface.com/api/v1">
					<small class="form-hint">Basis-URL der ResellerInterface API</small>
				</div>
				
				<div class="mb-3">
					<label class="form-label">API Key</label>
					<input type="text" name="resellerinterface_api_key" class="form-control" 
						   value="{$settings.resellerinterface_api_key|default:''}" 
						   placeholder="Ihr API-Key">
					<small class="form-hint">API-Key von ResellerInterface.com</small>
				</div>
			</div>
		</div>
		
		<!-- KeyHelp API -->
		<div class="card mb-4">
			<div class="card-header">
				<h3 class="card-title">KeyHelp API</h3>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<label class="form-label">KeyHelp URL</label>
					<input type="text" name="keyhelp_api_url" class="form-control" 
						   value="{$settings.keyhelp_api_url|default:''}" 
						   placeholder="https://keyhelp.yourdomain.com">
					<small class="form-hint">URL Ihrer KeyHelp-Installation</small>
				</div>
				
				<div class="mb-3">
					<label class="form-label">API Key</label>
					<input type="text" name="keyhelp_api_key" class="form-control" 
						   value="{$settings.keyhelp_api_key|default:''}" 
						   placeholder="Ihr KeyHelp API-Key">
					<small class="form-hint">API-Key aus KeyHelp Admin-Panel</small>
				</div>
			</div>
		</div>
		
		<!-- DynDNS Einstellungen -->
		<div class="card mb-4">
			<div class="card-header">
				<h3 class="card-title">DynDNS Einstellungen</h3>
			</div>
			<div class="card-body">
				<div class="mb-3">
					<label class="form-label">DynDNS Update URL</label>
					<input type="text" name="dyndns_update_url" class="form-control" 
						   value="{$settings.dyndns_update_url|default:'https://yourdomain.com/dyndns/update'}" 
						   placeholder="https://yourdomain.com/dyndns/update">
					<small class="form-hint">Öffentliche URL für DynDNS-Updates (wird Usern angezeigt)</small>
				</div>
				
				<div class="alert alert-info">
					<h4 class="alert-title">DynDNS Update-Endpoint</h4>
					<div class="text-muted">
						Der Update-Endpoint muss separat eingerichtet werden:<br>
						<code>/dyndns/update.php?token=XXX&ip=XXX</code>
						<br><br>
						User verwenden diese URL in ihren Routern/Clients.
					</div>
				</div>
			</div>
		</div>
		
		<div class="card-footer text-end">
			<button type="submit" name="save_settings" class="btn btn-primary">
				<i class="ti ti-device-floppy"></i> Einstellungen speichern
			</button>
		</div>
	</form>
</div>
