<?php
/**
 * Reseller Management Class
 */

if(!defined('B1GMAIL_INIT'))
	die('Direct access not allowed');

class BMReseller
{
	private $resellerID;
	private $db;
	
	public function __construct($resellerID)
	{
		global $db;
		$this->resellerID = $resellerID;
		$this->db = $db;
	}
	
	public function getTenants()
	{
		$res = $this->db->Query('SELECT * FROM {pre}reseller_tenants 
			WHERE reseller_id=? AND status="active"', $this->resellerID);
		$tenants = array();
		while($row = $res->FetchArray(MYSQLI_ASSOC))
			$tenants[] = $row;
		return $tenants;
	}
	
	public function createTenant($data)
	{
		$this->db->Query('INSERT INTO {pre}reseller_tenants 
			(reseller_id, tenant_name, company, contact_email, max_users, status, created_at)
			VALUES (?, ?, ?, ?, ?, "active", NOW())',
			$this->resellerID, $data['name'], $data['company'], 
			$data['email'], $data['max_users']);
		return $this->db->InsertId();
	}
}
