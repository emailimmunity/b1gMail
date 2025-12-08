<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt - {$site_name}</title>
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
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 { font-size: 36px; font-weight: 600; margin-bottom: 10px; }
        .header p { font-size: 16px; opacity: 0.9; }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #76B82A;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #76B82A;
            box-shadow: 0 0 0 3px rgba(118, 184, 42, 0.1);
        }
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #76B82A 0%, #5a9020 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(118, 184, 42, 0.3);
        }
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .info-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .info-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .info-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .info-text {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
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
        .back-link:hover { background: #5a9020; }
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
            .contact-form,
            .contact-info {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kontakt</h1>
        <p>Wir helfen Ihnen gerne weiter</p>
    </div>
    
    <div class="container">
        <div class="contact-grid">
            <div class="contact-form">
                <h2>Nachricht senden</h2>
                
                {if $success}
                    <div class="alert alert-success">
                        ✓ Vielen Dank für Ihre Nachricht! Wir melden uns schnellstmöglich bei Ihnen.
                    </div>
                {/if}
                
                {if $error}
                    <div class="alert alert-error">
                        ✗ {$error}
                    </div>
                {/if}
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-Mail-Adresse *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Betreff *</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Nachricht *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="btn-submit">
                        Nachricht senden
                    </button>
                </form>
            </div>
            
            <div class="contact-info">
                <h2>Kontaktinformationen</h2>
                
                <div class="info-item">
                    <div class="info-icon">📧</div>
                    <div class="info-title">E-Mail</div>
                    <div class="info-text">
                        {$support_email}<br>
                        Wir antworten innerhalb von 24 Stunden
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-title">Telefon</div>
                    <div class="info-text">
                        [Telefonnummer]<br>
                        Mo-Fr: 9:00 - 17:00 Uhr
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-title">Adresse</div>
                    <div class="info-text">
                        [Firmenname]<br>
                        [Straße und Hausnummer]<br>
                        [PLZ und Ort]
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">⏰</div>
                    <div class="info-title">Support-Zeiten</div>
                    <div class="info-text">
                        Montag - Freitag: 9:00 - 18:00 Uhr<br>
                        Samstag - Sonntag: Geschlossen
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="/" class="back-link">← Zurück zur Startseite</a>
        </div>
    </div>
</body>
</html>
