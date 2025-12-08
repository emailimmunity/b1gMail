<?php
/**
 * b1gMailServer Mail Routing
 * (c) 2025 b1gMail Development
 *
 * Automatisches Routing zwischen b1gMail <-> Postfix <-> Dovecot <-> Grommunio
 */

class MailRouting
{
	private $db;
	private $prefs;
	
	public function __construct($db, $prefs)
	{
		$this->db = $db;
		$this->prefs = $prefs;
	}
	
	/**
	 * Determine routing destination for outgoing mail
	 * Returns: 'internal', 'postfix', 'grommunio', or 'external'
	 */
	public function getOutgoingRoute($recipientEmail)
	{
		// Check if recipient is local user
		$res = $this->db->Query('SELECT `id` FROM {pre}users WHERE `email`=?', $recipientEmail);
		$isLocalUser = $res->RowCount() > 0;
		$res->Free();
		
		if($isLocalUser)
		{
			// Check if user prefers Grommunio
			if(!empty($this->prefs['grommunio_enabled']))
			{
				return 'grommunio';
			}
			
			return 'internal'; // Deliver to b1gMail mailbox
		}
		
		// External recipient - route via Postfix
		if(!empty($this->prefs['postfix_enabled']))
		{
			return 'postfix';
		}
		
		return 'external'; // Use b1gMail's built-in SMTP
	}
	
	/**
	 * Route outgoing mail to appropriate destination
	 */
	public function routeOutgoingMail($mailID, $recipientEmail, $mailData)
	{
		$route = $this->getOutgoingRoute($recipientEmail);
		
		switch($route)
		{
			case 'internal':
				// Already delivered to b1gMail mailbox
				return array('success' => true, 'route' => 'internal');
				
			case 'grommunio':
				return $this->deliverToGrommunio($recipientEmail, $mailData);
				
			case 'postfix':
				return $this->deliverToPostfix($recipientEmail, $mailData);
				
			case 'external':
			default:
				// Let b1gMail handle it
				return array('success' => true, 'route' => 'external');
		}
	}
	
	/**
	 * Deliver mail to Grommunio via SMTP
	 */
	private function deliverToGrommunio($recipientEmail, $mailData)
	{
		$grommunioHost = $this->prefs['grommunio_server'];
		$grommunioPort = 25; // SMTP port
		
		// Connect to Grommunio SMTP
		$smtp = fsockopen($grommunioHost, $grommunioPort, $errno, $errstr, 10);
		
		if(!$smtp)
		{
			return array('success' => false, 'message' => "Connection failed: $errstr");
		}
		
		// SMTP conversation
		fgets($smtp); // Banner
		
		fputs($smtp, "HELO " . gethostname() . "\r\n");
		fgets($smtp);
		
		fputs($smtp, "MAIL FROM:<{$mailData['from']}>\r\n");
		fgets($smtp);
		
		fputs($smtp, "RCPT TO:<$recipientEmail>\r\n");
		$response = fgets($smtp);
		
		if(substr($response, 0, 3) != '250')
		{
			fclose($smtp);
			return array('success' => false, 'message' => 'RCPT TO rejected');
		}
		
		fputs($smtp, "DATA\r\n");
		fgets($smtp);
		
		fputs($smtp, $mailData['raw_message'] . "\r\n.\r\n");
		fgets($smtp);
		
		fputs($smtp, "QUIT\r\n");
		fclose($smtp);
		
		return array('success' => true, 'route' => 'grommunio');
	}
	
	/**
	 * Deliver mail to Postfix via SMTP
	 */
	private function deliverToPostfix($recipientEmail, $mailData)
	{
		$postfixHost = $this->prefs['postfix_server'];
		$postfixPort = $this->prefs['postfix_smtp_port'];
		
		// Connect to Postfix
		$smtp = fsockopen($postfixHost, $postfixPort, $errno, $errstr, 10);
		
		if(!$smtp)
		{
			return array('success' => false, 'message' => "Connection failed: $errstr");
		}
		
		// SMTP conversation
		fgets($smtp); // Banner
		
		fputs($smtp, "HELO " . gethostname() . "\r\n");
		fgets($smtp);
		
		fputs($smtp, "MAIL FROM:<{$mailData['from']}>\r\n");
		fgets($smtp);
		
		fputs($smtp, "RCPT TO:<$recipientEmail>\r\n");
		$response = fgets($smtp);
		
		if(substr($response, 0, 3) != '250')
		{
			fclose($smtp);
			return array('success' => false, 'message' => 'RCPT TO rejected');
		}
		
		fputs($smtp, "DATA\r\n");
		fgets($smtp);
		
		fputs($smtp, $mailData['raw_message'] . "\r\n.\r\n");
		fgets($smtp);
		
		fputs($smtp, "QUIT\r\n");
		fclose($smtp);
		
		return array('success' => true, 'route' => 'postfix');
	}
	
	/**
	 * Handle incoming mail from Postfix/Dovecot
	 * This is called when mail arrives via SMTP
	 */
	public function handleIncomingMail($recipientEmail, $mailData)
	{
		// Find recipient user
		$res = $this->db->Query('SELECT `id` FROM {pre}users WHERE `email`=?', $recipientEmail);
		
		if($row = $res->FetchArray(MYSQLI_ASSOC))
		{
			$userID = $row['id'];
			$res->Free();
			
			// Store mail in b1gMail mailbox
			// This would integrate with b1gMail's mail storage system
			return array('success' => true, 'user_id' => $userID);
		}
		
		$res->Free();
		return array('success' => false, 'message' => 'Unknown recipient');
	}
}

/**
 * Grommunio Webhook Handler
 * Receives notifications from Grommunio when mail arrives
 */
class GrommunioWebhook
{
	private $db;
	private $prefs;
	
	public function __construct($db, $prefs)
	{
		$this->db = $db;
		$this->prefs = $prefs;
	}
	
	/**
	 * Handle webhook from Grommunio
	 */
	public function handleWebhook($payload)
	{
		// Validate webhook signature if configured
		if(!empty($this->prefs['grommunio_webhook_secret']))
		{
			$signature = $_SERVER['HTTP_X_GROMMUNIO_SIGNATURE'] ?? '';
			$expectedSignature = hash_hmac('sha256', json_encode($payload), $this->prefs['grommunio_webhook_secret']);
			
			if($signature !== $expectedSignature)
			{
				return array('success' => false, 'message' => 'Invalid signature');
			}
		}
		
		// Process webhook event
		$eventType = $payload['event'] ?? '';
		
		switch($eventType)
		{
			case 'mail.received':
				return $this->handleMailReceived($payload);
				
			case 'user.created':
				return $this->handleUserCreated($payload);
				
			case 'user.deleted':
				return $this->handleUserDeleted($payload);
				
			default:
				return array('success' => true, 'message' => 'Event ignored');
		}
	}
	
	/**
	 * Handle mail received event
	 */
	private function handleMailReceived($payload)
	{
		$recipientEmail = $payload['data']['recipient'] ?? '';
		$messageID = $payload['data']['message_id'] ?? '';
		
		// Sync mail from Grommunio to b1gMail if needed
		// This could use IMAP to fetch the mail
		
		PutLog("Webhook: Mail received for $recipientEmail (Message-ID: $messageID)", PRIO_NOTE, __FILE__, __LINE__);
		
		return array('success' => true);
	}
	
	/**
	 * Handle user created event
	 */
	private function handleUserCreated($payload)
	{
		$userEmail = $payload['data']['email'] ?? '';
		
		PutLog("Webhook: User created in Grommunio: $userEmail", PRIO_NOTE, __FILE__, __LINE__);
		
		return array('success' => true);
	}
	
	/**
	 * Handle user deleted event
	 */
	private function handleUserDeleted($payload)
	{
		$userEmail = $payload['data']['email'] ?? '';
		
		PutLog("Webhook: User deleted in Grommunio: $userEmail", PRIO_NOTE, __FILE__, __LINE__);
		
		return array('success' => true);
	}
}
