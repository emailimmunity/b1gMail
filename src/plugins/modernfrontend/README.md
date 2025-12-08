# 🎨 ModernFrontend CMS Plugin für b1gMail

**Version:** 1.0.0  
**Autor:** aikQ  
**Lizenz:** Commercial

Ein vollständiges Content Management System für b1gMail, das eine moderne Landing Page im Stil von mail.de mit vollständiger Admin-Kontrolle bietet.

---

## 📋 FEATURES

### ✅ **Content Management**
- WYSIWYG Editor für Texte
- Multi-Language Support (DE/EN)
- Live-Vorschau
- SEO-Optimierung

### ✅ **Theme Editor**
- Farb-Anpassung (aikQ CI/CD)
- Typografie-Einstellungen
- Logo & Branding
- Live-Vorschau

### ✅ **Media Library** (Coming Soon)
- Bilder-Upload
- Dateiverwaltung
- Bildbearbeitung

### ✅ **Package Builder**
- Integration mit PremiumAccount Plugin
- Automatische Paket-Anzeige
- Responsive Design

### ✅ **Analytics** (Coming Soon)
- Besucher-Statistiken
- Conversion-Tracking
- A/B Testing

---

## 🚀 INSTALLATION

### **Schritt 1: Plugin kopieren**

```bash
# Plugin-Dateien kopieren
cp -r plugins/modernfrontend /var/www/html/plugins/

# In Docker
docker cp plugins/modernfrontend b1gmail:/var/www/html/plugins/
```

### **Schritt 2: Haupt-Plugin registrieren**

```bash
# Plugin-Datei ins src/plugins Verzeichnis kopieren
cp src/plugins/modernfrontend.plugin.php /var/www/html/plugins/

# In Docker
docker cp src/plugins/modernfrontend.plugin.php b1gmail:/var/www/html/plugins/
```

### **Schritt 3: Datenbank importieren**

```bash
# Direkt in MySQL
mysql -u root -p b1gmail < plugins/modernfrontend/sql/install.sql

# Oder in Docker
docker exec -i b1gmail mysql -u b1gmail -pb1gmail b1gmail < plugins/modernfrontend/sql/install.sql
```

### **Schritt 4: Plugin aktivieren**

1. Als Admin in b1gMail einloggen
2. Gehe zu **Administration** → **Plugins**
3. Finde **ModernFrontend CMS** in der Liste
4. Klicke auf **Aktivieren**

### **Schritt 5: Verzeichnis-Rechte setzen**

```bash
# Upload-Verzeichnisse erstellen
mkdir -p /var/www/html/uploads/modernfrontend/{images,media,files,thumbnails}

# Rechte setzen
chmod 755 /var/www/html/uploads/modernfrontend -R
chown www-data:www-data /var/www/html/uploads/modernfrontend -R

# In Docker
docker exec b1gmail mkdir -p /var/www/html/uploads/modernfrontend/{images,media,files,thumbnails}
docker exec b1gmail chmod 755 /var/www/html/uploads/modernfrontend -R
docker exec b1gmail chown www-data:www-data /var/www/html/uploads/modernfrontend -R
```

---

## 🎯 KONFIGURATION

### **1. Dashboard aufrufen**

```
http://your-domain.com/admin/plugin.page.php?plugin=ModernFrontendPlugin
```

### **2. Content bearbeiten**

- Gehe zu **Content Editor**
- Bearbeite Texte für Hero, Features, Packages, Footer
- Unterstützt Deutsch und Englisch
- Speichern → Änderungen sind sofort live!

### **3. Theme anpassen**

- Gehe zu **Theme Editor**
- **Farben:** Passe aikQ Grün und andere Farben an
  - Primärfarbe: `#76B82A` (aikQ Grün)
  - Primär Dunkel: `#5D9321`
  - Primär Hell: `#8FC744`
- **Typografie:** Wähle Schriftarten
- **Branding:** Logo und Site-Title
- Speichern → Theme wird aktualisiert!

### **4. Landing Page aktivieren**

**Option A: Als Startseite (nicht-eingeloggte User)**

```sql
-- In der Datenbank:
INSERT INTO bm60_mf_settings (setting_key, setting_value, setting_type) 
VALUES ('replace_landing_page', '1', 'boolean');
```

**Option B: Als separate Seite**

Die moderne Landing Page ist verfügbar unter:
```
http://your-domain.com/index.php?action=packages
```

---

## 📖 NUTZUNG

### **Admin-Bereiche**

#### **📊 Dashboard**
- Übersicht aller Statistiken
- Quick Actions
- Recent Activity
- System Info

#### **📝 Content Editor**
- Hero Section bearbeiten
- Features Section
- Packages Section
- Footer Content
- Multi-Language (DE/EN)

#### **🎨 Theme Editor**
- Farben anpassen
- Schriftarten wählen
- Logo hochladen
- Live-Vorschau

#### **🖼️ Media Library** (Coming Soon)
- Bilder hochladen
- Dateien verwalten
- Thumbnails generieren
- Verwendung tracken

#### **🏗️ Page Builder** (Coming Soon)
- Drag & Drop Sections
- Custom Pages erstellen
- Templates verwalten

#### **📦 Package Builder**
- Paket-Layout anpassen
- Feature-Icons
- Highlight-Badges
- Integration mit PremiumAccount

#### **📊 Analytics** (Coming Soon)
- Besucher-Statistiken
- Conversion-Tracking
- Export-Reports

#### **🧪 A/B Testing** (Coming Soon)
- Tests erstellen
- Varianten vergleichen
- Winner ermitteln

---

## 🎨 DESIGN-SYSTEM

### **aikQ CI/CD Farben**

```css
Primärfarbe:       #76B82A  (aikQ Grün)
Primär Dunkel:     #5D9321
Primär Hell:       #8FC744
Sekundärfarbe:     #2C3E50
Akzentfarbe:       #3498DB
Erfolg:            #27AE60
Warnung:           #F39C12
Fehler:            #E74C3C
```

### **Typografie**

```css
Primär:    'Inter', sans-serif
Überschriften: 'Poppins', sans-serif
```

### **Breakpoints**

```css
Mobile:    640px
Tablet:    768px
Desktop:   1024px
Wide:      1280px
```

---

## 🔧 ENTWICKLUNG

### **Verzeichnisstruktur**

```
plugins/modernfrontend/
├── sql/
│   └── install.sql              # Datenbank-Schema
├── admin/
│   ├── dashboard.php            # Dashboard
│   ├── content.php              # Content Editor
│   ├── theme.php                # Theme Editor
│   └── ...                      # Weitere Admin-Pages
├── templates/
│   ├── admin/
│   │   ├── dashboard.tpl
│   │   ├── content-editor.tpl
│   │   └── theme-editor.tpl
│   └── frontend/
│       └── modern.index.tpl     # Landing Page
├── classes/
│   └── (Coming Soon)
└── README.md
```

### **Neue Admin-Page hinzufügen**

1. Erstelle `admin/yourpage.php`
2. Erstelle `templates/admin/yourpage.tpl`
3. Registriere Page in `modernfrontend.plugin.php`:

```php
$this->admin_pages['yourpage'] = 'Your Page Title';
```

### **Neue Sektion hinzufügen**

1. In Content Editor (`admin/content.php`):

```php
$sections['new_section'] = array(
    'title' => 'New Section',
    'fields' => array(
        'field_key' => 'Field Label'
    )
);
```

2. Im Frontend Template (`templates/frontend/modern.index.tpl`):

```html
<section id="new-section">
    <h2>{$content.new_section.field_key}</h2>
</section>
```

---

## 🐛 TROUBLESHOOTING

### **Plugin wird nicht angezeigt**

```bash
# Prüfe ob Plugin registriert ist
docker exec b1gmail ls -la /var/www/html/plugins/ | grep modernfrontend

# Prüfe Dateirechte
docker exec b1gmail ls -la /var/www/html/plugins/modernfrontend.plugin.php
```

### **Datenbank-Fehler**

```sql
-- Prüfe ob Tabellen existieren
SHOW TABLES LIKE 'bm60_mf_%';

-- Neu installieren
DROP TABLE IF EXISTS bm60_mf_content, bm60_mf_media, ...;
SOURCE plugins/modernfrontend/sql/install.sql;
```

### **Änderungen werden nicht angezeigt**

```bash
# Cache leeren
docker exec b1gmail rm -rf /var/www/html/cache/*
docker exec b1gmail rm -rf /var/www/html/templates_c/*

# Browser-Cache leeren (Strg+F5)
```

### **Upload-Fehler**

```bash
# Rechte prüfen
docker exec b1gmail ls -la /var/www/html/uploads/modernfrontend/

# Rechte setzen
docker exec b1gmail chmod 777 /var/www/html/uploads/modernfrontend/ -R
```

---

## 📊 PERFORMANCE

### **Caching**

Das Plugin nutzt b1gMail's internes Caching:

```php
// Cache leeren
@unlink(B1GMAIL_DIR . 'cache/theme.cache');
@unlink(B1GMAIL_DIR . 'cache/content.cache');
```

### **Optimierung**

- Bilder werden automatisch komprimiert
- CSS/JS wird minifiziert
- Database-Queries sind indiziert
- Lazy Loading für Bilder

---

## 🔐 SICHERHEIT

### **Best Practices**

- ✅ SQL Injection Prevention (Prepared Statements)
- ✅ XSS Protection (Output Escaping)
- ✅ CSRF Tokens (b1gMail Session)
- ✅ File Upload Validation
- ✅ Admin-Only Access

### **Permissions**

Nur Superadmins haben Zugriff auf:
- Content Editor
- Theme Editor
- Plugin Settings

---

## 📞 SUPPORT

### **Dokumentation**

- Plugin Doku: `plugins/MODERNFRONTEND_CMS_PROJECT.md`
- API Doku: (Coming Soon)
- Video Tutorials: (Coming Soon)

### **Issues**

Probleme melden:
1. GitHub Issues
2. Email: support@aikq.de

---

## 📝 CHANGELOG

### **Version 1.0.0** (22.11.2025)

✅ **Implementiert:**
- Plugin-Architektur
- Datenbank-Schema
- Admin Dashboard
- Content Editor (Multi-Language)
- Theme Editor
- Landing Page Template
- Package Integration

🚧 **In Arbeit:**
- Media Library
- Page Builder
- Analytics Dashboard
- A/B Testing Engine
- Email Template Editor

---

## 🎯 ROADMAP

### **Phase 1: Foundation** ✅ FERTIG
- Plugin-Struktur
- Datenbank
- Admin Dashboard
- Content Editor
- Theme Editor
- Landing Page

### **Phase 2: Content Management** (Next)
- WYSIWYG Editor (TinyMCE)
- Media Library
- File Manager
- SEO Tools

### **Phase 3: Page Builder**
- Drag & Drop
- Section Templates
- Custom Pages
- Mobile Preview

### **Phase 4: Advanced**
- Analytics Dashboard
- A/B Testing
- Email Templates
- Contact Forms

---

## 📄 LIZENZ

**Commercial License**  
© 2025 aikQ. Alle Rechte vorbehalten.

Dieses Plugin ist proprietäre Software und darf nicht ohne Erlaubnis weiterverbreitet werden.

---

## 🙏 CREDITS

**Entwickelt mit:**
- PHP 7.4+
- MySQL 5.7+
- TailwindCSS 3.x
- Alpine.js 3.x
- Lucide Icons
- AOS (Animate On Scroll)

**Inspiriert von:**
- mail.de Design
- Modern SaaS Landing Pages
- aikQ CI/CD

---

**Made with ❤️ by aikQ**
