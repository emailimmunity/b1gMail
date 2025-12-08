<?php
/*
 * b1gMailServer SabreDAV Version Check
 * (c) 2024 b1gMail Development
 *
 * Checks SabreDAV installation and version
 * 
 * WICHTIG: SabreDAV wird NUR für CalDAV/CardDAV verwendet!
 * WebDAV (Datei-Zugriff) wird separat mit SoSFTP/S3/Minio implementiert.
 */

class BMSabreDAVVersion
{
	/**
	 * Get SabreDAV version status
	 * 
	 * @return array Status information
	 */
	public static function getVersionStatus()
	{
		$status = array(
			'installed_version_found' => false,
			'installed_version' => 'Unbekannt',
			'status_text' => 'SabreDAV Version nicht verfügbar',
			'status_class' => 'warning',
			'is_critical' => false,
			'update_available' => false,
			'latest_version' => '',
			'published_at' => '',
			'release_url' => '',
			'composer_json_path' => ''
		);
		
		// Check if SabreDAV is installed via Composer
		$baseDir = dirname(__FILE__);
		$versionPaths = array(
			dirname($baseDir) . '/3rdparty/SabreDAV/vendor/sabre/dav/lib/DAV/Version.php',
			dirname($baseDir) . '/vendor/sabre/dav/lib/DAV/Version.php',
			dirname(dirname($baseDir)) . '/vendor/sabre/dav/lib/DAV/Version.php'
		);
		
		foreach($versionPaths as $versionPath)
		{
			if(file_exists($versionPath))
			{
				$status['installed_version_found'] = true;
				
				// Load Version class if not loaded
				if(!class_exists('Sabre\\DAV\\Version'))
				{
					require_once($versionPath);
				}
				
				// Get version from class constant
				if(class_exists('Sabre\\DAV\\Version'))
				{
					$status['installed_version'] = \Sabre\DAV\Version::VERSION;
					$status['status_text'] = 'SabreDAV v' . $status['installed_version'] . ' installiert';
					$status['status_class'] = 'success';
				}
				
				break;
			}
		}
		
		if(!$status['installed_version_found'])
		{
			$status['status_text'] = 'SabreDAV wurde nicht gefunden';
			$status['status_class'] = 'danger';
			$status['is_critical'] = true;
		}
		
		return $status;
	}
	
	/**
	 * Check if SabreDAV is available
	 * 
	 * @return bool
	 */
	public static function isAvailable()
	{
		$status = self::getVersionStatus();
		return $status['installed_version_found'];
	}
}
