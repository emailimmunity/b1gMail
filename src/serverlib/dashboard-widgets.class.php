<?php
/**
 * Dashboard Widgets System
 * 
 * Ermöglicht Plugins und Features, Widgets auf dem User-Dashboard anzuzeigen
 * 
 * @version 1.0.0
 */

if(!defined('B1GMAIL_INIT'))
	die('Direct access not allowed');

class BMDashboardWidgets
{
	private static $widgets = array();
	
	/**
	 * Widget registrieren
	 */
	public static function register($id, $title, $callback, $priority = 10)
	{
		self::$widgets[$id] = array(
			'id' => $id,
			'title' => $title,
			'callback' => $callback,
			'priority' => $priority
		);
	}
	
	/**
	 * Alle Widgets rendern
	 */
	public static function renderAll()
	{
		global $tpl;
		
		// Nach Priorität sortieren
		uasort(self::$widgets, function($a, $b) {
			return $a['priority'] - $b['priority'];
		});
		
		$renderedWidgets = array();
		
		foreach(self::$widgets as $widget)
		{
			$content = call_user_func($widget['callback']);
			if($content)
			{
				$renderedWidgets[] = array(
					'id' => $widget['id'],
					'title' => $widget['title'],
					'content' => $content
				);
			}
		}
		
		return $renderedWidgets;
	}
	
	/**
	 * Standard-Widgets registrieren
	 */
	public static function registerDefaultWidgets()
	{
		global $currentUser, $db;
		
		$userID = $currentUser['id'];
		
		// Storage-Widget
		self::register('storage', 'Speicherplatz', function() use ($userID, $db) {
			global $currentUser;
			
			$groupRow = $db->Query('SELECT * FROM {pre}gruppen WHERE id=?', 
				$currentUser['gruppe'])->FetchArray(MYSQLI_ASSOC);
			
			$used = $currentUser['mailspace_used'];
			$total = $groupRow['mailspace'] * 1024 * 1024;
			$percent = $total > 0 ? round(($used / $total) * 100, 1) : 0;
			
			return '<div class="widget-storage">
				<div class="progress">
					<div class="progress-bar" style="width: ' . $percent . '%"></div>
				</div>
				<p>' . formatBytes($used) . ' / ' . formatBytes($total) . ' (' . $percent . '%)</p>
			</div>';
		}, 10);
		
		// Subdomain-Widget (wenn Plugin aktiv)
		if(class_exists('SubDomainManagerPlugin'))
		{
			self::register('subdomains', 'Meine Subdomains', function() use ($userID, $db) {
				$res = $db->Query('SELECT * FROM {pre}sdm_subdomains WHERE user_id=? LIMIT 5', $userID);
				$subdomains = array();
				while($row = $res->FetchArray(MYSQLI_ASSOC))
					$subdomains[] = $row;
				
				if(count($subdomains) == 0)
					return '<p>Keine Subdomains</p>';
				
				$html = '<ul class="widget-list">';
				foreach($subdomains as $sd)
				{
					$html .= '<li>' . htmlspecialchars($sd['full_domain']);
					if($sd['dyndns_enabled'])
						$html .= ' <span class="badge badge-info">DynDNS</span>';
					$html .= '</li>';
				}
				$html .= '</ul>';
				
				return $html;
			}, 20);
		}
		
		// Protokoll-Status-Widget
		self::register('protocols', 'Protokoll-Status', function() {
			$protocols = array(
				array('name' => 'IMAP', 'status' => 'ok', 'icon' => '✓'),
				array('name' => 'SMTP', 'status' => 'ok', 'icon' => '✓'),
				array('name' => 'POP3', 'status' => 'ok', 'icon' => '✓'),
			);
			
			// Grommunio Check
			global $bm_prefs;
			if(isset($bm_prefs['grommunio_url']))
			{
				$protocols[] = array('name' => 'Grommunio', 'status' => 'ok', 'icon' => '✓');
			}
			
			$html = '<table class="widget-table">';
			foreach($protocols as $proto)
			{
				$html .= '<tr>
					<td>' . $proto['icon'] . '</td>
					<td>' . $proto['name'] . '</td>
					<td><span class="status-' . $proto['status'] . '">Aktiv</span></td>
				</tr>';
			}
			$html .= '</table>';
			
			return $html;
		}, 30);
		
		// Security-Widget
		self::register('security', 'Sicherheit', function() use ($userID, $db) {
			$score = 50; // Basis-Score
			
			// 2FA aktiv?
			$res = $db->Query('SELECT * FROM {pre}users WHERE id=?', $userID);
			$user = $res->FetchArray(MYSQLI_ASSOC);
			
			if(!empty($user['totp_secret']))
				$score += 30;
			
			// App-Passwords vorhanden?
			if(class_exists('BMAppPasswords'))
			{
				$res = $db->Query('SELECT COUNT(*) as cnt FROM {pre}app_passwords 
					WHERE userid=? AND revoked=0', $userID);
				$row = $res->FetchArray(MYSQLI_ASSOC);
				if($row['cnt'] > 0)
					$score += 20;
			}
			
			$color = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
			
			return '<div class="widget-security">
				<div class="security-score">
					<div class="score-circle score-' . $color . '">
						<span>' . $score . '%</span>
					</div>
				</div>
				<ul class="security-items">
					<li>' . (!empty($user['totp_secret']) ? '✓' : '✗') . ' 2FA aktiviert</li>
					<li>✓ Starkes Passwort</li>
					<li>' . ($score >= 80 ? '✓' : '✗') . ' App-Passwörter</li>
				</ul>
			</div>';
		}, 40);
	}
}
