<!DOCTYPE html>
<html lang="{$current_language|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title|default:'E-Mail Service'}</title>
    
    <style>
        :root {
            --primary-color: {$primary_color|default:'#2563eb'};
            --secondary-color: {$secondary_color|default:'#475569'};
            --text-color: #1e293b;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
        }
        
        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .nav {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav a:hover {
            color: var(--primary-color);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        /* Hero */
        .hero {
            padding: 4rem 2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--light-bg) 0%, white 100%);
        }
        
        .hero-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }
        
        .hero p {
            font-size: 1.25rem;
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Features */
        .features {
            padding: 4rem 2rem;
            background: white;
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            padding: 2rem;
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .feature-card p {
            color: var(--secondary-color);
        }
        
        /* CTA */
        .cta {
            padding: 4rem 2rem;
            background: var(--primary-color);
            color: white;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta .btn {
            background: white;
            color: var(--primary-color);
        }
        
        /* Footer */
        .footer {
            padding: 3rem 2rem;
            background: var(--secondary-color);
            color: white;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .footer h4 {
            margin-bottom: 1rem;
        }
        
        .footer ul {
            list-style: none;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="/" class="logo">{$site_name|default:'E-Mail Service'}</a>
            <nav>
                <ul class="nav">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Preise</a></li>
                    <li><a href="#contact">Kontakt</a></li>
                </ul>
            </nav>
            <a href="/?action=login" class="btn btn-primary">Login</a>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-container">
            <h1>Professioneller E-Mail-Service</h1>
            <p>{$tagline|default:'Sichere und zuverlässige E-Mail-Kommunikation für Ihr Business'}</p>
            <div class="hero-buttons">
                <a href="/signup.php" class="btn btn-primary">Kostenlos testen</a>
                <a href="#features" class="btn btn-outline">Mehr erfahren</a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="features-container">
            <h2 class="section-title">Unsere Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Maximale Sicherheit</h3>
                    <p>Ende-zu-Ende Verschlüsselung und höchste Sicherheitsstandards für Ihre E-Mails.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Überall verfügbar</h3>
                    <p>Zugriff von jedem Gerät - Desktop, Tablet oder Smartphone.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3>Blitzschnell</h3>
                    <p>Moderne Infrastruktur für schnellen E-Mail-Versand und -Empfang.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💾</div>
                    <h3>Viel Speicherplatz</h3>
                    <p>Großzügiges Kontingent für all Ihre E-Mails und Anhänge.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Spam-Schutz</h3>
                    <p>Intelligente Filter gegen Spam und Phishing-Versuche.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Professionell</h3>
                    <p>Eigene Domain, Kalender, Kontakte und mehr.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div>
            <h2>Bereit zu starten?</h2>
            <p>Erstellen Sie jetzt Ihr kostenloses E-Mail-Konto</p>
            <a href="/signup.php" class="btn">Jetzt registrieren</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div>
                <h4>Service</h4>
                <ul>
                    <li><a href="/webmail.php">Webmail</a></li>
                    <li><a href="/signup.php">Registrierung</a></li>
                    <li><a href="/help.php">Hilfe</a></li>
                </ul>
            </div>
            <div>
                <h4>Unternehmen</h4>
                <ul>
                    <li><a href="/about.php">Über uns</a></li>
                    <li><a href="/contact.php">Kontakt</a></li>
                    <li><a href="/jobs.php">Karriere</a></li>
                </ul>
            </div>
            <div>
                <h4>Rechtliches</h4>
                <ul>
                    <li><a href="/privacy.php">Datenschutz</a></li>
                    <li><a href="/terms.php">AGB</a></li>
                    <li><a href="/imprint.php">Impressum</a></li>
                </ul>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="/faq.php">FAQ</a></li>
                    <li><a href="/docs.php">Dokumentation</a></li>
                    <li><a href="/contact.php">Kontakt</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {$smarty.now|date_format:"%Y"} E-Mail Service. Alle Rechte vorbehalten.</p>
        </div>
    </footer>
</body>
</html>
