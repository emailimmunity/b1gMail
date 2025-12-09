# Branding API - ModernFrontend Integration Guide

**Datum:** 2025-12-09  
**Zweck:** Schritt-für-Schritt Anleitung zur Integration von GetBrandingForDomain() in ModernFrontend CMS  

---

## 🎯 Überblick

Diese Anleitung zeigt, wie du die **Branding API** in das **ModernFrontend CMS Plugin** integrierst, um Domain-spezifisches Branding ohne Template-Überschreibungen zu ermöglichen.

**Architektur:**
```
CleverBranding (Config) → GetBrandingForDomain() → ModernFrontend (Templates) → HTML
```

---

## 📋 Voraussetzungen

- ✅ Branding API installiert (`src/serverlib/branding.inc.php`)
- ✅ CleverBranding Plugin aktiviert
- ✅ ModernFrontend CMS Plugin vorhanden

**Check:**
```bash
docker exec b1gmail php -r "require_once '/var/www/html/serverlib/branding.inc.php'; echo 'Branding API loaded';"
```

---

## 🔧 Integration in 5 Schritten

### **Schritt 1: Branding API in init.inc.php laden**

**Datei:** `src/serverlib/init.inc.php`

```php
// Nach den anderen Includes
require_once __DIR__ . '/branding.inc.php';
```

**Warum?** Damit die API global verfügbar ist.

---

### **Schritt 2: Branding in ModernFrontend Plugin laden**

**Datei:** `src/plugins/modernfrontend.plugin.php`

**In der `OnLoad()` Methode:**

```php
function OnLoad()
{
    // ... bestehender Code ...
    
    // Branding für aktuelle Domain laden
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $this->branding = GetBrandingForDomain($host);
    
    // In globalen Smarty-Kontext schreiben (falls vorhanden)
    global $tpl;
    if (isset($tpl) && is_object($tpl)) {
        $tpl->assign('branding', $this->branding);
    }
}
```

**Warum?** Plugin hält Branding-Daten und stellt sie Smarty bereit.

---

### **Schritt 3: Branding in Admin-Seiten nutzen**

**Datei:** `src/plugins/modernfrontend/admin/dashboard.php`

**Am Anfang der Datei:**

```php
<?php
// Branding für Admin-Header
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST'] ?? 'localhost');

// CSS in <head> einfügen
echo '<style>' . GenerateBrandingCSS($branding) . '</style>';
?>

<div class="modernfrontend-dashboard">
    <header style="border-bottom: 3px solid <?= htmlspecialchars($branding['primary_color']) ?>">
        <h1><?= htmlspecialchars($branding['name']) ?> - Dashboard</h1>
    </header>
    
    <!-- Rest des Dashboards -->
</div>
```

---

### **Schritt 4: Branding in Frontend-Templates**

**Datei:** `src/plugins/modernfrontend/templates/landing.tpl` (oder andere)

**Im Smarty-Template:**

```html
<!DOCTYPE html>
<html lang="{$branding.language|default:'de'}">
<head>
    <meta charset="UTF-8">
    <title>{$branding.name|default:'b1gMail'}</title>
    <link rel="icon" href="{$branding.favicon_url}">
    
    <style>
        :root {
            --brand-primary: {$branding.primary_color};
            --brand-secondary: {$branding.secondary_color};
            --brand-accent: {$branding.accent_color};
        }
        
        body {
            background: {$branding.background};
        }
        
        header {
            background: var(--brand-primary);
            color: white;
        }
        
        .btn-primary {
            background: var(--brand-primary);
        }
        
        .btn-secondary {
            background: var(--brand-secondary);
        }
    </style>
</head>
<body class="{$branding.css_class}">
    <header>
        <img src="{$branding.logo_url}" alt="{$branding.name}" class="logo">
        <h1>{$branding.login_title}</h1>
    </header>
    
    <main>
        {$page_content}
    </main>
    
    <footer>
        {$branding.footer_text}
    </footer>
</body>
</html>
```

---

### **Schritt 5: Theme Customization mit Branding**

**Datei:** `src/plugins/modernfrontend/admin/theme.php`

**Branding-Vorschau anzeigen:**

```php
<?php
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST'] ?? 'localhost');
?>

<div class="theme-customization">
    <h2>Theme-Anpassung</h2>
    
    <div class="branding-info">
        <h3>Aktuelles Branding</h3>
        <table>
            <tr>
                <td>Domain:</td>
                <td><?= htmlspecialchars($branding['domain']) ?></td>
            </tr>
            <tr>
                <td>Name:</td>
                <td><?= htmlspecialchars($branding['name']) ?></td>
            </tr>
            <tr>
                <td>Primärfarbe:</td>
                <td>
                    <span style="display:inline-block; width:20px; height:20px; background:<?= htmlspecialchars($branding['primary_color']) ?>"></span>
                    <?= htmlspecialchars($branding['primary_color']) ?>
                </td>
            </tr>
            <tr>
                <td>Sekundärfarbe:</td>
                <td>
                    <span style="display:inline-block; width:20px; height:20px; background:<?= htmlspecialchars($branding['secondary_color']) ?>"></span>
                    <?= htmlspecialchars($branding['secondary_color']) ?>
                </td>
            </tr>
            <tr>
                <td>Logo:</td>
                <td><img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="Logo" style="max-width:200px"></td>
            </tr>
        </table>
    </div>
    
    <div class="color-override">
        <h3>Farben überschreiben (optional)</h3>
        <p>Du kannst die Farben im CleverBranding-Plugin ändern.</p>
        <a href="index.php?action=plugins&plugin=tcbrn" class="btn">Zu CleverBranding</a>
    </div>
</div>
```

---

## 🎨 CSS Best Practices

### **CSS Custom Properties verwenden**

✅ **RICHTIG:**
```css
:root {
  --brand-primary: <?= $branding['primary_color'] ?>;
  --brand-secondary: <?= $branding['secondary_color'] ?>;
}

.button {
  background: var(--brand-primary);
}

.button:hover {
  background: color-mix(in srgb, var(--brand-primary) 80%, black);
}
```

❌ **FALSCH:**
```css
.button {
  background: <?= $branding['primary_color'] ?>;  /* Nicht wiederverwendbar */
}
```

### **Domain-spezifisches CSS**

```css
/* Default für alle */
body {
  background: #fff;
}

/* Nur für bestimmte Domain */
body.branding-example-com {
  background: #f5f5f5;
}

body.branding-mail-example-com {
  background: #e8f4f8;
}
```

---

## 🧪 Testing

### **Test 1: Branding wird geladen**

```bash
docker exec b1gmail php -r "
require_once '/var/www/html/serverlib/init.inc.php';
\$b = GetBrandingForDomain('localhost');
echo 'Name: ' . \$b['name'] . \"\n\";
echo 'Farbe: ' . \$b['primary_color'] . \"\n\";
"
```

**Erwartete Ausgabe:**
```
Name: b1gMail
Farbe: #004080
```

---

### **Test 2: Smarty-Template**

**Erstelle:** `src/test-branding-template.php`

```php
<?php
require_once __DIR__ . '/serverlib/init.inc.php';

$branding = GetBrandingForDomain('localhost');

// Smarty laden
require_once __DIR__ . '/serverlib/smarty.class.php';
$tpl = new SmartyBC();
$tpl->setTemplateDir(__DIR__ . '/templates/');
$tpl->setCompileDir(__DIR__ . '/temp/');

// Branding zuweisen
$tpl->assign('branding', $branding);

// Mini-Template
$template = '
<html>
<head><title>{$branding.name}</title></head>
<body class="{$branding.css_class}">
    <h1>{$branding.name}</h1>
    <p>Farbe: {$branding.primary_color}</p>
</body>
</html>
';

// Ausgabe
$tpl->display('string:' . $template);
```

**Test ausführen:**
```bash
docker exec b1gmail php /var/www/html/test-branding-template.php
```

---

### **Test 3: Multi-Domain**

```bash
# Domain 1: localhost
docker exec b1gmail php -r "
require_once '/var/www/html/serverlib/init.inc.php';
echo GetBrandingForDomain('localhost')['name'] . \"\n\";
"

# Domain 2: mail.example.com
docker exec b1gmail php -r "
require_once '/var/www/html/serverlib/init.inc.php';
echo GetBrandingForDomain('mail.example.com')['name'] . \"\n\";
"
```

---

## 🔄 Migration bestehender Templates

### **Schritt 1: Hardcoded Werte identifizieren**

**Vorher:**
```html
<header style="background: #004080">
    <img src="/images/logo.png" alt="b1gMail">
</header>
<footer>© 2025 b1gMail</footer>
```

### **Schritt 2: Durch Branding-Variablen ersetzen**

**Nachher:**
```html
<header style="background: {$branding.primary_color}">
    <img src="{$branding.logo_url}" alt="{$branding.name}">
</header>
<footer>{$branding.footer_text}</footer>
```

### **Schritt 3: Testen**

```bash
# Template neu rendern
docker exec b1gmail php /var/www/html/test-template-migration.php
```

---

## 🚀 Deployment-Checkliste

- [ ] Branding API in `init.inc.php` inkludiert
- [ ] ModernFrontend Plugin lädt Branding in `OnLoad()`
- [ ] Admin-Seiten nutzen `GenerateBrandingCSS()`
- [ ] Frontend-Templates haben `{$branding.*}` Variablen
- [ ] CSS verwendet Custom Properties
- [ ] Alle hardcoded Farben/Logos entfernt
- [ ] Multi-Domain getestet
- [ ] Fallback auf Default-Branding funktioniert

---

## 📊 Beispiel: Landing Page mit Branding

**Vollständiges Beispiel:**

```php
<?php
// src/landing.php
require_once __DIR__ . '/serverlib/init.inc.php';

$branding = GetBrandingForDomain($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="<?= $branding['language'] ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($branding['name']) ?></title>
    <link rel="icon" href="<?= htmlspecialchars($branding['favicon_url']) ?>">
    <style>
        <?= GenerateBrandingCSS($branding) ?>
        
        .hero {
            background: linear-gradient(135deg, 
                var(--brand-primary) 0%, 
                var(--brand-secondary) 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        
        .cta-button {
            background: var(--brand-accent);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body class="<?= htmlspecialchars($branding['css_class']) ?>">
    <div class="hero">
        <img src="<?= htmlspecialchars($branding['logo_url']) ?>" 
             alt="<?= htmlspecialchars($branding['name']) ?>" 
             style="max-width: 300px; margin-bottom: 30px;">
        
        <h1><?= htmlspecialchars($branding['login_title']) ?></h1>
        
        <p>Professioneller E-Mail-Service für Ihr Unternehmen</p>
        
        <button class="cta-button">Jetzt starten</button>
    </div>
    
    <footer style="text-align: center; padding: 20px;">
        <?= htmlspecialchars($branding['footer_text']) ?>
    </footer>
</body>
</html>
```

---

## 🐛 Troubleshooting

### **Problem: Branding wird nicht angezeigt**

**Check 1:** Branding API geladen?
```bash
docker exec b1gmail php -r "var_dump(function_exists('GetBrandingForDomain'));"
```

**Check 2:** Smarty-Variable gesetzt?
```php
<?php var_dump($tpl->getTemplateVars('branding')); ?>
```

**Check 3:** Template-Syntax korrekt?
```smarty
{* Debug: *}
{$branding|@print_r}
```

---

### **Problem: Falsche Farben**

**Debug:**
```php
<?php
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST']);
echo "<pre>";
print_r($branding);
echo "</pre>";
?>
```

**Prüfen:**
- Ist `primary_color` im korrekten Format? (`#RRGGBB`)
- Ist das richtige Profil geladen? (check `profile_id`)

---

## 📚 Weiterführend

- **API-Referenz:** `docs/BRANDING_API.md`
- **Kollisions-Analyse:** `docs/CLEVERBRANDING_CMS_KOLLISION_ANALYSE.md`
- **Session Summary:** `docs/SESSION_SUMMARY_2025-12-09.md`

---

**Erstellt am:** 2025-12-09  
**Status:** Ready for Implementation  
**Nächster Schritt:** Integration in ModernFrontend testen
