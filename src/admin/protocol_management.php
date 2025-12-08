<?php
/**
 * Admin Protokoll-Verwaltung
 * Verwaltet alle Protokolle (IMAP, SMTP, SFTP, S3, etc.)
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);

$tpl->assign('page', 'protocol_management.tpl');
$tpl->assign('pageTitle', 'Protokoll-Verwaltung');
$tpl->assign('title', 'Protokoll-Verwaltung');

$message = '';
$error = '';

// ═══════════════════════════════════════════════════════════════
// ACTIONS
// ═══════════════════════════════════════════════════════════════

if(isset($_REQUEST['action']))
{
	switch($_REQUEST['action'])
	{
		// TOGGLE ENABLED/DISABLED
		case 'toggle':
			if(isset($_REQUEST['id']))
			{
				$id = (int)$_REQUEST['id'];
				$result = $db->Query('SELECT enabled FROM {pre}protocol_links WHERE id=?', $id);
				if($result && $result->RowCount() > 0)
				{
					$row = $result->FetchArray(MYSQLI_ASSOC);
					$result->Free();
					$newStatus = $row['enabled'] ? 0 : 1;
					$db->Query('UPDATE {pre}protocol_links SET enabled=?, updated_at=? WHERE id=?', 
						$newStatus, time(), $id);
					$message = 'Protokoll-Status aktualisiert!';
				}
				else
				{
					if($result) $result->Free();
					$error = 'Protokoll nicht gefunden!';
				}
			}
			break;
			
		// UPDATE PROTOCOL
		case 'update':
			if(isset($_POST['id']))
			{
				$id = (int)$_POST['id'];
				
				// Handle NULL values properly for database
				$server_host = !empty($_POST['server_host']) ? $_POST['server_host'] : '#NULL#';
				$server_port = (isset($_POST['server_port']) && $_POST['server_port'] !== '') ? (int)$_POST['server_port'] : '#NULL#';
				$server_path = !empty($_POST['server_path']) ? $_POST['server_path'] : '#NULL#';
				$help_link_title_de = !empty($_POST['help_link_title_de']) ? $_POST['help_link_title_de'] : '#NULL#';
				$help_link_title_en = !empty($_POST['help_link_title_en']) ? $_POST['help_link_title_en'] : '#NULL#';
				$help_link_url = !empty($_POST['help_link_url']) ? $_POST['help_link_url'] : '#NULL#';
				
				$sql = 'UPDATE {pre}protocol_links SET
					title_de = ?,
					title_en = ?,
					description_de = ?,
					description_en = ?,
					icon = ?,
					server_host = ?,
					server_port = ?,
					server_path = ?,
					ssl_type = ?,
					help_link_title_de = ?,
					help_link_title_en = ?,
					help_link_url = ?,
					updated_at = ?
					WHERE id = ?';
				
				$db->Query(
					$sql,
					$_POST['title_de'] ?? '',
					$_POST['title_en'] ?? '',
					$_POST['description_de'] ?? '',
					$_POST['description_en'] ?? '',
					$_POST['icon'] ?? '',
					$server_host,
					$server_port,
					$server_path,
					$_POST['ssl_type'] ?? 'ssl',
					$help_link_title_de,
					$help_link_title_en,
					$help_link_url,
					time(),
					$id
				);
				
				$message = 'Protokoll aktualisiert!';
				
				// ✅ NEW: Trigger SSL provisioning hook if SSL is enabled and hostname changed
				if(!empty($_POST['server_host']) && ($_POST['ssl_type'] ?? 'none') !== 'none')
				{
					if(file_exists(B1GMAIL_DIR . 'serverlib/protocol-ssl-bridge.inc.php')) {
						require_once(B1GMAIL_DIR . 'serverlib/protocol-ssl-bridge.inc.php');
						$bridge = getProtocolSSLBridge();
						$sslResult = $bridge->onProtocolUpdated($id, $_POST['ssl_type']);
						
						if(!$sslResult['success']) {
							PutLog('Protocol Management: SSL provisioning failed - ' . ($sslResult['error'] ?? 'Unknown error'),
								PRIO_WARNING, __FILE__, __LINE__);
							$message .= ' (SSL-Provisioning fehlgeschlagen: ' . $sslResult['error'] . ')';
						} else if(isset($sslResult['certificate_id'])) {
							$message .= ' (SSL-Zertifikat wird bereitgestellt...)';
						}
					}
				}
			}
			else
			{
				$error = 'Keine ID angegeben!';
			}
			break;
			
		// UPDATE ORDER
		case 'update_order':
			if(isset($_POST['order']))
			{
				$order = json_decode($_POST['order'], true);
				if(is_array($order))
				{
					foreach($order as $index => $id)
					{
						$db->Query('UPDATE {pre}protocol_links SET display_order=?, updated_at=? WHERE id=?',
							($index + 1) * 10, time(), (int)$id);
					}
					$message = 'Reihenfolge aktualisiert!';
				}
			}
			break;
			
		// DELETE (nur wenn nicht System-Protokoll)
		case 'delete':
			if(isset($_REQUEST['id']))
			{
				$id = (int)$_REQUEST['id'];
				$result = $db->Query('SELECT is_system, protocol_type FROM {pre}protocol_links WHERE id=?', $id);
				if($result && $result->RowCount() > 0)
				{
					$row = $result->FetchArray(MYSQLI_ASSOC);
					$result->Free();
					if($row['is_system'])
					{
						$error = 'System-Protokolle können nicht gelöscht werden!';
					}
					else
					{
						$db->Query('DELETE FROM {pre}protocol_links WHERE id=?', $id);
						$message = 'Protokoll gelöscht!';
					}
				}
				else
				{
					if($result) $result->Free();
					$error = 'Protokoll nicht gefunden!';
				}
			}
			break;
	}
}

// ═══════════════════════════════════════════════════════════════
// GET ALL PROTOCOLS
// ═══════════════════════════════════════════════════════════════

$result = $db->Query('SELECT * FROM {pre}protocol_links ORDER BY display_order ASC, id ASC');

$protocols = array();
$stats = array(
	'total' => 0,
	'enabled' => 0,
	'disabled' => 0,
	'by_category' => array()
);

// Load JMAP plugin if available for status checks
$jmapPlugin = null;
if(file_exists(B1GMAIL_DIR . 'plugins/stalwart-jmap.plugin.php')) {
	require_once(B1GMAIL_DIR . 'plugins/stalwart-jmap.plugin.php');
	$jmapPlugin = new StalwartJMAPPlugin();
}

if($result)
{
	while($row = $result->FetchArray(MYSQLI_ASSOC))
	{
		// Add runtime status for JMAP protocol
		if($row['protocol_type'] === 'jmap' && $jmapPlugin !== null) {
			$jmapStatus = $jmapPlugin->checkServerStatus();
			$row['runtime_status'] = array(
				'online' => $jmapStatus['online'],
				'message' => $jmapStatus['message'],
				'response_time' => $jmapStatus['response_time'] ?? 0,
				'version' => $jmapStatus['version'] ?? 'Unknown'
			);
		} else {
			$row['runtime_status'] = null;
		}
		
		$protocols[] = $row;
		$stats['total']++;
		
		if($row['enabled']) {
			$stats['enabled']++;
		} else {
			$stats['disabled']++;
		}
		
		$cat = $row['protocol_category'];
		if(!isset($stats['by_category'][$cat])) {
			$stats['by_category'][$cat] = 0;
		}
		$stats['by_category'][$cat]++;
	}
	$result->Free();
}

// ═══════════════════════════════════════════════════════════════
// TEMPLATE ASSIGNMENTS
// ═══════════════════════════════════════════════════════════════

$tpl->assign('protocols', $protocols);
$tpl->assign('stats', $stats);
$tpl->assign('message', $message);
$tpl->assign('error', $error);

// Get JMAP-specific statistics if plugin is loaded
if($jmapPlugin !== null) {
	$jmapStats = $jmapPlugin->getStatistics();
	$jmapServerStatus = $jmapPlugin->checkServerStatus();
	$tpl->assign('jmapStats', $jmapStats);
	$tpl->assign('jmapServerStatus', $jmapServerStatus);
	$tpl->assign('jmapAvailable', true);
} else {
	$tpl->assign('jmapAvailable', false);
}

// Available categories for dropdown
$tpl->assign('categories', array(
	'email' => 'E-Mail',
	'exchange' => 'Exchange',
	'dav' => 'DAV (Cal/Card/Web)',
	'file_transfer' => 'Datei-Transfer',
	'cloud_storage' => 'Cloud-Speicher',
	'other' => 'Sonstige'
));

// Available SSL types
$tpl->assign('ssl_types', array(
	'none' => 'Keine Verschlüsselung',
	'ssl' => 'SSL/TLS',
	'tls' => 'TLS',
	'starttls' => 'STARTTLS'
));

$tpl->display('page.tpl');
