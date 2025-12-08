{*
 * b1gMailServer Admin - Postfix SMTP Detail Page
 * Queue Management, Stats, Settings
 *}

<h2><i class="fa fa-envelope"></i> Postfix SMTP - Verwaltung</h2>

{* Connection Status *}
<fieldset>
	<legend>Connection Status</legend>
	<div class="alert alert-{if $postfix_connection.success}success{else}danger{/if}">
		<h5>
			<i class="fa {if $postfix_connection.success}fa-check-circle{else}fa-exclamation-triangle{/if}"></i>
			{if $postfix_connection.success}Verbindung erfolgreich{else}Verbindung fehlgeschlagen{/if}
		</h5>
		{if $postfix_connection.banner}
			<p><strong>Banner:</strong> <code>{$postfix_connection.banner}</code></p>
		{/if}
		{if $postfix_connection.message}
			<p>{$postfix_connection.message}</p>
		{/if}
	</div>
</fieldset>

{* Statistics *}
<fieldset>
	<legend>Statistiken</legend>
	<div class="row">
		<div class="col-md-3">
			<div class="card bg-primary text-white">
				<div class="card-body">
					<h3>{$smtp_stats.today_sent|number_format:0:",":"."}</h3>
					<p>Gesendet heute</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card bg-success text-white">
				<div class="card-body">
					<h3>{$smtp_stats.today_received|number_format:0:",":"."}</h3>
					<p>Empfangen heute</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card bg-warning text-white">
				<div class="card-body">
					<h3>{$smtp_stats.today_deferred|number_format:0:",":"."}</h3>
					<p>Verzögert heute</p>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card bg-danger text-white">
				<div class="card-body">
					<h3>{$smtp_stats.today_bounced|number_format:0:",":"."}</h3>
					<p>Bounced heute</p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row mt-3">
		<div class="col-md-4">
			<div class="list-group">
				<div class="list-group-item">
					<strong>Diese Woche:</strong> {$smtp_stats.week_sent|number_format:0:",":"."} gesendet
				</div>
				<div class="list-group-item">
					<strong>Diesen Monat:</strong> {$smtp_stats.month_sent|number_format:0:",":"."} gesendet
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="list-group">
				<div class="list-group-item">
					<strong>Queue Active:</strong> {$smtp_stats.queue_active}
				</div>
				<div class="list-group-item">
					<strong>Queue Deferred:</strong> {$smtp_stats.queue_deferred}
				</div>
				<div class="list-group-item">
					<strong>Queue Hold:</strong> {$smtp_stats.queue_hold}
				</div>
			</div>
		</div>
	</div>
</fieldset>

{* Queue Management *}
<fieldset>
	<legend>Queue Verwaltung</legend>
	
	{if $postfix_queue_status.available}
		<div class="alert alert-info">
			<strong>Postfix Queue Status:</strong> {$postfix_queue_status.message}
		</div>
		
		<div class="btn-group mb-3">
			<button type="button" class="btn btn-primary" onclick="flushQueue()">
				<i class="fa fa-refresh"></i> Queue Flushen
			</button>
			<button type="button" class="btn btn-warning" onclick="reloadQueueList()">
				<i class="fa fa-reload"></i> Liste neu laden
			</button>
		</div>
	{else}
		<div class="alert alert-warning">
			<i class="fa fa-warning"></i> Postfix Queue-Tools nicht verfügbar: {$postfix_queue_status.message}
		</div>
	{/if}
	
	{if $queue_messages}
		<table class="list">
			<thead>
				<tr>
					<th>Queue-ID</th>
					<th>Von</th>
					<th>An</th>
					<th>Größe</th>
					<th>Status</th>
					<th>Hinzugefügt</th>
					<th>Aktionen</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$queue_messages item=message}
				<tr>
					<td><code>{$message.queue_id}</code></td>
					<td>{$message.sender}</td>
					<td>{$message.recipient}</td>
					<td>{$message.size|formatsize}</td>
					<td>
						{if $message.status == 'active'}
							<span class="badge badge-success">Aktiv</span>
						{elseif $message.status == 'deferred'}
							<span class="badge badge-warning">Verzögert</span>
						{else}
							<span class="badge badge-secondary">{$message.status}</span>
						{/if}
					</td>
					<td>{$message.added|date_format:"%d.%m.%Y %H:%M"}</td>
					<td>
						<button class="btn btn-sm btn-danger" onclick="deleteQueueMessage('{$message.queue_id}')">
							<i class="fa fa-trash"></i> Löschen
						</button>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	{else}
		<div class="alert alert-info">
			<i class="fa fa-info-circle"></i> Keine Nachrichten in der Queue
		</div>
	{/if}
</fieldset>

{* Settings *}
<fieldset>
	<legend>Einstellungen</legend>
	<form method="post" action="{$pageURL}&action=postfix&save=true">
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Postfix Server:</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" name="postfix_server" 
					value="{$postfix_config.server}" placeholder="localhost">
			</div>
		</div>
		
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">SMTP Port:</label>
			<div class="col-sm-3">
				<input type="number" class="form-control" name="smtp_port" 
					value="{$postfix_config.smtp_port}" placeholder="25">
			</div>
			<label class="col-sm-3 col-form-label">Submission Port:</label>
			<div class="col-sm-3">
				<input type="number" class="form-control" name="submission_port" 
					value="{$postfix_config.submission_port}" placeholder="587">
			</div>
		</div>
		
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">SMTPS Port:</label>
			<div class="col-sm-3">
				<input type="number" class="form-control" name="smtps_port" 
					value="{$postfix_config.smtps_port}" placeholder="465">
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-save"></i> Einstellungen speichern
				</button>
			</div>
		</div>
	</form>
</fieldset>

<script>
function flushQueue() {
	if(confirm('Queue wirklich flushen?')) {
		window.location.href = '{$pageURL}&action=postfix&do=flushQueue';
	}
}

function deleteQueueMessage(queueId) {
	if(confirm('Nachricht ' + queueId + ' wirklich löschen?')) {
		window.location.href = '{$pageURL}&action=postfix&do=deleteMessage&queueId=' + queueId;
	}
}

function reloadQueueList() {
	window.location.reload();
}
</script>
