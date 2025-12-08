<?php
/**
 * ModernFrontend CMS - A/B Testing
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle test creation
if(isset($_POST['create_test']))
{
	$test_name = trim($_POST['test_name']);
	$description = trim($_POST['description']);
	$variant_a = json_encode(array('content' => trim($_POST['variant_a'])));
	$variant_b = json_encode(array('content' => trim($_POST['variant_b'])));
	$traffic_split = (int)$_POST['traffic_split'];
	
	if(!empty($test_name))
	{
		$db->Query('INSERT INTO {pre}mf_ab_tests(test_name, description, variant_a, variant_b, traffic_split, status) VALUES(?,?,?,?,?,?)',
			$test_name,
			$description,
			$variant_a,
			$variant_b,
			$traffic_split,
			'draft'
		);
		
		$success = 'A/B Test erstellt!';
	}
}

// Handle test start/stop
if(isset($_POST['change_status']))
{
	$test_id = (int)$_POST['test_id'];
	$new_status = $_POST['new_status'];
	
	if(in_array($new_status, array('running', 'paused', 'completed')))
	{
		$db->Query('UPDATE {pre}mf_ab_tests SET status=? WHERE id=?', $new_status, $test_id);
		
		if($new_status == 'running')
			$db->Query('UPDATE {pre}mf_ab_tests SET start_date=NOW() WHERE id=? AND start_date IS NULL', $test_id);
		elseif($new_status == 'completed')
			$db->Query('UPDATE {pre}mf_ab_tests SET end_date=NOW() WHERE id=?', $test_id);
		
		$success = 'Status geändert!';
	}
}

// Handle winner selection
if(isset($_POST['select_winner']))
{
	$test_id = (int)$_POST['test_id'];
	$winner = $_POST['winner'];
	
	if(in_array($winner, array('a', 'b')))
	{
		$db->Query('UPDATE {pre}mf_ab_tests SET winner=?, status=? WHERE id=?', $winner, 'completed', $test_id);
		$success = 'Gewinner ausgewählt!';
	}
}

// Load all tests
$tests = array();
$res = $db->Query('SELECT * FROM {pre}mf_ab_tests ORDER BY created_at DESC');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	// Get results
	$resA = $db->Query('SELECT COUNT(*) as participants, SUM(converted) as conversions FROM {pre}mf_ab_results WHERE test_id=? AND variant=?', $row['id'], 'a');
	$rowA = $resA->FetchArray(MYSQLI_ASSOC);
	$resA->Free();
	
	$resB = $db->Query('SELECT COUNT(*) as participants, SUM(converted) as conversions FROM {pre}mf_ab_results WHERE test_id=? AND variant=?', $row['id'], 'b');
	$rowB = $resB->FetchArray(MYSQLI_ASSOC);
	$resB->Free();
	
	$row['variant_a_participants'] = $rowA['participants'];
	$row['variant_a_conversions'] = $rowA['conversions'];
	$row['variant_a_rate'] = $rowA['participants'] > 0 ? round(($rowA['conversions'] / $rowA['participants']) * 100, 2) : 0;
	
	$row['variant_b_participants'] = $rowB['participants'];
	$row['variant_b_conversions'] = $rowB['conversions'];
	$row['variant_b_rate'] = $rowB['participants'] > 0 ? round(($rowB['conversions'] / $rowB['participants']) * 100, 2) : 0;
	
	$tests[] = $row;
}
$res->Free();

$tpl->assign('tests', $tests);
$tpl->assign('success', isset($success) ? $success : null);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/abtesting.tpl');
?>
