{*
 * ============================================================================
 * Modern Statistics Dashboard
 * Vereinheitlichtes Dashboard für alle Protokolle
 * ============================================================================
 *}

{if $_tplname=='modern'}
<div id="contentHeader">
	<div class="left">
		<i class="fa fa-tachometer" aria-hidden="true"></i>
		Protocol Dashboard
	</div>
</div>

<div class="scrollContainer"><div class="pad">
{else}
<h1><i class="fa fa-tachometer" aria-hidden="true"></i> Protocol Dashboard</h1>
{/if}

{* Overall Summary *}
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
	<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
		<div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
			<i class="fa fa-plug"></i> Total Connections Today
		</div>
		<div style="font-size: 36px; font-weight: bold;">
			{$summary.total_connections_today|number_format}
		</div>
	</div>
	
	<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
		<div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
			<i class="fa fa-envelope"></i> Emails Sent/Received
		</div>
		<div style="font-size: 36px; font-weight: bold;">
			{$summary.total_emails_today|number_format}
		</div>
	</div>
	
	<div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
		<div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
			<i class="fa fa-database"></i> Storage Used
		</div>
		<div style="font-size: 36px; font-weight: bold;">
			{($summary.total_storage_mb / 1024)|string_format:"%.1f"} GB
		</div>
	</div>
	
	<div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
		<div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
			<i class="fa fa-exchange"></i> Traffic Today
		</div>
		<div style="font-size: 36px; font-weight: bold;">
			{($summary.total_traffic_mb_today / 1024)|string_format:"%.1f"} GB
		</div>
	</div>
</div>

{* Connection Status Grid *}
<h2><i class="fa fa-server"></i> Service Status</h2>
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
	
	{* Cyrus Status *}
	<div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
		<h3 style="margin-top: 0; color: #667eea;">
			<i class="fa fa-inbox"></i> Cyrus IMAP/POP3
		</h3>
		<table style="width: 100%;">
			<tr>
				<td>Connection:</td>
				<td style="text-align: right;">
					{if $connections.cyrus.connected}
						<span style="color: green;"><i class="fa fa-check-circle"></i> Connected</span>
					{else}
						<span style="color: red;"><i class="fa fa-times-circle"></i> Offline</span>
					{/if}
				</td>
			</tr>
			<tr>
				<td>IMAP Connections:</td>
				<td style="text-align: right;"><strong>{$all_stats.cyrus.imap_connections_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>POP3 Connections:</td>
				<td style="text-align: right;"><strong>{$all_stats.cyrus.pop3_connections_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>Storage:</td>
				<td style="text-align: right;">{($all_stats.cyrus.storage_used_mb / 1024)|string_format:"%.2f"} GB</td>
			</tr>
		</table>
		<div style="margin-top: 15px;">
			<a href="?do=cyrus" class="btn btn-sm btn-primary">
				<i class="fa fa-arrow-right"></i> Details
			</a>
		</div>
	</div>
	
	{* Grommunio Status *}
	<div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
		<h3 style="margin-top: 0; color: #f5576c;">
			<i class="fa fa-exchange"></i> Grommunio MAPI/EWS
		</h3>
		<table style="width: 100%;">
			<tr>
				<td>Connection:</td>
				<td style="text-align: right;">
					{if $connections.grommunio.connected}
						<span style="color: green;"><i class="fa fa-check-circle"></i> Connected</span>
					{else}
						<span style="color: red;"><i class="fa fa-times-circle"></i> Offline</span>
					{/if}
				</td>
			</tr>
			<tr>
				<td>MAPI Connections:</td>
				<td style="text-align: right;"><strong>{$all_stats.grommunio.mapi_connections_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>EWS Requests:</td>
				<td style="text-align: right;"><strong>{$all_stats.grommunio.ews_requests_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>ActiveSync Devices:</td>
				<td style="text-align: right;">{$all_stats.grommunio.eas_devices|number_format}</td>
			</tr>
		</table>
		<div style="margin-top: 15px;">
			<a href="?do=grommunio" class="btn btn-sm btn-primary">
				<i class="fa fa-arrow-right"></i> Details
			</a>
		</div>
	</div>
	
	{* SFTPGo Status *}
	<div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
		<h3 style="margin-top: 0; color: #00f2fe;">
			<i class="fa fa-folder-open"></i> SFTPGo File Server
		</h3>
		<table style="width: 100%;">
			<tr>
				<td>Connection:</td>
				<td style="text-align: right;">
					{if $connections.sftpgo.api_connected}
						<span style="color: green;"><i class="fa fa-check-circle"></i> Connected</span>
					{else}
						<span style="color: red;"><i class="fa fa-times-circle"></i> Offline</span>
					{/if}
				</td>
			</tr>
			<tr>
				<td>SFTP Connections:</td>
				<td style="text-align: right;"><strong>{$all_stats.sftpgo.sftp_connections_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>Uploads Today:</td>
				<td style="text-align: right;">{$all_stats.sftpgo.uploads_mb_today|string_format:"%.1f"} MB</td>
			</tr>
			<tr>
				<td>Downloads Today:</td>
				<td style="text-align: right;">{$all_stats.sftpgo.downloads_mb_today|string_format:"%.1f"} MB</td>
			</tr>
		</table>
		<div style="margin-top: 15px;">
			<a href="?do=sftpgo" class="btn btn-sm btn-primary">
				<i class="fa fa-arrow-right"></i> Details
			</a>
		</div>
	</div>
	
	{* Postfix Status *}
	<div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
		<h3 style="margin-top: 0; color: #38f9d7;">
			<i class="fa fa-envelope-o"></i> Postfix SMTP
		</h3>
		<table style="width: 100%;">
			<tr>
				<td>Connection:</td>
				<td style="text-align: right;">
					{if $connections.postfix.connected}
						<span style="color: green;"><i class="fa fa-check-circle"></i> Connected</span>
					{else}
						<span style="color: red;"><i class="fa fa-times-circle"></i> Offline</span>
					{/if}
				</td>
			</tr>
			<tr>
				<td>Emails Sent:</td>
				<td style="text-align: right;"><strong>{$all_stats.postfix.emails_sent_today|number_format}</strong></td>
			</tr>
			<tr>
				<td>Queue Size:</td>
				<td style="text-align: right;">
					{if $all_stats.postfix.queue_size > 0}
						<span style="color: orange;">{$all_stats.postfix.queue_size|number_format}</span>
					{else}
						<span style="color: green;">0</span>
					{/if}
				</td>
			</tr>
			<tr>
				<td>Rejected Today:</td>
				<td style="text-align: right;">{$all_stats.postfix.emails_rejected_today|number_format}</td>
			</tr>
		</table>
		<div style="margin-top: 15px;">
			<a href="?do=postfix" class="btn btn-sm btn-primary">
				<i class="fa fa-arrow-right"></i> Details
			</a>
		</div>
	</div>
	
</div>

{* 30 Day Chart *}
<h2><i class="fa fa-line-chart"></i> 30 Day Trend</h2>
<div style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
	<canvas id="protocolChart" width="800" height="300"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('protocolChart').getContext('2d');
new Chart(ctx, {
	type: 'line',
	data: {
		labels: {$chart_data.labels|@json_encode},
		datasets: [
			{
				label: 'Cyrus Connections',
				data: {$chart_data.cyrus_connections|@json_encode},
				borderColor: '#667eea',
				backgroundColor: 'rgba(102, 126, 234, 0.1)',
				tension: 0.4
			},
			{
				label: 'Grommunio Connections',
				data: {$chart_data.grommunio_connections|@json_encode},
				borderColor: '#f5576c',
				backgroundColor: 'rgba(245, 87, 108, 0.1)',
				tension: 0.4
			},
			{
				label: 'SFTPGo Connections',
				data: {$chart_data.sftpgo_connections|@json_encode},
				borderColor: '#00f2fe',
				backgroundColor: 'rgba(0, 242, 254, 0.1)',
				tension: 0.4
			},
			{
				label: 'Postfix Emails Sent',
				data: {$chart_data.postfix_sent|@json_encode},
				borderColor: '#38f9d7',
				backgroundColor: 'rgba(56, 249, 215, 0.1)',
				tension: 0.4
			}
		]
	},
	options: {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				position: 'bottom'
			},
			title: {
				display: true,
				text: 'Connection Activity (Last 30 Days)'
			}
		},
		scales: {
			y: {
				beginAtZero: true
			}
		}
	}
});
</script>

{* Quick Actions *}
<h2><i class="fa fa-bolt"></i> Quick Actions</h2>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
	<a href="?do=cyrus&action=sync_all" class="btn btn-lg btn-success" style="padding: 20px; text-align: center;">
		<i class="fa fa-refresh fa-2x"></i><br />
		Sync All Cyrus
	</a>
	<a href="?do=grommunio&action=sync_all" class="btn btn-lg btn-primary" style="padding: 20px; text-align: center;">
		<i class="fa fa-cloud-upload fa-2x"></i><br />
		Sync Grommunio
	</a>
	<a href="?do=postfix&action=flush" class="btn btn-lg btn-warning" style="padding: 20px; text-align: center;">
		<i class="fa fa-paper-plane fa-2x"></i><br />
		Flush Queue
	</a>
	<a href="?do=stats" class="btn btn-lg btn-info" style="padding: 20px; text-align: center;">
		<i class="fa fa-bar-chart fa-2x"></i><br />
		Legacy Stats
	</a>
</div>

{if $_tplname=='modern'}
</div></div>
{/if}
