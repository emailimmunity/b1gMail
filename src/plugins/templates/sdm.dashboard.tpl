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

<div class="sdm-dashboard">
	<h2>🚀 SubDomainManager Dashboard</h2>
	
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h3 class="mb-0">{$stats.subdomains|default:0}</h3>
					<div class="text-muted">Subdomains</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h3 class="mb-0">{$stats.grants|default:0}</h3>
					<div class="text-muted">Freigaben</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="alert alert-success">
		<h4 class="alert-title">✅ Plugin läuft!</h4>
		<div class="text-muted">
			<strong>{$plugin_display_name|default:"SubDomainManager"}</strong> Version {$plugin_version} ist aktiv.
		</div>
	</div>
	
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Status</h3>
		</div>
		<div class="card-body">
			<p>✅ Datenbank: 8 Tabellen</p>
			<p>✅ Admin-Panel: 6 Tabs</p>
			<p>✅ User-Panel: Funktional</p>
			<p>⏳ DNS-Integration: In Arbeit</p>
			<p>⏳ DynDNS-Service: Geplant</p>
		</div>
	</div>
</div>
