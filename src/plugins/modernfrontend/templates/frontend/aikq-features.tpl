<!DOCTYPE html>
<html lang="{$mf_language_code|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.features_page.title" default="Features"} - {$site_name}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background: #f7fafc; color: #2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color: #fff; padding: 40px 20px; text-align: center; }
        .header h1 { font-size: 36px; font-weight: 700; }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
        .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); border: 2px solid transparent; transition: .25s; }
        .card:hover { transform: translateY(-3px); border-color: #76B82A; }
        .icon { width: 48px; height: 48px; border-radius: 12px; display:flex; align-items:center; justify-content:center; background: #76B82A; color:#fff; font-weight:700; margin-bottom: 12px; }
        .card h3 { font-size: 18px; margin-bottom: 8px; }
        .card p { color:#4a5568; }
        .content { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); margin-top: 24px; }
        .back-link { display:inline-block; margin-top: 24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
        .back-link:hover { background:#5a9020; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{mf_t key="frontend.features_page.heading" default="Features"}</h1>
        <p>{mf_t key="frontend.features_page.subheading" default="Alles, was Sie für professionelles E-Mail-Hosting brauchen"}</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="grid">
                <div class="card"><div class="icon">🔒</div><h3>{mf_t key="frontend.features_page.security_title" default="Sicherheit"}</h3><p>{mf_t key="frontend.features_page.security_text" default="SSL/TLS, 2FA, Spam- & Phishing-Schutz."}</p></div>
                <div class="card"><div class="icon">⚡</div><h3>{mf_t key="frontend.features_page.performance_title" default="Performance"}</h3><p>{mf_t key="frontend.features_page.performance_text" default="Schnelle Zustellung, moderne Infrastruktur."}</p></div>
                <div class="card"><div class="icon">👥</div><h3>{mf_t key="frontend.features_page.team_title" default="Teamfähig"}</h3><p>{mf_t key="frontend.features_page.team_text" default="Gemeinsame Postfächer, geteilte Kalender."}</p></div>
                <div class="card"><div class="icon">📱</div><h3>{mf_t key="frontend.features_page.mobile_title" default="Mobil & Desktop"}</h3><p>{mf_t key="frontend.features_page.mobile_text" default="IMAP/POP3/JMAP, Webmail, Mobile Clients."}</p></div>
                <div class="card"><div class="icon">🇩🇪</div><h3>{mf_t key="frontend.features_page.germany_title" default="Made in Germany"}</h3><p>{mf_t key="frontend.features_page.germany_text" default="DSGVO-konforme Datenhaltung in DE."}</p></div>
                <div class="card"><div class="icon">🎧</div><h3>{mf_t key="frontend.features_page.support_title" default="Support"}</h3><p>{mf_t key="frontend.features_page.support_text" default="Kompetent, schnell, erreichbar."}</p></div>
            </div>
            <div class="content">
                <h2 style="margin-bottom:12px;">{mf_t key="frontend.features_page.details" default="Details"}</h2>
                <p>{mf_t key="frontend.features_page.details_text" default="Diese Seite nutzt das CRM-Textsystem. Hinterlegen Sie Inhalte unter \u201eSprachen \u2192 Custom Texts\u201c mit dem Schl\u00fcssel"} <code>features</code>.</p>
            </div>
        {/if}
        <a href="/" class="back-link">← {mf_t key="frontend.features_page.back_home" default="Zurück zur Startseite"}</a>
    </div>
</body>
</html>
