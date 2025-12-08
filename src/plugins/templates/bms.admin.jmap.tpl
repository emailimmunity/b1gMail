<div class="top">
	<a href="{$pageURL}"><img src="{$tpldir}images/bms_logo.png" border="0" alt="b1gMailServer" align="right" /></a>
	JMAP (Stalwart Mail Server)
</div>

<fieldset>
	<legend>Server Status</legend>
	
	<table width="100%">
		<tr>
			<td width="200"><b>Container Status:</b></td>
			<td>
				{if $containerStatus.running}
					<span style="color:green;font-weight:bold;">✅ RUNNING</span>
				{else}
					<span style="color:red;font-weight:bold;">❌ STOPPED</span>
				{/if}
			</td>
		</tr>
		
		{if $jmapStatus}
		<tr>
			<td><b>JMAP Server:</b></td>
			<td>
				{if $jmapStatus.online}
					<span style="color:green;font-weight:bold;">✅ ONLINE</span>
				{else}
					<span style="color:red;font-weight:bold;">❌ OFFLINE</span>
				{/if}
			</td>
		</tr>
		
		{if $jmapStatus.online}
		<tr>
			<td><b>Response Time:</b></td>
			<td>{$jmapStatus.response_time} ms</td>
		</tr>
		<tr>
			<td><b>Server Version:</b></td>
			<td>{$jmapStatus.version}</td>
		</tr>
		{else}
		<tr>
			<td><b>Error:</b></td>
			<td style="color:red;">{$jmapStatus.message}</td>
		</tr>
		{/if}
		{/if}
	</table>
</fieldset>

{if $jmapProtocol}
<fieldset>
	<legend>Protocol Configuration</legend>
	
	<table width="100%">
		<tr>
			<td width="200"><b>Host:</b></td>
			<td>{$jmapProtocol.server_host}</td>
		</tr>
		<tr>
			<td><b>Port:</b></td>
			<td>{$jmapProtocol.server_port}</td>
		</tr>
		<tr>
			<td><b>Path:</b></td>
			<td>{$jmapProtocol.server_path}</td>
		</tr>
		<tr>
			<td><b>SSL Type:</b></td>
			<td>{$jmapProtocol.ssl_type}</td>
		</tr>
		<tr>
			<td><b>Enabled:</b></td>
			<td>
				{if $jmapProtocol.enabled}
					<span style="color:green;">✅ Yes</span>
				{else}
					<span style="color:red;">❌ No</span>
				{/if}
			</td>
		</tr>
	</table>
</fieldset>
{/if}

{if $jmapStats}
<fieldset>
	<legend>Statistics</legend>
	
	<table width="100%">
		<tr>
			<td width="200"><b>Total Users:</b></td>
			<td>{$jmapStats.total_users}</td>
		</tr>
		<tr>
			<td><b>JMAP-Enabled Users:</b></td>
			<td>{$jmapStats.jmap_enabled_users}</td>
		</tr>
		<tr>
			<td><b>Total Mailboxes:</b></td>
			<td>{$jmapStats.total_mailboxes}</td>
		</tr>
		<tr>
			<td><b>Total Messages:</b></td>
			<td>{$jmapStats.total_messages}</td>
		</tr>
		<tr>
			<td><b>Active Sessions:</b></td>
			<td>{$jmapSessionsCount}</td>
		</tr>
	</table>
</fieldset>
{/if}

<fieldset>
	<legend>Quick Actions</legend>
	
	<table width="100%">
		<tr>
			<td width="200">
				<img src="{$tpldir}images/settings.png" border="0" align="absmiddle" alt="" />
				<b>Protocol Management</b>
			</td>
			<td>
				<a href="/admin/protocol_management.php?sid={$sid}">Manage all protocols (JMAP, CalDAV, IMAP, etc.)</a>
			</td>
		</tr>
		<tr>
			<td>
				<img src="{$tpldir}images/info.png" border="0" align="absmiddle" alt="" />
				<b>Test JMAP Server</b>
			</td>
			<td>
				<a href="http://localhost:8008/.well-known/jmap" target="_blank">Open JMAP Session Endpoint</a>
				<span style="color:#888;">(opens in new tab)</span>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>Access Points</legend>
	
	<table width="100%">
		<tr>
			<td width="200"><b>HTTP Endpoint:</b></td>
			<td><code>http://localhost:8008/jmap</code></td>
		</tr>
		<tr>
			<td><b>HTTPS Endpoint:</b></td>
			<td><code>https://localhost:8444/jmap</code></td>
		</tr>
		<tr>
			<td><b>Session Discovery:</b></td>
			<td><code>http://localhost:8008/.well-known/jmap</code></td>
		</tr>
		<tr>
			<td><b>Alternative IMAP:</b></td>
			<td><code>localhost:3143</code> (unencrypted) / <code>localhost:3993</code> (SSL)</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>About JMAP</legend>
	
	<p>
		<b>JMAP (JSON Mail Access Protocol)</b> is a modern email protocol that provides:
	</p>
	<ul>
		<li>✅ Better performance than IMAP</li>
		<li>✅ Native support for push notifications</li>
		<li>✅ Efficient synchronization</li>
		<li>✅ RESTful JSON API</li>
		<li>✅ Reduced bandwidth usage</li>
	</ul>
	
	<p>
		<b>Integration Status:</b> JMAP is fully integrated into b1gMail via Stalwart Mail Server.
		All users are automatically synchronized, and JMAP capabilities are enabled by default.
	</p>
</fieldset>
