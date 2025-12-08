<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageTitle|default:'aikQ Mail - Professional Email Hosting'}</title>
    <meta name="description" content="{$content.hero.meta_description|default:'Professionelles E-Mail-Hosting mit aikQ - Sicher, schnell und zuverlässig'}">
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'aikq': {
                            DEFAULT: '{$theme.primary_color|default:"#76B82A"}',
                            'dark': '{$theme.primary_dark|default:"#5D9321"}',
                            'light': '{$theme.primary_light|default:"#8FC744"}',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
        }
        
        .gradient-hero {
            background: linear-gradient(135deg, {$theme.primary_color|default:"#76B82A"} 0%, {$theme.primary_dark|default:"#5D9321"} 100%);
        }
        
        .btn-primary {
            background: {$theme.primary_color|default:"#76B82A"};
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: {$theme.primary_dark|default:"#5D9321"};
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .feature-card {
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .package-card {
            transition: all 0.3s;
        }
        .package-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .package-card.featured {
            border: 2px solid {$theme.primary_color|default:"#76B82A"};
            position: relative;
        }
        .package-card.featured::before {
            content: "EMPFOHLEN";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: {$theme.primary_color|default:"#76B82A"};
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ open: false }">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    {if $theme.logo_url}
                        <img src="{$theme.logo_url}" alt="Logo" class="h-10">
                    {else}
                        <span class="text-2xl font-bold text-aikq">{$theme.site_title|default:'aikQ Mail'}</span>
                    {/if}
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-aikq transition">Features</a>
                    <a href="#packages" class="text-gray-700 hover:text-aikq transition">Pakete</a>
                    <a href="#about" class="text-gray-700 hover:text-aikq transition">Über uns</a>
                    <a href="#contact" class="text-gray-700 hover:text-aikq transition">Kontakt</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="hidden md:flex space-x-4">
                    <a href="/index.php?action=login" class="text-gray-700 hover:text-aikq transition">Anmelden</a>
                    <a href="/index.php?action=register" class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                        Kostenlos registrieren
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="open = !open" class="md:hidden text-gray-700">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div x-show="open" class="md:hidden mt-4 pb-4">
                <a href="#features" class="block py-2 text-gray-700 hover:text-aikq">Features</a>
                <a href="#packages" class="block py-2 text-gray-700 hover:text-aikq">Pakete</a>
                <a href="#about" class="block py-2 text-gray-700 hover:text-aikq">Über uns</a>
                <a href="#contact" class="block py-2 text-gray-700 hover:text-aikq">Kontakt</a>
                <a href="/index.php?action=login" class="block py-2 text-gray-700 hover:text-aikq">Anmelden</a>
                <a href="/index.php?action=register" class="btn-primary text-white px-6 py-2 rounded-lg font-medium block text-center mt-2">
                    Kostenlos registrieren
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-hero text-white py-20 md:py-32">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    {$content.hero.title|default:'Professionelles E-Mail-Hosting'}
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-white/90">
                    {$content.hero.subtitle|default:'Sicher, schnell und zuverlässig - Made in Germany'}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{$content.hero.cta_url|default:'/index.php?action=register'}" 
                       class="bg-white text-aikq px-8 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition inline-block">
                        {$content.hero.cta_text|default:'Kostenlos registrieren'}
                    </a>
                    <a href="#packages" 
                       class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-white/10 transition inline-block">
                        Pakete ansehen
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">
                    {$content.features.title|default:'Ihre Vorteile'}
                </h2>
                <p class="text-xl text-gray-600">
                    {$content.features.subtitle|default:'Professionelles E-Mail-Hosting mit allem, was Sie brauchen'}
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="shield-check" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">100% Sicher</h3>
                    <p class="text-gray-600">
                        Höchste Sicherheitsstandards mit SSL-Verschlüsselung und Spam-Schutz
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="zap" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Blitzschnell</h3>
                    <p class="text-gray-600">
                        Modernste Server-Infrastruktur für schnellen E-Mail-Empfang und -Versand
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="users" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Team-Funktionen</h3>
                    <p class="text-gray-600">
                        Perfekt für Teams mit gemeinsamen Postfächern und Kalendern
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="smartphone" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Mobil & Desktop</h3>
                    <p class="text-gray-600">
                        Zugriff von überall - Webmail, IMAP, POP3 und Mobile Apps
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="500">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="server" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Made in Germany</h3>
                    <p class="text-gray-600">
                        Server in Deutschland - DSGVO-konform und höchster Datenschutz
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card bg-white p-8 rounded-xl shadow-lg" data-aos="fade-up" data-aos-delay="600">
                    <div class="w-16 h-16 bg-aikq/10 rounded-full flex items-center justify-center mb-6">
                        <i data-lucide="headphones" class="w-8 h-8 text-aikq"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Premium Support</h3>
                    <p class="text-gray-600">
                        Schneller und kompetenter Support per E-Mail und Telefon
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4">
                    Unsere Pakete
                </h2>
                <p class="text-xl text-gray-600">
                    Wählen Sie das perfekte Paket für Ihre Bedürfnisse
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                {if $packages && count($packages) > 0}
                    {foreach from=$packages item=pkg name=pkgloop}
                        {if $smarty.foreach.pkgloop.index < 6}
                        <div class="package-card bg-white rounded-xl shadow-lg p-8 {if $pkg.accentuation == 1}featured{/if}" 
                             data-aos="fade-up" data-aos-delay="{$smarty.foreach.pkgloop.index * 100}">
                            
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{$pkg.title}</h3>
                            <p class="text-gray-600 mb-6">{$pkg.description}</p>
                            
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-aikq">{$pkg.price}</span>
                                <span class="text-gray-600">/{$pkg.priceInterval}</span>
                            </div>
                            
                            <ul class="space-y-3 mb-8">
                                {if $pkg.fields.speicher}
                                <li class="flex items-start">
                                    <i data-lucide="check" class="w-5 h-5 text-aikq mr-2 mt-1 flex-shrink-0"></i>
                                    <span>{$pkg.fields.speicher} MB Speicher</span>
                                </li>
                                {/if}
                                {if $pkg.fields.postfaecher}
                                <li class="flex items-start">
                                    <i data-lucide="check" class="w-5 h-5 text-aikq mr-2 mt-1 flex-shrink-0"></i>
                                    <span>{$pkg.fields.postfaecher} Postfächer</span>
                                </li>
                                {/if}
                                {if $pkg.fields.send_limit}
                                <li class="flex items-start">
                                    <i data-lucide="check" class="w-5 h-5 text-aikq mr-2 mt-1 flex-shrink-0"></i>
                                    <span>{$pkg.fields.send_limit}</span>
                                </li>
                                {/if}
                            </ul>
                            
                            <a href="/index.php?action=paccOrder&id={$pkg.id}" 
                               class="btn-primary text-white px-6 py-3 rounded-lg font-bold w-full block text-center">
                                Jetzt bestellen
                            </a>
                        </div>
                        {/if}
                    {/foreach}
                {else}
                    <div class="col-span-full text-center text-gray-600 py-12">
                        <p class="text-xl">Keine Pakete verfügbar</p>
                    </div>
                {/if}
            </div>
            
            <div class="text-center mt-12">
                <a href="/index.php?action=paccPackages" 
                   class="inline-block border-2 border-aikq text-aikq px-8 py-3 rounded-lg font-bold hover:bg-aikq hover:text-white transition">
                    Alle Pakete anzeigen →
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <h4 class="text-xl font-bold mb-4">{$theme.site_title|default:'aikQ Mail'}</h4>
                    <p class="text-gray-400">
                        {$content.footer.company_description|default:'Professionelles E-Mail-Hosting - Made in Germany'}
                    </p>
                </div>
                
                <!-- Links -->
                <div>
                    <h4 class="text-xl font-bold mb-4">Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="#packages" class="text-gray-400 hover:text-white transition">Pakete</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Support</a></li>
                    </ul>
                </div>
                
                <!-- Legal -->
                <div>
                    <h4 class="text-xl font-bold mb-4">Rechtliches</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Impressum</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Datenschutz</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">AGB</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="text-xl font-bold mb-4">Kontakt</h4>
                    <p class="text-gray-400 mb-2">
                        {$content.footer.contact_email|default:'info@aikq.de'}
                    </p>
                    <p class="text-gray-400">
                        {$content.footer.contact_phone|default:'+49 (0) 123 456789'}
                    </p>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {$smarty.now|date_format:"%Y"} {$theme.site_title|default:'aikQ Mail'}. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </footer>

    <!-- Initialize Icons & Animations -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
        
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
