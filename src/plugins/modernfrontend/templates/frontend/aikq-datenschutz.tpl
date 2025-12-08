<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenschutzerklärung - {$site_name}</title>
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
        <h1>Datenschutzerklärung</h1>
        <a href="/">← Zurück zur Startseite</a>
    </div>
    
    <div class="container">
        {if $privacy_content}
            {* Custom content from database (Admin-managed) *}
            {$privacy_content}
        {else}
            {* Fallback content if no custom text is defined *}
        <h2>1. Datenschutz auf einen Blick</h2>
        <h3>Allgemeine Hinweise</h3>
        <p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website nutzen und unsere E-Mail-Dienste verwenden.</p>
        
        <h3>Datenerfassung auf dieser Website</h3>
        <p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong></p>
        <p>Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten können Sie dem Impressum dieser Website entnehmen.</p>
        
        <h2>2. Hosting</h2>
        <p>Wir hosten die Inhalte unserer Website und E-Mail-Dienste bei folgendem Anbieter:</p>
        <p><strong>Serverstandort:</strong> Deutschland (DSGVO-konform)</p>
        
        <h2>3. Allgemeine Hinweise und Pflichtinformationen</h2>
        <h3>Datenschutz</h3>
        <p>Wir nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
        
        <h3>Hinweis zur verantwortlichen Stelle</h3>
        <p>Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:</p>
        <p>[Siehe Impressum]</p>
        
        <h2>4. Datenerfassung auf dieser Website</h2>
        <h3>4.1 Cookies</h3>
        <p>Unsere Internetseiten verwenden Cookies. Cookies sind kleine Textdateien, die auf Ihrem Endgerät gespeichert werden. Wir verwenden Cookies für:</p>
        <ul>
            <li>Session-Management (Login-Status)</li>
            <li>Spracheinstellungen</li>
            <li>Sicherheitsfunktionen</li>
        </ul>
        
        <h3>4.2 Server-Log-Dateien</h3>
        <p>Der Provider der Seiten erhebt und speichert automatisch Informationen in Server-Log-Dateien:</p>
        <ul>
            <li>Browsertyp und Browserversion</li>
            <li>Verwendetes Betriebssystem</li>
            <li>Referrer URL</li>
            <li>Hostname des zugreifenden Rechners</li>
            <li>Uhrzeit der Serveranfrage</li>
            <li>IP-Adresse</li>
        </ul>
        <p>Diese Daten werden nicht mit anderen Datenquellen zusammengeführt und nach 7 Tagen automatisch gelöscht.</p>
        
        <h3>4.3 Registrierung</h3>
        <p>Bei der Registrierung für unseren E-Mail-Dienst erheben wir folgende Daten:</p>
        <ul>
            <li>E-Mail-Adresse (Pflichtfeld)</li>
            <li>Passwort (verschlüsselt gespeichert)</li>
            <li>Vorname und Nachname (optional)</li>
            <li>IP-Adresse (zur Missbrauchsprävention)</li>
            <li>Zeitpunkt der Registrierung</li>
        </ul>
        
        <h2>5. E-Mail-Kommunikation</h2>
        <h3>5.1 Verschlüsselung</h3>
        <p>Die Übertragung Ihrer E-Mails erfolgt verschlüsselt über TLS/SSL. Ihre E-Mails werden auf deutschen Servern gespeichert.</p>
        
        <h3>5.2 Speicherung</h3>
        <p>E-Mails werden gemäß Ihrem gewählten Tarif gespeichert:</p>
        <ul>
            <li>Kostenlose Konten: Bis zu [X] GB</li>
            <li>Premium-Konten: Bis zu [X] GB</li>
        </ul>
        <p>Sie können Ihre E-Mails jederzeit selbst löschen.</p>
        
        <h3>5.3 Automatische Löschung</h3>
        <p>Bei Inaktivität von mehr als 12 Monaten kann das Konto automatisch deaktiviert werden. Sie werden zuvor per E-Mail informiert.</p>
        
        <h2>6. Ihre Rechte</h2>
        <p>Sie haben folgende Rechte:</p>
        <ul>
            <li><strong>Auskunftsrecht:</strong> Sie können Auskunft über Ihre gespeicherten Daten verlangen</li>
            <li><strong>Berichtigungsrecht:</strong> Sie können die Berichtigung unrichtiger Daten verlangen</li>
            <li><strong>Löschungsrecht:</strong> Sie können die Löschung Ihrer Daten verlangen</li>
            <li><strong>Einschränkung der Verarbeitung:</strong> Sie können die Einschränkung der Verarbeitung verlangen</li>
            <li><strong>Datenübertragbarkeit:</strong> Sie können Ihre Daten in einem gängigen Format erhalten</li>
            <li><strong>Widerspruchsrecht:</strong> Sie können der Verarbeitung Ihrer Daten widersprechen</li>
        </ul>
        
        <h2>7. Weitergabe von Daten</h2>
        <p>Eine Weitergabe Ihrer Daten an Dritte erfolgt nur:</p>
        <ul>
            <li>Mit Ihrer ausdrücklichen Einwilligung</li>
            <li>Zur Vertragsabwicklung (z.B. Zahlungsdienstleister)</li>
            <li>Aufgrund gesetzlicher Verpflichtungen</li>
        </ul>
        <p><strong>Wir verkaufen Ihre Daten niemals an Dritte!</strong></p>
        
        <h2>8. Datensicherheit</h2>
        <p>Wir verwenden moderne Sicherheitsmaßnahmen:</p>
        <ul>
            <li>TLS/SSL-Verschlüsselung für alle Verbindungen</li>
            <li>Verschlüsselte Passwort-Speicherung</li>
            <li>Regelmäßige Sicherheits-Updates</li>
            <li>Firewall und Intrusion Detection</li>
            <li>Tägliche Backups</li>
        </ul>
        
        <h2>9. Speicherdauer</h2>
        <p>Wir speichern Ihre Daten nur solange, wie dies für die Bereitstellung unserer Dienste erforderlich ist oder gesetzliche Aufbewahrungsfristen bestehen.</p>
        
        <h2>10. Externe Dienste</h2>
        <p>Wir verwenden keine Tracking-Dienste von Drittanbietern wie Google Analytics. Ihre Privatsphäre ist uns wichtig.</p>
        
        <h2>11. Änderungen der Datenschutzerklärung</h2>
        <p>Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den aktuellen rechtlichen Anforderungen entspricht.</p>
        
        <h2>12. Kontakt</h2>
        <p>Bei Fragen zum Datenschutz wenden Sie sich bitte an:</p>
        <p>[Kontaktdaten siehe Impressum]</p>
        
        <p style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0; color: #666; font-size: 14px;">
            <strong>Stand:</strong> {$smarty.now|date_format:"%d.%m.%Y"}
        </p>
        
        <a href="/" class="back-link">← Zurück zur Startseite</a>
        {/if}
    </div>
</body>
</html>
