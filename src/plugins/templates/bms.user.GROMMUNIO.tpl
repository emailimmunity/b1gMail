{*
 * ============================================================================
 * GROMMUNIO/OUTLOOK INTEGRATION FOR bms.user.tpl
 * ============================================================================
 * 
 * Diese Code-Blöcke müssen in bms.user.tpl eingefügt werden
 * um Outlook/Exchange Zugangsdaten anzuzeigen
 * 
 * EINFÜGEN NACH: JMAP-Sektion
 * 
 * ============================================================================
 *}

{* Grommunio (Outlook/Exchange) Protocol *}
{if $haveGrommunio}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-windows"></i> Outlook / Exchange (Grommunio)
		</th>
	</tr>

	<tr>
		<td class="listTableLeft">&nbsp;</td>
		<td class="listTableRight">
			Native Outlook-Unterstützung ohne Plugins!<br />
			Kompatibel mit Microsoft Exchange-Protokollen.
		</td>
	</tr>

	{* AutoDiscover - Automatische Konfiguration *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-magic"></i> {lng p="grommunio_autodiscover"}:</td>
		<td class="listTableRight">
			<div style="padding: 10px; background-color: #e8f5e9; border-left: 3px solid #4caf50;">
				<strong>✅ Automatische Konfiguration!</strong><br />
				Outlook konfiguriert sich automatisch mit Ihrer E-Mail-Adresse und Passwort.
			</div>
			<br />
			<strong>Setup:</strong><br />
			1. Outlook öffnen<br />
			2. Konto hinzufügen → E-Mail-Adresse: <code>{$username}</code><br />
			3. Passwort eingeben → Fertig!<br />
			<br />
			<small>AutoDiscover-URL (falls manuell): <code>{$bms_prefs.user_autodiscover_url}</code></small>
		</td>
	</tr>

	{* Outlook Desktop (MAPI) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-desktop"></i> Outlook Desktop:</td>
		<td class="listTableRight">
			<strong>Protokoll:</strong> MAPI over HTTP<br />
			<strong>Server:</strong> <code>{$bms_prefs.user_grommunio_server}</code><br />
			<strong>{lng p="username"}:</strong> <code>{$username}</code><br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<br />
			<span class="badge bg-success"><i class="fa fa-check"></i> Native Integration - Kein Plugin nötig!</span>
		</td>
	</tr>

	{* Mobile Devices (ActiveSync) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-mobile"></i> {lng p="grommunio_mobile_setup"}:</td>
		<td class="listTableRight">
			<strong>Protokoll:</strong> Exchange ActiveSync (EAS)<br />
			<strong>Server:</strong> <code>{$bms_prefs.user_grommunio_server}</code><br />
			<strong>EAS-URL:</strong> <code>{$bms_prefs.user_eas_url}</code><br />
			<strong>{lng p="username"}:</strong> <code>{$username}</code><br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<strong>SSL/TLS:</strong> ✅ Aktiviert<br />
			<br />
			<strong>Unterstützte Geräte:</strong><br />
			• iOS (iPhone/iPad)<br />
			• Android<br />
			• Windows Phone<br />
			• Outlook Mobile App
		</td>
	</tr>

	{* Exchange Web Services (EWS) *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-cloud"></i> {lng p="grommunio_ews"}:</td>
		<td class="listTableRight">
			<strong>EWS-URL:</strong> <code>{$bms_prefs.user_ews_url}</code><br />
			<br />
			Für Apps und Dienste, die Exchange Web Services unterstützen.
		</td>
	</tr>

	{* Verbundene Geräte *}
	{if $grommunio_devices|@count > 0}
	<tr>
		<td class="listTableLeft"><i class="fa fa-tablet"></i> {lng p="grommunio_devices"}:</td>
		<td class="listTableRight">
			<table style="width: 100%; border-collapse: collapse;">
				<tr style="background-color: #f5f5f5;">
					<th style="padding: 5px; text-align: left;">Gerät</th>
					<th style="padding: 5px; text-align: left;">Typ</th>
					<th style="padding: 5px; text-align: left;">Letzte Sync</th>
					<th style="padding: 5px; text-align: left;">Aktion</th>
				</tr>
				{foreach from=$grommunio_devices item=device}
				<tr>
					<td style="padding: 5px;">
						{$device.device_model}<br />
						<small style="color: #666;">{$device.device_id|truncate:30}</small>
					</td>
					<td style="padding: 5px;">{$device.device_type}</td>
					<td style="padding: 5px;">{$device.last_sync|date_format:"%d.%m.%Y %H:%M"}</td>
					<td style="padding: 5px;">
						<a href="prefs.php?action=bms_userarea&remove_device={$device.id}" 
						   onclick="return confirm('Gerät wirklich entfernen?')"
						   style="color: #d32f2f;">
							<i class="fa fa-trash"></i> {lng p="grommunio_device_remove"}
						</a>
						<br />
						<a href="prefs.php?action=bms_userarea&wipe_device={$device.id}"
						   onclick="return confirm('WARNUNG: Alle Daten auf dem Gerät werden gelöscht! Fortfahren?')"
						   style="color: #ff9800;">
							<i class="fa fa-eraser"></i> {lng p="grommunio_device_wipe"}
						</a>
					</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
	{/if}

	{* Client Downloads *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-download"></i> Client Downloads:</td>
		<td class="listTableRight">
			<strong>Outlook Desktop:</strong><br />
			<a href="https://www.microsoft.com/de-de/microsoft-365/outlook/email-and-calendar-software-microsoft-outlook" target="_blank">
				<i class="fa fa-external-link"></i> Microsoft Outlook
			</a> (Teil von Microsoft 365)<br />
			<br />
			<strong>Outlook Mobile:</strong><br />
			<a href="https://apps.apple.com/de/app/microsoft-outlook/id951937596" target="_blank">
				<i class="fa fa-apple"></i> iOS App Store
			</a> |
			<a href="https://play.google.com/store/apps/details?id=com.microsoft.office.outlook" target="_blank">
				<i class="fa fa-android"></i> Google Play Store
			</a>
		</td>
	</tr>

	{* Vorteile *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-star"></i> Vorteile:</td>
		<td class="listTableRight">
			✅ Native Outlook-Integration (kein Plugin!)<br />
			✅ Offline-Modus (Cached Exchange Mode)<br />
			✅ Automatische Konfiguration (AutoDiscover)<br />
			✅ Push-Notifications auf Mobile<br />
			✅ Kalender & Kontakte-Sync<br />
			✅ Remote-Wipe für verlorene Geräte<br />
			✅ Kompatibel mit allen Exchange-Clients
		</td>
	</tr>

	{* Hinweis zu anderen Protokollen *}
	<tr>
		<td class="listTableLeft"><i class="fa fa-info-circle"></i> Hinweis:</td>
		<td class="listTableRight">
			<div style="padding: 10px; background-color: #fff3e0; border-left: 3px solid #ff9800;">
				<strong>Alternative Protokolle:</strong><br />
				Sie können auch weiterhin IMAP/POP3 nutzen (via Cyrus).<br />
				Für Outlook empfehlen wir jedoch MAPI für die beste Erfahrung.
			</div>
		</td>
	</tr>
</table>
<br />
{/if}

{*
 * ============================================================================
 * ZUSÄTZLICHE SMARTY-VARIABLEN FÜR bms.user.tpl
 * ============================================================================
 * 
 * In b1gmailserver.plugin.php, Methode UserPage():
 *
 * $tpl->assign('haveGrommunio', defined('GROMMUNIO_ENABLED') && GROMMUNIO_ENABLED);
 * 
 * // Grommunio Config
 * if(defined('GROMMUNIO_ENABLED') && GROMMUNIO_ENABLED)
 * {
 *     $bms_prefs['user_grommunio_server'] = GROMMUNIO_SERVER;
 *     $bms_prefs['user_mapi_url'] = GROMMUNIO_MAPI_URL;
 *     $bms_prefs['user_ews_url'] = GROMMUNIO_EWS_URL;
 *     $bms_prefs['user_eas_url'] = GROMMUNIO_EAS_URL;
 *     $bms_prefs['user_autodiscover_url'] = GROMMUNIO_AUTODISCOVER_URL;
 * }
 * 
 * ============================================================================
 *}
