<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Über uns - {$site_name}</title>
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
            padding: 60px 20px;
            text-align: center;
        }
        .header h1 { font-size: 42px; font-weight: 600; margin-bottom: 15px; }
        .header p { font-size: 18px; opacity: 0.9; max-width: 600px; margin: 0 auto; }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #76B82A;
            margin: 40px 0 20px 0;
            font-size: 28px;
        }
        h2:first-child { margin-top: 0; }
        p { margin-bottom: 20px; color: #333; font-size: 16px; }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(118, 184, 42, 0.2);
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .feature-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .feature-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .cta-box {
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            margin: 50px 0 30px 0;
        }
        .cta-box h3 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .cta-box p {
            font-size: 16px;
            margin-bottom: 25px;
            color: white;
            opacity: 0.95;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: white;
            color: #76B82A;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #76B82A;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .back-link:hover { background: #5a9020; }
        @media (max-width: 768px) {
            .container { margin: 20px; padding: 30px 20px; }
            .feature-grid { grid-template-columns: 1fr; }
            .header { padding: 40px 20px; }
            .header h1 { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Über {$site_name}</h1>
        <p>Sicherer E-Mail-Service entwickelt in Deutschland</p>
    </div>
    
    <div class="container">
        <h2>Unsere Mission</h2>
        <p>
            Wir bei {$site_name} haben es uns zur Aufgabe gemacht, sichere und zuverlässige E-Mail-Kommunikation 
            für alle zugänglich zu machen. In einer Zeit, in der Datenschutz und Privatsphäre immer wichtiger werden, 
            bieten wir eine Lösung, der Sie vertrauen können.
        </p>
        
        <h2>Warum {$site_name}?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Maximale Sicherheit</div>
                <div class="feature-description">
                    Ende-zu-Ende-Verschlüsselung und höchste Sicherheitsstandards schützen Ihre Daten.
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🇩🇪</div>
                <div class="feature-title">Made in Germany</div>
                <div class="feature-description">
                    Entwickelt und gehostet in Deutschland - DSGVO-konform und vertrauenswürdig.
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Schnell & Zuverlässig</div>
                <div class="feature-description">
                    Moderne Infrastruktur garantiert schnelle Zustellung und hohe Verfügbarkeit.
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Einfach zu nutzen</div>
                <div class="feature-description">
                    Intuitive Benutzeroberfläche macht E-Mail-Verwaltung zum Kinderspiel.
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">💚</div>
                <div class="feature-title">Umweltfreundlich</div>
                <div class="feature-description">
                    Unsere Server laufen mit 100% Ökostrom für eine grünere Zukunft.
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <div class="feature-title">Datenschutz</div>
                <div class="feature-description">
                    Ihre Privatsphäre ist uns heilig - keine Werbung, kein Tracking, kein Verkauf Ihrer Daten.
                </div>
            </div>
        </div>
        
        <h2>Unsere Werte</h2>
        <p>
            <strong>Transparenz:</strong> Wir glauben an offene Kommunikation und transparente Geschäftspraktiken. 
            Sie haben jederzeit die volle Kontrolle über Ihre Daten.
        </p>
        <p>
            <strong>Innovation:</strong> Wir entwickeln unsere Dienste ständig weiter und integrieren die neuesten 
            Technologien, um Ihnen das beste E-Mail-Erlebnis zu bieten.
        </p>
        <p>
            <strong>Kundenservice:</strong> Unser Support-Team steht Ihnen jederzeit zur Verfügung und hilft bei 
            allen Fragen rund um Ihren E-Mail-Account.
        </p>
        
        <div class="cta-box">
            <h3>Überzeugt? Starten Sie jetzt!</h3>
            <p>Erstellen Sie Ihr kostenloses E-Mail-Konto in wenigen Sekunden.</p>
            <a href="/register" class="cta-button">Jetzt kostenlos registrieren</a>
        </div>
        
        <div style="text-align: center;">
            <a href="/" class="back-link">← Zurück zur Startseite</a>
        </div>
    </div>
</body>
</html>
