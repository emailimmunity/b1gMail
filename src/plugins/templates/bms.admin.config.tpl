{*
 * ============================================================================
 * Protokoll-Konfiguration Admin Template
 * ============================================================================
 *}

{if $_tplname=='modern'}
<div id="contentHeader">
	<div class="left">
		<i class="fa fa-cogs" aria-hidden="true"></i>
		Protokoll-Konfiguration
	</div>
</div>

<div class="scrollContainer"><div class="pad">
{else}
<h1><i class="fa fa-cogs" aria-hidden="true"></i> Protokoll-Konfiguration</h1>
{/if}

{* Nachrichten *}
{if isset($smarty.get.message)}
<div class="alert alert-success">
	<i class="fa fa-check"></i> {$smarty.get.message}
</div>
{/if}

{if isset($smarty.get.error)}
<div class="alert alert-danger">
	<i class="fa fa-exclamation-triangle"></i> {$smarty.get.error}
</div>
{/if}

{* Kategorie-Tabs *}
<ul class="nav nav-tabs">
	<li class="{if $current_category == 'cyrus'}active{/if}">
		<a href="{$pageURL}action=config&cat=cyrus">
			<i class="fa fa-envelope"></i> Cyrus IMAP
		</a>
	</li>
	<li class="{if $current_category == 'postfix'}active{/if}">
		<a href="{$pageURL}action=config&cat=postfix">
			<i class="fa fa-paper-plane"></i> Postfix SMTP
		</a>
	</li>
	<li class="{if $current_category == 'grommunio'}active{/if}">
		<a href="{$pageURL}action=config&cat=grommunio">
			<i class="fa fa-exchange"></i> Grommunio
		</a>
	</li>
	<li class="{if $current_category == 'sftpgo'}active{/if}">
		<a href="{$pageURL}action=config&cat=sftpgo">
			<i class="fa fa-cloud"></i> SFTPGo
		</a>
	</li>
</ul>

<br />

{* Config-Quellen Info *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-info-circle"></i> Konfigurations-Quellen
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Umgebungsvariablen:</td>
		<td class="listTableRight">
			{if isset($config_sources.env)}
				<span style="color: green;"><strong>{$config_sources.env.count} gefunden</strong></span>
				<br /><small>🔒 Nicht editierbar (höchste Priorität)</small>
			{else}
				<span style="color: gray;">Keine gesetzt</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Lokale Config-Datei:</td>
		<td class="listTableRight">
			{if isset($config_sources.file)}
				<span style="color: green;"><strong>Aktiv</strong></span>
				<br /><small>{$config_sources.file.count} Einstellungen</small>
			{else}
				<span style="color: gray;">Nicht vorhanden</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Datenbank:</td>
		<td class="listTableRight">
			{if isset($config_sources.db)}
				<span style="color: green;"><strong>Aktiv</strong></span>
				<br /><small>{$config_sources.db.count} Einstellungen gespeichert</small>
			{else}
				<span style="color: gray;">Leer</span>
			{/if}
		</td>
	</tr>
</table>

<br />

{* Konfigurations-Formular *}
<form method="post" action="{$pageURL}action=config&do=save&cat={$current_category}">
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="4">
			<i class="fa fa-cog"></i>
			{if $current_category == 'cyrus'}Cyrus IMAP/POP3/JMAP Konfiguration
			{elseif $current_category == 'postfix'}Postfix SMTP Konfiguration
			{elseif $current_category == 'grommunio'}Grommunio MAPI/EWS/EAS Konfiguration
			{elseif $current_category == 'sftpgo'}SFTPGo SFTP/FTPS/S3 Konfiguration
			{/if}
		</th>
	</tr>
	<tr>
		<th>Einstellung</th>
		<th>Wert</th>
		<th>Quelle</th>
		<th>Status</th>
	</tr>
	
	{foreach from=$protocol_config[$current_category] key=config_key item=config}
	<tr>
		<td class="listTableLeft">
			<strong>{$config.label}</strong><br />
			<small><code>{$config_key}</code></small>
		</td>
		<td class="listTableRight">
			{if $config.editable}
				{if $config.type == 'bool'}
					<select name="config_{$config_key}" class="form-control">
						<option value="yes" {if $config.value}selected{/if}>Ja</option>
						<option value="no" {if !$config.value}selected{/if}>Nein</option>
					</select>
					<input type="hidden" name="type_{$config_key}" value="bool" />
				{elseif $config.type == 'password'}
					<input type="password" name="config_{$config_key}" value="{$config.value}" class="form-control" />
					<input type="hidden" name="type_{$config_key}" value="string" />
				{elseif $config.type == 'int'}
					<input type="number" name="config_{$config_key}" value="{$config.value}" class="form-control" />
					<input type="hidden" name="type_{$config_key}" value="int" />
				{else}
					<input type="text" name="config_{$config_key}" value="{$config.value}" class="form-control" />
					<input type="hidden" name="type_{$config_key}" value="string" />
				{/if}
			{else}
				<input type="text" value="{$config.value}" class="form-control" disabled />
				<small style="color: #999;">Wird von {$config.source_detail} überschrieben</small>
			{/if}
		</td>
		<td>
			{if $config.source == 'env'}
				<span class="badge" style="background-color: #2196F3;">🌍 Umgebung</span>
				<br /><small>{$config.env_var}</small>
			{elseif $config.source == 'file'}
				<span class="badge" style="background-color: #FF9800;">📁 Datei</span>
			{elseif $config.source == 'db'}
				<span class="badge" style="background-color: #4CAF50;">💾 Datenbank</span>
			{else}
				<span class="badge" style="background-color: #9E9E9E;">⚙️ Default</span>
			{/if}
		</td>
		<td>
			{if $config.editable}
				<span style="color: green;"><i class="fa fa-edit"></i> Editierbar</span>
			{else}
				<span style="color: #999;"><i class="fa fa-lock"></i> Schreibgeschützt</span>
			{/if}
		</td>
	</tr>
	{/foreach}
</table>

<br />

<div class="btn-toolbar">
	<button type="submit" class="btn btn-primary">
		<i class="fa fa-save"></i> Speichern
	</button>
	
	<button type="button" class="btn btn-info" onclick="testConnection()">
		<i class="fa fa-plug"></i> Verbindung testen
	</button>
	
	<a href="{$pageURL}action=config&do=reset&cat={$current_category}" 
	   class="btn btn-warning"
	   onclick="return confirm('Wirklich alle Einstellungen zurücksetzen?')">
		<i class="fa fa-undo"></i> Zurücksetzen
	</a>
</div>
</form>

<br />

{* Backup/Restore *}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-download"></i> Backup & Restore
		</th>
	</tr>
	<tr>
		<td class="listTableLeft">Backup erstellen:</td>
		<td class="listTableRight">
			<a href="{$pageURL}action=config&do=backup" class="btn btn-success">
				<i class="fa fa-download"></i> Config als JSON exportieren
			</a>
		</td>
	</tr>
	<tr>
		<td class="listTableLeft">Backup wiederherstellen:</td>
		<td class="listTableRight">
			<form method="post" action="{$pageURL}action=config&do=restore" enctype="multipart/form-data" style="display: inline;">
				<input type="file" name="config_file" accept=".json" required />
				<button type="submit" class="btn btn-warning">
					<i class="fa fa-upload"></i> Config importieren
				</button>
			</form>
		</td>
	</tr>
</table>

{* JavaScript für Connection Test *}
<script>
function testConnection() {
	var category = '{$current_category}';
	
	// Spinner anzeigen
	var btn = event.target;
	btn.disabled = true;
	btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Teste...';
	
	// AJAX Request
	fetch('{$pageURL}action=config&do=test&cat=' + category)
		.then(response => response.json())
		.then(data => {
			btn.disabled = false;
			btn.innerHTML = '<i class="fa fa-plug"></i> Verbindung testen';
			
			if(data.success) {
				alert('✅ Verbindung erfolgreich!\n\n' + data.message + '\n\n' + data.details.join('\n'));
			} else {
				alert('❌ Verbindung fehlgeschlagen!\n\n' + data.message);
			}
		})
		.catch(error => {
			btn.disabled = false;
			btn.innerHTML = '<i class="fa fa-plug"></i> Verbindung testen';
			alert('❌ Fehler: ' + error);
		});
}
</script>

{if $_tplname=='modern'}
</div></div>
{/if}
