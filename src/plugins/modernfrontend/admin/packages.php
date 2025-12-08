<?php
/**
 * ModernFrontend CMS - Package Builder Admin
 */

// Admin mode check
if(!defined('B1GMAIL_INIT') || !isset($admin) || !$admin->isLoggedIn()) {
    die('Access denied');
}

// Get plugin instance
$plugin = $plugins['ModernFrontendPlugin'];

// Handle actions
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action) {
    case 'save':
        if(isset($_POST['package_data'])) {
            // Save package data
            $package = json_decode($_POST['package_data'], true);
            
            if($package) {
                $stmt = $db->Prepare('INSERT INTO {pre}mf_packages 
                    (name, description, price, duration, features, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    description=VALUES(description),
                    price=VALUES(price),
                    duration=VALUES(duration),
                    features=VALUES(features),
                    status=VALUES(status)');
                
                $stmt->bind_param('ssdissi',
                    $package['name'],
                    $package['description'],
                    $package['price'],
                    $package['duration'],
                    json_encode($package['features']),
                    $package['status'],
                    time()
                );
                
                $stmt->execute();
                $success = 'Package saved successfully!';
            }
        }
        break;
        
    case 'delete':
        if(isset($_REQUEST['id'])) {
            $db->Query('DELETE FROM {pre}mf_packages WHERE id=?', intval($_REQUEST['id']));
            $success = 'Package deleted!';
        }
        break;
}

// Get all packages
$packages = [];
$result = $db->Query('SELECT * FROM {pre}mf_packages ORDER BY sort_order ASC');
while($row = $result->FetchArray(MYSQLI_ASSOC)) {
    $row['features'] = json_decode($row['features'], true);
    $packages[] = $row;
}

// Assign to template
$tpl->assign('packages', $packages);
$tpl->assign('success', isset($success) ? $success : '');
$tpl->assign('error', isset($error) ? $error : '');
$tpl->assign('pageURL', 'plugin.page.php?plugin=ModernFrontendPlugin&page=packages');
?>
