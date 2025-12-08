<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding: 40px 20px; text-align:center; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .post { background:#fff; border-radius:12px; padding:20px; box-shadow:0 6px 18px rgba(0,0,0,.08); margin-bottom:14px; }
        .back-link { display:inline-block; margin-top:24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Blog</h1>
        <p>Aktuelle News und Updates</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="post"><strong>Start des neuen Webmail-Frontends</strong><br>Modernes Design mit Fokus auf Sicherheit.</div>
            <div class="post"><strong>Neue Business-Pakete</strong><br>Skalierbare Lösungen für Teams und Unternehmen.</div>
            <p style="margin-top:12px; color:#4a5568;">Verwalten Sie Inhalte über „Sprachen → Custom Texts“ mit Schlüssel <code>blog</code>.</p>
        {/if}
        <a href="/" class="back-link">← Zurück zur Startseite</a>
    </div>
</body>
</html>
