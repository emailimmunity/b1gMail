{*
 * ============================================================================
 * CYRUS/JMAP INTEGRATION FOR bms.user.tpl
 * ============================================================================
 * 
 * Diese Code-Blöcke müssen in bms.user.tpl eingefügt werden
 * um JMAP Login-Informationen analog zu POP3/IMAP/SMTP anzuzeigen
 * 
 * EINFÜGEN NACH: DAV-Protocols Sektion (nach Zeile ~190)
 * 
 * ============================================================================
 *}

{* JMAP Protocol (Modern API) *}
{if $haveJMAP}
<table class="listTable">
	<tr>
		<th class="listTableHead" colspan="2">
			<i class="fa fa-bolt"></i> JMAP (Modern Mail API)
		</th>
	</tr>

	<tr>
		<td class="listTableLeft">&nbsp;</td>
		<td class="listTableRight">
			JMAP ist das moderne Protokoll für E-Mail-Clients. 
			Schneller und effizienter als IMAP, besonders für mobile Geräte.
		</td>
	</tr>

	<tr>
		<td class="listTableLeft"><i class="fa fa-bolt"></i> {lng p="bms_jmap_setup"}:</td>
		<td class="listTableRight">
			<strong>JMAP URL:</strong> <code>{$bms_prefs.user_jmapurl}</code><br />
			<strong>Session Endpoint:</strong> <code>{$bms_prefs.user_jmapurl}.well-known/jmap</code><br />
			<strong>{lng p="username"}:</strong> {$username}<br />
			<strong>{lng p="password"}:</strong> <i>{lng p="bms_pwnote"}</i><br />
			<strong>Status:</strong> <span class="badge bg-success"><i class="fa fa-check"></i> Aktiv</span>
		</td>
	</tr>

	<tr>
		<td class="listTableLeft"><i class="fa fa-mobile"></i> Client-Support:</td>
		<td class="listTableRight">
			<div style="margin-top: 5px;">
				<strong>Desktop:</strong><br />
				<ul style="margin-left: 20px;">
					<li>Thunderbird (mit JMAP Plugin)</li>
					<li>Apple Mail (experimentell)</li>
					<li>Browser-basierte Clients</li>
				</ul>
			</div>
			<div style="margin-top: 10px;">
				<strong>Mobile:</strong><br />
				<ul style="margin-left: 20px;">
					<li>iOS/iPadOS (via Profile)</li>
					<li>Android (via JMAP Apps)</li>
				</ul>
			</div>
			<div style="margin-top: 10px; padding: 10px; background-color: #f0f8ff; border-left: 3px solid #1e90ff;">
				<i class="fa fa-info-circle"></i> 
				<strong>Hinweis:</strong> JMAP ist optional. IMAP/POP3 funktionieren weiterhin normal.
			</div>
		</td>
	</tr>

	{if $cyrus_stats}
	<tr>
		<td class="listTableLeft"><i class="fa fa-bar-chart"></i> Ihre Statistik:</td>
		<td class="listTableRight">
			<strong>Nachrichten:</strong> {$cyrus_stats.messages|number_format}<br />
			<strong>Ungelesen:</strong> {$cyrus_stats.unseen|number_format}<br />
			<strong>Quota:</strong> {$cyrus_stats.quota_used_mb} MB / {$cyrus_stats.quota_limit_mb} MB 
			({$cyrus_stats.quota_percent}%)
			<div style="width: 100%; height: 20px; background-color: #e0e0e0; margin-top: 5px;">
				<div style="width: {$cyrus_stats.quota_percent}%; height: 20px; background-color: {if $cyrus_stats.quota_percent > 90}#ff4444{elseif $cyrus_stats.quota_percent > 75}#ffaa00{else}#4CAF50{/if};"></div>
			</div>
		</td>
	</tr>
	{/if}
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
 * $tpl->assign('haveJMAP', defined('CYRUS_JMAP_ENABLED') && CYRUS_JMAP_ENABLED);
 * 
 * // JMAP Config
 * if(defined('CYRUS_JMAP_ENABLED') && CYRUS_JMAP_ENABLED)
 * {
 *     $bms_prefs['user_jmapurl'] = CYRUS_JMAP_URL;
 *     
 *     // Get user stats from Cyrus
 *     if(class_exists('BMUser'))
 *     {
 *         $user = new BMUser($userID);
 *         $stats = $user->GetCyrusMailboxStats();
 *         $quota = $user->GetCyrusQuota();
 *         
 *         if($stats && $quota)
 *         {
 *             $tpl->assign('cyrus_stats', array(
 *                 'messages' => $stats['messages'],
 *                 'unseen' => $stats['unseen'],
 *                 'quota_used_mb' => $quota['used_mb'],
 *                 'quota_limit_mb' => $quota['limit_mb'],
 *                 'quota_percent' => $quota['percent']
 *             ));
 *         }
 *     }
 * }
 * 
 * ============================================================================
 *}

{*
 * ============================================================================
 * LANGUAGE STRINGS (in bm60_texts Tabelle einfügen)
 * ============================================================================
 *
 * INSERT INTO `bm60_texts` (`lang`, `cat`, `phrase`, `text`) VALUES
 * ('deutsch', 'prefs', 'bms_jmap_setup', 'JMAP-Zugang'),
 * ('english', 'prefs', 'bms_jmap_setup', 'JMAP Access'),
 * ('deutsch', 'prefs', 'bms_jmap_info', 'JMAP ist das moderne E-Mail-Protokoll'),
 * ('english', 'prefs', 'bms_jmap_info', 'JMAP is the modern email protocol');
 *
 * ============================================================================
 *}
