{*
 * ============================================================================
 * Grommunio Admin Template
 * ============================================================================
 * Analog zu: bms.admin.cyrus.tpl
 * ============================================================================
 *}

{if $_tplname=='modern'}
<div id="contentHeader">
	<div class="left">
		<i class="fa fa-exchange" aria-hidden="true"></i>
		Grommunio (Outlook/Exchange)
	</div>
</div>

<div class="scrollContainer"><div class="pad">
{else}
<h1><i class="fa fa-exchange" aria-hidden="true"></i> Grommunio (Outlook/Exchange)</h1>
{/if}

{* Connection Status *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-plug"></i> Verbindungs-Status
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Grommunio Server:</td>
		<td class="listTableRight">
			{if $grommunio_stats.connected}
				<span style="color: green;"><i class="fa fa-check-circle"></i> <strong>Verbunden</strong></span><br />
				<small>Version: {$grommunio_stats.version}</small>
			{else}
				<span style="color: red;"><i class="fa fa-times-circle"></i> <strong>Getrennt</strong></span>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Protokolle:</td>
		<td class="listTableRight">
			<i class="fa fa-windows"></i> <strong>MAPI</strong> (Outlook Desktop)<br />
			<i class="fa fa-cloud"></i> <strong>EWS</strong> (Exchange Web Services)<br />
			<i class="fa fa-mobile"></i> <strong>EAS</strong> (ActiveSync Mobile)<br />
			<i class="fa fa-link"></i> <strong>RPC</strong> (Outlook Anywhere)
		</td>
	</tr>
</table>
<br />

{* Migration Status *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-exchange"></i> Migrations-Status
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Gesamt Benutzer:</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.total_users|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Migriert:</td>
		<td class="listTableRight">
			<span style="color: green;"><strong>{$grommunio_stats.migrated_users|number_format}</strong></span>
			({if $grommunio_stats.total_users > 0}{($grommunio_stats.migrated_users / $grommunio_stats.total_users * 100)|string_format:"%.1f"}{else}0{/if}%)
			<div style="width: 100%; height: 20px; background-color: #e0e0e0; margin-top: 5px;">
				<div style="width: {if $grommunio_stats.total_users > 0}{($grommunio_stats.migrated_users / $grommunio_stats.total_users * 100)|string_format:"%.1f"}{else}0{/if}%; height: 20px; background-color: #4CAF50;"></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Ausstehend:</td>
		<td class="listTableRight">
			<span style="color: orange;"><strong>{$grommunio_stats.pending_users|number_format}</strong></span>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Fehlgeschlagen:</td>
		<td class="listTableRight">
			{if $grommunio_stats.failed_users > 0}
				<span style="color: red;"><strong>{$grommunio_stats.failed_users|number_format}</strong></span>
			{else}
				<span style="color: green;">0</span>
			{/if}
		</td>
	</tr>
</table>
<br />

{* Active Sessions *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-users"></i> Aktive Sessions (letzte Stunde)
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Gesamt aktive Sessions:</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.active_sessions|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft"><i class="fa fa-windows"></i> MAPI (Outlook Desktop):</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.mapi_sessions|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft"><i class="fa fa-cloud"></i> EWS (Web Services):</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.ews_sessions|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft"><i class="fa fa-mobile"></i> EAS (Mobile Sync):</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.eas_sessions|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft"><i class="fa fa-link"></i> RPC (Outlook Anywhere):</td>
		<td class="listTableRight">
			<strong>{$grommunio_stats.rpc_sessions|number_format}</strong>
		</td>
	</tr>
</table>
<br />

{* ActiveSync Devices *}
{if $grommunio_devices|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="6">
			<i class="fa fa-mobile"></i> ActiveSync Geräte (Top 20)
		</th>
	</tr>
	<tr>
		<th>Benutzer</th>
		<th>Gerät</th>
		<th>Typ/Modell</th>
		<th>Betriebssystem</th>
		<th>Letzte Sync</th>
		<th>Aktionen</th>
	</tr>
	{foreach from=$grommunio_devices item=device}
	<tr>
		<td>{$device.email}</td>
		<td><small><code>{$device.device_id|truncate:20}</code></small></td>
		<td>{$device.device_type} / {$device.device_model}</td>
		<td>{$device.device_os}</td>
		<td>{$device.last_sync|date_format:"%d.%m.%Y %H:%M"}</td>
		<td>
			<a href="admin.php?page=bms&action=grommunio&remove_device={$device.id}" class="btn btn-xs btn-danger" 
			   onclick="return confirm('Gerät wirklich entfernen?')">
				<i class="fa fa-trash"></i> Entfernen
			</a>
			<a href="admin.php?page=bms&action=grommunio&wipe_device={$device.id}" class="btn btn-xs btn-warning"
			   onclick="return confirm('Gerät wirklich remote löschen?')">
				<i class="fa fa-eraser"></i> Wipe
			</a>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Recent Errors *}
{if $grommunio_errors|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="6">
			<i class="fa fa-exclamation-triangle"></i> Aktuelle Fehler
		</th>
	</tr>
	<tr>
		<th>Zeitstempel</th>
		<th>Benutzer</th>
		<th>Protokoll</th>
		<th>Operation</th>
		<th>Fehler</th>
		<th>Aktion</th>
	</tr>
	{foreach from=$grommunio_errors item=error}
	<tr>
		<td>{$error.timestamp|date_format:"%d.%m.%Y %H:%M"}</td>
		<td>{$error.email}</td>
		<td><span class="badge">{$error.protocol}</span></td>
		<td><code>{$error.operation}</code></td>
		<td><small>{$error.error_message|truncate:100}</small></td>
		<td>
			<a href="admin.php?page=bms&action=grommunio&resolve={$error.id}" class="btn btn-xs">
				<i class="fa fa-check"></i> Beheben
			</a>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Active Migrations *}
{if $grommunio_migrations|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="5">
			<i class="fa fa-spinner"></i> Laufende Migrationen
		</th>
	</tr>
	<tr>
		<th>Benutzer</th>
		<th>Status</th>
		<th>Gestartet</th>
		<th>Versuche</th>
		<th>Fehler</th>
	</tr>
	{foreach from=$grommunio_migrations item=migration}
	<tr>
		<td>{$migration.email}</td>
		<td>
			{if $migration.status == 'in_progress'}
				<span style="color: blue;"><i class="fa fa-spinner fa-spin"></i> In Arbeit</span>
			{elseif $migration.status == 'failed'}
				<span style="color: red;"><i class="fa fa-times"></i> Fehlgeschlagen</span>
			{elseif $migration.status == 'completed'}
				<span style="color: green;"><i class="fa fa-check"></i> Abgeschlossen</span>
			{else}
				<span style="color: gray;"><i class="fa fa-clock-o"></i> Ausstehend</span>
			{/if}
		</td>
		<td>{$migration.started|date_format:"%d.%m.%Y %H:%M"}</td>
		<td>{$migration.retry_count}</td>
		<td><small>{$migration.error|truncate:50}</small></td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Actions *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-cogs"></i> Aktionen
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Migration:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=grommunio&do=start_migration" class="btn btn-primary">
				<i class="fa fa-play"></i> Migration starten
			</a>
			<a href="admin.php?page=bms&action=grommunio&do=retry_failed" class="btn btn-info">
				<i class="fa fa-refresh"></i> Fehlgeschlagene wiederholen
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Synchronisation:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=grommunio&do=sync_all" class="btn btn-success">
				<i class="fa fa-sync"></i> Alle Mailboxen synchronisieren
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Logs:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=grommunio&do=view_logs" class="btn btn-secondary">
				<i class="fa fa-file-text"></i> Migrations-Log anzeigen
			</a>
		</td>
	</tr>
</table>
<br />

{* System Info *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-info-circle"></i> System-Information
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Grommunio Version:</td>
		<td class="listTableRight">
			<code>{$grommunio_stats.version}</code>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Unterstützte Clients:</td>
		<td class="listTableRight">
			<strong>Outlook Desktop</strong> (2013, 2016, 2019, 2021, 365) via MAPI<br />
			<strong>Outlook Mobile</strong> (iOS/Android) via EAS<br />
			<strong>Apple Mail</strong> via EWS/EAS<br />
			<strong>Android Mail</strong> via EAS<br />
			<strong>Windows Mail</strong> via EWS
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Dokumentation:</td>
		<td class="listTableRight">
			<a href="/GROMMUNIO_DEPLOYMENT_GUIDE.md" target="_blank">
				<i class="fa fa-book"></i> Deployment Guide
			</a>
		</td>
	</tr>
</table>

{if $_tplname=='modern'}
</div></div>
{/if}
