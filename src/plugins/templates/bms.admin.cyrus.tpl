{*
 * ============================================================================
 * Cyrus IMAP Admin Template
 * ============================================================================
 * Analog zu: DAV Stats, Server Stats
 * ============================================================================
 *}

{if $_tplname=='modern'}
<div id="contentHeader">
	<div class="left">
		<i class="fa fa-server" aria-hidden="true"></i>
		Cyrus IMAP Status
	</div>
</div>

<div class="scrollContainer"><div class="pad">
{else}
<h1><i class="fa fa-server" aria-hidden="true"></i> Cyrus IMAP Status</h1>
{/if}

{* Connection Status *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-plug"></i> Verbindungs-Status
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Cyrus IMAP Server:</td>
		<td class="listTableRight">
			{if $cyrus_stats.connection == 'success'}
				<span style="color: green;"><i class="fa fa-check-circle"></i> <strong>Verbunden</strong></span>
			{else}
				<span style="color: red;"><i class="fa fa-times-circle"></i> <strong>Getrennt</strong></span>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">JMAP Status:</td>
		<td class="listTableRight">
			{if $cyrus_stats.jmap_enabled}
				<span style="color: green;"><i class="fa fa-bolt"></i> <strong>Aktiviert</strong></span><br />
				<small>URL: <code>{$cyrus_stats.jmap_url}</code></small>
			{else}
				<span style="color: orange;"><i class="fa fa-minus-circle"></i> Deaktiviert</span>
			{/if}
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
			<strong>{$cyrus_stats.total_users|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Migriert:</td>
		<td class="listTableRight">
			<span style="color: green;"><strong>{$cyrus_stats.migrated_users|number_format}</strong></span>
			({if $cyrus_stats.total_users > 0}{($cyrus_stats.migrated_users / $cyrus_stats.total_users * 100)|string_format:"%.1f"}{else}0{/if}%)
			<div style="width: 100%; height: 20px; background-color: #e0e0e0; margin-top: 5px;">
				<div style="width: {if $cyrus_stats.total_users > 0}{($cyrus_stats.migrated_users / $cyrus_stats.total_users * 100)|string_format:"%.1f"}{else}0{/if}%; height: 20px; background-color: #4CAF50;"></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Ausstehend:</td>
		<td class="listTableRight">
			<span style="color: orange;"><strong>{$cyrus_stats.pending_users|number_format}</strong></span>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Fehlgeschlagen:</td>
		<td class="listTableRight">
			{if $cyrus_stats.failed_users > 0}
				<span style="color: red;"><strong>{$cyrus_stats.failed_users|number_format}</strong></span>
			{else}
				<span style="color: green;">0</span>
			{/if}
		</td>
	</tr>
</table>
<br />

{* Mailbox Statistics *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-inbox"></i> Mailbox-Statistiken
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Aktive Mailboxen:</td>
		<td class="listTableRight">
			<strong>{$cyrus_stats.total_mailboxes|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Gesamt Nachrichten:</td>
		<td class="listTableRight">
			<strong>{$cyrus_stats.total_messages|number_format}</strong>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Quota verwendet:</td>
		<td class="listTableRight">
			<strong>{($cyrus_stats.total_quota_used / 1024 / 1024)|string_format:"%.2f"} GB</strong>
			/ {($cyrus_stats.total_quota_limit / 1024 / 1024)|string_format:"%.2f"} GB
			({if $cyrus_stats.total_quota_limit > 0}{($cyrus_stats.total_quota_used / $cyrus_stats.total_quota_limit * 100)|string_format:"%.1f"}{else}0{/if}%)
		</td>
	</tr>
</table>
<br />

{* Recent Errors *}
{if $cyrus_errors|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="5">
			<i class="fa fa-exclamation-triangle"></i> Aktuelle Fehler
		</th>
	</tr>
	<tr>
		<th>Zeitstempel</th>
		<th>Benutzer</th>
		<th>Operation</th>
		<th>Fehler</th>
		<th>Aktion</th>
	</tr>
	{foreach from=$cyrus_errors item=error}
	<tr>
		<td>{$error.timestamp|date_format:"%d.%m.%Y %H:%M"}</td>
		<td>{$error.email}</td>
		<td><code>{$error.operation}</code></td>
		<td><small>{$error.error_message|truncate:100}</small></td>
		<td>
			<a href="admin.php?page=bms&action=cyrus&resolve={$error.id}" class="btn btn-xs">
				<i class="fa fa-check"></i> Beheben
			</a>
		</td>
	</tr>
	{/foreach}
</table>
<br />
{/if}

{* Active Migrations *}
{if $cyrus_migrations|@count > 0}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="6">
			<i class="fa fa-spinner"></i> Laufende Migrationen
		</th>
	</tr>
	<tr>
		<th>Benutzer</th>
		<th>Status</th>
		<th>Gestartet</th>
		<th>Nachrichten</th>
		<th>Versuche</th>
		<th>Fehler</th>
	</tr>
	{foreach from=$cyrus_migrations item=migration}
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
		<td>{$migration.messages_migrated} / {$migration.messages_total}</td>
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
			<a href="admin.php?page=bms&action=cyrus&do=start_migration" class="btn btn-primary">
				<i class="fa fa-play"></i> Migration starten
			</a>
			<a href="admin.php?page=bms&action=cyrus&do=pause_migration" class="btn btn-warning">
				<i class="fa fa-pause"></i> Pausieren
			</a>
			<a href="admin.php?page=bms&action=cyrus&do=retry_failed" class="btn btn-info">
				<i class="fa fa-refresh"></i> Fehlgeschlagene wiederholen
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Synchronisation:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=cyrus&do=sync_all" class="btn btn-success">
				<i class="fa fa-sync"></i> Alle Mailboxen synchronisieren
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Logs:</td>
		<td class="listTableRight">
			<a href="admin.php?page=bms&action=cyrus&do=view_logs" class="btn btn-secondary">
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
		<td class="listTableLeft">Cyrus Version:</td>
		<td class="listTableRight">
			<code>Cyrus IMAP 3.8+</code>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Postfix Status:</td>
		<td class="listTableRight">
			<span style="color: green;"><i class="fa fa-check"></i> Läuft</span>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Dokumentation:</td>
		<td class="listTableRight">
			<a href="/CYRUS_DEPLOYMENT_COMPLETE_GUIDE.md" target="_blank">
				<i class="fa fa-book"></i> Deployment Guide
			</a>
		</td>
	</tr>
</table>

{if $_tplname=='modern'}
</div></div>
{/if}
