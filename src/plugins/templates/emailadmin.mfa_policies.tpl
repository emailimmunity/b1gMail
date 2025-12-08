<div class="contentPadding">
	<h1>🔑 MFA-Policies (Multi-Factor Authentication)</h1>
	
	<div class="infoBox">
		<p><strong>Was sind MFA-Policies?</strong></p>
		<p>Als Domain-Administrator können Sie festlegen, ob Benutzer Ihrer Domain(s) Zwei-Faktor-Authentifizierung verwenden MÜSSEN.</p>
	</div>
	
	{if $message}
	<div class="msgBox {if $messageType == 'success'}msgBoxSuccess{else}msgBoxError{/if}">
		{$message}
	</div>
	{/if}
	
	<!-- MFA Policy per Domain -->
	<h2>MFA-Anforderungen pro Domain</h2>
	
	{foreach from=$domains item=domain}
	<div class="card" style="margin-bottom: 15px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px;">
		<div style="display: flex; justify-content: space-between; align-items: center;">
			<div>
				<h3 style="margin: 0 0 5px 0;">🌐 {$domain.domain}</h3>
				<p style="margin: 0; color: #666; font-size: 14px;">
					{$domain.user_count|default:0} User
					{if $domain.mfa_policy}
						• MFA: <strong style="color: #4CAF50;">Aktiv</strong>
					{else}
						• MFA: <span style="color: #999;">Nicht aktiv</span>
					{/if}
				</p>
			</div>
			
			<div>
				<button onclick="editMFAPolicy({$domain.id})" class="btn btn-primary">
					{if $domain.mfa_policy}Bearbeiten{else}Aktivieren{/if}
				</button>
			</div>
		</div>
		
		{if $domain.mfa_policy}
		<div style="margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
			<strong>Aktuelle Policy:</strong>
			<ul style="margin: 10px 0 0 20px;">
				<li>MFA-Pflicht: <strong>{if $domain.mfa_policy.mfa_required}✅ JA{else}❌ NEIN{/if}</strong></li>
				<li>Erlaubte Methoden: 
					{foreach from=$domain.mfa_policy.mfa_methods item=method}
						<span class="badge">{$method|upper}</span>
					{/foreach}
				</li>
				<li>Grace-Period: {$domain.mfa_policy.grace_period_days|default:30} Tage</li>
				<li>Compliance: {$domain.mfa_compliance.users_compliant|default:0} / {$domain.mfa_compliance.total_users|default:0} User ({math equation="round((compliant / total) * 100, 1)" compliant=$domain.mfa_compliance.users_compliant total=$domain.mfa_compliance.total_users}%)</li>
			</ul>
		</div>
		{/if}
	</div>
	{foreachelse}
	<p style="color: #999;">Keine Domains verfügbar oder keine Berechtigung.</p>
	{/foreach}
	
	<!-- Global MFA Statistics -->
	<h2 style="margin-top: 40px;">📊 Gesamt-Statistik</h2>
	
	<table class="tableStyle">
		<thead>
			<tr>
				<th>Methode</th>
				<th>Aktive User</th>
				<th>Anteil</th>
				<th>Trend</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>📱 TOTP (Google Authenticator)</td>
				<td>{$global_mfa_stats.totp|default:0}</td>
				<td>
					{if $stats.users.total > 0}
						{math equation="round((totp / total) * 100, 1)" totp=$global_mfa_stats.totp total=$stats.users.total}%
					{else}
						0%
					{/if}
				</td>
				<td><span style="color: #4CAF50;">↗ +12%</span></td>
			</tr>
			<tr>
				<td>🔑 Passkey (WebAuthn)</td>
				<td>{$global_mfa_stats.passkey|default:0}</td>
				<td>
					{if $stats.users.total > 0}
						{math equation="round((pk / total) * 100, 1)" pk=$global_mfa_stats.passkey total=$stats.users.total}%
					{else}
						0%
					{/if}
				</td>
				<td><span style="color: #4CAF50;">↗ +8%</span></td>
			</tr>
			<tr>
				<td>📨 SMS-TAN</td>
				<td>{$global_mfa_stats.sms|default:0}</td>
				<td>
					{if $stats.users.total > 0}
						{math equation="round((sms / total) * 100, 1)" sms=$global_mfa_stats.sms total=$stats.users.total}%
					{else}
						0%
					{/if}
				</td>
				<td><span style="color: #FF9800;">→ ±0%</span></td>
			</tr>
		</tbody>
	</table>
	
	<!-- Quick Actions -->
	<div style="margin-top: 30px;">
		<h3>🚀 Schnellaktionen</h3>
		
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
			<a href="{$pageURL}&action=force_password_migration&" 
			   class="action-card"
			   onclick="return confirm('Alle User per E-Mail zur Passwort-Migration auffordern?');">
				<div style="padding: 20px; background: #e3f2fd; border-radius: 5px; text-align: center; text-decoration: none; color: inherit;">
					<div style="font-size: 36px;">📧</div>
					<div style="font-weight: bold; margin-top: 10px;">Migrationsaufforderung versenden</div>
					<div style="font-size: 12px; color: #666; margin-top: 5px;">An alle MD5-User</div>
				</div>
			</a>
			
			<a href="{$pageURL}&action=mfa_report&" class="action-card">
				<div style="padding: 20px; background: #f3e5f5; border-radius: 5px; text-align: center; text-decoration: none; color: inherit;">
					<div style="font-size: 36px;">📊</div>
					<div style="font-weight: bold; margin-top: 10px;">Detaillierter MFA-Report</div>
					<div style="font-size: 12px; color: #666; margin-top: 5px;">Exportieren als CSV/PDF</div>
				</div>
			</a>
			
			<a href="{$pageURL}&action=security_audit&" class="action-card">
				<div style="padding: 20px; background: #fff3e0; border-radius: 5px; text-align: center; text-decoration: none; color: inherit;">
					<div style="font-size: 36px;">🔍</div>
					<div style="font-weight: bold; margin-top: 10px;">Security-Audit starten</div>
					<div style="font-size: 12px; color: #666; margin-top: 5px;">Schwachstellen finden</div>
				</div>
			</a>
		</div>
	</div>
</div>

<script>
function editMFAPolicy(domainId) {
	// TODO: Modal mit MFA-Policy-Editor
	alert('MFA-Policy-Editor wird geöffnet für Domain #' + domainId);
}
</script>

<style>
.action-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.2);
	transition: all 0.3s;
}

.badge {
	display: inline-block;
	padding: 3px 8px;
	background: #2196F3;
	color: white;
	border-radius: 3px;
	font-size: 11px;
	margin-right: 5px;
}
</style>

