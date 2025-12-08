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

<div class="sdm-admin-subdomains">
	<h2 class="mb-4">📋 Alle Subdomains</h2>
	
	<!-- Suche & Filter -->
	<div class="card mb-3">
		<div class="card-body">
			<form method="get" action="{$baseLink}&action=subdomains">
				<input type="hidden" name="p" value="plugins">
				<input type="hidden" name="plugin" value="{$plugin_name}">
				<input type="hidden" name="action" value="subdomains">
				<input type="hidden" name="sid" value="{$sid}">
				
				<div class="row">
					<div class="col-md-4">
						<label class="form-label">Suche (Subdomain oder Email)</label>
						<input type="text" name="search" class="form-control" placeholder="z.B. test.gtin.org oder user@domain.com" value="{$search}">
					</div>
					<div class="col-md-2">
						<label class="form-label">Pro Seite</label>
						<select name="per_page" class="form-select">
							<option value="10" {if $perPage == 10}selected{/if}>10</option>
							<option value="25" {if $perPage == 25}selected{/if}>25</option>
							<option value="50" {if $perPage == 50}selected{/if}>50</option>
							<option value="100" {if $perPage == 100}selected{/if}>100</option>
							<option value="200" {if $perPage == 200}selected{/if}>200</option>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">&nbsp;</label>
						<button type="submit" class="btn btn-primary w-100">🔍 Suchen</button>
					</div>
					{if $search}
					<div class="col-md-2">
						<label class="form-label">&nbsp;</label>
						<a href="{$baseLink}&action=subdomains&per_page={$perPage}" class="btn btn-secondary w-100">✖ Zurücksetzen</a>
					</div>
					{/if}
				</div>
			</form>
		</div>
	</div>
	
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Subdomains ({$totalCount} gesamt{if $search}, {$subdomains|@count} gefunden{/if})</h3>
		</div>
		<div class="card-body p-0">
			{if $subdomains}
			<div class="table-responsive">
				<table class="table table-vcenter card-table table-hover">
					<thead>
						<tr>
							<th>Subdomain</th>
							<th>Account (User)</th>
							<th>Status</th>
							<th>Emails</th>
							<th>DynDNS IP</th>
							<th>Erstellt</th>
						</tr>
					</thead>
					<tbody>
						{foreach $subdomains as $sub}
						<tr>
							<td><strong>{$sub.full_domain}</strong></td>
							<td>
								{if $sub.email}
								<a href="mailto:{$sub.email}">{$sub.email}</a>
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td>
								{if $sub.status == 'active'}
								<span class="badge bg-success">✓ Aktiv</span>
								{elseif $sub.status == 'suspended'}
								<span class="badge bg-warning">⏸ Gesperrt</span>
								{elseif $sub.status == 'deleted'}
								<span class="badge bg-danger">🗑 Gelöscht</span>
								{else}
								<span class="badge bg-secondary">{$sub.status}</span>
								{/if}
							</td>
							<td>
								{if $sub.email_count > 0}
								<span class="badge bg-info">{$sub.email_count} Email{if $sub.email_count != 1}s{/if}</span>
								{else}
								<span class="text-muted">0</span>
								{/if}
							</td>
							<td>
								{if $sub.dyndns_enabled}
								<code>{$sub.dyndns_current_ip|default:'-'}</code>
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td><small>{$sub.created_at|date_format:"%d.%m.%Y %H:%M"}</small></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			
			<!-- Pagination -->
			{if $totalPages > 1}
			<div class="card-footer d-flex align-items-center">
				<p class="m-0 text-muted">Seite {$currentPage} von {$totalPages} ({$totalCount} Einträge)</p>
				<ul class="pagination m-0 ms-auto">
					{if $currentPage > 1}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=subdomains&page={$prevPage}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">← Zurück</a>
					</li>
					{/if}
					
					{if $showFirstPage}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=subdomains&page=1&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">1</a>
					</li>
					{/if}
					
					{if $showStartDots}
					<li class="page-item disabled"><span class="page-link">...</span></li>
					{/if}
					
					{foreach $paginationPages as $pageNum}
						{if $pageNum == $currentPage}
						<li class="page-item active"><span class="page-link">{$pageNum}</span></li>
						{else}
						<li class="page-item">
							<a class="page-link" href="{$baseLink}&action=subdomains&page={$pageNum}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">{$pageNum}</a>
						</li>
						{/if}
					{/foreach}
					
					{if $showEndDots}
					<li class="page-item disabled"><span class="page-link">...</span></li>
					{/if}
					
					{if $showLastPage}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=subdomains&page={$totalPages}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">{$totalPages}</a>
					</li>
					{/if}
					
					{if $currentPage < $totalPages}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=subdomains&page={$nextPage}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">Weiter →</a>
					</li>
					{/if}
				</ul>
			</div>
			{/if}
			
			{else}
			<div class="empty p-4">
				<p class="empty-title h3">Keine Subdomains {if $search}gefunden{else}vorhanden{/if}</p>
				{if $search}
				<p class="empty-subtitle text-muted">Versuchen Sie einen anderen Suchbegriff.</p>
				{/if}
			</div>
			{/if}
		</div>
	</div>
</div>
