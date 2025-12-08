<?php
/**
 * Domain Manager - Automatische Domain-Erkennung
 * (c) 2025 b1gMail Development
 * 
 * Liest automatisch alle Domains aus:
 * - b1gMail Domain-Tabelle
 * - User-Email-Adressen
 * - Plugin Email-Admin Domains
 */

class DomainManager
{
	private $db;
	
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	/**
	 * Get all available domains in the system
	 */
	public function getAllDomains()
	{
		$domains = array();
		
		// 1. Get domains from b1gMail domain table (if exists)
		$res = $this->db->Query("SHOW TABLES LIKE '{pre}domains'");
		if($res->RowCount() > 0)
		{
			$res->Free();
			
			// Check if 'active' column exists
			$hasActiveColumn = false;
			$res = $this->db->Query("SHOW COLUMNS FROM {pre}domains LIKE 'active'");
			if($res->RowCount() > 0)
			{
				$hasActiveColumn = true;
			}
			$res->Free();
			
			// Query with or without active filter
			if($hasActiveColumn)
			{
				$res = $this->db->Query('SELECT `domain` FROM {pre}domains WHERE `active`=1');
			}
			else
			{
				$res = $this->db->Query('SELECT `domain` FROM {pre}domains');
			}
			
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$domain = strtolower(trim($row['domain']));
				if(!empty($domain) && !in_array($domain, $domains))
				{
					$domains[] = $domain;
				}
			}
			$res->Free();
		}
		else
		{
			$res->Free();
		}
		
		// 2. Extract domains from all user email addresses
		$res = $this->db->Query('SELECT DISTINCT `email` FROM {pre}users WHERE `gesperrt`=0 AND `email`!=""');
		while($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$email = strtolower(trim($row['email']));
			
			if(strpos($email, '@') !== false)
			{
				list(, $domain) = explode('@', $email, 2);
				
				if(!empty($domain) && !in_array($domain, $domains))
				{
					$domains[] = $domain;
				}
			}
		}
		$res->Free();
		
		// 3. Get domains from Email-Admin Plugin (if installed)
		$res = $this->db->Query("SHOW TABLES LIKE '{pre}ea_domains'");
		if($res->RowCount() > 0)
		{
			$res->Free();
			
			$res = $this->db->Query('SELECT `domain` FROM {pre}ea_domains');
			while($row = $res->FetchArray(MYSQLI_ASSOC))
			{
				$domain = strtolower(trim($row['domain']));
				if(!empty($domain) && !in_array($domain, $domains))
				{
					$domains[] = $domain;
				}
			}
			$res->Free();
		}
		else
		{
			$res->Free();
		}
		
		// Sort domains alphabetically
		sort($domains);
		
		return $domains;
	}
	
	/**
	 * Get count of domains
	 */
	public function getDomainCount()
	{
		return count($this->getAllDomains());
	}
	
	/**
	 * Check if a specific domain exists in the system
	 */
	public function domainExists($domain)
	{
		$domain = strtolower(trim($domain));
		$domains = $this->getAllDomains();
		
		return in_array($domain, $domains);
	}
	
	/**
	 * Get domains formatted for a specific protocol with subdomain
	 */
	public function getProtocolDomains($subdomain = '')
	{
		$domains = $this->getAllDomains();
		$result = array();
		
		foreach($domains as $domain)
		{
			if(!empty($subdomain))
			{
				$result[] = $subdomain . '.' . $domain;
			}
			else
			{
				$result[] = $domain;
			}
		}
		
		return $result;
	}
	
	/**
	 * Generate virtual_mailbox_domains for Postfix
	 */
	public function generatePostfixDomains()
	{
		$domains = $this->getAllDomains();
		return implode("\n", $domains);
	}
	
	/**
	 * Generate IMAP namespace configuration for Dovecot
	 */
	public function generateDovecotNamespaces()
	{
		$domains = $this->getAllDomains();
		$config = '';
		
		foreach($domains as $domain)
		{
			$config .= "namespace inbox {\n";
			$config .= "  separator = /\n";
			$config .= "  prefix = \n";
			$config .= "  inbox = yes\n";
			$config .= "}\n\n";
		}
		
		return $config;
	}
}
