<div class="contentPadding">
	<h1>👥 User-Verwaltung (Erweitert)</h1>
	
	{if $message}
	<div class="msgBox {if $messageType == 'success'}msgBoxSuccess{else}msgBoxError{/if}">
		{$message}
	</div>
	{/if}
	
	<!-- User-Liste mit erweiterten Aktionen -->
	<table class="tableStyle">
		<thead>
			<tr>
				<th>ID</th>
				<th>E-Mail</th>
				<th>Name</th>
				<th>Gruppe</th>
				<th>MFA-Status</th>
				<th>Passwort</th>
				<th>Aktionen</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$users item=user}
			<tr>
				<td>{$user.id}</td>
				<td>{$user.email}</td>
				<td>{$user.vorname} {$user.nachname}</td>
				<td>{$user.group_names|default:'Keine Gruppe'}</td>
				<td>
					{if isset($user.mfa_status) && $user.mfa_status.any_enabled}
						<span style="color: #4CAF50; font-weight: bold;">✅ Aktiv</span>
						<div style="font-size: 11px; color: #666;">
							{if isset($user.mfa_status.totp) && $user.mfa_status.totp.enabled}
								📱 TOTP
							{/if}
							{if isset($user.mfa_status.passkey) && $user.mfa_status.passkey.enabled}
								🔑 Passkey ({$user.mfa_status.passkey.devices|count})
							{/if}
						</div>
					{else}
						<span style="color: #999;">❌ Keine</span>
					{/if}
				</td>
				<td>
					{if $user.password_version == 2}
						<span style="color: #4CAF50;">✅ bcrypt</span>
					{else if $user.password_version == 1}
						<span style="color: #f44336;">⚠️ MD5</span>
					{else}
						<span style="color: #999;">?</span>
					{/if}
				</td>
				<td>
					<!-- Dropdown-Menü -->
					<div class="dropdown">
						<button class="btn btn-sm btn-primary">⚙️ Aktionen ▼</button>
						<div class="dropdown-content">
							
							<!-- Password Reset (ALLE Admins) -->
							{if $can_reset_password}
							<a href="javascript:void(0)" onclick="resetPassword({$user.id}, '{$user.email|escape}')">
								🔑 Passwort zurücksetzen
							</a>
							{/if}
							
							<!-- MFA Löschen (ALLE Admins) -->
							{if $can_reset_mfa && isset($user.mfa_status) && $user.mfa_status.any_enabled}
							<a href="javascript:void(0)" onclick="resetMFA({$user.id}, '{$user.email|escape}')" style="color: #FF9800;">
								🔐 MFA deaktivieren (Notfall)
							</a>
							{/if}
							
							<!-- Impersonation (NUR Superadmin!) -->
							{if $can_impersonate}
							<a href="{$pageURL}&action=impersonate&userid={$user.id}&" style="color: #2196F3;">
								👤 Als User einloggen
							</a>
							{else}
							<span style="color: #ccc; padding: 8px 12px; display: block; cursor: not-allowed;" title="Nur Superadmin">
								👤 Als User einloggen (Keine Berechtigung)
							</span>
							{/if}
							
							<!-- MFA-Details anzeigen -->
							{if isset($user.mfa_status) && $user.mfa_status.any_enabled}
							<a href="javascript:void(0)" onclick="showMFADetails({$user.id})">
								📊 MFA-Details anzeigen
							</a>
							{/if}
							
							<!-- Benutzer bearbeiten -->
							<a href="../admin/users.php?action=edit&id={$user.id}&sid={$smarty.session.sid}">
								✏️ Benutzer bearbeiten
							</a>
						</div>
					</div>
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="7" style="text-align: center; color: #999;">
					Keine User gefunden oder keine Berechtigung.
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	
	<!-- Legende -->
	<div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
		<strong>Berechtigungen:</strong>
		<ul style="margin: 10px 0; padding-left: 20px;">
			<li><strong>Superadmin:</strong> Passwort reset, MFA löschen, Impersonation ✅</li>
			<li><strong>Reseller/Domain-Admin:</strong> Passwort reset, MFA löschen (nur IHRE User) ✅</li>
			<li><strong>Subdomain-Admin:</strong> Passwort reset, MFA löschen (nur IHRE User) ✅</li>
			<li style="color: #f44336;"><strong>KEINE Impersonation</strong> für Reseller/Domain-Admins! (Nur Superadmin)</li>
		</ul>
	</div>
</div>

<!-- Modals -->
<div id="resetPasswordModal" style="display: none;">
	<div class="modal-backdrop" onclick="closeModal()"></div>
	<div class="modal-dialog">
		<div class="modal-content">
			<h2>🔑 Passwort zurücksetzen</h2>
			<p>Für User: <strong id="resetUserEmail"></strong></p>
			
			<form id="resetPasswordForm" method="post" action="{$pageURL}&action=reset_password&">
				<input type="hidden" name="target_userid" id="resetUserID" value="" />
				
				<div class="formField">
					<label>
						<input type="radio" name="reset_type" value="random" checked />
						Zufälliges Passwort generieren (empfohlen)
					</label>
				</div>
				
				<div class="formField">
					<label>
						<input type="radio" name="reset_type" value="manual" />
						Manuelles Passwort:
					</label>
					<input type="text" name="new_password" id="manualPassword" placeholder="Mindestens 8 Zeichen" disabled />
				</div>
				
				<div class="formField">
					<label>
						<input type="checkbox" name="force_change" checked />
						User muss Passwort beim nächsten Login ändern
					</label>
				</div>
				
				<div class="formField">
					<label>Grund (optional):</label>
					<textarea name="reason" rows="2" placeholder="z.B.: User hat Passwort vergessen"></textarea>
				</div>
				
				<div class="buttons">
					<button type="submit" class="btn btn-primary">✅ Passwort zurücksetzen</button>
					<button type="button" class="btn btn-secondary" onclick="closeModal()">Abbrechen</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="resetMFAModal" style="display: none;">
	<div class="modal-backdrop" onclick="closeModal()"></div>
	<div class="modal-dialog">
		<div class="modal-content">
			<h2>🔐 MFA deaktivieren (Notfall)</h2>
			<p>Für User: <strong id="mfaUserEmail"></strong></p>
			
			<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin: 15px 0;">
				<strong>⚠️ WARNUNG:</strong>
				<p style="margin: 5px 0 0 0;">
					Dies deaktiviert ALLE MFA-Methoden des Users:
				</p>
				<ul style="margin: 5px 0 0 20px;">
					<li>Google Authenticator (TOTP)</li>
					<li>Passkey-Geräte</li>
					<li>Vertrauenswürdige Geräte</li>
				</ul>
				<p style="margin: 10px 0 0 0;">
					User kann sich danach nur noch mit Passwort einloggen!
				</p>
			</div>
			
			<form id="resetMFAForm" method="post" action="{$pageURL}&action=reset_mfa&">
				<input type="hidden" name="target_userid" id="mfaUserID" value="" />
				
				<div class="formField">
					<label>Grund (erforderlich für Audit-Log!):</label>
					<textarea name="reason" rows="3" required placeholder="z.B.: User hat Smartphone verloren und kann sich nicht mehr einloggen"></textarea>
				</div>
				
				<div class="formField">
					<label>
						<input type="checkbox" name="confirm" required />
						Ich bestätige, dass ich die MFA des Users deaktivieren möchte
					</label>
				</div>
				
				<div class="buttons">
					<button type="submit" class="btn btn-danger">❌ MFA deaktivieren</button>
					<button type="button" class="btn btn-secondary" onclick="closeModal()">Abbrechen</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
// Password Reset Modal
function resetPassword(userid, email) {
	document.getElementById('resetUserID').value = userid;
	document.getElementById('resetUserEmail').textContent = email;
	document.getElementById('resetPasswordModal').style.display = 'block';
}

// MFA Reset Modal
function resetMFA(userid, email) {
	if(!confirm('MFA wirklich deaktivieren für ' + email + '?\n\nDies ist eine kritische Aktion und wird im Audit-Log protokolliert!')) {
		return;
	}
	
	document.getElementById('mfaUserID').value = userid;
	document.getElementById('mfaUserEmail').textContent = email;
	document.getElementById('resetMFAModal').style.display = 'block';
}

// Close Modal
function closeModal() {
	document.getElementById('resetPasswordModal').style.display = 'none';
	document.getElementById('resetMFAModal').style.display = 'none';
}

// Toggle manual password input
document.querySelectorAll('input[name="reset_type"]').forEach(function(radio) {
	radio.addEventListener('change', function() {
		document.getElementById('manualPassword').disabled = (this.value !== 'manual');
	});
});

// MFA Details anzeigen
function showMFADetails(userid) {
	// TODO: AJAX-Call to get full MFA details
	alert('MFA-Details für User #' + userid + '\n\n(Wird in vollständiger Version implementiert)');
}
</script>

<style>
.dropdown {
	position: relative;
	display: inline-block;
}

.dropdown-content {
	display: none;
	position: absolute;
	right: 0;
	background-color: white;
	min-width: 250px;
	box-shadow: 0 8px 16px rgba(0,0,0,0.2);
	z-index: 1;
	border-radius: 5px;
}

.dropdown:hover .dropdown-content {
	display: block;
}

.dropdown-content a,
.dropdown-content span {
	color: black;
	padding: 12px 16px;
	text-decoration: none;
	display: block;
	border-bottom: 1px solid #eee;
}

.dropdown-content a:hover {
	background-color: #f1f1f1;
}

.modal-backdrop {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0,0,0,0.5);
	z-index: 1000;
}

.modal-dialog {
	position: fixed;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	background: white;
	padding: 30px;
	border-radius: 10px;
	box-shadow: 0 10px 40px rgba(0,0,0,0.3);
	z-index: 1001;
	max-width: 500px;
	width: 90%;
}

.btn-danger {
	background: #f44336;
	color: white;
}

.btn-danger:hover {
	background: #d32f2f;
}
</style>

