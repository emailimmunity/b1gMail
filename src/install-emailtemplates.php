<?php
/**
 * Install EmailTemplates Plugin
 * Creates required database tables for user email templates
 */

require_once __DIR__ . '/serverlib/init.inc.php';
require_once __DIR__ . '/plugins/emailtemplates.plugin.php';

echo "=== EmailTemplates Plugin Installation ===\n\n";

$plugin = new EmailTemplatesPlugin();
echo "Plugin: " . $plugin->name . " v" . $plugin->version . "\n\n";

echo "Erstelle Tabellen...\n";

// EmailTemplates Plugin uses OnInstall() method
if (method_exists($plugin, 'OnInstall')) {
    $result = $plugin->OnInstall();
} else {
    echo "❌ Keine Install-Methode gefunden!\n";
    exit(1);
}

if ($result) {
    echo "✅ Installation erfolgreich!\n\n";
    
    // Prüfe erstellte Tabellen
    echo "Prüfe Tabellen...\n";
    $tables = array(
        'email_templates',
        'email_template_categories'
    );
    
    foreach ($tables as $table) {
        $res = $db->Query('SHOW TABLES LIKE "{pre}' . $table . '"');
        if ($res->FetchArray(MYSQLI_NUM)) {
            echo "  ✅ {$table}\n";
            
            // Zeige Tabellenstruktur
            $res2 = $db->Query('DESCRIBE {pre}' . $table);
            $fieldCount = 0;
            while ($row = $res2->FetchArray(MYSQLI_ASSOC)) {
                $fieldCount++;
            }
            echo "     → {$fieldCount} Felder\n";
            $res2->Free();
        } else {
            echo "  ❌ {$table} - FEHLT!\n";
        }
        $res->Free();
    }
    
    // Füge Demo-Kategorien hinzu (optional, nur wenn keine existieren)
    $res = $db->Query('SELECT COUNT(*) FROM {pre}email_template_categories');
    list($categoryCount) = $res->FetchArray(MYSQLI_NUM);
    $res->Free();
    
    if ($categoryCount == 0) {
        echo "\n📝 Erstelle Standard-Kategorien...\n";
        
        $defaultCategories = [
            ['name' => 'Business', 'color' => '#667eea'],
            ['name' => 'Personal', 'color' => '#48bb78'],
            ['name' => 'Marketing', 'color' => '#ed8936'],
            ['name' => 'Support', 'color' => '#4299e1'],
        ];
        
        foreach ($defaultCategories as $cat) {
            $db->Query('INSERT INTO {pre}email_template_categories 
                       (user_id, name, color, created_at) VALUES (-1, ?, ?, ?)',
                $cat['name'],
                $cat['color'],
                time()
            );
            echo "  ✅ Kategorie: {$cat['name']}\n";
        }
    }
    
    // Füge Demo-Templates hinzu (optional, nur wenn keine existieren)
    $res = $db->Query('SELECT COUNT(*) FROM {pre}email_templates');
    list($templateCount) = $res->FetchArray(MYSQLI_NUM);
    $res->Free();
    
    if ($templateCount == 0) {
        echo "\n📧 Erstelle Demo-Templates...\n";
        
        $defaultTemplates = [
            [
                'name' => 'Welcome Email',
                'category' => 'Business',
                'subject' => 'Welcome to {{service_name}}!',
                'body' => "Hello {{name}},\n\nWelcome to {{service_name}}! We're excited to have you on board.\n\nBest regards,\n{{sender_name}}",
                'html' => 0
            ],
            [
                'name' => 'Password Reset',
                'category' => 'Support',
                'subject' => 'Reset your password',
                'body' => "Hi {{name}},\n\nYou requested a password reset. Click the link below:\n\n{{reset_link}}\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\n{{service_name}} Team",
                'html' => 0
            ],
            [
                'name' => 'Newsletter',
                'category' => 'Marketing',
                'subject' => '{{month}} Newsletter - {{topic}}',
                'body' => "Dear {{name}},\n\nHere's what's new this month:\n\n{{content}}\n\nStay tuned!\n{{sender_name}}",
                'html' => 0
            ]
        ];
        
        foreach ($defaultTemplates as $tpl) {
            $db->Query('INSERT INTO {pre}email_templates 
                       (user_id, name, category, subject, body, html, placeholders, created_at, updated_at, used_count)
                       VALUES (-1, ?, ?, ?, ?, ?, ?, ?, ?, 0)',
                $tpl['name'],
                $tpl['category'],
                $tpl['subject'],
                $tpl['body'],
                $tpl['html'],
                json_encode(['name', 'service_name', 'sender_name', 'content', 'month', 'topic', 'reset_link']),
                time(),
                time()
            );
            echo "  ✅ Template: {$tpl['name']}\n";
        }
    }
    
    echo "\n✅ EmailTemplates Plugin ready for use!\n";
    echo "\nFeatures:\n";
    echo "- User-specific email templates\n";
    echo "- Category organization\n";
    echo "- Placeholder support ({{variable}})\n";
    echo "- HTML and plain-text templates\n";
    echo "- Usage tracking\n";
    
    echo "\nNächste Schritte:\n";
    echo "1. Container neu starten\n";
    echo "2. Im Admin Plugins aktivieren\n";
    echo "3. User-Login → Email Compose → Templates verfügbar\n";
    echo "4. Admin kann System-Templates erstellen (user_id=-1)\n";
} else {
    echo "❌ Installation fehlgeschlagen!\n";
    exit(1);
}
