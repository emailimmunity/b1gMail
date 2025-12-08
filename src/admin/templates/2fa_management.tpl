<div class="container-fluid">
	<h1 class="mb-4">🔐 2FA User Management</h1>

	{if $message != ''}
		<div class="alert alert-success alert-dismissible fade show">
			{$message}
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	{/if}

	{if $error != ''}
		<div class="alert alert-danger alert-dismissible fade show">
			{$error}
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	{/if}

	<!-- Statistics -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card text-white bg-primary">
				<div class="card-body">
					<h5 class="card-title">👥 Total Users</h5>
					<h2>{$stats.total_users}</h2>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-white bg-success">
				<div class="card-body">
					<h5 class="card-title">🔐 MFA Enabled</h5>
					<h2>{$stats.mfa_users} <small>({$stats.mfa_percentage}%)</small></h2>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card text-white bg-info">
				<div class="card-body">
					<h5 class="card-title">📱 App-Passwords</h5>
					<h2>{$stats.app_passwords}</h2>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card text-white bg-warning">
				<div class="card-body">
					<h5 class="card-title">🔑 WebAuthn</h5>
					<h2>{$stats.webauthn}</h2>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card text-white bg-secondary">
				<div class="card-body">
					<h5 class="card-title">🔐 Yubikey</h5>
					<h2>{$stats.yubikey}</h2>
				</div>
			</div>
		</div>
	</div>

	<!-- Search & Filter -->
	<div class="card mb-4">
		<div class="card-body">
			<form method="get" action="2fa_management.php" class="row g-3">
				<input type="hidden" name="sid" value="{$sid}">
				
				<div class="col-md-4">
					<label class="form-label">🔍 Search</label>
					<input type="text" name="search" class="form-control" value="{$search}" placeholder="Email, Name...">
				</div>
				
				<div class="col-md-3">
					<label class="form-label">Filter</label>
					<select name="filter" class="form-select">
						<option value="all" {if $filter == 'all'}selected{/if}>All Users</option>
						<option value="mfa_enabled" {if $filter == 'mfa_enabled'}selected{/if}>MFA Enabled</option>
						<option value="mfa_disabled" {if $filter == 'mfa_disabled'}selected{/if}>MFA Disabled</option>
						<option value="has_app_passwords" {if $filter == 'has_app_passwords'}selected{/if}>Has App-Passwords</option>
						<option value="has_webauthn" {if $filter == 'has_webauthn'}selected{/if}>Has WebAuthn</option>
						<option value="has_yubikey" {if $filter == 'has_yubikey'}selected{/if}>Has Yubikey</option>
					</select>
				</div>
				
				<div class="col-md-2">
					<label class="form-label">Per Page</label>
					<select name="per_page" class="form-select">
						<option value="25" {if $per_page == 25}selected{/if}>25</option>
						<option value="50" {if $per_page == 50}selected{/if}>50</option>
						<option value="100" {if $per_page == 100}selected{/if}>100</option>
					</select>
				</div>
				
				<div class="col-md-3">
					<label class="form-label">&nbsp;</label>
					<button type="submit" class="btn btn-primary w-100">Apply</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Users Table -->
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">Users ({$total_users} total)</h5>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-striped table-hover mb-0">
					<thead>
						<tr>
							<th>ID</th>
							<th>Email</th>
							<th>Name</th>
							<th>2FA Methods</th>
							<th>Last Login</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$users item=user}
						<tr>
							<td>{$user.id}</td>
							<td><strong>{$user.email}</strong></td>
							<td>{$user.vorname} {$user.nachname}</td>
							<td>
								{if $user.has_2fa}
									<span class="badge bg-success">✓ Enabled</span>
									{foreach from=$user.methods item=method}
										<span class="badge bg-info">{$method}</span>
									{/foreach}
								{else}
									<span class="badge bg-secondary">✗ No 2FA</span>
								{/if}
							</td>
							<td>
								{if $user.lastlogin > 0}
									{$user.lastlogin|date_format:"%d.%m.%Y %H:%M"}
								{else}
									<em>Never</em>
								{/if}
							</td>
							<td>
								{if $user.gesperrt == 1}
									<span class="badge bg-danger">🔒 Locked</span>
								{else}
									<span class="badge bg-success">✓ Active</span>
								{/if}
							</td>
							<td>
								{if $user.has_2fa}
									<a href="2fa_management.php?action=reset_2fa&userID={$user.id}&sid={$sid}" 
									   class="btn btn-sm btn-danger" 
									   onclick="return confirm('2FA für {$user.email} wirklich zurücksetzen?')">
										🔄 Reset 2FA
									</a>
								{else}
									<span class="text-muted small">No 2FA to reset</span>
								{/if}
							</td>
						</tr>
						{foreachelse}
						<tr>
							<td colspan="7" class="text-center text-muted py-4">
								No users found
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Pagination -->
		{if $total_pages > 1}
		<div class="card-footer">
			<nav>
				<ul class="pagination pagination-sm mb-0 justify-content-center">
					{if $current_page > 1}
						<li class="page-item">
							<a class="page-link" href="2fa_management.php?page={$current_page-1}&search={$search}&filter={$filter}&per_page={$per_page}&sid={$sid}">Previous</a>
						</li>
					{/if}
					
					{foreach from=$pagination item=p}
						<li class="page-item {if $p.current}active{/if}">
							<a class="page-link" href="2fa_management.php?page={$p.page}&search={$search}&filter={$filter}&per_page={$per_page}&sid={$sid}">{$p.page}</a>
						</li>
					{/foreach}
					
					{if $current_page < $total_pages}
						<li class="page-item">
							<a class="page-link" href="2fa_management.php?page={$current_page+1}&search={$search}&filter={$filter}&per_page={$per_page}&sid={$sid}">Next</a>
						</li>
					{/if}
				</ul>
			</nav>
		</div>
		{/if}
	</div>
</div>
