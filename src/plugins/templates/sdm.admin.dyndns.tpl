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

<div class="sdm-admin-dyndns">
	<h2 class="mb-4">🔄 DynDNS Übersicht</h2>
	
	<!-- Suche & Filter -->
	<div class="card mb-3">
		<div class="card-body">
			<form method="get" action="{$baseLink}&action=dyndns">
				<input type="hidden" name="p" value="plugins">
				<input type="hidden" name="plugin" value="{$plugin_name}">
				<input type="hidden" name="action" value="dyndns">
				<input type="hidden" name="sid" value="{$sid}">
				
				<div class="row">
					<div class="col-md-4">
						<label class="form-label">Suche (Domain oder Email)</label>
						<input type="text" name="search" class="form-control" placeholder="z.B. test.gtin.org" value="{$search}">
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
						<a href="{$baseLink}&action=dyndns&per_page={$perPage}" class="btn btn-secondary w-100">✖ Zurücksetzen</a>
					</div>
					{/if}
				</div>
			</form>
		</div>
	</div>
	
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">DynDNS-Subdomains ({$totalCount} gesamt{if $search}, {$dyndns_subdomains|@count} gefunden{/if})</h3>
		</div>
		<div class="card-body p-0">
			{if $dyndns_subdomains}
			<div class="table-responsive">
				<table class="table table-vcenter card-table table-hover">
					<thead>
						<tr>
							<th>Domain</th>
							<th>Account (User)</th>
							<th>Aktuelle IP</th>
							<th>Updates</th>
							<th>Letztes Update</th>
							<th>Token</th>
						</tr>
					</thead>
					<tbody>
						{foreach $dyndns_subdomains as $sub}
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
								{if $sub.dyndns_current_ip}
								<code style="font-size: 11px;">{$sub.dyndns_current_ip}</code>
								{else}
								<span class="text-muted">Noch kein Update</span>
								{/if}
							</td>
							<td>
								{if $sub.update_count > 0}
								<span class="badge bg-info">{$sub.update_count}x</span>
								{else}
								<span class="text-muted">0</span>
								{/if}
							</td>
							<td>
								{if $sub.dyndns_last_update}
								<small>{$sub.dyndns_last_update|date_format:"%d.%m.%Y %H:%M"|default:'-'}</small>
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td>
								<small class="text-muted" style="font-size: 10px;">{$sub.dyndns_token|truncate:12:"..."}</small>
							</td>
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
						<a class="page-link" href="{$baseLink}&action=dyndns&page={$prevPage}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">← Zurück</a>
					</li>
					{/if}
					
					{if $showFirstPage}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=dyndns&page=1&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">1</a>
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
							<a class="page-link" href="{$baseLink}&action=dyndns&page={$pageNum}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">{$pageNum}</a>
						</li>
						{/if}
					{/foreach}
					
					{if $showEndDots}
					<li class="page-item disabled"><span class="page-link">...</span></li>
					{/if}
					
					{if $showLastPage}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=dyndns&page={$totalPages}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">{$totalPages}</a>
					</li>
					{/if}
					
					{if $currentPage < $totalPages}
					<li class="page-item">
						<a class="page-link" href="{$baseLink}&action=dyndns&page={$nextPage}&per_page={$perPage}{if $search}&search={$search|escape:'url'}{/if}">Weiter →</a>
					</li>
					{/if}
				</ul>
			</div>
			{/if}
			
			{else}
			<div class="empty p-4">
				<p class="empty-title h3">Keine DynDNS-Subdomains {if $search}gefunden{else}vorhanden{/if}</p>
				{if $search}
				<p class="empty-subtitle text-muted">Versuchen Sie einen anderen Suchbegriff.</p>
				{else}
				<p class="empty-subtitle text-muted">DynDNS muss für Subdomains aktiviert werden.</p>
				{/if}
			</div>
			{/if}
		</div>
	</div>
</div>
