<h2>⚙️ Universal Search - Einstellungen</h2>

<form action="{$pageURL}&action=settings&" method="post">
	<fieldset>
		<legend>Indexierungs-Optionen</legend>
		
		<table width="100%">
			<tr>
				<td class="td1" width="300">E-Mails indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_emails" value="1" {if $settings.index_emails}checked{/if}>
					<small>Indexiert Subject, Body, Absender, Empfänger, Anhänge</small>
				</td>
			</tr>
			<tr>
				<td class="td1">WebDisk-Dateien indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_files" value="1" {if $settings.index_files}checked{/if}>
					<small>Indexiert Dateinamen, Typ, Größe</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Kalender indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_calendar" value="1" {if $settings.index_calendar}checked{/if}>
					<small>Indexiert Termine, Beschreibungen, Orte</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Kontakte indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_contacts" value="1" {if $settings.index_contacts}checked{/if}>
					<small>Indexiert Namen, E-Mails, Telefon, Adressen</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Notizen indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_notes" value="1" {if $settings.index_notes}checked{/if}>
					<small>Indexiert Notiz-Titel und Inhalt</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Aufgaben indexieren:</td>
				<td class="td2">
					<input type="checkbox" name="index_tasks" value="1" {if $settings.index_tasks}checked{/if}>
					<small>Indexiert Aufgaben-Titel und Beschreibungen</small>
				</td>
			</tr>
		</table>
	</fieldset>
	
	<fieldset>
		<legend>Such-Optionen</legend>
		
		<table width="100%">
			<tr>
				<td class="td1" width="300">Fuzzy Search (Tippfehler-Toleranz):</td>
				<td class="td2">
					<input type="checkbox" name="fuzzy_search" value="1" {if $settings.fuzzy_search}checked{/if}>
					<small>Findet auch Ergebnisse mit Tippfehlern</small>
				</td>
			</tr>
			<tr>
				<td class="td1">Real-time Indexing:</td>
				<td class="td2">
					<input type="checkbox" name="realtime_indexing" value="1" {if $settings.realtime_indexing}checked{/if}>
					<small>Neue Inhalte werden sofort indexiert</small>
				</td>
			</tr>
		</table>
	</fieldset>
	
	<fieldset>
		<legend>Compliance (TKÜV/DSGVO)</legend>
		
		<table width="100%">
			<tr>
				<td class="td1" width="300">Audit-Logging (TKÜV):</td>
				<td class="td2">
					<input type="checkbox" name="audit_logging" value="1" {if $settings.audit_logging}checked{/if}>
					<small>Loggt alle Suchanfragen (TKÜV-Pflicht bei Überwachung)</small>
				</td>
			</tr>
		</table>
		
		<div class="alert alert-warning mt-3">
			<strong>⚖️ TKÜV-Hinweis:</strong> Audit-Logging sollte aktiviert bleiben für Compliance!
		</div>
	</fieldset>
	
	<p align="right">
		<input class="button" type="submit" name="save" value=" ✓ Einstellungen speichern ">
	</p>
</form>

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

