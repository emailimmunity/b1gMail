<?php
/**
 * Docker Container Status Checker
 * (c) 2025 b1gMail Development
 */

class ContainerStatusChecker
{
	/**
	 * Check if a Docker container is running
	 */
	public static function getContainerStatus($containerName)
	{
		// TRY METHOD 1: Docker inspect (may be blocked by safe_mode)
		$cmd = "docker inspect --format='{{json .State}}' " . escapeshellarg($containerName) . " 2>&1";
		
		// Try exec first
		$output = @exec($cmd . " 2>&1", $lines, $returnCode);
		
		if(empty($output) || $returnCode !== 0)
		{
			// METHOD 2: Try via curl to Docker socket (if available)
			// METHOD 3: Simple port check as fallback
			return self::checkContainerByPort($containerName);
		}
		
		$fullOutput = implode("\n", $lines);
		$data = json_decode($fullOutput, true);
		
		if(!$data)
		{
			return self::checkContainerByPort($containerName);
		}
		
		$running = $data['Running'] ?? false;
		$startedAt = $data['StartedAt'] ?? '';
		
		// Calculate uptime
		$uptime = 'Unknown';
		if($startedAt && $running)
		{
			$start = strtotime($startedAt);
			$now = time();
			$diff = $now - $start;
			
			$days = floor($diff / 86400);
			$hours = floor(($diff % 86400) / 3600);
			$minutes = floor(($diff % 3600) / 60);
			
			if($days > 0)
				$uptime = "{$days}d {$hours}h";
			elseif($hours > 0)
				$uptime = "{$hours}h {$minutes}m";
			else
				$uptime = "{$minutes}m";
		}
		
		return array(
			'exists' => true,
			'running' => $running,
			'name' => $containerName,
			'status' => $data['Status'] ?? 'unknown',
			'uptime' => $uptime,
			'started_at' => $startedAt
		);
	}
	
	/**
	 * Fallback: Check container by port connectivity
	 */
	private static function checkContainerByPort($containerName)
	{
		// Port mapping for known containers
		// WICHTIG: PHP läuft im b1gmail-Container, daher Docker-Netzwerk-Namen verwenden!
		$portMap = array(
			'b1gmail-cyrus' => array('host' => 'cyrus', 'port' => 143),      // IMAP intern
			'b1gmail-postfix' => array('host' => 'postfix', 'port' => 25),   // SMTP intern
			'b1gmail-sftpgo' => array('host' => 'sftpgo', 'port' => 8080),   // Admin intern
			'cyrus' => array('host' => 'cyrus', 'port' => 143),
			'postfix' => array('host' => 'postfix', 'port' => 25),
			'sftpgo' => array('host' => 'sftpgo', 'port' => 8080)
		);
		
		if(!isset($portMap[$containerName]))
		{
			return array(
				'exists' => false,
				'running' => false,
				'name' => $containerName,
				'status' => 'unknown',
				'uptime' => 'N/A'
			);
		}
		
		$config = $portMap[$containerName];
		$fp = @fsockopen($config['host'], $config['port'], $errno, $errstr, 2);
		
		if($fp)
		{
			fclose($fp);
			return array(
				'exists' => true,
				'running' => true,
				'name' => $containerName,
				'status' => 'running',
				'uptime' => 'Active (Port Check)'
			);
		}
		
		return array(
			'exists' => true,
			'running' => false,
			'name' => $containerName,
			'status' => 'stopped',
			'uptime' => 'N/A'
		);
	}
	
	/**
	 * Check multiple containers at once
	 */
	public static function checkAllContainers($containerNames)
	{
		$results = array();
		
		foreach($containerNames as $name)
		{
			$results[$name] = self::getContainerStatus($name);
		}
		
		return $results;
	}
}
