<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allgemeine Geschäftsbedingungen - {$site_name}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .header h1 { font-size: 32px; font-weight: 600; }
        .header a {
            color: white;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
            opacity: 0.9;
        }
        .header a:hover { opacity: 1; text-decoration: underline; }
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
        h2:first-child { margin-top: 0; }
        h3 {
            color: #333;
            margin: 20px 0 10px 0;
            font-size: 18px;
        }
        p { margin-bottom: 15px; color: #333; }
        ul { margin: 10px 0 15px 30px; }
        li { margin-bottom: 8px; color: #333; }
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
        .back-link:hover { background: #5a9020; }
        @media (max-width: 768px) {
            .container { margin: 20px; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Allgemeine Geschäftsbedingungen (AGB)</h1>
        <a href="/">← Zurück zur Startseite</a>
    </div>
    
    <div class="container">
        {if $agb_content}
            {* Custom content from database (Admin-managed) *}
            {$agb_content}
        {else}
            {* Fallback content if no custom text is defined *}
        <h2>§ 1 Geltungsbereich</h2>
        <p>Diese Allgemeinen Geschäftsbedingungen gelten für alle Verträge über die Nutzung der E-Mail-Dienste von {$site_name}.</p>
        
        <h2>§ 2 Vertragsgegenstand</h2>
        <p>Der Anbieter stellt dem Kunden E-Mail-Postfächer sowie damit verbundene Dienste zur Verfügung:</p>
        <ul>
            <li>E-Mail-Empfang und -Versand</li>
            <li>Webmail-Zugriff</li>
            <li>IMAP/POP3/SMTP-Zugriff</li>
            <li>Speicherplatz gemäß gewähltem Tarif</li>
            <li>Kalender- und Kontaktverwaltung</li>
        </ul>
        
        <h2>§ 3 Vertragsschluss</h2>
        <p>Der Vertrag kommt durch die Registrierung des Kunden und die Bestätigung durch den Anbieter zustande. Mit der Registrierung erklärt der Kunde, diese AGB gelesen zu haben und akzeptiert sie.</p>
        
        <h2>§ 4 Leistungsumfang</h2>
        <h3>4.1 Verfügbarkeit</h3>
        <p>Der Anbieter bemüht sich um eine möglichst hohe Verfügbarkeit der Dienste. Eine Verfügbarkeit von 100% kann jedoch nicht garantiert werden.</p>
        
        <h3>4.2 Wartungsarbeiten</h3>
        <p>Der Anbieter behält sich vor, die Dienste für Wartungsarbeiten vorübergehend zu unterbrechen. Planbare Wartungsarbeiten werden nach Möglichkeit angekündigt.</p>
        
        <h2>§ 5 Pflichten des Kunden</h2>
        <p>Der Kunde verpflichtet sich:</p>
        <ul>
            <li>Seine Zugangsdaten geheim zu halten</li>
            <li>Keine rechtswidrigen Inhalte zu versenden</li>
            <li>Keine Spam-Mails zu versenden</li>
            <li>Die Dienste nicht für illegale Zwecke zu nutzen</li>
            <li>Die Speicherkapazität nicht durch übermäßig große Anhänge zu missbrauchen</li>
        </ul>
        
        <h2>§ 6 Vergütung und Zahlungsbedingungen</h2>
        <p>Die Vergütung richtet sich nach dem vom Kunden gewählten Tarif. Kostenlose Tarife können Einschränkungen im Funktionsumfang haben. Zahlungspflichtige Tarife werden monatlich oder jährlich im Voraus abgerechnet.</p>
        
        <h2>§ 7 Laufzeit und Kündigung</h2>
        <h3>7.1 Vertragslaufzeit</h3>
        <p>Der Vertrag wird auf unbestimmte Zeit geschlossen. Kostenlose Konten können jederzeit vom Kunden gelöscht werden. Kostenpflichtige Konten haben eine Mindestlaufzeit gemäß gewähltem Tarif.</p>
        
        <h3>7.2 Ordentliche Kündigung</h3>
        <p>Beide Parteien können den Vertrag mit einer Frist von 30 Tagen zum Monatsende kündigen.</p>
        
        <h3>7.3 Außerordentliche Kündigung</h3>
        <p>Das Recht zur außerordentlichen Kündigung aus wichtigem Grund bleibt unberührt. Ein wichtiger Grund liegt insbesondere vor bei:</p>
        <ul>
            <li>Verstoß gegen diese AGB</li>
            <li>Nutzung für illegale Zwecke</li>
            <li>Versand von Spam</li>
            <li>Zahlungsverzug</li>
        </ul>
        
        <h2>§ 8 Datenschutz</h2>
        <p>Der Anbieter behandelt alle Kundendaten vertraulich und gemäß der geltenden Datenschutzbestimmungen. Details sind in der Datenschutzerklärung geregelt.</p>
        
        <h2>§ 9 Haftung</h2>
        <h3>9.1 Haftungsbeschränkung</h3>
        <p>Der Anbieter haftet nur für Vorsatz und grobe Fahrlässigkeit. Die Haftung für leichte Fahrlässigkeit ist ausgeschlossen, soweit nicht Leben, Körper, Gesundheit oder wesentliche Vertragspflichten betroffen sind.</p>
        
        <h3>9.2 Datensicherung</h3>
        <p>Der Kunde ist selbst für die Sicherung seiner Daten verantwortlich. Der Anbieter empfiehlt regelmäßige Backups wichtiger E-Mails und Daten.</p>
        
        <h2>§ 10 Änderungen der AGB</h2>
        <p>Der Anbieter behält sich vor, diese AGB mit einer Ankündigungsfrist von 4 Wochen zu ändern. Widerspricht der Kunde nicht innerhalb der Frist, gelten die geänderten AGB als akzeptiert.</p>
        
        <h2>§ 11 Schlussbestimmungen</h2>
        <h3>11.1 Salvatorische Klausel</h3>
        <p>Sollten einzelne Bestimmungen dieser AGB unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.</p>
        
        <h3>11.2 Anwendbares Recht</h3>
        <p>Es gilt das Recht der Bundesrepublik Deutschland.</p>
        
        <p style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #666; font-size: 14px;">
            <strong>Stand:</strong> {$smarty.now|date_format:"%d.%m.%Y"}
        </p>
        
        <a href="/" class="back-link">← Zurück zur Startseite</a>
        {/if}
    </div>
</body>
</html>
