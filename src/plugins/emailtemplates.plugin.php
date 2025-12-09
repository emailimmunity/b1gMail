<?php
/*
 * b1gMail Email Templates Plugin
 * (c) 2025 b1gMail Project
 *
 * Save and reuse email templates with placeholders
 * PHP 8.0+ compatible (tested with PHP 8.1-8.3)`n * Uses modern PHP 8.x features: Property Types, Constructor Promotion, Named Arguments
 */

define('EMAILTEMPLATES_VERSION', '2.0.0');

/**
 * Email Templates Plugin
 */
class EmailTemplatesPlugin extends BMPlugin
{
	private array $prefs = [];
	
	public function __construct()
	{
		$this->type = BMPLUGIN_DEFAULT;
		$this->name = 'Email Templates';
		$this->author = 'b1gMail Project';
		$this->version = EMAILTEMPLATES_VERSION;
		$this->website = 'https://www.b1gmail.org/';
		
		$this->admin_pages = false;
	}
	
	public function OnLoad(): void
	{
		global $eventHandler;
		$eventHandler->registerHandler('ComposePageLoad', [$this, 'OnComposePageLoad']);
	}
	
	public function OnInstall(): bool
	{
		global $db;
		
		// Templates table
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}email_templates (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			name VARCHAR(255) NOT NULL,
			category VARCHAR(100) DEFAULT NULL,
			subject VARCHAR(500) DEFAULT NULL,
			body TEXT NOT NULL,
			html TINYINT(1) DEFAULT 0,
			placeholders TEXT DEFAULT NULL,
			created_at INT NOT NULL,
			updated_at INT NOT NULL,
			used_count INT DEFAULT 0,
			INDEX (user_id),
			INDEX (category)
		)');
		
		// Categories table
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}email_template_categories (
			id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			name VARCHAR(100) NOT NULL,
			color VARCHAR(7) DEFAULT "#667eea",
			created_at INT NOT NULL,
			INDEX (user_id)
		)');
		
		PutLog('Email Templates Plugin installed', PRIO_NOTE, __FILE__, __LINE__);
		return true;
	}
	
	public function OnComposePageLoad(array $params): void
	{
		global $thisUser, $tpl;
		
		// Load user templates
		$templates = $this->getUserTemplates($thisUser);
		$categories = $this->getCategories($thisUser);
		
		$tpl->assign('email_templates', $templates);
		$tpl->assign('template_categories', $categories);
		
		// Add template selector to compose page
		$tpl->assign('show_template_selector', true);
	}
	
	private function getUserTemplates(int $userID): array
	{
		global $db;
		
		$res = $db->Query('SELECT * FROM {pre}email_templates WHERE user_id=? ORDER BY name', $userID);
		$templates = [];
		while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$templates[] = $row;
		}
		$res->Free();
		
		return $templates;
	}
	
	private function getCategories(int $userID): array
	{
		global $db;
		
		$res = $db->Query('SELECT * FROM {pre}email_template_categories WHERE user_id=? ORDER BY name', $userID);
		$categories = [];
		while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
			$categories[] = $row;
		}
		$res->Free();
		
		return $categories;
	}
	
	public function saveTemplate(int $userID, array $data): int
	{
		global $db;
		
		$db->Query('INSERT INTO {pre}email_templates 
		           (user_id, name, category, subject, body, html, placeholders, created_at, updated_at)
		           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
			$userID,
			$data['name'],
			$data['category'] ?? null,
			$data['subject'] ?? '',
			$data['body'],
			$data['html'] ? 1 : 0,
			json_encode($this->extractPlaceholders($data['body'])),
			time(),
			time()
		);
		
		return $db->Insert_ID();
	}
	
	private function extractPlaceholders(string $text): array
	{
		preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $text, $matches);
		return array_unique($matches[1]);
	}
	
	public function applyTemplate(int $templateID, array $replacements = []): array
	{
		global $db;
		
		$res = $db->Query('SELECT * FROM {pre}email_templates WHERE id=?', $templateID);
		if ($res->RowCount() == 0) {
			$res->Free();
			return [];
		}
		
		$template = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Apply replacements
		$subject = $template['subject'];
		$body = $template['body'];
		
		foreach ($replacements as $key => $value) {
			$placeholder = '{{' . $key . '}}';
			$subject = str_replace($placeholder, $value, $subject);
			$body = str_replace($placeholder, $value, $body);
		}
		
		// Add default placeholders
		$defaults = [
			'date' => date('Y-m-d'),
			'time' => date('H:i'),
			'datetime' => date('Y-m-d H:i:s'),
			'user_name' => $GLOBALS['thisUser']->name ?? '',
			'user_email' => $GLOBALS['thisUser']->email ?? ''
		];
		
		foreach ($defaults as $key => $value) {
			$placeholder = '{{' . $key . '}}';
			$subject = str_replace($placeholder, $value, $subject);
			$body = str_replace($placeholder, $value, $body);
		}
		
		// Update usage count
		$db->Query('UPDATE {pre}email_templates SET used_count=used_count+1 WHERE id=?', $templateID);
		
		return [
			'subject' => $subject,
			'body' => $body,
			'html' => (bool)$template['html']
		];
	}
}

$plugins->registerPlugin('EmailTemplatesPlugin');
