<?php
/**
 * ModernFrontend CMS - Analytics Dashboard
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Date range
$days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
$date_from = date('Y-m-d H:i:s', time() - ($days * 86400));

// Total Pageviews
$res = $db->Query('SELECT COUNT(*) as total FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at >= ?', $date_from);
$row = $res->FetchArray(MYSQLI_ASSOC);
$total_pageviews = $row['total'];
$res->Free();

// Unique Visitors
$res = $db->Query('SELECT COUNT(DISTINCT session_id) as total FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at >= ?', $date_from);
$row = $res->FetchArray(MYSQLI_ASSOC);
$unique_visitors = $row['total'];
$res->Free();

// Conversion Rate (example: registrations)
$res = $db->Query('SELECT COUNT(*) as total FROM {pre}mf_analytics WHERE event_type="conversion" AND created_at >= ?', $date_from);
$row = $res->FetchArray(MYSQLI_ASSOC);
$conversions = $row['total'];
$res->Free();

$conversion_rate = $unique_visitors > 0 ? round(($conversions / $unique_visitors) * 100, 2) : 0;

// Top Pages
$top_pages = array();
$res = $db->Query('SELECT page_url, COUNT(*) as views FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at >= ? GROUP BY page_url ORDER BY views DESC LIMIT 10', $date_from);
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$top_pages[] = $row;
}
$res->Free();

// Pageviews by Day
$pageviews_by_day = array();
for($i = $days - 1; $i >= 0; $i--)
{
	$date = date('Y-m-d', time() - ($i * 86400));
	$date_start = $date . ' 00:00:00';
	$date_end = $date . ' 23:59:59';
	
	$res = $db->Query('SELECT COUNT(*) as total FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at BETWEEN ? AND ?', $date_start, $date_end);
	$row = $res->FetchArray(MYSQLI_ASSOC);
	$pageviews_by_day[] = array(
		'date' => $date,
		'count' => $row['total']
	);
	$res->Free();
}

// Referrers
$top_referrers = array();
$res = $db->Query('SELECT referrer, COUNT(*) as count FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at >= ? AND referrer IS NOT NULL AND referrer != "" GROUP BY referrer ORDER BY count DESC LIMIT 10', $date_from);
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$top_referrers[] = $row;
}
$res->Free();

// Browser/Device Stats (from user_agent)
$devices = array(
	'desktop' => 0,
	'mobile' => 0,
	'tablet' => 0,
	'unknown' => 0
);

$res = $db->Query('SELECT user_agent FROM {pre}mf_analytics WHERE event_type="pageview" AND created_at >= ? AND user_agent IS NOT NULL', $date_from);
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$ua = strtolower($row['user_agent']);
	if(strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false)
		$devices['mobile']++;
	elseif(strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false)
		$devices['tablet']++;
	elseif(!empty($ua))
		$devices['desktop']++;
	else
		$devices['unknown']++;
}
$res->Free();

$tpl->assign('days', $days);
$tpl->assign('total_pageviews', $total_pageviews);
$tpl->assign('unique_visitors', $unique_visitors);
$tpl->assign('conversions', $conversions);
$tpl->assign('conversion_rate', $conversion_rate);
$tpl->assign('top_pages', $top_pages);
$tpl->assign('pageviews_by_day', $pageviews_by_day);
$tpl->assign('top_referrers', $top_referrers);
$tpl->assign('devices', $devices);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/analytics.tpl');
?>
