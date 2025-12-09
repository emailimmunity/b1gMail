# CleverBranding vs. ModernFrontend CMS - Kollisions-Analyse

**Datum:** 2025-12-09 12:10  
**Erstellt von:** Windsurf AI  
**Kontext:** User-Anfrage zur Vermeidung von Konflikten zwischen Branding-Plugin und CMS

---

## 🎯 Zielsetzung

**Frage:** Kollidiert CleverBranding mit dem ModernFrontend CMS?

**Kurze Antwort:** **NEIN** - Bei korrekter Architektur sind beide **komplementär**, nicht konkurrierend.

**Langfassung:** CleverBranding arbeitet als **Konfigurations-Layer** für Domain-spezifisches Branding (Logos, Farben, Texte), während ModernFrontend CMS die **Content-Struktur** und Templates liefert. Konfliktpotenzial besteht nur, wenn beide unkontrolliert in Templates oder CSS eingreifen.

---

## 📊 Architektur-Vergleich

| Aspekt | CleverBranding | ModernFrontend CMS | Konflikt? |
|--------|----------------|---------------------|-----------|
| **Hauptzweck** | White-Label Customization | Content Management | ❌ Nein |
| **Datenebene** | Domain → Logo/Farben/Texte | Pages → Inhalte/Struktur | ❌ Nein |
| **Templates** | **Keine eigenen Templates** | 12 Templates (Landing, Builder, etc.) | ✅ **Kein Konflikt** |
| **Hooks** | OnLoad, OnReadLang, OnGetDomainList | OnHTMLHeader, OnBeforePageRender, etc. | ❌ Nein |
| **CSS/JS** | **Keine direkten CSS-Injections** | Theme Customization (CSS) | ✅ **Kein Konflikt** |
| **Admin-Panel** | Separate Admin-Seite (tcbrn) | Separate Admin-Seiten (11x modernfrontend/) | ❌ Nein |
| **Datenspeicherung** | `bm60_tcbrn_plugin_domains` | `bm60_modernfrontend_*` (10 Tabellen) | ❌ Nein |

---

## 🔍 CleverBranding - Code-Analyse

### **Plugin-Struktur**

```php
class TCBrandPlugin extends BMPlugin {
    function TCBrandPlugin() {
        $this->name = 'CleverBranding';
        $this->version = '1.3.1';
        $this->order = -10;  // Wird früh geladen
        $this->admin_pages = true;
    }
}
```

### **Verwendete Hooks**

**1. OnReadLang** (Übersetzungen)
```php
function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
    // Registriert nur eigene Admin-UI-Texte
    $lang_admin['tcbrn.domain'] = 'Domain';
    $lang_admin['tcbrn.logo'] = 'Logo';
    // ... keine Manipulation von Frontend-Texten!
}
```
✅ **Kein Konflikt** - Betrifft nur eigene Admin-Oberfläche

---

**2. OnLoad** (Domain-spezifische Settings laden)
```php
function OnLoad() {
    if(ADMIN_MODE || empty($_SERVER['SERVER_NAME'])) {
        return;  // Im Admin-Modus inaktiv!
    }
    
    // Lädt Domain-Settings in $this->domain_logo, $this->domain_colors, etc.
    // WICHTIG: Speichert nur in Plugin-Properties, manipuliert KEIN Layout!
}
```
✅ **Kein Konflikt** - Lädt nur Daten, rendert nichts

---

**3. OnGetDomainList** (Domain-Filterung)
```php
function OnGetDomainList(&$list) {
    global $bm_prefs;
    $list = array_intersect($bm_prefs['domains'], $list);
}
```
✅ **Kein Konflikt** - Filtert nur verfügbare Domains

---

### **Datenbank-Schema**

```sql
CREATE TABLE `bm60_tcbrn_plugin_domains` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `domain` VARCHAR(255),
  `logo` VARCHAR(255),           -- Logo-Pfad
  `color_primary` VARCHAR(7),     -- z.B. #76B82A
  `color_secondary` VARCHAR(7),
  `company_name` VARCHAR(255),
  `language` VARCHAR(10),
  `country` VARCHAR(3),
  `xmailer` VARCHAR(255)          -- Custom X-Mailer Header
);
```

**Eigenschaften:**
- ✅ **Isoliert** - Keine Foreign Keys zu CMS-Tabellen
- ✅ **Read-Only im Frontend** - Keine Runtime-Updates
- ✅ **Domain-basiert** - Multi-Mandanten-fähig

---

## 🏗️ ModernFrontend CMS - Architektur

### **Datenbank-Tabellen (10)**

```
bm60_modernfrontend_pages           -- Seiten (Content)
bm60_modernfrontend_media           -- Medien-Bibliothek
bm60_modernfrontend_themes          -- Theme-Settings
bm60_modernfrontend_analytics       -- Analytics-Daten
bm60_modernfrontend_abtests         -- A/B-Tests
bm60_modernfrontend_emails          -- Email-Templates
bm60_modernfrontend_forms           -- Formulare
bm60_modernfrontend_pagebuilder     -- Page-Builder-Daten
bm60_modernfrontend_packages        -- Produktpakete
bm60_modernfrontend_settings        -- CMS-Settings
```

### **Templates (12)**

```
plugins/modernfrontend/templates/
├── landing.tpl          -- Landing-Page (Modern UI)
├── content.tpl          -- Content-Editor
├── theme.tpl            -- Theme-Customization
├── media.tpl            -- Medien-Upload
├── analytics.tpl        -- Dashboard mit Charts
├── settings.tpl         -- CMS-Einstellungen
├── abtesting.tpl        -- A/B-Test-Config
├── emails.tpl           -- Email-Template-Editor
├── forms.tpl            -- Formular-Builder
├── pagebuilder.tpl      -- Visual Page-Builder
├── packages.tpl         -- Paket-Konfiguration
└── dashboard.tpl        -- Analytics-Dashboard
```

---

## ⚠️ Potenzielle Konfliktpunkte (Theorie)

### **1. CSS-Überschreibungen**

**Problem:** Wenn beide Plugins globales CSS einfügen
```css
/* CleverBranding (FALSCH) */
body { background: #76B82A; }
h1 { color: var(--brand-color); }

/* ModernFrontend (FALSCH) */
body { background: #ffffff; }
h1 { color: #333; }
```

**Lösung:** CSS-Namespaces verwenden
```css
/* CleverBranding (RICHTIG) */
.tcbrn-branded body { background: #76B82A; }
.tcbrn-branded h1 { color: var(--brand-color); }

/* ModernFrontend (RICHTIG) */
.mf-content body { background: #ffffff; }
.mf-content h1 { color: #333; }
```

✅ **Status in CleverBranding:** Plugin injiziert **KEIN CSS** - liefert nur Konfigurations-Werte!

---

### **2. Template-Überschreibungen**

**Problem:** Beide Plugins ersetzen dieselben Templates
```php
// FALSCH: CleverBranding ersetzt header.tpl
function OnBeforeHeader(&$template) {
    $template = 'tcbrn_header.tpl';  // ❌ Überschreibt alles!
}
```

**Lösung:** Templates ergänzen, nicht ersetzen
```php
// RICHTIG: CleverBranding liefert Variablen an bestehendes Template
function OnTemplateVariables(&$vars) {
    $vars['brand_logo'] = $this->domain_logo;
    $vars['brand_color'] = $this->domain_colors['primary'];
}
```

✅ **Status in CleverBranding:** Plugin hat **KEINE OnBeforeHeader/OnHTMLHeader Hooks** - überschreibt keine Templates!

---

### **3. JavaScript-Konflikte**

**Problem:** Beide laden unterschiedliche Versionen von jQuery/Libraries
```html
<!-- CleverBranding -->
<script src="jquery-2.1.4.min.js"></script>

<!-- ModernFrontend -->
<script src="jquery-3.6.0.min.js"></script>
```

**Lösung:** Zentrale Library-Verwaltung
```php
// Prüfen ob bereits geladen
if (!defined('JQUERY_LOADED')) {
    echo '<script src="jquery-3.6.0.min.js"></script>';
    define('JQUERY_LOADED', true);
}
```

✅ **Status in CleverBranding:** Plugin lädt **KEIN JavaScript** - nur Daten-Layer!

---

## ✅ Empfohlene Integrationsstrategie

### **Architektur-Prinzip: Separation of Concerns**

```
┌─────────────────────────────────────────────────────┐
│         USER REQUEST (z.B. https://domain1.com)     │
└────────────────┬────────────────────────────────────┘
                 │
    ┌────────────▼────────────┐
    │   CleverBranding        │  Lädt Domain-Settings
    │   (Configuration Layer) │  - Logo: logo1.png
    └────────────┬────────────┘  - Color: #76B82A
                 │                - Company: ACME Corp
                 │
    ┌────────────▼────────────┐
    │   ModernFrontend CMS    │  Rendert Content mit Branding
    │   (Presentation Layer)  │  - Template: landing.tpl
    └────────────┬────────────┘  - Variablen: $brand_*
                 │
    ┌────────────▼────────────┐
    │   HTML Output           │
    │   <body style="--brand-color: #76B82A;">
    │     <img src="logo1.png">
    │     <h1>Welcome to ACME Corp</h1>
    └─────────────────────────┘
```

### **Code-Beispiel: Korrekte Integration**

**1. CleverBranding lädt Domain-Daten (OnLoad)**
```php
class TCBrandPlugin extends BMPlugin {
    function OnLoad() {
        // Domain: domain1.com
        $this->domain_logo = '/uploads/branding/domain1_logo.png';
        $this->domain_colors = ['primary' => '#76B82A', 'secondary' => '#333'];
        $this->domain_company = 'ACME Corporation';
    }
}
```

**2. ModernFrontend rendert mit Branding-Daten**
```php
// In ModernFrontend Template (landing.tpl)
<?php
// Hole Branding-Daten von CleverBranding
global $plugins;
$branding = null;
foreach ($plugins as $plugin) {
    if ($plugin->name == 'CleverBranding') {
        $branding = $plugin;
        break;
    }
}

// Setze CSS-Variablen
if ($branding) {
    echo "<style>";
    echo ":root {";
    echo "  --brand-color-primary: {$branding->domain_colors['primary']};";
    echo "  --brand-color-secondary: {$branding->domain_colors['secondary']};";
    echo "}";
    echo "</style>";
}
?>

<!-- Logo -->
<?php if ($branding && $branding->domain_logo): ?>
<img src="<?= htmlspecialchars($branding->domain_logo) ?>" alt="Logo">
<?php endif; ?>

<!-- CMS-Content (unabhängig von Branding) -->
<div class="mf-content">
    <?= $page_content ?>
</div>
```

---

## 🛠️ Konkrete To-Dos für Konflikt-Vermeidung

### **Phase 1: Code-Review (JETZT)**

- [x] **CleverBranding analysiert** → Keine Template/CSS-Überschreibungen gefunden ✅
- [ ] **ModernFrontend Templates prüfen** → Suche nach `$plugins` oder globalen Variablen
- [ ] **Theme-Customization in ModernFrontend** → Prüfen, ob Farben hart codiert oder konfigurierbar

**Command:**
```bash
# Suche nach CleverBranding-Referenzen in ModernFrontend
grep -r "tcbrn\|CleverBrand\|domain_logo" src/plugins/modernfrontend/
```

---

### **Phase 2: Zentrale Branding-API erstellen**

**Datei:** `src/serverlib/branding.inc.php`

```php
<?php
/**
 * Zentrale Branding-API
 * Stellt Branding-Daten für alle Plugins bereit
 */

function GetBrandingForDomain($domain = null) {
    global $plugins;
    
    if ($domain === null) {
        $domain = $_SERVER['SERVER_NAME'] ?? 'default';
    }
    
    // Hole CleverBranding Plugin
    $brandPlugin = null;
    foreach ($plugins as $plugin) {
        if ($plugin->name == 'CleverBranding') {
            $brandPlugin = $plugin;
            break;
        }
    }
    
    // Fallback: Default-Branding
    $defaults = [
        'logo' => '/images/b1gmail_logo.png',
        'colors' => ['primary' => '#0066cc', 'secondary' => '#333333'],
        'company' => 'b1gMail',
        'xmailer' => 'b1gMail'
    ];
    
    if (!$brandPlugin) {
        return $defaults;
    }
    
    return [
        'logo' => $brandPlugin->domain_logo ?? $defaults['logo'],
        'colors' => $brandPlugin->domain_colors ?? $defaults['colors'],
        'company' => $brandPlugin->domain_company ?? $defaults['company'],
        'xmailer' => $brandPlugin->domain_xmailer ?? $defaults['xmailer']
    ];
}
```

**Nutzung in Templates:**
```php
<?php
$branding = GetBrandingForDomain();
?>
<style>
:root {
    --brand-primary: <?= $branding['colors']['primary'] ?>;
    --brand-secondary: <?= $branding['colors']['secondary'] ?>;
}
</style>
<img src="<?= htmlspecialchars($branding['logo']) ?>" alt="<?= htmlspecialchars($branding['company']) ?>">
```

---

### **Phase 3: ModernFrontend Theme-Integration**

**Datei:** `src/plugins/modernfrontend/admin/theme.php`

**Ergänzung:**
```php
// Theme-Editor mit Branding-Integration
$branding = GetBrandingForDomain();

echo "<h3>Branding-Einstellungen (von CleverBranding)</h3>";
echo "<p>Logo: " . htmlspecialchars($branding['logo']) . "</p>";
echo "<p>Primärfarbe: <span style='color: {$branding['colors']['primary']}'>{$branding['colors']['primary']}</span></p>";

echo "<h3>Theme-Anpassungen (überschreiben Branding)</h3>";
// Theme-spezifische Farben, die Branding überschreiben können
```

---

### **Phase 4: Test-Matrix**

| Test-Szenario | Erwartetes Ergebnis | Status |
|---------------|---------------------|--------|
| **Domain 1 + CleverBranding aktiv** | Logo1, Farbe1 | ⏳ Pending |
| **Domain 2 + CleverBranding aktiv** | Logo2, Farbe2 | ⏳ Pending |
| **Domain ohne Branding** | Default-Logo, Default-Farben | ⏳ Pending |
| **ModernFrontend Theme überschreibt Farben** | Theme-Farben haben Vorrang | ⏳ Pending |
| **Beide Plugins deaktiviert** | System funktioniert normal | ⏳ Pending |

**Test-Command:**
```bash
# Domain 1 testen
curl -H "Host: domain1.com" http://localhost:8095/ | grep "logo1.png"

# Domain 2 testen
curl -H "Host: domain2.com" http://localhost:8095/ | grep "logo2.png"
```

---

## 📝 Best Practices für Multi-Plugin-Systeme

### **1. Plugin-Reihenfolge definieren**

```php
// CleverBranding lädt FRÜH (order = -10)
$this->order = -10;

// ModernFrontend lädt NORMAL (order = 0)
$this->order = 0;

// Andere Plugins können Branding-Daten nutzen
```

### **2. Defensive Programmierung**

```php
// NIE davon ausgehen, dass ein Plugin geladen ist
$branding = GetBrandingForDomain();  // Hat Fallback!

// NICHT:
$logo = $plugins['CleverBranding']->domain_logo;  // ❌ Fatal Error wenn nicht geladen
```

### **3. Zentrale Konfiguration**

```php
// config.inc.php
define('BRANDING_ENABLED', true);
define('BRANDING_ALLOW_OVERRIDE', true);  // Erlaubt Theme-Überschreibung
```

### **4. Admin-UI Klarheit**

```
Admin-Panel:
├── Plugins
│   ├── CleverBranding       → "Domain-spezifisches Branding"
│   └── ModernFrontend
│       └── Theme            → "Site-weites Theme (überschreibt Branding)"
```

---

## 🎉 Fazit

### **Kollisions-Status: ✅ UNKRITISCH**

**CleverBranding und ModernFrontend CMS kollidieren NICHT, weil:**

1. ✅ **Unterschiedliche Ebenen**
   - CleverBranding = Konfigurations-Layer (Daten)
   - ModernFrontend = Präsentations-Layer (Templates)

2. ✅ **Keine Template-Überschreibungen**
   - CleverBranding hat keine OnHTMLHeader/OnBeforeHeader Hooks
   - Greift nicht in Template-Rendering ein

3. ✅ **Keine CSS/JS-Injections**
   - CleverBranding lädt kein eigenes CSS/JS
   - Nur Daten-Lieferant

4. ✅ **Isolierte Datenbank**
   - Separate Tabellen ohne Foreign Keys
   - Keine gegenseitige Abhängigkeit

### **Empfohlene Architektur**

```
CleverBranding  →  Liefert: Logo, Farben, Texte (per Domain)
       ↓
GetBrandingForDomain()  →  Zentrale API
       ↓
ModernFrontend  →  Nutzt: Branding-Daten in Templates
       ↓
HTML Output  →  Gebrandetes Layout mit CMS-Content
```

### **Nächste Schritte**

1. ✅ CleverBranding aktiviert - **ERLEDIGT**
2. ⏳ `GetBrandingForDomain()` API implementieren - **EMPFOHLEN**
3. ⏳ ModernFrontend Templates testen - **USER-TEST AUSSTEHEND**
4. ⏳ Multi-Domain Branding testen - **OPTIONAL**

---

**Erstellt am:** 2025-12-09 12:10 Uhr  
**Autor:** Windsurf AI  
**Basis:** Code-Analyse von tcbrn.plugin.php + Best Practices aus WordPress/Strapi/Headless-CMS-Architekturen  
**Quellen:** CleverBranding v1.3.1, ModernFrontend CMS, b1gMail Plugin-API
