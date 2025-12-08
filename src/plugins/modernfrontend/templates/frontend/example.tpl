<!DOCTYPE html>
<html lang="{$mf_language.code|default:'en'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title|default:'ModernFrontend CMS'}</title>
    
    <!-- ModernFrontend Design CSS -->
    {if $mf_css_url}
        <link rel="stylesheet" href="{$mf_css_url}">
    {/if}
    
    <!-- Inline Styles mit Design-Farben -->
    <style>
        :root {
            --primary-color: {$mf_primary_color|default:'#76B82A'};
            --secondary-color: {$mf_secondary_color|default:'#5D9321'};
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        
        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .content-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid var(--primary-color);
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <h1>🚀 ModernFrontend CMS</h1>
            <p>Multi-Domain · Multi-Design · Multi-Lingual System</p>
            
            <!-- Language Switcher -->
            {if $mf_language_switcher}
                <div style="margin-top:15px;">
                    {$mf_language_switcher}
                </div>
            {/if}
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Box -->
        <div class="content-box">
            <h2>Willkommen! / Welcome! / Bienvenue!</h2>
            <p>Dieses Beispiel zeigt die ModernFrontend CMS Integration.</p>
            
            <!-- Current State Info -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">🌍 Aktuelle Domain</div>
                    <div class="info-value">{$mf_domain.hostname|default:'Keine Domain'}</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">🎨 Aktuelles Design</div>
                    <div class="info-value">{$mf_design.name|default:'Kein Design'}</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">🌐 Aktuelle Sprache</div>
                    <div class="info-value">
                        {if $mf_language.flag_icon}{$mf_language.flag_icon} {/if}
                        {$mf_language.name|default:'Keine Sprache'}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Feature Demo -->
        <div class="content-box">
            <h2>✨ Features</h2>
            
            <h3>1. Automatische Domain-Erkennung</h3>
            <p>Das System erkennt automatisch die aktuelle Domain und lädt das zugehörige Design.</p>
            <ul>
                <li><strong>Hostname:</strong> {$mf_domain.hostname|default:'localhost'}</li>
                <li><strong>Design ID:</strong> {$mf_domain.design_id|default:'-'}</li>
                <li><strong>Status:</strong> {if $mf_domain.is_active == 1}✅ Aktiv{else}❌ Inaktiv{/if}</li>
            </ul>
            
            <h3>2. Automatisches Design-Loading</h3>
            <p>Das Design wird automatisch basierend auf der Domain geladen.</p>
            <ul>
                <li><strong>Design Name:</strong> {$mf_design.name|default:'-'}</li>
                <li><strong>Primärfarbe:</strong> <span style="display:inline-block; width:20px; height:20px; background:{$mf_primary_color|default:'#76B82A'}; border:1px solid #ddd; vertical-align:middle;"></span> {$mf_primary_color|default:'#76B82A'}</li>
                <li><strong>Sekundärfarbe:</strong> <span style="display:inline-block; width:20px; height:20px; background:{$mf_secondary_color|default:'#5D9321'}; border:1px solid #ddd; vertical-align:middle;"></span> {$mf_secondary_color|default:'#5D9321'}</li>
            </ul>
            
            <h3>3. Automatische Sprach-Erkennung</h3>
            <p>Die Sprache wird automatisch erkannt über URL, Cookie, Browser oder Domain-Default.</p>
            <ul>
                <li><strong>Sprache:</strong> {$mf_language.name|default:'-'} ({$mf_language.code|default:'-'})</li>
                <li><strong>Region:</strong> {$mf_language.region|default:'-'}</li>
                <li><strong>Flagge:</strong> {$mf_language.flag_icon|default:'-'}</li>
            </ul>
            
            <h3>4. Übersetzungs-System (Bereit)</h3>
            <p>Das System ist bereit für automatische Übersetzungen via Google Translate & DeepL.</p>
            <p><em>Konfiguriere API-Keys im Admin-Bereich um Übersetzungen zu aktivieren.</em></p>
        </div>
        
        <!-- Example Code -->
        <div class="content-box">
            <h2>💻 Code-Beispiele</h2>
            
            <h3>PHP-Verwendung:</h3>
            <pre style="background:#f8f9fa; padding:15px; border-radius:4px; overflow-x:auto;"><code>// Frontend-Helper laden
require_once(B1GMAIL_DIR . 'plugins/modernfrontend/frontend-helper.php');

// Text übersetzen
$german = mf_translate('Hello World', 'de', 'en');
echo $german; // Output: "Hallo Welt"

// Aktuellen State abrufen
$state = mf_get_state();
echo $state['language']['name']; // z.B. "German"

// Design-Farben abrufen
$color = mf_get_primary_color();
echo $color; // z.B. "#76B82A"

// Language Switcher generieren
echo mf_language_switcher('buttons', true, false);
</code></pre>

            <h3>Smarty Template-Verwendung:</h3>
            <pre style="background:#f8f9fa; padding:15px; border-radius:4px; overflow-x:auto;"><code>{* Aktuelle Sprache *}
<p>Sprache: {literal}{$mf_language.name}{/literal}</p>

{* Design-Farben *}
<div style="color: {literal}{$mf_primary_color}{/literal}">Text</div>

{* Language Switcher *}
{literal}{$mf_language_switcher}{/literal}

{* Design CSS einbinden *}
<link rel="stylesheet" href="{literal}{$mf_css_url}{/literal}">
</code></pre>
        </div>
        
        <!-- Admin Links -->
        <div class="content-box">
            <h2>⚙️ Admin-Bereich</h2>
            <p>Verwalte das System über den Admin-Bereich:</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/admin/plugin.page.php?plugin=ModernFrontendPlugin&page=domains" class="btn-primary">🌍 Domains</a>
                <a href="/admin/plugin.page.php?plugin=ModernFrontendPlugin&page=designs" class="btn-primary">🎨 Designs</a>
                <a href="/admin/plugin.page.php?plugin=ModernFrontendPlugin&page=languages" class="btn-primary">🌐 Sprachen</a>
                <a href="/admin/plugin.page.php?plugin=ModernFrontendPlugin&page=translations" class="btn-primary">📝 Übersetzungen</a>
            </div>
        </div>
    </div>
    
    <!-- ModernFrontend Design JS -->
    {if $mf_js_url}
        <script src="{$mf_js_url}"></script>
    {/if}
</body>
</html>
