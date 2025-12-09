# Branding API Documentation

**Version:** 1.0.0  
**Datum:** 2025-12-09  
**Datei:** `src/serverlib/branding.inc.php`  
**Autor:** b1gMail Project  

---

## 🎯 Überblick

Die **Branding API** ist eine zentrale Schnittstelle für Domain-spezifisches Branding in b1gMail. Sie integriert das **CleverBranding Plugin** als **Config-Layer** ohne Template-Überschreibungen.

### **Architektur-Prinzip**

```
CleverBranding Plugin  →  Liefert Domain-Konfiguration (DB)
           ↓
GetBrandingForDomain() →  Zentrale API-Schnittstelle
           ↓
ModernFrontend CMS     →  Nutzt Branding-Daten in Templates
           ↓
HTML Output            →  Gebrandetes Layout mit CMS-Content
```

**Vorteile:**
- ✅ **Separation of Concerns** - CleverBranding bleibt Config-Layer
- ✅ **Keine Template-Überschreibungen** - CMS behält Kontrolle
- ✅ **Zentrale API** - Ein Einstiegspunkt für alle Branding-Daten
- ✅ **Fallback-sicher** - Funktioniert auch ohne CleverBranding
- ✅ **Multi-Domain-fähig** - Unterschiedliches Branding per Domain

---

## 📚 API-Referenz

### **GetBrandingForDomain()**

Liefert Branding-Konfiguration für eine gegebene Domain.

#### **Signatur**

```php
function GetBrandingForDomain(string $domain, ?string $fallback = 'default'): array
```

#### **Parameter**

| Parameter | Typ | Default | Beschreibung |
|-----------|-----|---------|--------------|
| `$domain` | `string` | - | Domain (z.B. "mail.example.com") |
| `$fallback` | `string\|null` | `'default'` | Fallback-Profil-Name oder null |

#### **Rückgabe**

Assoziatives Array mit folgenden Keys:

| Key | Typ | Beispiel | Beschreibung |
|-----|-----|----------|--------------|
| `domain` | `string` | `"mail.example.com"` | Normalisierte Domain |
| `profile_id` | `int\|null` | `123` | CleverBranding-Profil-ID (null wenn Default) |
| `name` | `string` | `"Example Mail"` | Anzeigename / Brand-Name |
| `logo_url` | `string` | `"/branding/example/logo.png"` | Logo-Pfad |
| `favicon_url` | `string` | `"/branding/example/favicon.ico"` | Favicon-Pfad |
| `primary_color` | `string` | `"#0055aa"` | Primärfarbe (Hex) |
| `secondary_color` | `string` | `"#ffcc00"` | Sekundärfarbe (Hex) |
| `accent_color` | `string` | `"#00aa55"` | Akzentfarbe (Hex) |
| `background` | `string` | `"#ffffff"` | Hintergrundfarbe (Hex) |
| `css_class` | `string` | `"branding-example"` | CSS-Klasse für Body/Wrapper |
| `footer_text` | `string` | `"© 2025 Example Corp."` | Footer-Text |
| `login_title` | `string` | `"Willkommen bei Example Mail"` | Login-Seitentitel |
| `language` | `string` | `"de"` | Sprache (ISO 639-1) |
| `country` | `string` | `"de"` | Land (ISO 3166-1 alpha-2) |
| `xmailer` | `string` | `"Example Mail"` | X-Mailer Header |
| `template` | `string\|null` | `"modern"` | Template-Name (falls vorhanden) |
| `is_default` | `bool` | `true` | Ob Fallback-Branding |

#### **Auflösungs-Logik**

1. **Exakte Domain** - Sucht `mail.example.com` in CleverBranding-DB
2. **Basisdomain** - Falls nicht gefunden: Versucht `example.com`
3. **Fallback-Profil** - Falls nicht gefunden: Versucht `$fallback` (z.B. "default")
4. **Statisches Default** - Falls nichts gefunden: Liefert b1gMail-Standard-Branding

#### **Beispiele**

**Basis-Nutzung:**
```php
$branding = GetBrandingForDomain('mail.example.com');
echo $branding['name'];        // "Example Mail"
echo $branding['primary_color']; // "#0055aa"
```

**Mit Custom-Fallback:**
```php
$branding = GetBrandingForDomain('unknown.test', 'corporate');
// Versucht: unknown.test → test → corporate → default
```

**Ohne Fallback:**
```php
$branding = GetBrandingForDomain('test.com', null);
// Versucht nur: test.com → com → static default
```

---

### **NormalizeDomain()**

Normalisiert einen Domain-Namen.

#### **Signatur**

```php
function NormalizeDomain(string $domain): string
```

#### **Operationen**

- Konvertiert zu Kleinbuchstaben
- Entfernt Port-Nummer (z.B. `localhost:8095` → `localhost`)
- Entfernt führende/nachfolgende Whitespaces

#### **Beispiele**

```php
NormalizeDomain('LOCALHOST:8095');     // "localhost"
NormalizeDomain('Mail.Example.COM');   // "mail.example.com"
NormalizeDomain('  example.com  ');    // "example.com"
```

---

### **ExtractBaseDomain()**

Extrahiert die Basisdomain aus einem FQDN.

#### **Signatur**

```php
function ExtractBaseDomain(string $domain): string
```

#### **Logik**

- Nimmt die letzten 2 Teile eines FQDN
- Spezialfälle: `localhost`, IP-Adressen bleiben unverändert

#### **Beispiele**

```php
ExtractBaseDomain('mail.example.com');     // "example.com"
ExtractBaseDomain('sub.mail.example.com'); // "example.com"
ExtractBaseDomain('localhost');            // "localhost"
ExtractBaseDomain('example.com');          // "example.com"
ExtractBaseDomain('192.168.1.1');          // "192.168.1.1"
```

---

### **GetDefaultBranding()**

Liefert statisches Default-Branding.

#### **Signatur**

```php
function GetDefaultBranding(): array
```

#### **Verwendung**

Wird automatisch von `GetBrandingForDomain()` verwendet wenn:
- CleverBranding nicht installiert
- Keine Profile in DB vorhanden
- Domain nicht gefunden

#### **Default-Werte**

```php
[
    'profile_id'      => null,
    'name'            => 'b1gMail',
    'logo_url'        => '/images/b1gmail_logo.png',
    'favicon_url'     => '/favicon.ico',
    'primary_color'   => '#004080',
    'secondary_color' => '#0080ff',
    'accent_color'    => '#00aa55',
    'background'      => '#ffffff',
    'css_class'       => 'branding-default',
    'footer_text'     => '© 2025 b1gMail',
    'login_title'     => 'Willkommen bei b1gMail',
    'language'        => 'de',
    'country'         => 'de',
    'xmailer'         => 'b1gMail',
]
```

---

### **GetAllBrandingProfiles()**

Liefert alle verfügbaren Branding-Profile.

#### **Signatur**

```php
function GetAllBrandingProfiles(): array
```

#### **Rückgabe**

Array von Profilen:

```php
[
    [
        'profile_id' => 1,
        'domain'     => 'mail.example.com',
        'name'       => 'Example Mail',
        'language'   => 'de',
        'country'    => 'de',
        'active'     => true,
    ],
    // ...
]
```

#### **Verwendung**

Nützlich für:
- Admin-Übersichten
- Debugging
- Domain-Listen-Generierung

---

### **IsBrandingPluginActive()**

Prüft ob CleverBranding-Plugin aktiv ist.

#### **Signatur**

```php
function IsBrandingPluginActive(): bool
```

#### **Beispiel**

```php
if (IsBrandingPluginActive()) {
    // CleverBranding-spezifische Features
} else {
    // Fallback-Modus
}
```

---

### **GenerateBrandingCSS()**

Generiert CSS-Variablen-String aus Branding-Daten.

#### **Signatur**

```php
function GenerateBrandingCSS(array $branding): string
```

#### **Parameter**

`$branding` - Branding-Array von `GetBrandingForDomain()`

#### **Rückgabe**

CSS-String mit CSS Custom Properties

#### **Beispiel**

```php
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST']);
$css = GenerateBrandingCSS($branding);

echo '<style>' . $css . '</style>';
```

**Output:**

```css
:root {
  /* Branding für: mail.example.com */
  --brand-primary: #0055aa;
  --brand-secondary: #ffcc00;
  --brand-accent: #00aa55;
  --brand-background: #ffffff;
  --brand-name: 'Example Mail';
}

body.branding-example {
  background-color: var(--brand-background);
}
```

---

### **GetCountryCode()**

Konvertiert CleverBranding Country-ID zu ISO-Code.

#### **Signatur**

```php
function GetCountryCode($countryId): string
```

#### **Parameter**

`$countryId` - Integer (CleverBranding-ID) oder String (ISO-Code)

#### **Mapping**

| ID | ISO | Land |
|----|-----|------|
| 25 | de | Deutschland |
| 89 | at | Österreich |
| 105 | ch | Schweiz |
| 32 | fr | Frankreich |
| 37 | uk | Großbritannien |
| 133 | us | USA |
| ... | ... | ... |

**Vollständiges Mapping:** Siehe `src/serverlib/branding.inc.php` Zeilen 199-212

---

## 🔧 Integration

### **In ModernFrontend CMS Controller**

```php
// Controller / Bootstrap
require_once __DIR__ . '/serverlib/branding.inc.php';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$branding = GetBrandingForDomain($host);

// An Template-Engine übergeben (Smarty)
$smarty->assign('branding', $branding);
```

### **In Smarty-Template**

```html
<body class="{$branding.css_class}">
  <header style="background-color: {$branding.primary_color}">
    <img src="{$branding.logo_url}" alt="{$branding.name}">
    <h1>{$branding.login_title}</h1>
  </header>
  
  <main>
    {$page_content}
  </main>
  
  <footer>
    {$branding.footer_text}
  </footer>
</body>
```

### **In Plain PHP Template**

```php
<?php
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST']);
?>
<!DOCTYPE html>
<html lang="<?= $branding['language'] ?>">
<head>
    <title><?= htmlspecialchars($branding['name']) ?></title>
    <link rel="icon" href="<?= htmlspecialchars($branding['favicon_url']) ?>">
    <style>
        <?= GenerateBrandingCSS($branding) ?>
    </style>
</head>
<body class="<?= htmlspecialchars($branding['css_class']) ?>">
    <header style="background-color: <?= htmlspecialchars($branding['primary_color']) ?>">
        <img src="<?= htmlspecialchars($branding['logo_url']) ?>" 
             alt="<?= htmlspecialchars($branding['name']) ?>">
    </header>
    <footer><?= htmlspecialchars($branding['footer_text']) ?></footer>
</body>
</html>
```

### **In Admin-Panel**

```php
// Zeige alle verfügbaren Profile
$profiles = GetAllBrandingProfiles();

echo "<h2>Branding-Profile</h2>";
echo "<ul>";
foreach ($profiles as $profile) {
    $status = $profile['active'] ? '✅' : '❌';
    echo "<li>$status {$profile['domain']}: {$profile['name']} (ID: {$profile['profile_id']})</li>";
}
echo "</ul>";
```

---

## 🧪 Testing

### **Test-Script**

```bash
docker exec b1gmail php /var/www/html/test-branding-api.php
```

**Test-Bereiche:**
1. ✅ CleverBranding Plugin Status
2. ✅ Datenbank-Tabellen
3. ✅ Domain-Normalisierung
4. ✅ Basisdomain-Extraktion
5. ✅ Default-Branding
6. ✅ GetBrandingForDomain()
7. ✅ Verfügbare Profile
8. ✅ CSS-Generierung
9. ✅ Domain-Auflösung
10. ✅ Fallback-Mechanismus

---

## 📋 Best Practices

### **1. Defensive Programmierung**

✅ **RICHTIG:**
```php
$branding = GetBrandingForDomain($_SERVER['HTTP_HOST'] ?? 'localhost');
// Hat immer Fallback!
```

❌ **FALSCH:**
```php
$branding = $plugins['CleverBranding']->getBranding();  
// Fatal Error wenn Plugin nicht geladen!
```

### **2. CSS-Variablen verwenden**

✅ **RICHTIG:**
```css
:root {
  --brand-primary: <?= $branding['primary_color'] ?>;
}
button {
  background: var(--brand-primary);
}
```

❌ **FALSCH:**
```css
button {
  background: <?= $branding['primary_color'] ?>;  /* Nicht wiederverwendbar */
}
```

### **3. Escaping nicht vergessen**

✅ **RICHTIG:**
```php
<title><?= htmlspecialchars($branding['name']) ?></title>
```

❌ **FALSCH:**
```php
<title><?= $branding['name'] ?></title>  <!-- XSS-Risiko! -->
```

### **4. Caching berücksichtigen**

```php
// Optional: Branding cachen für Performance
$cacheKey = 'branding_' . md5($_SERVER['HTTP_HOST']);
$branding = $cache->get($cacheKey);

if (!$branding) {
    $branding = GetBrandingForDomain($_SERVER['HTTP_HOST']);
    $cache->set($cacheKey, $branding, 3600);  // 1h Cache
}
```

---

## 🔄 CleverBranding-Integration

### **Datenbank-Schema**

```sql
CREATE TABLE `bm60_tcbrn_plugin_domains` (
  `domainid` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  `mode` ENUM('standard','exact','regex') NOT NULL DEFAULT 'standard',
  `domain` TEXT NOT NULL,
  `template` VARCHAR(255),
  `language` VARCHAR(255),
  `country` INT UNSIGNED NOT NULL DEFAULT 25,  -- 25 = Deutschland
  `title` VARCHAR(255),
  `xmailer` VARCHAR(255),
  -- ... weitere Felder
);
```

### **API-Mapping**

| DB-Feld | API-Key | Transformation |
|---------|---------|----------------|
| `domainid` | `profile_id` | (int) |
| `title` | `name` | String |
| `domain` | `domain` | Normalisiert |
| `language` | `language` | ISO 639-1 |
| `country` | `country` | GetCountryCode() |
| `xmailer` | `xmailer` | String |
| `template` | `template` | String \| null |

**Fehlende DB-Felder:**
- `logo_url`, `favicon_url` → Template-basiert (`/branding/{domain}/logo.png`)
- Farben (`primary_color`, etc.) → Default-Werte, können über Template-CSS überschrieben werden

---

## 🚀 Deployment

### **1. Datei kopieren**

```bash
# API ist bereits in src/serverlib/branding.inc.php
```

### **2. In init.inc.php inkludieren**

```php
// In src/serverlib/init.inc.php
require_once __DIR__ . '/branding.inc.php';
```

### **3. CleverBranding installieren**

```bash
docker exec b1gmail php /var/www/html/install-cleverbranding.php
```

### **4. Tests ausführen**

```bash
docker exec b1gmail php /var/www/html/test-branding-api.php
```

### **5. In Templates integrieren**

Siehe **Integration**-Abschnitt oben.

---

## 🐛 Troubleshooting

### **Problem: "Tabelle existiert nicht"**

**Symptom:** `Table 'bm60_tcbrn_plugin_domains' doesn't exist`

**Lösung:**
```bash
# CleverBranding installieren
docker exec b1gmail php /var/www/html/install-cleverbranding.php

# Tabellen prüfen
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail --skip-ssl \
  -e "SHOW TABLES LIKE 'bm60_tcbrn%';"
```

---

### **Problem: "Undefined constant MYSQL_NUM"**

**Symptom:** PHP 8.x Fehler in `tcbrn.plugin.php`

**Lösung:**
```bash
# Bugfix anwenden
docker exec b1gmail sed -i 's/MYSQL_NUM/MYSQLI_NUM/g' /var/www/html/plugins/tcbrn.plugin.php

# Zurückkopieren
docker cp b1gmail:/var/www/html/plugins/tcbrn.plugin.php src/plugins/tcbrn.plugin.php
```

---

### **Problem: "Branding wird nicht geladen"**

**Debug-Schritte:**

1. **API-Test ausführen:**
   ```bash
   docker exec b1gmail php /var/www/html/test-branding-api.php
   ```

2. **Domain-Profile prüfen:**
   ```bash
   docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail --skip-ssl \
     -e "SELECT domainid, domain, title, active FROM bm60_tcbrn_plugin_domains;"
   ```

3. **Domain-Matching debuggen:**
   ```php
   $domain = $_SERVER['HTTP_HOST'];
   $normalized = NormalizeDomain($domain);
   $base = ExtractBaseDomain($normalized);
   
   echo "Original: $domain\n";
   echo "Normalized: $normalized\n";
   echo "Base: $base\n";
   ```

---

## 📚 Weiterführende Dokumentation

- **CleverBranding vs CMS:** `docs/CLEVERBRANDING_CMS_KOLLISION_ANALYSE.md`
- **Plugin-Status:** `docs/plugins-status.md`
- **Feature-Branch Workflow:** `docs/FEATURE_BRANCH_WORKFLOW.md`

---

**Erstellt am:** 2025-12-09  
**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**Autor:** b1gMail Project / Windsurf AI
