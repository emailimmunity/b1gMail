<!DOCTYPE html>
<html lang="{$mf_language_code|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.business_page.title" default="Business"} - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding: 40px 20px; text-align:center; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .section { background:#fff; border-radius:12px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.08); }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 24px; }
        .card { background:#f9fafb; border-radius:10px; padding:16px; border:1px solid #e5e7eb; }
        .back-link { display:inline-block; margin-top:24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{mf_t key="frontend.business_page.heading" default="Business"}</h1>
        <p>{mf_t key="frontend.business_page.subheading" default="Die beste Lösung für Unternehmen und Teams"}</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="section">
                <h2>{mf_t key="frontend.business_page.why_title" default="Warum {$site_name} für Unternehmen?"}</h2>
                <div class="grid">
                    <div class="card">• {mf_t key="frontend.business_page.feature1" default="Gemeinsame Postfächer & Delegation"}</div>
                    <div class="card">• {mf_t key="frontend.business_page.feature2" default="Rollen & Berechtigungen"}</div>
                    <div class="card">• {mf_t key="frontend.business_page.feature3" default="SLA & Prioritätssupport"}</div>
                    <div class="card">• {mf_t key="frontend.business_page.feature4" default="API & Integrationen"}</div>
                    <div class="card">• {mf_t key="frontend.business_page.feature5" default="Compliance & DSGVO"}</div>
                    <div class="card">• {mf_t key="frontend.business_page.feature6" default="Skalierbare Pakete"}</div>
                </div>
                <p style="margin-top:16px; color:#4a5568;">{mf_t key="frontend.business_page.manage_text" default="Verwalten Sie Inhalte über \u201eSprachen \u2192 Custom Texts\u201c mit Schlüssel"} <code>business</code>.</p>
            </div>
        {/if}
        <a href="/" class="back-link">← {mf_t key="frontend.business_page.back_home" default="Zurück zur Startseite"}</a>
    </div>
</body>
</html>
