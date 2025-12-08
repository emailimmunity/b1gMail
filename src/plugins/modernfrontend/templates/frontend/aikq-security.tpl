<!DOCTYPE html>
<html lang="{$mf_language_code|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.security_page.title" default="Sicherheit"} - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding: 40px 20px; text-align:center; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.08); border:2px solid transparent; transition:.25s; }
        .card:hover { border-color:#76B82A; }
        .list { display:grid; grid-template-columns:1fr; gap:16px; }
        .back-link { display:inline-block; margin-top:24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{mf_t key="frontend.security_page.heading" default="Sicherheit"}</h1>
        <p>{mf_t key="frontend.security_page.subheading" default="Schutz Ihrer Daten nach höchsten Standards"}</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="card">
                <h2 style="margin-bottom:12px;">{mf_t key="frontend.security_page.measures" default="Unsere Maßnahmen"}</h2>
                <div class="list">
                    <div>• {mf_t key="frontend.security_page.ssl" default="SSL/TLS überall"}</div>
                    <div>• {mf_t key="frontend.security_page.2fa" default="2-Faktor-Authentifizierung"}</div>
                    <div>• {mf_t key="frontend.security_page.spam_filter" default="Spam- & Virenfilter"}</div>
                    <div>• {mf_t key="frontend.security_page.phishing" default="Phishing-Schutz"}</div>
                    <div>• {mf_t key="frontend.security_page.encryption" default="Verschlüsselter Speicher"}</div>
                    <div>• {mf_t key="frontend.security_page.updates" default="Regelmäßige Sicherheitsupdates"}</div>
                </div>
                <p style="margin-top:16px; color:#4a5568;">{mf_t key="frontend.security_page.manage_text" default="Verwalten Sie Inhalte über \u201eSprachen \u2192 Custom Texts\u201c mit Schlüssel"} <code>security</code>.</p>
            </div>
        {/if}
        <a href="/" class="back-link">← {mf_t key="frontend.security_page.back_home" default="Zurück zur Startseite"}</a>
    </div>
</body>
</html>
