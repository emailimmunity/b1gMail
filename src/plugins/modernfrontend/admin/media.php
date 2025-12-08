<?php
/**
 * ModernFrontend CMS - Media Library
 */

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this script is not allowed');

// Security: Plugin is called from admin framework, no additional checks needed

global $db, $tpl;

// Handle file upload
if(isset($_FILES['media_upload']) && $_FILES['media_upload']['error'] == 0)
{
	$file = $_FILES['media_upload'];
	$folder_id = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
	
	// Validate file
	$allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf');
	$max_size = 10 * 1024 * 1024; // 10MB
	
	if(!in_array($file['type'], $allowed_types))
	{
		$error = 'Dateityp nicht erlaubt!';
	}
	elseif($file['size'] > $max_size)
	{
		$error = 'Datei zu groß! Maximum: 10MB';
	}
	else
	{
		// Generate unique filename
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		$filename = uniqid() . '_' . time() . '.' . $ext;
		$filepath = '/uploads/modernfrontend/media/' . $filename;
		$full_path = B1GMAIL_DIR . 'uploads/modernfrontend/media/' . $filename;
		
		// Move file
		if(move_uploaded_file($file['tmp_name'], $full_path))
		{
			// Get image dimensions
			$width = null;
			$height = null;
			if(strpos($file['type'], 'image/') === 0)
			{
				list($width, $height) = @getimagesize($full_path);
			}
			
			// Save to database
			$db->Query('INSERT INTO {pre}mf_media(filename, original_filename, filepath, mime_type, file_size, width, height, folder_id, uploaded_by) VALUES(?,?,?,?,?,?,?,?,?)',
				$filename,
				$file['name'],
				$filepath,
				$file['type'],
				$file['size'],
				$width,
				$height,
				$folder_id,
				$_SESSION['admin_id']
			);
			
			$success = 'Datei erfolgreich hochgeladen!';
			
			PutLog('ModernFrontend: Media uploaded: ' . $file['name'],
				PRIO_NOTE,
				__FILE__,
				__LINE__);
		}
		else
		{
			$error = 'Fehler beim Hochladen!';
		}
	}
}

// Handle file delete
if(isset($_POST['delete_media']))
{
	$media_id = (int)$_POST['media_id'];
	
	// Get file info
	$res = $db->Query('SELECT * FROM {pre}mf_media WHERE id=?', $media_id);
	if($res->RowCount() == 1)
	{
		$media = $res->FetchArray(MYSQLI_ASSOC);
		$full_path = B1GMAIL_DIR . ltrim($media['filepath'], '/');
		
		// Delete file
		@unlink($full_path);
		
		// Delete from database
		$db->Query('DELETE FROM {pre}mf_media WHERE id=?', $media_id);
		
		$success = 'Datei gelöscht!';
	}
	$res->Free();
}

// Handle folder create
if(isset($_POST['create_folder']))
{
	$folder_name = trim($_POST['folder_name']);
	$parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
	
	if(!empty($folder_name))
	{
		$db->Query('INSERT INTO {pre}mf_media_folders(name, parent_id) VALUES(?,?)', $folder_name, $parent_id);
		$success = 'Ordner erstellt!';
	}
}

// Get current folder
$current_folder = isset($_GET['folder']) ? (int)$_GET['folder'] : null;

// Load folders
$folders = array();
$res = $db->Query('SELECT * FROM {pre}mf_media_folders ORDER BY name ASC');
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	$folders[] = $row;
}
$res->Free();

// Load media files
$media_files = array();
$sql = 'SELECT * FROM {pre}mf_media';
if($current_folder)
{
	$sql .= ' WHERE folder_id=' . $current_folder;
}
else
{
	$sql .= ' WHERE folder_id IS NULL';
}
$sql .= ' ORDER BY uploaded_at DESC';

$res = $db->Query($sql);
while($row = $res->FetchArray(MYSQLI_ASSOC))
{
	// Format file size
	$row['size_formatted'] = formatFileSize($row['file_size']);
	
	// Is image?
	$row['is_image'] = strpos($row['mime_type'], 'image/') === 0;
	
	$media_files[] = $row;
}
$res->Free();

// Get folder breadcrumb
$breadcrumb = array();
if($current_folder)
{
	$folder_id = $current_folder;
	while($folder_id)
	{
		$res = $db->Query('SELECT * FROM {pre}mf_media_folders WHERE id=?', $folder_id);
		if($res->RowCount() == 1)
		{
			$folder = $res->FetchArray(MYSQLI_ASSOC);
			array_unshift($breadcrumb, $folder);
			$folder_id = $folder['parent_id'];
		}
		else
		{
			break;
		}
		$res->Free();
	}
}

// Helper function
function formatFileSize($bytes)
{
	if($bytes >= 1073741824)
		return number_format($bytes / 1073741824, 2) . ' GB';
	elseif($bytes >= 1048576)
		return number_format($bytes / 1048576, 2) . ' MB';
	elseif($bytes >= 1024)
		return number_format($bytes / 1024, 2) . ' KB';
	else
		return $bytes . ' Bytes';
}

$tpl->assign('media_files', $media_files);
$tpl->assign('folders', $folders);
$tpl->assign('current_folder', $current_folder);
$tpl->assign('breadcrumb', $breadcrumb);
$tpl->assign('success', isset($success) ? $success : null);
$tpl->assign('error', isset($error) ? $error : null);
$tpl->assign('pageURL', 'admin/plugin.page.php?plugin=ModernFrontendPlugin');
$tpl->assign('page', MODERNFRONTEND_PATH . 'templates/admin/media-library.tpl');
?>
