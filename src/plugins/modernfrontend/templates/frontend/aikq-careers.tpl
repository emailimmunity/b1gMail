<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karriere - {$site_name}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Inter, system-ui, Arial, sans-serif; background:#f7fafc; color:#2d3748; }
        .header { background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%); color:#fff; padding:40px 20px; text-align:center; }
        .container { max-width: 1000px; margin:40px auto; padding:0 20px; }
        .job { background:#fff; border-radius:12px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.08); margin-bottom:20px; }
        .back-link { display:inline-block; margin-top:24px; background:#76B82A; color:#fff; padding:10px 18px; text-decoration:none; border-radius:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Karriere</h1>
        <p>Werden Sie Teil des Teams</p>
    </div>
    <div class="container">
        {if $page_content}
            {$page_content}
        {else}
            <div class="job">
                <h2>Noch keine offenen Stellen</h2>
                <p>Hinterlegen Sie Stellenangebote über „Sprachen → Custom Texts“ mit Schlüssel <code>careers</code>.</p>
            </div>
        {/if}
        <a href="/" class="back-link">← Zurück zur Startseite</a>
    </div>
</body>
</html>
