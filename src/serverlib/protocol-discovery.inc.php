<?php
/**
 * Protocol Service Discovery System
 * 
 * Allows Docker containers to auto-discover their configuration
 * from the database without requiring restarts.
 * 
 * Usage in containers:
 *   while(true) {
 *       $config = ProtocolDiscovery::getServiceConfig('IMAP');
 *       rebindToPort($config['server_port']);
 *       sleep(60);
 *   }
 * 
 * @date 2025-11-27
 */

if(!defined('B1GMAIL_INIT'))
    die('Access denied');

class ProtocolDiscovery
{
    /**
     * Cache to prevent excessive DB queries
     */
    private static $cache = array();
    private static $cacheTime = 0;
    private static $cacheTTL = 30; // 30 seconds cache
    
    /**
     * Get configuration for a specific service/protocol
     * 
     * @param string $protocolType Protocol type (e.g., 'IMAP', 'SMTP', 'EAS')
     * @return array|null Configuration array or null if not found
     */
    public static function getServiceConfig($protocolType)
    {
        global $db;
        
        // Check cache first
        if(self::isCacheValid() && isset(self::$cache[$protocolType])) {
            return self::$cache[$protocolType];
        }
        
        // Query database
        $result = $db->Query('SELECT * FROM {pre}protocol_links WHERE protocol_type=? LIMIT 1', 
            $protocolType);
        
        if($result->RowCount() == 0) {
            return null;
        }
        
        $config = $result->FetchArray(MYSQLI_ASSOC);
        $result->Free();
        
        // Process configuration
        $config = self::processConfig($config);
        
        // Cache it
        self::$cache[$protocolType] = $config;
        self::$cacheTime = time();
        
        return $config;
    }
    
    /**
     * Get all enabled protocols for service discovery
     * 
     * @return array Array of protocol configurations
     */
    public static function getAllEnabledServices()
    {
        global $db;
        
        // Check cache
        if(self::isCacheValid() && isset(self::$cache['_all'])) {
            return self::$cache['_all'];
        }
        
        $services = array();
        $result = $db->Query('SELECT * FROM {pre}protocol_links WHERE enabled=1 ORDER BY display_order ASC');
        
        while($row = $result->FetchArray(MYSQLI_ASSOC)) {
            $services[$row['protocol_type']] = self::processConfig($row);
        }
        $result->Free();
        
        // Cache it
        self::$cache['_all'] = $services;
        self::$cacheTime = time();
        
        return $services;
    }
    
    /**
     * Get services by category
     * 
     * @param string $category Category name (email, exchange, dav, etc.)
     * @return array
     */
    public static function getServicesByCategory($category)
    {
        global $db;
        
        $cacheKey = '_cat_' . $category;
        if(self::isCacheValid() && isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }
        
        $services = array();
        $result = $db->Query('SELECT * FROM {pre}protocol_links WHERE protocol_category=? AND enabled=1 ORDER BY display_order ASC',
            $category);
        
        while($row = $result->FetchArray(MYSQLI_ASSOC)) {
            $services[$row['protocol_type']] = self::processConfig($row);
        }
        $result->Free();
        
        self::$cache[$cacheKey] = $services;
        self::$cacheTime = time();
        
        return $services;
    }
    
    /**
     * Check if service configuration has changed
     * Useful for containers to detect changes without full reload
     * 
     * @param string $protocolType Protocol type
     * @param int $lastKnownUpdate Unix timestamp of last known update
     * @return bool True if changed
     */
    public static function hasConfigChanged($protocolType, $lastKnownUpdate)
    {
        global $db;
        
        $result = $db->Query('SELECT updated_at FROM {pre}protocol_links WHERE protocol_type=? LIMIT 1',
            $protocolType);
        
        if($result->RowCount() == 0) {
            return false;
        }
        
        list($updatedAt) = $result->FetchArray(MYSQLI_NUM);
        $result->Free();
        
        return ($updatedAt > $lastKnownUpdate);
    }
    
    /**
     * Get current configuration hash for change detection
     * 
     * @param string $protocolType Protocol type
     * @return string MD5 hash of configuration
     */
    public static function getConfigHash($protocolType)
    {
        $config = self::getServiceConfig($protocolType);
        if(!$config) return '';
        
        // Hash relevant fields for change detection
        $hashData = implode('|', array(
            $config['server_host'],
            $config['server_port'],
            $config['server_path'],
            $config['ssl_type'],
            $config['enabled']
        ));
        
        return md5($hashData);
    }
    
    /**
     * Process configuration (resolve placeholders, apply overrides)
     * 
     * @param array $config Raw config from database
     * @return array Processed configuration
     */
    private static function processConfig($config)
    {
        // Resolve {domain} placeholder
        if(isset($config['server_host']) && strpos($config['server_host'], '{domain}') !== false) {
            $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $config['server_host'] = str_replace('{domain}', $domain, $config['server_host']);
        }
        
        // Apply environment variable overrides (Docker)
        // Environment variables take precedence over database
        $envOverrides = self::getEnvironmentOverrides($config['protocol_type']);
        if($envOverrides) {
            $config = array_merge($config, $envOverrides);
            $config['_source'] = 'environment'; // Mark as env-sourced
        } else {
            $config['_source'] = 'database';
        }
        
        // Parse JSON extra_config if present
        if(isset($config['extra_config']) && !empty($config['extra_config'])) {
            $extraConfig = @json_decode($config['extra_config'], true);
            if(is_array($extraConfig)) {
                $config['_extra'] = $extraConfig;
            }
        }
        
        return $config;
    }
    
    /**
     * Get environment variable overrides for protocol
     * 
     * @param string $protocolType Protocol type
     * @return array|null Override values or null
     */
    private static function getEnvironmentOverrides($protocolType)
    {
        $overrides = array();
        
        // Map protocol types to environment variable prefixes
        $envMap = array(
            'IMAP'    => 'CYRUS',
            'POP3'    => 'CYRUS',
            'SMTP'    => 'POSTFIX',
            'EAS'     => 'GROMMUNIO',
            'EWS'     => 'GROMMUNIO',
            'MAPI'    => 'GROMMUNIO',
            'SFTP'    => 'SFTPGO',
            'FTPS'    => 'SFTPGO',
            'S3'      => 'SFTPGO',
            'WebDAV'  => 'SFTPGO',
            'CalDAV'  => 'SABREDAV',
            'CardDAV' => 'SABREDAV'
        );
        
        if(!isset($envMap[$protocolType])) {
            return null;
        }
        
        $prefix = $envMap[$protocolType];
        
        // Check for environment variable overrides
        if($host = getenv($prefix . '_HOST')) {
            $overrides['server_host'] = $host;
        }
        
        if($port = getenv($prefix . '_' . $protocolType . '_PORT')) {
            $overrides['server_port'] = (int)$port;
        }
        
        if($ssl = getenv($prefix . '_SSL_TYPE')) {
            $overrides['ssl_type'] = $ssl;
        }
        
        return empty($overrides) ? null : $overrides;
    }
    
    /**
     * Check if cache is still valid
     * 
     * @return bool
     */
    private static function isCacheValid()
    {
        return (time() - self::$cacheTime) < self::$cacheTTL;
    }
    
    /**
     * Clear cache (useful for testing or after config changes)
     */
    public static function clearCache()
    {
        self::$cache = array();
        self::$cacheTime = 0;
    }
    
    /**
     * Export configuration as JSON (for container consumption)
     * 
     * @param string $protocolType Protocol type or 'all' for all services
     * @return string JSON string
     */
    public static function exportJSON($protocolType = 'all')
    {
        if($protocolType === 'all') {
            $config = self::getAllEnabledServices();
        } else {
            $config = self::getServiceConfig($protocolType);
        }
        
        return json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

?>
