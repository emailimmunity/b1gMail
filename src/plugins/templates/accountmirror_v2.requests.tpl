{*
 * AccountMirror v2.0 - Auskunftsersuchen (TKÜV § 113 TKG)
 * Gestaffelte Auskunftsstufen nach BNetzA-Vorgaben
 *}

<div class="section-header">
	<h2>📋 Auskunftsersuchen von Ermittlungsbehörden</h2>
	<p class="notice">
		<strong>⚖️ Rechtsgrundlage:</strong> TKÜV § 113 TKG | 
		<strong>Stufen:</strong> Bestandsdaten → Verkehrsdaten → Inhaltsdaten → Vollumfänglich
	</p>
</div>

{* Liste der Auskunftsersuchen *}
<fieldset>
	<legend>📊 Offene & Abgeschlossene Ersuchen</legend>
	
	<table class="list">
		<tr>
			<th width="30">Priorität</th>
			<th width="120">Ersuchen-Nr.</th>
			<th width="180">Behörde</th>
			<th width="100">Stufe</th>
			<th width="150">Betroffener Account</th>
			<th width="100">Zeitraum</th>
			<th width="100">Frist</th>
			<th width="80">Status</th>
			<th width="120">Aktionen</th>
		</tr>
		
		{foreach from=$requests item=req}
		{cycle name=class values="td1,td2" assign=class}
		<tr class="{$class}">
			<td align="center">
				{if $req.priority == 'immediate'}
					<span style="color:red; font-weight:bold;">🔴 SOFORT</span>
				{elseif $req.priority == 'urgent'}
					<span style="color:orange; font-weight:bold;">🟠 DRINGEND</span>
				{else}
					<span style="color:green;">🟢 Normal</span>
				{/if}
			</td>
			<td><strong>{$req.request_number|escape}</strong></td>
			<td>
				{$req.authority|escape}
				{if $req.file_number}
					<br><small>Az: {$req.file_number|escape}</small>
				{/if}
			</td>
			<td>
				{if $req.request_type == 'bestandsdaten'}
					<span class="badge badge-info">📇 Bestandsdaten</span>
				{elseif $req.request_type == 'verkehrsdaten'}
					<span class="badge badge-warning">📊 Verkehrsdaten</span>
				{elseif $req.request_type == 'inhaltsdaten'}
					<span class="badge badge-danger">📧 Inhaltsdaten</span>
				{else}
					<span class="badge badge-dark">💯 Vollumfänglich</span>
				{/if}
			</td>
			<td>
				{$req.target_email|escape|default:'-'}
				{if $req.target_userid}
					<br><small>ID: {$req.target_userid}</small>
				{/if}
			</td>
			<td style="font-size:11px;">
				{if $req.request_period_from}
					Von: {$req.request_period_from|date_format:"%d.%m.%Y"}<br>
					Bis: {$req.request_period_to|date_format:"%d.%m.%Y"|default:"unbegrenzt"}
				{else}
					<em>Kein Zeitraum</em>
				{/if}
			</td>
			<td>
				{if $req.deadline}
					{$req.deadline|date_format:"%d.%m.%Y"}
				{else}
					<em>Keine</em>
				{/if}
			</td>
			<td align="center">
				{if $req.status == 'pending'}
					<span style="color:orange; font-weight:bold;">⏳ Offen</span>
				{elseif $req.status == 'in_progress'}
					<span style="color:blue; font-weight:bold;">⚙️ In Arbeit</span>
				{elseif $req.status == 'completed'}
					<span style="color:green; font-weight:bold;">✅ Erledigt</span>
				{elseif $req.status == 'rejected'}
					<span style="color:red;">❌ Abgelehnt</span>
				{else}
					<span style="color:purple;">⚠️ Teil-Erledigt</span>
				{/if}
			</td>
			<td>
				{if $req.status == 'pending' || $req.status == 'in_progress'}
					<a href="{$pageURL}&tab=requests&generate_response=1&request_id={$req.id}&sid={$sid}" 
					   class="btn btn-sm btn-primary" 
					   onclick="return confirm('Auskunft jetzt generieren?');">
						📤 Generieren
					</a>
				{/if}
				{if $req.status == 'completed'}
					<button onclick="toggleDetails({$req.id})" class="btn btn-sm btn-info">
						👁️ Daten anzeigen
					</button>
					<a href="{$pageURL}&tab=requests&export_csv=1&request_id={$req.id}&sid={$sid}" 
					   class="btn btn-sm btn-success">
						📥 CSV Export
					</a>
				{/if}
			</td>
		</tr>
		
		{* DETAIL-ANSICHT: Auskunftsdaten (ausklappbar) *}
		{if $req.status == 'completed' && $req.response_data}
		<tr id="details-{$req.id}" class="details-row" style="display:none;">
			<td colspan="9" style="background:#f0f8ff; border-left:4px solid #667eea; padding:20px;">
				<div class="response-details">
					<h3>📄 Auskunftsdaten für Ersuchen {$req.request_number|escape}</h3>
					<p style="color:#666; margin-bottom:20px;">
						<strong>Generiert:</strong> {$req.completed_at|date_format:"%d.%m.%Y %H:%M"} | 
						<strong>Typ:</strong> {$req.request_type|upper} | 
						<button onclick="copyToClipboard('response-data-{$req.id}')" class="btn btn-sm btn-secondary">
							📋 Alle Daten kopieren
						</button>
					</p>
					
					<div id="response-data-{$req.id}" class="response-data-container">
						{assign var="data" value=$req.response_data|@json_decode:true}
						
						{* STUFE 1: BESTANDSDATEN *}
						{if $data.bestandsdaten}
						<fieldset class="data-section">
							<legend>📇 Bestandsdaten (§ 113 Abs. 1 TKG)</legend>
							<table class="data-table">
								<tr>
									<th width="200">Feld</th>
									<th>Wert</th>
								</tr>
								{foreach from=$data.bestandsdaten key=k item=v}
								<tr>
									<td><strong>{$k|escape}:</strong></td>
									<td><span class="copyable">{$v|escape|default:'-'}</span></td>
								</tr>
								{/foreach}
							</table>
						</fieldset>
						{/if}
						
						{* STUFE 2: VERKEHRSDATEN *}
						{if $data.verkehrsdaten}
						<fieldset class="data-section">
							<legend>📊 Verkehrsdaten (§ 113 Abs. 2 TKG)</legend>
							
							{if $data.verkehrsdaten.total_emails}
							<p><strong>Anzahl E-Mails:</strong> {$data.verkehrsdaten.total_emails}</p>
							{/if}
							
							{if $data.verkehrsdaten.emails}
							<table class="data-table">
								<tr>
									<th width="50">Mail-ID</th>
									<th width="150">Von</th>
									<th width="150">An</th>
									<th width="200">Betreff</th>
									<th width="130">Datum</th>
									<th width="80">Größe</th>
									<th width="100">IP</th>
								</tr>
								{foreach from=$data.verkehrsdaten.emails item=email}
								<tr>
									<td>{$email.mail_id}</td>
									<td class="copyable">{$email.from|escape|truncate:30}</td>
									<td class="copyable">{$email.to|escape|truncate:30}</td>
									<td class="copyable">{$email.subject|escape|truncate:40}</td>
									<td>{$email.date|date_format:"%d.%m.%Y %H:%M"}</td>
									<td>{$email.size|number_format:0:".":" "} B</td>
									<td><code>{$email.sender_ip|default:'-'}</code></td>
								</tr>
								{/foreach}
							</table>
							{/if}
						</fieldset>
						{/if}
						
						{* STUFE 3: INHALTSDATEN *}
						{if $data.inhaltsdaten}
						<fieldset class="data-section" style="border:2px solid #dc3545;">
							<legend style="color:#dc3545;">📧 Inhaltsdaten (§ 100a StPO - Richterliche Anordnung!)</legend>
							
							{if $data.inhaltsdaten.total_emails_with_content}
							<p><strong>Anzahl E-Mails mit Inhalt:</strong> {$data.inhaltsdaten.total_emails_with_content}</p>
							{/if}
							
							{if $data.inhaltsdaten.emails}
							{foreach from=$data.inhaltsdaten.emails item=email}
							<div class="email-content" style="background:#fff; border:1px solid #ddd; padding:15px; margin-bottom:15px; border-radius:6px;">
								<h4>Mail #{$email.mail_id}</h4>
								<table class="data-table-mini">
									<tr>
										<td width="100"><strong>Von:</strong></td>
										<td class="copyable">{$email.from|escape}</td>
									</tr>
									<tr>
										<td><strong>An:</strong></td>
										<td class="copyable">{$email.to|escape}</td>
									</tr>
									<tr>
										<td><strong>Betreff:</strong></td>
										<td class="copyable">{$email.subject|escape}</td>
									</tr>
									<tr>
										<td><strong>Datum:</strong></td>
										<td>{$email.date|date_format:"%d.%m.%Y %H:%M"}</td>
									</tr>
								</table>
								
								<h5>📄 Text-Inhalt:</h5>
								<div class="email-body copyable" style="background:#f9f9f9; padding:10px; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow:auto; white-space:pre-wrap; font-family:monospace; font-size:12px;">
{$email.body_text|escape|default:'<em>Kein Text-Body</em>'}
								</div>
								
								{if $email.attachments}
								<h5>📎 Anhänge ({$email.attachments|@count}):</h5>
								<ul>
									{foreach from=$email.attachments item=att}
									<li>{$att.name|escape} ({$att.size|number_format:0:".":" "} Bytes)</li>
									{/foreach}
								</ul>
								{/if}
							</div>
							{/foreach}
							{/if}
						</fieldset>
						{/if}
						
						{* RAW JSON (für Vollständigkeit) *}
						<details style="margin-top:20px;">
							<summary style="cursor:pointer; color:#667eea; font-weight:bold;">🔧 Raw JSON-Daten anzeigen</summary>
							<pre class="json-raw copyable" style="background:#2b2b2b; color:#f8f8f2; padding:15px; border-radius:6px; overflow:auto; max-height:400px; margin-top:10px;">{$req.response_data|@json_encode:JSON_PRETTY_PRINT|escape}</pre>
						</details>
					</div>
				</div>
			</td>
		</tr>
		{/if}
		
		{if $req.notes}
		<tr class="{$class}">
			<td></td>
			<td colspan="8" style="background:#fffacd; padding:10px;">
				<strong>Notizen:</strong> {$req.notes|escape|nl2br}
			</td>
		</tr>
		{/if}
		{foreachelse}
		<tr>
			<td colspan="9" align="center" style="padding:30px;">
				<em>Keine Auskunftsersuchen vorhanden</em>
			</td>
		</tr>
		{/foreach}
	</table>
</fieldset>

{* Neues Auskunftsersuchen anlegen *}
<fieldset>
	<legend>➕ Neues Auskunftsersuchen anlegen</legend>
	
	<form method="post" action="{$pageURL}&tab=requests&add_request=1&sid={$sid}">
		<table width="100%">
			<tr>
				<td class="td1" width="200"><strong>Ersuchen-Nummer *:</strong></td>
				<td class="td2">
					<input type="text" name="request_number" size="40" required 
					       placeholder="z.B. BKA-2025-001234" />
					<br><small>Eindeutige Nummer von der Behörde</small>
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>Anfragende Behörde *:</strong></td>
				<td class="td2">
					<input type="text" name="authority" size="40" required 
					       placeholder="z.B. Bundeskriminalamt, Staatsanwaltschaft München I" />
				</td>
			</tr>
			<tr>
				<td class="td1">Kontakt Behörde:</td>
				<td class="td2">
					<input type="text" name="authority_contact" size="40" 
					       placeholder="z.B. sachbearbeiter@bka.bund.de" />
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>Auskunftsstufe *:</strong></td>
				<td class="td2">
					<select name="request_type" required onchange="showRequestTypeInfo(this.value)">
						<option value="">Bitte wählen...</option>
						<option value="bestandsdaten">📇 Stufe 1: Bestandsdaten (§ 113 Abs. 1 TKG)</option>
						<option value="verkehrsdaten">📊 Stufe 2: Verkehrsdaten (§ 113 Abs. 2 TKG)</option>
						<option value="inhaltsdaten">📧 Stufe 3: Inhaltsdaten (§ 100a StPO - Richter!)</option>
						<option value="vollumfaenglich">💯 Stufe 4: Vollumfänglich (alles)</option>
					</select>
					<div id="request_type_info" style="margin-top:10px; padding:10px; border-radius:4px; display:none;"></div>
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>Rechtsgrundlage *:</strong></td>
				<td class="td2">
					<textarea name="legal_basis" rows="2" cols="50" required 
					          placeholder="z.B. § 100a StPO, Gerichtsbeschluss vom 15.01.2025"></textarea>
				</td>
			</tr>
			<tr>
				<td class="td1">Aktenzeichen:</td>
				<td class="td2">
					<input type="text" name="file_number" size="40" 
					       placeholder="z.B. 123 Js 456/24" />
				</td>
			</tr>
			<tr>
				<td class="td1"><strong>Betroffener Account *:</strong></td>
				<td class="td2">
					<input type="text" name="target_email" size="40" required 
					       placeholder="user@example.com" />
					<br><small>E-Mail-Adresse des zu überwachenden Accounts</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Zeitraum:</td>
				<td class="td2">
					Von: {html_select_date prefix="period_from" start_year="-2" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="period_from" display_seconds=false}
					<br><br>
					Bis: {html_select_date prefix="period_to" field_order="DMY" field_separator="."}, 
					{html_select_time prefix="period_to" display_seconds=false}
				</td>
			</tr>
			<tr>
				<td class="td1">Frist:</td>
				<td class="td2">
					{html_select_date prefix="deadline" end_year="+1" field_order="DMY" field_separator="."}
				</td>
			</tr>
			<tr>
				<td class="td1">Priorität:</td>
				<td class="td2">
					<select name="priority">
						<option value="normal">🟢 Normal</option>
						<option value="urgent">🟠 Dringend (innerhalb 7 Tage)</option>
						<option value="immediate">🔴 Sofort (Gefahr im Verzug)</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1">Notizen:</td>
				<td class="td2">
					<textarea name="notes" rows="3" cols="50" 
					          placeholder="Interne Notizen..."></textarea>
				</td>
			</tr>
		</table>
		
		<p align="right">
			<button type="submit" class="btn btn-primary">✓ Auskunftsersuchen anlegen</button>
		</p>
	</form>
</fieldset>

<fieldset>
	<legend>ℹ️ Gestaffelte Auskunftsstufen (BNetzA)</legend>
	
	<table width="100%" class="info-table">
		<tr>
			<th width="150">Stufe</th>
			<th width="200">Was wird ausgeliefert?</th>
			<th width="200">Rechtsgrundlage</th>
			<th>Beispiele</th>
		</tr>
		<tr>
			<td><strong>📇 Bestandsdaten</strong><br>§ 113 Abs. 1</td>
			<td>
				• Name, Adresse<br>
				• Geburtsdatum<br>
				• Telefonnummer<br>
				• Registrierungsdatum<br>
				• Letzter Login
			</td>
			<td>
				§ 113 TKG Abs. 1<br>
				<em>Einfache Anfrage</em>
			</td>
			<td>
				"Wer ist der Inhaber?"<br>
				"Wann wurde registriert?"
			</td>
		</tr>
		<tr>
			<td><strong>📊 Verkehrsdaten</strong><br>§ 113 Abs. 2</td>
			<td>
				• Von/An (Absender/Empfänger)<br>
				• Betreff<br>
				• Datum & Uhrzeit<br>
				• IP-Adressen<br>
				• Login-Zeiten<br>
				<strong>KEINE Inhalte!</strong>
			</td>
			<td>
				§ 113 TKG Abs. 2<br>
				<em>Erweiterte Anfrage</em>
			</td>
			<td>
				"Mit wem kommuniziert?"<br>
				"Wann & woher Zugriff?"
			</td>
		</tr>
		<tr>
			<td><strong>📧 Inhaltsdaten</strong><br>§ 100a StPO</td>
			<td>
				• Alle E-Mail-Inhalte<br>
				• Anhänge<br>
				• Webdisk-Dateien<br>
				• Kalender<br>
				• Kontakte
			</td>
			<td>
				§ 100a/b StPO<br>
				<strong style="color:red;">NUR mit richterlicher Anordnung!</strong>
			</td>
			<td>
				"Was steht in den Mails?"<br>
				"Welche Dateien?"
			</td>
		</tr>
		<tr>
			<td><strong>💯 Vollumfänglich</strong></td>
			<td>
				• Bestandsdaten<br>
				• Verkehrsdaten<br>
				• Inhaltsdaten<br>
				<strong>ALLES!</strong>
			</td>
			<td>
				Alle obigen + § 100a StPO<br>
				<strong style="color:red;">Richterliche Anordnung PFLICHT!</strong>
			</td>
			<td>
				Komplette Überwachung<br>
				Alle Daten
			</td>
		</tr>
	</table>
</fieldset>

<style>
.section-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 20px;
	border-radius: 8px;
	margin-bottom: 20px;
}

.section-header h2 {
	margin: 0 0 10px 0;
	color: white;
}

.notice {
	background: rgba(255,255,255,0.2);
	padding: 10px;
	border-radius: 4px;
	margin: 10px 0 0 0;
}

.badge {
	display: inline-block;
	padding: 4px 10px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
}

.badge-info { background: #17a2b8; color: white; }
.badge-warning { background: #ffc107; color: #000; }
.badge-danger { background: #dc3545; color: white; }
.badge-dark { background: #343a40; color: white; }

.btn {
	padding: 6px 12px;
	border-radius: 4px;
	text-decoration: none;
	display: inline-block;
	font-size: 12px;
	font-weight: 600;
	cursor: pointer;
	border: none;
}

.btn-sm { padding: 4px 8px; font-size: 11px; }
.btn-primary { background: #667eea; color: white; }
.btn-success { background: #28a745; color: white; }
.btn:hover { opacity: 0.9; }

.info-table {
	width: 100%;
	border-collapse: collapse;
}

.info-table th {
	background: #f0f0f0;
	padding: 10px;
	border: 1px solid #ddd;
	font-weight: bold;
}

.info-table td {
	padding: 10px;
	border: 1px solid #ddd;
	vertical-align: top;
}

.td1 {
	background: #f9f9f9;
	font-weight: 600;
	padding: 10px;
}

.td2 {
	background: #fff;
	padding: 10px;
}

.list th {
	background: #f0f0f0;
	padding: 8px;
	font-weight: bold;
	border-bottom: 2px solid #ddd;
}

.list td {
	padding: 8px;
	border-bottom: 1px solid #eee;
	vertical-align: top;
}

/* Detail-Ansicht Styles */
.details-row {
	animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.response-details h3 {
	color: #667eea;
	margin-top: 0;
	border-bottom: 3px solid #667eea;
	padding-bottom: 10px;
}

.data-section {
	margin-bottom: 20px;
	border: 1px solid #ddd;
	border-radius: 6px;
	padding: 15px;
}

.data-section legend {
	font-weight: bold;
	color: #667eea;
	padding: 0 10px;
}

.data-table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 10px;
}

.data-table th {
	background: #667eea;
	color: white;
	padding: 10px;
	text-align: left;
	font-weight: bold;
}

.data-table td {
	padding: 8px;
	border-bottom: 1px solid #eee;
}

.data-table tr:hover {
	background: #f9f9f9;
}

.data-table-mini {
	width: 100%;
	margin-bottom: 15px;
}

.data-table-mini td {
	padding: 5px;
	border-bottom: 1px solid #eee;
}

.copyable {
	cursor: pointer;
	padding: 2px 4px;
	border-radius: 3px;
	transition: background 0.2s;
}

.copyable:hover {
	background: #fff3cd;
}

.btn-info {
	background: #17a2b8;
	color: white;
}

.btn-secondary {
	background: #6c757d;
	color: white;
}

.email-content h4 {
	margin-top: 0;
	color: #667eea;
}

.email-content h5 {
	margin: 15px 0 10px 0;
	color: #555;
	font-size: 14px;
}
</style>

<script>
// Detail-Ansicht ein/ausklappen
function toggleDetails(requestId) {
	var detailRow = document.getElementById('details-' + requestId);
	if(detailRow) {
		if(detailRow.style.display === 'none') {
			detailRow.style.display = 'table-row';
		} else {
			detailRow.style.display = 'none';
		}
	}
}

// In Zwischenablage kopieren
function copyToClipboard(elementId) {
	var element = document.getElementById(elementId);
	if(!element) return;
	
	var text = element.innerText || element.textContent;
	
	// Moderne Clipboard API
	if(navigator.clipboard && navigator.clipboard.writeText) {
		navigator.clipboard.writeText(text).then(function() {
			alert('✅ Daten in Zwischenablage kopiert!\n\nSie können die Daten jetzt einfügen (Strg+V).');
		}).catch(function(err) {
			fallbackCopy(text);
		});
	} else {
		fallbackCopy(text);
	}
}

// Fallback für ältere Browser
function fallbackCopy(text) {
	var textarea = document.createElement('textarea');
	textarea.value = text;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	textarea.select();
	
	try {
		var successful = document.execCommand('copy');
		if(successful) {
			alert('✅ Daten in Zwischenablage kopiert!');
		} else {
			alert('❌ Kopieren fehlgeschlagen. Bitte manuell markieren und kopieren.');
		}
	} catch(err) {
		alert('❌ Kopieren fehlgeschlagen: ' + err);
	}
	
	document.body.removeChild(textarea);
}

// Einzelnes Element kopieren (bei Klick auf .copyable)
document.addEventListener('DOMContentLoaded', function() {
	document.addEventListener('click', function(e) {
		if(e.target.classList.contains('copyable')) {
			var text = e.target.innerText || e.target.textContent;
			
			if(navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					// Visuelles Feedback
					var originalBg = e.target.style.background;
					e.target.style.background = '#28a745';
					e.target.style.color = 'white';
					setTimeout(function() {
						e.target.style.background = originalBg;
						e.target.style.color = '';
					}, 300);
				});
			}
		}
	});
});

function showRequestTypeInfo(type) {
	var infoDiv = document.getElementById('request_type_info');
	var infos = {
		'bestandsdaten': '<strong style="color:#17a2b8;">📇 Bestandsdaten:</strong> Name, Adresse, Kontaktdaten, Registrierung. <em>Einfache Anfrage nach § 113 Abs. 1 TKG</em>',
		'verkehrsdaten': '<strong style="color:#ffc107;">📊 Verkehrsdaten:</strong> Mit wem kommuniziert? Wann & woher? <strong>KEINE Inhalte!</strong> <em>Nach § 113 Abs. 2 TKG</em>',
		'inhaltsdaten': '<strong style="color:#dc3545;">📧 Inhaltsdaten:</strong> E-Mail-Texte, Anhänge, Webdisk. <strong style="color:red;">NUR mit richterlicher Anordnung nach § 100a StPO!</strong>',
		'vollumfaenglich': '<strong style="color:#343a40;">💯 Vollumfänglich:</strong> ALLE Daten (Bestands-, Verkehrs- & Inhaltsdaten). <strong style="color:red;">Richterliche Anordnung PFLICHT!</strong>'
	};
	
	if(type && infos[type]) {
		infoDiv.innerHTML = infos[type];
		infoDiv.style.display = 'block';
		infoDiv.style.background = type == 'bestandsdaten' ? '#e7f3ff' : (type == 'verkehrsdaten' ? '#fff3cd' : '#f8d7da');
		infoDiv.style.border = '1px solid ' + (type == 'bestandsdaten' ? '#b3d9ff' : (type == 'verkehrsdaten' ? '#ffc107' : '#dc3545'));
	} else {
		infoDiv.style.display = 'none';
	}
}
</script>
