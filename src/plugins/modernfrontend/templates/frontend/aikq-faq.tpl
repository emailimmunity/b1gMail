<!DOCTYPE html>
<html lang="{$mf_language_code|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{mf_t key="frontend.faq_page.title" default="FAQ"} - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding: 40px 20px; text-align:center; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .qa { background:#fff; border-radius:12px; padding:20px; box-shadow:0 6px 18px rgba(0,0,0,.08); margin-bottom:14px; }
        .back-link { display:inline-block; margin-top:24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{mf_t key="frontend.faq_page.heading" default="FAQ"}</h1>
        <p>{mf_t key="frontend.faq_page.subheading" default="Häufig gestellte Fragen und Antworten"}</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="qa"><strong>{mf_t key="frontend.faq_page.q1" default="Wie registriere ich mich?"}</strong><br>{mf_t key="frontend.faq_page.a1" default="Über \u201eRegistrieren\u201c oben rechts."}}</div>
            <div class="qa"><strong>{mf_t key="frontend.faq_page.q2" default="Unterstützte Protokolle?"}</strong><br>{mf_t key="frontend.faq_page.a2" default="IMAP, POP3, JMAP, SMTP."}}</div>
            <div class="qa"><strong>{mf_t key="frontend.faq_page.q3" default="Wie erreiche ich den Support?"}</strong><br>{mf_t key="frontend.faq_page.a3" default="Per E-Mail an support@domain."}}</div>
            <p style="margin-top:12px; color:#4a5568;">{mf_t key="frontend.faq_page.manage_text" default="Verwalten Sie Inhalte über \u201eSprachen \u2192 Custom Texts\u201c mit Schlüssel"} <code>faq</code>.</p>
        {/if}
        <a href="/" class="back-link">← {mf_t key="frontend.faq_page.back_home" default="Zurück zur Startseite"}</a>
    </div>
</body>
</html>
