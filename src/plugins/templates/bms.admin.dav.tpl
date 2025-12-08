{*
 * b1gMailServer Admin - DAV Protocols Management
 * CalDAV, CardDAV, WebDAV Configuration & Statistics
 *}

<form action="{$pageURL}&action=dav&save=true&" method="post" onsubmit="spin(this)">

{* SabreDAV Version Status *}
<fieldset>
	<legend>📚 SabreDAV Version</legend>
	
	<div class="row mb-3">
		<div class="col-md-12">
			<div class="alert alert-{$sabredav_version_status.status_class}" role="alert">
				<h5>
					<i class="fa {if $sabredav_version_status.is_critical}fa-exclamation-triangle{elseif $sabredav_version_status.update_available}fa-info-circle{else}fa-check-circle{/if}"></i>
					{$sabredav_version_status.status_text}
				</h5>
				
				{if $sabredav_version_status.installed_version_found}
					<p>
						<strong>Installierte Version:</strong> {$sabredav_version_status.installed_version}<br>
						{if $sabredav_version_status.update_available}
							<strong>Neueste Version:</strong> {$sabredav_version_status.latest_version}<br>
							<strong>Veröffentlicht:</strong> {$sabredav_version_status.published_at|date_format:"%d.%m.%Y"}<br>
							<a href="{$sabredav_version_status.release_url}" target="_blank" class="btn btn-sm btn-primary mt-2">
								<i class="fa fa-download"></i> Release-Informationen anzeigen
							</a>
						{else}
							<span class="text-success"><i class="fa fa-check"></i> Sie verwenden die neueste Version!</span>
						{/if}
					</p>
				{else}
					<p class="text-danger">
						<i class="fa fa-warning"></i> SabreDAV-Version konnte nicht ermittelt werden.
					</p>
				{/if}
			</div>
		</div>
	</div>
</fieldset>

{* CalDAV Configuration *}
<fieldset>
	<legend>📅 CalDAV (SabreDAV)</legend>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">CalDAV Server:</label>
		<div class="col-sm-6">
			<input type="text" class="form-control" name="caldav_server" 
				   value="{$bms_prefs.user_caldavserver}" placeholder="caldav.domain.de" required>
		</div>
		<div class="col-sm-3">
			<input type="number" class="form-control" name="caldav_port" 
				   value="{$bms_prefs.user_caldavport}" placeholder="443" min="1" max="65535" required>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">{lng p="bms_dav_ssl"}:</label>
		<div class="col-sm-9">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="caldav_ssl" value="yes" 
					   {if $bms_prefs.user_caldav_ssl=='yes'}checked{/if}>
				<label class="form-check-label">
					SSL/TLS verwenden (empfohlen)
				</label>
			</div>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Server-URL:</label>
		<div class="col-sm-9">
			<code>{if $bms_prefs.user_caldav_ssl=='yes'}https://{else}http://{/if}{$bms_prefs.user_caldavserver}:{$bms_prefs.user_caldavport}/interface/caldav.php/</code>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Statistiken (heute):</label>
		<div class="col-sm-9">
			<div class="list-group">
				<div class="list-group-item">
					<strong>Sitzungen:</strong> {$caldav_sessions_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Traffic:</strong> {size bytes=$caldav_traffic_today}</div>
				<div class="list-group-item">
					<strong>Events erstellt:</strong> {$caldav_events_created_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Events aktualisiert:</strong> {$caldav_events_updated_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Events gelöscht:</strong> {$caldav_events_deleted_today|number_format:0:",":"."}</div>
			</div>
		</div>
	</div>
</fieldset>

{* CardDAV Configuration *}
<fieldset>
	<legend>📇 CardDAV (SabreDAV)</legend>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">CardDAV Server:</label>
		<div class="col-sm-6">
			<input type="text" class="form-control" name="carddav_server" 
				   value="{$bms_prefs.user_carddavserver}" placeholder="carddav.domain.de" required>
		</div>
		<div class="col-sm-3">
			<input type="number" class="form-control" name="carddav_port" 
				   value="{$bms_prefs.user_carddavport}" placeholder="443" min="1" max="65535" required>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">{lng p="bms_dav_ssl"}:</label>
		<div class="col-sm-9">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="carddav_ssl" value="yes" 
					   {if $bms_prefs.user_carddav_ssl=='yes'}checked{/if}>
				<label class="form-check-label">
					SSL/TLS verwenden (empfohlen)
				</label>
			</div>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Server-URL:</label>
		<div class="col-sm-9">
			<code>{if $bms_prefs.user_carddav_ssl=='yes'}https://{else}http://{/if}{$bms_prefs.user_carddavserver}:{$bms_prefs.user_carddavport}/interface/carddav.php/</code>
		</div>
	</div>
	
	<div class="row mb-3">
		<label class="col-sm-3 col-form-label">Statistiken (heute):</label>
		<div class="col-sm-9">
			<div class="list-group">
				<div class="list-group-item">
					<strong>Sitzungen:</strong> {$carddav_sessions_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Traffic:</strong> {size bytes=$carddav_traffic_today}</div>
				<div class="list-group-item">
					<strong>Kontakte erstellt:</strong> {$carddav_contacts_created_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Kontakte aktualisiert:</strong> {$carddav_contacts_updated_today|number_format:0:",":"."}</div>
				<div class="list-group-item">
					<strong>Kontakte gelöscht:</strong> {$carddav_contacts_deleted_today|number_format:0:",":"."}</div>
			</div>
		</div>
	</div>
</fieldset>

{* WebDAV DEAKTIVIERT - Nutze SFTPGo stattdessen *}
<div class="alert alert-info">
	<strong>ℹ️ HINWEIS:</strong> WebDAV wurde aus SabreDAV entfernt um Konflikte zu vermeiden.<br>
	<strong>📦 Für Dateispeicherung nutze:</strong> <a href="{$pageURL|replace:'dav':'sftpgo'}"><strong>SFTPGo</strong></a> (Tab: SFTPGo)<br>
	<em>SFTPGo bietet: SFTP, FTPS, WebDAV, HTTP/S mit S3/Minio-Backend</em><br><br>
	<strong>📚 SabreDAV ist zuständig für:</strong>
	<ul>
		<li>📅 <strong>CalDAV</strong> - Kalender-Synchronisation</li>
		<li>📇 <strong>CardDAV</strong> - Kontakt-Synchronisation</li>
	</ul>
</div>

{* Submit Button *}
<div class="row">
	<div class="col-md-12">
		<button type="submit" class="btn btn-primary">
			<i class="fa fa-save"></i> Speichern
		</button>
	</div>
</div>

</form>

{* Help Text *}
<div class="alert alert-success mt-4">
	<h5><i class="fa fa-info-circle"></i> Client-Konfiguration</h5>
	<ul>
		<li><strong>📅 CalDAV:</strong> Synchronisiert Kalender mit iOS, Android, Thunderbird, Lightning, etc.</li>
		<li><strong>📇 CardDAV:</strong> Synchronisiert Kontakte mit iOS, Android, Thunderbird, etc.</li>
		<li><strong>🔒 SSL/TLS:</strong> Sollte immer aktiviert sein für sichere Verbindungen (Port 443).</li>
		<li><strong>👥 Gruppen-Berechtigungen:</strong> DAV-Protokolle können pro Gruppe aktiviert/deaktiviert werden.</li>
		<li><strong>📦 Dateispeicherung:</strong> Nutze <strong>SFTPGo</strong> statt WebDAV für bessere Performance und S3-Backend!</li>
	</ul>
</div>
