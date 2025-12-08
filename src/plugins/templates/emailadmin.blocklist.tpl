<div class="emailadmin-blocklist">
	<h2 class="mb-4">🚫 Email Blocklist</h2>
	
	<div class="alert alert-warning">
		<i class="ti ti-alert-triangle"></i>
		<strong>Wichtig:</strong> Email-Adressen auf dieser Liste können sich NICHT registrieren oder einloggen.
		Z.B. Spam-Accounts, Missbrauch, etc.
	</div>
	
	<!-- Nachrichten -->
	{if $message}
	<div class="alert alert-{if $messageType == 'success'}success{else}danger{/if} alert-dismissible">
		{$message}
		<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
	</div>
	{/if}
	
	<!-- Email zur Blocklist hinzufügen -->
	<div class="card mb-4">
		<div class="card-header">
			<h3 class="card-title">Email blockieren</h3>
		</div>
		<div class="card-body">
			<form method="post" action="{$pageURL}&action=blocklist">
				<div class="row">
					<div class="col-md-6">
						<div class="mb-3">
							<label class="form-label required">Email-Adresse</label>
							<input type="email" name="email" class="form-control" placeholder="z.B. spam@example.com" required>
							<small class="form-hint">Gültige Email-Adresse eingeben</small>
						</div>
					</div>
					<div class="col-md-3">
						<div class="mb-3">
							<label class="form-label">Grund (optional)</label>
							<input type="text" name="reason" class="form-control" placeholder="z.B. Spam, Missbrauch">
						</div>
					</div>
					<div class="col-md-3">
						<label class="form-label">&nbsp;</label>
						<button type="submit" name="add_block" class="btn btn-danger w-100">
							<i class="ti ti-ban"></i> Blockieren
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<!-- Blocklist -->
	<div class="card">
		<div class="card-header">
			<h3 class="card-title">Blockierte Email-Adressen ({$blocklist|@count})</h3>
		</div>
		<div class="card-body p-0">
			{if $blocklist}
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Email-Adresse</th>
							<th>Grund</th>
							<th class="w-1"></th>
						</tr>
					</thead>
					<tbody>
						{foreach $blocklist as $block}
						<tr>
							<td><span class="text-muted">{$block.id}</span></td>
							<td>
								<code class="text-danger">{$block.adress}</code>
								<br><small class="text-muted">Kann sich nicht registrieren/einloggen</small>
							</td>
							<td>
								{if $block.action}
								<span class="badge bg-gray">{$block.action}</span>
								{else}
								<span class="text-muted">-</span>
								{/if}
							</td>
							<td>
								<a href="{$pageURL}&action=blocklist&delete={$block.id}" 
								   onclick="return confirm('Email \"{$block.adress}\" von Blocklist entfernen?')" 
								   class="btn btn-sm btn-success">
									<i class="ti ti-trash"></i> Entfernen
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
					<i class="ti ti-ban" style="font-size: 3rem;"></i>
				</div>
				<p class="empty-title">Keine blockierten Emails</p>
				<p class="empty-subtitle text-muted">
					Fügen Sie oben eine Email-Adresse zur Blocklist hinzu
				</p>
			</div>
			{/if}
		</div>
	</div>
	
	<!-- Hinweise -->
	<div class="alert alert-info mt-4">
		<h4 class="alert-title">💡 Verwendung</h4>
		<p class="mb-2">Diese Email-Blocklist schützt Ihr System vor unerwünschten Benutzern:</p>
		<ul class="mb-0">
			<li><strong>Spam-Accounts:</strong> Blockieren Sie bekannte Spam-Email-Adressen</li>
			<li><strong>Missbrauch:</strong> Verhindern Sie erneute Registrierung von gesperrten Usern</li>
			<li><strong>Sicherheit:</strong> Schützen Sie vor bekannten böswilligen Accounts</li>
			<li><strong>Compliance:</strong> Blockieren Sie Emails nach rechtlichen Vorgaben</li>
		</ul>
	</div>
</div>
