<!DOCTYPE html>
<html lang="{$mf_language.code|default:'de'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$page_title|default:$branding.name|default:'aikQ - Dein sicheres E-Mail-Postfach'}</title>
    <meta name="description" content="Sicheres, verschlüsseltes E-Mail-Postfach mit modernster Technologie. Made in Germany.">
    
    {* Branding API: Favicon *}
    {if $branding.favicon_url}
    <link rel="icon" href="{$branding.favicon_url}">
    {/if}
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- ModernFrontend Design CSS -->
    {if $mf_css_url}
        <link rel="stylesheet" href="{$mf_css_url}">
    {/if}
    
    <style>
        :root {
            {* Branding API: Use branding colors with ModernFrontend fallback *}
            --aikq-green: {$branding.primary_color|default:$mf_primary_color|default:'#76B82A'};
            --aikq-green-dark: {$branding.secondary_color|default:$mf_secondary_color|default:'#5D9321'};
            --aikq-green-light: {$branding.accent_color|default:'#8FD645'};
            --aikq-gray-dark: #2D3748;
            --aikq-gray: #4A5568;
            --aikq-gray-light: #E2E8F0;
            --aikq-white: #FFFFFF;
            --aikq-bg: {$branding.background|default:'#F7FAFC'};
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--aikq-bg);
            color: var(--aikq-gray-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header / Navigation */
        .header {
            background: var(--aikq-white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header.scrolled {
            box-shadow: var(--shadow-md);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            color: var(--aikq-green);
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--aikq-green), var(--aikq-green-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }
        
        .nav-menu {
            display: flex;
            gap: 30px;
            list-style: none;
            align-items: center;
        }
        
        .nav-link {
            color: var(--aikq-gray);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--aikq-green);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--aikq-green);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--aikq-green), var(--aikq-green-dark));
            color: white;
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--aikq-green);
            border: 2px solid var(--aikq-green);
        }
        
        .btn-secondary:hover {
            background: var(--aikq-green);
            color: white;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(118, 184, 42, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .hero-content h1 {
            font-size: 52px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--aikq-gray-dark);
            margin-bottom: 20px;
        }
        
        .hero-content h1 .highlight {
            color: var(--aikq-green);
            background: linear-gradient(135deg, var(--aikq-green), var(--aikq-green-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-content p {
            font-size: 20px;
            color: var(--aikq-gray);
            margin-bottom: 35px;
            line-height: 1.8;
        }
        
        .hero-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .hero-image {
            position: relative;
        }
        
        .hero-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow-xl);
            position: relative;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .hero-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .hero-card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--aikq-green), var(--aikq-green-dark));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }
        
        .hero-card h3 {
            font-size: 24px;
            color: var(--aikq-gray-dark);
        }
        
        .hero-card-features {
            list-style: none;
        }
        
        .hero-card-features li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--aikq-gray);
            border-bottom: 1px solid var(--aikq-gray-light);
        }
        
        .hero-card-features li:last-child {
            border-bottom: none;
        }
        
        .checkmark {
            width: 24px;
            height: 24px;
            background: var(--aikq-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        /* Features Section */
        .features {
            padding: 100px 20px;
            background: white;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header h2 {
            font-size: 42px;
            font-weight: 700;
            color: var(--aikq-gray-dark);
            margin-bottom: 15px;
        }
        
        .section-header p {
            font-size: 18px;
            color: var(--aikq-gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background: var(--aikq-bg);
            border-radius: 15px;
            padding: 35px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--aikq-green);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--aikq-green-light), var(--aikq-green));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 22px;
            color: var(--aikq-gray-dark);
            margin-bottom: 12px;
        }
        
        .feature-card p {
            color: var(--aikq-gray);
            line-height: 1.7;
        }
        
        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, var(--aikq-green), var(--aikq-green-dark));
            padding: 80px 20px;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }
        
        .stat-item h3 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-item p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        /* CTA Section */
        .cta {
            padding: 100px 20px;
            background: var(--aikq-bg);
            text-align: center;
        }
        
        .cta h2 {
            font-size: 42px;
            font-weight: 700;
            color: var(--aikq-gray-dark);
            margin-bottom: 20px;
        }
        
        .cta p {
            font-size: 20px;
            color: var(--aikq-gray);
            margin-bottom: 35px;
        }
        
        /* Footer */
        .footer {
            background: var(--aikq-gray-dark);
            color: white;
            padding: 60px 20px 30px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--aikq-green-light);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--aikq-green-light);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Language Switcher Styles */
        .language-switcher {
            position: relative;
        }
        
        .language-switcher select {
            padding: 8px 35px 8px 12px;
            border: 2px solid var(--aikq-gray-light);
            border-radius: 8px;
            background: white;
            color: var(--aikq-gray);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234A5568' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        
        .language-switcher select:hover {
            border-color: var(--aikq-green);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--aikq-gray);
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .hero-content h1 {
                font-size: 38px;
            }
            
            .nav-menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .section-header h2 {
                font-size: 32px;
            }
        }
        
        @media (max-width: 640px) {
            .hero-content h1 {
                font-size: 32px;
            }
            
            .hero-content p {
                font-size: 16px;
            }
            
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav-container">
            <a href="/" class="logo">
                <div class="logo-icon">aQ</div>
                <span>aikQ</span>
            </a>
            
            <ul class="nav-menu">
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#security" class="nav-link">Sicherheit</a></li>
                <li><a href="#pricing" class="nav-link">Preise</a></li>
                <li><a href="#about" class="nav-link">Über uns</a></li>
            </ul>
            
            <div class="nav-actions">
                <!-- Language Switcher -->
                {if $mf_language_switcher}
                    <div class="language-switcher">
                        {$mf_language_switcher}
                    </div>
                {/if}
                
                <a href="/login" class="btn btn-secondary">Anmelden</a>
                <a href="/register" class="btn btn-primary">Kostenlos starten</a>
            </div>
            
            <button class="mobile-menu-toggle">☰</button>
        </nav>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>
                    Dein <span class="highlight">sicheres</span><br>
                    E-Mail-Postfach
                </h1>
                <p>
                    Verschlüsselte Kommunikation mit modernster Technologie. 
                    Entwickelt in Deutschland, gehostet in Deutschland.
                </p>
                <div class="hero-actions">
                    <a href="/register" class="btn btn-primary">
                        <span>🚀</span>
                        Jetzt kostenlos starten
                    </a>
                    <a href="#features" class="btn btn-secondary">
                        Mehr erfahren
                    </a>
                </div>
            </div>
            
            <div class="hero-image">
                <div class="hero-card">
                    <div class="hero-card-header">
                        <div class="hero-card-icon">📧</div>
                        <div>
                            <h3>Postfach-Features</h3>
                        </div>
                    </div>
                    <ul class="hero-card-features">
                        <li>
                            <span class="checkmark">✓</span>
                            <span>Ende-zu-Ende Verschlüsselung</span>
                        </li>
                        <li>
                            <span class="checkmark">✓</span>
                            <span>100 GB Speicherplatz</span>
                        </li>
                        <li>
                            <span class="checkmark">✓</span>
                            <span>Spam- & Virenschutz</span>
                        </li>
                        <li>
                            <span class="checkmark">✓</span>
                            <span>Mobile Apps (iOS & Android)</span>
                        </li>
                        <li>
                            <span class="checkmark">✓</span>
                            <span>DSGVO-konform</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Warum aikQ?</h2>
                <p>Sicherheit und Benutzerfreundlichkeit perfekt vereint</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Maximale Sicherheit</h3>
                    <p>
                        Ende-zu-Ende-Verschlüsselung, 2-Faktor-Authentifizierung 
                        und sichere Server in Deutschland.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Blitzschnell</h3>
                    <p>
                        Moderne Technologie sorgt für schnelle Ladezeiten 
                        und flüssige Bedienung.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Überall verfügbar</h3>
                    <p>
                        Nutze dein Postfach auf allen Geräten - Web, iOS, 
                        Android und Desktop.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Datenschutz</h3>
                    <p>
                        DSGVO-konform, Made in Germany. Deine Daten bleiben 
                        in Deutschland.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💾</div>
                    <h3>Viel Speicher</h3>
                    <p>
                        Bis zu 100 GB Speicherplatz für E-Mails, Anhänge 
                        und Cloud-Dateien.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Intelligenter Filter</h3>
                    <p>
                        KI-basierter Spam- und Virenschutz hält dein Postfach 
                        sauber.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>500K+</h3>
                    <p>Zufriedene Nutzer</p>
                </div>
                <div class="stat-item">
                    <h3>99.9%</h3>
                    <p>Verfügbarkeit</p>
                </div>
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Made in Germany</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Support</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Bereit für sicheres E-Mail?</h2>
            <p>Starte jetzt kostenlos und erlebe die Zukunft der E-Mail-Kommunikation.</p>
            <a href="/register" class="btn btn-primary">
                <span>🚀</span>
                Jetzt kostenfrei registrieren
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>aikQ Mail</h4>
                    <ul class="footer-links">
                        <li><a href="/features">Features</a></li>
                        <li><a href="/pricing">Preise</a></li>
                        <li><a href="/business">Business</a></li>
                        <li><a href="/security">Sicherheit</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Unternehmen</h4>
                    <ul class="footer-links">
                        <li><a href="/about">Über uns</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/press">Presse</a></li>
                        <li><a href="/careers">Karriere</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="/help">Hilfe-Center</a></li>
                        <li><a href="/contact">Kontakt</a></li>
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="/status">System-Status</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Rechtliches</h4>
                    <ul class="footer-links">
                        <li><a href="/privacy">Datenschutz</a></li>
                        <li><a href="/terms">AGB</a></li>
                        <li><a href="/imprint">Impressum</a></li>
                        <li><a href="/cookies">Cookie-Richtlinie</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                {* Branding API: Use branding footer text with fallback *}
                <p>{$branding.footer_text|default:'&copy; 2025 aikQ. Alle Rechte vorbehalten. Made with 💚 in Germany.'}</p>
                {if $mf_language}
                    <p style="margin-top: 10px; font-size: 14px;">
                        Aktuelle Sprache: {$mf_language.name} ({$mf_language.code})
                        {if $mf_design} | Design: {$mf_design.name}{/if}
                        {if $branding.is_default === false} | Branding: {$branding.name}{/if}
                    </p>
                {/if}
            </div>
        </div>
    </footer>
    
    <!-- ModernFrontend Design JS -->
    {if $mf_js_url}
        <script src="{$mf_js_url}"></script>
    {/if}
    
    <!-- Scroll Effect -->
    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
