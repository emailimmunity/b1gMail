<h2>🔄 Universal Search - Neu indexieren</h2>

{if isset($reindex_stats)}
<div class="alert alert-success">
	<h5><i class="fa fa-check-circle"></i> Indexierung gestartet!</h5>
	<p>Folgende Elemente wurden in die Warteschlange eingereiht:</p>
	<ul>
		{if $reindex_stats.emails > 0}<li><strong>E-Mails:</strong> {$reindex_stats.emails}</li>{/if}
		{if $reindex_stats.files > 0}<li><strong>Dateien:</strong> {$reindex_stats.files}</li>{/if}
		{if $reindex_stats.calendar > 0}<li><strong>Kalender:</strong> {$reindex_stats.calendar}</li>{/if}
		{if $reindex_stats.contacts > 0}<li><strong>Kontakte:</strong> {$reindex_stats.contacts}</li>{/if}
	</ul>
	<p><small>Die Indexierung läuft im Hintergrund via Cron-Job. Dies kann einige Minuten dauern.</small></p>
</div>
{/if}

<form action="{$pageURL}&action=reindex&" method="post">
	<fieldset>
		<legend>Benutzer neu indexieren</legend>
		
		<table width="100%">
			<tr>
				<td class="td1" width="200">Benutzer:</td>
				<td class="td2">
					<select name="userid" required class="form-control">
						<option value="">-- Benutzer auswählen --</option>
						{foreach from=$users item=user}
							<option value="{$user.id}">{$user.email} (ID: {$user.id})</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td class="td1">Was indexieren:</td>
				<td class="td2">
					<select name="type" class="form-control">
						<option value="all">Alles (E-Mails, Dateien, Kalender, Kontakte)</option>
						<option value="emails">Nur E-Mails</option>
						<option value="files">Nur WebDisk-Dateien</option>
						<option value="calendar">Nur Kalender</option>
						<option value="contacts">Nur Kontakte</option>
					</select>
				</td>
			</tr>
		</table>
		
		<p align="right">
			<input class="button" type="submit" name="reindex_user" value=" 🔄 Neu indexieren ">
		</p>
	</fieldset>
</form>

<div class="alert alert-info">
	<h5><i class="fa fa-info-circle"></i> Wann neu indexieren?</h5>
	<ul>
		<li>Nach der ersten Installation</li>
		<li>Wenn Suchergebnisse fehlen</li>
		<li>Nach größeren Daten-Importen</li>
		<li>Bei Problemen mit der Suche</li>
	</ul>
</div>

<style>
.td1 {
	background: #f9f9f9;
	font-weight: 600;
	padding: 10px;
}

.td2 {
	background: #fff;
	padding: 10px;
}

.form-control {
	width: 100%;
	padding: 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.button {
	background: #4CAF50;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 4px;
	cursor: pointer;
	font-weight: bold;
}

.button:hover {
	background: #45a049;
}
</style>

