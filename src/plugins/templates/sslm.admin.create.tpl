<form action="{$pageURL}" method="post" onsubmit="spin(this)">

{if isset($createResult)}
	<div class="alert alert-{if $createResult.success}success{else}danger{/if}">
		<h5>
			{if $createResult.success}
				<i class="fa fa-check-circle"></i> Erfolg!
			{else}
				<i class="fa fa-exclamation-circle"></i> Fehler!
			{/if}
		</h5>
		<p>{$createResult.message}</p>
		{if $createResult.success}
			<a href="{$pageURL|replace:'&action=create':''}" class="btn btn-success">
				<i class="fa fa-arrow-left"></i> Zurück zur Übersicht
			</a>
		{/if}
	</div>
{/if}

<fieldset>
	<legend>➕ Neues SSL-Zertifikat erstellen</legend>
	
	{* Step 1: Certificate Type *}
	<div class="card mb-3">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">1️⃣ Zertifikat-Typ auswählen</h5>
		</div>
		<div class="card-body">
			<div class="form-check mb-2">
				<input class="form-check-input" type="radio" name="cert_type" id="type_single" 
				       value="single" checked onchange="updateTypeSelection()">
				<label class="form-check-label" for="type_single">
					<strong>Einzelzertifikat</strong> - Eine spezifische Domain
					<br><small class="text-muted">Beispiel: imap.domain.tld</small>
				</label>
			</div>
			
			<div class="form-check mb-2">
				<input class="form-check-input" type="radio" name="cert_type" id="type_wildcard" 
				       value="wildcard" onchange="updateTypeSelection()">
				<label class="form-check-label" for="type_wildcard">
					<strong>Wildcard-Zertifikat</strong> - Alle Subdomains
					<br><small class="text-muted">Beispiel: *.domain.tld (deckt imap.domain.tld, smtp.domain.tld, etc. ab)</small>
					<br><span class="badge bg-warning">⚠️ Erfordert DNS-01 Validierung!</span>
				</label>
			</div>
			
			<div class="form-check mb-2">
				<input class="form-check-input" type="radio" name="cert_type" id="type_san" 
				       value="san" onchange="updateTypeSelection()">
				<label class="form-check-label" for="type_san">
					<strong>SAN-Zertifikat</strong> - Mehrere spezifische Domains
					<br><small class="text-muted">Ein Zertifikat für bis zu 100 Domains</small>
				</label>
			</div>
			
			<div class="form-check mb-2">
				<input class="form-check-input" type="radio" name="cert_type" id="type_upload" 
				       value="upload" onchange="updateTypeSelection()">
				<label class="form-check-label" for="type_upload">
					<strong>📤 Externes Zertifikat hochladen</strong> - Bereits gekauftes Zertifikat
					<br><small class="text-muted">Für bei CA gekaufte Zertifikate (z.B. Comodo, DigiCert, etc.)</small>
					<br><span class="badge bg-info">💡 Domain wird automatisch aus Zertifikat erkannt</span>
				</label>
			</div>
		</div>
	</div>
	
	{* Step 2: Domain Selection *}
	<div class="card mb-3">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">2️⃣ Domain(s) auswählen</h5>
		</div>
		<div class="card-body">
			{* Single Domain *}
			<div id="single_domain_section">
				<label class="form-label"><strong>Domain:</strong></label>
				<input type="text" class="form-control mb-2" name="single_domain" 
				       placeholder="imap.domain.tld" value="{$smarty.get.domain|default:''}">
				<small class="text-muted">Vollständiger Hostname (FQDN)</small>
			</div>
			
			{* Wildcard Domain *}
			<div id="wildcard_domain_section" style="display:none;">
				<label class="form-label"><strong>Basis-Domain:</strong></label>
				<input type="text" class="form-control mb-2" name="base_domain" 
				       placeholder="domain.tld" value="{$smarty.get.domain|default:''}">
				<small class="text-muted">Ohne Subdomain (wird automatisch *.domain.tld)</small>
				
				{if count($discovered_domains) > 0}
				<div class="alert alert-info mt-3">
					<strong>📋 Erkannte Basis-Domains:</strong><br>
					{foreach from=$discovered_domains key=domain item=data}
						<button type="button" class="btn btn-sm btn-outline-primary mt-1" 
						        onclick="document.querySelector('input[name=base_domain]').value='{$domain}'">
							{$domain}
						</button>
					{/foreach}
				</div>
				{/if}
			</div>
			
			{* SAN Domains *}
			<div id="san_domain_section" style="display:none;">
				<label class="form-label"><strong>Domains auswählen:</strong></label>
				
				{if count($discovered_domains) > 0}
					{foreach from=$discovered_domains key=domain item=data}
						<div class="card mb-2">
							<div class="card-header">
								<strong>{$domain}</strong>
							</div>
							<div class="card-body">
								{foreach from=$data.subdomains item=sub}
									<div class="form-check">
										<input class="form-check-input" type="checkbox" 
										       name="selected_domains[]" value="{$sub.fqdn}" 
										       id="domain_{$sub.fqdn|replace:'.':'_'}">
										<label class="form-check-label" for="domain_{$sub.fqdn|replace:'.':'_'}">
											<code>{$sub.fqdn}</code> 
											<small class="text-muted">({$sub.protocol})</small>
										</label>
									</div>
								{/foreach}
							</div>
						</div>
					{/foreach}
				{else}
					<textarea class="form-control" name="manual_domains" rows="5" 
					          placeholder="imap.domain.tld&#10;smtp.domain.tld&#10;mail.domain.tld"></textarea>
					<small class="text-muted">Eine Domain pro Zeile</small>
				{/if}
			</div>
		</div>
	</div>
	
	{* Step 2b: Upload External Certificate (only visible when type_upload selected) *}
	<div class="card mb-3" id="upload_section" style="display:none;">
		<div class="card-header bg-success text-white">
			<h5 class="mb-0">📤 Externes Zertifikat hochladen</h5>
		</div>
		<div class="card-body">
			<div class="alert alert-info">
				<h6><i class="fa fa-info-circle"></i> Upload-Anleitung</h6>
				<p class="mb-0">
					Lade dein bereits gekauftes SSL-Zertifikat hoch. Das System erkennt automatisch:<br>
					• Die Domain(s) aus dem Zertifikat<br>
					• Zertifikatstyp (Single, Wildcard, SAN)<br>
					• Gültigkeitsdauer<br>
					• Aussteller (CA)
				</p>
			</div>
			
			<div class="mb-3">
				<label for="cert_file" class="form-label"><strong>1. Certificate File (.crt, .pem)</strong></label>
				<textarea class="form-control font-monospace" id="cert_file" name="cert_file" rows="10" 
				          placeholder="-----BEGIN CERTIFICATE-----&#10;MIIFXzCCA0egAwIBAgIRAKe...&#10;-----END CERTIFICATE-----"></textarea>
				<small class="text-muted">Kopiere den Inhalt deines Zertifikats hier ein</small>
			</div>
			
			<div class="mb-3">
				<label for="private_key" class="form-label"><strong>2. Private Key (.key)</strong></label>
				<textarea class="form-control font-monospace" id="private_key" name="private_key" rows="10" 
				          placeholder="-----BEGIN PRIVATE KEY-----&#10;MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAo...&#10;-----END PRIVATE KEY-----"></textarea>
				<small class="text-muted">Dein privater Schlüssel (NIEMALS weitergeben!)</small>
			</div>
			
			<div class="mb-3">
				<label for="chain_cert" class="form-label"><strong>3. Chain/Intermediate Certificate (optional)</strong></label>
				<textarea class="form-control font-monospace" id="chain_cert" name="chain_cert" rows="8" 
				          placeholder="-----BEGIN CERTIFICATE-----&#10;...Intermediate CA Certificate...&#10;-----END CERTIFICATE-----"></textarea>
				<small class="text-muted">CA Bundle / Intermediate Certificate (falls vorhanden)</small>
			</div>
			
			<div class="alert alert-warning">
				<h6><i class="fa fa-exclamation-triangle"></i> Wichtig!</h6>
				<ul class="mb-0">
					<li>Alle Dateien müssen im PEM-Format sein</li>
					<li>Der Private Key muss unverschlüsselt sein (kein Passwort)</li>
					<li>Das Zertifikat muss noch gültig sein</li>
				</ul>
			</div>
		</div>
	</div>
	
	{* Step 3: Certificate Settings *}
	<div class="card mb-3" id="acme_section">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">3️⃣ Zertifikat-Einstellungen</h5>
		</div>
		<div class="card-body">
			<div class="form-check mb-3">
				<input class="form-check-input" type="checkbox" name="auto_renew" 
				       id="auto_renew" checked>
				<label class="form-check-label" for="auto_renew">
					<strong>Automatische Erneuerung</strong>
					<br><small class="text-muted">Zertifikat wird 30 Tage vor Ablauf automatisch erneuert</small>
				</label>
			</div>
			
			<div class="alert alert-success">
				<h6><i class="fa fa-lock"></i> Let's Encrypt</h6>
				<p class="mb-0">
					Kostenlose SSL-Zertifikate mit 90 Tagen Gültigkeit.<br>
					Automatische Erneuerung alle 60 Tage.
				</p>
			</div>
		</div>
	</div>
	
	{* Step 4: Deployment *}
	<div class="card mb-3">
		<div class="card-header bg-primary text-white">
			<h5 class="mb-0">4️⃣ Deployment (optional)</h5>
		</div>
		<div class="card-body">
			<p><strong>Zertifikat automatisch deployen zu:</strong></p>
			
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="deploy_apache" id="deploy_apache" checked>
				<label class="form-check-label" for="deploy_apache">
					<strong>Apache/Nginx</strong> - Webserver
				</label>
			</div>
			
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="deploy_dovecot" id="deploy_dovecot" checked>
				<label class="form-check-label" for="deploy_dovecot">
					<strong>Dovecot</strong> - IMAP/POP3/Sieve
				</label>
			</div>
			
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="deploy_postfix" id="deploy_postfix" checked>
				<label class="form-check-label" for="deploy_postfix">
					<strong>Postfix</strong> - SMTP
				</label>
			</div>
			
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="deploy_sftpgo" id="deploy_sftpgo">
				<label class="form-check-label" for="deploy_sftpgo">
					<strong>SFTPGo</strong> - SFTP/FTPS/WebDAV
				</label>
			</div>
			
			<small class="text-muted mt-2 d-block">
				Services werden nach erfolgreicher Zertifikatserstellung automatisch neu geladen
			</small>
		</div>
	</div>
	
	{* Actions *}
	<div class="d-flex justify-content-between">
		<a href="{$pageURL|replace:'&action=create':''}" class="btn btn-secondary">
			<i class="fa fa-arrow-left"></i> Abbrechen
		</a>
		<div>
			<button type="submit" name="create" class="btn btn-primary">
				<i class="fa fa-save"></i> Zertifikat erstellen (ohne ACME)
			</button>
			<button type="submit" name="create" class="btn btn-success">
				<i class="fa fa-certificate"></i> Erstellen & Let's Encrypt starten
				<input type="hidden" name="start_acme" value="1">
			</button>
		</div>
	</div>
</fieldset>

{* Info Box *}
<div class="alert alert-warning mt-4">
	<h5><i class="fa fa-exclamation-triangle"></i> ⚠️ Wichtige Hinweise</h5>
	<ul class="mb-0">
		<li><strong>Wildcard-Zertifikate</strong> erfordern DNS-01 Validierung (DNS-Eintrag). HTTP-01 funktioniert NICHT!</li>
		<li><strong>Domain muss erreichbar sein:</strong> Für HTTP-01 Validierung muss die Domain auf diesen Server zeigen</li>
		<li><strong>Port 80 muss offen sein:</strong> Let's Encrypt nutzt HTTP (Port 80) für die Validierung</li>
		<li><strong>Rate Limits:</strong> Let's Encrypt hat Rate Limits (50 Zertifikate/Woche pro Domain)</li>
		<li><strong>Staging-Modus:</strong> Teste zuerst mit Staging-Zertifikaten (in Einstellungen)</li>
	</ul>
</div>

<script>
function updateTypeSelection() {
	const type = document.querySelector('input[name="cert_type"]:checked').value;
	
	// Domain selection sections (for ACME)
	document.getElementById('single_domain_section').style.display = 
		type === 'single' ? 'block' : 'none';
	document.getElementById('wildcard_domain_section').style.display = 
		type === 'wildcard' ? 'block' : 'none';
	document.getElementById('san_domain_section').style.display = 
		type === 'san' ? 'block' : 'none';
	
	// Upload section (for external certificates)
	document.getElementById('upload_section').style.display = 
		type === 'upload' ? 'block' : 'none';
	
	// ACME settings (hide for external upload)
	document.getElementById('acme_section').style.display = 
		type === 'upload' ? 'none' : 'block';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
	// Pre-select type from URL parameter
	const urlType = '{$smarty.get.type|default:''}';
	if(urlType) {
		const typeRadio = document.getElementById('type_' + urlType);
		if(typeRadio) {
			typeRadio.checked = true;
			updateTypeSelection();
		}
	}
});
</script>

</form>
