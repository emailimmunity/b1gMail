<?php
/**
 * AccountMirrorV2 - Password V2 Integration
 * 
 * Makes Account Mirror compatible with encrypted credentials
 * 
 * CHANGES NEEDED in AccountMirrorV2.class.php:
 * 1. Include PasswordManager at top
 * 2. Update mirrorExternalPOP3Emails() method
 * 3. Update mirrorExternalIMAPEmails() method
 */

// ═══════════════════════════════════════════════════════════════
// ADD AT TOP OF AccountMirrorV2.class.php
// ═══════════════════════════════════════════════════════════════

/*
require_once(dirname(__FILE__) . '/../password.class.php');
*/

// ═══════════════════════════════════════════════════════════════
// ADD THIS METHOD TO AccountMirrorV2 CLASS
// ═══════════════════════════════════════════════════════════════

/**
 * Mirror emails from external POP3 accounts
 * 
 * Works with encrypted credentials (Master-Key access!)
 * 
 * @return array Result
 */
public function mirrorExternalPOP3Emails()
{
	global $db;
	
	$result = array('success' => true, 'message' => '', 'mirrored' => 0);
	
	if(!$this->config)
	{
		$result['success'] = false;
		$result['message'] = 'Mirror configuration not loaded';
		return $result;
	}
	
	// Only if external email mirroring is enabled
	if(!$this->config['mirror_emails'])
	{
		return $result;
	}
	
	// Get source user
	$sourceUser = _new('BMUser', array($this->config['userid']));
	
	// Get all POP3 accounts
	$res = $db->Query('SELECT id, p_host, p_user, p_pass, p_pass_iv, p_pass_tag, p_pass_encrypted, p_pass_version, p_port, p_ssl 
	                   FROM {pre}pop3 
	                   WHERE user = ?',
	                   $this->config['userid']);
	
	while($row = $res->FetchArray(MYSQLI_ASSOC))
	{
		try
		{
			// ═══════════════════════════════════════════════════════════════
			// DECRYPT PASSWORD (System has Master-Key access!)
			// ═══════════════════════════════════════════════════════════════
			
			$password = PasswordManager::decryptField($row, 'p_pass', 'pop3');
			
			// Connect to external POP3 server
			$pop3 = new POP3();
			$pop3->Connect($row['p_host'], $row['p_port'], $row['p_ssl'] == 'yes');
			
			if(!$pop3->Login($row['p_user'], $password))
			{
				PutLog(sprintf('AccountMirror v2: External POP3 login failed for #%d (%s)',
				              $this->mirrorId, $row['p_host']),
				       PRIO_WARNING, __FILE__, __LINE__);
				continue;
			}
			
			// Get messages
			$messages = $pop3->ListMessages();
			
			foreach($messages as $msgNum => $msgSize)
			{
				// Fetch message
				$msgData = $pop3->RetrieveMessage($msgNum);
				
				// Mirror to target account
				$mirrorResult = $this->mirrorEmailContent($msgData, FOLDER_INBOX);
				
				if($mirrorResult['success'])
				{
					$result['mirrored']++;
				}
			}
			
			$pop3->Disconnect();
			
			// Log success
			$this->logSync('external_pop3_mirrored', 
			              sprintf('Mirrored %d emails from %s', count($messages), $row['p_host']));
		}
		catch(Exception $e)
		{
			PutLog(sprintf('AccountMirror v2: Error mirroring external POP3 #%d: %s',
			              $row['id'], $e->getMessage()),
			       PRIO_WARNING, __FILE__, __LINE__);
		}
	}
	
	$res->Free();
	
	$result['message'] = sprintf('Mirrored %d external emails', $result['mirrored']);
	
	return $result;
}

/**
 * Mirror email content (helper)
 * 
 * @param string $emailContent Raw email content
 * @param int $folderID Target folder
 * @return array Result
 */
private function mirrorEmailContent($emailContent, $folderID)
{
	global $db;
	
	// Parse email
	$mail = _new('BMMail');
	$mail->Import($emailContent);
	
	// Get target mailbox
	$targetMailbox = _new('BMMailbox', array($this->config['mirror_to']));
	
	// Store in target account
	$mailID = $targetMailbox->AddMail(
		$mail->GetSubject(),
		$mail->GetBody(),
		$folderID,
		$mail->GetFrom(),
		$mail->GetDate(),
		$mail->GetHeaders()
	);
	
	if($mailID > 0)
	{
		// Log to sync log
		$this->logSync('email_mirrored', sprintf('Mail #%d mirrored from external source', $mailID));
		
		return array('success' => true, 'mail_id' => $mailID);
	}
	
	return array('success' => false, 'message' => 'Failed to store email');
}

?>

