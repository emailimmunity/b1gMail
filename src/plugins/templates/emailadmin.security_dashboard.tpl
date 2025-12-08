<div class="contentPadding">
	<h1>🔐 Security Dashboard</h1>
	
	<!-- Password Migration Status -->
	<div class="card" style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
		<h2 style="margin-top: 0;">📊 Password Migration Status</h2>
		
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
			<div style="text-align: center; padding: 20px; background: #e3f2fd; border-radius: 5px;">
				<div style="font-size: 36px; font-weight: bold; color: #2196F3;">{$stats.users.total|default:0}</div>
				<div style="color: #666;">Total Users</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #c8e6c9; border-radius: 5px;">
				<div style="font-size: 36px; font-weight: bold; color: #4CAF50;">{$stats.users.bcrypt_count|default:0}</div>
				<div style="color: #666;">bcrypt (sicher)</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #ffebee; border-radius: 5px;">
				<div style="font-size: 36px; font-weight: bold; color: #f44336;">{$stats.users.md5_count|default:0}</div>
				<div style="color: #666;">MD5 (unsicher)</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #fff3e0; border-radius: 5px;">
				<div style="font-size: 36px; font-weight: bold; color: #FF9800;">
					{if $stats.users.total > 0}
						{math equation="round((bcrypt / total) * 100, 1)" bcrypt=$stats.users.bcrypt_count total=$stats.users.total}%
					{else}
						0%
					{/if}
				</div>
				<div style="color: #666;">Migration Rate</div>
			</div>
		</div>
		
		<!-- Progress Bar -->
		<div style="background: #e0e0e0; height: 30px; border-radius: 15px; overflow: hidden; margin-bottom: 10px;">
			<div style="background: linear-gradient(90deg, #4CAF50, #8BC34A); height: 100%; width: {if $stats.users.total > 0}{math equation="(bcrypt / total) * 100" bcrypt=$stats.users.bcrypt_count total=$stats.users.total}{else}0{/if}%; transition: width 0.5s;"></div>
		</div>
		
		<p style="text-align: center; color: #666; font-size: 14px;">
			{$stats.users.bcrypt_count|default:0} von {$stats.users.total|default:0} Usern auf bcrypt migriert
		</p>
	</div>
	
	<!-- Credentials Encryption Status -->
	<div class="card" style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
		<h2 style="margin-top: 0;">🔒 Credentials Encryption Status</h2>
		
		<table class="tableStyle">
			<thead>
				<tr>
					<th>Protokoll</th>
					<th>Total</th>
					<th>Verschlüsselt</th>
					<th>Noch Plaintext</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$stats.credentials key=protocol item=data}
				<tr>
					<td><strong>{$protocol|upper}</strong></td>
					<td>{$data.total|default:0}</td>
					<td style="color: #4CAF50; font-weight: bold;">{$data.encrypted_count|default:0}</td>
					<td style="color: #f44336;">{math equation="total - encrypted" total=$data.total encrypted=$data.encrypted_count}</td>
					<td>
						{if $data.encrypted_count == $data.total}
							<span style="color: #4CAF50;">✅ Komplett</span>
						{else if $data.encrypted_count > 0}
							<span style="color: #FF9800;">⏳ In Arbeit</span>
						{else}
							<span style="color: #f44336;">❌ Nicht gestartet</span>
						{/if}
					</td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="5" style="text-align: center; color: #999;">
						Keine Credentials-Daten verfügbar
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<!-- MFA Adoption -->
	<div class="card" style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
		<h2 style="margin-top: 0;">🔑 Multi-Factor Authentication (MFA)</h2>
		
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
			<div style="text-align: center; padding: 20px; background: #e8f5e9; border-radius: 5px;">
				<div style="font-size: 28px; font-weight: bold; color: #4CAF50;">{$mfa_stats.totp_users|default:0}</div>
				<div style="color: #666;">TOTP (Google Auth)</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #e3f2fd; border-radius: 5px;">
				<div style="font-size: 28px; font-weight: bold; color: #2196F3;">{$mfa_stats.passkey_users|default:0}</div>
				<div style="color: #666;">Passkey (WebAuthn)</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #fce4ec; border-radius: 5px;">
				<div style="font-size: 28px; font-weight: bold; color: #E91E63;">{$mfa_stats.sms_users|default:0}</div>
				<div style="color: #666;">SMS-TAN</div>
			</div>
			
			<div style="text-align: center; padding: 20px; background: #fff9c4; border-radius: 5px;">
				<div style="font-size: 28px; font-weight: bold; color: #FFC107;">
					{if $stats.users.total > 0}
						{math equation="round((mfa / total) * 100, 1)" mfa=$mfa_stats.total_mfa_users total=$stats.users.total}%
					{else}
						0%
					{/if}
				</div>
				<div style="color: #666;">MFA Adoption</div>
			</div>
		</div>
	</div>
	
	<!-- Security Score -->
	<div class="card" style="padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
		<h2 style="margin-top: 0;">⭐ Gesamt-Security-Score</h2>
		
		{assign var="security_score" value=0}
		
		{* Calculate score *}
		{if $stats.users.total > 0}
			{* Password migration: 40 points *}
			{math equation="(bcrypt / total) * 40" bcrypt=$stats.users.bcrypt_count total=$stats.users.total assign="pw_score"}
			
			{* MFA adoption: 30 points *}
			{math equation="(mfa / total) * 30" mfa=$mfa_stats.total_mfa_users total=$stats.users.total assign="mfa_score"}
			
			{* Credentials encryption: 30 points *}
			{assign var="cred_score" value=30}
			{if $stats.credentials.pop3.encrypted_count < $stats.credentials.pop3.total}
				{assign var="cred_score" value=0}
			{/if}
			
			{math equation="pw + mfa + cred" pw=$pw_score mfa=$mfa_score cred=$cred_score assign="security_score"}
		{/if}
		
		<div style="text-align: center;">
			<div style="font-size: 72px; font-weight: bold; 
			            color: {if $security_score >= 80}#4CAF50{else if $security_score >= 50}#FF9800{else}#f44336{/if};">
				{$security_score|round:0}/100
			</div>
			
			<div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
				{if $security_score >= 90}
					<strong style="color: #4CAF50;">✅ EXZELLENT</strong>
					<p>Ihre Sicherheit ist auf höchstem Niveau!</p>
				{else if $security_score >= 70}
					<strong style="color: #8BC34A;">✅ SEHR GUT</strong>
					<p>Ihre Sicherheit ist gut, einige Verbesserungen möglich.</p>
				{else if $security_score >= 50}
					<strong style="color: #FF9800;">⚠️ MITTEL</strong>
					<p>Verbesserungen empfohlen für optimale Sicherheit.</p>
				{else}
					<strong style="color: #f44336;">🔴 NIEDRIG</strong>
					<p>Dringende Sicherheits-Maßnahmen erforderlich!</p>
				{/if}
			</div>
		</div>
		
		<div style="margin-top: 30px;">
			<h3>Empfehlungen:</h3>
			<ul>
				{if $stats.users.md5_count > 0}
				<li style="color: #f44336;">
					<strong>Kritisch:</strong> {$stats.users.md5_count} User noch mit unsicherem MD5-Passwort
					→ User auffordern, sich einzuloggen (automatische Migration)
				</li>
				{/if}
				
				{if $mfa_stats.total_mfa_users / $stats.users.total < 0.3}
				<li style="color: #FF9800;">
					<strong>Empfohlen:</strong> MFA-Adoption noch unter 30%
					→ User über Vorteile von 2FA informieren
				</li>
				{/if}
				
				{if !$stats.credentials.pop3.encrypted_count}
				<li style="color: #f44336;">
					<strong>Kritisch:</strong> POP3-Passwörter noch unverschlüsselt
					→ Migrations-Script ausführen
				</li>
				{/if}
			</ul>
		</div>
	</div>
</div>

