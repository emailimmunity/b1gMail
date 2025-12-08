<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressum - {$site_name}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 600;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
            opacity: 0.9;
        }
        
        .header a:hover {
            opacity: 1;
            text-decoration: underline;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #76B82A;
            margin: 30px 0 15px 0;
            font-size: 24px;
        }
        
        h2:first-child {
            margin-top: 0;
        }
        
        p {
            margin-bottom: 15px;
            color: #333;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background: #76B82A;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        
        .back-link:hover {
            background: #5a9020;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Impressum</h1>
        <a href="/">← Zurück zur Startseite</a>
    </div>
    
    <div class="container">
        {if $impressum_content}
            {* Custom content from database (Admin-managed) *}
            {$impressum_content}
        {else}
            {* Fallback content if no custom text is defined *}
        <h2>Angaben gemäß § 5 TMG</h2>
        <p>
            <strong>Betreiber:</strong><br>
            [Firmenname]<br>
            [Straße und Hausnummer]<br>
            [PLZ und Ort]
        </p>
        
        <h2>Kontakt</h2>
        <p>
            <strong>Telefon:</strong> [Telefonnummer]<br>
            <strong>E-Mail:</strong> [E-Mail-Adresse]<br>
            <strong>Website:</strong> {$site_name}
        </p>
        
        <h2>Vertretungsberechtigte</h2>
        <p>
            [Name der vertretungsberechtigten Person(en)]
        </p>
        
        <h2>Handelsregister</h2>
        <p>
            <strong>Registergericht:</strong> [Gericht]<br>
            <strong>Registernummer:</strong> [HRB/HRA Nummer]
        </p>
        
        <h2>Umsatzsteuer-ID</h2>
        <p>
            <strong>Umsatzsteuer-Identifikationsnummer gemäß §27a Umsatzsteuergesetz:</strong><br>
            [USt-IdNr.]
        </p>
        
        <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
        <p>
            [Name]<br>
            [Adresse]
        </p>
        
        <h2>Streitschlichtung</h2>
        <p>
            Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: 
            <a href="https://ec.europa.eu/consumers/odr" target="_blank" style="color: #76B82A;">https://ec.europa.eu/consumers/odr</a>.<br>
            Unsere E-Mail-Adresse finden Sie oben im Impressum.
        </p>
        <p>
            Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.
        </p>
        
        <h2>Haftung für Inhalte</h2>
        <p>
            Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. 
            Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen 
            zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
        </p>
        
        <a href="/" class="back-link">← Zurück zur Startseite</a>
        {/if}
    </div>
</body>
</html>
